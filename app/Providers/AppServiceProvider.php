<?php

namespace App\Providers;

use App\Models\AcademicYear;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::preventLazyLoading(!app()->isProduction());
        Paginator::useTailwind();
        // Implicitly grant "Super-Admin" role all permission checks using can()
        Gate::before(function ($user, $ability) {
            if ($user->hasRole('Super-Admin')) {
                return true;
            }
        });

        // Share Firebase config with all views (including Twig templates)
        View::share('firebase_config', config('firebase.client'));

        // Existing view composer
        View::composer(['components/core/header', 'exams/index', 'faculty-home'], function($view){
            $current_academic_year = AcademicYear::current()->academic_year_interval ?? null;
            $view->with('current_academic_year', $current_academic_year);
        });
    }
}