<template>
    <div>
        <!-- Back Button -->
        <button
            @click="$router.back()"
            class="mb-4 flex items-center text-indigo-600 hover:text-indigo-800 cursor-pointer"
        >
            <svg
                xmlns="http://www.w3.org/2000/svg"
                class="h-5 w-5 mr-1"
                viewBox="0 0 20 20"
                fill="currentColor"
            >
                <path
                    fill-rule="evenodd"
                    d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z"
                    clip-rule="evenodd"
                />
            </svg>
            Back to Search
        </button>

        <!-- Loading State -->
        <div v-if="loading" class="text-center py-10">
            <svg
                class="animate-spin h-10 w-10 mx-auto text-indigo-500"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
            >
                <circle
                    class="opacity-25"
                    cx="12"
                    cy="12"
                    r="10"
                    stroke="currentColor"
                    stroke-width="4"
                ></circle>
                <path
                    class="opacity-75"
                    fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                ></path>
            </svg>
        </div>

        <!-- Error State -->
        <div
            v-else-if="error"
            class="bg-red-50 p-4 rounded-md border-l-4 border-red-500"
        >
            <p class="text-red-700">{{ error }}</p>
        </div>

        <!-- Recipe Detail -->
        <div
            v-else-if="recipe"
            class="bg-white rounded-lg shadow-sm overflow-hidden lg:w-3/4 mx-auto"
        >
            <!-- Recipe Header -->
            <div class="relative h-64 bg-gray-200 overflow-hidden">
                <img
                    :src="getRecipeImage()"
                    :alt="recipe.name"
                    class="w-full h-full object-cover"
                />
            </div>

            <!-- Recipe Content -->
            <div class="p-6">
                <h1 class="text-2xl font-bold mb-1">{{ recipe.name }}</h1>
                <p class="text-gray-500 mb-4">By {{ getAuthor() }}</p>
                <p class="mb-6">{{ recipe.description }}</p>

                <div class="grid md:grid-cols-3 gap-6">
                    <!-- Ingredients -->
                    <div>
                        <h2 class="text-lg font-bold mb-3 border-b pb-2">
                            Ingredients
                        </h2>
                        <ul class="space-y-2">
                            <li
                                v-for="(ingredient, i) in recipe.ingredients"
                                :key="i"
                                class="flex items-start"
                            >
                                <span class="text-indigo-500 mr-2">•</span>
                                {{ ingredient }}
                            </li>
                        </ul>
                    </div>

                    <!-- Steps -->
                    <div class="md:col-span-2">
                        <h2 class="text-lg font-bold mb-3 border-b pb-2">
                            Instructions
                        </h2>
                        <ol class="list-decimal list-inside space-y-3">
                            <li
                                v-for="(step, i) in getSteps()"
                                :key="i"
                                class="pl-2"
                            >
                                {{ step }}
                            </li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <!-- Not Found -->
        <div v-else class="text-center py-10">
            <p class="text-gray-500">Recipe not found</p>
        </div>
    </div>
</template>

<script>
import axios from "axios";

export default {
    data() {
        return {
            recipe: null,
            loading: true,
            error: null,
        };
    },
    created() {
        this.fetchRecipe();
    },
    methods: {
        async fetchRecipe() {
            this.loading = true;
            this.error = null;

            try {
                const slug = this.$route.params.slug;
                const response = await axios.get(
                    `http://localhost:8888/api/recipes/${slug}`
                );
                this.recipe = response.data.data;
            } catch (err) {
                console.error(err);
                this.error =
                    err.response?.data?.message || "Error loading recipe";
            } finally {
                this.loading = false;
            }
        },
        getRecipeImage() {
            return this.recipe.image || "https://picsum.photos/200/300";
        },
        getAuthor() {
            if (!this.recipe.author_email) return "Unknown";
            return this.recipe.author_email;
        },
        getSteps() {
            if (!this.recipe.steps) return [];
            try {
                return typeof this.recipe.steps === "string"
                    ? JSON.parse(this.recipe.steps)
                    : this.recipe.steps;
            } catch (e) {
                return [this.recipe.steps];
            }
        },
    },
};
</script>
