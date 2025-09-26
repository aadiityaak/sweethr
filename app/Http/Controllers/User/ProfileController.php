<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function show(): Response
    {
        // Force completely fresh user data from database - bypass all caching
        $userId = auth()->id();
        $user = \App\Models\User::with(['department:id,name', 'position:id,title'])->find($userId);

        // Double check - force refresh again
        $user->refresh();

        // Debug: Log what we're sending
        \Log::info('ProfileController sending user data', [
            'user_id' => $user->id,
            'face_recognition_enabled_raw' => $user->face_recognition_enabled,
            'face_recognition_enabled_type' => gettype($user->face_recognition_enabled),
            'face_recognition_enabled_bool' => (bool) $user->face_recognition_enabled,
            'face_recognition_mandatory_raw' => $user->face_recognition_mandatory,
            'face_setup_at' => $user->face_setup_at,
            'has_face_descriptors' => ! empty($user->face_descriptors),
            'user_attributes' => $user->getAttributes(),
        ]);

        // Prepare fresh user data for frontend
        $userData = $user->makeVisible(['face_recognition_enabled', 'face_recognition_mandatory', 'face_setup_at'])->toArray();

        // Add explicit face recognition status to ensure fresh data
        $userData['face_recognition_enabled'] = (bool) $user->face_recognition_enabled;
        $userData['face_recognition_mandatory'] = (bool) $user->face_recognition_mandatory;
        $userData['face_setup_at'] = $user->face_setup_at?->toISOString();
        $userData['has_face_descriptors'] = !empty($user->face_descriptors);

        // Add avatar URL if exists
        $userData['avatar'] = $user->avatar ? Storage::disk('public')->url($user->avatar) : null;

        // Add cache busting timestamp
        $userData['_cache_buster'] = now()->timestamp;

        \Log::info('Final userData being sent to frontend', $userData);

        return Inertia::render('user/Profile', [
            'user' => $userData,
        ]);
    }

    public function updateBasic(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
        ]);

        $user->update([
            'name' => $request->name,
            'phone' => $request->phone,
            'address' => $request->address,
            'date_of_birth' => $request->date_of_birth,
        ]);

        return back()->with('success', 'Profil berhasil diperbarui!');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'Password berhasil diubah!');
    }

    public function uploadAvatar(Request $request): RedirectResponse
    {
        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'], // Max 2MB
        ]);

        $user = auth()->user();

        // Delete old avatar if exists
        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        // Store new avatar
        $avatarPath = $request->file('avatar')->store('avatars', 'public');

        // Update user avatar path
        $user->update([
            'avatar' => $avatarPath,
        ]);

        return back()->with('success', 'Foto profil berhasil diperbarui!');
    }

    public function deleteAvatar(): RedirectResponse
    {
        $user = auth()->user();

        if ($user->avatar) {
            // Delete avatar file
            Storage::disk('public')->delete($user->avatar);

            // Remove avatar path from database
            $user->update([
                'avatar' => null,
            ]);
        }

        return back()->with('success', 'Foto profil berhasil dihapus!');
    }
}
