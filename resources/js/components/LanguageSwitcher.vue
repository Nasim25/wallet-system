<template>
    <select v-model="lang" @change="createAgreement" class=" bg-green-700 p-2 rounded-md text-white cursor-pointer">
        <option value="en">English</option>
        <option value="bn">বাংলা</option>
    </select>
</template>

<script setup>
import { useI18n } from 'vue-i18n'
import axios from 'axios'

const { locale } = useI18n()
const lang = locale

/**
 * Auth helper
 */
function getTokenOrRedirect() {
    const token = localStorage.getItem('token')
    if (!token) {
        router.push('/login')
        return null
    }
    return token
}

/**
 * Agreement
 */
async function createAgreement() {
    const token = getTokenOrRedirect()
    if (!token) return

    try {
        const { data } = await axios.post(
            '/api/locale/switch',
            { locale: lang.value },
            { headers: { Authorization: `Bearer ${token}` } }
        )

        localStorage.setItem('locale', lang.value)

    } catch (error) {
        alert(error.response?.data?.message || error.message)
    } finally {

    }
}

</script>
