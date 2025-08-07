<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RankingQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'question_id',
        'item',
        'order',
        'item_points'
    ];

    public function question(){
        return $this->belongsTo(Question::class);
    }
}
