<?php

namespace App\Http\Controllers;

use App\Factories\QuestionFactory;
use App\Models\Question;
use App\Models\Subject;
use App\Models\Topic;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Validator;

class QuestionController extends Controller
{
    public function getQuestionsForUser()
    {   
        $user = auth()->user();
        $user_courses = $user->getCourseIds();
        $subjectIds = Subject::whereIn('course_id', $user_courses)->get()->pluck('id');
        $topicIds = Topic::whereIn('subject_id', $subjectIds)->get()->pluck('id');;
        return Question::wherein('topic_id', $topicIds)->get();
    }
    
    public function index(){
        $questions = $this->getQuestionsForUser();
        $header = ['ID', 'Name', 'Subject', 'Topic', 'Type', 'Author', 'Date Created'];
        $rows = $questions->map(function ($question) {
            return [
                'id' => $question->id,
                'name' => $question->name,
                'topic' => $question->topic->name,
                'subject' => $question->topic->subject->name,
                'type' => $question->question_type->value,
                'author' => $question->author->getFullName(),
                'Date Created' => Carbon::parse($question->created_at)->format('m/d/Y')
            ];
        });

        $data = [
            'headers' => $header,
            'rows' => $rows
        ];

        return view('questions/index', $data);
    }

    public function show(Question $question){
        $question_type = $question->getTypeModel();
        $data = [
            'question' => $question,
            'question_type' => $question_type
        ];
        return view('questions/show', $data);
    }
    public function create(){
        $topics = Topic::all()->pluck('name', 'id');
        $question_types = [
            '' => 'Select A Question Type',
            'multiple_choice' => 'Multiple Choice',
            'true_or_false'=> 'True or False',
            'identification' => 'Identification',
            'ranking' => 'Ranking/Ordering/Process',
            'matching' => 'Matching Items',
            'coding' => 'Coding'
        ];

        $data =[
            'topics' => $topics,
            'question_types' => $question_types
        ];

        return view('questions/create', $data);
    }

    public function store(){
        $validator = Validator::make(request()->all(), [
            'topic' => ['required'],
            'type' => ['required'],
            'name' => ['required', 'string'],
            'points' => ['required', 'integer', 'min:1'],
            'items.*' => ['required', 'string', 'min:1'],
            'solution' => ['required', 'string'], 
        ], [
            'items.*.required' => 'This field is required.',
        ]);

        $question_type = request('type');
        $item_count = count(request()->input('items', []));
        if ($validator->fails()) {
            return redirect()->route('question.types', ['type' => $question_type, 'item_count' => $item_count])
                ->withErrors($validator)
                ->withInput();
        }

        $data = $validator->validated();

        \Log::info('data can be stored', $data);
        QuestionFactory::create($data);
        \Log::info('Question Creation Successful');
        
        return redirect('/questions');
    }
    public function edit(Question $question){
        $subjects = Subject::whereIn('course_id', $question->subject->course()->get()->pluck('id'))->get()->pluck('name', 'id');

        $data = [
            'question' => $question, 
            'subjects' => $subjects
        ];
    
        return view('questions/edit', $data);
    }

    public function update(Question $question){
        $this->authorize('update', $question);


        request()->validate([
            'name'    => ['required'],
            'subject'     => ['required', 'integer'],
        ]);

        $question->update([
            'name' => request('name'),
            'subject_id' => request('subject'),
        ]);

        return redirect()->route('questions.show', $question);
    }
    public function destroy(Question $question){

        $this->authorize('delete', $question);

        $question->delete();

        return redirect('/questions');

    }
}
