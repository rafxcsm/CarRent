<template>
  <div class="receipts-page">
    <div class="background"></div>

    <main class="main-content">
      <section class="receipts-container">

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
              <p><strong>Rental length:</strong> {{ receipt.days }} {{ receipt.days === 1 ? 'day' : 'days' }}</p>
              <p><strong>Return:</strong> {{ receipt.returnDate }} · {{ receipt.returnTime }}</p>

              <p>
                <strong>Status:</strong>
                <span
                  class="status-badge"
                  :class="`status-${receipt.status}`"
                >
                  {{ receipt.status }}
                </span>
              </p>

              <p v-if="receipt.discountType">
                <strong>Discount:</strong>
                {{ discountLabel(receipt.discountType) }}
                <span class="discount-state">({{ receipt.discountStatus }})</span>
              </p>
              <p v-if="receipt.discountType && receipt.discountStatus === 'verified'">
                <strong>Daily rate after discount:</strong>
                ₱{{ formatRate(receipt.rate - receipt.discountAmount) }} / day
                <span class="discount-detail">
                  ({{ receipt.discountPercent }}% off; ₱{{ formatRate(receipt.discountAmount) }} per day)
                </span>
              </p>
              <p v-else-if="receipt.discountType && receipt.discountStatus === 'pending'" class="discount-detail">
                Estimated reduction: ₱{{ formatRate(receipt.estimatedDiscountPerDay) }} per day. Show your valid ID at pickup; the discount is applied after admin verification.
              </p>
              <p v-else-if="receipt.discountType && receipt.discountStatus === 'rejected'" class="discount-detail">
                The discount request was not approved; the regular daily rate applies.
              </p>

              <hr />

              <h4>Pickup Location</h4>
              <p><strong>Address:</strong> {{ receipt.pickupAddress || 'Contact the rental business for pickup details.' }}</p>
              <p v-if="receipt.pickupPhone"><strong>Phone:</strong> {{ receipt.pickupPhone }}</p>
              <p v-if="receipt.pickupInstructions">{{ receipt.pickupInstructions }}</p>

              <hr />

              <h4>Vehicle Details</h4>
              <p><strong>Car:</strong> {{ receipt.vehicle }}</p>
              <p><strong>Rate:</strong> ₱{{ formatRate(receipt.rate) }} / day</p>
              <p><strong>{{ receipt.discountStatus === 'verified' ? 'Total after verified discount:' : 'Estimated rental total:' }}</strong> ₱{{ formatRate(receipt.finalAmount) }}</p>
              <p v-if="receipt.discountType && receipt.discountStatus === 'pending'" class="discount-detail">Total may change after the rental admin checks your ID.</p>

              <button
                v-if="receipt.qrPayload && !receipt.returnedAt"
                type="button"
                class="qr-button"
                @click="openQr(receipt)"
              >
                {{ receipt.qrVerifiedAt ? 'Show return QR' : 'Show pickup QR' }}
              </button>
              <p v-if="receipt.qrVerifiedAt" class="qr-verified">
                Pickup verified by admin: {{ formatTimestamp(receipt.qrVerifiedAt) }}
              </p>
              <p v-if="receipt.returnedAt" class="qr-verified">
                Vehicle return recorded: {{ formatTimestamp(receipt.returnedAt) }}
              </p>

              <hr />

              <h4>Payment Receipt</h4>
              <div>
                <span v-if="receipt.proof_file">
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

        <p v-else class="no-receipts">No receipts found.</p>

      </section>
    </main>

    <!-- IMAGE MODAL -->
    <div
      v-if="showImageModal"
      class="image-modal"
      @click.self="closeImageModal"
    >
      <div class="image-modal-content">
        <span class="close-btn" @click="closeImageModal">&times;</span>
        <img :src="modalImageUrl" alt="Receipt Image" />
      </div>
    </div>

    <div v-if="showQrModal" class="qr-modal" @click.self="closeQr">
      <section class="qr-modal-content" role="dialog" aria-modal="true" aria-labelledby="qrTitle">
        <button type="button" class="close-btn" aria-label="Close QR code" @click="closeQr">&times;</button>
        <h3 id="qrTitle">{{ selectedQrReceipt?.qrVerifiedAt ? 'Return verification' : 'Pickup verification' }}</h3>
        <p>{{ selectedQrReceipt?.qrVerifiedAt
          ? 'Show this code to the admin when you return the vehicle.'
          : 'Show this code to the admin when you collect the approved vehicle.' }}</p>
        <img v-if="qrImageUrl" :src="qrImageUrl" alt="Booking verification QR code" class="booking-qr" />
        <p v-else-if="qrError" class="qr-error">{{ qrError }}</p>
        <p v-else>Preparing your QR code…</p>
        <p v-if="selectedQrReceipt" class="qr-booking-number">
          Booking R-{{ String(selectedQrReceipt.id).padStart(5, '0') }}
        </p>
      </section>
    </div>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router'
import { ref, onMounted, onBeforeUnmount } from 'vue'
import api from '@/api'
import QRCode from 'qrcode'

const router = useRouter()
const receipts = ref([])
const showImageModal = ref(false)
const modalImageUrl = ref('')
const showQrModal = ref(false)
const selectedQrReceipt = ref(null)
const qrImageUrl = ref('')
const qrError = ref('')
let receiptsPollTimer = null

const formatRate = (value) => {
  return Number(value || 0).toLocaleString(undefined, {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  })
}

const formatTimestamp = (value) => new Date(value).toLocaleString()

const openImageModal = (url) => {
  modalImageUrl.value = url
  showImageModal.value = true
}

const closeImageModal = () => {
  modalImageUrl.value = ''
  showImageModal.value = false
}

const discountLabel = (type) => ({
  senior: 'Senior citizen',
  pwd: 'PWD',
}[type] || 'Discount')

const openQr = async (receipt) => {
  selectedQrReceipt.value = receipt
  qrImageUrl.value = ''
  qrError.value = ''
  showQrModal.value = true

  try {
    qrImageUrl.value = await QRCode.toDataURL(receipt.qrPayload, {
      width: 260,
      margin: 2,
      errorCorrectionLevel: 'M',
    })
  } catch (error) {
    qrError.value = 'Could not create the pickup QR. Please refresh your bookings and try again.'
  }
}

const closeQr = () => {
  showQrModal.value = false
  selectedQrReceipt.value = null
  qrImageUrl.value = ''
  qrError.value = ''
}

const fetchReceipts = async () => {
  try {
    const user = JSON.parse(sessionStorage.getItem('user'))
    const token = sessionStorage.getItem('token')

    if (!user || !token) {
      receipts.value = []
      return
    }

    const response = await api.get('/user/rentals', {
      headers: {
        Authorization: `Bearer ${token}`
      }
    })

    if (response.data?.success) {
      receipts.value = response.data.rentals.map((rental) => {
        const dateObj = rental.rental_start
          ? new Date(rental.rental_start)
          : null
        const endObj = rental.rental_end ? new Date(rental.rental_end) : null

        return {
          id: rental.id,
          vehicle: rental.vehicle,
          rate: rental.rate,
          days: Number(rental.days || 1),
          totalAmount: Number(rental.total_amount || 0),
          finalAmount: Number(rental.final_amount ?? rental.total_amount ?? 0),
          pickupAddress: rental.pickup_address,
          pickupPhone: rental.pickup_phone,
          pickupInstructions: rental.pickup_instructions,
          status: rental.status,
          discountType: rental.discount_type,
          discountStatus: rental.discount_status,
          discountPercent: rental.discount_percent,
          discountAmount: Number(rental.discount_amount || 0),
          estimatedDiscountPerDay: Number(rental.estimated_discount_per_day || 0),
          qrPayload: rental.qr_payload,
          qrVerifiedAt: rental.qr_verified_at,
          returnedAt: rental.returned_at,
          proof_file: rental.proof_file,
          proof_url: rental.proof_url,
          date: dateObj ? dateObj.toLocaleDateString() : 'N/A',
          time: dateObj
            ? dateObj.toLocaleTimeString([], {
                hour: '2-digit',
                minute: '2-digit',
              })
            : 'N/A',
          returnDate: endObj ? endObj.toLocaleDateString() : 'N/A',
          returnTime: endObj
            ? endObj.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
            : 'N/A',
        }
      })
    } else {
      receipts.value = []
    }
  } catch (error) {
    console.error('Failed to fetch receipts:', error.response?.data || error)

    if (error.response?.status === 401) {
      sessionStorage.clear()
      alert('Session expired. Please login again.')
      router.push('/user')
    }

    receipts.value = []
  }
}

onMounted(() => {
  fetchReceipts()
  receiptsPollTimer = window.setInterval(() => {
    if (document.visibilityState === 'visible') fetchReceipts()
  }, 15000)
})

onBeforeUnmount(() => {
  window.clearInterval(receiptsPollTimer)
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
  display: flex;
  flex-direction: column;
  gap: 200px;
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
}

.receipt-card h3 {
  margin-bottom: 15px;
  font-size: 1.3rem;
  color: #ffd700;
}

.receipt-details {
  color: #fff;
}

.receipt-details h4 {
  margin: 10px 0 8px;
}

.receipt-details p {
  margin: 6px 0;
}

.receipt-details hr {
  border: none;
  border-top: 1px solid rgba(255,255,255,0.2);
  margin: 14px 0;
}

.no-receipts {
  text-align: center;
  opacity: 0.7;
  color: #fff;
}

.discount-state {
  margin-left: 4px;
  color: #ddd;
  text-transform: capitalize;
}

.discount-detail {
  color: #d8d8d8;
  font-size: 0.9rem;
}

.qr-button {
  display: inline-flex;
  margin-top: 12px;
  padding: 10px 14px;
  border: 0;
  border-radius: 8px;
  background: #ffd700;
  color: #171717;
  font-weight: 700;
  cursor: pointer;
}

.qr-button:hover {
  background: #ffe34d;
}

.qr-verified {
  margin-top: 12px;
  color: #86efac;
  font-weight: 600;
}

.status-badge {
  display: inline-block;
  padding: 6px 12px;
  border-radius: 999px;
  font-size: 12px;
  font-weight: 600;
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
  max-width: 500px;
  width: 90%;
}

.image-modal-content img {
  width: 100%;
  border-radius: 12px;
}

.close-btn {
  position: absolute;
  top: -10px;
  right: -10px;
  font-size: 28px;
  color: #fff;
  cursor: pointer;
}

.qr-modal {
  position: fixed;
  inset: 0;
  z-index: 10000;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
  background: rgba(0, 0, 0, 0.78);
}

.qr-modal-content {
  position: relative;
  width: min(100%, 390px);
  padding: 28px 22px 22px;
  border: 1px solid rgba(255, 215, 0, 0.22);
  border-radius: 16px;
  background: #1a1a1a;
  color: #fff;
  text-align: center;
  box-shadow: 0 18px 48px rgba(0, 0, 0, 0.55);
}

.qr-modal-content h3 {
  margin: 0 30px 8px;
  color: #ffd700;
}

.qr-modal-content p {
  line-height: 1.5;
}

.booking-qr {
  display: block;
  width: 260px;
  max-width: 100%;
  margin: 18px auto;
  padding: 10px;
  border-radius: 12px;
  background: #fff;
}

.qr-booking-number {
  color: #c9c9c9;
  font-size: 0.9rem;
}

.qr-error {
  color: #ffb3b3;
}
</style>
