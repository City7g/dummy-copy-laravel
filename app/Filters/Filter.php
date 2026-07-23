<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

interface Filter
{
    public function apply(Builder $query): Builder;
}
