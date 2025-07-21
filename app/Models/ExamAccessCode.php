<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamAccessCode extends Model
{
    protected $fillable = [
        'exam_id',
        'access_code'
    ];
    
    public function exam() {
        return $this->belongsTo(Exam::class);
    }

}
