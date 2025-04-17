<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use App\TracksUserActivity;

class Topic extends Model
{
    use HasFactory, Notifiable, TracksUserActivity;

    protected $fillable = [
        'name',
    ];

    public function subjects(){
        return $this->belongsTo(Subject::class);
    }

    public function questions(){
        return $this->hasMany(Question::class);
    }
}
