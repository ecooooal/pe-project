<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class AcademicYear extends Model
{
    protected $fillable = [
        'year_label',
        'start_date',
        'end_date',
        'is_current',
    ];

        protected static function booted()
    {
        static::saving(function ($year) {
            if ($year->is_current) {
                static::where('id', '!=', $year->id)
                      ->update(['is_current' => false]);

                Cache::forget('academic_year_current');
            }
        });
    }

        public static function current(): ?self
    {
        return Cache::remember('academic_year_current', 3600, function () {
            return static::where('is_current', true)->first();
        });
    }


}
