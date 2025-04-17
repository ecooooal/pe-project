<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use App\TracksUserActivity;

class Question extends Model
{
    use HasFactory, Notifiable, TracksUserActivity;

    protected $fillable = [
        'question_type',
        'name',
        'points'
    ];

    public function topics(){
        return $this->belongsToMany(Subject::class)->withTimestamps();
    }

    public function multiple_choice_questions(){
        return $this->hasMany(MultipleChoiceQuestion::class)->withTimestamps();
    }
    public function true_or_false_questions(){
        return $this->hasMany(TrueOrFalseQuestion::class)->withTimestamps();
    }
    public function identification_questions(){
        return $this->hasMany(IdentificationQuestion::class)->withTimestamps();
    }
    public function ranking_questions(){
        return $this->hasMany(RankingQuestion::class)->withTimestamps();
    }
    public function matching_questions(){
        return $this->hasMany(MatchingQuestion::class)->withTimestamps();
    }
}
