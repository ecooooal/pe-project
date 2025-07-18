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
                    $order = 1;
                    foreach ($question_type_data['items'] as $item) {
                        $question->rankingQuestions()->create([
                            'order' => $order,
                            'item' => $item
                        ]);
                        $order++;
                    }
                    DB::commit();
                    break;       

                case 'matching':
                    foreach($question_type_data['items'] as $item){
                        $question->matchingQuestions()->create([
                            'first_item' => $item['left'],
                            'second_item' => $item['right']
                        ]);                    
                    }
                    DB::commit();
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

    public static function update(Question $question, array $data)
    {
        DB::beginTransaction();
        try {
            $previous_question_type = $question->question_type->value;

            switch ($data['type']) {
                case 'multiple_choice':
                    Self::prepareUpdateQuestion($question);
                    $choice_keys = ['a', 'b', 'c', 'd'];
                    $items = array_combine($choice_keys, $data['items']);
                    foreach ($items as $key => $item) {
                        $question->multipleChoiceQuestions()->create([
                            'choice_key' => $key,
                            'item' => $item,
                            'is_correct' => $key == $data['solution']
                        ]);
                    }
                    break;
                    
                case 'true_or_false':
                    if ($previous_question_type != $data['type']){
                        Self::prepareUpdateQuestion($question);
                    }
                    $question->trueOrFalseQuestion()->updateOrCreate(['solution' => $data['solution']]);
                    break;

                case 'identification':
                    if ($previous_question_type != $data['type']){
                        Self::prepareUpdateQuestion($question);
                    }
                    $question->identificationQuestion()->updateOrCreate(['solution' => $data['solution']]);
                    break;

                case 'ranking':
                    Self::prepareUpdateQuestion($question);
                    $order = 1;
                    foreach ($data['items'] as $item) {
                        $question->rankingQuestions()->create([
                            'order' => $order,
                            'item' => $item
                        ]);
                        $order++;
                    }
                    break;       

                case 'matching':
                    Self::prepareUpdateQuestion($question);

                    foreach($data['items'] as $item){
                        $question->matchingQuestions()->create([
                            'first_item' => $item['left'],
                            'second_item' => $item['right']
                        ]);                    
                    }
                    break;

                case 'coding':
                    Self::prepareUpdateQuestion($question);
                    $instruction = $data['instruction'];
                    $language_data = json_decode($data['supported_languages'], true);
                    $slug_name = Str::slug($data['name']);
                    $folder = "codingQuestions/{$question->id}_{$slug_name}/";
                    Storage::makeDirectory($folder);

                    $coding_question = $question->codingQuestion()->create(['instruction' => $instruction]);

                    foreach ($language_data as $language => $codes) {
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
                    break;
                default:
                    throw new \Exception("Unsupported question type: {$data['type']}");
            }
            $question->update([
                'topic_id' => (int) $data['topic'],
                'question_type' => $data['type'],
                'name' => $data['name'],
                'points' => $data['points']
            ]);
            DB::commit();
            return $question;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

    }

    private static function prepareUpdateQuestion(Question $question)
    {
        switch ($question->question_type->value) {
            case 'multiple_choice':
                $question->multipleChoiceQuestions()->delete();
                break;
                
            case 'true_or_false':
                $question->trueOrFalseQuestion()->delete();
                break;

            case 'identification':
                $question->identificationQuestion()->delete();
                break;

            case 'ranking':
                $question->rankingQuestions()->delete();
                break;

            case 'matching':
                $question->matchingQuestions()->delete();
                break;

            case 'coding':
                if ($question->codingQuestion) {
                    $question->codingQuestion->codingQuestionLanguages()->delete();
                    $question->codingQuestion()->delete(); 
                    $old_slug_name = Str::slug($question['name']);
                    $old_folder = "codingQuestions/{$question->id}_{$old_slug_name}/";
                    Storage::deleteDirectory($old_folder);
                    }
                break;

            default:
            throw new \Exception("Unsupported question type: {$question->question_type->value}");
        }
    }
    private static function getClassName(string $code, string $language): string
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

    private static function getExtension(string $language): string
    {
        return match (strtolower($language)) {
            'java' => 'java',
            'python' => 'py',
            'c++' => 'cpp',
            default => 'txt',
        };
    }

}
