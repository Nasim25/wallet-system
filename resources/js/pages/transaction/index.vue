<template>
    <div class="p-6 bg-white rounded-2xl shadow-lg">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold mb-4">{{ $t("transaction_history") }}</h2>
            <button @click="downloadStatement"
                class="px-2 py-2 mb-2 bg-pink-500 text-white rounded-lg hover:bg-pink-500 cursor-pointer ">{{
                    $t("download_statement") }}</button>
        </div>


        <!-- Loader -->
        <div v-if="loading" class="text-center py-10">Loading transactions...</div>

        <!-- Empty State -->
        <div v-else-if="transactions.length === 0" class="text-center py-10 text-gray-500">
            {{ $t("no_transactions") }}
        </div>

        <!-- Table -->
        <div v-else class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">{{ $t("date") }}</th>
                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">{{ $t("transaction_id") }}
                        </th>
                        <th class="px-4 py-2 text-right text-sm font-medium text-gray-700">{{ $t("amount") }}</th>
                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">{{ $t("type") }}</th>
                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">{{ $t("status") }}</th>
                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">{{ $t("action") }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <tr v-for="tx in transactions" :key="tx.id">
                        <td class="px-4 py-2 text-sm">{{ tx.created_at }}</td>
                        <td class="px-4 py-2 text-sm">{{ tx.trx_id }}</td>
                        <td class="px-4 py-2 text-right text-sm font-semibold">৳ {{ tx.amount }}</td>
                        <td class="px-4 py-2 text-sm capitalize">{{ tx.type }}</td>
                        <td class="px-4 py-2 text-sm">
                            <span :class="{
                                'text-green-600': tx.status === 'completed',
                                'text-red-600': tx.status === 'failed',
                                'text-yellow-500': tx.status === 'pending'
                            }">
                                {{ tx.status }}
                            </span>
                        </td>
                        <td>
                            <button @click="openRefundModal(tx)" :disabled="tx.type !== 'credit' || tx.amount <= 0"
                                :class="[
                                    'px-3 text-sm py-1 rounded-lg',
                                    tx.type === 'credit' && tx.amount > 0
                                        ? 'bg-red-500 text-white hover:bg-red-600'
                                        : 'bg-gray-300 text-gray-500 cursor-not-allowed'
                                ]">
                                {{ $t("refund") }}
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div v-if="lastPage > 1" class="flex justify-end mt-4 space-x-2">
            <button @click="goToPage(currentPage - 1)" :disabled="!prevPageUrl"
                class="px-3 py-1 rounded-lg border hover:bg-gray-100 disabled:opacity-50 cursor-pointer">
                Previous
            </button>

            <button v-for="page in pages" :key="page" @click="goToPage(page)"
                :class="['px-3 py-1 rounded-lg border cursor-pointer', page === currentPage ? 'bg-pink-500 text-white' : '']">
                {{ page }}
            </button>

            <button @click="goToPage(currentPage + 1)" :disabled="!nextPageUrl"
                class="px-3 py-1 rounded-lg border cursor-pointer hover:bg-gray-100 disabled:opacity-50">
                Next
            </button>
        </div>

        <!-- Refund Modal -->
        <div v-if="showRefundModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">

            <div class="bg-white w-full max-w-md rounded-2xl shadow-lg p-6">
                <h3 class="text-lg font-semibold mb-4">{{ $t("refund_transaction") }}</h3>

                <div class="space-y-3">
                    <div class="text-sm text-gray-600">
                        {{ $t("refundable_amount") }}
                        <span class="font-semibold text-gray-900">
                            {{ selectedTx?.amount }}
                        </span>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            {{ $t("refund_amount") }}
                        </label>
                        <input type="number" v-model="refundAmount" :max="selectedTx?.amount" min="1"
                            class="w-full border rounded-lg px-3 py-2 focus:ring focus:ring-pink-300"
                            placeholder="Enter refund amount" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            {{ $t("refund_reason") }}
                        </label>
                        <input type="text" v-model="refundReason"
                            class="w-full border rounded-lg px-3 py-2 focus:ring focus:ring-pink-300"
                            :placeholder="$t('refund_reason')" />
                    </div>
                </div>

                <div class="flex justify-end gap-3 mt-6">
                    <button @click="closeRefundModal" class="px-4 py-2 rounded-lg border hover:bg-gray-100">
                        {{ $t("cancel") }}
                    </button>

                    <button @click="confirmRefund"
                        :disabled="refundAmount <= 0 || refundAmount > selectedTx?.amount || refundLoading"
                        class="px-4 py-2 rounded-lg bg-red-500 text-white hover:bg-red-600 disabled:opacity-50">
                        {{ refundLoading ? $t("processing") : $t("confirm_refund") }}
                    </button>
                </div>
            </div>
        </div>

    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import axios from 'axios'
import { useToast } from 'vue-toastification'

const toast = useToast()
const transactions = ref([])
const loading = ref(false)
const currentPage = ref(1)
const lastPage = ref(1)
const prevPageUrl = ref(null)
const nextPageUrl = ref(null)

const showRefundModal = ref(false)
const selectedTx = ref(null)
const refundAmount = ref(0)
const refundReason = ref('')
const refundLoading = ref(false)

/**
 * Fetch transactions
 */
async function fetchTransactions(page = 1) {
    loading.value = true
    const token = localStorage.getItem('token')
    if (!token) return

    try {
        const { data } = await axios.get(`/api/transactions?page=${page}`, {
            headers: { Authorization: `Bearer ${token}` },
        })

        transactions.value = data.data || []
        currentPage.value = data.current_page
        lastPage.value = data.last_page
        prevPageUrl.value = data.prev_page_url
        nextPageUrl.value = data.next_page_url
    } catch (err) {
        console.error('Failed to fetch transactions', err)
    } finally {
        loading.value = false
    }
}

/**
 * Pagination click
 */
function goToPage(page) {
    if (page < 1 || page > lastPage.value) return
    fetchTransactions(page)
}

/**
 * Pages array for page numbers
 */
const pages = computed(() => {
    const arr = []
    for (let i = 1; i <= lastPage.value; i++) arr.push(i)
    return arr
})

async function downloadStatement() {
    const token = localStorage.getItem('token')
    if (!token) return

    try {
        const response = await axios.get('/api/transactions/statement', {
            headers: {
                Authorization: `Bearer ${token}`,
            },
            responseType: 'blob',
        })

        // Create blob URL
        const blob = new Blob([response.data])
        const url = window.URL.createObjectURL(blob)

        // Create temporary link
        const link = document.createElement('a')
        link.href = url

        // File name (backend header > fallback)
        link.download = 'transaction-statement.pdf'
        document.body.appendChild(link)

        link.click()

        // Cleanup
        link.remove()
        window.URL.revokeObjectURL(url)
    } catch (error) {
        console.error('Failed to download statement', error)
        alert('Failed to download statement')
    }
}

async function confirmRefund() {
    if (!selectedTx.value) return

    refundLoading.value = true
    const token = localStorage.getItem('token')

    try {
        await axios.post(
            `/api/refund`,
            {
                amount: refundAmount.value,
                transaction_id: selectedTx.value.id,
                reason: refundReason.value
            },
            {
                headers: { Authorization: `Bearer ${token}` },
            }
        )

        closeRefundModal()
        fetchTransactions(currentPage.value)
        toast.success('Refund successful');
    } catch (error) {
        console.error(error)
        toast.error(error.response?.data?.message || 'Refund failed')
    } finally {
        refundLoading.value = false

    }
}

function openRefundModal(tx) {
    selectedTx.value = tx
    refundAmount.value = tx.amount
    refundReason.value = ''
    showRefundModal.value = true
}

function closeRefundModal() {
    showRefundModal.value = false
    selectedTx.value = null
    refundReason.value = ''
    refundAmount.value = 0
}


onMounted(() => fetchTransactions())
</script>
