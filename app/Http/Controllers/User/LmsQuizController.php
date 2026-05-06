<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\LmsQuiz;
use App\Models\LmsQuizAttempt;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class LmsQuizController extends Controller
{
    public function index(): Response
    {
        $user = auth()->user();

        $quizzes = LmsQuiz::with(['category.parent'])
            ->withCount(['questions' => fn ($q) => $q->where('is_active', true)])
            ->where('is_active', true)
            ->whereHas('category', fn ($q) => $q->where('is_active', true)->visibleForUser($user))
            ->latest()
            ->paginate(10);

        return Inertia::render('user/Lms/Quiz/Index', [
            'quizzes' => $quizzes,
        ]);
    }

    public function show(LmsQuiz $lms_quiz): Response
    {
        if (! $lms_quiz->is_active) {
            abort(404);
        }

        $this->ensureQuizVisible($lms_quiz);

        $userId = (int) auth()->id();

        $lms_quiz->load(['category.parent']);
        $lms_quiz->loadCount(['questions' => fn ($q) => $q->where('is_active', true)]);

        $inProgress = LmsQuizAttempt::query()
            ->where('lms_quiz_id', $lms_quiz->id)
            ->where('user_id', $userId)
            ->whereNull('submitted_at')
            ->latest('created_at')
            ->first();

        $attempts = LmsQuizAttempt::query()
            ->where('lms_quiz_id', $lms_quiz->id)
            ->where('user_id', $userId)
            ->whereNotNull('submitted_at')
            ->latest('submitted_at')
            ->limit(10)
            ->get();

        $submittedAttempts = (int) LmsQuizAttempt::query()
            ->where('lms_quiz_id', $lms_quiz->id)
            ->where('user_id', $userId)
            ->whereNotNull('submitted_at')
            ->count();

        $maxAttempts = max(1, (int) ($lms_quiz->max_attempts ?? 1));
        $remainingAttempts = max(0, $maxAttempts - $submittedAttempts);

        return Inertia::render('user/Lms/Quiz/Show', [
            'quiz' => $lms_quiz,
            'inProgressAttempt' => $inProgress,
            'attempts' => $attempts,
            'attemptStats' => [
                'max_attempts' => $maxAttempts,
                'submitted_attempts' => $submittedAttempts,
                'remaining_attempts' => $remainingAttempts,
            ],
        ]);
    }

    public function start(LmsQuiz $lms_quiz): RedirectResponse
    {
        if (! $lms_quiz->is_active) {
            abort(404);
        }

        $this->ensureQuizVisible($lms_quiz);

        $userId = (int) auth()->id();

        $existing = LmsQuizAttempt::query()
            ->where('lms_quiz_id', $lms_quiz->id)
            ->where('user_id', $userId)
            ->whereNull('submitted_at')
            ->latest('created_at')
            ->first();

        if ($existing) {
            return redirect()->route('user.lms-quizzes.attempts.take', [$lms_quiz, $existing]);
        }

        $submittedAttempts = (int) LmsQuizAttempt::query()
            ->where('lms_quiz_id', $lms_quiz->id)
            ->where('user_id', $userId)
            ->whereNotNull('submitted_at')
            ->count();

        $maxAttempts = max(1, (int) ($lms_quiz->max_attempts ?? 1));
        if ($submittedAttempts >= $maxAttempts) {
            return redirect()->route('user.lms-quizzes.show', $lms_quiz)
                ->with('error', 'Max attempt sudah tercapai untuk kuis ini.');
        }

        $maxScore = (int) $lms_quiz->questions()->where('is_active', true)->sum('points');

        $attempt = LmsQuizAttempt::create([
            'lms_quiz_id' => $lms_quiz->id,
            'user_id' => $userId,
            'started_at' => now(),
            'max_score' => $maxScore,
        ]);

        return redirect()->route('user.lms-quizzes.attempts.take', [$lms_quiz, $attempt]);
    }

    public function take(LmsQuiz $lms_quiz, LmsQuizAttempt $attempt): Response
    {
        $this->ensureAttemptAccess($lms_quiz, $attempt);

        if ($attempt->submitted_at) {
            return Inertia::render('user/Lms/Quiz/Result', $this->resultProps($lms_quiz, $attempt));
        }

        if (! $attempt->started_at) {
            $attempt->update(['started_at' => now()]);
        }

        $lms_quiz->load(['category.parent']);
        $questions = $lms_quiz->questions()
            ->where('is_active', true)
            ->orderBy('order')
            ->get(['id', 'type', 'question', 'options', 'points', 'order']);

        return Inertia::render('user/Lms/Quiz/Take', [
            'quiz' => $lms_quiz,
            'attempt' => $attempt->only(['id', 'started_at', 'submitted_at', 'answers']),
            'questions' => $questions,
        ]);
    }

    public function submit(Request $request, LmsQuiz $lms_quiz, LmsQuizAttempt $attempt): RedirectResponse
    {
        $this->ensureAttemptAccess($lms_quiz, $attempt);

        if ($attempt->submitted_at) {
            return redirect()->route('user.lms-quizzes.attempts.show', [$lms_quiz, $attempt]);
        }

        $validated = $request->validate([
            'answers' => 'nullable|array',
        ]);

        $answersInput = $validated['answers'] ?? [];

        $questions = $lms_quiz->questions()
            ->where('is_active', true)
            ->orderBy('order')
            ->get(['id', 'type', 'options', 'correct_answer', 'points']);

        $normalizedAnswers = [];
        $score = 0;
        $maxScore = 0;

        foreach ($questions as $q) {
            $maxScore += (int) $q->points;
            $value = $answersInput[$q->id] ?? null;
            $normalizedAnswers[(string) $q->id] = $value;

            if ($this->isCorrectAnswer($q->type, $q->correct_answer, $value)) {
                $score += (int) $q->points;
            }
        }

        $percentage = $maxScore > 0 ? (int) round(($score / $maxScore) * 100) : 0;
        $isPassed = $percentage >= (int) $lms_quiz->passing_score;

        DB::transaction(function () use ($attempt, $normalizedAnswers, $score, $maxScore, $isPassed) {
            $attempt->update([
                'submitted_at' => now(),
                'score' => $score,
                'max_score' => $maxScore,
                'is_passed' => $isPassed,
                'answers' => $normalizedAnswers,
            ]);
        });

        return redirect()->route('user.lms-quizzes.attempts.show', [$lms_quiz, $attempt]);
    }

    public function result(LmsQuiz $lms_quiz, LmsQuizAttempt $attempt): Response
    {
        $this->ensureAttemptAccess($lms_quiz, $attempt);

        return Inertia::render('user/Lms/Quiz/Result', $this->resultProps($lms_quiz, $attempt));
    }

    private function ensureAttemptAccess(LmsQuiz $lms_quiz, LmsQuizAttempt $attempt): void
    {
        $userId = (int) auth()->id();

        if ((int) $attempt->user_id !== $userId) {
            abort(403);
        }

        if ((int) $attempt->lms_quiz_id !== (int) $lms_quiz->id) {
            abort(404);
        }

        if (! $lms_quiz->is_active) {
            abort(404);
        }

        $this->ensureQuizVisible($lms_quiz);
    }

    private function ensureQuizVisible(LmsQuiz $lms_quiz): void
    {
        $user = auth()->user();

        $lms_quiz->loadMissing(['category']);
        if (! $lms_quiz->category || ! $lms_quiz->category->is_active) {
            abort(404);
        }
        if (! $lms_quiz->category->isVisibleToUser($user)) {
            abort(404);
        }
    }

    private function resultProps(LmsQuiz $lms_quiz, LmsQuizAttempt $attempt): array
    {
        $lms_quiz->load(['category.parent']);

        $questions = $lms_quiz->questions()
            ->where('is_active', true)
            ->orderBy('order')
            ->get(['id', 'type', 'question', 'options', 'correct_answer', 'points', 'order']);

        $answers = $attempt->answers ?? [];
        $items = $questions->map(function ($q) use ($answers) {
            $userAnswer = $answers[(string) $q->id] ?? null;
            $isCorrect = $this->isCorrectAnswer($q->type, $q->correct_answer, $userAnswer);

            return [
                'id' => $q->id,
                'type' => $q->type,
                'question' => $q->question,
                'options' => $q->options,
                'correct_answer' => $q->correct_answer,
                'points' => $q->points,
                'user_answer' => $userAnswer,
                'is_correct' => $isCorrect,
            ];
        });

        return [
            'quiz' => $lms_quiz,
            'attempt' => $attempt->only(['id', 'started_at', 'submitted_at', 'score', 'max_score', 'is_passed']),
            'items' => $items,
        ];
    }

    private function isCorrectAnswer(string $type, $correctAnswer, $value): bool
    {
        $correctValue = is_array($correctAnswer) ? ($correctAnswer['value'] ?? null) : null;

        if ($type === 'multiple_choice') {
            if ($value === null || $correctValue === null) return false;
            if (! is_numeric($value) || ! is_numeric($correctValue)) return false;
            return (int) $value === (int) $correctValue;
        }

        if ($type === 'true_false') {
            if ($correctValue === null) return false;
            $userBool = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($userBool === null) return false;
            return (bool) $correctValue === $userBool;
        }

        $expected = is_string($correctValue) ? trim($correctValue) : '';
        if ($expected === '' || $value === null) return false;
        $given = trim((string) $value);
        if ($given === '') return false;

        return mb_strtolower($expected) === mb_strtolower($given);
    }
}
