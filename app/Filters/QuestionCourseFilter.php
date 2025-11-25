<?php 
namespace App\Filters;

use Spatie\QueryBuilder\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class QuestionCourseFilter implements Filter
{
    public function __invoke(Builder $query, $value, string $property)
    {
        $values = (array) $value;

        $query->whereHas('topic.subject.courses', function ($q) use ($values) {
            $q->where(function ($q2) use ($values) {
                foreach ($values as $value) {
                    if (is_numeric($value)) {
                        $q2->orWhere('courses.id', $value);
                    }
                }
            });
        });

    }
}
