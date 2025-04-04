<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
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
    return view('exams/show');
});


Route::get('/questions', function(){
    return view('questions/index');
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
    return view('topics/show');
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
    return view('subjects/show');
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

Route::get('/profile', function(){
    return view('profile');
});




// Route::get('/jobs/create', [JobController::class, 'create']);
// Route::post('/jobs', [JobController::class, 'store']);
// Route::get('/jobs/{job}', [JobController::class, 'show']);
// Route::get('/jobs/{job}/edit', [JobController::class, 'edit'])->middleware(['auth', 'can:edit-job,job']);
// Route::patch('/jobs/{job}', [JobController::class, 'update']);
// Route::delete('/jobs/{job}', [JobController::class, 'delete']);