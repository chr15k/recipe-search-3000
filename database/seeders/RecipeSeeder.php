<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * I've optimized this seeder for efficiently generating substantial recipe datasets (up to 1M entries) with minimal wait time.
 * It creates 100,000 recipes by default, each containing 4-6 ingredients.
 * Modify the `TOTAL_RECIPES` constant to adjust the dataset size as needed.
 */
class RecipeSeeder extends Seeder
{
    private const AUTHOR_EMAILS = ['chef@example.com', 'cook@example.com', 'foodie@example.com', 'gourmet@example.com'];

    private const COOKING_METHODS = ['Roasted', 'Grilled', 'Baked', 'Fried', 'Steamed', 'Sautéed', 'Boiled', 'Poached', 'Braised', 'Stir-fried'];

    private const CUISINES = ['Italian', 'Mexican', 'Chinese', 'Indian', 'French', 'Thai', 'Japanese', 'Greek', 'Spanish', 'American'];

    private const DISH_TYPES = ['Soup', 'Stew', 'Salad', 'Sandwich', 'Pasta', 'Curry', 'Casserole', 'Stir-fry', 'Roast', 'Pie'];

    private const PROTEINS = ['Chicken', 'Beef', 'Pork', 'Lamb', 'Salmon', 'Tuna', 'Tofu', 'Shrimp', 'Beans', 'Lentils'];

    private const VEGETABLES = ['Broccoli', 'Spinach', 'Carrots', 'Potatoes', 'Zucchini', 'Peppers', 'Mushrooms', 'Onions', 'Tomatoes', 'Eggplant'];

    private const FRUITS = ['Apple', 'Banana', 'Strawberry', 'Blueberry', 'Mango', 'Peach', 'Cherry', 'Lemon', 'Lime', 'Pineapple'];

    private const UNITS = ['cup', 'tbsp', 'tsp', 'lb', 'oz', 'g', 'kg', 'ml', 'L', 'pinch'];

    private const PREPARATIONS = ['diced', 'chopped', 'minced', 'sliced', 'grated', 'mashed', 'puréed', 'crushed', 'peeled', 'trimmed'];

    private const DESCRIPTIONS = [
        'A delicious recipe everyone will enjoy.',
        'Perfect for family gatherings and special occasions.',
        'Quick and easy meal for busy weeknights.',
        'An elegant dish with complex flavors.',
        'Comfort food at its finest.',
        'A healthy alternative to traditional favorites.',
        'Seasonal ingredients create amazing flavors.',
        'Budget-friendly without sacrificing taste.',
        'Impressive dish that takes minimal effort.',
        'A classic recipe with a modern twist.',
    ];

    private $now;

    private $recipeNames = [];

    private $images = [];

    private $ingredientDescriptions = [];

    private $stepLists = [];

    private const TOTAL_RECIPES = 100000;

    private const BATCH_SIZE = 5000;

    private const PRE_GENERATE_COUNT = 1000; // Pre-generate 1000 unique names and descriptions

    public function __construct()
    {
        $this->now = now()->format('Y-m-d H:i:s');
    }

    public function run(): void
    {
        $this->command->info('Preparing data...');

        // Pre-generate data pools
        $this->preGenerateData();

        $this->command->info('Starting insertion of '.self::TOTAL_RECIPES.' recipes...');
        $this->command->getOutput()->progressStart(self::TOTAL_RECIPES / self::BATCH_SIZE);

        // Tune MySQL for faster inserts
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::statement('SET UNIQUE_CHECKS=0');
        DB::statement('SET AUTOCOMMIT=0');

        // Process in batches
        for ($i = 0; $i < self::TOTAL_RECIPES; $i += self::BATCH_SIZE) {
            $this->processBatch($i + 1, min(self::BATCH_SIZE, self::TOTAL_RECIPES - $i));

            // Periodic commits prevent transaction log from growing too large
            if ($i % (self::TOTAL_RECIPES / 10) === 0) {
                DB::statement('COMMIT');
                DB::statement('SET AUTOCOMMIT=0');
            }

            $this->command->getOutput()->progressAdvance();
        }

        // Commit then restore MySQL settings
        DB::statement('COMMIT');
        DB::statement('SET AUTOCOMMIT=1');
        DB::statement('SET UNIQUE_CHECKS=1');
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $this->command->getOutput()->progressFinish();
        $this->command->info('Successfully created '.self::TOTAL_RECIPES.' recipes!');

        cache()->flush();
    }

    /**
     * Pre-generate data for recipes, ingredients, and steps.
     */
    private function preGenerateData(): void
    {
        // Generate recipe names
        for ($i = 0; $i < self::PRE_GENERATE_COUNT; $i++) {
            $method = self::COOKING_METHODS[array_rand(self::COOKING_METHODS)];
            $protein = self::PROTEINS[array_rand(self::PROTEINS)];
            $vegetable = self::VEGETABLES[array_rand(self::VEGETABLES)];
            $cuisine = self::CUISINES[array_rand(self::CUISINES)];
            $dish = self::DISH_TYPES[array_rand(self::DISH_TYPES)];

            $this->recipeNames[] = "$method $protein with $vegetable";
            $this->recipeNames[] = "$cuisine $dish";
            $this->recipeNames[] = "$protein $dish";
        }

        // Generate ingredient descriptions
        for ($i = 0; $i < self::PRE_GENERATE_COUNT; $i++) {
            $amount = mt_rand(1, 4);
            $unit = self::UNITS[array_rand(self::UNITS)];

            $ingredientsPool = array_merge(
                self::PROTEINS,
                self::VEGETABLES,
                self::FRUITS
            );
            $ingredient = $ingredientsPool[array_rand($ingredientsPool)];

            $prep = mt_rand(0, 1) ? ', '.self::PREPARATIONS[array_rand(self::PREPARATIONS)] : '';

            $this->ingredientDescriptions[] = "$amount $unit $ingredient$prep";
        }

        // Generate varied step lists (4-6 steps per recipe)
        $actions = ['Prepare', 'Chop', 'Mix', 'Heat', 'Stir', 'Combine', 'Wash', 'Marinate', 'Slice', 'Season'];
        $cookings = ['Bake', 'Simmer', 'Cook', 'Grill', 'Roast', 'Sauté', 'Steam', 'Boil', 'Broil', 'Fry'];
        $times = ['10-15', '15-20', '20-25', '25-30', '5-10', '30-40', '45-50', '8-12', '12-15', '3-5'];
        $endings = [
            'Serve hot',
            'Garnish with herbs and serve',
            'Let rest before serving',
            'Serve immediately',
            'Plate and enjoy',
            'Serve with your favorite sides',
            'Enjoy while fresh',
            'Serve chilled',
            'Sprinkle with seasoning before serving',
            'Pair with a side salad',
        ];

        // Generate food images
        $this->images = [];
        $foodCategories = ['biryani', 'burger', 'butter-chicken', 'dessert', 'dosa', 'idly', 'pasta', 'pizza', 'rice', 'samosa'];

        // Get 10 random food images from Foodish API
        for ($i = 0; $i < 20; $i++) {
            $category = $foodCategories[array_rand($foodCategories)];
            $this->images[] = "https://foodish-api.com/images/{$category}/{$category}".($i + 1).'.jpg';
        }

        for ($i = 0; $i < self::PRE_GENERATE_COUNT; $i++) {
            $stepCount = mt_rand(4, 6);
            $steps = [];

            // First step is always preparation
            $steps[] = $actions[array_rand($actions)].' all ingredients before starting.';

            // Middle steps vary
            for ($j = 1; $j < $stepCount - 1; $j++) {
                if ($j == 1) {
                    $steps[] = $actions[array_rand($actions)].' the ingredients '.
                        ['thoroughly', 'gently', 'carefully', 'well', 'properly'][array_rand(['thoroughly', 'gently', 'carefully', 'well', 'properly'])].'.';
                } else {
                    $steps[] = $cookings[array_rand($cookings)].' for '.
                        $times[array_rand($times)].' minutes until '.
                        ['golden', 'tender', 'done', 'fragrant', 'cooked through'][array_rand(['golden', 'tender', 'done', 'fragrant', 'cooked through'])].'.';
                }
            }

            // Last step is serving
            $steps[] = $endings[array_rand($endings)].'!';

            $this->stepLists[] = $steps;
        }
    }

    private function processBatch(int $startId, int $count): void
    {
        $recipes = [];
        $ingredients = [];
        $recipeCount = count($this->recipeNames);
        $ingredientCount = count($this->ingredientDescriptions);
        $stepCount = count($this->stepLists);

        // Insert recipes in smaller batches
        $recipeBatchSize = 5000;
        for ($j = 0; $j < $count; $j++) {
            $recipeId = $startId + $j;
            $recipeName = $this->recipeNames[$recipeId % $recipeCount];
            $slug = Str::slug(substr($recipeName, 0, 50)).'-'.$recipeId;
            $steps = $this->stepLists[$recipeId % $stepCount];

            $recipes[] = [
                'id' => $recipeId,
                'name' => $recipeName,
                'slug' => $slug,
                'description' => self::DESCRIPTIONS[$recipeId % 10],
                'author_email' => self::AUTHOR_EMAILS[$recipeId % count(self::AUTHOR_EMAILS)],
                'steps' => json_encode($steps),
                'steps_text' => implode(' ', $steps),
                'created_at' => $this->now,
                'updated_at' => $this->now,
                'image' => $this->images[array_rand($this->images)],
            ];

            // Add 4-6 ingredients per recipe
            $ingredientsPerRecipe = mt_rand(4, 6);
            for ($k = 0; $k < $ingredientsPerRecipe; $k++) {
                $ingredients[] = [
                    'recipe_id' => $recipeId,
                    'description' => $this->ingredientDescriptions[($recipeId + $k) % $ingredientCount],
                    'created_at' => $this->now,
                    'updated_at' => $this->now,
                ];
            }

            // Insert in smaller batches to prevent large operations
            if (($j + 1) % $recipeBatchSize === 0 || $j === $count - 1) {
                // Bulk insert recipes
                DB::table('recipes')->insert($recipes);

                // Bulk insert ingredients in smaller chunks
                foreach (array_chunk($ingredients, 1000) as $chunk) {
                    DB::table('ingredients')->insert($chunk);
                }

                $recipes = [];
                $ingredients = [];
            }
        }
    }
}
