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
              <th>Rental Start</th>
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

              <!-- Rental Start -->
              <td>
                {{ formatDate(rental.rental_start) }}
              </td>

              <!-- Receipt -->
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

              <!-- Status -->
              <td class="status-cell">
                <span
                  class="status-badge"
                  :class="`status-${rental.status}`"
                >
                  {{ rental.status }}
                </span>
              </td>

              <!-- Action -->
              <td>
                <template v-if="rental.status === 'pending'">
                  <button
                    class="approveBtn"
                    @click="approveRental(rental.id)"
                  >
                    Approve
                  </button>

                  <button
                    class="denyBtn"
                    @click="denyRental(rental.id)"
                  >
                    Deny
                  </button>
                </template>

                <template v-else>
                  <span class="processed-text">
                    Processed
                  </span>
                </template>
              </td>
            </tr>

            <tr v-if="!rentals.length">
              <td colspan="7" class="empty-state">
                No rentals found.
              </td>
            </tr>
          </tbody>
        </table>
      </section>
    </main>

    <!-- Receipt Modal -->
    <div
      v-if="showReceiptModal"
      id="receiptModal"
      @click.self="closeReceipt"
    >
      <span
        class="closeReceipt"
        @click="closeReceipt"
      >
        &times;
      </span>

      <div class="receipt-placeholder">
        <h3>Receipt Preview</h3>

        <p>{{ selectedReceipt }}</p>

        <img
          v-if="selectedReceiptUrl && isImage(selectedReceiptUrl)"
          :src="selectedReceiptUrl"
          alt="Payment Receipt"
          class="receipt-image"
        />

        <div
          v-else-if="selectedReceiptUrl"
          class="receipt-file-box"
        >
          <a
            :href="selectedReceiptUrl"
            target="_blank"
            rel="noopener noreferrer"
          >
            Open Receipt File
          </a>
        </div>

        <p v-else>
          No file uploaded yet.
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue"
import axios from "axios"

const showReceiptModal = ref(false)
const selectedReceipt = ref("")
const selectedReceiptUrl = ref("")
const rentals = ref([])

const fetchRentals = async () => {
  try {
    const response = await axios.get("/admin/rentals")

    if (response.data?.success) {
      rentals.value = response.data.rentals
    } else {
      rentals.value = []
    }
  } catch (error) {
    console.error("Failed to fetch rentals:", error)
    rentals.value = []
  }
}

onMounted(() => {
  fetchRentals()
})

const openReceipt = (rental) => {
  selectedReceipt.value =
    rental.proof_file || "No receipt uploaded"

  selectedReceiptUrl.value =
    rental.proof_url || ""

  showReceiptModal.value = true
}

const closeReceipt = () => {
  selectedReceipt.value = ""
  selectedReceiptUrl.value = ""
  showReceiptModal.value = false
}

const approveRental = async (id) => {
  try {
    const response = await axios.put(
      `/admin/rentals/${id}/approve`
    )

    if (response.data?.success) {
      await fetchRentals()
    }
  } catch (error) {
    console.error("Approve failed:", error)
    alert(
      error.response?.data?.message ||
        "Failed to approve rental."
    )
  }
}

const denyRental = async (id) => {
  try {
    const response = await axios.put(
      `/admin/rentals/${id}/deny`
    )

    if (response.data?.success) {
      await fetchRentals()
    }
  } catch (error) {
    console.error("Deny failed:", error)
    alert(
      error.response?.data?.message ||
        "Failed to deny rental."
    )
  }
}

const isImage = (url) => {
  return /\.(jpg|jpeg|png|gif|webp)$/i.test(url)
}

const formatDate = (date) => {
  if (!date) return "N/A"

  return new Date(date).toLocaleString("en-PH", {
    year: "numeric",
    month: "short",
    day: "numeric",
    hour: "2-digit",
    minute: "2-digit",
  })
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
  background: #0f0f0f;
  overflow-y: auto;
  overflow-x: hidden;
}

.main-content {
  min-height: 100%;
  display: flex;
  flex-direction: column;
}

.content-section {
  margin-top: 10px;
  background: rgba(20, 20, 20, 0.9);
  border-radius: 14px;
  padding: 25px;
  backdrop-filter: blur(5px);
  border: 1px solid rgba(255,255,255,0.05);
}

/* Table */

table {
  width: 100%;
  border-collapse: collapse;
  background: rgba(255,255,255,0.03);
  border-radius: 12px;
  overflow: hidden;
}

thead {
  background: rgba(255,255,255,0.06);
}

th {
  text-align: left;
  padding: 14px 18px;
  font-size: 13px;
  font-weight: 600;
  color: #bbb;
  letter-spacing: .5px;
}

td {
  padding: 14px 18px;
  font-size: 14px;
  color: #e4e4e4;
}

tbody tr {
  border-bottom: 1px solid rgba(255,255,255,0.05);
  transition: 0.2s ease;
}

tbody tr:hover {
  background: rgba(255,255,255,0.04);
}

/* Receipt */

.receipt-link {
  color: #4da3ff;
  cursor: pointer;
  text-decoration: none;
  font-weight: 500;
}

.receipt-link:hover {
  text-decoration: underline;
}

/* Status */

.status-cell {
  width: 130px;
}

.status-badge {
  padding: 5px 14px;
  border-radius: 999px;
  font-size: 11px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: .4px;
}

.status-pending {
  background: rgba(255,193,7,.15);
  color: #ffc107;
}

.status-approved {
  background: rgba(40,167,69,.15);
  color: #28a745;
}

.status-denied {
  background: rgba(220,53,69,.15);
  color: #dc3545;
}

/* Buttons */

.approveBtn {
  background: #22c55e;
  border: none;
  padding: 7px 14px;
  border-radius: 6px;
  color: white;
  cursor: pointer;
  font-size: 13px;
  font-weight: 600;
  transition: .2s ease;
}

.approveBtn:hover {
  background: #16a34a;
}

.denyBtn {
  background: #ef4444;
  border: none;
  padding: 7px 14px;
  border-radius: 6px;
  color: white;
  cursor: pointer;
  margin-left: 8px;
  font-size: 13px;
  font-weight: 600;
  transition: .2s ease;
}

.denyBtn:hover {
  background: #dc2626;
}

.processed-text {
  color: #888;
  font-size: 13px;
  font-weight: 500;
}

/* Modal */

#receiptModal {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0,0,0,.75);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 9999;
}

.receipt-placeholder {
  background: #1a1a1a;
  padding: 30px;
  border-radius: 12px;
  max-width: 650px;
  width: 100%;
  color: white;
  box-shadow: 0 0 40px rgba(0,0,0,.6);
}

.receipt-placeholder h3 {
  margin-bottom: 15px;
}

.closeReceipt {
  position: absolute;
  top: 25px;
  right: 40px;
  color: white;
  font-size: 28px;
  cursor: pointer;
}

.receipt-image {
  max-width: 100%;
  margin-top: 20px;
  border-radius: 8px;
}

.receipt-file-box a {
  color: #4da3ff;
  text-decoration: none;
}

.receipt-file-box a:hover {
  text-decoration: underline;
}

/* Empty */

.empty-state {
  text-align: center;
  padding: 30px;
  color: #888;
  font-size: 14px;
}
</style>