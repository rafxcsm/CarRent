<template>
  <div class="vehicles-page" :style="{ backgroundImage: `url(${bgImage})` }">
    <div class="background"></div>

    <main class="main-content">
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

          <h2>{{ vehicle.brand }} {{ vehicle.model }}</h2>

          <p class="price">
            ₱{{ formatPrice(vehicle.rate) }} <span>per day</span>
          </p>

          <button
            v-if="vehicle.status === 'Available'"
            class="view-btn"
            type="button"
            @click="openDetails(vehicle)"
          >
            See More
          </button>

          <button
            v-else
            class="unavailable-btn"
            type="button"
            disabled
          >
            Unavailable
          </button>
        </div>
      </section>
    </main>

    <div v-if="showDetailsModal" class="car-details-popup">
      <div class="car-details-container">
        <span class="close-btn" @click="closeDetails">&times;</span>

        <img :src="selectedVehicle.image" id="carImage" alt="Vehicle Image" />
        <h2 id="carName">
          {{ selectedVehicle.brand }} {{ selectedVehicle.model }}
        </h2>
        <p class="price" id="carPrice">
          ₱{{ formatPrice(selectedVehicle.rate) }} <span>per day</span>
        </p>

        <div class="car-info-box">
          <h3>Car Details</h3>
          <ul id="carFeatures">
            <li><strong>Brand:</strong> {{ selectedVehicle.brand }}</li>
            <li><strong>Model:</strong> {{ selectedVehicle.model }}</li>
            <li><strong>Seats:</strong> {{ selectedVehicle.seats || 'N/A' }}</li>
            <li><strong>Transmission:</strong> {{ selectedVehicle.transmission || 'N/A' }}</li>
            <li><strong>Fuel:</strong> {{ selectedVehicle.fuel || 'N/A' }}</li>
            <li><strong>Status:</strong> {{ selectedVehicle.status }}</li>
          </ul>
        </div>

        <div class="action-buttons">
          <button id="bookNowBtn" type="button" @click="openBooking">
            Book Now
          </button>
        </div>
      </div>
    </div>

    <div v-if="showBookingModal" class="booking-modal">
      <div class="booking-modal-content">
        <span class="close-btn" @click="closeBooking">&times;</span>

        <div class="booking-fields">
          <h4>Book Vehicle</h4>

          <label for="bookDate">Select Date</label>
          <input v-model="booking.date" id="bookDate" type="date" required />

          <label for="bookTime">Select Time</label>
          <input v-model="booking.time" id="bookTime" type="time" required />

          <label for="bookProof">Upload File or Image</label>
          <input
            id="bookProof"
            type="file"
            accept="image/*,.pdf,.doc,.docx"
            @change="handleProofFile"
          />

          <button
            id="confirmBookBtn"
            class="confirm-btn"
            type="button"
            :disabled="submitting"
            @click="confirmBooking"
          >
            {{ submitting ? 'Submitting...' : 'Confirm Booking' }}
          </button>
        </div>
      </div>
    </div>

    <div v-if="showProcessingModal" class="processing-modal">
      <div class="processing-content" id="processingContent">
        <template v-if="!processingDone">
          <h3>Your transaction is being processed.</h3>
          <p>Please wait...</p>
        </template>

        <template v-else>
          <h3>Your transaction has been submitted.</h3>
          <p>Please wait for admin approval.</p>
          <button class="ok-btn" type="button" @click="closeProcessing">
            OK
          </button>
        </template>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue'
import axios from 'axios'
import { useRouter } from 'vue-router'
import defaultCarImg from '@/assets/img/logocar.png.png'

const router = useRouter()

const vehicles = ref([])
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
  seats: 0,
  transmission: '',
  fuel: '',
  status: '',
  image: '',
})

const booking = reactive({
  date: '',
  time: '',
  proofFile: null,
})

const formatPrice = (value) => {
  return Number(value || 0).toLocaleString(undefined, {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  })
}

const fetchVehicles = async () => {
  loading.value = true

  try {
    const response = await axios.get('/user/vehicles')

    if (response.data?.success) {
      vehicles.value = response.data.vehicles.map((vehicle) => ({
        ...vehicle,
        image: vehicle.image
          ? `/uploads/vehicles/${vehicle.image}`
          : defaultCarImg,
      }))
    } else {
      vehicles.value = []
    }
  } catch (error) {
    console.error('Failed to fetch vehicles:', error)
    vehicles.value = []
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchVehicles()
})

const openDetails = (vehicle) => {
  Object.assign(selectedVehicle, vehicle)
  showDetailsModal.value = true
}

const closeDetails = () => {
  showDetailsModal.value = false
}

const openBooking = () => {
  booking.date = ''
  booking.time = ''
  booking.proofFile = null
  showDetailsModal.value = false
  showBookingModal.value = true
}

const closeBooking = () => {
  showBookingModal.value = false
}

const handleProofFile = (event) => {
  booking.proofFile = event.target.files?.[0] || null
}


 const confirmBooking = async () => {
  if (!booking.date || !booking.time || !booking.proofFile) {
    alert('Please select booking date, time, and upload a file or image.')
    return
  }

  const user = JSON.parse(localStorage.getItem('user'))

  if (!user || !user.id) {
    alert('Please login first.')
    router.push('/login')
    return
  }

  try {
    submitting.value = true
    showBookingModal.value = false
    showProcessingModal.value = true
    processingDone.value = false

    const rentalStart = `${booking.date} ${booking.time}:00`

    const formData = new FormData()
    formData.append('customer_id', user.id)
    formData.append('vehicle_id', selectedVehicle.id)
    formData.append('rental_start', rentalStart)
    formData.append('proof_file', booking.proofFile)

    const response = await axios.post('/user/rentals', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    })

    if (response.data?.success) {
      processingDone.value = true
    } else {
      throw new Error(response.data?.message || 'Rental failed.')
    }
  } catch (error) {
    console.error('Rental failed:', error)
    alert(error.response?.data?.message || 'Failed to submit rental.')
    showProcessingModal.value = false
    processingDone.value = false
  } finally {
    submitting.value = false
  }
}

const closeProcessing = () => {
  showProcessingModal.value = false
  processingDone.value = false
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
  pointer-events: none !important;
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
  padding: 25px;
  border-radius: 16px;
  width: 100%;
  max-width: 500px;
  position: relative;
  z-index: 10000;
  box-shadow: 0 0 30px rgba(0, 0, 0, 0.45);
  pointer-events: auto;
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

#carImage {
  width: 100%;
  border-radius: 12px;
  margin-bottom: 15px;
}

.car-info-box {
  margin-top: 15px;
}

.car-info-box ul {
  list-style: none;
  padding: 0;
}

.car-info-box li {
  margin-bottom: 8px;
}

.action-buttons,
.booking-fields {
  margin-top: 20px;
}

.booking-fields {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.booking-fields label {
  font-weight: 600;
  margin-top: 4px;
}

.booking-fields input {
  padding: 10px;
  border-radius: 8px;
  border: none;
  outline: none;
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
</style>