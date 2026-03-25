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
        <h2 id="carName">{{ selectedVehicle.brand }} {{ selectedVehicle.model }}</h2>
        <p class="price" id="carPrice">
          ₱{{ formatPrice(selectedVehicle.rate) }} <span>per day</span>
        </p>

        <div class="car-info-box">
          <h3>Car Details</h3>
          <ul id="carFeatures">
            <li><strong>Brand:</strong> {{ selectedVehicle.brand }}</li>
            <li><strong>Model:</strong> {{ selectedVehicle.model }}</li>
            <li><strong>Seats:</strong> {{ selectedVehicle.seats }}</li>
            <li><strong>Transmission:</strong> {{ selectedVehicle.transmission }}</li>
            <li><strong>Fuel:</strong> {{ selectedVehicle.fuel }}</li>
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
            @click="confirmBooking"
          >
            Confirm Booking
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
          <h3>Your transaction is being processed.</h3>
          <p>Please wait...</p>
          <button class="ok-btn" type="button" @click="closeProcessing">OK</button>
        </template>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue'

import UserNavbar from '@/components/user/navbar.vue'
import defaultCarImg from '@/assets/img/logocar.png.png'


const vehicles = ref([
  {
    id: 1,
    brand: 'Toyota',
    model: 'Vios',
    rate: 2500,
    seats: 5,
    transmission: 'Automatic',
    fuel: 'Gasoline',
    status: 'Available',
    image: defaultCarImg,
  },
  {
    id: 2,
    brand: 'Honda',
    model: 'City',
    rate: 2800,
    seats: 5,
    transmission: 'Automatic',
    fuel: 'Gasoline',
    status: 'Available',
    image: defaultCarImg,
  },
  {
    id: 3,
    brand: 'Mitsubishi',
    model: 'Montero',
    rate: 4500,
    seats: 7,
    transmission: 'Automatic',
    fuel: 'Diesel',
    status: 'Unavailable',
    image: defaultCarImg,
  },
  {
    id: 4,
    brand: 'Suzuki',
    model: 'Ertiga',
    rate: 3000,
    seats: 7,
    transmission: 'Manual',
    fuel: 'Gasoline',
    status: 'Available',
    image: defaultCarImg,
  },
])

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
  return Number(value).toLocaleString(undefined, {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  })
}

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

const confirmBooking = () => {
  if (!booking.date || !booking.time || !booking.proofFile) {
    alert('Please select booking date, time, and upload a file or image.')
    return
  }

  showBookingModal.value = false
  showProcessingModal.value = true
  processingDone.value = false

  setTimeout(() => {
    processingDone.value = true
  }, 1500)
}

const closeProcessing = () => {
  showProcessingModal.value = false
  processingDone.value = false
}
</script>

<style scoped>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
  font-family: "Poppins", sans-serif;
}

.vehicles-page {
  background-color: #0e0e0e;
  color: white;
  overflow: hidden;
  min-height: 100vh;
  position: relative;
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
}
</style>