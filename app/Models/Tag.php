<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    protected $fillable = [
        'name',
    ];
    public function questions()
    {
        return $this->morphedByMany(Question::class, 'taggable')
                    ->withPivot('type')
                    ->withTimestamps();
    }
    
    public function taggables()
    {
        return $this->morphToMany(Model::class, 'taggable')
                    ->withPivot('type')
                    ->withTimestamps();
    }
}
