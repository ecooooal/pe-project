<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CodingQuestionLanguage extends Model
{
    protected $fillable = [
        'coding_question_id',
        'language',
        'complete_solution_file',
        'initial_solution_file',
        'test_case_file'
    ];

    public function codingQuestion(){
        return $this->belongsTo(CodingQuestion::class);
    }
}
