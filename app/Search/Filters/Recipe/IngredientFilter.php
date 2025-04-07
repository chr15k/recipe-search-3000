<?php

namespace App\Search\Filters\Recipe;

use App\Search\Contracts\RecipeFilter;
use App\Search\Utils\Sanitizer;
use Illuminate\Database\Eloquent\Builder;

final class IngredientFilter implements RecipeFilter
{
    public function apply(Builder $query, mixed $value): Builder
    {
        if (empty($value)) {
            return $query;
        }

        $ingredient = Sanitizer::searchTerm($value);

        // Optimized query using FULLTEXT index.
        return $query->whereHas('ingredients',
            fn (Builder $q) => $q->whereRaw('MATCH(description) AGAINST(? IN BOOLEAN MODE)', [$ingredient])
        );
    }
}
