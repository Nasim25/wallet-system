<template>
    <div
        class="relative  mx-auto overflow-hidden rounded-3xl bg-linear-to-br from-pink-500 to-purple-700 p-8 text-white shadow-2xl shadow-electric-500/20">
        <h1 class="text-2xl font-bold mb-4">Wallet Balance</h1>

        <p>Your balance: ৳ {{ wallet?.balance ?? 0 }}</p>

        <button v-if="!wallet?.agreement_token" @click="createAgreement" :disabled="loading"
            class="mt-10 bottom-0 bg-white text-purple-700 px-4 py-2 rounded-lg hover:bg-gray-100 transition cursor-pointer disabled:opacity-50">
            <span v-if="!loading">Create Agreement</span>
            <span v-else>Creating...</span>
        </button>

        <button v-if="wallet?.agreement_token" @click="createAgreement" :disabled="loading"
            class="mt-10 bottom-0 bg-white text-purple-700 px-4 py-2 rounded-lg hover:bg-gray-100 transition cursor-pointer disabled:opacity-50">
            <span v-if="!loading">Deposit money </span>
            <span v-else>Creating...</span>
        </button>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

const wallet = ref(null)
const loading = ref(false)
const router = useRouter()
const route = router.currentRoute.value;

onMounted(async () => {
    const token = localStorage.getItem('token')
    if (!token) return router.push('/login')

    try {
        const res = await axios.get('/api/wallet', {
            headers: { Authorization: `Bearer ${token}` },
        })
        wallet.value = res.data
    } catch (err) {
        localStorage.removeItem('token')
        router.push('/login')
    }

    if (route.query.agreement === 'created') {
        alert('Agreement created')
    }
})


async function createAgreement() {
    const token = localStorage.getItem('token')
    if (!token) return router.push('/login')

    try {
        loading.value = true
        const res = await axios.post(
            '/api/wallet/agreement',
            { payment_method: 'bkash' },
            { headers: { Authorization: `Bearer ${token}` } }
        )

        if (res.data?.success && res.data?.bkashURL) {
            window.location.href = res.data.bkashURL
        } else {
            throw new Error(res.data?.message || 'Failed to create agreement')
        }
    } catch (err) {
        console.error('Create agreement error', err)
        alert(err.response?.data?.message || err.message || 'Error')
    } finally {
        loading.value = false
    }
}
</script>