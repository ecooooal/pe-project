<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use App\Mail\TestMail;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class ReviewerController extends Controller
{
    /**
     * Display all reviewer files.
     */
    public function index()
    {
        $reviewerFiles = DB::table('reviewer_files')
            ->join('reviewers', 'reviewer_files.reviewer_id', '=', 'reviewers.id')
            ->select(
                'reviewer_files.id',
                'reviewer_files.topic',
                'reviewer_files.original_name',
                'reviewer_files.path',
                'reviewer_files.created_at'
            )
            ->get();

        $rows = $reviewerFiles->map(function ($file) {
            return [
                $file->id,
                $file->topic,
                $file->original_name ?? basename($file->path),
                $file->path,
                Carbon::parse($file->created_at)->format('d-m-Y'),
            ];
        })->toArray();

        return view('reviewers.index', [
            'headers' => ['ID', 'Topic', 'Name', 'Path', 'Date Created'],
            'rows' => $rows,
        ]);
    }

    /**
     * Show the form to create a new reviewer file.
     */
    public function create()
    {
        // Optional: fetch all reviewers to choose from
        $reviewers = DB::table('reviewers')->get();

        return view('reviewers/create', [
            'reviewers' => $reviewers,
        ]);
    }

    /**
     * Store a new reviewer file.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'topic' => 'required|string|max:255',
            'email' => 'required|string',
            'reviewerFile.*' => 'required|file|mimes:pdf|max:10240'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Create reviewer record first
        $reviewerId = DB::table('reviewers')->insertGetId([
            'email' => $request->email,
            'topic' => $request->topic,
            'author' => auth()->user()->name,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if (!$reviewerId) {
            return back()->with('error', 'Failed to create reviewer record.');
        }

        // Parse emails
        $emailList = preg_split('/[\s,]+/', $request->email);
        $emailList = array_filter($emailList, function ($email) {
            return filter_var($email, FILTER_VALIDATE_EMAIL);
        });

        if (empty($emailList)) {
            return back()->with('error', 'No valid email addresses provided.');
        }

        // Store uploaded files
        $attachmentPaths = [];
        foreach ($request->file('reviewerFile') as $file) {
            $originalName = $file->getClientOriginalName();
            $uniqueName = uniqid() . '_' . $originalName;
            $path = $file->storeAs('uploads/reviewers', $uniqueName, 'public');

            DB::table('reviewer_files')->insert([
                'topic' => $request->topic,
                'reviewer_id' => $reviewerId,
                'path' => 'storage/' . $path,
                'original_name' => $originalName,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $attachmentPaths[] = storage_path("app/public/" . $path);
        }

        // Send email with attachments
        $data = [
            'subject' => 'Reviewer Assignment Notification',
            'name' => auth()->user()->name ?? 'Instructor',
            'topic' => $request->topic,
            'message' => "Hi there, please check the attached reviewer file(s) related to your assigned topic.",
        ];

        foreach ($emailList as $email) {
            Mail::to($email)->send(new TestMail($data, $attachmentPaths));
        }

        return redirect('/reviewers')->with('success', 'Reviewer added and email sent.');
    }

    /**
     * Delete a reviewer file by ID.
     */
    public function destroy($id)
    {
        // Find the reviewer file record
        $file = DB::table('reviewer_files')->where('id', $id)->first();

        if (!$file) {
            return redirect()->back()->with('error', 'Reviewer file not found.');
        }

        // Delete the physical file
        $storagePath = str_replace('storage/', '', $file->path);
        if (Storage::disk('public')->exists($storagePath)) {
            Storage::disk('public')->delete($storagePath);
        }

        // Delete the reviewer_file record
        DB::table('reviewer_files')->where('id', $id)->delete();

        // If no more files linked to reviewer, delete the reviewer
        $remaining = DB::table('reviewer_files')
            ->where('reviewer_id', $file->reviewer_id)
            ->count();

        if ($remaining === 0) {
            DB::table('reviewers')->where('id', $file->reviewer_id)->delete();
        }

        return redirect()->back()->with('success', 'Reviewer file deleted successfully.');
    }
}
