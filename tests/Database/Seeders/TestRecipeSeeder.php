<?php

namespace Tests\Database\Seeders;

use App\Models\Ingredient;
use App\Models\Recipe;
use Illuminate\Database\Seeder;

class TestRecipeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create specific test recipes with predictable data
        $this->createTestRecipes();

        // Add additional random recipes to ensure pagination works
        Recipe::factory()
            ->count(100)
            ->has(Ingredient::factory()->count(5))
            ->create();
    }

    /**
     * Create specific test recipes with predictable data for testing.
     *
     * @return void
     */
    private function createTestRecipes()
    {
        // Recipe 1 - Chocolate Cake
        $recipe1 = Recipe::create([
            'name' => 'Delicious Chocolate Cake',
            'slug' => 'delicious-chocolate-cake',
            'description' => 'A rich and moist chocolate cake recipe perfect for any occasion',
            'author_email' => 'chef@example.com',
            'steps' => [
                'Preheat oven to 350°F (175°C)',
                'Mix dry ingredients in a large bowl',
                'Add wet ingredients and mix until smooth',
                'Pour batter into greased cake pans',
                'Bake for 30-35 minutes',
                'Let cool before frosting',
            ],
        ]);

        Ingredient::create([
            'recipe_id' => $recipe1->id,
            'description' => '2 cups all-purpose flour',
        ]);

        Ingredient::create([
            'recipe_id' => $recipe1->id,
            'description' => '2 cups granulated sugar',
        ]);

        Ingredient::create([
            'recipe_id' => $recipe1->id,
            'description' => '3/4 cup unsweetened cocoa powder',
        ]);

        Ingredient::create([
            'recipe_id' => $recipe1->id,
            'description' => '2 eggs',
        ]);

        // Recipe 2 - Vegetable Stir Fry
        $recipe2 = Recipe::create([
            'name' => 'Quick Vegetable Stir Fry',
            'slug' => 'quick-vegetable-stir-fry',
            'description' => 'A healthy and easy vegetable stir fry ready in 20 minutes',
            'author_email' => 'nutritionist@example.com',
            'steps' => [
                'Prepare all vegetables by cutting them into small pieces',
                'Heat oil in a large wok over medium-high heat',
                'Add garlic and ginger, cook for 30 seconds',
                'Add vegetables and stir fry for 5-7 minutes',
                'Add sauce and cook for another 2 minutes',
                'Serve hot with rice or noodles',
            ],
        ]);

        Ingredient::create([
            'recipe_id' => $recipe2->id,
            'description' => '2 tablespoons vegetable oil',
        ]);

        Ingredient::create([
            'recipe_id' => $recipe2->id,
            'description' => '3 cloves garlic, minced',
        ]);

        Ingredient::create([
            'recipe_id' => $recipe2->id,
            'description' => '1 red bell pepper, sliced',
        ]);

        Ingredient::create([
            'recipe_id' => $recipe2->id,
            'description' => '2 cups broccoli florets',
        ]);

        // Recipe 3 - Banana Smoothie
        $recipe3 = Recipe::create([
            'name' => 'Energizing Banana Smoothie',
            'slug' => 'energizing-banana-smoothie',
            'description' => 'A quick breakfast smoothie with banana and protein',
            'author_email' => 'fitness@example.com',
            'steps' => [
                'Add all ingredients to a blender',
                'Blend until smooth and creamy',
                'Pour into a glass and enjoy immediately',
            ],
        ]);

        Ingredient::create([
            'recipe_id' => $recipe3->id,
            'description' => '2 ripe bananas',
        ]);

        Ingredient::create([
            'recipe_id' => $recipe3->id,
            'description' => '1 cup milk or almond milk',
        ]);

        Ingredient::create([
            'recipe_id' => $recipe3->id,
            'description' => '2 tablespoons honey',
        ]);

        Ingredient::create([
            'recipe_id' => $recipe3->id,
            'description' => '1 scoop vanilla protein powder',
        ]);

        // Recipe 4 - Garlic Pasta
        $recipe4 = Recipe::create([
            'name' => 'Simple Garlic Pasta',
            'slug' => 'simple-garlic-pasta',
            'description' => 'A quick and easy pasta dish with garlic and olive oil',
            'author_email' => 'chef@example.com', // Same author as chocolate cake
            'steps' => [
                'Cook pasta according to package directions',
                'While pasta cooks, heat olive oil in a large pan',
                'Add minced garlic and red pepper flakes, cook for 1-2 minutes',
                'Drain pasta and add to the garlic oil',
                'Add chopped parsley and Parmesan cheese',
                'Season with salt and pepper to taste',
            ],
        ]);

        Ingredient::create([
            'recipe_id' => $recipe4->id,
            'description' => '8 ounces spaghetti or linguine',
        ]);

        Ingredient::create([
            'recipe_id' => $recipe4->id,
            'description' => '1/4 cup extra virgin olive oil',
        ]);

        Ingredient::create([
            'recipe_id' => $recipe4->id,
            'description' => '5 cloves garlic, minced',
        ]);

        Ingredient::create([
            'recipe_id' => $recipe4->id,
            'description' => '1/4 cup freshly grated Parmesan cheese',
        ]);
    }
}
