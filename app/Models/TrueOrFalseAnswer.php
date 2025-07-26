<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrueOrFalseAnswer extends Model
{
    protected $fillable = [
        'student_answer_id',
        'answer'
    ];

    public function studentAnswer(){
        return $this->belongsTo(StudentAnswer::class);
    }
}
