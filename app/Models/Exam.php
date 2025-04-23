<?php

namespace App\Models;

use App\TracksUserActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Exam extends Model
{
    use HasFactory, Notifiable, TracksUserActivity;

    protected $fillable = [
        'name',
        'course_id',
        'access_code',
        'max_score',
        'duration',
        'retakes',
        'examination_date',
        'published'
    ];

    public function course() {
        return $this->belongsTo(Course::class);
    }
    
    public function questions() {
        return $this->belongsToMany(Question::class)->withTimestamps();
    }
}
