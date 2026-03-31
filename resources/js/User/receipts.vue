<template>
  <div class="receipts-page">
    <div class="background"></div>

    <main class="main-content">
      <section class="receipts-container">

        <!-- Receipts list -->
        <div v-if="receipts.length > 0" id="receiptsList">
          <div
            v-for="receipt in receipts"
            :key="receipt.id"
            class="receipt-card"
          >
            <h3>Receipt No: R-{{ String(receipt.id).padStart(5, '0') }}</h3>

            <div class="receipt-details">
              <h4>Booking Information</h4>
              <p><strong>Date:</strong> {{ receipt.date }}</p>
              <p><strong>Time:</strong> {{ receipt.time }}</p>
              <p>
                <strong>Status:</strong>
                <span
                  class="status-badge"
                  :class="`status-${receipt.status}`"
                >
                  {{ receipt.status }}
                </span>
              </p>

              <hr />

              <h4>Vehicle Details</h4>
              <p><strong>Car:</strong> {{ receipt.vehicle }}</p>
              <p><strong>Rate:</strong> ₱{{ formatRate(receipt.rate) }} / day</p>

              <hr />

              <h4>Payment Receipt</h4>
              <div>
                <span v-if="receipt.proof_file">
                  <!-- Always show as link -->
                  <a
                    href="#"
                    @click.prevent="openImageModal(receipt.proof_url)"
                  >
                    {{ receipt.proof_file }}
                  </a>
                </span>
                <span v-else>N/A</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Empty state -->
        <p v-else class="no-receipts">No receipts found.</p>
      </section>
    </main>

    <!-- Modal for viewing full image -->
    <div v-if="showImageModal" class="image-modal" @click.self="closeImageModal">
      <div class="image-modal-content">
        <span class="close-btn" @click="closeImageModal">&times;</span>
        <img :src="modalImageUrl" alt="Receipt Image" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const showSuccessMessage = ref(false)
const receipts = ref([])

// Modal state
const showImageModal = ref(false)
const modalImageUrl = ref('')

// Format rate with 2 decimal places
const formatRate = (value) => {
  return Number(value || 0).toLocaleString(undefined, {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  })
}

// Open image modal
const openImageModal = (url) => {
  modalImageUrl.value = url
  showImageModal.value = true
}

// Close image modal
const closeImageModal = () => {
  modalImageUrl.value = ''
  showImageModal.value = false
}

// Fetch receipts from backend
const fetchReceipts = async () => {
  try {
    const response = await axios.get("/admin/rentals") // Use your correct endpoint
    if (response.data?.success) {
      receipts.value = response.data.rentals.map(rental => {
        const dateObj = new Date(rental.rental_start)
        return {
          id: rental.id,
          vehicle: rental.vehicle,
          rate: rental.rate,
          status: rental.status,
          proof_file: rental.proof_file,
          proof_url: rental.proof_url,
          date: dateObj.toLocaleDateString(),
          time: dateObj.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
        }
      })

      if (receipts.value.length > 0) {
        showSuccessMessage.value = true
        setTimeout(() => (showSuccessMessage.value = false), 2500)
      }
    } else {
      receipts.value = []
    }
  } catch (error) {
    console.error("Failed to fetch receipts:", error)
    receipts.value = []
  }
}

onMounted(() => {
  fetchReceipts()
})
</script>

<style scoped>
/* Keep your existing styles unchanged */
.receipts-page {
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

.background {
  background: inherit;
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  z-index: 0;
  opacity: 0.3;
  filter: brightness(60%);
}

.main-content {
  flex: 1;
  display: flex;
  flex-direction: column;
  padding: 40px;
  position: relative;
  overflow-y: auto;
  min-height: 100vh;
  z-index: 1;
}

.receipts-container {
  margin-top: 0;
  display: flex;
  flex-direction: column;
  gap: 25px;
}

.receipt-card {
  background: rgba(255, 255, 255, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.2);
  padding: 25px;
  border-radius: 15px;
  backdrop-filter: blur(6px);
  box-shadow: 0 0 12px rgba(0, 0, 0, 0.5);
  transition: 0.3s;
}

.receipt-card:hover {
  transform: scale(1.015);
  background: rgba(255, 255, 255, 0.15);
}

.receipt-card h3 {
  margin-bottom: 15px;
  font-size: 1.3rem;
  color: #ffd700;
}

.receipt-details h4 {
  margin: 10px 0 8px;
  font-size: 1rem;
  color: #fff;
}

.receipt-details p {
  margin: 6px 0;
  font-size: 0.95rem;
}

.receipt-details hr {
  border: none;
  border-top: 1px solid rgba(255, 255, 255, 0.2);
  margin: 14px 0;
}

.no-receipts {
  font-size: 1.1rem;
  text-align: center;
  opacity: 0.7;
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

@media (max-width: 768px) {
  .receipt-card {
    padding: 20px;
  }

  .receipt-card h3 {
    font-size: 1.1rem;
  }

  .main-content {
    padding: 20px;
  }
}

/* Modal styles */
/* Modal styles */
.image-modal {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.7);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 9999;
}

.image-modal-content {
  position: relative;
  max-width: 500px;   /* max width of modal */
  max-height: 400px;  /* max height of modal */
  width: 90%;
  height: auto;
}

.image-modal-content img {
  width: 100%;
  height: auto;
  border-radius: 12px;
}

.image-modal-content .close-btn {
  position: absolute;
  top: -10px;
  right: -10px;
  font-size: 28px;
  font-weight: bold;
  color: #fff;
  cursor: pointer;
  background: rgba(0,0,0,0.6);
  border-radius: 50%;
  padding: 0 10px;
  line-height: 1;
}
</style>  