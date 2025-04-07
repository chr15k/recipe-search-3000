<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\SearchRecipeRequest;
use App\Http\Requests\Api\ShowRecipeRequest;
use App\Http\Resources\RecipeResource;
use App\Models\Recipe;
use App\Search\Filters\Common\ExactMatchFilter;
use App\Search\Filters\Recipe\IngredientFilter;
use App\Search\Filters\Recipe\KeywordFilter;
use App\Search\Search;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Symfony\Component\HttpFoundation\JsonResponse;

final class RecipeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function search(SearchRecipeRequest $request): ResourceCollection
    {
        $recipes = Search::for(Recipe::class, $request)
            ->with('ingredients')
            ->allowedFilters([
                'author_email' => new ExactMatchFilter('author_email'),
                'keyword' => new KeywordFilter,
                'ingredient' => new IngredientFilter,
            ])
            ->applyFilters()
            ->paginate();

        return RecipeResource::collection($recipes);
    }

    /**
     * Display the specified resource.
     */
    public function show(ShowRecipeRequest $request, string $slug): RecipeResource
    {
        $recipe = Recipe::query()
            ->where('slug', $slug)
            ->with('ingredients')
            ->first();

        if (! $recipe) {
            throw new HttpResponseException(response()->json([
                'message' => 'Not Found',
                'errors' => ['recipe' => 'Recipe not found'],
            ], JsonResponse::HTTP_NOT_FOUND));
        }

        return new RecipeResource($recipe);
    }
}
