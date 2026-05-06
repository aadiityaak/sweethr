<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\LmsAssignment;
use App\Models\LmsAssignmentSubmission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class LmsAssignmentController extends Controller
{
    public function index(): Response
    {
        $user = auth()->user();

        $assignments = LmsAssignment::with(['category.parent'])
            ->where('is_active', true)
            ->whereHas('category', fn ($q) => $q->where('is_active', true)->visibleForUser($user))
            ->latest()
            ->paginate(10);

        return Inertia::render('user/Lms/Assignment/Index', [
            'assignments' => $assignments,
        ]);
    }

    public function show(LmsAssignment $lms_assignment): Response
    {
        if (! $lms_assignment->is_active) {
            abort(404);
        }

        $lms_assignment->load(['category.parent']);
        if (! $lms_assignment->category || ! $lms_assignment->category->is_active) {
            abort(404);
        }
        if (! $lms_assignment->category->isVisibleToUser(auth()->user())) {
            abort(404);
        }

        $userId = (int) auth()->id();

        $submission = LmsAssignmentSubmission::query()
            ->where('lms_assignment_id', $lms_assignment->id)
            ->where('user_id', $userId)
            ->latest('submitted_at')
            ->first();

        $submittedAttempts = (int) LmsAssignmentSubmission::query()
            ->where('lms_assignment_id', $lms_assignment->id)
            ->where('user_id', $userId)
            ->whereNotNull('submitted_at')
            ->count();

        $maxAttempts = max(1, (int) ($lms_assignment->max_attempts ?? 1));
        $remainingAttempts = max(0, $maxAttempts - $submittedAttempts);

        return Inertia::render('user/Lms/Assignment/Show', [
            'assignment' => $lms_assignment,
            'submission' => $submission,
            'attemptStats' => [
                'max_attempts' => $maxAttempts,
                'submitted_attempts' => $submittedAttempts,
                'remaining_attempts' => $remainingAttempts,
            ],
        ]);
    }

    public function submit(Request $request, LmsAssignment $lms_assignment): RedirectResponse
    {
        if (! $lms_assignment->is_active) {
            abort(404);
        }

        $lms_assignment->load(['category']);
        if (! $lms_assignment->category || ! $lms_assignment->category->is_active) {
            abort(404);
        }
        if (! $lms_assignment->category->isVisibleToUser(auth()->user())) {
            abort(404);
        }

        $validated = $request->validate([
            'content' => 'nullable|string',
            'attachment' => 'nullable|file|max:10240',
        ]);

        $userId = (int) auth()->id();

        $submittedAttempts = (int) LmsAssignmentSubmission::query()
            ->where('lms_assignment_id', $lms_assignment->id)
            ->where('user_id', $userId)
            ->whereNotNull('submitted_at')
            ->count();

        $maxAttempts = max(1, (int) ($lms_assignment->max_attempts ?? 1));
        if ($submittedAttempts >= $maxAttempts) {
            return redirect()->route('user.lms-assignments.show', $lms_assignment)
                ->with('error', 'Max attempt sudah tercapai untuk tugas ini.');
        }

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $attachmentPath = $file->store('lms/assignments/submissions', 'public');
        }

        DB::transaction(function () use ($lms_assignment, $userId, $validated, $attachmentPath) {
            LmsAssignmentSubmission::create([
                'lms_assignment_id' => $lms_assignment->id,
                'user_id' => $userId,
                'content' => $validated['content'] ?? null,
                'attachment_path' => $attachmentPath,
                'submitted_at' => now(),
            ]);
        });

        return redirect()->route('user.lms-assignments.show', $lms_assignment)
            ->with('success', 'Tugas berhasil dikumpulkan.');
    }
}
