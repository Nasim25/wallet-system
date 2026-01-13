<template>
    <nav class="bg-transparent text-white p-4 flex justify-between items-center">
        <div class="font-bold">WalletSystem</div>

        <div>
            <LanguageSwitcher />
            <button v-if="user" @click="logout" class="bg-red-500 px-3 py-1 rounded">
                Logout
            </button>
        </div>
    </nav>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import LanguageSwitcher from '@/components/LanguageSwitcher.vue'

const user = ref(null)

onMounted(async () => {
    const token = localStorage.getItem('token')

    if (!token) return

    try {
        const res = await axios.get('/api/user', {
            headers: {
                Authorization: `Bearer ${token}`,
            },
        })

        user.value = res.data
    } catch (error) {
        user.value = null
    }
})

async function logout() {
    const token = localStorage.getItem('token')

    if (!token) return

    try {
        await axios.post(
            '/api/logout',
            {},
            {
                headers: {
                    Authorization: `Bearer ${token}`,
                },
            }
        )
    } finally {
        localStorage.removeItem('token')
        window.location.href = '/login'
    }
}
</script>
