<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\FaceRecognitionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FaceRecognitionController extends Controller
{
    public function __construct(
        private FaceRecognitionService $faceRecognitionService
    ) {}

    public function setup(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'descriptors' => 'required|array|min:1|max:5',
            'descriptors.*' => 'required|array|size:128',
            'descriptors.*.*' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Data wajah tidak valid.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = auth()->user();
        $descriptors = $request->input('descriptors');

        $success = $this->faceRecognitionService->storeFaceDescriptors($user, $descriptors);

        if ($success) {
            // Force refresh user data after successful operation
            $user->refresh();
            auth()->setUser($user);

            return response()->json([
                'success' => true,
                'message' => 'Pengenalan wajah berhasil disetup.',
                'data' => [
                    'face_recognition_enabled' => $user->face_recognition_enabled,
                    'setup_at' => $user->face_setup_at?->toISOString(),
                ],
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Gagal menyimpan data wajah. Silakan coba lagi.',
        ], 500);
    }

    public function verify(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'confidence' => 'required|numeric|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Data verifikasi tidak valid.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = auth()->user();
        $confidence = $request->input('confidence');

        $result = $this->faceRecognitionService->verifyFaceMatch($user, $confidence);

        return response()->json($result);
    }

    public function status(): JsonResponse
    {
        // Get fresh user data from database to avoid cache issues
        $userId = auth()->id();
        $user = \App\Models\User::find($userId);

        $descriptors = $this->faceRecognitionService->getFaceDescriptors($user);
        $isRequired = $this->faceRecognitionService->isFaceRecognitionRequired($user);

        return response()->json([
            'enabled' => $user->face_recognition_enabled,
            'required' => $isRequired,
            'mandatory' => $user->face_recognition_mandatory,
            'setup_at' => $user->face_setup_at?->toISOString(),
            'attempts_today' => $user->face_recognition_attempts,
            'attempts_date' => $user->face_attempts_date,
            'has_descriptors' => ! empty($descriptors),
        ]);
    }

    public function delete(): JsonResponse
    {
        $user = auth()->user();

        $success = $this->faceRecognitionService->deleteFaceData($user);

        if ($success) {
            // Force refresh user data after successful operation
            $user->refresh();
            auth()->setUser($user);

            return response()->json([
                'success' => true,
                'message' => 'Data pengenalan wajah berhasil dihapus.',
                'data' => [
                    'face_recognition_enabled' => $user->face_recognition_enabled,
                    'face_descriptors' => $user->face_descriptors,
                ],
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Gagal menghapus data wajah. Silakan coba lagi.',
        ], 500);
    }

    public function getDescriptors(): JsonResponse
    {
        // Get fresh user data from database to avoid cache issues
        $userId = auth()->id();
        $user = \App\Models\User::find($userId);

        if (! $user->face_recognition_enabled) {
            return response()->json([
                'success' => false,
                'message' => 'Pengenalan wajah belum diaktifkan.',
            ], 404);
        }

        $descriptors = $this->faceRecognitionService->getFaceDescriptors($user);

        if (! $descriptors) {
            return response()->json([
                'success' => false,
                'message' => 'Data wajah tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'descriptors' => $descriptors,
        ]);
    }

    public function resetAttempts(): JsonResponse
    {
        $user = auth()->user();

        $user->update([
            'face_recognition_attempts' => 0,
            'face_attempts_date' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Percobaan verifikasi wajah berhasil direset.',
        ]);
    }

    public function stats(): JsonResponse
    {
        $user = auth()->user();

        // Only admin can view stats
        if (! $user->is_admin) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak.',
            ], 403);
        }

        $stats = $this->faceRecognitionService->getFaceRecognitionStats();

        return response()->json([
            'success' => true,
            'stats' => $stats,
        ]);
    }
}
