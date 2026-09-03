<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LmsCurriculumMatrix;
use App\Models\Position;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CurriculumMatrixController extends Controller
{
    public function index(Request $request)
    {
        $positions = Position::query()
            ->where('is_active', true)
            ->orderBy('level')
            ->orderBy('title')
            ->get(['id', 'title', 'level']);

        $items = LmsCurriculumMatrix::query()
            ->with(['position:id,title,level', 'category:id,name', 'material:id,title', 'quiz:id,title', 'assignment:id,title'])
            ->when($request->get('position'), fn ($q, $posId) => $q->where('position_id', $posId))
            ->orderBy('position_id')
            ->orderBy('item_type')
            ->get();

        return Inertia::render('admin/Lms/CurriculumMatrix/Index', [
            'items' => $items,
            'positions' => $positions,
            'filters' => ['position' => $request->get('position', '')],
        ]);
    }

    public function create()
    {
        return Inertia::render('admin/Lms/CurriculumMatrix/Create', $this->formData());
    }

    public function store(Request $request)
    {
        $validated = $this->validateItem($request);

        LmsCurriculumMatrix::create($validated);

        return redirect()->route('admin.curriculum-matrix.index')
            ->with('success', 'Item kurikulum berhasil ditambahkan.');
    }

    public function edit(LmsCurriculumMatrix $curriculum_matrix)
    {
        return Inertia::render('admin/Lms/CurriculumMatrix/Edit', [
            ...$this->formData(),
            'item' => $curriculum_matrix,
        ]);
    }

    public function update(Request $request, LmsCurriculumMatrix $curriculum_matrix)
    {
        $validated = $this->validateItem($request);

        $curriculum_matrix->update($validated);

        return redirect()->route('admin.curriculum-matrix.index')
            ->with('success', 'Item kurikulum berhasil diperbarui.');
    }

    public function destroy(LmsCurriculumMatrix $curriculum_matrix)
    {
        $curriculum_matrix->delete();

        return redirect()->route('admin.curriculum-matrix.index')
            ->with('success', 'Item kurikulum berhasil dihapus.');
    }

    private function validateItem(Request $request): array
    {
        return $request->validate([
            'position_id' => ['required', 'exists:positions,id'],
            'item_type' => ['required', 'in:material,quiz,assignment'],
            'lms_category_id' => ['nullable', 'exists:lms_categories,id'],
            'lms_material_id' => ['nullable', 'exists:lms_materials,id'],
            'lms_quiz_id' => ['nullable', 'exists:lms_quizzes,id'],
            'lms_assignment_id' => ['nullable', 'exists:lms_assignments,id'],
            'is_mandatory' => ['boolean'],
            'deadline_days' => ['nullable', 'integer', 'min:1', 'max:365'],
        ]);
    }

    private function formData(): array
    {
        return [
            'positions' => Position::query()->where('is_active', true)->orderBy('title')->get(['id', 'title', 'level']),
            'categories' => \App\Models\LmsCategory::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'materials' => \App\Models\LmsMaterial::query()->orderBy('title')->get(['id', 'title']),
            'quizzes' => \App\Models\LmsQuiz::query()->orderBy('title')->get(['id', 'title']),
            'assignments' => \App\Models\LmsAssignment::query()->orderBy('title')->get(['id', 'title']),
        ];
    }
}
