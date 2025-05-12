<?php

namespace App\Services;
use App\Models\Course;
use App\Models\Subject;
use App\Models\Topic;
use App\Models\Question;
use App\Models\User;
class UserService
{
    public function getCoursesForUser(User $user)
    {
        return $user->courses()
            ->with('subjects.topics.questions')
            ->get();
    }
    public function getCourseById($courseId)
    {
        return Course::with('subjects')
                      ->findOrFail($courseId);
    }

    public function getSubjectsForUser(User $user)
    {
        return Subject::whereIn('course_id', $user->getCourseIds());
    }

    public function getSubjectById($subjectId)
    {
        return Subject::with('topics.questions')
                      ->findOrFail($subjectId);
    }

    public function getTopicsForUser(User $user)
    {
        $subjectIds = $this->getSubjectsForUser($user)->pluck('id');
        return Topic::whereIn('subject_id', $subjectIds);
    }

    public function getTopicById($topicId)
    {
        return Topic::with('questions') // eager load questions
                    ->findOrFail($topicId);
    }

    public function getQuestionsForUser(User $user)
    {
        $topicIds = $this->getTopicsForUser($user)->pluck('id');
        return Question::with('topic.subject.course')
            ->whereIn('topic_id', $topicIds);
    }

    public function getQuestionById($questionId)
    {
        return Question::with('topic.subject.course')
                       ->findOrFail($questionId);
    }

    
}
