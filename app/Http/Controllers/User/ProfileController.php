<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Department;
use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function show(): Response
    {
        $user = auth()->user()->load(['department:id,name', 'position:id,title']);

        return Inertia::render('user/Profile', [
            'user' => $user,
        ]);
    }

    public function updateBasic(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
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
