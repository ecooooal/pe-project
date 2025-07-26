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
}
