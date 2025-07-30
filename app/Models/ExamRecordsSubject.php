<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamRecordsSubject extends Model
{
    protected $table = 'exam_records_subjects';

    protected $fillable = [
        'exam_record_id',
        'subject_id',
        'subject_name',
        'score_obtained',
        'score',
    ];

    public function examRecord(){
        return $this->belongsTo(ExamRecord::class);
    }
}

