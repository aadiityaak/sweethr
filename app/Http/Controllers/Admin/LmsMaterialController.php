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

        unset($validated['file']);
        LmsMaterial::create($validated);

        return redirect()->route('admin.lms-materials.index')
            ->with('success', 'Materi berhasil diunggah!');
    }

    public function show(LmsMaterial $lms_material)
    {
        return Inertia::render('admin/Lms/Material/Show', [
            'material' => $lms_material,
        ]);
    }

    public function edit(LmsMaterial $lms_material)
    {
        return Inertia::render('admin/Lms/Material/Edit', [
            'material' => $lms_material,
        ]);
    }

    public function update(Request $request, LmsMaterial $lms_material)
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
            if ($lms_material->file_path) {
                Storage::disk('public')->delete($lms_material->file_path);
            }

            $path = $request->file('file')->store('lms/materials', 'public');
            $validated['file_path'] = $path;
        }

        unset($validated['file']);
        $lms_material->update($validated);

        return redirect()->route('admin.lms-materials.index')
            ->with('success', 'Materi berhasil diperbarui!');
    }

    public function updateWithFiles(Request $request, LmsMaterial $lms_material)
    {
        return $this->update($request, $lms_material);
    }

    public function destroy(LmsMaterial $lms_material)
    {
        if ($lms_material->file_path) {
            Storage::disk('public')->delete($lms_material->file_path);
        }

        $lms_material->delete();

        return redirect()->route('admin.lms-materials.index')
            ->with('success', 'Materi berhasil dihapus!');
    }
}
