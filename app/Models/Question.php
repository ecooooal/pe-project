<?php

namespace App\Models;

use App\Enums\QuestionType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use App\TracksUserActivity;

class Question extends Model
{
    use HasFactory, Notifiable, TracksUserActivity;

    protected $fillable = [
        'question_type',
        'topic_id',
        'name',
        'points'
    ];

    protected $casts = [
        'question_type' => QuestionType::class,
    ];

    public function topics(){
        return $this->belongsTo(Topic::class);
    }

    public function multiple_choice_questions(){
        return $this->hasMany(MultipleChoiceQuestion::class);
    }
    public function true_or_false_questions(){
        return $this->hasMany(TrueOrFalseQuestion::class);
    }
    public function identification_questions(){
        return $this->hasMany(IdentificationQuestion::class);
    }
    public function ranking_questions(){
        return $this->hasMany(RankingQuestion::class);
    }
    public function matching_questions(){
        return $this->hasMany(MatchingQuestion::class);
    }

    public function getTypeModel()
{
    return match ($this->question_type) {
        QuestionType::MultipleChoice => $this->multiple_choice_questions,
        QuestionType::TrueOrFalse => $this->true_or_false_questions,
        QuestionType::Identification => $this->identification_questions,
        QuestionType::Ranking => $this->ranking_questions,
        QuestionType::Matching => $this->matching_questions,
    };
}

}
