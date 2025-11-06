<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Events\StudentEnrolled;
use App\Models\Notification;

class SendEnrollmentNotification
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
    public function handle(StudentEnrolled $event): void
    {
        // Student-side notification
        $studentNotif = Notification::create([
            'title'   => 'Enrollment Confirmed',
            'message' => "You are successfully enrolled in {$event->exam->name}.",
            'is_public' => false,
        ]);
        $studentNotif->users()->attach($event->student->id);

        // Professor-side notification
        $profNotif = Notification::create([
            'title'   => 'New Student Enrolled',
            'message' => "{$event->student->first_name} enrolled in {$event->exam->name}.",
            'is_public' => false,
        ]);
        $profNotif->roles()->attach([1,2,3,4]); 
        //superadmin role_id = 1
        //faculty role_id = 2
        //department head role_id = 3
        //college dean role_id = 4
        
    }
}
