<template>
    <div class="p-6 bg-white rounded-2xl shadow-lg">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold mb-4">Transaction History</h2>
            <button class="px-2 py-2 mb-2 bg-pink-500 text-white rounded-lg hover:bg-pink-500 ">Download Statement</button>
        </div>


        <!-- Loader -->
        <div v-if="loading" class="text-center py-10">Loading transactions...</div>

        <!-- Empty State -->
        <div v-else-if="transactions.length === 0" class="text-center py-10 text-gray-500">
            No transactions found.
        </div>

        <!-- Table -->
        <div v-else class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Date</th>
                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Transaction ID</th>
                        <th class="px-4 py-2 text-right text-sm font-medium text-gray-700">Amount</th>
                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Type</th>
                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Status</th>
                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-700">Action</th>
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
                            <button :disabled="tx.type !== 'credit'" :class="[
                                'px-3 text-sm py-1 rounded-lg cursor-pointer',
                                tx.type === 'credit'
                                    ? 'bg-red-500 text-white hover:bg-red-600'
                                    : 'bg-gray-300 text-gray-500 cursor-not-allowed'
                            ]">
                                Refund
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
    </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import axios from 'axios'

const transactions = ref([])
const loading = ref(false)
const currentPage = ref(1)
const lastPage = ref(1)
const prevPageUrl = ref(null)
const nextPageUrl = ref(null)

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


onMounted(() => fetchTransactions())
</script>
