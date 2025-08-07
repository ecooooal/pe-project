<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MatchingAnswer extends Model
{
    protected $fillable = [
        'student_answer_id',
        'first_item_answer',
        'second_item_answer',
        'answer_points'
    ];

    public function studentAnswer(){
        return $this->belongsTo(StudentAnswer::class);
    }
}
