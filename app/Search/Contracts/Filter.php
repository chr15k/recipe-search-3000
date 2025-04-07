<?php

namespace App\Search\Contracts;

use Illuminate\Database\Eloquent\Builder;

interface Filter
{
    /**
     * Apply the filter to the query.
     */
    public function apply(Builder $query, mixed $value): Builder;
}
