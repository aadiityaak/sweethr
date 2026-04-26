<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\LmsAssignment;
use App\Models\LmsMaterial;
use App\Models\LmsQuiz;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LmsController extends Controller
{
    public function index(Request $request): Response
    {
        $materials = LmsMaterial::with(['category.parent'])
            ->where('is_active', true)
            ->latest()
            ->paginate(5, ['*'], 'materials_page')
            ->withQueryString();

        $quizzes = LmsQuiz::with(['category.parent'])
            ->withCount('questions')
            ->where('is_active', true)
            ->latest()
            ->paginate(5, ['*'], 'quizzes_page')
            ->withQueryString();

        $assignments = LmsAssignment::with(['category.parent'])
            ->where('is_active', true)
            ->latest()
            ->paginate(5, ['*'], 'assignments_page')
            ->withQueryString();

        return Inertia::render('user/Lms/Index', [
            'materials' => $materials,
            'quizzes' => $quizzes,
            'assignments' => $assignments,
            'totals' => [
                'materials' => (int) ($materials->total() ?? 0),
                'quizzes' => (int) LmsQuiz::where('is_active', true)->count(),
                'assignments' => (int) LmsAssignment::where('is_active', true)->count(),
            ],
        ]);
    }

    public function show(LmsMaterial $lms_material): Response
    {
        if (! $lms_material->is_active) {
            abort(404);
        }

        $lms_material->load(['category.parent']);

        return Inertia::render('user/Lms/Show', [
            'material' => $lms_material,
        ]);
    }
}
