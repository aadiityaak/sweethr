<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LmsAssignment;
use App\Models\LmsAssignmentSubmission;
use App\Models\LmsMaterial;
use App\Models\LmsQuiz;
use App\Models\LmsQuizAttempt;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LmsTrackingController extends Controller
{
    public function index(Request $request): Response
    {
        $search = trim((string) $request->get('search', ''));

        $users = User::query()
            ->select(['id', 'name', 'employee_id'])
            ->where('is_admin', false)
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('name', 'like', '%'.$search.'%')
                        ->orWhere('employee_id', 'like', '%'.$search.'%');
                });
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $userIds = $users->getCollection()->pluck('id')->all();

        $quizAgg = collect();
        $assignmentAgg = collect();

        if (count($userIds)) {
            $quizAgg = LmsQuizAttempt::query()
                ->selectRaw('user_id')
                ->selectRaw('count(*) as attempts')
                ->selectRaw('sum(case when submitted_at is not null then 1 else 0 end) as submitted')
                ->selectRaw('sum(case when is_passed = 1 then 1 else 0 end) as passed')
                ->selectRaw('avg(case when submitted_at is not null and max_score > 0 then (score / max_score) * 100 else null end) as avg_percent')
                ->selectRaw('max(submitted_at) as last_submitted_at')
                ->whereIn('user_id', $userIds)
                ->groupBy('user_id')
                ->get()
                ->keyBy('user_id');

            $assignmentAgg = LmsAssignmentSubmission::query()
                ->join('lms_assignments', 'lms_assignments.id', '=', 'lms_assignment_submissions.lms_assignment_id')
                ->whereIn('lms_assignment_submissions.user_id', $userIds)
                ->selectRaw('lms_assignment_submissions.user_id as user_id')
                ->selectRaw('count(*) as submissions')
                ->selectRaw('sum(case when lms_assignment_submissions.graded_at is not null then 1 else 0 end) as graded')
                ->selectRaw('avg(case when lms_assignment_submissions.score is not null and lms_assignments.max_score > 0 then (lms_assignment_submissions.score / lms_assignments.max_score) * 100 else null end) as avg_percent')
                ->selectRaw('max(lms_assignment_submissions.submitted_at) as last_submitted_at')
                ->groupBy('lms_assignment_submissions.user_id')
                ->get()
                ->keyBy('user_id');
        }

        $users->setCollection(
            $users->getCollection()->map(function ($u) use ($quizAgg, $assignmentAgg) {
                $quiz = $quizAgg->get($u->id);
                $asg = $assignmentAgg->get($u->id);

                $quizAvg = $quiz?->avg_percent !== null ? round((float) $quiz->avg_percent, 1) : null;
                $asgAvg = $asg?->avg_percent !== null ? round((float) $asg->avg_percent, 1) : null;

                $quizLast = $quiz?->last_submitted_at ? (string) $quiz->last_submitted_at : null;
                $asgLast = $asg?->last_submitted_at ? (string) $asg->last_submitted_at : null;

                $lastActivity = null;
                if ($quizLast && $asgLast) {
                    $lastActivity = $quizLast > $asgLast ? $quizLast : $asgLast;
                } else {
                    $lastActivity = $quizLast ?: $asgLast;
                }

                return [
                    'id' => $u->id,
                    'name' => $u->name,
                    'employee_id' => $u->employee_id,
                    'quiz' => [
                        'attempts' => (int) ($quiz?->attempts ?? 0),
                        'submitted' => (int) ($quiz?->submitted ?? 0),
                        'passed' => (int) ($quiz?->passed ?? 0),
                        'avg_percent' => $quizAvg,
                        'last_submitted_at' => $quizLast,
                    ],
                    'assignment' => [
                        'submissions' => (int) ($asg?->submissions ?? 0),
                        'graded' => (int) ($asg?->graded ?? 0),
                        'avg_percent' => $asgAvg,
                        'last_submitted_at' => $asgLast,
                    ],
                    'last_activity_at' => $lastActivity,
                ];
            })
        );

        $quizSubmitted = (int) LmsQuizAttempt::query()->whereNotNull('submitted_at')->count();
        $quizPassed = (int) LmsQuizAttempt::query()->whereNotNull('submitted_at')->where('is_passed', true)->count();

        $assignmentSubmitted = (int) LmsAssignmentSubmission::query()->whereNotNull('submitted_at')->count();
        $assignmentPending = (int) LmsAssignmentSubmission::query()->whereNotNull('submitted_at')->whereNull('graded_at')->count();

        $stats = [
            'employees' => (int) User::query()->where('is_admin', false)->count(),
            'materials_active' => (int) LmsMaterial::query()->where('is_active', true)->count(),
            'quizzes_active' => (int) LmsQuiz::query()->where('is_active', true)->count(),
            'assignments_active' => (int) LmsAssignment::query()->where('is_active', true)->count(),
            'quiz_submitted' => $quizSubmitted,
            'quiz_pass_rate' => $quizSubmitted > 0 ? round(($quizPassed / $quizSubmitted) * 100, 1) : 0,
            'assignment_submitted' => $assignmentSubmitted,
            'assignment_pending_grading' => $assignmentPending,
        ];

        return Inertia::render('admin/Lms/Tracking/Index', [
            'stats' => $stats,
            'users' => $users,
            'filters' => [
                'search' => $search ?: null,
            ],
        ]);
    }
}
