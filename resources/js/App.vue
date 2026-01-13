<template>
    <div class="min-h-screen">
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
