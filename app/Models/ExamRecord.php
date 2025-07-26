<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamRecord extends Model
{
    protected $fillable = [
        'student_paper_id',
        'attempt',
        'subjects',
        'subject_score_obtained',
        'subject_score',
        'total_score',
        'date_taken',
        'time_taken',
        'status'
    ];

    public function studentPaper(){
        return $this->hasOne(StudentPaper::class);
    }}
