<?php

namespace App\Search\Filters\Common;

use App\Search\Contracts\Filter;
use Illuminate\Database\Eloquent\Builder;

final class ExactMatchFilter implements Filter
{
    public function __construct(private string $column)
    {
        $this->column = $column;
    }

    public function apply(Builder $query, mixed $value): Builder
    {
        if (empty($value)) {
            return $query;
        }

        return $query->where($this->column, $value);
    }
}
