<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    protected $fillable = [
        'exam_id',
        'reports_data'
    ];

    public function exam(){
        return $this->belongsTo(Exam::class);
    }
}
