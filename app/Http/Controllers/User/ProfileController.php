<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function show(): Response
    {
        // Clear any auth cache and force fresh user data from database
        auth()->forgetUser();

        $userId = auth()->id();

        // Force completely fresh query from database
        $user = User::with(['department:id,name', 'position:id,title'])
            ->where('id', $userId)
            ->first();

        // Clear model cache and reload from database
        $user->setRawAttributes($user->getAttributes());
        $user->refresh();

        // Double check by reloading without relationships
        $freshUser = User::find($userId);
        $user->face_recognition_enabled = $freshUser->face_recognition_enabled;
        $user->face_setup_at = $freshUser->face_setup_at;
        $user->face_descriptors = $freshUser->face_descriptors;

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

        // Set cache headers to prevent caching
        response()->header('Cache-Control', 'no-cache, no-store, must-revalidate');
        response()->header('Pragma', 'no-cache');
        response()->header('Expires', '0');

        return Inertia::render('user/Profile', [
            'user' => $user->makeVisible(['face_recognition_enabled', 'face_recognition_mandatory', 'face_setup_at'])->toArray(),
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
}
