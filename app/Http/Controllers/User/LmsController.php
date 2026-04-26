<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\LmsAssignment;
use App\Models\LmsAssignmentSubmission;
use App\Models\LmsMaterial;
use App\Models\LmsMaterialRead;
use App\Models\LmsQuiz;
use App\Models\LmsQuizAttempt;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LmsController extends Controller
{
    public function index(Request $request): Response
    {
        $userId = (int) auth()->id();

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

        $materialIds = $materials->getCollection()->pluck('id')->all();
        $readIdMap = [];
        if (count($materialIds)) {
            $readIds = LmsMaterialRead::query()
                ->where('user_id', $userId)
                ->whereIn('lms_material_id', $materialIds)
                ->pluck('lms_material_id')
                ->all();
            $readIdMap = array_fill_keys($readIds, true);
        }

        $materials->setCollection(
            $materials->getCollection()->map(function ($m) use ($readIdMap) {
                $m->setAttribute('is_read', isset($readIdMap[$m->id]));
                return $m;
            })
        );

        $quizIds = $quizzes->getCollection()->pluck('id')->all();
        $quizAgg = collect();
        if (count($quizIds)) {
            $quizAgg = LmsQuizAttempt::query()
                ->where('user_id', $userId)
                ->whereIn('lms_quiz_id', $quizIds)
                ->whereNotNull('submitted_at')
                ->selectRaw('lms_quiz_id')
                ->selectRaw('count(*) as submitted_attempts')
                ->selectRaw('max(is_passed) as best_is_passed')
                ->selectRaw('max(case when max_score > 0 then (score / max_score) * 100 else null end) as best_percent')
                ->selectRaw('max(submitted_at) as last_submitted_at')
                ->groupBy('lms_quiz_id')
                ->get()
                ->keyBy('lms_quiz_id');
        }

        $quizzes->setCollection(
            $quizzes->getCollection()->map(function ($q) use ($quizAgg) {
                $agg = $quizAgg->get($q->id);
                $bestPercent = $agg?->best_percent !== null ? round((float) $agg->best_percent, 1) : null;

                $q->setAttribute('is_done', (int) ($agg?->submitted_attempts ?? 0) > 0);
                $q->setAttribute('submitted_attempts', (int) ($agg?->submitted_attempts ?? 0));
                $q->setAttribute('best_is_passed', (bool) ($agg?->best_is_passed ?? false));
                $q->setAttribute('best_percent', $bestPercent);
                $q->setAttribute('last_submitted_at', $agg?->last_submitted_at ? (string) $agg->last_submitted_at : null);
                return $q;
            })
        );

        $assignmentIds = $assignments->getCollection()->pluck('id')->all();
        $submissions = collect();
        if (count($assignmentIds)) {
            $submissions = LmsAssignmentSubmission::query()
                ->where('user_id', $userId)
                ->whereIn('lms_assignment_id', $assignmentIds)
                ->get()
                ->keyBy('lms_assignment_id');
        }

        $assignments->setCollection(
            $assignments->getCollection()->map(function ($a) use ($submissions) {
                $sub = $submissions->get($a->id);
                $isDone = (bool) ($sub?->submitted_at);
                $scorePercent = null;
                if ($sub?->score !== null && (int) $a->max_score > 0) {
                    $scorePercent = round(((int) $sub->score / (int) $a->max_score) * 100, 1);
                }

                $a->setAttribute('is_done', $isDone);
                $a->setAttribute('submitted_at', $sub?->submitted_at ? (string) $sub->submitted_at : null);
                $a->setAttribute('graded_at', $sub?->graded_at ? (string) $sub->graded_at : null);
                $a->setAttribute('score', $sub?->score !== null ? (int) $sub->score : null);
                $a->setAttribute('score_percent', $scorePercent);
                return $a;
            })
        );

        $quizTaken = (int) LmsQuizAttempt::query()
            ->where('user_id', $userId)
            ->whereNotNull('submitted_at')
            ->distinct('lms_quiz_id')
            ->count('lms_quiz_id');

        $quizSubmittedAttempts = (int) LmsQuizAttempt::query()
            ->where('user_id', $userId)
            ->whereNotNull('submitted_at')
            ->count();

        $quizPassedAttempts = (int) LmsQuizAttempt::query()
            ->where('user_id', $userId)
            ->whereNotNull('submitted_at')
            ->where('is_passed', true)
            ->count();

        $quizAvgPercent = (float) (LmsQuizAttempt::query()
            ->where('user_id', $userId)
            ->whereNotNull('submitted_at')
            ->selectRaw('avg(case when max_score > 0 then (score / max_score) * 100 else null end) as avg_percent')
            ->value('avg_percent') ?? 0);

        return Inertia::render('user/Lms/Index', [
            'materials' => $materials,
            'quizzes' => $quizzes,
            'assignments' => $assignments,
            'progress' => [
                'quizzes_taken' => $quizTaken,
                'quiz_submitted_attempts' => $quizSubmittedAttempts,
                'quiz_passed_attempts' => $quizPassedAttempts,
                'quiz_avg_percent' => $quizSubmittedAttempts > 0 ? round($quizAvgPercent, 1) : null,
            ],
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

        $userId = (int) auth()->id();

        $lms_material->load(['category.parent']);

        LmsMaterialRead::query()->updateOrCreate(
            [
                'lms_material_id' => $lms_material->id,
                'user_id' => $userId,
            ],
            [
                'read_at' => now(),
            ]
        );

        return Inertia::render('user/Lms/Show', [
            'material' => $lms_material,
        ]);
    }
}
