<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\AnnouncementCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class AnnouncementController extends Controller
{
    public function index()
    {
        $announcements = Announcement::with(['category', 'author'])
            ->latest()
            ->paginate(15);

        return Inertia::render('admin/Announcement/Index', [
            'announcements' => $announcements,
        ]);
    }

    public function create()
    {
        $categories = AnnouncementCategory::active()->get();

        return Inertia::render('admin/Announcement/Create', [
            'categories' => $categories,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'excerpt' => 'nullable|string|max:500',
            'category_id' => 'required|exists:announcement_categories,id',
            'image' => 'nullable|image|max:2048', // 2MB max
            'priority' => 'required|in:low,normal,high,urgent',
            'is_active' => 'boolean',
            'published_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after:published_at',
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('announcements', 'public');
            $validated['image_path'] = $imagePath;
        }

        $validated['created_by'] = auth()->id();

        // Set published_at to now if not provided and is_active is true
        if ($validated['is_active'] && !$validated['published_at']) {
            $validated['published_at'] = now();
        }

        Announcement::create($validated);

        return redirect()->route('admin.announcements.index')
            ->with('success', 'Pengumuman berhasil dibuat!');
    }

    public function show(Announcement $announcement)
    {
        $announcement->load(['category', 'author']);

        return Inertia::render('admin/Announcement/Show', [
            'announcement' => $announcement,
        ]);
    }

    public function edit(Announcement $announcement)
    {
        $categories = AnnouncementCategory::active()->get();
        $announcement->load(['category', 'author']);

        return Inertia::render('admin/Announcement/Edit', [
            'announcement' => $announcement,
            'categories' => $categories,
        ]);
    }

    public function update(Request $request, Announcement $announcement)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'excerpt' => 'nullable|string|max:500',
            'category_id' => 'required|exists:announcement_categories,id',
            'image' => 'nullable|image|max:2048',
            'remove_image' => 'boolean',
            'priority' => 'required|in:low,normal,high,urgent',
            'is_active' => 'boolean',
            'published_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after:published_at',
        ]);

        // Handle image upload/removal
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($announcement->image_path) {
                Storage::disk('public')->delete($announcement->image_path);
            }

            $imagePath = $request->file('image')->store('announcements', 'public');
            $validated['image_path'] = $imagePath;
        } elseif ($request->boolean('remove_image')) {
            // Remove existing image
            if ($announcement->image_path) {
                Storage::disk('public')->delete($announcement->image_path);
            }
            $validated['image_path'] = null;
        }

        // Remove remove_image from validated data as it's not a model field
        unset($validated['remove_image']);

        $announcement->update($validated);

        return redirect()->route('admin.announcements.index')
            ->with('success', 'Pengumuman berhasil diperbarui!');
    }

    public function updateWithFiles(Request $request, Announcement $announcement)
    {
        // This method handles file uploads via POST request
        return $this->update($request, $announcement);
    }

    public function destroy(Announcement $announcement)
    {
        // Delete image if exists
        if ($announcement->image_path) {
            Storage::disk('public')->delete($announcement->image_path);
        }

        $announcement->delete();

        return redirect()->route('admin.announcements.index')
            ->with('success', 'Pengumuman berhasil dihapus!');
    }

    public function toggleStatus(Announcement $announcement)
    {
        $announcement->update([
            'is_active' => !$announcement->is_active,
            'published_at' => !$announcement->is_active ? now() : $announcement->published_at,
        ]);

        $status = $announcement->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Pengumuman berhasil {$status}!");
    }
}
