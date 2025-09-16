<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class FaceRecognitionService
{
    private const MIN_CONFIDENCE_SCORE = 65;
    private const MAX_DAILY_ATTEMPTS = 3;

    public function storeFaceDescriptors(User $user, array $descriptors): bool
    {
        try {
            $encryptedDescriptors = Crypt::encrypt($descriptors);

            $user->update([
                'face_descriptors' => $encryptedDescriptors,
                'face_recognition_enabled' => true,
                'face_setup_at' => now(),
                'face_recognition_attempts' => 0,
                'face_attempts_date' => null,
            ]);

            Log::info('Face descriptors stored for user', ['user_id' => $user->id]);
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to store face descriptors', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    public function getFaceDescriptors(User $user): ?array
    {
        if (!$user->face_descriptors || !$user->face_recognition_enabled) {
            return null;
        }

        try {
            return Crypt::decrypt($user->face_descriptors);
        } catch (\Exception $e) {
            Log::error('Failed to decrypt face descriptors', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    public function verifyFaceMatch(User $user, float $confidence): array
    {
        $result = [
            'success' => false,
            'confidence' => $confidence,
            'can_retry' => true,
            'attempts_remaining' => 0,
            'mandatory' => $user->face_recognition_mandatory,
        ];

        // Check if user has face recognition set up
        if (!$user->face_recognition_enabled || !$user->face_descriptors) {
            $result['error'] = 'Face recognition belum disetup. Silakan setup di profile.';
            return $result;
        }

        // Reset attempts if it's a new day
        if ($user->face_attempts_date !== today()) {
            $user->update([
                'face_recognition_attempts' => 0,
                'face_attempts_date' => today(),
            ]);
        }

        $attempts = $user->face_recognition_attempts;
        $result['attempts_remaining'] = max(0, self::MAX_DAILY_ATTEMPTS - $attempts);

        // Check if confidence meets threshold
        if ($confidence >= self::MIN_CONFIDENCE_SCORE) {
            $result['success'] = true;
            // Reset attempts on success
            $user->update(['face_recognition_attempts' => 0]);

            Log::info('Face verification successful', [
                'user_id' => $user->id,
                'confidence' => $confidence
            ]);

            return $result;
        }

        // Increment failed attempts
        $newAttempts = $attempts + 1;
        $user->update(['face_recognition_attempts' => $newAttempts]);

        $result['attempts_remaining'] = max(0, self::MAX_DAILY_ATTEMPTS - $newAttempts);
        $result['can_retry'] = $newAttempts < self::MAX_DAILY_ATTEMPTS;

        // After max attempts, make face recognition optional
        if ($newAttempts >= self::MAX_DAILY_ATTEMPTS) {
            $result['mandatory'] = false;
            $result['error'] = 'Verifikasi wajah gagal 3 kali. Anda dapat melanjutkan tanpa verifikasi wajah hari ini.';

            Log::warning('User exceeded face verification attempts', [
                'user_id' => $user->id,
                'attempts' => $newAttempts
            ]);
        } else {
            $remaining = self::MAX_DAILY_ATTEMPTS - $newAttempts;
            $result['error'] = "Wajah tidak cocok (skor: {$confidence}%). Sisa percobaan: {$remaining}";
        }

        return $result;
    }

    public function isFaceRecognitionRequired(User $user): bool
    {
        // If user doesn't have face recognition enabled, not required
        if (!$user->face_recognition_enabled) {
            return false;
        }

        // If not mandatory for this user, not required
        if (!$user->face_recognition_mandatory) {
            return false;
        }

        // Reset attempts if it's a new day
        if ($user->face_attempts_date !== today()) {
            $user->update([
                'face_recognition_attempts' => 0,
                'face_attempts_date' => today(),
            ]);
            return true;
        }

        // If user has exceeded max attempts today, not required
        return $user->face_recognition_attempts < self::MAX_DAILY_ATTEMPTS;
    }

    public function deleteFaceData(User $user): bool
    {
        try {
            $user->update([
                'face_descriptors' => null,
                'face_recognition_enabled' => false,
                'face_setup_at' => null,
                'face_recognition_attempts' => 0,
                'face_attempts_date' => null,
                'face_recognition_mandatory' => true,
            ]);

            Log::info('Face data deleted for user', ['user_id' => $user->id]);
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to delete face data', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    public function getFaceRecognitionStats(): array
    {
        $totalUsers = User::where('is_admin', false)->count();
        $enabledUsers = User::where('face_recognition_enabled', true)->count();
        $todayAttempts = User::where('face_attempts_date', today())
                           ->where('face_recognition_attempts', '>', 0)
                           ->count();

        return [
            'total_users' => $totalUsers,
            'enabled_users' => $enabledUsers,
            'setup_percentage' => $totalUsers > 0 ? round(($enabledUsers / $totalUsers) * 100, 2) : 0,
            'failed_attempts_today' => $todayAttempts,
        ];
    }
}
