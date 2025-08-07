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
    protected $casts = [
        'date_taken' => 'datetime',
        'created_at' => 'datetime',
    ];


    public function studentPaper(){
        return $this->belongsTo(StudentPaper::class);
    }

    public function subjects(){
        return $this->hasMany(ExamRecordsSubject::class, 'exam_record_id');
    }
}
