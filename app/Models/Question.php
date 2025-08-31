<?php

namespace App\Models;

use App\Enums\QuestionType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use App\TracksUserActivity;

class Question extends Model
{
    use HasFactory, Notifiable, TracksUserActivity;

    protected $fillable = [
        'question_type',
        'topic_id',
        'name',
        'total_points'
    ];

    protected $casts = [
        'question_type' => QuestionType::class,
    ];

    public function topic(){
        return $this->belongsTo(Topic::class);
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function exams() {
        return $this->belongsToMany(Exam::class)->withTimestamps();
    }

    public function tags()
    {
        return $this->morphToMany(Tag::class, 'taggable')
                    ->withPivot('type')
                    ->withTimestamps();
    }

    public function requiredTags()
    {
        return $this->tags()->wherePivot('type', 'required');
    }

    public function optionalTags()
    {
        return $this->tags()->wherePivot('type', 'optional');
    }

    public function multipleChoiceQuestions(){
        return $this->hasMany(MultipleChoiceQuestion::class);
    }
    public function trueOrFalseQuestion(){
        return $this->hasOne(TrueOrFalseQuestion::class);
    }
    public function identificationQuestion(){
        return $this->hasOne(IdentificationQuestion::class);
    }
    public function rankingQuestions(){
        return $this->hasMany(RankingQuestion::class);
    }
    public function matchingQuestions(){
        return $this->hasMany(MatchingQuestion::class);
    }

    public function codingQuestion(){
        return $this->hasOne(CodingQuestion::class);
    }

    public function getTypeModel()
    {                
        return match ($this->question_type) {
            QuestionType::MultipleChoice => $this->multipleChoiceQuestions,
            QuestionType::TrueOrFalse => $this->trueOrFalseQuestion,
            QuestionType::Identification => $this->identificationQuestion,
            QuestionType::Ranking => $this->rankingQuestions,
            QuestionType::Matching => $this->matchingQuestions,
            QuestionType::Coding => $this->codingQuestion,
            default => null,
        };
    }
    

}
