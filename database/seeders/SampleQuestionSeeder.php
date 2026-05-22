<?php

namespace Database\Seeders;

use App\Models\Answer;
use App\Models\Question;
use Illuminate\Database\Seeder;

class SampleQuestionSeeder extends Seeder
{
    public function run(): void
    {
        $question = Question::create([
            'title' => 'What is 2 + 2?',
            'description' => null,
            'question_type' => 'Selective',
            'points' => 5,
            'level_id' => 1,
            'category_id' => 1,
            'is_approve' => 1,
            'subject_id' => 1,
            'language_id' => 1,
            'created_by' => 1,
        ]);

        Answer::insert([
            ['answer' => '3', 'is_correct' => false, 'question_id' => $question->id],
            ['answer' => '4', 'is_correct' => true, 'question_id' => $question->id],
            ['answer' => '5', 'is_correct' => false, 'question_id' => $question->id],
            ['answer' => '6', 'is_correct' => false, 'question_id' => $question->id],
        ]);
    }
}
