<script setup lang="ts">
import { ref, onMounted, onUnmounted, nextTick } from 'vue';
import { Camera, X, Check, AlertCircle, Loader2 } from 'lucide-vue-next';
import * as faceapi from 'face-api.js';

interface Props {
    mode?: 'setup' | 'verification';
    storedDescriptors?: number[][];
    onCapture?: (descriptors: number[][]) => void;
    onVerification?: (confidence: number) => void;
    onError?: (error: string) => void;
}

const props = withDefaults(defineProps<Props>(), {
    mode: 'setup',
    storedDescriptors: () => [],
    onCapture: () => {},
    onVerification: () => {},
    onError: () => {},
});

const emit = defineEmits<{
    capture: [descriptors: number[][]];
    verification: [confidence: number];
    error: [error: string];
    close: [];
}>();

// Refs
const videoRef = ref<HTMLVideoElement>();
const canvasRef = ref<HTMLCanvasElement>();
const stream = ref<MediaStream | null>(null);
const isLoading = ref(true);
const isProcessing = ref(false);
const faceDetected = ref(false);
const errorMessage = ref('');
const modelsLoaded = ref(false);
const loadingStatus = ref('Memulai...');

// Face detection state
const capturedDescriptors = ref<number[][]>([]);
const detectionInterval = ref<number | null>(null);
const verificationConfidence = ref<number | null>(null);
const verificationStatus = ref<'pending' | 'success' | 'failed' | null>(null);

const loadModels = async () => {
    try {
        console.log('Starting to load face-api.js models...');

        // Try local models first
        let MODEL_URL = '/models';

        try {
            // Test if local models are accessible with cache busting
            const testUrl = `${MODEL_URL}/tiny_face_detector_model-weights_manifest.json?t=${Date.now()}`;
            console.log('Testing model access:', testUrl);

            const testResponse = await fetch(testUrl);
            console.log('Response status:', testResponse.status);
            console.log('Response headers:', Object.fromEntries(testResponse.headers));

            if (!testResponse.ok) {
                throw new Error(`Local models not accessible: ${testResponse.status} ${testResponse.statusText}`);
            }

            const testContent = await testResponse.text();
            console.log('Model manifest preview:', testContent.substring(0, 200));
            console.log('Local model files are accessible');
        } catch (localError) {
            console.warn('Local models failed, trying CDN fallback:', localError);
            // Fallback to CDN
            MODEL_URL = 'https://raw.githubusercontent.com/justadudewhohacks/face-api.js/master/weights';
        }

        // Load models one by one with better error handling and timeout
        const loadWithTimeout = async (loadFunction: () => Promise<void>, name: string, timeoutMs = 15000) => {
            console.log(`Loading ${name}...`);
            loadingStatus.value = `Memuat ${name}...`;
            return Promise.race([
                loadFunction(),
                new Promise((_, reject) =>
                    setTimeout(() => reject(new Error(`${name} loading timeout after ${timeoutMs}ms`)), timeoutMs)
                )
            ]);
        };

        await loadWithTimeout(
            () => faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL),
            'tiny face detector'
        );

        await loadWithTimeout(
            () => faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
            'face landmarks'
        );

        await loadWithTimeout(
            () => faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL),
            'face recognition'
        );

        modelsLoaded.value = true;
        console.log('All Face-api.js models loaded successfully from:', MODEL_URL);
    } catch (error) {
        console.error('Detailed error loading face-api.js models:', error);
        const errorMsg = error instanceof Error ? error.message : 'Unknown error';
        errorMessage.value = `Gagal memuat model AI: ${errorMsg}. Coba refresh halaman atau periksa koneksi internet.`;
        emit('error', errorMessage.value);
    }
};

const startCamera = async () => {
    try {
        console.log('Starting camera...');

        // Check if getUserMedia is supported
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            throw new Error('getUserMedia tidak didukung di browser ini');
        }

        // Mobile-optimized constraints
        const isMobile = /Android|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);

        const constraints = {
            video: {
                width: { ideal: isMobile ? 320 : 640 },
                height: { ideal: isMobile ? 240 : 480 },
                facingMode: 'user',
                frameRate: { ideal: isMobile ? 15 : 30 }
            }
        };

        console.log('Requesting camera access...');
        stream.value = await navigator.mediaDevices.getUserMedia(constraints);
        console.log('Camera access granted');

        if (videoRef.value) {
            videoRef.value.srcObject = stream.value;
            await videoRef.value.play();
            console.log('Video playing');

            // Wait for video to be ready
            videoRef.value.addEventListener('loadedmetadata', () => {
                console.log('Video metadata loaded, video ready');
                // Note: Don't set isLoading = false here yet, wait for models
            });

            // Also set video visibility when playing
            videoRef.value.addEventListener('playing', () => {
                console.log('Video is playing');
                if (modelsLoaded.value) {
                    isLoading.value = false;
                    startFaceDetection();
                }
            });
        }
    } catch (error) {
        console.error('Camera access error:', error);
        const errorMsg = error instanceof Error ? error.message : 'Unknown camera error';
        errorMessage.value = `Tidak dapat mengakses kamera: ${errorMsg}. Pastikan izin kamera telah diberikan.`;
        emit('error', errorMessage.value);
        isLoading.value = false;
    }
};

const startFaceDetection = () => {
    if (!videoRef.value || !modelsLoaded.value) return;

    const video = videoRef.value;
    const canvas = canvasRef.value;

    console.log('Starting face detection, video dimensions:', {
        videoWidth: video.videoWidth,
        videoHeight: video.videoHeight,
        clientWidth: video.clientWidth,
        clientHeight: video.clientHeight,
        canvasExists: !!canvas
    });

    // Set canvas dimensions to match video
    if (canvas && video.videoWidth > 0 && video.videoHeight > 0) {
        const displaySize = { width: video.videoWidth, height: video.videoHeight };
        faceapi.matchDimensions(canvas, displaySize);
        console.log('Canvas dimensions set to:', displaySize);
    }

    // Mobile-optimized detection interval
    const isMobile = /Android|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
    const detectionDelay = isMobile ? 500 : 100; // 500ms for mobile, 100ms for desktop

    detectionInterval.value = window.setInterval(async () => {
        if (!videoRef.value || isProcessing.value) return;

        try {
            // Mobile-optimized detection options
            const detectionOptions = new faceapi.TinyFaceDetectorOptions({
                inputSize: isMobile ? 128 : 416, // Smaller input size for mobile
                scoreThreshold: isMobile ? 0.3 : 0.5 // Lower threshold for mobile
            });

            const detection = await faceapi
                .detectSingleFace(videoRef.value, detectionOptions)
                .withFaceLandmarks()
                .withFaceDescriptor();

            faceDetected.value = !!detection;

            // Draw detection box if canvas is available
            if (canvasRef.value && detection) {
                const canvas = canvasRef.value;
                const displaySize = {
                    width: videoRef.value.videoWidth,
                    height: videoRef.value.videoHeight
                };

                faceapi.matchDimensions(canvas, displaySize);
                const ctx = canvas.getContext('2d');
                if (ctx) {
                    ctx.clearRect(0, 0, canvas.width, canvas.height);
                    // Bounding boxes and landmarks hidden for cleaner UI
                }
            }
        } catch (error) {
            console.error('Face detection error:', error);
        }
    }, detectionDelay);
};

const capturePhoto = async () => {
    if (!videoRef.value || !faceDetected.value || isProcessing.value) return;

    isProcessing.value = true;

    try {
        const detection = await faceapi
            .detectSingleFace(videoRef.value, new faceapi.TinyFaceDetectorOptions())
            .withFaceLandmarks()
            .withFaceDescriptor();

        if (!detection) {
            throw new Error('Wajah tidak terdeteksi. Pastikan wajah Anda terlihat jelas di kamera.');
        }

        if (props.mode === 'setup') {
            // For setup mode, collect multiple descriptors
            capturedDescriptors.value.push(Array.from(detection.descriptor));

            if (capturedDescriptors.value.length >= 3) {
                emit('capture', capturedDescriptors.value);
                props.onCapture(capturedDescriptors.value);
            }
        } else if (props.mode === 'verification') {
            // For verification mode, compare with stored descriptors
            if (props.storedDescriptors.length === 0) {
                throw new Error('Data wajah tidak ditemukan. Silakan setup face recognition terlebih dahulu.');
            }

            // Compare with stored descriptors
            let bestMatch = 0;
            for (const storedDescriptor of props.storedDescriptors) {
                const distance = faceapi.euclideanDistance(detection.descriptor, new Float32Array(storedDescriptor));
                const similarity = Math.max(0, (1 - distance) * 100);
                bestMatch = Math.max(bestMatch, similarity);
            }

            // Store confidence for display
            verificationConfidence.value = Math.round(bestMatch);

            // Determine verification status based on confidence
            if (bestMatch >= 80) {
                verificationStatus.value = 'success';
            } else if (bestMatch >= 40) {
                verificationStatus.value = 'success'; // Still success as per new logic - face detected
            } else {
                verificationStatus.value = 'success'; // Still success - any face detection is acceptable
            }

            emit('verification', bestMatch);
            props.onVerification(bestMatch);
        }
    } catch (error) {
        const errorMsg = error instanceof Error ? error.message : 'Terjadi kesalahan saat memproses wajah.';
        emit('error', errorMsg);
        props.onError(errorMsg);
    } finally {
        isProcessing.value = false;
    }
};

const stopCamera = () => {
    if (stream.value) {
        stream.value.getTracks().forEach(track => track.stop());
        stream.value = null;
    }

    if (detectionInterval.value) {
        clearInterval(detectionInterval.value);
        detectionInterval.value = null;
    }
};

const closeCapture = () => {
    stopCamera();
    emit('close');
};

// Helper function to get confidence level description
const getConfidenceLabel = (confidence: number) => {
    if (confidence >= 80) return 'Kecocokan Tinggi';
    if (confidence >= 60) return 'Kecocokan Sedang';
    if (confidence >= 40) return 'Kecocokan Rendah';
    return 'Kecocokan Sangat Rendah';
};

const getConfidenceColor = (confidence: number) => {
    if (confidence >= 80) return 'text-green-700 bg-green-100 dark:text-green-400 dark:bg-green-900/20';
    if (confidence >= 60) return 'text-yellow-700 bg-yellow-100 dark:text-yellow-400 dark:bg-yellow-900/20';
    if (confidence >= 40) return 'text-orange-700 bg-orange-100 dark:text-orange-400 dark:bg-orange-900/20';
    return 'text-red-700 bg-red-100 dark:text-red-400 dark:bg-red-900/20';
};

onMounted(async () => {
    console.log('FaceCapture component mounted');

    // Check if face-api.js is available
    if (typeof faceapi === 'undefined') {
        console.error('face-api.js is not loaded!');
        errorMessage.value = 'Library AI tidak dimuat. Silakan refresh halaman.';
        emit('error', errorMessage.value);
        return;
    }

    console.log('face-api.js is available');

    // For testing - start camera first to check permissions
    console.log('Testing camera access first...');
    loadingStatus.value = 'Meminta akses kamera...';

    try {
        await startCamera();
        console.log('Camera started successfully, now loading models...');
        loadingStatus.value = 'Kamera aktif, memuat AI models...';

        // Load models after camera is working
        await loadModels();

        if (modelsLoaded.value) {
            console.log('Models loaded successfully, restarting face detection...');
            loadingStatus.value = 'Siap untuk deteksi wajah';
            isLoading.value = false; // Show video now
            startFaceDetection();
        } else {
            console.error('Models failed to load');
            loadingStatus.value = 'Gagal memuat models';
        }
    } catch (error) {
        console.error('Error in component mounting:', error);
        loadingStatus.value = 'Error: ' + (error instanceof Error ? error.message : 'Unknown error');
    }
});

onUnmounted(() => {
    stopCamera();
});
</script>

<template>
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm">
        <div class="relative w-full max-w-lg mx-4 bg-white rounded-xl shadow-2xl dark:bg-gray-900">
            <!-- Header -->
            <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900/20">
                        <Camera class="h-5 w-5 text-blue-600 dark:text-blue-400" />
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            {{ mode === 'setup' ? 'Setup Pengenalan Wajah' : 'Verifikasi Wajah' }}
                        </h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            {{ mode === 'setup' ? 'Ambil 3 foto wajah untuk setup' : 'Posisikan wajah di depan kamera' }}
                        </p>
                    </div>
                </div>
                <button
                    @click="closeCapture"
                    class="rounded-lg p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800 dark:hover:text-gray-300"
                >
                    <X class="h-5 w-5" />
                </button>
            </div>

            <!-- Camera View -->
            <div class="p-4">
                <div class="flex justify-center mb-6">
                    <div class="relative w-56 h-56 sm:w-72 sm:h-72 md:w-80 md:h-80 aspect-square rounded-full overflow-hidden bg-gray-900 border-4"
                         :class="[
                             faceDetected ? 'border-green-500' : 'border-gray-300'
                         ]">
                        <!-- Loading State -->
                        <div v-if="isLoading" class="absolute inset-0 flex items-center justify-center bg-gray-900/50">
                            <div class="text-center text-white bg-gray-800/80 p-6 rounded-lg">
                                <Loader2 class="mx-auto h-8 w-8 animate-spin text-blue-400" />
                                <p class="mt-3 text-lg font-medium">{{ loadingStatus }}</p>
                                <p class="mt-1 text-sm text-gray-300">Mohon tunggu...</p>
                            </div>
                        </div>

                        <!-- Video Stream -->
                        <video
                            ref="videoRef"
                            autoplay
                            muted
                            playsinline
                            class="w-full h-full object-cover scale-150"
                            :class="{ 'opacity-0': isLoading, 'opacity-100': !isLoading }"
                        ></video>

                        <!-- Detection Overlay -->
                        <canvas
                            ref="canvasRef"
                            class="absolute inset-0 h-full w-full pointer-events-none"
                            :class="{ 'opacity-0': isLoading, 'opacity-100': !isLoading }"
                        ></canvas>

                        <!-- Scanning Animation Overlay -->
                        <div v-if="!isLoading && faceDetected" class="absolute inset-0 rounded-full overflow-hidden">
                            <!-- Vertical Scanning Line -->
                            <div class="absolute inset-0">
                                <div class="absolute left-0 right-0 h-1 bg-gradient-to-r from-transparent via-white via-white to-transparent opacity-80 shadow-lg animate-scan"></div>
                            </div>

                            <!-- Pulsing Rings -->
                            <div class="absolute inset-2 rounded-full border-2 border-green-400/30 animate-pulse"></div>
                            <div class="absolute inset-6 rounded-full border border-green-400/20 animate-ping" style="animation-duration: 2s;"></div>

                            <!-- Corner Markers -->
                            <div class="absolute top-8 left-8 w-6 h-6 border-l-2 border-t-2 border-green-400 rounded-tl-lg opacity-60"></div>
                            <div class="absolute top-8 right-8 w-6 h-6 border-r-2 border-t-2 border-green-400 rounded-tr-lg opacity-60"></div>
                            <div class="absolute bottom-8 left-8 w-6 h-6 border-l-2 border-b-2 border-green-400 rounded-bl-lg opacity-60"></div>
                            <div class="absolute bottom-8 right-8 w-6 h-6 border-r-2 border-b-2 border-green-400 rounded-br-lg opacity-60"></div>
                        </div>

                        <!-- Face Detection Indicator - 30px from top, centered horizontally -->
                        <div class="absolute top-8 left-1/2 transform -translate-x-1/2 pointer-events-none">
                            <div class="text-center space-y-2">
                                <div
                                    class="inline-flex items-center gap-2 rounded-full px-4 py-2 text-sm font-medium backdrop-blur-sm"
                                    :class="{
                                        'bg-green-100/80 text-green-800 dark:bg-green-900/40 dark:text-green-300': faceDetected,
                                        'bg-red-100/80 text-red-800 dark:bg-red-900/40 dark:text-red-300': !faceDetected && !isLoading,
                                        'bg-gray-100/80 text-gray-800 dark:bg-gray-900/40 dark:text-gray-300': isLoading
                                    }"
                                >
                                    <div
                                        class="h-2 w-2 rounded-full"
                                        :class="{
                                            'bg-green-500 animate-pulse': faceDetected,
                                            'bg-red-500': !faceDetected && !isLoading,
                                            'bg-gray-500 animate-spin': isLoading
                                        }"
                                    ></div>
                                    {{ faceDetected ? 'Terdeteksi' : isLoading ? 'Loading...' : 'Cari Wajah...' }}
                                </div>

                                <!-- Confidence Score Display -->
                                <div v-if="mode === 'verification' && verificationConfidence !== null"
                                     class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-xs font-medium transition-all duration-300 backdrop-blur-sm"
                                     :class="getConfidenceColor(verificationConfidence)">
                                    <Check v-if="verificationStatus === 'success'" class="h-3 w-3" />
                                    <AlertCircle v-else class="h-3 w-3" />
                                    <span>{{ verificationConfidence }}% - {{ getConfidenceLabel(verificationConfidence) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Setup Progress - 30px from bottom, centered horizontally -->
                        <div v-if="mode === 'setup' && capturedDescriptors.length > 0" class="absolute bottom-8 left-1/2 transform -translate-x-1/2 pointer-events-none">
                            <div class="flex items-center gap-2 rounded-full bg-blue-100/80 backdrop-blur-sm px-4 py-2 text-sm font-medium text-blue-800 dark:bg-blue-900/40 dark:text-blue-300">
                                <Check class="h-4 w-4" />
                                {{ capturedDescriptors.length }}/3 Foto
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Photo Guidance for Setup Mode -->
                <div v-if="mode === 'setup'" class="mt-4 text-center">
                    <div class="inline-flex items-center gap-2 rounded-full bg-blue-50/80 backdrop-blur-sm px-4 py-2 text-sm font-medium text-blue-700 dark:bg-blue-900/20 dark:text-blue-300">
                        <span v-if="capturedDescriptors.length === 0">📷 Foto 1: Hadap lurus ke depan</span>
                        <span v-else-if="capturedDescriptors.length === 1">📷 Foto 2: Putar kepala ke kiri sedikit</span>
                        <span v-else-if="capturedDescriptors.length === 2">📷 Foto 3: Putar kepala ke kanan sedikit</span>
                        <span v-else>✅ Semua foto selesai!</span>
                    </div>
                </div>

                <!-- Capture Button -->
                <div class="flex justify-center mt-4">
                    <button
                        @click="capturePhoto"
                        :disabled="!faceDetected || isProcessing || isLoading"
                        class="flex items-center gap-2 rounded-lg px-6 py-3 font-medium text-white transition-colors disabled:cursor-not-allowed disabled:opacity-50"
                        :class="{
                            'bg-blue-600 hover:bg-blue-700': faceDetected && !isProcessing,
                            'bg-gray-400': !faceDetected || isProcessing || isLoading
                        }"
                    >
                        <Loader2 v-if="isProcessing" class="h-4 w-4 animate-spin" />
                        <Check v-else-if="mode === 'verification' && verificationConfidence !== null" class="h-4 w-4" />
                        <Camera v-else class="h-4 w-4" />
                        {{ isProcessing ? 'Memproses...' :
                           mode === 'verification' && verificationConfidence !== null ? 'Verifikasi Ulang' :
                           mode === 'setup' ? 'Ambil Foto' : 'Verifikasi Wajah' }}
                    </button>
                </div>

                <!-- Instructions -->
                <div class="mt-4 space-y-2">
                    <div class="text-sm text-gray-600 dark:text-gray-400">
                        <div class="mb-2 font-medium">Petunjuk:</div>
                        <ul class="space-y-1 text-xs">
                            <li>• Pastikan wajah terlihat jelas dan terang</li>
                            <li>• Posisikan wajah di tengah kamera</li>
                            <li>• Jangan menggunakan masker atau kacamata gelap</li>
                            <li v-if="mode === 'setup'">• Ambil foto dari sudut yang berbeda</li>
                            <li v-if="mode === 'verification'">• Anda akan melihat persentase kecocokan setelah verifikasi</li>
                            <li v-if="mode === 'verification'">• Semua wajah yang terdeteksi akan diterima untuk absensi</li>
                        </ul>
                    </div>
                </div>

                <!-- Error Message -->
                <div v-if="errorMessage" class="mt-4 flex items-center gap-2 rounded-lg bg-red-50 p-3 text-sm text-red-800 dark:bg-red-900/20 dark:text-red-400">
                    <AlertCircle class="h-4 w-4 flex-shrink-0" />
                    {{ errorMessage }}
                </div>

                <!-- Verification Results -->
                <div v-if="mode === 'verification' && verificationConfidence !== null" class="mt-4">
                    <div class="rounded-lg border p-4 bg-green-50 border-green-200 dark:bg-green-900/10 dark:border-green-800">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="flex items-center gap-2">
                                    <Check class="h-5 w-5 text-green-600 dark:text-green-400" />
                                    <span class="font-medium text-green-900 dark:text-green-100">Verifikasi Berhasil</span>
                                </div>
                                <p class="text-sm mt-1 text-green-800 dark:text-green-200">
                                    Tingkat kecocokan: <span class="font-semibold">{{ verificationConfidence }}%</span>
                                    <span class="ml-1">({{ getConfidenceLabel(verificationConfidence) }})</span>
                                </p>
                            </div>
                        </div>
                        <div class="mt-2 text-xs text-green-600 dark:text-green-400">
                            Wajah terdeteksi dan absensi dapat dilanjutkan.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
@keyframes scan {
    0% { top: 0%; }
    50% { top: 100%; }
    100% { top: 0%; }
}

.animate-scan {
    animation: scan 2s ease-in-out infinite;
}
</style>