<?php

namespace App\Filters;

use Spatie\QueryBuilder\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class FiltersRating implements Filter
{
    public function __invoke(
        Builder $query,
        mixed $value,
        string $property,
    ): void {
        $query->whereHas("rating", fn($q) => $q->where("rating", $value));
    }
}

// class FiltersRating implements Filter
// {
//     public function __invoke(Builder $query, $value, string $property)
//     {
//         dd(1);
//         $query->whereHas("rating", function (Builder $query) use ($value) {
//             if (
//                 is_numeric($value) &&
//                 (int) $value == $value &&
//                 (int) $value >= 0 &&
//                 (int) $value <= 5
//             ) {
//                 $query->where("rating", $value);
//             }

//             $query;
//         });
//     }
// }
