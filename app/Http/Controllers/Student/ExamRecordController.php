<?php

namespace App\Http\Controllers\Student;

use App\Models\ExamRecord;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ExamRecordController extends Controller
{
    public function index()
    {
        return view(view: 'students/records/show');
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        //
    }

    public function show(ExamRecord $examRecord)
    {
        return view(view: 'students/exams/get-exam-papers');
    }
}
