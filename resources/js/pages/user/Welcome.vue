<script setup lang="ts">
import AnnouncementCarousel from '@/components/AnnouncementCarousel.vue';
import AnnouncementModal from '@/components/AnnouncementModal.vue';
import BottomNavigation from '@/components/BottomNavigation.vue';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { useToast } from '@/components/ui/toast/use-toast';
import { useCompanySettings } from '@/composables/useCompanySettings';
import { useFaceRecognition } from '@/composables/useFaceRecognition';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import * as faceapi from 'face-api.js';
import { BarChart3, Calendar, CheckCircle, Clock, Eye, Loader2, LogOut, MapPin, User, UserCircle } from 'lucide-vue-next';
import { onMounted, onUnmounted, ref, watch } from 'vue';

interface User {
    id: number;
    name: string;
    email: string;
    employee_id: string;
    department?: {
        id: number;
        name: string;
    };
    position?: {
        id: number;
        title: string;
    };
}

interface TodayAttendance {
    id: number;
    check_in_time: string | null;
    check_out_time: string | null;
    status: string;
    work_duration: number | null;
    office_location: {
        id: number;
        name: string;
    };
}

interface OfficeLocation {
    id: number;
    name: string;
    address: string;
    latitude: number;
    longitude: number;
    radius_meters: number;
}

interface Props {
    user?: User;
    todayAttendance?: TodayAttendance | null;
    officeLocations?: OfficeLocation[];
    stats?: {
        monthly_attendance_days: number;
        monthly_late_days: number;
        monthly_work_hours: number;
        leave_balance: number;
    };
    faceRecognitionEnabled?: boolean;
    faceDescriptors?: number[][];
    announcements?: Announcement[];
    allowOutsideRadius?: boolean;
}

interface AnnouncementCategory {
    id: number;
    name: string;
    color: string;
    icon: string;
}

interface Author {
    id: number;
    name: string;
}

interface Announcement {
    id: number;
    title: string;
    content: string;
    excerpt: string;
    category: AnnouncementCategory;
    author: Author;
    priority: 'low' | 'normal' | 'high' | 'urgent';
    published_at: string;
    expires_at: string | null;
    image_url: string | null;
}

const { user, todayAttendance, officeLocations, stats, faceRecognitionEnabled, faceDescriptors, announcements, allowOutsideRadius } =
    defineProps<Props>();

const { companyName, companyLogo } = useCompanySettings();
const { toast } = useToast();

// Face recognition composable for reactive status
const { faceRecognitionStatus, faceDescriptors: reactiveFaceDescriptors, initializeFaceRecognitionStatus } = useFaceRecognition();

// Alert modal function
const showAlert = (title: string, message: string, variant: 'success' | 'destructive' | 'warning' | 'default' = 'destructive') => {
    alertModal.value = {
        isOpen: true,
        title,
        message,
        variant,
    };
};

// Check-in form and location state
const form = useForm({
    office_location_id: '',
    latitude: 0,
    longitude: 0,
    face_confidence: 0,
    face_photo: '',
});

const locationStatus = ref<'idle' | 'loading' | 'success' | 'error'>('idle');

// Modal state for alerts
const alertModal = ref({
    isOpen: false,
    title: '',
    message: '',
    variant: 'destructive' as 'success' | 'destructive' | 'warning' | 'default',
});
const locationError = ref('');
const selectedOffice = ref<OfficeLocation | null>(null);
const isInRange = ref(false);
const distanceToOffice = ref(0);
const isCheckingIn = ref(false);

// Announcement modal state
const selectedAnnouncement = ref<Announcement | null>(null);
const showAnnouncementModal = ref(false);

const handleAnnouncementClick = (announcement: Announcement) => {
    selectedAnnouncement.value = announcement;
    showAnnouncementModal.value = true;
};

const closeAnnouncementModal = () => {
    showAnnouncementModal.value = false;
    selectedAnnouncement.value = null;
};
const isCheckingOut = ref(false);

// Real-time face recognition state
const videoElement = ref<HTMLVideoElement | null>(null);
const canvasElement = ref<HTMLCanvasElement | null>(null);
const faceDetectionActive = ref(false);
const faceMatchConfidence = ref(0);
const isFaceMatched = ref(false);
const detectionInterval = ref<number | null>(null);
const stream = ref<MediaStream | null>(null);

// Auto-capture state
const capturedFaceData = ref<{
    confidence: number;
    timestamp: Date;
    descriptor: Float32Array;
    imageDataUrl: string;
} | null>(null);
const isFaceCaptured = ref(false);
const captureTimeout = ref<number | null>(null);
const isCapturing = ref(false);

const formatTime = (time: string | null) => {
    if (!time) return '--:--';
    return time.substring(0, 5);
};

const formatDuration = (minutes: number | null) => {
    if (!minutes) return '--';
    const hours = Math.floor(minutes / 60);
    const mins = minutes % 60;
    return `${hours}h ${mins}m`;
};

const currentTime = ref('');
const currentDate = ref('');
let timeInterval: number | null = null;

const updateTime = () => {
    currentTime.value = new Date().toLocaleTimeString('id-ID', {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
    });
    currentDate.value = new Date().toLocaleDateString('id-ID', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};

// Location and check-in functions
const calculateDistance = (lat1: number, lng1: number, lat2: number, lng2: number) => {
    const earthRadius = 6371000; // meters
    const lat1Rad = (lat1 * Math.PI) / 180;
    const lng1Rad = (lng1 * Math.PI) / 180;
    const lat2Rad = (lat2 * Math.PI) / 180;
    const lng2Rad = (lng2 * Math.PI) / 180;

    const deltaLat = lat2Rad - lat1Rad;
    const deltaLng = lng2Rad - lng1Rad;

    const a =
        Math.sin(deltaLat / 2) * Math.sin(deltaLat / 2) + Math.cos(lat1Rad) * Math.cos(lat2Rad) * Math.sin(deltaLng / 2) * Math.sin(deltaLng / 2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));

    return earthRadius * c;
};

const getCurrentLocation = (autoAction = false) => {
    if (!navigator.geolocation) {
        locationStatus.value = 'error';
        locationError.value = 'Geolocation is not supported by this browser.';
        toast({
            title: '❌ Geolocation Error',
            description: 'Browser ini tidak mendukung geolocation. Mohon gunakan browser yang mendukung GPS.',
            variant: 'destructive',
            duration: 5000,
        });
        return;
    }

    locationStatus.value = 'loading';
    navigator.geolocation.getCurrentPosition(
        (position) => {
            form.latitude = position.coords.latitude;
            form.longitude = position.coords.longitude;
            locationStatus.value = 'success';
            checkOfficeProximity();

            // Auto perform action if requested
            if (autoAction === 'checkin') {
                performCheckIn();
            } else if (autoAction === 'checkout') {
                performCheckOut();
            }
        },
        (error) => {
            locationStatus.value = 'error';
            let errorMessage = '';
            switch (error.code) {
                case error.PERMISSION_DENIED:
                    locationError.value = 'Akses lokasi ditolak. Mohon izinkan akses lokasi.';
                    errorMessage = 'Akses lokasi ditolak. Mohon izinkan akses lokasi di browser dan coba lagi.';
                    break;
                case error.POSITION_UNAVAILABLE:
                    locationError.value = 'Informasi lokasi tidak tersedia.';
                    errorMessage = 'Informasi lokasi tidak tersedia. Pastikan GPS aktif dan koneksi internet stabil.';
                    break;
                case error.TIMEOUT:
                    locationError.value = 'Permintaan lokasi timeout.';
                    errorMessage = 'Permintaan lokasi timeout. Coba lagi dalam beberapa saat.';
                    break;
                default:
                    locationError.value = 'Terjadi kesalahan saat mengambil lokasi.';
                    errorMessage = 'Terjadi kesalahan saat mengambil lokasi. Mohon coba lagi.';
                    break;
            }
            toast({
                title: '❌ Gagal Mengambil Lokasi',
                description: errorMessage,
                variant: 'destructive',
                duration: 5000,
            });
        },
        {
            enableHighAccuracy: true,
            timeout: 10000,
            maximumAge: 60000,
        },
    );
};

const checkOfficeProximity = () => {
    if (!form.latitude || !form.longitude || !officeLocations) return;

    let nearestOffice: OfficeLocation | null = null;
    let nearestDistance = Infinity;
    let isWithinRange = false;

    officeLocations.forEach((office) => {
        const distance = calculateDistance(form.latitude, form.longitude, office.latitude, office.longitude);

        if (distance < nearestDistance) {
            nearestDistance = distance;
            nearestOffice = office;
        }

        if (distance <= office.radius_meters) {
            isWithinRange = true;
            form.office_location_id = office.id.toString();
        }
    });

    selectedOffice.value = nearestOffice;
    distanceToOffice.value = Math.round(nearestDistance);
    isInRange.value = isWithinRange;
};

const performCheckIn = () => {
    console.log(
        'performCheckIn called - isInRange:',
        isInRange.value,
        'faceRecognitionEnabled:',
        faceRecognitionEnabled,
        'faceDescriptors:',
        faceDescriptors?.length,
        'isFaceCaptured:',
        isFaceCaptured.value,
    );

    // Check if user is in range OR has permission to check-in outside radius
    if (!isInRange.value && !allowOutsideRadius) {
        console.log('Not in range - distanceToOffice:', distanceToOffice.value, 'selectedOffice:', selectedOffice.value);
        if (selectedOffice.value) {
            const distanceNeeded = Math.round(distanceToOffice.value - selectedOffice.value.radius_meters);
            console.log('Distance needed:', distanceNeeded, 'radius:', selectedOffice.value.radius_meters);
            showAlert(
                '❌ Tidak Dapat Check In',
                `Anda masih berada di luar radius ${selectedOffice.value.radius_meters}m. Mohon mendekat ${distanceNeeded}m lagi ke ${selectedOffice.value.name}.`,
                'destructive',
            );
        } else {
            showAlert(
                '❌ Tidak Dapat Check In',
                'Anda tidak berada dalam jangkauan lokasi kantor manapun. Mohon mendekat ke lokasi kantor.',
                'destructive',
            );
        }
        locationStatus.value = 'idle'; // Reset status so user can try again
        return;
    }

    // Check if face recognition is required and captured
    if (faceRecognitionStatus.value.enabled && faceRecognitionStatus.value.has_descriptors) {
        if (!isFaceCaptured.value) {
            showAlert('❌ Verifikasi Wajah Diperlukan', 'Posisikan wajah Anda di depan kamera untuk verifikasi terlebih dahulu.', 'destructive');
            return;
        }
    }

    // Proceed with check-in (location and face verified)
    executeCheckIn();
};

const executeCheckIn = () => {
    console.log('executeCheckIn called - starting check-in process');
    isCheckingIn.value = true;

    // Set face confidence and photo if face recognition is enabled and captured
    if (faceRecognitionStatus.value.enabled && faceRecognitionStatus.value.has_descriptors && capturedFaceData.value) {
        console.log('Setting face data:', {
            confidence: capturedFaceData.value.confidence,
            hasImage: !!capturedFaceData.value.imageDataUrl,
            imageLength: capturedFaceData.value.imageDataUrl?.length,
        });
        form.face_confidence = capturedFaceData.value.confidence;
        form.face_photo = capturedFaceData.value.imageDataUrl;
    } else {
        console.log('Face data not available:', {
            faceRecognitionEnabled: faceRecognitionStatus.value.enabled,
            hasDescriptors: faceRecognitionStatus.value.has_descriptors,
            hasCapturedData: !!capturedFaceData.value,
        });
    }

    toast({
        title: '🕐 Sedang Check In...',
        description: 'Memproses absensi masuk Anda.',
        variant: 'default',
    });

    form.post('/attendance/check-in', {
        onSuccess: () => {
            isCheckingIn.value = false;
            locationStatus.value = 'idle'; // Reset for next time
            stopFaceDetection(); // Stop face detection after successful check-in

            // Reset capture state
            capturedFaceData.value = null;
            isFaceCaptured.value = false;

            toast({
                title: '✅ Check In Berhasil!',
                description: `Absensi masuk Anda telah tercatat pada ${new Date().toLocaleTimeString('id-ID')}.`,
                variant: 'success',
                duration: 5000,
            });

            // Use Inertia router.reload() to fetch fresh data from server
            // This forces a fresh request and bypasses any cache
            router.reload({ only: ['todayAttendance'] });
        },
        onError: (errors) => {
            isCheckingIn.value = false;
            locationStatus.value = 'idle'; // Reset so user can try again
            console.error('Check-in errors:', errors);
            toast({
                title: '❌ Check In Gagal',
                description: errors.message || 'Terjadi kesalahan saat melakukan check in. Silakan coba lagi.',
                variant: 'destructive',
                duration: 6000,
            });
        },
        preserveState: false,
        preserveScroll: false,
    });
};

const handleCheckInClick = () => {
    console.log(
        'Check-in clicked - locationStatus:',
        locationStatus.value,
        'isInRange:',
        isInRange.value,
        'faceRecognitionEnabled:',
        faceRecognitionStatus.value.enabled,
        'hasDescriptors:',
        faceRecognitionStatus.value.has_descriptors,
        'isFaceCaptured:',
        isFaceCaptured.value,
    );

    if (locationStatus.value === 'idle' || locationStatus.value === 'error') {
        console.log('Getting current location...');
        getCurrentLocation('checkin');
    } else if (locationStatus.value === 'success' && (isInRange.value || allowOutsideRadius)) {
        console.log('Performing check-in...');
        performCheckIn();
    } else {
        console.log('Check-in conditions not met:', {
            locationStatus: locationStatus.value,
            isInRange: isInRange.value,
            allowOutsideRadius: allowOutsideRadius,
            buttonDisabled:
                isCheckingIn.value ||
                (locationStatus.value === 'success' && !isInRange.value && !allowOutsideRadius) ||
                (faceRecognitionEnabled && faceDescriptors && !isFaceCaptured.value),
        });

        // Provide specific error message
        if (locationStatus.value === 'success' && !isInRange.value && !allowOutsideRadius) {
            toast({
                title: '❌ Di Luar Area Kantor',
                description: 'Anda berada di luar radius kantor. Mohon mendekat ke area kantor untuk check-in.',
                variant: 'destructive',
                duration: 4000,
            });
        } else if (faceRecognitionStatus.value.enabled && faceRecognitionStatus.value.has_descriptors && !isFaceCaptured.value) {
            toast({
                title: '❌ Verifikasi Wajah Diperlukan',
                description: 'Posisikan wajah Anda di depan kamera untuk verifikasi sebelum check-in.',
                variant: 'destructive',
                duration: 4000,
            });
        } else if (isCheckingIn.value) {
            toast({
                title: '⏳ Sedang Proses Check-in',
                description: 'Check-in sedang dalam proses. Mohon tunggu sebentar.',
                variant: 'default',
                duration: 3000,
            });
        }
    }
};

// Real-time face detection functions
const loadFaceApiModels = async () => {
    try {
        console.log('Loading face-api models...');

        // Load all required models including TinyYolov2 for face detection
        await Promise.all([
            faceapi.nets.tinyFaceDetector.loadFromUri('/models'),
            faceapi.nets.ssdMobilenetv1.loadFromUri('/models'),
            faceapi.nets.faceLandmark68Net.loadFromUri('/models'),
            faceapi.nets.faceRecognitionNet.loadFromUri('/models'),
        ]);

        console.log('Face-api models loaded successfully');
        return true;
    } catch (error) {
        console.error('Failed to load face-api models:', error);
        toast({
            title: '❌ Face Recognition Error',
            description: 'Gagal memuat model face recognition. Pastikan koneksi internet stabil dan refresh halaman.',
            variant: 'destructive',
            duration: 5000,
        });
        return false;
    }
};

const startFaceDetection = async () => {
    if (!faceRecognitionStatus.value.enabled || !faceRecognitionStatus.value.has_descriptors) {
        return;
    }

    try {
        // Load face-api models
        const modelsLoaded = await loadFaceApiModels();
        if (!modelsLoaded) {
            console.error('Cannot start face detection: models not loaded');
            toast({
                title: '❌ Face Recognition Tidak Tersedia',
                description: 'Model face recognition gagal dimuat. Face recognition tidak dapat digunakan.',
                variant: 'destructive',
                duration: 5000,
            });
            return;
        }

        // Mobile-optimized video stream
        const isMobile = /Android|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);

        stream.value = await navigator.mediaDevices.getUserMedia({
            video: {
                width: isMobile ? 240 : 320,
                height: isMobile ? 180 : 240,
                facingMode: 'user',
                frameRate: { ideal: isMobile ? 10 : 15 },
            },
        });

        if (videoElement.value) {
            videoElement.value.srcObject = stream.value;
            await new Promise((resolve) => {
                videoElement.value!.onloadedmetadata = resolve;
            });

            faceDetectionActive.value = true;
            startDetectionLoop();
        }
    } catch (error) {
        console.error('Failed to start face detection:', error);
        toast({
            title: '❌ Kamera Error',
            description: 'Tidak dapat mengakses kamera untuk verifikasi wajah.',
            variant: 'destructive',
        });
    }
};

const startDetectionLoop = () => {
    if (detectionInterval.value) {
        clearInterval(detectionInterval.value);
    }

    // Mobile-optimized detection interval
    const isMobile = /Android|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
    const detectionDelay = isMobile ? 2000 : 1000; // 2 seconds for mobile, 1 second for desktop

    detectionInterval.value = window.setInterval(async () => {
        await detectFace();
    }, detectionDelay);
};

const detectFace = async () => {
    if (!videoElement.value || !canvasElement.value || !reactiveFaceDescriptors.value || reactiveFaceDescriptors.value.length === 0) {
        return;
    }

    try {
        // Mobile-optimized detection options
        const isMobile = /Android|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
        const detectionOptions = new faceapi.TinyFaceDetectorOptions({
            inputSize: isMobile ? 128 : 416, // Smaller input size for mobile
            scoreThreshold: isMobile ? 0.3 : 0.5, // Lower threshold for mobile
        });

        const detection = await faceapi.detectSingleFace(videoElement.value, detectionOptions).withFaceLandmarks().withFaceDescriptor();

        if (detection) {
            // Compare with stored descriptors
            const storedDescriptors = reactiveFaceDescriptors.value.map((desc) => new Float32Array(desc));
            const currentDescriptor = detection.descriptor;

            let bestMatch = 0;
            for (const storedDesc of storedDescriptors) {
                const distance = faceapi.euclideanDistance(currentDescriptor, storedDesc);
                const similarity = Math.max(0, (1 - distance) * 100);
                bestMatch = Math.max(bestMatch, similarity);
            }

            faceMatchConfidence.value = Math.round(bestMatch);
            isFaceMatched.value = bestMatch >= 65; // 65% confidence threshold for faster detection

            // Auto-capture face when verified
            if (isFaceMatched.value && !isFaceCaptured.value) {
                autoCaptureFace(bestMatch, currentDescriptor);
            }

            // Draw detection on canvas
            const canvas = canvasElement.value;
            if (canvas) {
                const displaySize = { width: 320, height: 320 };
                faceapi.matchDimensions(canvas, displaySize);

                // Clear canvas without drawing detection box
                const ctx = canvas.getContext('2d');
                if (ctx) {
                    ctx.clearRect(0, 0, canvas.width, canvas.height);
                }
            }
        } else {
            isFaceMatched.value = false;
            faceMatchConfidence.value = 0;

            // Clear canvas
            const canvas = canvasElement.value;
            const ctx = canvas?.getContext('2d');
            if (ctx) {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
            }
        }
    } catch (error) {
        console.error('Face detection error:', error);
    }
};

const autoCaptureFace = (confidence: number, descriptor: Float32Array) => {
    // Clear any existing timeout
    if (captureTimeout.value) {
        clearTimeout(captureTimeout.value);
    }

    // Start capture process
    isCapturing.value = true;

    // Set timeout to capture after 0.5 seconds of stable detection
    captureTimeout.value = window.setTimeout(() => {
        // Capture image from video
        const imageDataUrl = captureImageFromVideo();

        if (imageDataUrl) {
            capturedFaceData.value = {
                confidence: Math.round(confidence),
                timestamp: new Date(),
                descriptor: descriptor,
                imageDataUrl: imageDataUrl,
            };
            isFaceCaptured.value = true;
            isCapturing.value = false;

            console.log('Face captured successfully:', {
                confidence: Math.round(confidence),
                imageLength: imageDataUrl.length,
                timestamp: new Date(),
            });

            toast({
                title: '✅ Wajah Tertangkap!',
                description: `Verifikasi berhasil dengan tingkat keyakinan ${Math.round(confidence)}%. Anda dapat check-in sekarang.`,
                variant: 'success',
                duration: 3000,
            });

            // Stop detection after successful capture
            stopFaceDetection();
        }
    }, 500); // 0.5 second delay for fast capture
};

const captureImageFromVideo = (): string | null => {
    if (!videoElement.value) return null;

    try {
        // Create a temporary canvas to capture the video frame
        const tempCanvas = document.createElement('canvas');
        const tempCtx = tempCanvas.getContext('2d');

        if (!tempCtx) return null;

        // Set canvas size to video size
        tempCanvas.width = videoElement.value.videoWidth;
        tempCanvas.height = videoElement.value.videoHeight;

        // Draw current video frame to canvas
        tempCtx.drawImage(videoElement.value, 0, 0);

        // Convert to data URL (base64 image)
        return tempCanvas.toDataURL('image/jpeg', 0.8);
    } catch (error) {
        console.error('Failed to capture image from video:', error);
        return null;
    }
};

const stopFaceDetection = () => {
    if (detectionInterval.value) {
        clearInterval(detectionInterval.value);
        detectionInterval.value = null;
    }

    if (captureTimeout.value) {
        clearTimeout(captureTimeout.value);
        captureTimeout.value = null;
    }

    if (stream.value) {
        stream.value.getTracks().forEach((track) => track.stop());
        stream.value = null;
    }

    faceDetectionActive.value = false;
    isFaceMatched.value = false;
    faceMatchConfidence.value = 0;
    isCapturing.value = false;
};

const resetCapture = () => {
    // Reset capture state
    capturedFaceData.value = null;
    isFaceCaptured.value = false;

    // Restart face detection
    startFaceDetection();

    toast({
        title: '🔄 Memulai Ulang',
        description: 'Verifikasi wajah dimulai ulang. Posisikan wajah Anda ke kamera.',
        variant: 'default',
        duration: 3000,
    });
};

const performCheckOut = () => {
    isCheckingOut.value = true;
    toast({
        title: '🕐 Sedang Check Out...',
        description: 'Memproses absensi keluar Anda.',
        variant: 'default',
    });

    const checkoutForm = useForm({
        latitude: form.latitude,
        longitude: form.longitude,
    });

    checkoutForm.post('/attendance/check-out', {
        onSuccess: () => {
            isCheckingOut.value = false;
            locationStatus.value = 'idle'; // Reset for next time
            toast({
                title: '✅ Check Out Berhasil!',
                description: `Absensi keluar Anda telah tercatat pada ${new Date().toLocaleTimeString('id-ID')}.`,
                variant: 'success',
                duration: 5000,
            });
            // Refresh the page to update attendance data
            window.location.reload();
        },
        onError: (errors) => {
            isCheckingOut.value = false;
            locationStatus.value = 'idle'; // Reset so user can try again
            console.error('Check-out errors:', errors);
            toast({
                title: '❌ Check Out Gagal',
                description: errors.message || 'Terjadi kesalahan saat melakukan check out. Silakan coba lagi.',
                variant: 'destructive',
                duration: 6000,
            });
        },
        preserveState: true,
        preserveScroll: true,
    });
};

const handleCheckOutClick = () => {
    if (locationStatus.value === 'idle' || locationStatus.value === 'error') {
        getCurrentLocation('checkout');
    } else if (locationStatus.value === 'success') {
        performCheckOut();
    }
};

// Watch for face recognition status changes
watch(
    () => faceRecognitionStatus.value.enabled && faceRecognitionStatus.value.has_descriptors,
    (newValue, oldValue) => {
        console.log('Face recognition status changed:', { newValue, oldValue });

        if (!newValue && oldValue) {
            // Face recognition was disabled or data was deleted - stop detection
            console.log('Face recognition disabled - stopping detection');
            stopFaceDetection();

            // Clear any captured data
            capturedFaceData.value = null;
            isFaceCaptured.value = false;
        } else if (newValue && !oldValue && !todayAttendance?.check_in_time) {
            // Face recognition was enabled - start detection
            console.log('Face recognition enabled - starting detection');
            startFaceDetection();
        }
    },
);

onMounted(async () => {
    updateTime();
    timeInterval = window.setInterval(updateTime, 1000);

    // Initialize face recognition status from props
    await initializeFaceRecognitionStatus({
        face_recognition_enabled: faceRecognitionEnabled,
        face_recognition_mandatory: user?.face_recognition_mandatory,
        face_setup_at: user?.face_setup_at,
        face_descriptors: faceDescriptors,
    });

    // Start face detection if enabled and user hasn't checked in yet
    if (faceRecognitionStatus.value.enabled && faceRecognitionStatus.value.has_descriptors && !todayAttendance?.check_in_time) {
        startFaceDetection();
    }
});

onUnmounted(() => {
    if (timeInterval) {
        clearInterval(timeInterval);
    }
    stopFaceDetection();
});
</script>

<template>
    <Head :title="`${companyName} - Employee Portal`" />

    <!-- Mobile-only Layout -->
    <div class="mx-auto min-h-screen max-w-[480px] bg-background">
        <!-- Header -->
        <div class="sticky top-0 z-50 border-b border-border bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60">
            <div class="flex items-center justify-between p-4">
                <div class="flex items-center gap-3">
                    <div class="overflow-hidden rounded-lg">
                        <img v-if="companyLogo" :src="companyLogo" :alt="companyName" class="h-12 w-12 object-contain" />
                        <User v-else class="h-4 w-4 text-primary-foreground" />
                    </div>
                    <div>
                        <h1 class="text-lg font-semibold">{{ companyName }}</h1>
                        <p class="text-sm text-muted-foreground">{{ user?.name || 'Guest' }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <!-- Admin Dashboard Button - Only show for admin -->
                    <Link
                        v-if="user?.is_admin"
                        href="/admin/dashboard"
                        class="rounded-md bg-primary p-2 text-primary-foreground transition-colors hover:bg-primary/90"
                        title="Admin Dashboard"
                    >
                        <UserCircle class="h-4 w-4" />
                    </Link>
                    <Link
                        href="/logout"
                        method="post"
                        as="button"
                        class="rounded-md bg-secondary p-2 text-secondary-foreground transition-colors hover:bg-secondary/80"
                        title="Logout"
                    >
                        <LogOut class="h-4 w-4" />
                    </Link>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="px-4 py-6 pb-20">
            <!-- Today's Attendance - Moved to top -->
            <div class="mb-8 rounded-lg border bg-card p-6">
                <!-- Clean horizontal layout for attendance info -->
                <div class="rounded-lg border bg-gradient-to-r from-card to-card/80 p-4">
                    <div class="grid grid-cols-3 gap-4">
                        <!-- Check In -->
                        <div class="text-center">
                            <div class="mb-2 flex items-center justify-center gap-2">
                                <div class="flex h-6 w-6 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/30">
                                    <div class="h-2 w-2 rounded-full bg-green-600 dark:bg-green-400"></div>
                                </div>
                                <span class="text-xs font-semibold text-green-700 dark:text-green-400">MASUK</span>
                            </div>
                            <p class="text-lg font-bold text-foreground">
                                {{ formatTime(todayAttendance?.check_in_time) }}
                            </p>
                        </div>

                        <!-- Check Out -->
                        <div class="text-center">
                            <div class="mb-2 flex items-center justify-center gap-2">
                                <div class="flex h-6 w-6 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30">
                                    <div class="h-2 w-2 rounded-full bg-red-600 dark:bg-red-400"></div>
                                </div>
                                <span class="text-xs font-semibold text-red-700 dark:text-red-400">KELUAR</span>
                            </div>
                            <p class="text-lg font-bold text-foreground">
                                {{ formatTime(todayAttendance?.check_out_time) }}
                            </p>
                        </div>

                        <!-- Duration -->
                        <div class="text-center">
                            <div class="mb-2 flex items-center justify-center gap-2">
                                <div class="flex h-6 w-6 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900/30">
                                    <div class="h-2 w-2 rounded-full bg-blue-600 dark:bg-blue-400"></div>
                                </div>
                                <span class="text-xs font-semibold text-blue-700 dark:text-blue-400">DURASI</span>
                            </div>
                            <p class="text-lg font-bold text-foreground">
                                {{ formatDuration(todayAttendance?.work_duration) }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Face Recognition Warning -->
                <div
                    v-if="!faceRecognitionStatus.enabled || !faceRecognitionStatus.has_descriptors"
                    class="mt-4 rounded-lg border border-orange-200 bg-orange-50 p-4 dark:border-orange-800 dark:bg-orange-900/20"
                >
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0">
                            <div class="flex h-10 w-10 items-center justify-center rounded-md bg-orange-100 dark:bg-orange-900/30">
                                <Eye class="h-5 w-5 text-orange-600 dark:text-orange-400" />
                            </div>
                        </div>
                        <div class="flex-1">
                            <h4 class="mb-1 text-sm font-medium text-orange-800 dark:text-orange-200">Face Recognition Belum Setup</h4>
                            <p class="mb-3 text-sm text-orange-700 dark:text-orange-300">
                                Setup pengenalan wajah untuk keamanan tambahan saat absensi.
                            </p>
                            <Link
                                href="/user/profile"
                                class="inline-flex items-center gap-2 rounded-md border border-orange-300 bg-orange-100 px-3 py-2 text-sm font-medium text-orange-700 transition-colors hover:bg-orange-200 dark:border-orange-700 dark:bg-orange-900/50 dark:text-orange-300 dark:hover:bg-orange-900/70"
                            >
                                <UserCircle class="h-4 w-4" />
                                Setup Sekarang
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Real-time Face Recognition -->
                <div
                    v-if="faceRecognitionStatus.enabled && faceRecognitionStatus.has_descriptors && !todayAttendance?.check_in_time"
                    class="mt-4 flex justify-center"
                >
                    <div class="relative">
                        <!-- Face Captured State -->
                        <div v-if="isFaceCaptured" class="relative h-48 w-48 overflow-hidden rounded-full border-4 border-green-500 bg-gray-900">
                            <!-- Captured Face Image (Full Circle) -->
                            <img
                                v-if="capturedFaceData?.imageDataUrl"
                                :src="capturedFaceData.imageDataUrl"
                                alt="Captured Face"
                                class="h-full w-full object-cover"
                            />

                            <!-- Success Overlay -->
                            <div class="absolute inset-0 flex items-center justify-center bg-green-500/20">
                                <div class="rounded-full bg-green-500 p-2 shadow-lg">
                                    <CheckCircle class="h-6 w-6 text-white" />
                                </div>
                            </div>

                            <!-- Confidence Badge -->
                            <div class="absolute top-4 left-1/2 -translate-x-1/2 -translate-y-1/2 transform rounded-full px-3 py-1 backdrop-blur-sm">
                                <span class="text-base font-bold text-white">{{ capturedFaceData?.confidence }}%</span>
                            </div>

                            <!-- Reset Button -->
                            <div class="absolute bottom-3 left-1/2 -translate-x-1/2 transform">
                                <button
                                    @click="resetCapture"
                                    class="rounded-full px-3 py-1.5 text-xs font-medium text-white backdrop-blur-sm transition-colors hover:bg-black/80"
                                >
                                    Ulangi
                                </button>
                            </div>
                        </div>

                        <!-- Active Detection State -->
                        <div
                            v-else
                            class="relative h-48 w-48 overflow-hidden rounded-full border-4 bg-gray-900"
                            :class="faceDetectionActive ? (isFaceMatched ? 'border-green-500' : 'border-red-500') : 'border-gray-300'"
                        >
                            <!-- Video Element (Circular) -->
                            <video
                                ref="videoElement"
                                autoplay
                                muted
                                playsinline
                                class="h-full w-full scale-150 object-cover"
                                style="transform: scaleX(-1)"
                                v-show="faceDetectionActive"
                            />

                            <!-- Canvas for face detection overlay (Circular) -->
                            <canvas
                                ref="canvasElement"
                                width="320"
                                height="320"
                                class="absolute top-0 left-0 h-full w-full"
                                v-show="faceDetectionActive"
                            />

                            <!-- Scanning Animation Overlay -->
                            <div v-if="faceDetectionActive && !isCapturing" class="absolute inset-0 overflow-hidden rounded-full">
                                <!-- Vertical Scanning Line (like barcode scanner) -->
                                <div class="absolute inset-0">
                                    <div
                                        class="scan-line absolute right-0 left-0 h-0.5 bg-gradient-to-r from-transparent via-white to-transparent opacity-80 shadow-lg"
                                    ></div>
                                </div>

                                <!-- Pulsing Rings -->
                                <div class="absolute inset-1 animate-pulse rounded-full border-2 border-green-400/30"></div>
                                <div
                                    class="absolute inset-4 animate-ping rounded-full border border-green-400/20"
                                    style="animation-duration: 2s"
                                ></div>

                                <!-- Corner Markers -->
                                <div class="absolute top-4 left-4 h-4 w-4 rounded-tl-lg border-t-2 border-l-2 border-green-400 opacity-60"></div>
                                <div class="absolute top-4 right-4 h-4 w-4 rounded-tr-lg border-t-2 border-r-2 border-green-400 opacity-60"></div>
                                <div class="absolute bottom-4 left-4 h-4 w-4 rounded-bl-lg border-b-2 border-l-2 border-green-400 opacity-60"></div>
                                <div class="absolute right-4 bottom-4 h-4 w-4 rounded-br-lg border-r-2 border-b-2 border-green-400 opacity-60"></div>
                            </div>

                            <!-- Loading/Status Overlay -->
                            <div v-if="!faceDetectionActive" class="absolute inset-0 flex items-center justify-center rounded-full bg-gray-800/80">
                                <div class="text-center text-white">
                                    <Loader2 class="mx-auto mb-2 h-6 w-6 animate-spin" />
                                    <p class="text-xs">Memuat kamera...</p>
                                </div>
                            </div>

                            <!-- Face Match Status (Circular) -->
                            <div class="absolute bottom-0 left-1/2 mt-1 -translate-x-1/2 translate-y-full transform">
                                <div
                                    v-if="faceDetectionActive && !isCapturing"
                                    class="rounded-full bg-black/70 px-3 py-1 text-center text-xs whitespace-nowrap text-white"
                                >
                                    <span v-if="isFaceMatched" class="text-green-400">
                                        ✅ Terverifikasi ({{ Math.round(faceMatchConfidence) }}%)
                                    </span>
                                    <span v-else-if="faceMatchConfidence > 0" class="text-red-400">
                                        ❌ Gagal ({{ Math.round(faceMatchConfidence) }}%)
                                    </span>
                                    <span v-else class="text-yellow-400"> 🔍 Mencari wajah... </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="todayAttendance?.office_location" class="mt-4 flex items-center gap-2 rounded-md bg-muted p-3">
                    <MapPin class="h-4 w-4 text-muted-foreground" />
                    <span class="text-sm text-muted-foreground">
                        {{ todayAttendance.office_location?.name || 'Remote' }}
                    </span>
                </div>

                <!-- Face Match Confidence Display - Centered and Prominent -->
                <div
                    v-if="faceRecognitionStatus.enabled && faceRecognitionStatus.has_descriptors && faceMatchConfidence > 0"
                    class="mt-6 mb-6 flex justify-center"
                >
                    <div
                        class="w-full max-w-[300px] min-w-[200px] rounded-xl border border-gray-200 bg-white p-6 text-center shadow-lg dark:border-gray-700 dark:bg-gray-800"
                    >
                        <div class="mb-2 text-sm font-medium text-gray-600 dark:text-gray-400">Tingkat Kecocokan Wajah</div>
                        <div
                            class="mb-2 text-4xl font-bold"
                            :class="{
                                'text-green-600 dark:text-green-400': faceMatchConfidence >= 70,
                                'text-yellow-600 dark:text-yellow-400': faceMatchConfidence >= 40 && faceMatchConfidence < 70,
                                'text-red-600 dark:text-red-400': faceMatchConfidence < 40,
                            }"
                        >
                            {{ Math.round(faceMatchConfidence) }}%
                        </div>
                        <div
                            class="mb-3 text-sm font-medium"
                            :class="{
                                'text-green-600 dark:text-green-400': faceMatchConfidence >= 70,
                                'text-yellow-600 dark:text-yellow-400': faceMatchConfidence >= 40 && faceMatchConfidence < 70,
                                'text-red-600 dark:text-red-400': faceMatchConfidence < 40,
                            }"
                        >
                            {{ faceMatchConfidence >= 70 ? '✅ Sangat Cocok' : faceMatchConfidence >= 40 ? '⚠️ Cukup Cocok' : '❌ Kurang Cocok' }}
                        </div>
                        <!-- Progress bar -->
                        <div class="h-2 w-full rounded-full bg-gray-200 dark:bg-gray-700">
                            <div
                                class="h-2 rounded-full transition-all duration-300"
                                :class="{
                                    'bg-green-500': faceMatchConfidence >= 70,
                                    'bg-yellow-500': faceMatchConfidence >= 40 && faceMatchConfidence < 70,
                                    'bg-red-500': faceMatchConfidence < 40,
                                }"
                                :style="{ width: faceMatchConfidence + '%' }"
                            ></div>
                        </div>
                    </div>
                </div>

                <!-- Attendance Actions -->
                <div class="mt-6 grid grid-cols-2 gap-4">
                    <!-- Check In Button -->
                    <button
                        v-if="!todayAttendance?.check_in_time"
                        @click="handleCheckInClick"
                        :disabled="
                            isCheckingIn ||
                            (locationStatus === 'success' && !isInRange) ||
                            (faceRecognitionStatus.enabled && faceRecognitionStatus.has_descriptors && !isFaceCaptured) ||
                            !faceRecognitionStatus.enabled ||
                            !faceRecognitionStatus.has_descriptors
                        "
                        class="flex min-h-[48px] touch-manipulation items-center justify-center gap-2 rounded-lg px-3 py-3 font-medium text-white shadow-sm transition-colors disabled:cursor-not-allowed disabled:opacity-50"
                        :class="{
                            'bg-green-600 hover:bg-green-700 active:bg-green-800':
                                (locationStatus === 'idle' || locationStatus === 'error' || (locationStatus === 'success' && isInRange)) &&
                                faceRecognitionStatus.enabled &&
                                faceRecognitionStatus.has_descriptors &&
                                isFaceCaptured,
                            'bg-blue-600 hover:bg-blue-700 active:bg-blue-800': locationStatus === 'loading',
                            'bg-gray-500':
                                (locationStatus === 'success' && !isInRange) ||
                                (faceRecognitionStatus.enabled && faceRecognitionStatus.has_descriptors && !isFaceCaptured) ||
                                !faceRecognitionStatus.enabled ||
                                !faceRecognitionStatus.has_descriptors,
                            'bg-orange-500 hover:bg-orange-600 active:bg-orange-700':
                                faceRecognitionStatus.enabled && faceRecognitionStatus.has_descriptors && faceMatchConfidence > 0 && !isFaceCaptured,
                        }"
                    >
                        <Loader2 v-if="locationStatus === 'loading' || isCheckingIn" class="h-4 w-4 animate-spin" />
                        <CheckCircle v-else-if="isFaceCaptured && isInRange" class="h-4 w-4" />
                        <Eye v-else-if="faceRecognitionStatus.enabled && faceRecognitionStatus.has_descriptors && !isFaceCaptured" class="h-4 w-4" />
                        <Clock v-else class="h-4 w-4" />
                        <span class="text-xs font-medium">
                            {{
                                isCheckingIn
                                    ? 'Sedang Check In...'
                                    : locationStatus === 'loading'
                                      ? 'Mencari Lokasi...'
                                      : locationStatus === 'success' && !isInRange
                                        ? 'Di Luar Area'
                                        : !faceRecognitionStatus.enabled || !faceRecognitionStatus.has_descriptors
                                          ? 'Tidak Tersedia'
                                          : faceRecognitionStatus.enabled && faceRecognitionStatus.has_descriptors && !isFaceCaptured
                                            ? 'Tunggu Verifikasi'
                                            : isFaceCaptured && isInRange
                                              ? 'Siap Check In!'
                                              : locationStatus === 'error'
                                                ? 'Coba Lagi'
                                                : 'Check In'
                            }}
                        </span>
                    </button>
                    <div
                        v-else
                        class="flex min-h-[48px] items-center justify-center gap-2 rounded-lg bg-green-100 px-3 py-3 font-medium text-green-800 dark:bg-green-900/20 dark:text-green-200"
                    >
                        <CheckCircle class="h-4 w-4" />
                        <span class="text-xs font-medium">Sudah Masuk</span>
                    </div>

                    <!-- Check Out Button -->
                    <button
                        v-if="todayAttendance?.check_in_time && !todayAttendance?.check_out_time"
                        @click="handleCheckOutClick"
                        :disabled="isCheckingOut"
                        class="flex min-h-[48px] touch-manipulation items-center justify-center gap-2 rounded-lg px-3 py-3 font-medium text-white shadow-sm transition-colors disabled:cursor-not-allowed disabled:opacity-50"
                        :class="{
                            'bg-red-600 hover:bg-red-700 active:bg-red-800':
                                locationStatus === 'idle' || locationStatus === 'error' || locationStatus === 'success',
                            'bg-blue-600 hover:bg-blue-700 active:bg-blue-800': locationStatus === 'loading',
                        }"
                    >
                        <Loader2 v-if="locationStatus === 'loading' || isCheckingOut" class="h-4 w-4 animate-spin" />
                        <Clock v-else class="h-4 w-4" />
                        <span class="text-xs font-medium">
                            {{
                                isCheckingOut
                                    ? 'Sedang Check Out...'
                                    : locationStatus === 'loading'
                                      ? 'Mencari Lokasi...'
                                      : locationStatus === 'error'
                                        ? 'Coba Lagi'
                                        : 'Check Out'
                            }}
                        </span>
                    </button>
                    <div
                        v-else-if="todayAttendance?.check_out_time"
                        class="flex min-h-[48px] items-center justify-center gap-2 rounded-lg bg-red-100 px-3 py-3 font-medium text-red-800 dark:bg-red-900/20 dark:text-red-200"
                    >
                        <CheckCircle class="h-4 w-4" />
                        <span class="text-xs font-medium">Sudah Keluar</span>
                    </div>
                    <div
                        v-else
                        class="flex min-h-[48px] items-center justify-center gap-2 rounded-lg bg-muted px-3 py-3 font-medium text-muted-foreground"
                    >
                        <Clock class="h-4 w-4" />
                        <span class="text-xs font-medium">Belum Waktunya</span>
                    </div>
                </div>
            </div>

            <!-- Welcome Section -->
            <div class="mb-8 rounded-xl border bg-gradient-to-br from-card via-card to-card/80 p-6 shadow-sm">
                <div class="space-y-3 text-center">
                    <!-- Avatar Section -->
                    <div class="relative mx-auto">
                        <div
                            class="mx-auto flex h-14 w-14 items-center justify-center overflow-hidden rounded-full bg-gradient-to-br from-primary/20 to-primary/10 ring-4 ring-primary/5"
                        >
                            <img v-if="user?.avatar" :src="user.avatar" :alt="user.name" class="h-full w-full object-cover" />
                            <UserCircle v-else class="h-7 w-7 text-primary" />
                        </div>
                        <div
                            class="absolute -right-1 -bottom-1 flex h-5 w-5 items-center justify-center rounded-full border-2 border-background bg-green-500"
                        >
                            <div class="h-1.5 w-1.5 animate-pulse rounded-full bg-white"></div>
                        </div>
                    </div>

                    <!-- Greeting -->
                    <div class="space-y-2">
                        <h2 class="bg-gradient-to-r from-foreground to-foreground/80 bg-clip-text text-xl font-bold">
                            Halo, {{ user?.name?.split(' ')[0] || 'Guest' }}! 👋
                        </h2>
                        <div class="flex flex-wrap items-center justify-center gap-2 text-muted-foreground">
                            <div class="h-1.5 w-1.5 rounded-full bg-primary"></div>
                            <p class="text-sm font-medium">
                                {{ user?.department?.name || 'No Department' }}
                            </p>
                            <div class="h-1 w-1 rounded-full bg-muted-foreground/50"></div>
                            <p class="text-sm">
                                {{ user?.position?.title || 'No Position' }}
                            </p>
                        </div>
                    </div>

                    <!-- Date & Time Display -->
                    <div class="rounded-lg border border-border/50 bg-gradient-to-r from-muted/50 to-muted/30 p-3">
                        <div class="mb-2 flex items-center justify-center gap-2">
                            <Calendar class="h-4 w-4 text-primary" />
                            <p class="text-center text-xs font-semibold text-foreground">
                                {{ currentDate }}
                            </p>
                        </div>
                        <div class="flex items-center justify-center gap-2">
                            <Clock class="h-4 w-4 text-muted-foreground" />
                            <p class="font-mono text-lg font-bold tracking-wider text-primary">
                                {{ currentTime }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Announcements Section -->
            <div v-if="announcements && announcements.length > 0" class="mb-8">
                <div class="mb-6 flex items-center gap-3">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-500/10 ring-1 ring-blue-500/20">
                        <Calendar class="h-4 w-4 text-blue-600 dark:text-blue-400" />
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Pengumuman Terbaru</h2>
                    </div>
                </div>
                <AnnouncementCarousel
                    :announcements="announcements"
                    :auto-play="true"
                    :auto-play-interval="6000"
                    @announcement-click="handleAnnouncementClick"
                />
            </div>

            <!-- Monthly Stats -->
            <div class="rounded-lg border bg-card p-6">
                <h3 class="mb-6 flex items-center gap-2 text-lg font-semibold">
                    <BarChart3 class="h-4 w-4" />
                    Statistik Bulan Ini
                </h3>

                <div class="grid grid-cols-2 gap-4">
                    <div class="rounded-md border bg-card p-4 text-center">
                        <div class="mx-auto mb-2 flex h-8 w-8 items-center justify-center rounded-md bg-muted">
                            <Calendar class="h-4 w-4 text-muted-foreground" />
                        </div>
                        <p class="text-xl font-bold">{{ stats?.monthly_attendance_days || 0 }}</p>
                        <p class="text-xs font-medium text-muted-foreground">Hari Masuk</p>
                    </div>

                    <div class="rounded-md border bg-card p-3 text-center">
                        <div class="mx-auto mb-2 flex h-8 w-8 items-center justify-center rounded-md bg-muted">
                            <Clock class="h-4 w-4 text-muted-foreground" />
                        </div>
                        <p class="text-xl font-bold">{{ stats?.monthly_late_days || 0 }}</p>
                        <p class="text-xs font-medium text-muted-foreground">Hari Terlambat</p>
                    </div>

                    <div class="rounded-md border bg-card p-3 text-center">
                        <div class="mx-auto mb-2 flex h-8 w-8 items-center justify-center rounded-md bg-muted">
                            <Clock class="h-4 w-4 text-muted-foreground" />
                        </div>
                        <p class="text-xl font-bold">{{ stats?.monthly_work_hours || 0 }}</p>
                        <p class="text-xs font-medium text-muted-foreground">Jam Kerja</p>
                    </div>

                    <div class="rounded-md border bg-card p-3 text-center">
                        <div class="mx-auto mb-2 flex h-8 w-8 items-center justify-center rounded-md bg-muted">
                            <Calendar class="h-4 w-4 text-muted-foreground" />
                        </div>
                        <p class="text-xl font-bold">{{ stats?.leave_balance || 0 }}</p>
                        <p class="text-xs font-medium text-muted-foreground">Sisa Cuti</p>
                    </div>
                </div>
            </div>
        </div>

        <BottomNavigation current-route="/home" />

        <!-- Alert Modal -->
        <Dialog v-model:open="alertModal.isOpen">
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>{{ alertModal.title }}</DialogTitle>
                    <DialogDescription>
                        {{ alertModal.message }}
                    </DialogDescription>
                </DialogHeader>
                <div class="flex justify-end">
                    <Button @click="alertModal.isOpen = false" :variant="alertModal.variant === 'destructive' ? 'destructive' : 'default'">
                        OK
                    </Button>
                </div>
            </DialogContent>
        </Dialog>

        <!-- Announcement Detail Modal -->
        <AnnouncementModal :announcement="selectedAnnouncement" :open="showAnnouncementModal" @close="closeAnnouncementModal" />
    </div>
</template>

<style scoped>
@keyframes scanLine {
    0% {
        top: 0;
    }
    50% {
        top: calc(100% - 4px);
    }
    100% {
        top: 0;
    }
}

.scan-line {
    animation: scanLine 2s ease-in-out infinite;
}
</style>
