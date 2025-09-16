import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { useToast } from '@/components/ui/toast/use-toast';

interface FaceRecognitionResult {
    success: boolean;
    confidence?: number;
    can_retry?: boolean;
    attempts_remaining?: number;
    mandatory?: boolean;
    error?: string;
}

export function useFaceRecognition() {
    const { toast } = useToast();

    const isLoading = ref(false);
    const showFaceCapture = ref(false);
    const faceDescriptors = ref<number[][]>([]);
    const isSetupMode = ref(false);
    const verificationResult = ref<FaceRecognitionResult | null>(null);

    const isSetupComplete = computed(() => {
        return faceDescriptors.value.length > 0;
    });

    const setupFaceRecognition = async (descriptors: number[][]) => {
        isLoading.value = true;

        try {
            // Ensure CSRF cookie is available before making the request
            await fetch('/sanctum/csrf-cookie', {
                credentials: 'include',
            });

            const response = await fetch('/api/face-recognition/setup', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ descriptors }),
                credentials: 'include', // Include cookies for session authentication
            });

            if (!response.ok) {
                if (response.status === 401) {
                    throw new Error('Session expired. Please login again.');
                } else if (response.status === 403) {
                    throw new Error('Access denied.');
                } else {
                    throw new Error(`Server error: ${response.status}`);
                }
            }

            const data = await response.json();

            if (data.success) {
                faceDescriptors.value = descriptors;
                showFaceCapture.value = false;

                toast({
                    title: '✅ Setup Berhasil!',
                    description: 'Pengenalan wajah telah berhasil diaktifkan.',
                    variant: 'success',
                });

                return true;
            } else {
                throw new Error(data.message || 'Setup gagal');
            }
        } catch (error) {
            console.error('Face recognition setup error:', error);
            toast({
                title: '❌ Setup Gagal',
                description: error instanceof Error ? error.message : 'Terjadi kesalahan saat setup.',
                variant: 'destructive',
            });
            return false;
        } finally {
            isLoading.value = false;
        }
    };

    const verifyFace = async (confidence: number): Promise<FaceRecognitionResult> => {
        isLoading.value = true;

        try {
            // Ensure CSRF cookie is available before making the request
            await fetch('/sanctum/csrf-cookie', {
                credentials: 'include',
            });

            const response = await fetch('/api/face-recognition/verify', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ confidence }),
                credentials: 'include', // Include cookies for session authentication
            });

            if (!response.ok) {
                if (response.status === 401) {
                    throw new Error('Session expired. Please login again.');
                } else if (response.status === 403) {
                    throw new Error('Access denied.');
                } else {
                    throw new Error(`Server error: ${response.status}`);
                }
            }

            const result = await response.json();
            verificationResult.value = result;

            if (result.success) {
                showFaceCapture.value = false;
                toast({
                    title: '✅ Verifikasi Berhasil!',
                    description: `Wajah terverifikasi dengan tingkat keyakinan ${result.confidence}%.`,
                    variant: 'success',
                });
            } else {
                toast({
                    title: '❌ Verifikasi Gagal',
                    description: result.error || 'Wajah tidak cocok.',
                    variant: 'destructive',
                });
            }

            return result;
        } catch (error) {
            console.error('Face verification error:', error);
            const errorResult: FaceRecognitionResult = {
                success: false,
                error: 'Terjadi kesalahan saat verifikasi wajah.',
                can_retry: true,
                mandatory: true,
            };

            toast({
                title: '❌ Verifikasi Error',
                description: errorResult.error,
                variant: 'destructive',
            });

            return errorResult;
        } finally {
            isLoading.value = false;
        }
    };

    const deleteFaceData = async () => {
        isLoading.value = true;

        try {
            // Ensure CSRF cookie is available before making the request
            await fetch('/sanctum/csrf-cookie', {
                credentials: 'include',
            });

            const response = await fetch('/api/face-recognition/delete', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'include', // Include cookies for session authentication
            });

            if (!response.ok) {
                if (response.status === 401) {
                    throw new Error('Session expired. Please login again.');
                } else if (response.status === 403) {
                    throw new Error('Access denied.');
                } else {
                    throw new Error(`Server error: ${response.status}`);
                }
            }

            const data = await response.json();

            if (data.success) {
                faceDescriptors.value = [];

                toast({
                    title: '✅ Data Dihapus',
                    description: 'Data pengenalan wajah telah dihapus.',
                    variant: 'success',
                });

                // Refresh page to update UI state
                router.reload();
                return true;
            } else {
                throw new Error(data.message || 'Gagal menghapus data');
            }
        } catch (error) {
            console.error('Delete face data error:', error);
            toast({
                title: '❌ Gagal Menghapus',
                description: error instanceof Error ? error.message : 'Terjadi kesalahan.',
                variant: 'destructive',
            });
            return false;
        } finally {
            isLoading.value = false;
        }
    };

    const openFaceSetup = () => {
        isSetupMode.value = true;
        showFaceCapture.value = true;
    };

    const openFaceVerification = (storedDescriptors: number[][]) => {
        faceDescriptors.value = storedDescriptors;
        isSetupMode.value = false;
        showFaceCapture.value = true;
    };

    const closeFaceCapture = () => {
        showFaceCapture.value = false;
        verificationResult.value = null;
    };

    const handleFaceCaptureError = (error: string) => {
        toast({
            title: '❌ Error Kamera',
            description: error,
            variant: 'destructive',
        });
    };

    const checkFaceRecognitionStatus = async () => {
        try {
            const response = await fetch('/api/face-recognition/status', {
                headers: {
                    'Accept': 'application/json',
                },
                credentials: 'include', // Include cookies for session authentication
            });
            if (!response.ok) {
                if (response.status === 401) {
                    return { enabled: false, error: 'Not authenticated' };
                } else {
                    throw new Error(`Server error: ${response.status}`);
                }
            }

            const data = await response.json();

            if (data.enabled) {
                faceDescriptors.value = data.descriptors || [];
            }

            return data;
        } catch (error) {
            console.error('Face recognition status check error:', error);
            return { enabled: false };
        }
    };

    return {
        // State
        isLoading,
        showFaceCapture,
        faceDescriptors,
        isSetupMode,
        verificationResult,

        // Computed
        isSetupComplete,

        // Methods
        setupFaceRecognition,
        verifyFace,
        deleteFaceData,
        openFaceSetup,
        openFaceVerification,
        closeFaceCapture,
        handleFaceCaptureError,
        checkFaceRecognitionStatus,
    };
}