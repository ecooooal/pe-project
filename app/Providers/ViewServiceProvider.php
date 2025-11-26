<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class ViewServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Share Firebase config with all views
        View::share('firebase_config', config('firebase.client'));
    }

    public function register(): void
    {
        //
    }
}