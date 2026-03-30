<template>
  <div class="receipts-page" :style="{ backgroundImage: `url(${bgImage})` }">
    <div class="background"></div>

    <main class="main-content">
      <section class="receipts-container">
        <div v-if="showSuccessMessage" class="success-message">
          Receipt uploaded successfully.
        </div>

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
              <p><strong>File:</strong> {{ receipt.proof_file || 'N/A' }}</p>
            </div>
          </div>
        </div>

        <p v-else class="no-receipts">No receipts found.</p>
      </section>
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const showSuccessMessage = ref(false)
const receipts = ref([])

const formatRate = (value) => {
  return Number(value || 0).toLocaleString(undefined, {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  })
}

onMounted(() => {
  const savedReceipts = JSON.parse(localStorage.getItem('receipts') || '[]')

  receipts.value = savedReceipts.filter((receipt) => {
    return (
      receipt &&
      receipt.id &&
      receipt.date &&
      receipt.time &&
      receipt.vehicle &&
      Number(receipt.rate) > 0
    )
  })

  if (receipts.value.length > 0) {
    showSuccessMessage.value = true

    setTimeout(() => {
      showSuccessMessage.value = false
    }, 2500)
  }
})
</script>

<style scoped>
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
  margin-bottom: 20px;
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

.success-message {
  margin-bottom: 15px;
  padding: 12px;
  border-radius: 8px;
  background: #e8f7e8;
  color: #1f7a1f;
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
</style>