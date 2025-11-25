<?php

namespace App\Models;

<<<<<<< HEAD
=======
use Illuminate\Database\Eloquent\Factories\HasFactory;
>>>>>>> 97b8b1a8f93ca357a924efd6f2d7f88d6b86faaa
use Illuminate\Database\Eloquent\Model;

class Reviewer extends Model
{
<<<<<<< HEAD
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'reviewers';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'topic',
        'name',
        'author',
        'path',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
=======
    use HasFactory;

    protected $table = 'reviewers'; // optional if your table is named "reviewers"

    protected $fillable = [
        'email',
        'topic',
        'author',
        // Add additional fields like file_path if you store files
    ];

    // Optional: relationships
    // Example if reviewer belongs to a subject
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    // Example if reviewer has many files (if files are stored separately)
    public function files()
    {
        return $this->hasMany(ReviewerFile::class);
    }
}
>>>>>>> 97b8b1a8f93ca357a924efd6f2d7f88d6b86faaa
