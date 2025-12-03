<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Response;
use Dompdf\Dompdf;
use Dompdf\Options;
use App\Models\Reviewer;
use App\Jobs\SendReviewerEmailJob;
use App\Jobs\SendExamRecordEmailJob;
use Carbon\Carbon;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class MailController extends Controller
{
    // ============================================================================
    // HELPER METHODS - Toaster
    // ============================================================================
    
    private function toaster($type, $message, $title = null)
    {
        $titles = [
            'success' => $title ?? 'Success!',
            'error' => $title ?? 'Error!',
            'warning' => $title ?? 'Warning!',
            'info' => $title ?? 'Info'
        ];

        session()->flash('toast', json_encode([
            'status' => ucfirst($type) . '!',
            'message' => $message,
            'type' => $type
        ]));
    }

    // ============================================================================
    // HELPER METHODS - User Name Extraction
    // ============================================================================
    
    private function getUserFullName($user)
    {
        if (isset($user->first_name) && isset($user->last_name)) {
            return $user->first_name . ' ' . $user->last_name;
        } elseif (isset($user->profile->first_name) && isset($user->profile->last_name)) {
            return $user->profile->first_name . ' ' . $user->profile->last_name;
        } elseif (isset($user->student->first_name) && isset($user->student->last_name)) {
            return $user->student->first_name . ' ' . $user->student->last_name;
        } elseif (isset($user->name)) {
            return $user->name;
        } else {
            return $user->email ?? 'User';
        }
    }

    private function getUserFirstName($user)
    {
        if (isset($user->first_name)) {
            return $user->first_name;
        } elseif (isset($user->profile->first_name)) {
            return $user->profile->first_name;
        } elseif (isset($user->student->first_name)) {
            return $user->student->first_name;
        } elseif (isset($user->name)) {
            return explode(' ', $user->name)[0];
        } else {
            return 'Student';
        }
    }

    private function getUserLastName($user)
    {
        if (isset($user->last_name)) {
            return $user->last_name;
        } elseif (isset($user->profile->last_name)) {
            return $user->profile->last_name;
        } elseif (isset($user->student->last_name)) {
            return $user->student->last_name;
        } elseif (isset($user->name)) {
            $parts = explode(' ', $user->name);
            return count($parts) > 1 ? end($parts) : '';
        } else {
            return '';
        }
    }

    // ============================================================================
    // REVIEWER MANAGEMENT - Index & Display
    // ============================================================================
    
    /**
     * Display all reviewer files (single table version)
     */
    public function reviewersIndex()
    {
        $reviewer_subjects = Reviewer::distinct()->pluck('name')->sort()->toArray();
        $reviewer_topics = DB::table('reviewers')
            ->join('topics', 'reviewers.topic', '=', 'topics.id')
            ->select('topic', 'topics.name')
            ->distinct()
            ->pluck('name', 'topic')
            ->sort()
            ->toArray();      

        $query = QueryBuilder::for(Reviewer::class)
            ->with('topicDetail')
            ->allowedFilters(['name', AllowedFilter::exact('topic'),
            ])
            ->allowedIncludes('topicDetail')
            ->paginate(10)
            ->appends(request()->query());

        $rows = $query->map(function ($file) {
            // Extract original filename from path (remove unique prefix)
            $fileName = basename($file->path);
            $displayName = preg_replace('/^\w+_/', '', $fileName); // Remove uniqid prefix
            return [
                $file->id,
                $file->name,  // Subject name,
                $file->topicDetail->name ?? 'N/A',
                $displayName, // Clean filename
                Carbon::parse($file->created_at)->format('d-m-Y'),
            ];
        })->toArray();
        return view('reviewers.index', [
            'headers' => ['ID', 'Subject', 'Topic', 'File Name', 'Date Created', 'Actions'],
            'rows' => $rows,
            'models' => $query,
            'subjects' => $reviewer_subjects,
            'topics' => $reviewer_topics
        ]);
    }

    // ============================================================================
    // REVIEWER MANAGEMENT - Create Form
    // ============================================================================
    
    /**
     * Show form for creating reviewers
     */
    public function create()
    {
        $subjects = [];
        try {
            if (class_exists('\App\\Models\\Subject')) {
                $subjects = \App\Models\Subject::with('topics')->orderBy('name')->get();
            }
        } catch (\Exception $e) {
            \Log::warning('Could not load subjects for reviewer create form: ' . $e->getMessage());
        }

        $reviewers = DB::table('reviewers')->get();

        return view('reviewers.create', [
            'subjects' => $subjects,
            'reviewers' => $reviewers,
        ]);
    }

    // ============================================================================
    // REVIEWER MANAGEMENT - Store/Upload
    // ============================================================================
    
    /**
     * Store new reviewer files (single table version)
     */
    public function index(Request $request)
    {
        // Validation with custom error messages
        $validator = Validator::make($request->all(), [
            'subject_id' => 'required|integer|exists:subjects,id',
            'topic_id' => 'required|integer',
            'reviewerFile.*' => 'required|file|mimes:pdf|max:5120'
        ], [
            'reviewerFile.*.max' => 'Each PDF file must not exceed 5MB.',
            'reviewerFile.*.mimes' => 'Only PDF files are allowed.',
            'subject_id.exists' => 'Selected subject does not exist.',
        ]);

        if ($validator->fails()) {
            $this->toaster('error', $validator->errors()->first());
            return back()->withErrors($validator)->withInput();
        }

        try {
            // Get subject name
            $subjectName = 'Subject ' . $request->input('subject_id');
            try {
                if (class_exists('\App\\Models\\Subject')) {
                    $subjectModel = \App\Models\Subject::find($request->input('subject_id'));
                    if ($subjectModel) {
                        $subjectName = $subjectModel->name;
                    }
                }
            } catch (\Exception $e) {
                \Log::warning('Subject lookup failed: ' . $e->getMessage());
            }

            // Get topic ID (store as bigInteger as per migration)
            $topicId = $request->input('topic_id');

            $user = Auth::user();
            $authorName = $this->getUserFullName($user);

            // Store uploaded files with duplicate checking
            $fileCount = 0;
            $skippedFiles = [];

            foreach ($request->file('reviewerFile') as $file) {
                $originalName = $file->getClientOriginalName();
                $uniqueName = uniqid() . '_' . $originalName;

                // Check for duplicates (same subject + topic + filename)
                $exists = DB::table('reviewers')
                    ->where('name', $subjectName)
                    ->where('topic', $topicId)
                    ->where('path', 'LIKE', '%' . $originalName)
                    ->exists();

                if ($exists) {
                    $skippedFiles[] = $originalName;
                    continue; // Skip duplicate
                }

                // Store file in PRIVATE storage
                $path = $file->storeAs('reviewers', $uniqueName, 'private');

                // Insert directly to reviewers table
                DB::table('reviewers')->insert([
                    'name' => $subjectName,      // Subject name (Math, Physics, etc.)
                    'topic' => $topicId,          // Topic ID as bigInteger
                    'author' => $authorName,      // Professor name
                    'path' => $path,              // File path in storage
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $fileCount++;
            }

            // Handle case where no files were uploaded
            if ($fileCount === 0) {
                if (count($skippedFiles) > 0) {
                    $this->toaster('warning', 'All files were duplicates and were not uploaded: ' . implode(', ', $skippedFiles));
                    return back();
                }

                $this->toaster('error', 'No files were uploaded.');
                return back();
            }

            // Build success message
            $successMessage = $fileCount > 1 
                ? "Successfully uploaded {$fileCount} reviewer file(s)!" 
                : 'Reviewer uploaded successfully!';

            if (count($skippedFiles) > 0) {
                $successMessage .= ' (' . count($skippedFiles) . ' duplicate(s) skipped)';
            }

            $this->toaster('success', $successMessage);

            // Check if user wants to add another
            if ($request->has('add_another')) {
                return redirect('/reviewers/create');
            }

            return redirect('/reviewers');

        } catch (\Exception $e) {
            \Log::error('Reviewer upload failed: ' . $e->getMessage());
            $this->toaster('error', 'Failed to upload reviewer. Please try again.');
            return back()->withInput();
        }
    }

    // ============================================================================
    // REVIEWER MANAGEMENT - Delete
    // ============================================================================
    
    /**
     * Delete reviewer file (single table version)
     */
    public function destroy($id)
    {
        try {
            $file = DB::table('reviewers')->where('id', $id)->first();

            if (!$file) {
                return response()->json([
                    'success' => false,
                    'message' => 'Reviewer file not found.'
                ], 404);
            }

            // Delete physical file from PRIVATE storage
            if (Storage::disk('private')->exists($file->path)) {
                Storage::disk('private')->delete($file->path);
                \Log::info('Deleted reviewer file: ' . $file->path);
            }

            // Delete database record
            DB::table('reviewers')->where('id', $id)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Reviewer file deleted successfully'
            ]);

        } catch (\Exception $e) {
            \Log::error('Reviewer deletion failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete reviewer: ' . $e->getMessage()
            ], 500);
        }
    }

    // ============================================================================
    // REVIEWER MANAGEMENT - Download
    // ============================================================================
    
    /**
     * Download reviewer PDF (single table version)
     */
    public function downloadReviewer($id)
    {
        try {
            $user = auth()->user();

            // Enhanced security check
            if (!$user) {
                abort(403, 'You must be logged in to download files.');
            }

            // Add permission check for faculty access
            if (!$user->can('view faculty')) {
                abort(403, 'You do not have permission to download reviewer files.');
            }

            $file = DB::table('reviewers')->where('id', $id)->first();

            if (!$file) {
                abort(404, 'Reviewer file not found.');
            }

            // Check file exists in PRIVATE storage
            if (!Storage::disk('private')->exists($file->path)) {
                \Log::error('Reviewer file missing from storage: ' . $file->path);
                abort(404, 'File not found on server.');
            }

            $fullPath = Storage::disk('private')->path($file->path);
            
            // Extract original filename (remove unique prefix)
            $fileName = basename($file->path);
            $originalName = preg_replace('/^\w+_/', '', $fileName);

            return response()->download($fullPath, $originalName);

        } catch (\Exception $e) {
            \Log::error('Reviewer download failed: ' . $e->getMessage());
            abort(404, 'Reviewer not found or file is missing.');
        }
    }

    // ============================================================================
    // EMAIL REVIEWER - For Students
    // ============================================================================
    
    /**
     * Method for students to email themselves a reviewer (single table version)
     */
    public function emailReviewer(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'reviewer_file_id' => 'required|integer',
            'student_email' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Invalid data provided.'], 422);
        }

        try {
            $student = Auth::user();
            $emailAddress = ($request->student_email === 'auto' || empty($request->student_email)) 
                ? $student->email 
                : $request->student_email;

            if (!filter_var($emailAddress, FILTER_VALIDATE_EMAIL)) {
                return response()->json(['success' => false, 'message' => 'Invalid email address.'], 422);
            }

            $file = DB::table('reviewers')
                ->where('id', $request->reviewer_file_id)
                ->first();

            if (!$file) {
                return response()->json(['success' => false, 'message' => 'Reviewer file not found.'], 404);
            }

            // Use PRIVATE storage
            $filePath = Storage::disk('private')->path($file->path);

            if (!file_exists($filePath)) {
                return response()->json(['success' => false, 'message' => 'Reviewer file not found on server.'], 404);
            }

            // Get topic name for email subject
            $topicName = 'Topic ' . $file->topic;
            try {
                if (class_exists('\App\\Models\\Topic')) {
                    $topicModel = \App\Models\Topic::find($file->topic);
                    if ($topicModel) {
                        $topicName = $topicModel->name;
                    }
                }
            } catch (\Exception $e) {
                \Log::warning('Topic lookup failed: ' . $e->getMessage());
            }

            dispatch(new SendReviewerEmailJob(
                [$emailAddress],
                $topicName,
                $filePath,
                [
                    "student" => [
                        "first_name" => $this->getUserFirstName($student),
                        "last_name" => $this->getUserLastName($student),
                    ]
                ]
            ));

            return response()->json(['success' => true, 'message' => 'Reviewer email queued! You will receive it shortly.']);

        } catch (\Exception $e) {
            \Log::error('Student reviewer email failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to queue reviewer email.'], 500);
        }
    }

    // ============================================================================
    // EMAIL EXAM RECORD - For Students
    // ============================================================================
    
    /**
     * Method for students to email themselves an exam record
     */
    public function emailExamRecord(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'exam_record_id' => 'required|integer|exists:exam_records,id',
            'student_email' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Invalid data provided.'], 422);
        }

        try {
            $student = Auth::user();
            $emailAddress = ($request->student_email === 'auto' || empty($request->student_email)) 
                ? $student->email 
                : $request->student_email;

            if (!filter_var($emailAddress, FILTER_VALIDATE_EMAIL)) {
                return response()->json(['success' => false, 'message' => 'Invalid email address.'], 422);
            }

            $examRecord = \App\Models\ExamRecord::with(['exam', 'subjects'])->find($request->exam_record_id);

            if (!$examRecord) {
                return response()->json(['success' => false, 'message' => 'Exam record not found.'], 404);
            }

            $studentData = [
                'first_name' => $this->getUserFirstName($student),
                'last_name' => $this->getUserLastName($student),
                'email' => $student->email,
                'id' => $student->id
            ];

            dispatch(new SendExamRecordEmailJob(
                [$emailAddress],
                'Your Exam Record - ' . $examRecord->exam->name,
                $examRecord->toArray(),
                $studentData
            ));

            return response()->json(['success' => true, 'message' => 'Exam record email queued! You will receive it shortly.']);

        } catch (\Exception $e) {
            \Log::error('Student exam record email failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to queue exam record email.'], 500);
        }
    }

    // ============================================================================
    // EXAM RECORD PDF - Download
    // ============================================================================
    
    /**
     * Download exam record as PDF
     */
    public function downloadExamRecord($examRecordId)
    {
        try {
            $student = Auth::user();
            $examRecord = \App\Models\ExamRecord::with(['exam', 'subjects'])->find($examRecordId);

            if (!$examRecord) {
                abort(404, 'Exam record not found.');
            }

            if (!$examRecord->exam) {
                abort(404, 'Associated exam has been deleted and is no longer available.');
            }

            $tempDir = storage_path('app/temp_downloads');
            if (!is_dir($tempDir)) {
                mkdir($tempDir, 0755, true);
            }

            $fileName = 'exam_record_' . $examRecord->id . '_' . uniqid() . '.pdf';
            $pdfPath = $tempDir . '/' . $fileName;

            $this->createExamRecordPDF($examRecord, $pdfPath);

            $downloadName = 'Exam_Record_' . str_replace(' ', '_', $examRecord->exam->name) . '_Attempt_' . $examRecord->attempt . '.pdf';

            return response()->download($pdfPath, $downloadName)->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            \Log::error('Exam record download failed: ' . $e->getMessage());
            abort(500, 'Failed to generate exam record PDF: ' . $e->getMessage());
        }
    }

    // ============================================================================
    // EXAM RECORD PDF - Helper Methods
    // ============================================================================
    
    /**
     * Create exam record PDF using Dompdf
     */
    private function createExamRecordPDF($examRecord, $filePath)
    {
        $student = Auth::user();
        $percentage = ($examRecord->total_score / $examRecord->exam->max_score) * 100;
        $performanceData = $this->getPerformanceAnalysis($percentage);

        $html = $this->generateExamRecordHTML($examRecord, $student, $percentage, $performanceData);

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        file_put_contents($filePath, $dompdf->output());
    }

    /**
     * Get performance analysis data
     */
    private function getPerformanceAnalysis($percentage)
    {
        if ($percentage >= 90) {
            return [
                'level' => 'Excellent',
                'recommendation' => 'Keep up the outstanding work!'
            ];
        } elseif ($percentage >= 80) {
            return [
                'level' => 'Very Good',
                'recommendation' => 'Continue your good study habits.'
            ];
        } elseif ($percentage >= 70) {
            return [
                'level' => 'Good',
                'recommendation' => 'Focus on areas that need improvement.'
            ];
        } elseif ($percentage >= 60) {
            return [
                'level' => 'Satisfactory',
                'recommendation' => 'Review study materials and seek help if needed.'
            ];
        } else {
            return [
                'level' => 'Needs Improvement',
                'recommendation' => 'Consider additional study time and tutoring.'
            ];
        }
    }

    /**
     * Generate HTML for exam record PDF
     */
    private function generateExamRecordHTML($examRecord, $student, $percentage, $performanceData)
    {
        $firstName = $this->getUserFirstName($student);
        $lastName = $this->getUserLastName($student);

        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
            color: #333;
        }
        .header {
            text-align: center;
            border-bottom: 3px solid #1e3a8a;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #1e3a8a;
            margin: 0;
            font-size: 28px;
        }
        .info-box {
            background: #f3f4f6;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .info-item {
            margin: 8px 0;
        }
        .info-label {
            font-weight: bold;
            color: #1e3a8a;
        }
        .section {
            margin: 25px 0;
        }
        .section-title {
            color: #1e3a8a;
            font-size: 18px;
            font-weight: bold;
            border-bottom: 2px solid #1e3a8a;
            padding-bottom: 5px;
            margin-bottom: 15px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        th, td {
            border: 1px solid #d1d5db;
            padding: 10px;
            text-align: left;
        }
        th {
            background: #1e3a8a;
            color: white;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background: #f9fafb;
        }
        .score-highlight {
            background: #dbeafe;
            font-weight: bold;
            font-size: 16px;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #e5e7eb;
            text-align: center;
            font-size: 12px;
            color: #6b7280;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>EXAM RECORD</h1>
    </div>

    <div class="info-box">
        <div class="info-item"><span class="info-label">Student:</span> ' . htmlspecialchars($firstName) . ' ' . htmlspecialchars($lastName) . '</div>
        <div class="info-item"><span class="info-label">Exam:</span> ' . htmlspecialchars($examRecord->exam->name) . '</div>
        <div class="info-item"><span class="info-label">Attempt:</span> #' . htmlspecialchars($examRecord->attempt) . '</div>
        <div class="info-item score-highlight"><span class="info-label">Score:</span> ' . htmlspecialchars($examRecord->total_score) . '/' . htmlspecialchars($examRecord->exam->max_score) . ' (' . number_format($percentage, 1) . '%)</div>
        <div class="info-item"><span class="info-label">Status:</span> ' . htmlspecialchars(ucfirst($examRecord->status)) . '</div>
        <div class="info-item"><span class="info-label">Date Taken:</span> ' . date('m/d/Y', strtotime($examRecord->date_taken)) . '</div>
        <div class="info-item"><span class="info-label">Time Taken:</span> ' . htmlspecialchars($examRecord->time_taken) . ' minutes</div>
    </div>';

        if ($examRecord->subjects && count($examRecord->subjects) > 0) {
            $html .= '
    <div class="section">
        <div class="section-title">SUBJECT BREAKDOWN</div>
        <table>
            <thead>
                <tr>
                    <th>Subject Name</th>
                    <th>Score Obtained</th>
                    <th>Maximum Score</th>
                    <th>Percentage</th>
                </tr>
            </thead>
            <tbody>';

            foreach ($examRecord->subjects as $subject) {
                $subjectPercentage = $subject->score > 0 ? ($subject->score_obtained / $subject->score) * 100 : 0;
                $html .= '
                <tr>
                    <td>' . htmlspecialchars($subject->subject_name) . '</td>
                    <td>' . htmlspecialchars($subject->score_obtained) . '</td>
                    <td>' . htmlspecialchars($subject->score) . '</td>
                    <td>' . number_format($subjectPercentage, 1) . '%</td>
                </tr>';
            }

            $html .= '
            </tbody>
        </table>
    </div>';
        }

        $html .= '
    <div class="section">
        <div class="section-title">PERFORMANCE ANALYSIS</div>
        <div class="info-box">
            <div class="info-item"><span class="info-label">Overall Score Percentage:</span> ' . number_format($percentage, 1) . '%</div>
            <div class="info-item"><span class="info-label">Performance Level:</span> ' . htmlspecialchars($performanceData['level']) . '</div>
            <div class="info-item"><span class="info-label">Recommendation:</span> ' . htmlspecialchars($performanceData['recommendation']) . '</div>
        </div>
    </div>

    <div class="footer">
        <p>Generated on: ' . date('F j, Y \a\t g:i A') . '</p>
        <p>Academic System</p>
    </div>
</body>
</html>';

        return $html;
    }
}