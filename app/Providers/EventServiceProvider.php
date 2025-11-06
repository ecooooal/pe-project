<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
    \App\Events\StudentEnrolled::class => [
    \App\Listeners\SendEnrollmentNotification::class,
        ],
    \App\Events\ExamSubmitted::class => [
    \App\Listeners\SendSubmissionNotification::class,
        ],
    \App\Events\ExamResultsPublished::class => [
    \App\Listeners\SendResultsNotification::class,
        ],
    ];
    
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
