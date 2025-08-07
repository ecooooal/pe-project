<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentAnswer extends Model
{
    protected $fillable = [
        'student_paper_id',
        'question_id',
        'points',
        'is_answered',
        'is_correct',
        'answered_at'
    ];

    public function studentPaper(){
        return $this->belongsTo(StudentPaper::class);
    }
    public function question(){
        return $this->belongsTo(Question::class);
    }

    public function multipleChoiceAnswer(){
        return $this->hasOne(MultipleChoiceAnswer::class);
    }
    public function trueOrFalseAnswer(){
        return $this->hasOne(TrueOrFalseAnswer::class);
    }
    public function identificationAnswer(){
        return $this->hasOne(IdentificationAnswer::class);
    }
    public function rankingAnswers(){
        return $this->hasMany(RankingAnswer::class);
    }
    public function matchingAnswers(){
        return $this->hasMany(MatchingAnswer::class);
    }

    public function codingAnswer(){
        return $this->hasOne(CodingAnswer::class);
    }
}
