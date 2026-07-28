<?php

namespace App\Filters;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\Filters\Filter;

class FiltersSearch implements Filter
{
    public function __invoke(
        Builder $query,
        mixed $value,
        string $property,
    ): void {
        $ids = Product::search($value)->keys();

        $query->whereIn("id", $ids);
    }
}
