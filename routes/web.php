<?php

use App\Http\Controllers\SessionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('landing-page');
});

Route::get('/test', function () {
    return view('test-page');
})->middleware('auth.basic');

Route::post('/login', [SessionController::class, 'authenticate']);
Route::post('/logout', [SessionController::class, 'logout']);


Route::get('/faculty', function () {
    return view('faculty-home');
});

Route::get('/exams', function(){
    return view('exams/index');
});
Route::get('/exams/create', function(){
    return view('exams/create');
});
Route::get('/exams/show', function(){
    return view('exams/show');
});
Route::get('/exams/edit', function(){
    return view('exams/edit');
});
Route::get('/exams/questions', function(){
    return view('exams/questions');
});


Route::get('/questions', function(){
    return view('questions/index');
});
Route::post('/questions', function(Request $request){
    dd($request->post());
});
Route::get('/questions/create', function(){
    return view('questions/create');
});
Route::get('/questions/show', function(){
    return view('questions/show');
});
Route::get('/questions/edit', function(){
    return view('questions/show');
});
Route::get('/questions/create/question-type', function (Request $request) {
    $counter = session('counter', 4);
    $type = $request->query('type'); 
    switch ($type) {
        case 'multiple_choice':
            return view('questions-types/multiple-choice');
        
        case 'true_or_false':
            return view('questions-types/true-false');
        
        case 'identification':
            return view('questions-types/identification', compact('counter'));

        case 'ranking_ordering_process':
            return view('questions-types/rank-order-process');
        
        case 'coding':
            return view('questions-types/coding');

        default:
            return '';
        }
});

Route::get('/questions/create/add-item', function () {
    $counter = session('counter', 4);
    $counter++;
    session()->flash('counter', $counter);

    return view('questions-types/new-text-item', ['counter' => $counter]);
});

Route::get('/topics', function(){
    return view('topics/index');
});
Route::get('/topics/create', function(){
    return view('topics/create');
});
Route::get('/topics/show', function(){
    return view('topics/show');
});
Route::get('/topics/edit', function(){
    return view('topics/edit');
});
Route::get('/topics/questions', function(){
    return view('topics/questions');
});

Route::get('/subjects', function(){
    return view('subjects/index');
});
Route::get('/subjects/create', function(){
    return view('subjects/create');
});
Route::get('/subjects/show', function(){
    return view('subjects/show');
});
Route::get('/subjects/edit', function(){
    return view('subjects/edit');
});
Route::get('/subjects/questions', function(){
    return view('subjects/questions');
});

Route::get('/reviewers', function(){
    return view('reviewers/index');
});
Route::get('/reviewers/create', function(){
    return view('reviewers/create');
});
Route::post('/reviewers', function(Request $request){
    dd($request->post());
});
Route::get('/reviewers/show', function(){
    return view('reviewers/show');
});
Route::get('/reviewers/edit', function(){
    return view('reviewers/edit');
});
Route::get('/reviewers/questions', function(){
    return view('reviewers/questions');
});

Route::get('/exams/hello/time/set', function(){
    return view('exams/index');
});

Route::get('/reports', function(){
    return view('reports');
});

Route::get('/notifications', function(){
    return view('notifications');
});

Route::get('/settings', function(){
    return view('settings');
});

Route::get('/profiles/show', function(){
    return view('profiles/show');
});
Route::get('/profiles/subjects', function(){
    return view('profiles/subjects');
});
Route::get('/profiles/courses', function(){
    return view('profiles/courses');
});
//testing hi i'm new branch