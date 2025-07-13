<?php

namespace App\Factories;

use App\Models\Question;
use App\Models\MultipleChoiceQuestion;
use App\Models\TrueOrFalseQuestion;
use App\Models\IdentificationQuestion;
use App\Models\RankingQuestion;
use App\Models\MatchingQuestion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Str;

class QuestionFactory
{
    public static function create(array $data)
    {
        $question_data = [
            'topic_id' => (int) $data['topic'],
            'question_type' => $data['type'],
            'name' => $data['name'],
            'points' => $data['points']
        ];   

        if ($question_data['question_type'] == 'coding'){
            $coding_instruction = $data['instruction'];
            $coding_question_language_data = json_decode($data['supported_languages'], true);
        } else {
            $question_type_data = [
            'items' => $data['items'] ?? [],
            'solution' => $data['solution']
            ]; 
        }
       

        \Log::info('Question Data', $question_data);
        DB::beginTransaction();

        try {
            $question = Question::create($question_data);

            switch ($question_data['question_type']) {
                case 'multiple_choice':
                    $choice_keys = ['a','b','c','d'];
                    $question_type_data['items'] = array_combine($choice_keys,  $question_type_data['items']);
                    foreach($question_type_data['items'] as $key => $item){
                        $question->multipleChoiceQuestions()->create([
                            'choice_key' => $key,
                            'item' => $item,
                            'is_correct' => $key == $question_type_data['solution']
                        ]);
                    }   
                    DB::commit();
                    break;
                case 'true_or_false':
                    $question->trueOrFalseQuestion()->create(['solution' => $question_type_data['solution']]);
                    DB::commit();
                    break;
                case 'identification':
                    $question->identificationQuestion()->create(['solution' => $question_type_data['solution']]);
                    DB::commit();
                    break;

                case 'ranking':
                    switch ($question_type_data['solution']){
                        case 'ascending':
                            $ascending_solution = array_reverse($question_type_data['items']);

                            $order = 1;
                            foreach($ascending_solution as $item){
                            $question->rankingQuestions()->create([
                                'order' => $order, 
                                'item' => $item]);
                            $order++;

                            }      
                            DB::commit();

                            break;

                        case 'descending':
                            $order = 1;
                            foreach($question_type_data['items'] as $item){
                                $question->rankingQuestions()->create([
                                    'order' => $order, 
                                    'item' => $item]);
                                $order++;

                                } 
                                DB::commit();

                                break;       

                        default:
                        throw new \Exception("Unknown ranking solution: {$question_type_data['solution']}");
                    }
                    break;

                case 'matching':
                    foreach($question_type_data['items'] as $item){
                        \Log::info('Question Type items', [$item]);
                    }
                    // MatchingQuestion::create($data);
                    break;

                case 'coding':
                    \Log::info('Creating Coding Question Model');
                    $slug_name = Str::slug($question_data['name']);
                    $folder = "codingQuestions/{$question->id}_{$slug_name}/";
                    Storage::makeDirectory($folder);

                    $coding_question = $question->codingQuestion()->create(['instruction' => $coding_instruction]);
                    
                    \Log::info('Creating Coding Question Languages Model');
                    foreach ($coding_question_language_data as $language => $codes) {
                        $language_folder = "{$folder}supportedLanguages/{$language}/";
                        Storage::makeDirectory($language_folder);
                        $ext = self::getExtension($language);

                        $complete_solution_name = self::getClassName($codes['complete_solution'], $language);
                        $test_case_name =  self::getClassName($codes['test_case'], $language);

                        $complete_solution_file_path = "{$language_folder}{$complete_solution_name}.{$ext}";
                        $initial_solution_file_path = "{$language_folder}initial_solution.{$ext}";
                        $test_case_file_path = "{$language_folder}{$test_case_name}.{$ext}";

                        Storage::put($complete_solution_file_path, $codes['complete_solution']);
                        Storage::put($initial_solution_file_path, $codes['initial_solution']);
                        Storage::put($test_case_file_path, $codes['test_case']);

                        $coding_question->codingQuestionLanguages()->create([
                            'language' => $language,
                            'complete_solution_file_path' => $complete_solution_file_path,
                            'initial_solution_file_path' => $initial_solution_file_path,
                            'test_case_file_path' => $test_case_file_path,
                            'class_name' => $complete_solution_name,
                            'test_class_name' => $test_case_name
                        ]);
                    }

                    DB::commit();

                    break;

                default:
                    throw new \Exception("Unknown question type: {$question_data['question_type']}");
            }
        } catch (\Exception $e) {
            DB::rollBack(); 
            throw $e;
        }
    }

    public static function getClassName(string $code, string $language): string
    {
        $patterns = [
            'java' => '/\bclass\s+(\w+)/',
            'c++' => '/\b(?:class|struct)\s+(\w+)/',
            'python' => '/^[ \t]*class\s+(\w+)/m',
        ];

        $pattern = $patterns[strtolower($language)] ?? '/\bclass\s+(\w+)/';
        $class_name = 'tmp';

        if (preg_match($pattern, $code, $matches)) {
            $class_name = $matches[1];
        }

        return $class_name;
    }

    public static function getExtension(string $language): string
    {
        return match (strtolower($language)) {
            'java' => 'java',
            'python' => 'py',
            'c++' => 'cpp',
            default => 'txt',
        };
    }

}
