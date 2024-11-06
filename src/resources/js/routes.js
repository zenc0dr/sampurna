import { createWebHistory, createRouter } from "vue-router";

const routes = [
    {
        path: "/sampurna.ui",
        name: "Sampurna",
        component: () => import("../vue/components/pages/SampurnaUi.vue"),
    }
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

export default router;
