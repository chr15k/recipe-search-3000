import { createRouter, createWebHistory } from "vue-router";
import RecipeSearch from "../components/RecipeSearch.vue";
import RecipeDetail from "../components/RecipeDetail.vue";

const router = createRouter({
    history: createWebHistory(import.meta.env.BASE_URL),
    routes: [
        {
            path: "/",
            component: RecipeSearch,
        },
        {
            path: "/recipe/:slug",
            component: RecipeDetail,
        },
    ],
});

export default router;
