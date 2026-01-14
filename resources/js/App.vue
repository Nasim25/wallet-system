<template>
    <div class="min-h-screen bg-[#0A1628]">
        <!-- Sidebar + Main (only show when authenticated) -->
        <template v-if="user">
            <Sidebar />
            <main class="flex-1 p-6 ml-64">
                <div class="p-4 pb-10 flex justify-between items-center">
                    <h1 class="text-white font-bold text-2xl">{{ $t("welcome") }} {{ user?.name }}</h1>
                    <div>
                        <LanguageSwitcher />
                        <button @click="logout" class="bg-red-500 px-3 py-1 rounded text-white ml-4">{{ $t("logout") }}</button>
                    </div>
                </div>
                <router-view />
            </main>
        </template>

        <!-- Login/Register view (when not authenticated) -->
        <template v-else>
            <router-view />
        </template>
    </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { useRouter } from "vue-router";
import Sidebar from "./components/Sidebar.vue";
import LanguageSwitcher from "@/components/LanguageSwitcher.vue";
import axios from "axios";

const router = useRouter();
const user = ref(null);

onMounted(async () => {
    const token = localStorage.getItem("token");
    if (token) {
        try {
            const res = await axios.get("/api/user", {
                headers: { Authorization: `Bearer ${token}` },
            });
            user.value = res.data;
        } catch (err) {
            localStorage.removeItem("token");
            router.push("/login");
        }
    }
});

async function logout() {
    const token = localStorage.getItem("token");
    if (!token) return;

    try {
        await axios.post(
            "/api/logout",
            {},
            { headers: { Authorization: `Bearer ${token}` } }
        );
    } finally {
        localStorage.removeItem("token");
        user.value = null;
        router.push("/login");
    }
}
</script>