<template>
    <div class="min-h-screen bg-[#0A1628]">
        <!-- <Navbar /> -->

        <Sidebar />
        <main class="flex-1 p-6 ml-64">
            <div class="p-4 pb-10 flex justify-between items-center">
                <h1 class="text-white font-bold text-2xl">Welcome back {{ user?.name }}</h1>
                <div>
                    <LanguageSwitcher />
                    <button class="bg-red-500 px-3 py-1 rounded text-white ml-4">Logout</button>
                </div>
            </div>
            <router-view />
        </main>
    </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { useRouter } from "vue-router";
import Navbar from "./components/Navbar.vue";
import Sidebar from "./components/Sidebar.vue";
import LanguageSwitcher from "@/components/LanguageSwitcher.vue";
import axios from "axios"; // Make sure axios installed and imported

const router = useRouter();
const user = ref(null); // reactive state

onMounted(async () => {
    const token = localStorage.getItem("token");
    if (token) {
        try {
            const res = await axios.get("/api/user", {
                headers: { Authorization: `Bearer ${token}` },
            });
            user.value = res.data; // update reactive ref
        } catch (err) {
            localStorage.removeItem("token");
            router.push("/login");
        }
    } else {
        router.push("/login");
    }
});
</script>

<style>
/* global style if needed */
</style>
