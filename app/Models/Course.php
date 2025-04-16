<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use App\TracksUserActivity;

class Course extends Model
{
    use HasFactory, Notifiable, SoftDeletes, TracksUserActivity;

    protected $fillable = [
        'name',
        'abbreviation'
    ];

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

}
