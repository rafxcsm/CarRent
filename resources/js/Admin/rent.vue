<template>
  <div class="rent-page">
    <main class="main-content">
      <section class="content-section">
        <table id="rentalTable">
          <thead>
            <tr>
              <th>ID</th>
              <th>Customer</th>
              <th>Vehicle</th>
              <th>Payment Receipt</th>
              <th>Status</th>
              <th>Action</th>
            </tr>
          </thead>

          <tbody>
            <tr v-for="rental in rentals" :key="rental.id">
              <td>{{ rental.id }}</td>
              <td>{{ rental.customer }}</td>
              <td>{{ rental.vehicle }}</td>

              <td>
                <span
                  v-if="rental.proof_file"
                  class="receipt-link"
                  @click="openReceipt(rental)"
                >
                  {{ rental.proof_file }}
                </span>
                <span v-else>N/A</span>
              </td>

              <td class="status-cell">
                <span
                  class="status-badge"
                  :class="`status-${rental.status}`"
                >
                  {{ rental.status }}
                </span>
              </td>

              <td>
                <template v-if="rental.status === 'pending'">
                  <button class="approveBtn" @click="approveRental(rental.id)">
                    Approve
                  </button>
                  <button class="denyBtn" @click="denyRental(rental.id)">
                    Deny
                  </button>
                </template>
                <template v-else>
                  <span class="processed-text">Processed</span>
                </template>
              </td>
            </tr>

            <tr v-if="!rentals.length">
              <td colspan="6" class="empty-state">No rentals found.</td>
            </tr>
          </tbody>
        </table>
      </section>
    </main>

    <div v-if="showReceiptModal" id="receiptModal" @click.self="closeReceipt">
      <span class="closeReceipt" @click="closeReceipt">&times;</span>
      <div class="receipt-placeholder">
        <h3>Receipt Preview</h3>
        <p>{{ selectedReceipt }}</p>

        <img
          v-if="selectedReceiptUrl && isImage(selectedReceiptUrl)"
          :src="selectedReceiptUrl"
          alt="Payment Receipt"
          class="receipt-image"
        />

        <div v-else-if="selectedReceiptUrl" class="receipt-file-box">
          <a :href="selectedReceiptUrl" target="_blank" rel="noopener noreferrer">
            Open Receipt File
          </a>
        </div>

        <p v-else>No file uploaded yet.</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const showReceiptModal = ref(false)
const selectedReceipt = ref('')
const selectedReceiptUrl = ref('')
const rentals = ref([])

const fetchRentals = async () => {
  try {
    const response = await axios.get('/admin/rentals')

    if (response.data?.success) {
      rentals.value = response.data.rentals
    } else {
      rentals.value = []
    }
  } catch (error) {
    console.error('Failed to fetch rentals:', error)
    rentals.value = []
  }
}

onMounted(() => {
  fetchRentals()
})

const openReceipt = (rental) => {
  selectedReceipt.value = rental.proof_file || 'No receipt uploaded'
  selectedReceiptUrl.value = rental.proof_url || ''
  showReceiptModal.value = true
}

const closeReceipt = () => {
  selectedReceipt.value = ''
  selectedReceiptUrl.value = ''
  showReceiptModal.value = false
}

const approveRental = async (id) => {
  try {
    const response = await axios.put(`/admin/rentals/${id}/approve`)

    if (response.data?.success) {
      await fetchRentals()
    }
  } catch (error) {
    console.error('Approve failed:', error)
    alert(error.response?.data?.message || 'Failed to approve rental.')
  }
}

const denyRental = async (id) => {
  try {
    const response = await axios.put(`/admin/rentals/${id}/deny`)

    if (response.data?.success) {
      await fetchRentals()
    }
  } catch (error) {
    console.error('Deny failed:', error)
    alert(error.response?.data?.message || 'Failed to deny rental.')
  }
}

const isImage = (url) => {
  return /\.(jpg|jpeg|png|gif|webp)$/i.test(url)
}
</script>

<style scoped>
.rent-page {
  box-sizing: border-box;
  position: fixed;
  top: 80px;
  left: 317px;
  width: calc(100vw - 317px);
  height: calc(100vh - 80px);
  padding: 30px;
  background: black;
  overflow-y: auto;
  overflow-x: hidden;
}

.main-content {
  min-height: 100%;
  display: flex;
  flex-direction: column;
  position: relative;
  z-index: 1;
}

.content-section {
  margin-top: 40px;
  background: rgba(0, 0, 0, 0.6);
  border-radius: 15px;
  padding: 40px;
  backdrop-filter: blur(3px);
  flex: 1;
}

table {
  width: 100%;
  border-collapse: collapse;
  background: rgba(255, 255, 255, 0.08);
  border-radius: 10px;
  overflow: hidden;
  margin-bottom: 25px;
}

thead {
  background: rgba(255, 255, 255, 0.15);
}

th,
td {
  text-align: left;
  padding: 12px 18px;
  font-size: 0.95rem;
}

th {
  font-weight: 600;
  letter-spacing: 0.5px;
}

tbody tr {
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  transition: background 0.3s;
}

tbody tr:hover {
  background: rgba(255, 255, 255, 0.1);
}

.receipt-link {
  color: #007bff;
  cursor: pointer;
  text-decoration: underline;
}

#receiptModal {
  display: flex;
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.6);
  justify-content: center;
  align-items: center;
  z-index: 9999;
  padding: 20px;
}

.receipt-placeholder {
  max-width: 700px;
  max-height: 90vh;
  width: 100%;
  border-radius: 10px;
  background: #fff;
  color: #111;
  padding: 30px;
  text-align: center;
  overflow: auto;
}

.closeReceipt {
  position: absolute;
  top: 30px;
  right: 40px;
  font-size: 30px;
  color: white;
  cursor: pointer;
}

.receipt-image {
  max-width: 100%;
  max-height: 400px;
  object-fit: contain;
  margin-top: 15px;
  border-radius: 8px;
}

.receipt-file-box {
  margin-top: 20px;
}

.receipt-file-box a {
  color: #007bff;
  font-weight: 600;
  text-decoration: underline;
}

.status-badge {
  display: inline-block;
  padding: 6px 12px;
  border-radius: 999px;
  font-size: 12px;
  font-weight: 600;
  text-transform: uppercase;
}

.status-pending {
  background: #fff3cd;
  color: #856404;
}

.status-approved {
  background: #d4edda;
  color: #155724;
}

.status-denied {
  background: #f8d7da;
  color: #721c24;
}

.approveBtn,
.denyBtn {
  border: none;
  padding: 8px 14px;
  margin-right: 8px;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 600;
}

.approveBtn {
  background: #2ecc71;
  color: white;
}

.denyBtn {
  background: #e74c3c;
  color: white;
}

.processed-text {
  color: #bbb;
  font-weight: 600;
}

.empty-state {
  text-align: center;
  color: #ccc;
  padding: 30px;
}
</style>