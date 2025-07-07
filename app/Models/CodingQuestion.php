<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CodingQuestion extends Model
{
    protected $fillable = [
        'question_id',
        'instruction'
    ];

    public function question(){
        return $this->belongsTo(Question::class);
    }
    public function codingQuestionLanguages(){
        return $this->hasMany(CodingQuestionLanguage::class);
    }
}
