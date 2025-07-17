<?php

namespace App\Http\Controllers;

use App\Factories\QuestionFactory;
use App\Models\MultipleChoiceQuestion;
use App\Models\Question;
use App\Models\Subject;
use App\Models\Topic;
use App\Services\QuestionService;
use App\Services\UserService;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Str;
use Validator;

class QuestionController extends Controller
{
    protected $userService;
    protected $questionService;

    public function __construct(UserService $userService, QuestionService $questionService)
    {
        $this->userService = $userService;
        $this->questionService = $questionService;

    }

    public function index(){
        $questions = $this->userService->getQuestionsForUser(auth()->user())->paginate(10);
        $header = ['ID', 'Name', 'Subject', 'Topic', 'Type', 'Author', 'Date Created'];
        $rows = $questions->map(function ($question) {
            return [
                'id' => $question->id,
                'name' => $question->name,
                'subject' => $question->topic->subject->name,
                'topic' => $question->topic->name,
                'type' => $question->question_type->name,
                'author' => $question->author->getFullName(),
                'Date Created' => Carbon::parse($question->created_at)->format('m/d/Y')
            ];
        });

        $data = [
            'headers' => $header,
            'rows' => $rows,
            'questions' => $questions
        ];

        return view('questions/index', $data);
    }

    public function show(Question $question){
        $question->load('topic.subject.course');
        $question_type = $question->getTypeModel();
        $data = [
            'question' => $question,
            'question_type' => $question_type
        ];
        return view('questions/show', $data);
    }
    public function create(){
        $courses = $this->userService->getCoursesForUser(auth()->user());
        // $subjects = $this->userService->getSubjectsForUser(auth()->user());
        $courses = $courses->pluck('name', 'id');
        // $subjects = $subjects->pluck('name', 'id');
        $question_types = [
            '' => 'Select A Question Type',
            'multiple_choice' => 'Multiple Choice',
            'true_or_false'=> 'True or False',
            'identification' => 'Identification',
            'ranking' => 'Ranking/Ordering/Process',
            'matching' => 'Matching Items'
        ];

        $data =[
            'courses' => $courses,
            // 'subjects' => $subjects,
            'question_types' => $question_types
        ];

        return view('questions/create', $data);
    }

    public function createCodingQuestion(){
        $courses = $this->userService->getCoursesForUser(auth()->user())->pluck('name', 'id');
        $programming_languages = [
            'java' => "Java",
            'c++' => "C++",
            'python' => "Python",
        ];

        $markdown = Str::of('- *Laravel*')->markdown();

        $data =[
            'courses' => $courses,
            'programming_languages' => $programming_languages,
            'markdown' => $markdown
        ];

        return view('questions-types/coding', $data);
    }

    public function store(){
        $question_type = request('type');
        $item_count = count(request()->input('items', []));

        $rules = [
            'topic' => ['required', 'exists:topics,id'],
            'type' => ['required'],
            'name' => ['required', 'string', 'unique:questions,name'],
            'points' => ['required', 'integer', 'min:1'],
            'subject' => ['required'],
        ];

        if ($question_type === 'coding') {
            $rules['instruction'] = ['required'];
            $rules['supported_languages'] = ['required', 'json', function ($attribute, $value, $fail) {
                $decoded = json_decode($value, true);
                if (empty($decoded)) {
                    $fail('Coding question must have at least one programming language.');
                }
            }];
            $instruction = request()->post('instruction');
            $markdown = Str::of($instruction)->markdown(['html_input' => 'strip']) ?? '';
            $supported = json_decode(request()->post('supported_languages', '{}'), true);
            $messages = [
                'supported_languages.required' => 'Coding question must have at least one programming language.',
            ];
        } else {
            if ($question_type === 'matching'){
                $rules['items.*.left'] = ['required', 'string', 'min:1'];
                $rules['items.*.right'] = ['required', 'string', 'min:1'];
                $messages = [
                    'items.*.left.required' => 'Left side is required.',
                    'items.*.right.required' => 'Right side is required.',
                ];
            } else {
                $rules['items.*'] = ['required', 'string', 'min:1'];
                $messages = [
                    'items.*.required' => 'This field is required.',
                ];
            }
            $rules['solution'] = ['required', 'string'];
        }


        $validator = Validator::make(request()->all(), $rules, $messages);
        if ($validator->fails()) {
            if($question_type == 'coding'){
                return view('components/core/coding-question-error', [
                    'errors' => $validator->errors()
                ]);
            } else {
                return redirect()->route('question.types', ['type' => $question_type, 'item_count' => $item_count])
                ->withErrors($validator)
                ->withInput();
            }
        }

        $data = $validator->validated();

        \Log::info('data can be stored', $data);
        QuestionFactory::create($data);
        \Log::info('Question Creation Successful');
        
        if (request()->header('HX-Request')) {
            return response('', 200)->header('HX-Redirect', url('/questions'));
        }

        return redirect('/questions');    
    }
    public function edit(Question $question){
        if ($question->question_type->value == 'coding'){
            $data = [
            'question' => $question
            ];
        } else {
            $subjects = Subject::whereIn('course_id', $question->topic->subject->course()->get()->pluck('id'))->get()->pluck('name', 'id');
            $question_types = [
                'multiple_choice' => 'Multiple Choice',
                'true_or_false'=> 'True or False',
                'identification' => 'Identification',
                'ranking' => 'Ranking/Ordering/Process',
                'matching' => 'Matching Items'
            ];
            $data = [
                'question' => $question, 
                'subjects' => $subjects,
                'question_types' => $question_types
            ];
        }
    
        return view('questions/edit', $data);
    }

    public function update(Question $question){
        $this->authorize('update', $question);

        $question_type = request('type');
        $item_count = count(request()->input('items', []));

        $rules = [
            'topic' => ['required', 'exists:topics,id'],
            'type' => ['required'],
            'name' => ['required', 'string', Rule::unique('questions', 'name')->ignore($question->id)->whereNull('deleted_at'),],
            'points' => ['required', 'integer', 'min:1'],
            'subject' => ['required'],
        ];

        if ($question_type === 'coding') {
            $rules['instruction'] = ['required'];
            $rules['supported_languages'] = ['required', 'json', function ($attribute, $value, $fail) {
                $decoded = json_decode($value, true);
                if (empty($decoded)) {
                    $fail('Coding question must have at least one programming language.');
                }
            }];
            $instruction = request()->post('instruction');
            $markdown = Str::of($instruction)->markdown(['html_input' => 'strip']) ?? '';
            $supported = json_decode(request()->post('supported_languages', '{}'), true);
            $messages = [
                'supported_languages.required' => 'Coding question must have at least one programming language.',
            ];
        } else {
            if ($question_type === 'matching'){
                $rules['items.*.left'] = ['required', 'string', 'min:1'];
                $rules['items.*.right'] = ['required', 'string', 'min:1'];
                $messages = [
                    'items.*.left.required' => 'Left side is required.',
                    'items.*.right.required' => 'Right side is required.',
                ];
            } else {
                $rules['items.*'] = ['required', 'string', 'min:1'];
                $messages = [
                    'items.*.required' => 'This field is required.',
                ];
            }
            $rules['solution'] = ['required', 'string'];
        }

        $validator = Validator::make(request()->all(), $rules, $messages);
        if ($validator->fails()) {
            if($question_type == 'coding'){
                return view('components/core/coding-question-error', [
                    'errors' => $validator->errors()
                ]);
            } else {
                return redirect()->route('question.types', ['type' => $question_type, 'item_count' => $item_count])
                ->withErrors($validator)
                ->withInput();
            }
        }

        $data = $validator->validated();

        \Log::info('data can be updated', $data);
        QuestionFactory::update($question, $data);
        \Log::info('Question Update Successful');
        
        if (request()->header('HX-Request')) {
            return response('', 200)->header('HX-Redirect', route('questions.show', $question));
        }
        
        return redirect()->route('questions.show', $question);
    }
    public function destroy(Question $question){

        $this->authorize('delete', $question);

        $question->delete();

        return redirect('/questions');

    }

    public function getSubjectsForCourses(Request $request){
        $courseId = $request->input('course');
        
        $course = $this->userService->getCourseById($courseId);
        $subjects = $course->subjects->pluck('name', 'id');
        $subjects = $subjects->isEmpty() ? null : $subjects;

        if(empty($subjects)){
            return view('/components/core/partials-subject', ['subjects' => [""=>"No Subjects Available"]]);
        }   

        return view('/components/core/partials-subject', ['subjects' => $subjects]);    
    }
    public function getTopicsForSubjects(Request $request){
        $subjectId = $request->input('subject');

        if(empty($subjectId)){
            return view('/components/core/partial-topic', ['topics' => [""=>"No Topics Available"]]);    
        }   

        $subject = $this->userService->getSubjectById($subjectId);

        $topics = $subject->topics->pluck('name', 'id');

        if(empty($topics)){
            return view('/components/core/partial-topic', ['topics' => ["No Topics Available"]]);    
        }   

        return view('/components/core/partial-topic', ['topics' => $topics]);    
    }

    public function question_type_show(Question $question){
        $question_type_data = $this->questionService->getQuestionTypeShow($question);

        $data = [
            'question' => $question,
            'question_type_data' => $question_type_data
        ];
    

        return view('questions-types/show', $data);
    }

    public function loadQuestionType(Request $request)
    {
        $type = $request->input('type');
        $itemCount = (int) $request->input('item_count', 4);
        $isEdit = filter_var($request->input('edit'), FILTER_VALIDATE_BOOLEAN);
        $question = null;
        $validTypes = ['multiple_choice', 'true_or_false', 'identification', 'ranking', 'matching', 'coding'];
        
        if (!in_array($type, $validTypes)) {
            abort(400, 'Invalid question type');
        }

        if ($isEdit) {
            $questionId = $request->input('question_id');
            if (!$questionId) {
                abort(400, 'Missing question_id');
            }
            $question = Question::findOrFail($questionId);
            $question_type_data = $this->questionService->getQuestionTypeShow($question);
            if ($type == 'coding'){
                $subjects = Subject::whereIn('course_id', $question->topic->subject->course()->get()->pluck('id'))->get()->pluck('name', 'id');
                $programming_languages = [
                        'java' => "Java",
                        'c++' => "C++",
                        'python' => "Python",
                    ];
            }
            
        }

        return match ($type) {
            'multiple_choice' => view('questions-types/multiple-choice', $isEdit 
                ? compact('question_type_data', 'isEdit') 
                : compact('isEdit')),
            
            'true_or_false'   => view('questions-types/true-false', $isEdit 
                ? compact('question_type_data', 'isEdit') 
                : compact('isEdit')),

            'identification'  => view('questions-types/identification', $isEdit 
                ? compact('question_type_data', 'isEdit') 
                : compact('isEdit')),

            'ranking' => view('questions-types/rank-order-process', $isEdit 
                ? compact('question_type_data', 'isEdit') 
                : compact('itemCount', 'question', 'isEdit')),

            'matching' => view('questions-types/matching-items', $isEdit 
                ? compact('question_type_data', 'isEdit') 
                : compact('itemCount','isEdit')),

            'coding' => view('questions-types/coding', $isEdit 
                ? compact('question_type_data', 'isEdit', 'subjects', 'question', 'programming_languages') 
                : compact('isEdit')),
        };

    }

    public function previewMarkdown(Request $request){
        $data = $request->post();
        $markdown = Str::of($request->post('instruction'))->markdown([
            'html_input' => 'strip',
        ]);

        return view('components/core/preview-markdown', ['data'=> $data, 'markdown' => $markdown]);
    }

    public function togglePreviewButton(){

        return view('components/core/toggle-preview');
    }

    public function validateCompleteSolution(Request $request){
        $complete_solution = $request->post('validate-complete-solution');
        $test_case = $request->post('validate-test-case');

        if (empty($complete_solution) || empty($test_case)) {
            $api_data = ['error' => 'Complete solution and test case are both required.'];
        } else {
            $language = $request->post('language-to-validate');
            $api_data = $this->questionService::validate($language, $complete_solution, $test_case);
        }


        $data = [
            'post_data' => $request->post(),
            'api_data' => $api_data
        ];
        
        return view('questions-types/validate-complete-solution', ['data'=> $data]);
    }

}
