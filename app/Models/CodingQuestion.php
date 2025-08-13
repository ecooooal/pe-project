<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CodingQuestion extends Model
{
    protected $fillable = [
        'question_id',
        'instruction',
        'syntax_points',
        'runtime_points',
        'test_case_points'
    ];

    public function question(){
        return $this->belongsTo(Question::class);
    }
    public function codingQuestionLanguages(){
        return $this->hasMany(CodingQuestionLanguage::class);
    }

    public function getSpecificLanguage(string $language){
        return $this->codingQuestionLanguages->where('language', $language)->first();
    }
}
