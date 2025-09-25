<?php

namespace App\Rules;

use App\Models\AcademicYear;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NoAcademicYearCollisions implements ValidationRule
{
 protected $startDate;
    protected $idToIgnore;

    public function __construct($startDate, $idToIgnore = null)
    {
        $this->startDate = $startDate;
        $this->idToIgnore = $idToIgnore;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
        {
            $endDate = $value; // Use the value passed to the method

            $query = AcademicYear::where(function ($query) use ($endDate) {
                $query->whereBetween('start_date', [$this->startDate, $endDate])
                    ->orWhereBetween('end_date', [$this->startDate, $endDate])
                    ->orWhere(function ($query) use ($endDate) {
                        $query->where('start_date', '<=', $this->startDate)
                                ->where('end_date', '>=', $endDate);
                    });
            });

            // Ignore the current model if we are updating
            if ($this->idToIgnore) {
                $query->where('id', '!=', $this->idToIgnore);
            }

            if ($query->count() > 0) {
                $fail('The academic year dates overlap with an existing academic year.');
            }
        }
}
