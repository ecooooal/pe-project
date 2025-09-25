<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class AcademicYear extends Model
{
    protected $fillable = [
        'year_label',
        'start_date',
        'end_date',
        'is_locked',
    ];
    protected $casts = [
        'start_date' => 'date:Y-m-d',
        'end_date'   => 'date:Y-m-d',
    ];
    
    public function scopeCurrent($query){
        return $query->where('start_date', '<=',  today())
                     ->where('end_date', '>=',  today())
                     ->where('is_locked', false);
    }
    public static function current(){
        return static::where('start_date', '<=',  today())
                     ->where('end_date', '>=',  today())
                     ->first();
    }
     protected function academicYearInterval(): Attribute
    {
        return Attribute::get(function ($value, $attributes) {
            // Get the year from the start_date (e.g., 2025)
            $startYear = Carbon::parse($attributes['start_date'])->year;
            
            // Get the year from the end_date (e.g., 2026)
            $endYear = Carbon::parse($attributes['end_date'])->year;

            // Concatenate them to form the label
            return "{$startYear}-{$endYear}";
        });
    }


}
