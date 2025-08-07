<?php

namespace Database\Seeders;

use App\Factories\QuestionFactory as OwnQuestionFactory;
use App\Models\Course;
use App\Models\Exam;
use App\Models\Question;
use App\Models\Subject;
use App\Models\Topic;
use App\Models\User;
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
                            'solution' => 'true'
                        ],
                        [
                            'name' => 'What is the name for a memory location that stores specific value, such as numbers and letters?',
                            'type' => 'identification',
                            'points' => 1,
                            'solution' => 'variable'
                        ],
                        [
                            'name' => 'This is a memory location whose value cannot be changed during program execution.',
                            'type' => 'identification',
                            'points' => 7,
                            'solution' => 'Constant'
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
                            'solution' => 'c'
                        ],
                        [
                            'name' => 'A reserved words or keywords can used in naming variables while retaining its original purpose.',
                            'type' => 'true_or_false',
                            'points' => 1,
                            'solution' => 'false'
                        ],
                        [
                            'name' => 'In java environment what method does the execution always begins?',
                            'type' => 'identification',
                            'points' => 2,
                            'solution' => 'main'
                        ],
                        [
                            'name' => 'In descending order, rank the flow on how a java program is created',
                            'type' => 'ranking',
                            'items' => [
                                1 => [
                                    "solution" => "Code is written",
                                    "points" => "2"
                                ],
                                2 => [
                                    "solution" => "Code is compiled",
                                    "points" => "2"
                                ],
                                3 => [
                                    "solution" => "Code is run",
                                    "points" => "2"
                                ],
                                4 => [
                                    "solution" => "terminate if the code has syntax errors",
                                    "points" => "2"
                                ],
                                5 => [
                                    "solution" => "Check if the output is not unexpected",
                                    "points" => "2"
                                ]
                            ]
                        ]
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
                            'solution' => 'b'
                        ],
                        [
                            'name' => 'Logical errors can be fixed as simple as correcting grammatical mistakes in the program.',
                            'type' => 'true_or_false',
                            'points' => 1,
                            'solution' => 'false'
                        ],
                        [
                            'name' => 'This error is encountered when the program produced unexpected result. (2 words)',
                            'type' => 'identification',
                            'points' => 2,
                            'solution' => 'logical error'
                        ],
                        [
                            'name' => 'Match The the following errors:',
                            'type' => 'matching',
                            'items' => [
                                1 => [
                                    "left" => "syntax error",
                                    "right" => "error on grammar",
                                    "points" => "2"
                                ],
                                2 => [
                                    "left" => "logical error",
                                    "right" => "unexpected output by the program",
                                    "points" => "2"
                                ],
                                3 => [
                                    "left" => "test case error",
                                    "right" => "failure in test cases",
                                    "points" => "5"
                                ]
                            ]
                        ]
                    ],
                    'Date Created' => '01/01/2025'
                ],
            ],
        ],
    ];


    
    public function run(): void
    {
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
                        $super_admin_id = User::role('super_admin')->first()->id;
                        $question_factory = new OwnQuestionFactory();
                        $question_factory->createFakeData($questionData, $super_admin_id);
                    }
                }
            }
        }
    }
}
