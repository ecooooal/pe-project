<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Subject;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    public function getSubjectsForUser()
    {   
        $user = auth()->user();
        $courseIds = $user->courses()->get()->pluck('id');

        return Subject::with('course') 
            ->whereIn('course_id', $courseIds)
            ->get();
    }
    public function index(){
        $subject_courses = $this->getSubjectsForUser();
        $header = ['ID', 'Course', 'Name', 'Year Level', 'Date Created'];
        $rows = $subject_courses->map(function ($subject) {
            return [
                'id' => $subject->id,
                'course' => $subject->course->name,
                'name' => $subject->name,
                'year_level' => $subject->year_level,
                'Date Created' => Carbon::parse($subject->created_at)->format('m/d/Y')
            ];
        });

        $data = [
            'headers' => $header,
            'rows' => $rows
        ];

        return view('subjects/index', $data);
    }

    public function show(Subject $subject){
    
        return view('subjects/show', ['subject' => $subject]);
    }

    public function create(){
        $courses = Course::all()->pluck('name', 'id');

        return view('subjects/create', ['courses' => $courses]);
    }

    public function store(){
        dd(request()->post());
    }

    public function edit(Subject $subject){
        $courses = Course::all()->pluck('name', 'id');

        $data = [
            'subject' => $subject, 
            'courses' => $courses
        ];
    
        return view('subjects/edit', $data);
    }
}
