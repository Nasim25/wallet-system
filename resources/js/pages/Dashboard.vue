<template>
    <div class="max-w-md mx-auto mt-20 p-6 bg-red rounded shadow">
        <h1 class="text-2xl font-bold mb-4">Dashboard</h1>
        <p>Welcome, {{ user?.name }}</p>
        <p>Your balance: ৳ {{ user?.wallet?.balance ?? 0 }}</p>
    </div>
</template>

<script>
export default {
    data() {
        return { user: null };
    },
    async created() {
        const token = localStorage.getItem("token");
        if (token) {
            try {
                const res = await this.$axios.get("/api/user", {
                    headers: { Authorization: `Bearer ${token}` },
                });
                this.user = res.data;
            } catch (err) {
                localStorage.removeItem("token");
                this.$router.push("/login");
            }
        } else {
            this.$router.push("/login");
        }
    },
};
</script>
