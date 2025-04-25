<?php

namespace App\Services;
use App\Models\Course;
use App\Models\Exam;
use App\Models\Subject;
use App\Models\Topic;
use App\Models\Question;
use App\Models\User;
use Illuminate\Support\Collection;
class ExamService
{
    public function getCourseForExam(Exam $exam)
    {
        return $exam->course()->with('subjects.topics.questions')->first();
    }

    public function getQuestionsForExam(Exam $exam)
    {
        return $exam->questions()->with('topic.subject')->get();
    }

    public function getAvailableQuestionsForExam(Exam $exam)
    {
        $exam_course = $this->getCourseForExam($exam); 

        $exam_questions = $exam->questions()->pluck('question_id');

        $available_questions = $exam_course->subjects->flatMap(function ($subject) {
            return $subject->topics->flatMap->questions;
        });

        return $available_questions->whereNotIn('id', $exam_questions);
    }

    public function getTopicsForExam(Exam $exam){
        $exam_questions = $this->getQuestionsForExam($exam);
        $exam_topics = $exam_questions->pluck('topic')->unique('id');
        return $exam_topics;
    }

    public function getSubjectsForExam(Exam $exam){
        $exam_topics = $this->getTopicsForExam($exam);
        $exam_subjects = $exam_topics->pluck('subject')->unique('id');
        return $exam_subjects;
    }
    public function getQuestionTypeCounts(Exam $exam): array
    {
        $questions = $this->getQuestionsForExam($exam); // already eager loaded

        return $questions->groupBy('question_type')->map(function ($group) {
            return $group->count();
        })->toArray();
    }


    public function transformQuestionRows(Collection $questions)
    {
        return $questions->map(fn ($question) => [
            'id' => $question->id, 
            'name' => $question->name,
            'subject' => $question->topic->subject->name,
            'topic' => $question->topic->name,
            'type' => $question->question_type->name
        ]);
    }


    // algorithm for fetching and building the exam
    public function assignScoreToQuestionsForExam(Exam $exam)
    {
        // Get all questions related to the exam’s course
        $course = $this->getCourseForExam($exam);
        $questions = $course->subjects->flatMap(function ($subject) {
            return $subject->topics->flatMap->questions;
        });

        // Count questions per subject
        $questions_in_subjects = $questions->groupBy(fn($question) => $question->topic->subject->id)
                                        ->map->count();

        // Assign score to each question
        $scored_questions = $questions->map(function ($question) use ($questions_in_subjects) {
            $subject_id = $question->topic->subject->id;
            $subject_total = $questions_in_subjects[$subject_id] ?? 1;
            $question->subject_coverage_score = 1 / $subject_total;
            return $question;
        });

        return $scored_questions;
    }


    // algorithm for shuffling the question list
}