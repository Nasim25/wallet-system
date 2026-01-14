<template>
    <div class="relative mx-auto overflow-hidden rounded-3xl
               bg-linear-to-br from-pink-500 to-purple-700
               p-8 text-white shadow-2xl shadow-electric-500/20">
        <h1 class="mb-4 text-2xl font-bold">Wallet Balance</h1>
        <p>Your balance: ৳ {{ balance }}</p>

        <!-- Agreement -->
        <button v-if="!hasAgreement" @click="createAgreement" :disabled="loading" class="mt-10 bg-white px-4 py-2 rounded-lg
                   text-purple-700 hover:bg-gray-100
                   transition disabled:opacity-50">
            <span v-if="!loading">Create Agreement</span>
            <span v-else>Processing...</span>
        </button>

        <!-- Deposit -->
        <button v-else @click="openDepositModal" class="mt-10 bg-white px-4 py-2 rounded-lg
                   text-purple-700 hover:bg-gray-100 transition cursor-pointer">
            Deposit Money
        </button>
    </div>

    <!-- Deposit Modal -->
    <div v-if="showDepositModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
        <div class="w-full max-w-sm rounded-2xl bg-white p-6 text-gray-800">
            <h2 class="mb-4 text-xl font-bold">Deposit Amount</h2>

            <input v-model.number="depositAmount" type="number" min="10" placeholder="Enter amount"
                class="w-full rounded-lg border p-3 mb-4 focus:outline-none focus:ring-2 focus:ring-purple-500" />

            <div class="flex justify-end gap-3">
                <button @click="closeDepositModal" class="rounded-lg bg-gray-200 px-4 py-2 cursor-pointer">
                    Cancel
                </button>

                <button @click="confirmDeposit" :disabled="loading || !isValidAmount" class="rounded-lg bg-purple-600 px-4 py-2 text-white
                           disabled:opacity-50 cursor-pointer">
                    <span v-if="!loading">Confirm</span>
                    <span v-else>Processing...</span>
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import axios from 'axios'
import { useToast } from 'vue-toastification'

const toast = useToast()
const wallet = ref(null)
const loading = ref(false)

const showDepositModal = ref(false)
const depositAmount = ref(null)

const router = useRouter()
const route = useRoute()

const hasAgreement = computed(() => !!wallet.value?.agreement_token)
const balance = computed(() => wallet.value?.balance ?? 0)

const isValidAmount = computed(
    () => depositAmount.value && depositAmount.value > 0
)

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
 * Fetch wallet
 */
async function fetchWallet() {
    const token = getTokenOrRedirect()
    if (!token) return

    try {
        const { data } = await axios.get('/api/wallet', {
            headers: { Authorization: `Bearer ${token}` },
        })
        wallet.value = data
    } catch {
        localStorage.removeItem('token')
        router.push('/login')
    }
}

/**
 * Agreement
 */
async function createAgreement() {
    const token = getTokenOrRedirect()
    if (!token) return

    loading.value = true

    try {
        const { data } = await axios.post(
            '/api/wallet/agreement',
            { payment_method: 'bkash' },
            { headers: { Authorization: `Bearer ${token}` } }
        )

        if (data?.success && data?.bkashURL) {
            window.location.href = data.bkashURL
        } else {
            throw new Error(data?.message || 'Agreement failed')
        }
    } catch (error) {
        alert(error.response?.data?.message || error.message)
    } finally {
        loading.value = false
    }
}

/**
 * Modal Controls
 */
function openDepositModal() {
    depositAmount.value = null
    showDepositModal.value = true
}

function closeDepositModal() {
    if (!loading.value) {
        showDepositModal.value = false
    }
}

/**
 * Confirm Deposit
 */
async function confirmDeposit() {
    const token = getTokenOrRedirect()
    if (!token || !isValidAmount.value) return

    loading.value = true

    try {
        const { data } = await axios.post(
            '/api/payment/create',
            {
                payment_method: 'bkash',
                amount: depositAmount.value,
            },
            { headers: { Authorization: `Bearer ${token}` } }
        )

        if (data?.success && data?.bkashURL) {
            window.location.href = data.bkashURL
        } else {
            throw new Error(data?.message || 'Deposit failed')
        }
    } catch (error) {
        alert(error.response?.data?.message || error.message)
    } finally {
        loading.value = false
        showDepositModal.value = false
    }
}

/**
 * Lifecycle
 */
onMounted(async () => {
    await fetchWallet()

    if (route.query.agreement === 'created') {
        toast.success('Agreement created successfully')
    }

    if (route.query.payment === 'success') {
        toast.success('Deposit successful')
    }
})
</script>
