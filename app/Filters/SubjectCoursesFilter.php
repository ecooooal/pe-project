<?php 
namespace App\Filters;

use Spatie\QueryBuilder\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;


class SubjectCoursesFilter implements Filter
{
    public function __invoke(Builder $query, $value, string $property)
    {
        
        $ids = is_array($value) ? $value : explode(',', $value);

        $query->whereHas('courses', function (Builder $q) use ($ids) {
            $q->whereIn('courses.id', $ids);
        });
    }
}
