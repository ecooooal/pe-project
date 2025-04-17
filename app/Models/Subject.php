<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use App\TracksUserActivity;

class Subject extends Model
{
    use HasFactory, Notifiable, TracksUserActivity;

    protected $fillable = [
        'name',
        'year_level'
    ];

    public function courses(){
        return $this->belongsToMany(Course::class)->withTimestamps();
    }

    public function topics(){
        return $this->hasMany(Topic::class)->withTimestamps();
    }
}
