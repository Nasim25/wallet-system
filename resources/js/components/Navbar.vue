<template>
    <nav class="bg-blue-600 text-white p-4 flex justify-between items-center">
        <div class="font-bold">WalletSystem</div>
        <div>
            <button
                v-if="user"
                @click="logout"
                class="bg-red-500 px-3 py-1 rounded"
            >
                Logout
            </button>
        </div>
    </nav>
</template>

<script>
import axios from "axios";
export default {
    data() {
        return { user: null };
    },
    async created() {
        const token = localStorage.getItem("token");
        if (token) {
            try {
                const res = await axios.get("/api/user", {
                    headers: { Authorization: `Bearer ${token}` },
                });
                this.user = res.data;
            } catch (err) {
                this.user = null;
            }
        }
    },
    methods: {
        async logout() {
            const token = localStorage.getItem("token");
            if (token) {
                await axios.post(
                    "/api/logout",
                    {},
                    { headers: { Authorization: `Bearer ${token}` } }
                );
                localStorage.removeItem("token");
                window.location.href = "/login";
            }
        },
    },
};
</script>
