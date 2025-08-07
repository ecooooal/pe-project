<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CodingAnswer extends Model
{
    protected $fillable = [
        'student_answer_id',
        'answer_language',
        'answer_file_path',
        'answer_syntax_points',
        'answer_runtime_points',
        'answer_test_case_points'
    ];

    public function studentAnswer(){
        return $this->belongsTo(StudentAnswer::class);
    }
}
