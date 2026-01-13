<template>
    <div class="min-h-screen bg-gray-100">
        <Navbar />
        <router-view />
    </div>
</template>

<script>
import Navbar from "./components/Navbar.vue";

export default {
    components: { Navbar },
};
</script>

<style>
/* global style if needed */
</style>
