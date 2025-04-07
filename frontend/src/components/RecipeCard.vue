<template>
    <div
        class="bg-white rounded-lg shadow hover:shadow-md transition-shadow cursor-pointer overflow-hidden"
        @click="$emit('click')"
    >
        <div class="h-48 bg-gray-200 overflow-hidden">
            <img
                :src="getRecipeImage()"
                :alt="recipe.name"
                class="w-full h-full object-cover"
                loading="lazy"
            />
        </div>

        <div class="p-4">
            <h3 class="font-bold text-lg mb-2 text-gray-800">
                {{ recipe.name }}
            </h3>
            <p class="text-gray-600 text-sm mb-3">
                {{ recipe.description }}
            </p>

            <div
                class="flex justify-between items-center text-xs text-gray-500 pt-2 border-t"
            >
                <span>{{ getAuthor() }}</span>
                <span
                    class="bg-indigo-100 text-indigo-700 px-2 py-1 rounded-full"
                >
                    {{ getIngredientCount() }}
                </span>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        recipe: {
            type: Object,
            required: true,
        },
    },
    methods: {
        getRecipeImage() {
            return this.recipe.image || "https://picsum.photos/200/300";
        },
        getAuthor() {
            if (!this.recipe.author_email) return "Unknown";
            return this.recipe.author_email;
        },
        getIngredientCount() {
            if (!this.recipe.ingredients) return "? ingredients";
            return `${this.recipe.ingredients.length} ingredients`;
        },
    },
};
</script>
