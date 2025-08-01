<?php

namespace App\Http\Controllers\Student;

use App\Models\Exam;
use App\Models\ExamRecord;
use App\Models\StudentAnswer;
use App\Models\StudentPaper;
use App\Services\ExamTakingService;
use DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Str;

class ExamRecordController extends Controller
{
    public function index(Exam $exam)
    {
        $user = auth()->user();
        $exam_records = ExamRecord::whereHas('studentPaper', function ($query) use ($exam, $user) {
            $query->where('exam_id', $exam->id)
                ->where('user_id', $user->id);
        })->with('subjects')->get();
    
        
        return view( 'students/records/index', ['exam' => $exam, 'exam_records' => $exam_records]);
    }

    public function store(StudentPaper $student_paper)
    {
        // validate that the student_paper's author is the authenticated user
        $student_paper->update(['submitted_at' => now()]);
        $exam_id = $student_paper->exam->id;
        $exam = Exam::find($exam_id);
        $attempt_count = ExamRecord::whereHas('studentPaper', function($query) use ($exam_id, $student_paper) {
            $query->where('exam_id', $exam_id)
                ->where('user_id', $student_paper->user_id);
        })->count();

        $subject_table = DB::table('student_answers')
        ->join('questions', 'student_answers.question_id', '=', 'questions.id')
        ->join('topics', 'questions.topic_id', '=', 'topics.id')
        ->join('subjects', 'topics.subject_id', '=', 'subjects.id')
        ->where('student_answers.student_paper_id', $student_paper->id)
        ->groupBy('subjects.id', 'subjects.name')
        ->select(
            'subjects.id as id',    
            'subjects.name as subject_name',
            DB::raw('SUM(student_answers.points) as subject_score_obtained'),
            DB::raw('SUM(questions.total_points) as subject_score'))
        ->get()
        ->keyBy('id');

        $total_score = $subject_table->sum('subject_score');
        $date_taken = $student_paper->created_at;
        
        $time_taken = round($student_paper->created_at->diffInMinutes($student_paper->submitted_at));

        $exam_record = $student_paper->examRecord()->updateOrCreate(
        ['student_paper_id' => $student_paper->id],
            [
                    'attempt' => $attempt_count,
                    'total_score' => $total_score,
                    'date_taken' => $date_taken,
                    'time_taken' => $time_taken,
            ]);

        $transformed_subject_table = $subject_table->map(function ($item) use ($exam_record) {
            return [
                'exam_record_id'   => $exam_record->id,
                'subject_id'       => $item->id,
                'subject_name'     => $item->subject_name,
                'score_obtained'   => (int) $item->subject_score_obtained,
                'score'            => (int) $item->subject_score,
                'created_at'       => now(),
                'updated_at'       => now(),
            ];
        })->values()->toArray();

        DB::table('exam_records_subjects')->upsert(
        $transformed_subject_table,
        ['exam_record_id', 'subject_id'],
        ['score_obtained', 'score', 'updated_at']
        );

        $student_paper->update(['status'  => 'completed']);

        return response('', 204)->header('HX-Redirect', route('exam_records.show', ['exam' => $exam, 'exam_record' => $exam_record]));
    }

    public function show(Exam $exam, ExamRecord $examRecord)
    {
        $examRecord->load('subjects');
        $student_paper = StudentPaper::with('studentAnswers.question')
            ->find($examRecord->student_paper_id);

        $rows = [];
        foreach ($student_paper->studentAnswers as $i => $answer) {
            $question = $answer->question;

            $neededRelations = $student_paper->studentAnswers
                ->map(fn($a) => $a->question->question_type->value)
                ->unique()
                ->map(fn($type) => 'studentAnswers.' . Str::camel($type) . (in_array($type, ['ranking', 'matching']) ? 'Answers' : 'Answer'))
                ->values()
                ->all();

            $student_paper->load(array_merge(['studentAnswers.question'], $neededRelations));


            // if ($i === 0) {
            //     dump([
            //         'neededRelations' => $neededRelations,
            //         'student_paper' => $student_paper,
            //         'loaded_relations' => $answer->getRelations(),
            //     ]);
            // }


            // Load and get the specific answer
            if ($answer->is_answered){
                $question_type_answer = ExamTakingService::getAnswerType($answer, $question);
                if ($question_type_answer != null){
                    $yourAnswer = match ($question->question_type->value) {
                                    'multiple_choice' => $question_type_answer?->answer ?? 'N/A',
                                    'true_or_false' => $question_type_answer?->answer ? 'True' : 'False',
                                    'identification' => $question_type_answer?->answer ?? 'N/A',
                                    'ranking' => $question_type_answer ?? 'N/A',
                                    'matching' => $question_type_answer->map(fn($a) => "{$a->first_item_answer} → {$a->secondd_item_answer}")->implode(', '),
                                    default => 'Unsupported',
                                };
                }
            }

            $rows[] = [
                's' => ExamTakingService::getAnswerType($answer, $question),
                'number' => $i + 1,
                'question' => $question->name,
                'question_type' => $question->question_type->getName(),
                'your_answer' => $yourAnswer ?? $answer->is_answered ?? 'Not answered',
                'is_answered'=> $answer->is_answered,
                'score' => $answer->points ?? 0,
                'max_score' => $question->total_points,
                'status' => ($answer->gained_points >= $question->points) ? 'Correct' : 'Incorrect',
                'question_type_answer' => $question_type_answer ?? null,
                'answer_test' => $answer
            ];
        }


        $data = [
            'exam_record' => $examRecord,
            'student_paper' => $student_paper,
            'exam' => $exam,
            'rows' => $rows
        ];

        return view('students/records/show', $data);
    }
}
