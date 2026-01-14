<template>
    <div class="max-w-md mx-auto mt-20 p-6 rounded shadow card">
        <h1 class="text-2xl font-bold mb-4 text-white">Register</h1>
        <input v-model="name" placeholder="Name" class="w-full p-2 border mb-2 rounded text-white" />
        <input v-model="email" placeholder="Email" class="w-full p-2 border mb-2 rounded text-white" />
        <input v-model="password" type="password" placeholder="Password" class="w-full p-2 border mb-2 rounded text-white" />
        <input v-model="password_confirmation" type="password" placeholder="Confirm Password"
            class="w-full p-2 border mb-4 rounded text-white" />
        <button @click="register" class="bg-blue-500 text-white w-full py-2 rounded cursor-pointer">
            Register
        </button>
        <p class="text-red-500 mt-2">{{ message }}</p>
        <p class="mt-2 text-sm text-white">
            Already have an account?
            <router-link to="/login" class="text-blue-500">Login</router-link>
        </p>
    </div>
</template>

<script>
export default {
    data() {
        return {
            name: "",
            email: "",
            password: "",
            password_confirmation: "",
            message: "",
        };
    },
    methods: {
        async register() {
            try {
                await this.$axios.post("/api/register", {
                    name: this.name,
                    email: this.email,
                    password: this.password,
                    password_confirmation: this.password_confirmation,
                });
                this.message = "Registered successfully!";
                this.$router.push("/login");
            } catch (err) {
                this.message = err.response?.data?.message || "Error occurred";
            }
        },
    },
};
</script>
