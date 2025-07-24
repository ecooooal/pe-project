<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MatchingQuestion extends Model
{
    protected $fillable = [
        'question_id',
        'first_item',
        'second_item',
        'points'
    ];

    public function question(){
        return $this->belongsTo(Question::class);
    }
}
