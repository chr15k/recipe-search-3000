<template>
    <div>
        <!-- Search Form -->
        <div class="bg-white p-4 rounded-lg shadow-sm">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1"
                        >Keyword</label
                    >
                    <input
                        type="text"
                        v-model="keyword"
                        placeholder="Search by name, description..."
                        @input="handleSearch"
                        class="w-full p-2 border rounded"
                    />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1"
                        >Ingredient</label
                    >
                    <input
                        type="text"
                        v-model="ingredient"
                        placeholder="Search by ingredient..."
                        @input="handleSearch"
                        class="w-full p-2 border rounded"
                    />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1"
                        >Author email</label
                    >
                    <input
                        type="author_email"
                        v-model="author_email"
                        placeholder="Filter by author..."
                        @input="handleSearch"
                        class="w-full p-2 border rounded"
                    />
                </div>
            </div>
        </div>

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
            <p class="mt-2 text-gray-600">Loading recipes...</p>
        </div>

        <!-- Error State -->
        <div
            v-else-if="error"
            class="bg-red-50 p-4 rounded-md border-l-4 border-red-500"
        >
            <p class="text-red-700">{{ error }}</p>
        </div>

        <!-- Results -->
        <div v-else>
            <!-- No Results -->
            <p
                v-if="recipes.length === 0"
                class="text-center py-10 text-gray-500"
            >
                No recipes found. Try different search terms.
            </p>

            <!-- Recipe Grid -->
            <div v-else>
                <div
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-6"
                >
                    <recipe-card
                        v-for="recipe in recipes"
                        :key="recipe.id"
                        :recipe="recipe"
                        @click="viewRecipe(recipe)"
                    />
                </div>

                <!-- Pagination -->
                <div
                    v-if="pagination.total_pages > 1"
                    class="mt-8 flex justify-center space-x-4 py-2"
                >
                    <button
                        @click="changePage(pagination.current_page - 1)"
                        :disabled="pagination.current_page === 1"
                        class="px-4 py-2 border rounded disabled:opacity-50 cursor-pointer disabled:cursor-not-allowed"
                    >
                        Previous
                    </button>
                    <span class="py-2">
                        Page {{ pagination.current_page }} of
                        {{ pagination.total_pages }}
                    </span>
                    <button
                        @click="changePage(pagination.current_page + 1)"
                        :disabled="
                            pagination.current_page === pagination.total_pages
                        "
                        class="px-4 py-2 border rounded disabled:opacity-50 cursor-pointer disabled:cursor-not-allowed"
                    >
                        Next
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from "axios";
import { debounce } from "lodash";
import RecipeCard from "./RecipeCard.vue";

export default {
    components: {
        RecipeCard,
    },
    data() {
        return {
            recipes: [],
            keyword: "",
            ingredient: "",
            author_email: "",
            loading: false,
            error: null,
            pagination: {
                current_page: 1,
                total_pages: 1,
                total: 0,
            },
        };
    },
    created() {
        this.debouncedSearch = debounce(this.search, 300);

        // Check for URL params
        const query = this.$route.query;
        if (query.keyword) this.keyword = query.keyword;
        if (query.ingredient) this.ingredient = query.ingredient;
        if (query.author_email) this.author_email = query.author_email;
        if (query.page) this.pagination.current_page = parseInt(query.page);

        // Initial search
        this.search();
    },
    methods: {
        handleSearch() {
            this.pagination.current_page = 1;

            const keywordLength = this.keyword ? this.keyword.length : 0;
            const ingredientLength = this.ingredient
                ? this.ingredient.length
                : 0;
            const authorEmailLength = this.author_email
                ? this.author_email.length
                : 0;

            // Email validation regex
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            const isValidEmail =
                !this.author_email || emailRegex.test(this.author_email);

            // Only search if email is valid and at least one field has more than 2 characters or all are empty
            if (
                isValidEmail &&
                (keywordLength > 2 ||
                    ingredientLength > 2 ||
                    (authorEmailLength > 2 && isValidEmail) ||
                    (keywordLength === 0 &&
                        ingredientLength === 0 &&
                        authorEmailLength === 0))
            ) {
                this.debouncedSearch();
            }
        },
        async search() {
            this.loading = true;
            this.error = null;

            try {
                // Prepare params
                const params = {};
                if (this.keyword && this.keyword.length > 2)
                    params.keyword = this.keyword;
                if (this.ingredient && this.ingredient.length > 2)
                    params.ingredient = this.ingredient;
                if (this.author_email && this.author_email.length > 2)
                    params.author_email = this.author_email;
                params.page = this.pagination.current_page;

                // Update URL
                this.$router.replace({
                    query: { ...params },
                });

                const response = await axios.get(
                    "http://localhost:8888/api/recipes/search",
                    { params }
                );

                this.recipes = response.data.data;
                this.pagination = {
                    current_page: response.data.meta.current_page,
                    total_pages: response.data.meta.last_page,
                    total: response.data.meta.total,
                };
            } catch (err) {
                console.error(err);
                if (err.response?.data?.errors) {
                    const errors = Object.values(err.response.data.errors)
                        .flat()
                        .join(", ");
                    this.error = `Error: ${errors}`;
                } else {
                    this.error =
                        err.response?.data?.message || "Error fetching recipes";
                }
            } finally {
                this.loading = false;
            }
        },
        changePage(page) {
            this.pagination.current_page = page;
            this.search();
        },
        viewRecipe(recipe) {
            this.$router.push(`/recipe/${recipe.slug}`);
        },
    },
};
</script>
