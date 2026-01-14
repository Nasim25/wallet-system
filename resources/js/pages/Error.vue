<template>
    <div
        class="relative mx-auto overflow-hidden rounded-3xl flex justify-center items-center flex-col h-96 w-96  text-center p-6">
        <p class="text-lg mb-6 text-white">Something went wrong. Please try again later.</p>
        <button @click="goHome" class="bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700">
            Go to Dashboard
        </button>
    </div>

</template>

<script setup>

import { useRouter } from 'vue-router'
const router = useRouter()


function goHome() {
    router.push('/dashboard')
}
</script>
