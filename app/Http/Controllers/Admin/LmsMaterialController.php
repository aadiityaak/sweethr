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
    public function index(Request $request)
    {
        $categoryId = (int) $request->get('category', 0);
        $search = trim((string) $request->get('search', ''));
        $selectedCategory = null;

        $query = LmsMaterial::query()->with(['category.parent'])->latest();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%'.$search.'%')
                    ->orWhere('description', 'like', '%'.$search.'%');
            });
        }

        if ($categoryId > 0) {
            $selectedCategory = LmsCategory::query()->with('parent')->find($categoryId);

            $categoryIds = [$categoryId];
            $all = LmsCategory::query()->get(['id', 'parent_id']);
            $childrenByParent = [];

            foreach ($all as $cat) {
                if ($cat->parent_id === null) {
                    continue;
                }
                $childrenByParent[$cat->parent_id][] = (int) $cat->id;
            }

            $stack = [$categoryId];
            while (count($stack)) {
                $current = array_pop($stack);
                $children = $childrenByParent[$current] ?? [];
                foreach ($children as $childId) {
                    if (in_array($childId, $categoryIds, true)) {
                        continue;
                    }
                    $categoryIds[] = $childId;
                    $stack[] = $childId;
                }
            }

            $query->whereIn('lms_category_id', $categoryIds);
        }

        $materials = $query->paginate(15)->withQueryString();
        $categories = LmsCategory::with('children')->whereNull('parent_id')->orderBy('name')->get(['id', 'name', 'parent_id']);

        return Inertia::render('admin/Lms/Material/Index', [
            'materials' => $materials,
            'selectedCategory' => $selectedCategory,
            'categories' => $categories,
            'filters' => [
                'category' => $categoryId > 0 ? $categoryId : null,
                'search' => $search !== '' ? $search : null,
            ],
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
        $request->merge([
            'youtube_url' => $request->input('youtube_url') ?: null,
        ]);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'lms_category_id' => 'required|exists:lms_categories,id',
            'youtube_url' => 'nullable|url|max:2048',
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
        $request->merge([
            'youtube_url' => $request->input('youtube_url') ?: null,
        ]);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'lms_category_id' => 'required|exists:lms_categories,id',
            'youtube_url' => 'nullable|url|max:2048',
            'remove_file' => 'boolean',
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

        if ($request->boolean('remove_file') && ! $request->hasFile('file')) {
            if ($lms_material->file_path) {
                Storage::disk('public')->delete($lms_material->file_path);
            }
            $validated['file_path'] = '';
        }

        unset($validated['file'], $validated['thumbnail'], $validated['remove_file']);
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
