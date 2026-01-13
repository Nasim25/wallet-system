import { createRouter, createWebHistory } from "vue-router";
import Home from "../pages/Home.vue";

const routes = [
    { path: "/", component: Home },
    { path: "/wallet", component: () => import("../pages/Wallet.vue") },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

export default router;
