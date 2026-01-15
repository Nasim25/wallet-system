<template>
    <div
        class="max-w-md mx-auto mt-20 p-6 rounded-2xl shadow hover:-translate-y-1 ease-in duration-200 card text-left border-l-4 border-l-emerald-500">
        <h1 class="text-2xl font-bold mb-4 text-white">{{ $t('login') }}</h1>
        <input v-model="email" type="email" :placeholder="$t('email')"
            class="w-full p-2 border mb-3 rounded text-white" />
        <input v-model="password" type="password" :placeholder="$t('password')"
            class="w-full p-2 border mb-3 rounded text-white" />
        <button @click="login" class="bg-green-500 text-white w-full py-2 rounded cursor-pointer">
            {{ $t('login') }}
        </button>
        <p class="text-red-500 mt-2">{{ message }}</p>
        <p class="mt-2 text-sm text-white">
            {{ $t('dont_have_account') }}
            <router-link to="/register" class="text-blue-500">{{ $t('register') }}</router-link>
        </p>
    </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import axios from "axios";
import { useRouter } from "vue-router";
const router = useRouter();
const email = ref("");
const password = ref("");
const message = ref("");

const login = async () => {
    try {
        const res = await axios.post("/api/login", {
            email: email.value,
            password: password.value,
        });

        localStorage.setItem("token", res.data.access_token);
        message.value = "Login successful!";
        window.location.href = "/dashboard";
    } catch (err) {
        message.value =
            err.response?.data?.message || "Invalid credentials";
    }
};

onMounted(async () => {
    const token = localStorage.getItem("token");
    if (token) {
        router.push("/dashboard");
    }
});
</script>
