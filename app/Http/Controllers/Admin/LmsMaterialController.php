<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LmsCategory;
use App\Models\LmsMaterial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class LmsMaterialController extends Controller
{
    public function index()
    {
        $materials = LmsMaterial::with(['category.parent'])->latest()->paginate(15);

        return Inertia::render('admin/Lms/Material/Index', [
            'materials' => $materials,
        ]);
    }

    public function create()
    {
        $categories = LmsCategory::with('children')->whereNull('parent_id')->orderBy('name')->get();

        return Inertia::render('admin/Lms/Material/Create', [
            'categories' => $categories,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'lms_category_id' => 'required|exists:lms_categories,id',
            'file' => 'required|file|max:51200', // 50MB max
            'thumbnail' => 'nullable|image|max:5120',
            'is_active' => 'boolean',
        ]);

        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('lms/materials', 'public');
            $validated['file_path'] = $path;
        }

        if ($request->hasFile('thumbnail')) {
            $path = $request->file('thumbnail')->store('lms/materials/thumbnails', 'public');
            $validated['thumbnail_path'] = $path;
        }

        unset($validated['file'], $validated['thumbnail']);
        LmsMaterial::create($validated);

        return redirect()->route('admin.lms-materials.index')
            ->with('success', 'Materi berhasil diunggah!');
    }

    public function show(LmsMaterial $lms_material)
    {
        $lms_material->load(['category.parent']);

        return Inertia::render('admin/Lms/Material/Show', [
            'material' => $lms_material,
        ]);
    }

    public function edit(LmsMaterial $lms_material)
    {
        $categories = LmsCategory::with('children')->whereNull('parent_id')->orderBy('name')->get();
        $lms_material->load(['category.parent']);

        return Inertia::render('admin/Lms/Material/Edit', [
            'material' => $lms_material,
            'categories' => $categories,
        ]);
    }

    public function update(Request $request, LmsMaterial $lms_material)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'lms_category_id' => 'required|exists:lms_categories,id',
            'file' => 'nullable|file|max:51200',
            'thumbnail' => 'nullable|image|max:5120',
            'is_active' => 'boolean',
        ]);

        if ($request->hasFile('file')) {
            if ($lms_material->file_path) {
                Storage::disk('public')->delete($lms_material->file_path);
            }

            $path = $request->file('file')->store('lms/materials', 'public');
            $validated['file_path'] = $path;
        }

        if ($request->hasFile('thumbnail')) {
            if ($lms_material->thumbnail_path) {
                Storage::disk('public')->delete($lms_material->thumbnail_path);
            }

            $path = $request->file('thumbnail')->store('lms/materials/thumbnails', 'public');
            $validated['thumbnail_path'] = $path;
        }

        unset($validated['file'], $validated['thumbnail']);
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

        if ($lms_material->thumbnail_path) {
            Storage::disk('public')->delete($lms_material->thumbnail_path);
        }

        $lms_material->delete();

        return redirect()->route('admin.lms-materials.index')
            ->with('success', 'Materi berhasil dihapus!');
    }
}
