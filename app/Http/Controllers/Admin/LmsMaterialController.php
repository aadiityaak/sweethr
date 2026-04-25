<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LmsMaterial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class LmsMaterialController extends Controller
{
    public function index()
    {
        $materials = LmsMaterial::latest()->paginate(15);

        return Inertia::render('admin/Lms/Material/Index', [
            'materials' => $materials,
        ]);
    }

    public function create()
    {
        return Inertia::render('admin/Lms/Material/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:video,pdf,module',
            'file' => 'required|file|max:51200', // 50MB max
            'is_active' => 'boolean',
        ]);

        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('lms/materials', 'public');
            $validated['file_path'] = $path;
        }

        LmsMaterial::create($validated);

        return redirect()->route('admin.lms-materials.index')
            ->with('success', 'Materi berhasil diunggah!');
    }

    public function show(LmsMaterial $lmsMaterial)
    {
        return Inertia::render('admin/Lms/Material/Show', [
            'material' => $lmsMaterial,
        ]);
    }

    public function edit(LmsMaterial $lmsMaterial)
    {
        return Inertia::render('admin/Lms/Material/Edit', [
            'material' => $lmsMaterial,
        ]);
    }

    public function update(Request $request, LmsMaterial $lmsMaterial)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:video,pdf,module',
            'file' => 'nullable|file|max:51200',
            'is_active' => 'boolean',
        ]);

        if ($request->hasFile('file')) {
            // Delete old file
            if ($lmsMaterial->file_path) {
                Storage::disk('public')->delete($lmsMaterial->file_path);
            }

            $path = $request->file('file')->store('lms/materials', 'public');
            $validated['file_path'] = $path;
        }

        $lmsMaterial->update($validated);

        return redirect()->route('admin.lms-materials.index')
            ->with('success', 'Materi berhasil diperbarui!');
    }

    public function updateWithFiles(Request $request, LmsMaterial $lmsMaterial)
    {
        return $this->update($request, $lmsMaterial);
    }

    public function destroy(LmsMaterial $lmsMaterial)
    {
        if ($lmsMaterial->file_path) {
            Storage::disk('public')->delete($lmsMaterial->file_path);
        }

        $lmsMaterial->delete();

        return redirect()->route('admin.lms-materials.index')
            ->with('success', 'Materi berhasil dihapus!');
    }
}
