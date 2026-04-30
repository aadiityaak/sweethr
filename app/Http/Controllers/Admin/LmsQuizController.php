<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LmsCategory;
use App\Models\LmsQuiz;
use App\Models\LmsQuizQuestion;
use App\Models\LmsQuizAttempt;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class LmsQuizController extends Controller
{
    public function index(Request $request)
    {
        $categoryId = (int) $request->get('category', 0);
        $search = trim((string) $request->get('search', ''));

        $query = LmsQuiz::with(['category.parent'])
            ->withCount('questions')
            ->latest();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%'.$search.'%')
                    ->orWhere('description', 'like', '%'.$search.'%');
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

        $quizzes = $query->paginate(15)->withQueryString();
        $categories = LmsCategory::with('children')->whereNull('parent_id')->orderBy('name')->get(['id', 'name', 'parent_id']);

        return Inertia::render('admin/Lms/Quiz/Index', [
            'quizzes' => $quizzes,
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

        return Inertia::render('admin/Lms/Quiz/Create', [
            'categories' => $categories,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'lms_category_id' => 'required|exists:lms_categories,id',
            'time_limit_minutes' => 'nullable|integer|min:1|max:1440',
            'passing_score' => 'required|integer|min:0|max:100',
            'max_attempts' => 'required|integer|min:1|max:1000',
            'is_active' => 'boolean',
        ]);

        $quiz = LmsQuiz::create($validated);

        return redirect()->route('admin.lms-quizzes.edit', $quiz)
            ->with('success', 'Kuis berhasil dibuat. Silakan tambahkan pertanyaan.');
    }

    public function show(LmsQuiz $lms_quiz)
    {
        $lms_quiz->load(['category.parent', 'questions']);

        return Inertia::render('admin/Lms/Quiz/Show', [
            'quiz' => $lms_quiz,
        ]);
    }

    public function edit(LmsQuiz $lms_quiz)
    {
        $categories = LmsCategory::with('children')->whereNull('parent_id')->orderBy('name')->get();
        $lms_quiz->load(['category.parent', 'questions']);

        return Inertia::render('admin/Lms/Quiz/Edit', [
            'quiz' => $lms_quiz,
            'categories' => $categories,
        ]);
    }

    public function update(Request $request, LmsQuiz $lms_quiz)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'lms_category_id' => 'required|exists:lms_categories,id',
            'time_limit_minutes' => 'nullable|integer|min:1|max:1440',
            'passing_score' => 'required|integer|min:0|max:100',
            'max_attempts' => 'required|integer|min:1|max:1000',
            'is_active' => 'boolean',
        ]);

        $lms_quiz->update($validated);

        return redirect()->route('admin.lms-quizzes.edit', $lms_quiz)
            ->with('success', 'Kuis berhasil diperbarui!');
    }

    public function destroy(LmsQuiz $lms_quiz)
    {
        $lms_quiz->delete();

        return redirect()->route('admin.lms-quizzes.index')
            ->with('success', 'Kuis berhasil dihapus!');
    }

    public function attempts(Request $request, LmsQuiz $lms_quiz)
    {
        $query = LmsQuizAttempt::with(['user:id,name'])
            ->where('lms_quiz_id', $lms_quiz->id)
            ->latest('submitted_at');

        if ($request->search) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->search.'%');
            });
        }

        $attempts = $query->paginate(15)->withQueryString();
        $lms_quiz->load(['category.parent']);

        return Inertia::render('admin/Lms/QuizAttempt/Index', [
            'quiz' => $lms_quiz,
            'attempts' => $attempts,
            'filters' => $request->only(['search']),
        ]);
    }

    public function storeQuestion(Request $request, LmsQuiz $lms_quiz)
    {
        $validated = $request->validate([
            'type' => 'required|in:multiple_choice,true_false,short_answer',
            'question' => 'required|string',
            'points' => 'required|integer|min:1|max:1000',
            'order' => 'nullable|integer|min:0|max:100000',
            'is_active' => 'boolean',
            'options' => 'nullable|array',
            'options.*' => 'nullable|string|max:255',
            'correct_option_index' => 'nullable|integer|min:0|max:20',
            'correct_true_false' => 'nullable|boolean',
            'correct_short_answer' => 'nullable|string|max:255',
        ]);

        [$options, $correctAnswer] = $this->normalizeQuestionPayload($validated);

        LmsQuizQuestion::create([
            'lms_quiz_id' => $lms_quiz->id,
            'type' => $validated['type'],
            'question' => $validated['question'],
            'points' => $validated['points'],
            'order' => $validated['order'] ?? 0,
            'is_active' => $validated['is_active'] ?? true,
            'options' => $options,
            'correct_answer' => $correctAnswer,
        ]);

        return redirect()->route('admin.lms-quizzes.edit', $lms_quiz)
            ->with('success', 'Pertanyaan berhasil ditambahkan!');
    }

    public function updateQuestion(Request $request, LmsQuiz $lms_quiz, LmsQuizQuestion $question)
    {
        if ((int) $question->lms_quiz_id !== (int) $lms_quiz->id) {
            abort(404);
        }

        $validated = $request->validate([
            'type' => 'required|in:multiple_choice,true_false,short_answer',
            'question' => 'required|string',
            'points' => 'required|integer|min:1|max:1000',
            'order' => 'nullable|integer|min:0|max:100000',
            'is_active' => 'boolean',
            'options' => 'nullable|array',
            'options.*' => 'nullable|string|max:255',
            'correct_option_index' => 'nullable|integer|min:0|max:20',
            'correct_true_false' => 'nullable|boolean',
            'correct_short_answer' => 'nullable|string|max:255',
        ]);

        [$options, $correctAnswer] = $this->normalizeQuestionPayload($validated);

        $question->update([
            'type' => $validated['type'],
            'question' => $validated['question'],
            'points' => $validated['points'],
            'order' => $validated['order'] ?? 0,
            'is_active' => $validated['is_active'] ?? true,
            'options' => $options,
            'correct_answer' => $correctAnswer,
        ]);

        return redirect()->route('admin.lms-quizzes.edit', $lms_quiz)
            ->with('success', 'Pertanyaan berhasil diperbarui!');
    }

    public function destroyQuestion(LmsQuiz $lms_quiz, LmsQuizQuestion $question)
    {
        if ((int) $question->lms_quiz_id !== (int) $lms_quiz->id) {
            abort(404);
        }

        $question->delete();

        return redirect()->route('admin.lms-quizzes.edit', $lms_quiz)
            ->with('success', 'Pertanyaan berhasil dihapus!');
    }

    private function normalizeQuestionPayload(array $validated): array
    {
        $type = $validated['type'];

        if ($type === 'multiple_choice') {
            $rawOptions = $validated['options'] ?? [];
            $options = collect($rawOptions)
                ->map(fn ($v) => is_string($v) ? trim($v) : '')
                ->filter(fn ($v) => $v !== '')
                ->values()
                ->all();

            if (count($options) < 2) {
                throw ValidationException::withMessages([
                    'options' => 'Opsi minimal 2 untuk pilihan ganda.',
                ]);
            }

            $index = $validated['correct_option_index'];
            if (! is_int($index) || $index < 0 || $index >= count($options)) {
                throw ValidationException::withMessages([
                    'correct_option_index' => 'Jawaban benar pilihan ganda tidak valid.',
                ]);
            }

            return [$options, ['value' => $index]];
        }

        if ($type === 'true_false') {
            if (! array_key_exists('correct_true_false', $validated)) {
                throw ValidationException::withMessages([
                    'correct_true_false' => 'Jawaban benar wajib diisi untuk Benar/Salah.',
                ]);
            }

            return [null, ['value' => (bool) $validated['correct_true_false']]];
        }

        $answer = isset($validated['correct_short_answer']) ? trim((string) $validated['correct_short_answer']) : '';

        return [null, $answer !== '' ? ['value' => $answer] : null];
    }
}
