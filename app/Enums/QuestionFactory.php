<?php

namespace App\Factories;

use App\Models\Question;
use App\Models\MultipleChoiceQuestion;
use App\Models\TrueOrFalseQuestion;
use App\Models\IdentificationQuestion;
use App\Models\RankingQuestion;
use App\Models\MatchingQuestion;

class QuestionFactory
{
    public static function create(string $questionType, array $data): Question
    {
        // You can add more complex logic here if needed (validation, transformation, etc.)
        switch ($questionType) {
            case 'multiple_choice':
                return MultipleChoiceQuestion::create($data);
            case 'true_or_false':
                return TrueOrFalseQuestion::create($data);
            case 'identification':
                return IdentificationQuestion::create($data);
            case 'ranking':
                return RankingQuestion::create($data);
            case 'matching':
                return MatchingQuestion::create($data);
            default:
                throw new \Exception("Unknown question type: {$questionType}");
        }
    }
}
