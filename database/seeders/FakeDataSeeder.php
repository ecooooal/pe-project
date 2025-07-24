<?php

namespace Database\Seeders;

use App\Factories\QuestionFactory as OwnQuestionFactory;
use App\Models\Course;
use App\Models\Exam;
use App\Models\Question;
use App\Models\Subject;
use App\Models\Topic;
use App\Services\QuestionTypeService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FakeDataSeeder extends Seeder
{
    protected $subjects_with_topics = [
        [
            'name' => 'Computer Programming 1',
            'Date Created' => '01/01/2025',
            'topics' => [
                [
                    'name' => 'Data Types',
                    'questions' => [
                        [
                            'name' => 'A byte is an 8-bit signed integer?',
                            'type' => 'true_or_false',
                            'points' => 1,
                            'solution' => 'true',
                            'author' => 'superAdmin',
                            'Date Created' => '01/01/2025'
                        ],
                        [
                            'name' => 'What is the name for a memory location that stores specific value, such as numbers and letters?',
                            'type' => 'identification',
                            'points' => 1,
                            'solution' => 'variable',
                            'author' => 'superAdmin',
                            'Date Created' => '01/01/2025'
                        ],
                        [
                            'name' => 'This is a memory location whose value cannot be changed during program execution.',
                            'type' => 'identification',
                            'points' => 7,
                            'solution' => 'Constant',
                            'author' => 'superAdmin',
                            'Date Created' => '01/01/2025'
                        ],
                    ],
                    'Date Created' => '01/01/2025'
                ],
                [
                    'name' => 'Programming Environments',
                    'questions' => [
                        [
                            'name' => 'What is the basic unit of a java program?',
                            'type' => 'multiple_choice',
                            'points' => 1,
                            'items' => ['Applet', 'Source Code', 'Class', 'Syntax'],
                            'solution' => 'c',
                            'author' => 'superAdmin',
                            'Date Created' => '01/01/2025'
                        ],
                        [
                            'name' => 'A reserved words or keywords can used in naming variables while retaining its original purpose.',
                            'type' => 'true_or_false',
                            'points' => 1,
                            'solution' => 'false',
                            'author' => 'superAdmin',
                            'Date Created' => '01/01/2025'
                        ],
                        [
                            'name' => 'In java environment what method does the execution always begins?',
                            'type' => 'identification',
                            'points' => 2,
                            'solution' => 'main',
                            'author' => 'superAdmin',
                            'Date Created' => '01/01/2025'
                        ],
                    ],
                    'Date Created' => '01/01/2025'
                ],
                [
                    'name' => 'Syntax and Logical Errors',
                    'questions' => [
                        [
                            'name' => 'What does it mean when syntax errors is encountered',
                            'type' => 'multiple_choice',
                            'points' => 1,
                            'items' => ['It means there is a logical error.', 'There is a grammatical mistake in the code of the program', 'There are comments in the codes.', 'The code was compiled but got unexpected output.'],
                            'solution' => 'b',
                            'author' => 'superAdmin',
                            'Date Created' => '01/01/2025'
                        ],
                        [
                            'name' => 'Logical errors can be fixed as simple as correcting grammatical mistakes in the program.',
                            'type' => 'true_or_false',
                            'points' => 1,
                            'solution' => 'false',
                            'author' => 'superAdmin',
                            'Date Created' => '01/01/2025'
                        ],
                        [
                            'name' => 'This error is encountered when the program produced unexpected result. (2 words)',
                            'type' => 'identification',
                            'points' => 2,
                            'solution' => 'logical error',
                            'author' => 'superAdmin',
                            'Date Created' => '01/01/2025'
                        ],
                    ],
                    'Date Created' => '01/01/2025'
                ],
            ],
        ],
    ];


    
    public function run(): void
    {
        $question_type_service = app(QuestionTypeService::class);
       
        foreach ($this->subjects_with_topics as $subjectData) {
            $subject = Subject::firstOrCreate([
                'name' => $subjectData['name'],
                'course_id' => 1,
                'year_level' => 1,
            ]);

            foreach ($subjectData['topics'] as $topicData) {
                $topic = Topic::firstOrCreate([
                    'name' => $topicData['name'],
                    'subject_id' => $subject->id,
                ]);


                foreach ($topicData['questions'] ?? [] as $questionData) {
                    $exists = Question::where('name', $questionData['name'])
                        ->where('topic_id', $topic->id)
                        ->exists();

                    if (! $exists) {
                        $questionData['topic'] = $topic->id;
                        $question_factory = new OwnQuestionFactory($question_type_service);
                        $question_factory->create($questionData);
                    }
                }
            }
        }
    }
}
