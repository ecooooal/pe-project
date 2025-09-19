<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Exam;
use App\Models\ExamAccessCode;
use App\Models\Question;
use App\Services\ExamService;
use App\Services\UserService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Str;

class ExamController extends Controller
{
    protected $userService;
    protected $examService;

    public function __construct(UserService $userService, ExamService $examService)
    {
        $this->userService = $userService;
        $this->examService = $examService;
    }

    public function index(){
        $courseIds = $this->userService->getCoursesForUser(auth()->user())->pluck('id');
        $exams = Exam::with(['courses', 'questions'])
            ->whereHas('courses', function ($query) use ($courseIds) {
                $query->whereIn('courses.id', $courseIds);
            })
            ->paginate(10);
        $header = ['Name', 'Questions', 'Status', 'is Published', 'Examination Date'];
        $rows = $exams->map(function ($exam) {
            return [
                'id' => $exam->id,
                'name' => $exam->name,
                'questions' => $exam->questions->count(),
                'status' => $exam->questions()->sum('total_points') >= $exam->max_score ? 'Complete' : 'Incomplete',
                'is_published' => $exam->is_published ? 'Yes' : 'No',
                'examination date' => Carbon::parse($exam->examination_date)->format('m/d/Y')
            ];
        });

        $data = [
            'headers' => $header,
            'rows' => $rows,
            'exams' => $exams,
            'url' => 'exams'
        ];

        return view('exams/index', $data);

    }

    public function show(Exam $exam){
        $exam->load(['questions','courses']);

        return view('exams/show', ['exam' => $exam]);
    }

    public function create(){
        $courses =$this->userService->getCoursesForUser(auth()->user()); 
        return view('exams/create', ['courses' => $courses]);

    }

    public function store(){

        $validated = request()->validate([
            'name' => 'required|string|max:255',
            'courses' => 'required|array|min:1',
            'courses.*' => 'exists:courses,id|required|string|distinct',
            'max_score' => 'required|integer|gte:1',
            'duration' => 'nullable|integer|min:1',
            'retakes' => 'nullable|integer|min:1',
            'examination_date' => 'nullable|date|after:now',
            'passing_score' => 'required|integer|gte:1|max:100'
        ], [
            'courses' => 'The course field is required.',
            'courses.*' => 'Invalid Course',
            'max_score' => 'The max score field is required',
            'examination_date' => 'The examination date field must be a date after now.'
        ]);

        $exam = Exam::create([
            'name' => request('name'),
            'max_score' => request('max_score'),
            'duration' => request('duration') ?? null,
            'retakes' => request('retakes') ?? null, 
            'examination_date' => request('examination_date'),
            'passing_score' => request('passing_score')
        ]);

        $exam->courses()->attach($validated['courses']);

        $access_code = $this->examService->generateAccessCode();
        $this->examService->saveAccessCode($exam, $access_code);

        session()->flash('toast', json_encode([
            'status' => 'Created!',
            'message' => 'Exam: ' . $exam->name,
            'type' => 'success'
        ]));

        return redirect('/exams');
    }

    public function edit(Exam $exam){
        if ($exam->is_published) {
            return back();
        }
            return view('exams/edit', ['exam'=>$exam]);
    }

    public function update(Exam $exam){
        request()->validate([
            'name' => 'required|string|max:255',
            'max_score' => 'required|integer|gte:1',
            'duration' => 'nullable|integer|min:1',
            'retakes' => 'nullable|integer|min:1',
            'examination_date' => 'nullable|date|after:now',
            'passing_score' => 'required|integer|gte:1|max:100'
        ], [
            'max_score' => 'The max score field is required',
            'examination_date' => 'The examination date field must be a date after now.'
        ]);

            $exam->update([
                'name' => request('name'),
                'max_score' => request('max_score'),
                'duration' => request('duration') ?? null,
                'retakes' => request('retakes') ?? null, 
                'examination_date' => request('examination_date') ?? null,
                'passing_score' => request('passing_score')
            ]);

        session()->flash('toast', json_encode([
            'status' => 'Updated!',
            'message' => 'Exam: ' . $exam->name,
            'type' => 'info'
        ]));
        return redirect()->route('exams.show', $exam);

    }

    public function destroy(Exam $exam){
        
        $this->authorize('delete', $exam);

        if ($exam->is_published) {
            return back()->with('error', 'You cannot delete an exam that is currently published.');
        }

        session()->flash('toast', json_encode([
            'status' => 'Destroyed!',
            'message' => 'Exam: ' . $exam->name,
            'type' => 'warning'
        ]));

        $exam->delete();

        return redirect('/exams');
    }

    public function exam_builder_show(Exam $exam){  
        $exam_course = $this->examService->getCourseForExam($exam);
        $exam_questions =  $this->examService->getQuestionsForExam($exam);
        $exam_topics = $this->examService->getTopicsForExam($exam);
        $exam_subjects = $this->examService->getSubjectsForExam($exam);
        $exam_question_types = $this->examService->getQuestionTypeCounts($exam);

        $available_questions = $this->examService->getAvailableQuestionsForExam($exam);
        $questions_header = ['Name', 'Type', 'Points'];
        $exam_questions_rows = $this->examService->transformQuestionRows($exam_questions);
        $available_questions_rows = $this->examService->transformQuestionRows($available_questions);
        $data = [
            'exam' => $exam,
            'exam_course' => $exam_course,
            'exam_subjects' => $exam_subjects,
            'exam_topics' => $exam_topics,
            'exam_available_questions' => $available_questions,
            'exam_questions' => $exam_questions,
            'exam_question_types' => $exam_question_types,
            'questions_header' => $questions_header,
            'available_questions_rows' => $available_questions_rows,
            'exam_questions_rows' => $exam_questions_rows,
        ];

        return view('exams/exam-builder', $data);
    }

    public function toggle_question(Exam $exam, Question $question){
        if ($exam->questions->contains($question->id)) {
            $exam->questions()->detach($question->id);
        } else {
            $exam->questions()->attach($question->id);
        }

        $exam_questions =  $this->examService->getQuestionsForExam($exam);
        $exam_topics = $this->examService->getTopicsForExam($exam);
        $exam_subjects = $this->examService->getSubjectsForExam($exam);
        $exam_question_types = $this->examService->getQuestionTypeCounts($exam);

        $available_questions = $this->examService->getAvailableQuestionsForExam($exam);
        $questions_header = ['Name', 'Type', 'Points'];
        $exam_questions_rows = $this->examService->transformQuestionRows($exam_questions);
        $available_questions_rows = $this->examService->transformQuestionRows($available_questions);

        $data = [
            'exam' => $exam,
            'exam_subjects' => $exam_subjects,
            'exam_topics' => $exam_topics,
            'exam_available_questions' => $available_questions,
            'exam_questions' => $exam_questions,
            'exam_question_types' => $exam_question_types,
            'questions_header' => $questions_header,
            'available_questions_rows' => $available_questions_rows,
            'exam_questions_rows' => $exam_questions_rows
        ];

        return view('components/core/partials-exam-builder', $data);
    }

    public function build_exam(Exam $exam){
        $algorithm = request()->query('algorithm');
        $subject_weight = (request()->query('subject_weight') ?: 60) / 100;
        $criteria = request()->query('criteria') ?: 'density';

        $optimal_set_of_questions = match ($algorithm) {
                'greedy' => $this->examService->useGreedyAlgorithm($exam, $subject_weight, $criteria),
                'dynamic_programming' => $this->examService->useDynamicProgramming($exam, $subject_weight, $criteria),
                default => $this->examService->useDynamicProgramming($exam, $subject_weight, $criteria),
            };
        $questions_to_sync = array_column($optimal_set_of_questions['questions'], 'id');
        $exam->questions()->sync($questions_to_sync);
        $exam->update(['applied_algorithm' => $optimal_set_of_questions['algorithm']]);

        return response('', 200)->header('HX-Refresh', 'true');    
    }

    public function generateAccessCode(Exam $exam){
        $access_code = $this->examService->generateAccessCode();
        return view('components/core/partials-exam-access-code', ['exam' => $exam, 'access_code' => $access_code, 'generate' => true]);
    }

    public function getAccessCode(Exam $exam){
        $access_codes = $this->examService->getAccessCodesForExam($exam);
        return view('exams/get-access-codes', ['exam' => $exam ,'access_codes' => $access_codes]);
    }

    public function saveAccessCode(Exam $exam, Request $request){
        $access_code = $request->post('code');
        $is_success =$this->examService->saveAccessCode($exam, $access_code);
        
        if ($is_success !== true) {
            session()->flash('toast', json_encode([
                'status' => 'Error!',
                'message' => 'Access Code Not Saved',
                'type' => 'error'
            ]));
            return back()->withErrors(['access_code' => $is_success]);
            
        }

        $toast =  json_encode([
            'status' => 'Created!',
            'message' => 'Access Code Saved',
            'type' => 'success'
        ]);


        return view('components/core/partials-exam-access-code', 
        ['access_code' => $access_code, 
                'generate' => false, 
                'toast' => $toast
        ]);
    }

    public function destroyAccessCode(Exam $exam, Request $request)
    {
        $access_code = $request->input('code'); 
        $deleted = $exam->accessCodes()
                ->where('access_code', $access_code)
                ->delete();
        if (!$deleted) {
            return back()->withErrors(['access_code' => 'Code not found']);
        }
        
        return response()->noContent();
    }

    public function publishExam(Exam $exam){
        $is_published = $this->examService->attemptToPublish($exam);
        if (!$is_published){
            return view('components/core/partials-exam-builder-publish-form-error', [
                'error' => 'Sum of question points do not match the max score.'
            ]);        
        }

        return response('', 200)->header('HX-Refresh', 'true');
    }

    public function swap_partial_algorithm(Exam $exam){

        return view('exams/partials-algorithms', ['exam'=> $exam]);
    }

    public function swap_tabs(){

        return view('exams/partials-tabs-algorithm');
    }
}
