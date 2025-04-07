<?php

namespace App\Search\Concerns;

use App\Search\Contracts\Filter;

trait FiltersQuery
{
    protected array $allowedFilters = [];

    /**
     * Set the allowed filters for the query.
     *
     * Can be an instance of Filter or a callable.
     *
     * @param  array<string, Filter|callable>  $filters  Keyed by request parameter name.
     * @return static
     */
    public function allowedFilters(array $filters): self
    {
        $this->allowedFilters = $filters;

        return $this;
    }

    public function applyFilters(): self
    {
        foreach ($this->allowedFilters as $filterName => $filter) {

            $paramName = is_string($filterName) ? $filterName : $filter;

            if ($this->request->has($paramName)) {
                $value = $this->request->input($paramName);

                if ($filter instanceof Filter) {
                    $filter->apply($this->baseQuery, $value);
                } elseif (is_callable($filter)) {
                    $filter($this->baseQuery, $value);
                }
            }
        }

        return $this;
    }
}
