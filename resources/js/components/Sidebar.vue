<template>
    <div class="fixed left-0 top-0 h-screen w-64 bg-gray-800 border-r border-white/5 flex flex-col z-50">

        <div class="p-8 flex items-center gap-3 border-b border-white/5">

            <span class="text-xl font-bold tracking-tight text-white">
                Wallet System
            </span>
        </div>

        <nav class="flex-1 px-4 py-4 space-y-2">
            <button
                class="w-full flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 group bg-pink-500 text-white shadow-lg shadow-electric-500/20">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                </svg>
                <span class="font-medium text-sm">Wallet</span>

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

</script>
