<?php

namespace App\Search;

use App\Search\Concerns\FiltersQuery;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

final class Search
{
    use FiltersQuery;

    protected Builder $baseQuery;

    /**
     * Cache duration in minutes.
     */
    protected int $cacheDuration = 15;

    /**
     * Create a new search instance.
     */
    public function __construct(private string $model, private ?Request $request = null)
    {
        $this->request = $request ?? request();
        $this->baseQuery = $model::query();

        $this->cacheDuration = config('search.cache.enabled')
            ? config('search.cache.duration', 15)
            : 0;
    }

    /**
     * Create a new instance from the Recipe model.
     */
    public static function for(string $model, ?Request $request = null): self
    {
        return new self($model, $request);
    }

    /**
     * Include eager loaded relationships.
     */
    public function with(array|string $relations): self
    {
        $this->baseQuery->with($relations);

        return $this;
    }

    /**
     * Get the paginated results.
     */
    public function paginate(?int $perPage = null): LengthAwarePaginator
    {
        $perPage = $perPage ?? (int) $this->request->input('per_page', 12);
        $perPage = min($perPage, 50); // Limit the max per page

        // Generate cache key based on the current query and request
        $cacheKey = $this->generateCacheKey($perPage);

        return Cache::remember($cacheKey, now()->addMinutes($this->cacheDuration), function () use ($perPage) {
            return $this->baseQuery->paginate($perPage)->appends($this->request->query());
        });
    }

    /**
     * Generate a cache key based on the current query and request.
     */
    protected function generateCacheKey(int $perPage): string
    {
        $filterValues = [];

        foreach ($this->allowedFilters as $filterName => $filter) {
            $paramName = is_string($filterName) ? $filterName : $filter;
            $value = $this->request->input($paramName);

            if ($value !== null) {
                $filterValues[$paramName] = $value;
            }
        }

        return $this->model.'_search_'.md5(
            json_encode($filterValues).'_'.
                $perPage.'_'.
                $this->request->input('page', 1)
        );
    }
}
