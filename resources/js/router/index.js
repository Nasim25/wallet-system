import { createRouter, createWebHistory } from "vue-router";
import Login from "../pages/auth/Login.vue";
import Register from "../pages/auth/Register.vue";
import Dashboard from "../pages/Dashboard.vue";
import axios from "axios";

const routes = [
    { path: "/", redirect: "/login" },
    { path: "/login", component: Login },
    { path: "/register", component: Register },
    { path: "/dashboard", component: Dashboard, meta: { requiresAuth: true } },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

router.beforeEach(async (to, from, next) => {
    const token = localStorage.getItem("token");
    if (to.meta.requiresAuth) {
        if (!token) return next("/login");
        try {
            await axios.get("/api/user", {
                headers: { Authorization: `Bearer ${token}` },
            });
            next();
        } catch (err) {
            localStorage.removeItem("token");
            next("/login");
        }
    } else next();
});

export default router;
