<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\LmsAssignment;
use App\Models\LmsAssignmentSubmission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class LmsAssignmentController extends Controller
{
    public function index(): Response
    {
        $assignments = LmsAssignment::with(['category.parent'])
            ->where('is_active', true)
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

        $submission = LmsAssignmentSubmission::query()
            ->where('lms_assignment_id', $lms_assignment->id)
            ->where('user_id', (int) auth()->id())
            ->latest('submitted_at')
            ->first();

        return Inertia::render('user/Lms/Assignment/Show', [
            'assignment' => $lms_assignment,
            'submission' => $submission,
        ]);
    }

    public function submit(Request $request, LmsAssignment $lms_assignment): RedirectResponse
    {
        if (! $lms_assignment->is_active) {
            abort(404);
        }

        $validated = $request->validate([
            'content' => 'nullable|string',
            'attachment' => 'nullable|file|max:10240',
        ]);

        $userId = (int) auth()->id();

        $existing = LmsAssignmentSubmission::query()
            ->where('lms_assignment_id', $lms_assignment->id)
            ->where('user_id', $userId)
            ->latest('submitted_at')
            ->first();

        $attachmentPath = $existing?->attachment_path;
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $attachmentPath = $file->store('lms/assignments/submissions', 'public');
        }

        DB::transaction(function () use ($existing, $lms_assignment, $userId, $validated, $attachmentPath) {
            $submission = $existing ?? new LmsAssignmentSubmission([
                'lms_assignment_id' => $lms_assignment->id,
                'user_id' => $userId,
            ]);

            $oldAttachment = $submission->attachment_path;

            $submission->fill([
                'content' => $validated['content'] ?? null,
                'attachment_path' => $attachmentPath,
                'submitted_at' => now(),
            ]);

            $submission->save();

            if ($oldAttachment && $oldAttachment !== $attachmentPath) {
                Storage::disk('public')->delete($oldAttachment);
            }
        });

        return redirect()->route('user.lms-assignments.show', $lms_assignment)
            ->with('success', 'Tugas berhasil dikumpulkan.');
    }
}

