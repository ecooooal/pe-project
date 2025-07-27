<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentPaper extends Model
{
    protected $fillable = [
        'exam_id',
        'user_id',
        'question_count',
        'questions_order',
        'current_position',
        'status',
        'last_seen_at',
        'submitted_at',
        'expired_at'
    ];

    public function exam(){
        return $this->belongsTo(Exam::class);
    }
    public function user(){
        return $this->belongsTo(User::class);
    }

    public function studentAnswers() {
        return $this->hasMany(StudentAnswer::class);
    }
}
