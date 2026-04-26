<?php

namespace Database\Seeders;

use App\Models\LmsCategory;
use App\Models\LmsQuiz;
use App\Models\LmsQuizQuestion;
use Illuminate\Database\Seeder;

class LmsQuizSeeder extends Seeder
{
    public function run(): void
    {
        $leafCategories = LmsCategory::query()
            ->whereNotNull('parent_id')
            ->orderBy('id')
            ->get();

        if ($leafCategories->isEmpty()) {
            $fallback = LmsCategory::firstOrCreate(
                ['parent_id' => null, 'name' => 'Modul'],
                ['is_active' => true],
            );

            $leafCategories = collect([$fallback]);
        }

        $quizzes = [
            [
                'title' => 'Kuis Hygiene & Sanitasi',
                'description' => '<p>Uji pemahaman tentang standar kebersihan dapur.</p>',
                'time_limit_minutes' => 10,
                'passing_score' => 70,
                'questions' => [
                    [
                        'type' => 'multiple_choice',
                        'question' => '<p>Tujuan utama sanitasi peralatan adalah…</p>',
                        'options' => ['Membuat peralatan terlihat mengkilap', 'Mengurangi risiko kontaminasi', 'Menghemat waktu kerja', 'Meningkatkan rasa makanan'],
                        'correct_answer' => ['value' => 1],
                        'points' => 2,
                    ],
                    [
                        'type' => 'true_false',
                        'question' => '<p>Mencuci tangan sebelum memegang makanan adalah bagian dari personal hygiene.</p>',
                        'options' => null,
                        'correct_answer' => ['value' => true],
                        'points' => 1,
                    ],
                    [
                        'type' => 'short_answer',
                        'question' => '<p>Sebutkan 1 contoh tindakan untuk mencegah cross-contamination.</p>',
                        'options' => null,
                        'correct_answer' => ['value' => 'Pisahkan talenan mentah dan matang'],
                        'points' => 2,
                    ],
                ],
            ],
            [
                'title' => 'Kuis Service Excellence',
                'description' => '<p>Uji pemahaman komunikasi dan pelayanan customer.</p>',
                'time_limit_minutes' => 10,
                'passing_score' => 70,
                'questions' => [
                    [
                        'type' => 'multiple_choice',
                        'question' => '<p>Langkah pertama saat menerima komplain adalah…</p>',
                        'options' => ['Menyalahkan customer', 'Memotong pembicaraan', 'Mendengarkan dan menunjukkan empati', 'Langsung memberi diskon'],
                        'correct_answer' => ['value' => 2],
                        'points' => 2,
                    ],
                    [
                        'type' => 'true_false',
                        'question' => '<p>Upselling sebaiknya dilakukan dengan memaksa customer.</p>',
                        'options' => null,
                        'correct_answer' => ['value' => false],
                        'points' => 1,
                    ],
                ],
            ],
        ];

        foreach ($quizzes as $index => $quizData) {
            $categoryId = $leafCategories[$index % $leafCategories->count()]->id;

            $quiz = LmsQuiz::updateOrCreate(
                ['title' => $quizData['title']],
                [
                    'lms_category_id' => $categoryId,
                    'description' => $quizData['description'],
                    'time_limit_minutes' => $quizData['time_limit_minutes'],
                    'passing_score' => $quizData['passing_score'],
                    'is_active' => true,
                ],
            );

            foreach ($quizData['questions'] as $order => $questionData) {
                LmsQuizQuestion::updateOrCreate(
                    [
                        'lms_quiz_id' => $quiz->id,
                        'order' => $order,
                    ],
                    [
                        'type' => $questionData['type'],
                        'question' => $questionData['question'],
                        'options' => $questionData['options'],
                        'correct_answer' => $questionData['correct_answer'],
                        'points' => $questionData['points'],
                        'is_active' => true,
                    ],
                );
            }
        }
    }
}
