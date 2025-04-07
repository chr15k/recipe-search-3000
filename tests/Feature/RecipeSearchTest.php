<?php

namespace Tests\Feature;

use App\Models\Ingredient;
use App\Models\Recipe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Database\Seeders\TestRecipeSeeder;
use Tests\TestCase;

class RecipeSearchTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Set up the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate:fresh');
        $this->seed(TestRecipeSeeder::class);
    }

    /**
     * Test searching recipes with keyword.
     *
     * @return void
     */
    public function test_search_recipes_by_keyword()
    {
        // Get a unique word from an existing recipe name
        $recipe = Recipe::first();
        $uniqueKeyword = explode(' ', $recipe->name)[0]; // Get the first word of the recipe name

        $response = $this->getJson("/api/recipes/search?keyword={$uniqueKeyword}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id', 'name', 'slug', 'description', 'author_email', 'steps', 'ingredients',
                    ],
                ],
                'meta' => [
                    'current_page', 'from', 'last_page', 'links', 'path', 'per_page', 'to', 'total',
                ],
            ]);

        // Check that the response contains data and verify the recipe with matching ID is present
        $responseData = $response->decodeResponseJson();

        $this->assertNotEmpty($responseData['data']);

        $foundRecipe = collect($responseData['data'])->firstWhere('id', $recipe->id);
        $this->assertNotNull($foundRecipe, "Recipe with ID {$recipe->id} not found in search results");

        // Verify that the returned recipes contain the keyword in name, description, ingredients, or steps_text
        $decodedResponse = $response->decodeResponseJson();
        $this->assertNotEmpty($decodedResponse['data']);

        // Get complete recipes with relationships to check all fields
        $recipeIds = collect($decodedResponse['data'])->pluck('id')->toArray();
        $recipes = Recipe::with(['ingredients'])->whereIn('id', $recipeIds)->get();

        foreach ($recipes as $recipe) {
            $keywordLower = strtolower($uniqueKeyword);
            $containsKeyword =
                str_contains(strtolower($recipe->name), $keywordLower) ||
                str_contains(strtolower($recipe->description), $keywordLower) ||
                str_contains(strtolower($recipe->steps_text), $keywordLower) ||
                $recipe->ingredients->contains(function ($ingredient) use ($keywordLower) {
                    return str_contains(strtolower($ingredient->description), $keywordLower);
                });

            $this->assertTrue($containsKeyword, "Recipe does not contain the keyword '{$uniqueKeyword}' in any searchable field");
        }
    }

    /**
     * Test searching recipes by author email.
     *
     * @return void
     */
    public function test_search_recipes_by_author_email()
    {
        // Get an author email from an existing recipe
        $recipe = Recipe::first();
        $authorEmail = $recipe->author_email;

        $response = $this->getJson("/api/recipes/search?author_email={$authorEmail}");

        $response->assertStatus(200)
            ->assertJsonFragment([
                'author_email' => $authorEmail,
            ]);

        // Verify all returned recipes have the correct author email (exact match)
        $decodedResponse = $response->decodeResponseJson();
        foreach ($decodedResponse['data'] as $recipeData) {
            $this->assertEquals($authorEmail, $recipeData['author_email']);
        }
    }

    /**
     * Test searching recipes by ingredient with partial match.
     *
     * @return void
     */
    public function test_search_recipes_by_ingredient_partial_match()
    {
        // Get an ingredient from the database that belongs to a recipe
        $ingredient = Ingredient::first();
        $ingredientKeyword = explode(' ', $ingredient->description)[0]; // Get the first word

        // Make sure the ingredient keyword is at least 2 characters long
        if (strlen($ingredientKeyword) <= 1) {
            // Try to find a word with at least 2 characters
            $words = explode(' ', $ingredient->description);
            foreach ($words as $word) {
                if (strlen($word) >= 2) {
                    $ingredientKeyword = $word;
                    break;
                }
            }
            // If no suitable word found, use the first two characters of the description or the whole description if it's short
            if (strlen($ingredientKeyword) <= 1) {
                $ingredientKeyword = strlen($ingredient->description) > 1
                    ? substr($ingredient->description, 0, 2)
                    : $ingredient->description.'x'; // Add a character if description is only 1 char
            }
        }

        $response = $this->getJson("/api/recipes/search?ingredient={$ingredientKeyword}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'meta',
            ]);

        // Verify that we got results
        $decodedResponse = $response->decodeResponseJson();

        $this->assertNotEmpty($decodedResponse['data']);

        // Verify returned recipes have the ingredient (need to load ingredients to check)
        $recipeIds = collect($decodedResponse['data'])->pluck('id')->toArray();
        $recipes = Recipe::with('ingredients')->whereIn('id', $recipeIds)->get();

        // Check if each recipe has an ingredient containing our keyword as a partial match
        foreach ($recipes as $recipe) {
            $hasIngredient = $recipe->ingredients->contains(function ($recipeIngredient) use ($ingredientKeyword) {
                return str_contains(strtolower($recipeIngredient->description), strtolower($ingredientKeyword));
            });

            $this->assertTrue($hasIngredient, "Recipe ID {$recipe->id} does not contain the ingredient keyword: {$ingredientKeyword}");
        }
    }

    /**
     * Test combined search with multiple parameters as AND conditions.
     *
     * @return void
     */
    public function test_search_recipes_with_multiple_parameters_as_and_condition()
    {
        // Find a recipe with ingredients for testing
        $recipe = Recipe::with(['ingredients'])->first();
        $authorEmail = $recipe->author_email;

        // Get a word from the recipe name or description that's over 2 characters
        $words = explode(' ', $recipe->description);
        $keywordFromDescription = null;
        foreach ($words as $word) {
            if (strlen($word) > 2) {
                $keywordFromDescription = $word;
                break;
            }
        }
        // Fallback if no word > 2 chars was found
        if (! $keywordFromDescription) {
            $keywordFromDescription = $words[0].$words[1] ?? '';
        }

        // Get a word from one of the recipe's ingredients
        $ingredient = $recipe->ingredients->first();
        $ingredientKeyword = explode(' ', $ingredient->description)[0];

        // Make sure ingredientKeyword is over 2 characters
        if (strlen($ingredientKeyword) <= 2) {
            $words = explode(' ', $ingredient->description);
            foreach ($words as $word) {
                if (strlen($word) > 2) {
                    $ingredientKeyword = $word;
                    break;
                }
            }
            // Fallback if no word > 2 chars was found
            if (strlen($ingredientKeyword) <= 2) {
                $ingredientKeyword = $words[0].($words[1] ?? '');
            }
        }

        // First, do individual searches to count how many recipes match each criteria
        $responseEmail = $this->getJson("/api/recipes/search?author_email={$authorEmail}");
        $emailCount = $responseEmail->decodeResponseJson()['meta']['total'];

        $responseKeyword = $this->getJson("/api/recipes/search?keyword={$keywordFromDescription}");
        $keywordCount = $responseKeyword->decodeResponseJson()['meta']['total'];

        $responseIngredient = $this->getJson("/api/recipes/search?ingredient={$ingredientKeyword}");
        $ingredientCount = $responseIngredient->decodeResponseJson()['meta']['total'];

        // Now perform the combined search
        $response = $this->getJson(
            "/api/recipes/search?author_email={$authorEmail}&keyword={$keywordFromDescription}&ingredient={$ingredientKeyword}"
        );

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'meta',
            ]);

        // Verify the results of the combined search
        $decodedResponse = $response->decodeResponseJson();
        $combinedCount = $decodedResponse['meta']['total'];

        // The combined count should be less than or equal to the minimum count of the individual searches
        // This verifies the AND condition is working
        $minCount = min($emailCount, $keywordCount, $ingredientCount);
        $this->assertLessThanOrEqual($minCount, $combinedCount,
            "Combined search results count ({$combinedCount}) exceeds minimum individual search count ({$minCount}), suggesting AND condition isn't working");

        // Verify all returned recipes match ALL criteria
        if (! empty($decodedResponse['data'])) {
            $recipeIds = collect($decodedResponse['data'])->pluck('id')->toArray();
            $foundRecipes = Recipe::with(['ingredients'])->whereIn('id', $recipeIds)->get();

            foreach ($foundRecipes as $foundRecipe) {
                // Check email criteria (exact match)
                $this->assertEquals($authorEmail, $foundRecipe->author_email);

                // Check keyword criteria (in name, description, ingredients, or steps_text)
                $keywordLower = strtolower($keywordFromDescription);
                $containsKeyword =
                    str_contains(strtolower($foundRecipe->name), $keywordLower) ||
                    str_contains(strtolower($foundRecipe->description), $keywordLower) ||
                    str_contains(strtolower($foundRecipe->steps_text), $keywordLower) ||
                    $foundRecipe->ingredients->contains(function ($ing) use ($keywordLower) {
                        return str_contains(strtolower($ing->description), $keywordLower);
                    });

                $this->assertTrue($containsKeyword, "Recipe {$foundRecipe->id} does not contain keyword '{$keywordFromDescription}'");

                // Check ingredient criteria (partial match)
                $ingredientLower = strtolower($ingredientKeyword);
                $hasIngredient = $foundRecipe->ingredients->contains(function ($ing) use ($ingredientLower) {
                    return str_contains(strtolower($ing->description), $ingredientLower);
                });

                $this->assertTrue($hasIngredient, "Recipe {$foundRecipe->id} does not contain ingredient '{$ingredientKeyword}'");
            }
        }
    }

    /**
     * Test validation error responses.
     *
     * @return void
     */
    public function test_search_with_invalid_parameters()
    {
        // Test with invalid email format
        $response = $this->getJson('/api/recipes/search?author_email=invalid-email');

        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => [
                    'author_email',
                ],
            ]);

        // Test with keyword that's too short
        $response = $this->getJson('/api/recipes/search?keyword=a');

        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => [
                    'keyword',
                ],
            ]);
    }

    /**
     * Test pagination works correctly.
     *
     * @return void
     */
    public function test_search_with_pagination()
    {
        $perPage = 5;

        $response = $this->getJson("/api/recipes/search?per_page={$perPage}");

        $response->assertStatus(200)
            ->assertJsonCount($perPage, 'data')
            ->assertJsonStructure([
                'data',
                'meta' => [
                    'current_page', 'from', 'last_page', 'links', 'path', 'per_page', 'to', 'total',
                ],
            ]);

        $decodedResponse = $response->decodeResponseJson();
        $this->assertEquals($perPage, $decodedResponse['meta']['per_page']);
    }
}
