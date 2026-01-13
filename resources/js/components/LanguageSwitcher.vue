<template>
    <select v-model="lang" @change="changeLang">
        <option value="en">English</option>
        <option value="bn">বাংলা</option>
    </select>
</template>

<script setup>
import { useI18n } from 'vue-i18n'

const { locale } = useI18n()
const lang = locale

function changeLang() {
    localStorage.setItem('lang', lang.value)
}
</script>
