<?php

namespace App\Services;
use App\Models\Exam;
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
        $exam_topics = $exam_questions
            ->groupBy(fn($question) => $question->topic->id)
            ->map(function($questions) {
                $topic = $questions->first()->topic;
                $topic->question_count = $questions->count();
                return $topic;
            })
            ->sortBy([
                ['question_count', 'desc'],
                ['name', 'asc'],
            ]);

        return $exam_topics;
    }

    public function getSubjectsForExam(Exam $exam){
        $exam_topics = $this->getTopicsForExam($exam);
        $exam_subjects = $exam_topics            
            ->groupBy(fn($topic) => $topic->subject->id)
            ->map(function($topics) {
                $subject = $topics->first()->subject;
                $subject->question_count = $topics->sum('question_count');
                return $subject;
            })
            ->sortBy([
                ['question_count', 'desc'],
                ['name', 'asc'],
            ]);
        return $exam_subjects;
    }
    public function getQuestionTypeCounts(Exam $exam): array
    {
        $questions = $this->getQuestionsForExam($exam); // already eager loaded

        return $questions->groupBy('question_type')->map(function ($type) {
            return $type->count();
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
    public function assignValuesToQuestionsForKnapsack(Exam $exam)
    {
        // Get all questions related to the exam’s course
        $course = $this->getCourseForExam($exam);
        
        // Check if course does exist
        if ($course) {

            $questions = $course->subjects->flatMap(function ($subject) {
                                            return $subject->topics->flatMap->questions;
                                        });

            // Count questions per subject and topic
            $questions_in_subjects = $questions->groupBy(fn($question) => $question->topic->subject->id)
                                            ->map->count();
            $questions_in_topics = $questions->groupBy(fn($question) => $question->topic->id)
                                            ->map->count();

            // Assign score to each question
            $scored_questions = $questions->map(function ($question) use ($questions_in_subjects, $questions_in_topics) {
                // get subject and topic ids
                $subject_id = $question->topic->subject->id;
                $topic_id = $question->topic->id;

                // get total counts of subjects and topics
                $subject_total = $questions_in_subjects[$subject_id] ?? 1;
                $topic_total = $questions_in_topics[$topic_id] ?? 1;

                // assign scores for coverage in subject and topic
                $question->topic_coverage_score = 1 / $topic_total;
                $question->subject_coverage_score = 1 / $subject_total;

                // calculate final coverage score
                // weight * subject_coverage score + weight * topic_coverage score
                $question->coverage_score = 0.6 * $question->subject_coverage_score 
                            + 0.4 * $question->topic_coverage_score;

                // calculate value density = value/weight whereas value = coverage score and weight = question points
                // The 'best' is defined by density of the item
                $question->density = log($question->coverage_score + 1) / ($question->points ?? 1);

                return $question;
            });
            // prepare data to represent set of questions to pick as Knapsack
            $knapsack = $scored_questions->map(fn($question) => ['id'=>$question->id, 'name'=>$question->name, 'value'=>$question->density, 'weight'=>$question->points]);
        }
        return $knapsack;
    }

    public function useGreedyAlgorithm(Exam $exam){
        $valued_questions = $this->assignValuesToQuestionsForKnapsack($exam);
        $item_questions = $valued_questions->sortByDesc('value');
        $question_combination = [];
        $max_weight = $exam->max_score;
        $total_value = 0.0;
        $total_weight = 0.0;
        foreach ($item_questions as $item) {
            if (($total_weight + $item['weight']) <= $max_weight) {
                $question_combination[] = $item;
                $total_weight += $item['weight'];
                $total_value += $item['value'];
            }
        }
        return [
            'questions' => $question_combination, 
            'total value' => $total_value, 
            'Exam Max Score' => $max_weight, 
            'total weights' => $total_weight
        ];
    }

    // algorithm for shuffling the question list
}