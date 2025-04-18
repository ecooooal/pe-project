<?php

namespace App\Enums;

enum QuestionType: string
{
    case MultipleChoice = 'multiple_choice';
    case TrueOrFalse = 'true_or_false';
    case Identification = 'identification';
    case Ranking = 'ranking';
    case Matching = 'matching';
}
