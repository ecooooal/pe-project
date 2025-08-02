<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reviewer extends Model
{
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
