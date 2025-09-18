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
            console.log('=== SETUP FACE RECOGNITION DEBUG ===');
            console.log('Descriptors received:', descriptors.length);
            console.log('Descriptor length check:', descriptors.map(d => d.length));
            console.log('Current URL:', window.location.href);
            console.log('CSRF Token:', document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'));

            // Ensure CSRF cookie is available before making the request
            console.log('Fetching CSRF cookie...');
            const csrfCookieResponse = await fetch('/sanctum/csrf-cookie', {
                credentials: 'same-origin',
            });
            console.log('CSRF cookie response:', csrfCookieResponse.status);

            // Wait a bit for cookie to be set
            await new Promise(resolve => setTimeout(resolve, 100));

            // Get CSRF token from meta tag after ensuring cookie is set
            let csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            console.log('CSRF token from meta tag:', csrfToken);

            if (!csrfToken) {
                // Try to get from cookies as fallback
                const cookies = document.cookie.split(';');
                const xsrfCookie = cookies.find(cookie => cookie.trim().startsWith('XSRF-TOKEN='));
                if (xsrfCookie) {
                    csrfToken = decodeURIComponent(xsrfCookie.split('=')[1]);
                    console.log('CSRF token from cookie:', csrfToken);
                }
            }

            if (!csrfToken) {
                throw new Error('CSRF token not found. Please refresh the page.');
            }

            console.log('Making API request to /api/face-recognition/setup...');
            console.log('Request payload:', {
                descriptors: descriptors.map(d => `Array(${d.length})`)
            });

            // Make the API request with proper headers
            const response = await fetch('/api/face-recognition/setup', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-XSRF-TOKEN': csrfToken, // Additional CSRF header
                },
                body: JSON.stringify({ descriptors }),
                credentials: 'include', // Changed from 'same-origin' to 'include'
            });

            console.log('Response received:', {
                status: response.status,
                statusText: response.statusText,
                ok: response.ok,
                headers: Object.fromEntries(response.headers.entries())
            });

            if (!response.ok) {
                const errorText = await response.text();
                console.error('Error response body:', errorText);

                if (response.status === 401) {
                    throw new Error('Session expired. Please refresh the page and login again.');
                } else if (response.status === 403) {
                    throw new Error('Access denied. Please check your permissions.');
                } else if (response.status === 419) {
                    throw new Error('CSRF token mismatch. Please refresh the page.');
                } else {
                    throw new Error(`Server error: ${response.status} - ${errorText}`);
                }
            }

            const data = await response.json();
            console.log('API response data:', data);

            if (data.success) {
                console.log('Setup successful! Updating local state...');
                faceDescriptors.value = descriptors;
                showFaceCapture.value = false;

                toast({
                    title: '✅ Setup Berhasil!',
                    description: 'Pengenalan wajah telah berhasil diaktifkan. Halaman akan diperbarui dalam 1 detik.',
                    variant: 'success',
                });

                console.log('=== SETUP COMPLETED SUCCESSFULLY ===');
                console.log('Face recognition enabled in backend:', data.data?.face_recognition_enabled);
                return true;
            } else {
                console.error('Setup failed with response:', data);
                throw new Error(data.message || 'Setup gagal - respon server tidak valid');
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