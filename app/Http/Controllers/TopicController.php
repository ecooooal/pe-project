<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use App\Models\Topic;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TopicController extends Controller
{
    public function getTopicsForUser()
    {   
        $user = auth()->user();
        $user_courses = $user->getCourseIds();
        $subjectIds = Subject::whereIn('course_id', $user_courses)->get()->pluck('id');
        return Topic::whereIn('subject_id', $subjectIds)->get();

    }

    public function index(){
        $topic_subjects = $this->getTopicsForUser();
        $header = ['ID', 'Subject', 'Name', 'Year Level', 'Date Created'];
        $rows = $topic_subjects->map(function ($topic) {
            return [
                'id' => $topic->id,
                'course' => $topic->subject->name,
                'name' => $topic->name,
                'year_level' => $topic->subject->year_level,
                'Date Created' => Carbon::parse($topic->created_at)->format('m/d/Y')
            ];
        });

        $data = [
            'headers' => $header,
            'rows' => $rows
        ];

        return view('topics/index', $data);
    }

    public function show(Topic $topic){
    
        return view('topics/show', ['topic' => $topic]);
    }
    public function create(){
        $subjects = Subject::all()->pluck('name', 'id');

        return view('topics/create', ['subjects' => $subjects]);
    }

    public function store(){
        dd(request()->post());
    }
    public function edit(Topic $topic){
        $subjects = Subject::whereIn('course_id', $topic->subject->course()->get()->pluck('id'))->get()->pluck('name', 'id');

        $data = [
            'topic' => $topic, 
            'subjects' => $subjects
        ];
    
        return view('topics/edit', $data);
    }
}
