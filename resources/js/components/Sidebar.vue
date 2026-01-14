<template>
    <div class="fixed left-0 top-0 h-screen w-64 bg-gray-800 border-r border-white/5 flex flex-col z-50">
        <div class="p-8 flex items-center gap-3 border-b border-white/5">
            <span class="text-xl font-bold tracking-tight text-white">
                Wallet System
            </span>
        </div>

        <nav class="flex-1 px-4 py-4 space-y-2">
            <button @click="navigate('/dashboard')" :class="[
                'menu_btn',
                isActive('/dashboard') ? 'bg-pink-500 shadow-lg shadow-electric-500/30' : 'hover:bg-white/5'
            ]">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                </svg>
                <span class="font-medium text-sm">Wallet</span>
            </button>

            <button @click="navigate('/transactions')" :class="[
                'menu_btn',
                isActive('/transactions') ? 'bg-pink-500 ' : 'hover:bg-white/5'
            ]">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2" />
                </svg>
                <span class="font-medium text-sm">Transactions</span>
            </button>
        </nav>

        <div class="p-4 border-t border-white/5 mx-4 mb-4">
            <button class="flex items-center gap-3 w-full p-3 rounded-xl hover:bg-white/5 transition-colors text-left">
                <div class="w-10 h-10 rounded-full bg-linear-to-tr from-electric-500 to-purple-500 p-[2px]">
                    <div
                        class="w-full h-full rounded-full bg-navy-800 flex items-center justify-center overflow-hidden">
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-white truncate">
                        User
                    </p>
                    <p class="text-xs text-gray-400 truncate">user</p>
                </div>
            </button>
        </div>
    </div>
</template>

<script setup>
import { useRouter, useRoute } from 'vue-router'
const router = useRouter()
const route = useRoute()

function navigate(path) {
    router.push(path)
}

function isActive(path) {
    return route.path === path || route.path.startsWith(path + '/')
}
</script>