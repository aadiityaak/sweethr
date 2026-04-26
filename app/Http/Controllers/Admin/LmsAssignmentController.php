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
    public function index()
    {
        $assignments = LmsAssignment::with(['category.parent'])
            ->latest()
            ->paginate(15);

        return Inertia::render('admin/Lms/Assignment/Index', [
            'assignments' => $assignments,
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
