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

// Face detection state
const capturedDescriptors = ref<number[][]>([]);
const detectionInterval = ref<number | null>(null);

const loadModels = async () => {
    try {
        const MODEL_URL = '/models';

        await Promise.all([
            faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL),
            faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL),
            faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL),
        ]);

        modelsLoaded.value = true;
        console.log('Face-api.js models loaded successfully');
    } catch (error) {
        console.error('Error loading face-api.js models:', error);
        errorMessage.value = 'Gagal memuat model AI. Silakan refresh halaman.';
        emit('error', errorMessage.value);
    }
};

const startCamera = async () => {
    try {
        const constraints = {
            video: {
                width: { ideal: 640 },
                height: { ideal: 480 },
                facingMode: 'user'
            }
        };

        stream.value = await navigator.mediaDevices.getUserMedia(constraints);

        if (videoRef.value) {
            videoRef.value.srcObject = stream.value;
            await videoRef.value.play();

            // Wait for video to be ready
            videoRef.value.addEventListener('loadedmetadata', () => {
                isLoading.value = false;
                startFaceDetection();
            });
        }
    } catch (error) {
        console.error('Camera access error:', error);
        errorMessage.value = 'Tidak dapat mengakses kamera. Pastikan izin kamera telah diberikan.';
        emit('error', errorMessage.value);
        isLoading.value = false;
    }
};

const startFaceDetection = () => {
    if (!videoRef.value || !modelsLoaded.value) return;

    detectionInterval.value = window.setInterval(async () => {
        if (!videoRef.value || isProcessing.value) return;

        try {
            const detection = await faceapi
                .detectSingleFace(videoRef.value, new faceapi.TinyFaceDetectorOptions())
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

                    const resizedDetection = faceapi.resizeResults(detection, displaySize);
                    faceapi.draw.drawDetections(canvas, [resizedDetection]);
                    faceapi.draw.drawFaceLandmarks(canvas, [resizedDetection]);
                }
            }
        } catch (error) {
            console.error('Face detection error:', error);
        }
    }, 100);
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

onMounted(async () => {
    await loadModels();
    if (modelsLoaded.value) {
        await startCamera();
    }
});

onUnmounted(() => {
    stopCamera();
});
</script>

<template>
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-sm">
        <div class="relative w-full max-w-md mx-4 bg-white rounded-xl shadow-2xl dark:bg-gray-900">
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
            <div class="relative p-4">
                <div class="relative aspect-video w-full overflow-hidden rounded-lg bg-gray-100 dark:bg-gray-800">
                    <!-- Loading State -->
                    <div v-if="isLoading" class="absolute inset-0 flex items-center justify-center">
                        <div class="text-center">
                            <Loader2 class="mx-auto h-8 w-8 animate-spin text-blue-600" />
                            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Memuat kamera...</p>
                        </div>
                    </div>

                    <!-- Video Stream -->
                    <video
                        ref="videoRef"
                        autoplay
                        muted
                        playsinline
                        class="h-full w-full object-cover"
                        :class="{ 'opacity-0': isLoading }"
                    ></video>

                    <!-- Detection Overlay -->
                    <canvas
                        ref="canvasRef"
                        class="absolute inset-0 h-full w-full"
                        :class="{ 'opacity-0': isLoading }"
                    ></canvas>

                    <!-- Face Detection Indicator -->
                    <div class="absolute top-4 right-4">
                        <div
                            class="flex items-center gap-2 rounded-full px-3 py-1 text-xs font-medium"
                            :class="{
                                'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400': faceDetected,
                                'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400': !faceDetected && !isLoading
                            }"
                        >
                            <div
                                class="h-2 w-2 rounded-full"
                                :class="{
                                    'bg-green-500 animate-pulse': faceDetected,
                                    'bg-red-500': !faceDetected && !isLoading
                                }"
                            ></div>
                            {{ faceDetected ? 'Wajah Terdeteksi' : 'Cari Wajah...' }}
                        </div>
                    </div>

                    <!-- Setup Progress -->
                    <div v-if="mode === 'setup' && capturedDescriptors.length > 0" class="absolute bottom-4 left-4">
                        <div class="flex items-center gap-2 rounded-full bg-blue-100 px-3 py-1 text-xs font-medium text-blue-800 dark:bg-blue-900/20 dark:text-blue-400">
                            <Check class="h-3 w-3" />
                            {{ capturedDescriptors.length }}/3 Foto
                        </div>
                    </div>
                </div>

                <!-- Error Message -->
                <div v-if="errorMessage" class="mt-4 flex items-center gap-2 rounded-lg bg-red-50 p-3 text-sm text-red-800 dark:bg-red-900/20 dark:text-red-400">
                    <AlertCircle class="h-4 w-4 flex-shrink-0" />
                    {{ errorMessage }}
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
                        </ul>
                    </div>
                </div>

                <!-- Capture Button -->
                <div class="mt-6 flex justify-center">
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
                        <Camera v-else class="h-4 w-4" />
                        {{ isProcessing ? 'Memproses...' :
                           mode === 'setup' ? 'Ambil Foto' : 'Verifikasi Wajah' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>