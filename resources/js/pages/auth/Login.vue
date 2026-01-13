<template>
    <div
        class="max-w-md mx-auto mt-20 p-6 rounded-2xl shadow hover:-translate-y-1 ease-in duration-200 card text-left border-l-4 border-l-emerald-500"
    >
        <h1 class="text-2xl font-bold mb-4">Login</h1>
        <input
            v-model="email"
            type="email"
            placeholder="Email"
            class="w-full p-2 border mb-3 rounded"
        />
        <input
            v-model="password"
            type="password"
            placeholder="Password"
            class="w-full p-2 border mb-3 rounded"
        />
        <button
            @click="login"
            class="bg-green-500 text-white w-full py-2 rounded cursor-pointer"
        >
            Login
        </button>
        <p class="text-red-500 mt-2">{{ message }}</p>
        <p class="mt-2 text-sm">
            Don't have an account?
            <router-link to="/register" class="text-blue-500"
                >Register</router-link
            >
        </p>
    </div>
</template>

<script>
export default {
    data() {
        return { email: "", password: "", message: "" };
    },
    methods: {
        async login() {
            try {
                const res = await this.$axios.post("/api/login", {
                    email: this.email,
                    password: this.password,
                });
                localStorage.setItem("token", res.data.access_token);
                this.message = "Login successful!";
                this.$router.push("/dashboard");
            } catch (err) {
                this.message =
                    err.response?.data?.message || "Invalid credentials";
            }
        },
    },
};
</script>
