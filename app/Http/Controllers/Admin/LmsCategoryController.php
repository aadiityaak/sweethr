<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LmsCategory;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LmsCategoryController extends Controller
{
    public function index()
    {
        $categories = LmsCategory::with('children')->whereNull('parent_id')->orderBy('name')->get();

        return Inertia::render('admin/Lms/Category/Index', [
            'categories' => $categories,
        ]);
    }

    public function create()
    {
        $parents = LmsCategory::whereNull('parent_id')->orderBy('name')->get(['id', 'name']);

        return Inertia::render('admin/Lms/Category/Create', [
            'parents' => $parents,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:lms_categories,id',
            'is_active' => 'boolean',
        ]);

        LmsCategory::create($validated);

        return redirect()->route('admin.lms-categories.index')
            ->with('success', 'Kategori LMS berhasil dibuat!');
    }

    public function edit(LmsCategory $lms_category)
    {
        $parents = LmsCategory::whereNull('parent_id')
            ->where('id', '!=', $lms_category->id)
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('admin/Lms/Category/Edit', [
            'category' => $lms_category,
            'parents' => $parents,
        ]);
    }

    public function update(Request $request, LmsCategory $lms_category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:lms_categories,id|not_in:'.$lms_category->id,
            'is_active' => 'boolean',
        ]);

        $lms_category->update($validated);

        return redirect()->route('admin.lms-categories.index')
            ->with('success', 'Kategori LMS berhasil diperbarui!');
    }

    public function destroy(LmsCategory $lms_category)
    {
        if ($lms_category->children()->exists()) {
            return redirect()->route('admin.lms-categories.index')
                ->with('error', 'Kategori masih memiliki sub kategori. Hapus sub kategori terlebih dahulu.');
        }

        if ($lms_category->materials()->exists()) {
            return redirect()->route('admin.lms-categories.index')
                ->with('error', 'Kategori masih digunakan oleh materi. Pindahkan materi terlebih dahulu.');
        }

        $lms_category->delete();

        return redirect()->route('admin.lms-categories.index')
            ->with('success', 'Kategori LMS berhasil dihapus!');
    }
}
