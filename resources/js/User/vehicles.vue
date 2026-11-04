<template>
  <div class="vehicles-page">

    <main class="main-content">

      <div v-if="clientSubscription?.status === 'past_due'" class="subscription-notice past-due-notice" role="status">
        This rental business is in its subscription grace period. Some services may pause after {{ formatSubscriptionDate(clientSubscription.grace_ends_at) }}.
      </div>
      <div v-else-if="clientSubscription?.status === 'suspended'" class="subscription-notice suspended-notice" role="status">
        Booking is temporarily paused for this rental business. You can still view your existing bookings; contact the business for updates.
      </div>

      <section v-if="business?.pickup_address || business?.pickup_phone" class="pickup-location-card" aria-label="Rental business pickup details">
        <div class="pickup-location-mark" aria-hidden="true">⌖</div>
        <div>
          <p class="pickup-eyebrow">PICKUP LOCATION · {{ business.name }}</p>
          <strong>{{ business.pickup_address || 'Pickup address not set yet' }}</strong>
          <p v-if="business.pickup_phone">Questions? Call {{ business.pickup_phone }}</p>
          <p v-if="business.pickup_instructions" class="pickup-instructions">{{ business.pickup_instructions }}</p>
        </div>
      </section>

      <section class="vehicles-grid">

        <div
          v-for="vehicle in vehicles"
          :key="vehicle.id"
          class="vehicle-card"
          :class="{ unavailable: vehicle.status !== 'Available' }"
        >

          <img
            :src="vehicle.image"
            :class="{ blurred: vehicle.status !== 'Available' }"
            alt="Vehicle"
          />

          <h2>
            {{ vehicle.brand }} {{ vehicle.model }}
          </h2>

          <p class="price">
            ₱{{ formatPrice(vehicle.rate) }}
            <span>per day</span>
          </p>

          <button
            v-if="vehicle.status === 'Available'"
            class="view-btn"
            @click="openDetails(vehicle)"
          >
            See More
          </button>

          <button
            v-else
            class="unavailable-btn"
            disabled
          >
            Unavailable
          </button>

        </div>

      </section>

    </main>

    <!-- DETAILS MODAL -->
    <div
      v-if="showDetailsModal"
      class="car-details-popup"
    >
      <div class="car-details-container">

        <span
          class="close-btn"
          @click="closeDetails"
        >
          &times;
        </span>

        <img
          :src="selectedVehicle.image"
          alt="Vehicle"
        />

        <h2>
          {{ selectedVehicle.brand }}
          {{ selectedVehicle.model }}
        </h2>

        <p class="price">
          ₱{{ formatPrice(selectedVehicle.rate) }}
          <span>per day</span>
        </p>

        <div class="car-info-box">
          <h3>Car Details</h3>

          <ul>
            <li>
              <strong>Brand:</strong>
              {{ selectedVehicle.brand }}
            </li>

            <li>
              <strong>Model:</strong>
              {{ selectedVehicle.model }}
            </li>

            <li>
              <strong>Status:</strong>
              {{ selectedVehicle.status }}
            </li>
          </ul>

        </div>

        <button
          id="bookNowBtn"
          :disabled="clientSubscription?.status === 'suspended'"
          @click="openBooking"
        >
          {{ clientSubscription?.status === 'suspended' ? 'Bookings paused' : 'Book Now' }}
        </button>

      </div>
    </div>

    <!-- BOOKING MODAL -->
    <div
      v-if="showBookingModal"
      class="booking-modal"
    >
      <div class="booking-modal-content">

        <span
          class="close-btn"
          @click="closeBooking"
        >
          &times;
        </span>

        <h4>Book Vehicle</h4>

        <div class="booking-layout">
          <div class="booking-calendar-panel">
            <label>Choose a pickup date</label>
            <AvailabilityCalendar
              v-model="booking.date"
              :vehicle-id="selectedVehicle.id"
              mode="user"
              :vehicle-status="selectedVehicle.status"
              :duration-days="booking.days"
              @availability-change="booking.dateValid = $event"
            />
          </div>

          <div class="booking-fields">
            <label for="rentalDays">Rental duration</label>
            <select id="rentalDays" v-model.number="booking.days">
              <option v-for="day in 30" :key="day" :value="day">{{ day }} {{ day === 1 ? 'day' : 'days' }}</option>
            </select>
            <div v-if="booking.date" class="booking-range-summary">
              <span>Pickup <strong>{{ formatBookingDate(booking.date) }}</strong></span>
              <span>Return <strong>{{ formatBookingDate(returnDate) }}</strong></span>
              <span>Estimated rental total <strong>₱{{ formatPrice(Number(selectedVehicle.rate || 0) * booking.days) }}</strong></span>
            </div>
            <div v-if="business?.pickup_address" class="booking-pickup-details">
              <strong>Pickup at {{ business.name }}</strong>
              <span>{{ business.pickup_address }}</span>
              <span v-if="business.pickup_phone">{{ business.pickup_phone }}</span>
              <span v-if="business.pickup_instructions">{{ business.pickup_instructions }}</span>
            </div>
            <label for="pickupTime">Pickup time</label>
            <input id="pickupTime" v-model="booking.time" type="time" />

            <label for="discountType">Discount (optional)</label>
            <select id="discountType" v-model="booking.discountType">
              <option value="">No discount</option>
              <option value="senior">Senior citizen</option>
              <option value="pwd">Person with disability (PWD)</option>
            </select>
            <p v-if="booking.discountType" class="discount-note">
              The admin will check your valid ID at pickup. The discount is applied after verification.
            </p>

            <label for="proofFile">Upload payment proof</label>
            <input id="proofFile" type="file" @change="handleProofFile" />

            <button
              class="confirm-btn"
              :disabled="submitting || !booking.date || !booking.dateValid"
              @click="confirmBooking"
            >
              {{ submitting ? 'Submitting...' : 'Confirm Booking' }}
            </button>
          </div>
        </div>

      </div>
    </div>

    <!-- PROCESSING -->
    <div
      v-if="showProcessingModal"
      class="processing-modal"
    >
      <div class="processing-content">

        <template v-if="!processingDone">
          <h3>Processing your request...</h3>
          <p>Please wait</p>
        </template>

        <template v-else>
          <h3>Submitted Successfully</h3>
          <p>Waiting for admin approval</p>

          <button @click="closeProcessing">
            OK
          </button>
        </template>

      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/api'
import defaultCarImg from '@/assets/img/logocar.png.png'
import AvailabilityCalendar from '@/components/AvailabilityCalendar.vue'

const apiOrigin = import.meta.env.VITE_API_ORIGIN || window.location.origin

const router = useRouter()

const vehicles = ref([])
const clientSubscription = ref(null)
const business = ref(null)
const loading = ref(false)
const submitting = ref(false)

const showDetailsModal = ref(false)
const showBookingModal = ref(false)
const showProcessingModal = ref(false)
const processingDone = ref(false)

const selectedVehicle = reactive({
  id: '',
  brand: '',
  model: '',
  rate: 0,
  status: '',
  image: '',
})

const booking = reactive({
  date: '',
  days: 1,
  dateValid: false,
  time: '',
  proofFile: null,
  discountType: '',
})

const returnDate = computed(() => booking.date ? shiftIsoDate(booking.date, booking.days) : '')

function shiftIsoDate(iso, days) {
  const [year, month, day] = iso.split('-').map(Number)
  const date = new Date(year, month - 1, day)
  date.setDate(date.getDate() + Number(days))
  return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`
}

const formatBookingDate = (iso) => {
  if (!iso) return '—'
  const [year, month, day] = iso.split('-').map(Number)
  return new Date(year, month - 1, day).toLocaleDateString(undefined, { dateStyle: 'medium' })
}

/* ================= PRICE FORMAT ================= */
const formatPrice = (value) => {
  return Number(value || 0).toLocaleString()
}

const formatSubscriptionDate = (value) => value
  ? new Intl.DateTimeFormat(undefined, { dateStyle: 'medium' }).format(new Date(value))
  : 'the end of the grace period'

/* ================= FETCH VEHICLES ================= */
const fetchVehicles = async () => {
  loading.value = true

  try {
    const response = await api.get('/user/vehicles')
    clientSubscription.value = response.data.client_subscription || null
    business.value = response.data.client || null

    vehicles.value = (response.data.vehicles || []).map(v => ({
      ...v,
      image: v.image_url
        ? v.image_url
        : v.image
        ? new URL(`/uploads/vehicles/${v.image}`, apiOrigin).toString()
        : defaultCarImg
    }))
  } catch (err) {
    console.error(err)

    if (err.response?.status === 401) {
      alert('Session expired. Please login again.')
      sessionStorage.clear()
      router.push('/user')
    }
  } finally {
    loading.value = false
  }
}

/* ================= LIFECYCLE ================= */
onMounted(() => {
  fetchVehicles()
})

/* ================= MODALS ================= */
const openDetails = (v) => {
  Object.assign(selectedVehicle, v)
  showDetailsModal.value = true
}

const closeDetails = () => {
  showDetailsModal.value = false
}

const openBooking = () => {
  booking.date = ''
  booking.time = ''
  booking.days = 1
  booking.dateValid = false
  booking.proofFile = null
  booking.discountType = ''
  showBookingModal.value = true
  showDetailsModal.value = false
}

const closeBooking = () => {
  showBookingModal.value = false
}

const handleProofFile = (e) => {
  booking.proofFile = e.target.files[0]
}

/* ================= SESSION CHECK ================= */
const getUser = () => {
  return JSON.parse(sessionStorage.getItem('user'))
}

/* ================= BOOKING ================= */
const confirmBooking = async () => {
  const user = getUser()

  if (!user) {
    alert('Please login first')
    router.push('/user')
    return
  }

  if (!booking.date || !booking.dateValid || !booking.time || !booking.proofFile) {
    alert('Choose an available pickup date and fill in the remaining booking details.')
    return
  }

  try {
    submitting.value = true

    showBookingModal.value = false
    showProcessingModal.value = true
    processingDone.value = false

    const formData = new FormData()

    formData.append('vehicle_id', selectedVehicle.id)
    formData.append(
      'rental_start',
      `${booking.date} ${booking.time}:00`
    )
    formData.append('rental_end', `${returnDate.value} ${booking.time}:00`)
    formData.append('proof_file', booking.proofFile)
    if (booking.discountType) {
      formData.append('discount_type', booking.discountType)
    }

    const token = sessionStorage.getItem('token')

    const res = await api.post(
      '/user/rentals',
      formData,
      {
        headers: {
          Authorization: `Bearer ${token}`,
          'Content-Type': 'multipart/form-data'
        }
      }
    )

    if (res.data?.success) {
      processingDone.value = true
    } else {
      throw new Error('Booking failed')
    }
  } catch (err) {
    console.error(err.response?.data || err)
    const validationMessage = Object.values(err.response?.data?.errors || {})[0]?.[0]
    alert(validationMessage || err.response?.data?.message || 'Booking failed')
    showProcessingModal.value = false
  } finally {
    submitting.value = false
  }
}

const closeProcessing = () => {
  showProcessingModal.value = false
  router.push('/user/receipts')
}
</script>

<style scoped>
.vehicles-page {
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
  display: flex;
  flex-direction: column;
  padding: 40px;
  position: relative;
  overflow-y: auto;
  min-height: 100vh;
  z-index: 1;
}

.subscription-notice { margin: 0 0 16px; padding: 13px 16px; border: 1px solid var(--line-subtle); border-radius: 12px; font-size: .82rem; line-height: 1.5; }
.past-due-notice { border-color: var(--warning); color: var(--warning); background: var(--warning-soft); }
.suspended-notice { border-color: var(--danger); color: var(--danger); background: var(--danger-soft); }
.pickup-location-card { display:flex; align-items:flex-start; gap:14px; margin:0 0 20px; padding:17px 20px; border:1px solid var(--blue-border); border-radius:16px; color:var(--text-primary); background:var(--blue-soft); }
.pickup-location-mark { display:grid; width:38px; height:38px; flex:0 0 38px; place-items:center; border-radius:12px; color:#fff; background:var(--blue); font-size:1.35rem; }
.pickup-eyebrow { margin:0 0 4px; color:var(--blue-ink); font-size:.68rem; font-weight:800; letter-spacing:.1em; }
.pickup-location-card strong { display:block; font-size:.95rem; }
.pickup-location-card p:not(.pickup-eyebrow) { margin:4px 0 0; color:var(--text-secondary); font-size:.82rem; line-height:1.5; }
.booking-range-summary,.booking-pickup-details { display:grid; gap:8px; margin:0 0 16px; padding:13px; border:1px solid var(--line-subtle); border-radius:11px; background:var(--surface-raised); color:var(--text-secondary); font-size:.82rem; }
.booking-range-summary span { display:flex; justify-content:space-between; gap:10px; }
.booking-range-summary strong,.booking-pickup-details strong { color:var(--text-primary); }
.booking-pickup-details span { color:var(--text-secondary); line-height:1.45; }

.vehicles-grid {
  margin-top: 40px;
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
  gap: 30px;
  justify-items: center;
}

.vehicle-card {
  background: rgba(77, 77, 77, 0.4);
  border-radius: 20px;
  backdrop-filter: blur(3px);
  text-align: center;
  padding: 20px;
  width: 270px;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  position: relative;
}

.vehicle-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 0 15px rgba(255, 215, 0, 0.3);
}

.vehicle-card img {
  width: 100%;
  height: 180px;
  object-fit: cover;
  border-radius: 12px;
  margin-bottom: 10px;
}

.vehicle-card.unavailable img.blurred {
  filter: blur(6px) brightness(0.6);
}

.vehicle-card h2 {
  font-weight: 600;
  font-size: 1.1rem;
  margin: 10px 0 5px;
  color: #fff;
}

.price {
  color: #a892ff;
  font-weight: 700;
  font-size: 1rem;
}

.price span {
  font-weight: 400;
  font-size: 0.8rem;
  color: #ccc;
}

.view-btn,
.unavailable-btn {
  margin-top: 15px;
  width: 100%;
  padding: 10px 0;
  border: none;
  border-radius: 8px;
  font-weight: 700;
  cursor: pointer;
  transition: 0.3s ease;
}

.view-btn {
  background: #ffd700;
  color: #000;
}

.view-btn:hover {
  opacity: 0.9;
}

.unavailable-btn {
  background: #fff;
  color: red;
  cursor: not-allowed;
}

.car-details-popup,
.booking-modal,
.processing-modal {
  display: flex;
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.65);
  justify-content: center;
  align-items: center;
  z-index: 9999;
  padding: 20px;
}

.car-details-container,
.booking-modal-content,
.processing-content {
  background: #1b1b1b;
  color: #fff;
  padding: 18px;
  border-radius: 16px;
  width: 90%;
  max-width: 340px;
  position: relative;
  z-index: 10000;
  box-shadow: 0 0 30px rgba(0, 0, 0, 0.45);
  pointer-events: auto;
  box-sizing: border-box;
  text-align: center;
}

.car-details-container img {
  width: 100%;
  height: 150px;
  object-fit: cover;
  border-radius: 10px;
  margin-bottom: 12px;
}

.close-btn {
  position: absolute;
  top: 12px;
  right: 16px;
  font-size: 28px;
  cursor: pointer;
  color: #fff;
  z-index: 10001;
}

.booking-modal-content label {
  display: block;
  margin-top: 10px;
  font-weight: 600;
  text-align: left;
}

.booking-modal-content input {
  width: 100%;
  padding: 8px;
  margin-top: 5px;
  border-radius: 8px;
  border: none;
  outline: none;
}

.booking-modal-content select {
  width: 100%;
  padding: 10px;
  margin-top: 5px;
  border: 1px solid rgba(255, 255, 255, 0.14);
  border-radius: 8px;
  background: #292929;
  color: #fff;
}

.discount-note {
  margin: 7px 0 2px;
  color: #d3d3d3;
  font-size: 0.82rem;
  line-height: 1.45;
  text-align: left;
}

#bookNowBtn,
.confirm-btn,
.ok-btn {
  background: #ffd700;
  color: #000;
  border: none;
  padding: 10px 16px;
  border-radius: 8px;
  cursor: pointer;
  font-weight: 700;
  margin-top: 15px;
  width: 100%;
}

#bookNowBtn:hover,
.confirm-btn:hover,
.ok-btn:hover {
  opacity: 0.9;
}

.confirm-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

#bookNowBtn:disabled { opacity: .55; cursor: not-allowed; }

.booking-modal-content {
  width: min(1060px, calc(100vw - 48px));
  max-width: none;
  max-height: calc(100vh - 48px);
  overflow-y: auto;
  padding: 26px;
  background: var(--surface-card, #142238);
  color: var(--text-primary, #e8f0fb);
  text-align: left;
  border: 1px solid var(--line-subtle, #2b405d);
  box-shadow: var(--shadow-popover, 0 24px 64px rgba(0, 0, 0, .42));
}

.booking-modal {
  align-items: flex-start;
  overflow-y: auto;
  padding: calc(var(--app-header-height, 76px) + 36px) 20px 20px;
}

.booking-modal-content {
  max-height: calc(100vh - var(--app-header-height, 76px) - 56px);
  margin: 0 auto;
}

.booking-modal-content h4 {
  margin: 0 40px 18px 0;
  color: var(--text-primary, #e8f0fb);
  font-size: 1.35rem;
}

.booking-layout {
  display: grid;
  grid-template-columns: minmax(0, 1.15fr) minmax(280px, .85fr);
  align-items: start;
  gap: 24px;
}

.booking-calendar-panel {
  min-width: 0;
  padding: 18px;
  border: 1px solid var(--line-subtle, #2b405d);
  border-radius: 16px;
  background: var(--surface-raised, #182a42);
}

.booking-calendar-panel > label {
  margin: 0 0 12px;
  font-size: 1rem;
}

.booking-fields { min-width: 0; }

.booking-fields input,
.booking-fields select {
  box-sizing: border-box;
  min-height: 48px;
  padding: 10px 12px;
  border: 1px solid var(--line-subtle, #2b405d);
  border-radius: 10px;
  background: var(--surface-muted, #1a2b43);
  color: var(--text-primary, #e8f0fb);
}

.booking-fields input[type="file"] { padding: 8px; }

.booking-fields .confirm-btn {
  margin-top: 20px;
  padding: 13px 16px;
  border-radius: 11px;
  background: var(--blue, #5b98ff);
  color: #fff;
}

.booking-fields .confirm-btn:hover { background: var(--blue-hover, #79adff); }

.discount-note { color: var(--text-secondary, #b0c1d6); }

@media (max-width: 760px) {
  .booking-modal-content {
    width: min(100%, 560px);
    max-height: calc(100vh - var(--app-header-height, 76px) - 48px);
    padding: 20px;
  }
  .booking-modal {
    padding: calc(var(--app-header-height, 76px) + 36px) 12px 12px;
  }
  .booking-layout { grid-template-columns: minmax(0, 1fr); gap: 12px; }
  .booking-calendar-panel { padding: 13px; }
  :deep(.calendar-day) { min-height: 40px; }
}
</style>
