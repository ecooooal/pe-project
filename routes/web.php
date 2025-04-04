<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
});

Route::get('/exams', function(){
    return view('exams/index');
});
Route::get('/exams/hello/time/set', function(){
    return view('exams/index');
});
// Route::get('/jobs/create', [JobController::class, 'create']);
// Route::post('/jobs', [JobController::class, 'store']);
// Route::get('/jobs/{job}', [JobController::class, 'show']);
// Route::get('/jobs/{job}/edit', [JobController::class, 'edit'])->middleware(['auth', 'can:edit-job,job']);
// Route::patch('/jobs/{job}', [JobController::class, 'update']);
// Route::delete('/jobs/{job}', [JobController::class, 'delete']);