<?php

namespace App\Observers;

use App\Models\Recipe;
use Illuminate\Support\Str;

class RecipeObserver
{
    /**
     * Handle the Recipe "creating" event.
     */
    public function creating(Recipe $recipe)
    {
        $slug = Str::slug($recipe->name);
        $count = 1;

        // ensure the initial slug is unique
        while (Recipe::query()->where('slug', $slug)->exists()) {
            $slug = Str::slug($recipe->name).'-'.$count++;
        }

        $recipe->slug = $slug;

        if (is_array($recipe->steps)) {
            $recipe->steps_text = implode(' ', $recipe->steps);
        }
    }

    /**
     * Handle the Recipe "updating" event.
     */
    public function updating(Recipe $recipe): void
    {
        if ($recipe->isDirty('steps') && is_array($recipe->steps)) {
            $recipe->steps_text = implode(' ', $recipe->steps);
        }
    }
}
