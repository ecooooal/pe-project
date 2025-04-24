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
    public function getExamQuestions(Exam $exam){
        return $exam->questions()->get();
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
        return $questions->map(fn ($q) => ['id' => $q->id, 'name' => $q->name]);
    }


    // algorithm for fetching and building the exam
    // algorithm for shuffling the question list
}