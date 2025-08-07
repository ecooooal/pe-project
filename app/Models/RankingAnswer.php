<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RankingAnswer extends Model
{
    protected $fillable = [
        'student_answer_id',
        'answer',
        'answer_order',
        'answer_points'
    ];

    public function studentAnswer(){
        return $this->belongsTo(StudentAnswer::class);
    }
}
