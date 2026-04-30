<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LmsAssignment;
use App\Models\LmsAssignmentSubmission;
use App\Models\LmsCategory;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LmsAssignmentController extends Controller
{
    public function index(Request $request)
    {
        $categoryId = (int) $request->get('category', 0);
        $search = trim((string) $request->get('search', ''));

        $query = LmsAssignment::with(['category.parent'])->latest();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%'.$search.'%')
                    ->orWhere('description', 'like', '%'.$search.'%')
                    ->orWhere('instructions', 'like', '%'.$search.'%');
            });
        }

        if ($categoryId > 0) {
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

        $assignments = $query->paginate(15)->withQueryString();
        $categories = LmsCategory::with('children')->whereNull('parent_id')->orderBy('name')->get(['id', 'name', 'parent_id']);

        return Inertia::render('admin/Lms/Assignment/Index', [
            'assignments' => $assignments,
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

        return Inertia::render('admin/Lms/Assignment/Create', [
            'categories' => $categories,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'instructions' => 'nullable|string',
            'lms_category_id' => 'required|exists:lms_categories,id',
            'due_at' => 'nullable|date',
            'max_score' => 'required|integer|min:1|max:100000',
            'max_attempts' => 'required|integer|min:1|max:1000',
            'is_active' => 'boolean',
        ]);

        $assignment = LmsAssignment::create($validated);

        return redirect()->route('admin.lms-assignments.edit', $assignment)
            ->with('success', 'Tugas berhasil dibuat!');
    }

    public function show(LmsAssignment $lms_assignment)
    {
        $lms_assignment->load(['category.parent']);

        return Inertia::render('admin/Lms/Assignment/Show', [
            'assignment' => $lms_assignment,
        ]);
    }

    public function edit(LmsAssignment $lms_assignment)
    {
        $categories = LmsCategory::with('children')->whereNull('parent_id')->orderBy('name')->get();
        $lms_assignment->load(['category.parent']);

        return Inertia::render('admin/Lms/Assignment/Edit', [
            'assignment' => $lms_assignment,
            'categories' => $categories,
        ]);
    }

    public function update(Request $request, LmsAssignment $lms_assignment)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'instructions' => 'nullable|string',
            'lms_category_id' => 'required|exists:lms_categories,id',
            'due_at' => 'nullable|date',
            'max_score' => 'required|integer|min:1|max:100000',
            'max_attempts' => 'required|integer|min:1|max:1000',
            'is_active' => 'boolean',
        ]);

        $lms_assignment->update($validated);

        return redirect()->route('admin.lms-assignments.edit', $lms_assignment)
            ->with('success', 'Tugas berhasil diperbarui!');
    }

    public function destroy(LmsAssignment $lms_assignment)
    {
        $lms_assignment->delete();

        return redirect()->route('admin.lms-assignments.index')
            ->with('success', 'Tugas berhasil dihapus!');
    }

    public function submissions(Request $request, LmsAssignment $lms_assignment)
    {
        $query = LmsAssignmentSubmission::with(['user:id,name', 'grader:id,name'])
            ->where('lms_assignment_id', $lms_assignment->id)
            ->latest('submitted_at');

        if ($request->search) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->search.'%');
            });
        }

        $submissions = $query->paginate(15)->withQueryString();
        $lms_assignment->load(['category.parent']);

        return Inertia::render('admin/Lms/AssignmentSubmission/Index', [
            'assignment' => $lms_assignment,
            'submissions' => $submissions,
            'filters' => $request->only(['search']),
        ]);
    }
}
