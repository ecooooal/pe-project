<?php 
namespace App\Filters;

use Spatie\QueryBuilder\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class QuestionLevelFilter implements Filter
{
    public function __invoke(Builder $query, $value, string $property)
    {
        {
            $values = (array) $value;

            $query->whereHas('questionLevel', function (Builder $q) use ($values) {
                $q->whereIn('tags.id', $values);
            });

        }
    }
}
