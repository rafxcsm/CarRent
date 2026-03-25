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
                  @click="openReceipt(rental.proof_file)"
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
                  <span class="processed-text">Processing</span>
                </template>
              </td>
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
        <p>No image uploaded yet.</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const showReceiptModal = ref(false)
const selectedReceipt = ref('')

const rentals = ref([
  {
    id: 1,
    customer: 'Juan Dela Cruz',
    vehicle: 'Toyota, Vios, White',
    proof_file: 'receipt-juan.png',
    status: 'pending',
  },
  {
    id: 2,
    customer: 'Maria Santos',
    vehicle: 'Honda, City, Red',
    proof_file: 'receipt-maria.png',
    status: 'approved',
  },
  {
    id: 3,
    customer: 'Mark Reyes',
    vehicle: 'Mitsubishi, Montero, Black',
    proof_file: 'receipt-mark.png',
    status: 'denied',
  },
  {
    id: 4,
    customer: 'Angela Lopez',
    vehicle: 'Suzuki, Ertiga, Silver',
    proof_file: '',
    status: 'pending',
  },
])

const openReceipt = (fileName) => {
  selectedReceipt.value = fileName
  showReceiptModal.value = true
}

const closeReceipt = () => {
  selectedReceipt.value = ''
  showReceiptModal.value = false
}

const approveRental = (id) => {
  const rental = rentals.value.find((item) => item.id === id)
  if (rental) rental.status = 'approved'
}

const denyRental = (id) => {
  const rental = rentals.value.find((item) => item.id === id)
  if (rental) rental.status = 'denied'
}
</script>

<style scoped>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
  font-family: "Poppins", sans-serif;
}

.rent-page {
  min-height: calc(100vh - 80px);
  background: #0e0e0e;
  color: white;
  margin-left: 308px;
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
}

.receipt-placeholder {
  max-width: 500px;
  max-height: 500px;
  border-radius: 10px;
  background: #fff;
  color: #111;
  padding: 30px;
  text-align: center;
}

.closeReceipt {
  position: absolute;
  top: 30px;
  right: 40px;
  font-size: 30px;
  color: white;
  cursor: pointer;
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
  color: #777;
  font-weight: 600;
}
</style>