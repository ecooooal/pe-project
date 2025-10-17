<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Events\ExamSubmitted;
use App\Models\Notification;

class SendSubmissionNotification
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(ExamSubmitted $event): void
    {
        // Student confirmation
        $studentNotif = Notification::create([
            'title'   => 'Submission Successful',
            'message' => "You have successfully submitted your answers for {$event->exam->name}.",
            'is_public' => false,
        ]);
        $studentNotif->users()->attach($event->student->id);

        // Professor side
        $profNotif = Notification::create([
            'title'   => 'Exam Submitted',
            'message' => "{$event->student->first_name} submitted answers for {$event->exam->name}.",
            'is_public' => false,
        ]);
        $profNotif->roles()->attach([1,2,3,4]); 
        //superadmin role_id = 1
        //faculty role_id = 2
        //department head role_id = 3
        //college dean role_id = 4

    }
}
