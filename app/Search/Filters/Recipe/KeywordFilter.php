<?php

namespace App\Search\Filters\Recipe;

use App\Search\Contracts\RecipeFilter;
use App\Search\Utils\Sanitizer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

final class KeywordFilter implements RecipeFilter
{
    public function apply(Builder $query, mixed $value): Builder
    {
        if (empty($value)) {
            return $query;
        }

        $keyword = Sanitizer::searchTerm($value);

        // Optimized query using FULLTEXT index (I also used UNION to avoid MySQL's cross-table MATCH() limitation).
        // From profiling, this is significantly faster than using LIKE or multiple MATCH clauses.
        return $query->whereIn('id', fn ($q) => $q->select('recipe_id')
            ->from(function ($subquery) use ($keyword) {
                $subquery
                    ->select(DB::raw('id AS recipe_id'))
                    ->from('recipes')
                    ->whereRaw('MATCH(name, description, steps_text) AGAINST(? IN BOOLEAN MODE)', [$keyword]);
                $subquery
                    ->union(DB::table('ingredients')
                        ->select('recipe_id')
                        ->whereRaw('MATCH(description) AGAINST(? IN BOOLEAN MODE)', [$keyword])
                    );
            }, 'matched')->distinct()
        );
    }
}
