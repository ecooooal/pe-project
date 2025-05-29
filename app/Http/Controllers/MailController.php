<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\TestMail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class MailController extends Controller
{
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'topic' => 'required|string',
            'email' => 'required|string',
            'reviewerFile.*' => 'required|file|max:10240'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $emailList = explode(",", $request->email);
        $emailList = array_map('trim', $emailList);
        $emailList = array_filter($emailList, function ($email) {
            return filter_var($email, FILTER_VALIDATE_EMAIL);
        });
       
        if (empty($emailList)) {
            return back()->with('error', 'No valid email addresses provided.');
        }
        

        $attachmentPaths = [];
        foreach ($request->file('reviewerFile') as $file) {
            $randomName = uniqid() . '_.' . $file->getClientOriginalExtension();
            $path = "temp_attachments/{$randomName}";
            $file->move(storage_path('app/temp_attachments'), $randomName);

            $attachmentPaths[] = storage_path("/app/{$path}");
        }
        try {
            $data = [
                'subject' => 'Reviewer Assignment Notification', // More descriptive subject
                'name' => $request->name,
                'topic' => $request->topic,
                'message' => "Hi there, a reviewer has been assigned to you regarding your performance. Please check the attached file(s) for details and guidance.", // More professional message
            ];

           
            foreach ($emailList as $email) {
                
                Mail::to($email)->send(new TestMail($data, $attachmentPaths));
            }

           
            foreach ($attachmentPaths as $path) {
               
                $relativePath = str_replace(Storage::path(''), '', $path);
                Storage::delete($relativePath);
            }

            return back()->with('success', 'Emails sent successfully to all recipients!');

        } catch (\Exception $e) {
           
            \Log::error('Mail sending failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'emails' => $emailList,
                'attachment_paths' => $attachmentPaths
            ]);

         
            foreach ($attachmentPaths as $path) {
                $relativePath = str_replace(Storage::path(''), '', $path);
                Storage::delete($relativePath);
            }

            return back()->with('error', 'Failed to send emails. Please check logs for details. Error: ' . $e->getMessage());
        }
    }
}
