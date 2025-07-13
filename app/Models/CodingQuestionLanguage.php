<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CodingQuestionLanguage extends Model
{
    protected $fillable = [
        'coding_question_id',
        'language',
        'complete_solution_file_path',
        'initial_solution_file_path',
        'test_case_file_path',
        'class_name',
        'test_class_name'
    ];

    public function codingQuestion(){
        return $this->belongsTo(CodingQuestion::class);
    }
}
