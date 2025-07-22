<?php

namespace Database\Seeders;

use App\Factories\QuestionFactory as OwnQuestionFactory;
use App\Models\Course;
use App\Models\Exam;
use App\Models\Question;
use App\Models\Subject;
use App\Models\Topic;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FakeDataSeeder extends Seeder
{

//     public function run()
// {
// $subjects_with_topics = [
//     [
//         'name' => 'Introduction to Computer Science',
//         'topics' => [
//             [
//                 'name' => 'Basic computer hardware and software',
//                 'questions' => [
//                     [
//                         'name' => 'What is the function of a CPU?',
//                         'type_of_a_question' => ['multiple_choice'],
//                         'points' => 5
//                     ],
//                     [
//                         'name' => 'Explain the use of RAM in computing.',
//                         'type_of_a_question' => ['short_answer'],
//                         'points' => 10
//                     ]
//                 ]
//             ],
//             // Add more topics...
//         ]
//     ],
//     // Add more subjects...
// ];

    // foreach ($subjects_with_topics as $subjectData) {
    //     $subject = \App\Models\Subject::create([
    //         'name' => $subjectData['name']
    //     ]);

    //     foreach ($subjectData['topics'] as $topicData) {
    //         $topic = $subject->topics()->create([
    //             'name' => $topicData['name']
    //         ]);

    //         foreach ($topicData['questions'] ?? [] as $questionData) {
    //             $topic->questions()->create([
    //                 'name' => $questionData['name'],
    //                 'type_of_a_question' => json_encode($questionData['type_of_a_question']),
    //                 'points' => $questionData['points']
    //             ]);
    //         }
    //     }
    // }
// }

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
                        OwnQuestionFactory::create($questionData);
                    }
                }
            }
        }
    }
}
