<template>
  <div class="dashboard-page">
    <div v-if="loading" class="status-message">Loading dashboard...</div>
    <div v-else-if="error" class="status-message error-message">{{ error }}</div>

    <section v-else class="dashboard-layout">
      <!-- Top Stats -->
      <div class="stats-row">
        <div v-for="(stat, index) in dashboard.stats" :key="index" class="stat-box">
          <h3>{{ stat.value }}</h3>
          <p>{{ stat.title }}</p>
        </div>
      </div>

      <!-- Booking Status -->
      <div class="booking-row">
        <div class="booking-box approved">
          <h3>{{ dashboard.bookings.approved }}</h3>
          <p>Approved</p>
        </div>
        <div class="booking-box pending">
          <h3>{{ dashboard.bookings.pending }}</h3>
          <p>Pending</p>
        </div>
        <div class="booking-box denied">
          <h3>{{ dashboard.bookings.denied }}</h3>
          <p>Denied</p>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const dashboard = ref({
  stats: [],
  bookings: { approved: 0, pending: 0, denied: 0 },
})

const loading = ref(true)
const error = ref('')

const fetchDashboard = async () => {
  try {
    const response = await axios.get('/admin/dashboard')
    if (response.data?.success) {
      const data = response.data.data
      dashboard.value.stats = data.stats || []
      dashboard.value.bookings = {
        approved: data.bookings?.approved ?? 0,
        pending: data.bookings?.pending ?? 0,
        denied: data.bookings?.denied ?? 0,
      }
    } else {
      error.value = 'Failed to load dashboard data.'
    }
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to load dashboard data.'
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchDashboard()
})
</script>

<style scoped>
.dashboard-page {
  position: fixed;
  top: 80px;
  left: 317px;
  width: calc(100vw - 317px);
  height: calc(100vh - 80px);
  padding: 20px;
  background: #0f0f0f;
  overflow-y: auto;
  overflow-x: hidden;
  display: flex;
  flex-direction: column;
  box-sizing: border-box;
}

.status-message {
  color: white;
  font-size: 18px;
  text-align: center;
  margin-top: 50px;
}

.error-message {
  color: #ffb3b3;
}

/* Top stats row */
.stats-row {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: 20px;
  margin-bottom: 30px;
}

/* Stat Boxes */
.stat-box {
  background: #1a1a1a;
  color: white;
  padding: 25px 20px;
  border-radius: 14px;
  text-align: center;
  box-shadow: 0 0 15px rgba(0,0,0,0.5);
  transition: transform 0.2s ease;
}

.stat-box:hover {
  transform: translateY(-5px);
}

.stat-box h3 {
  font-size: 2rem;
  margin: 0 0 8px 0;
}

/* Booking status row */
.booking-row {
  display: flex;
  gap: 20px;
  flex-wrap: wrap;
  justify-content: space-between;
}

/* Booking Boxes */
.booking-box {
  flex: 1 1 calc(33% - 20px);
  min-width: 150px;
  padding: 30px 15px;
  border-radius: 14px;
  color: white;
  text-align: center;
  font-weight: bold;
  box-shadow: 0 0 15px rgba(0,0,0,0.5);
  transition: transform 0.2s ease;
}

.booking-box:hover {
  transform: translateY(-5px);
}

.booking-box h3 {
  font-size: 2rem;
  margin: 0 0 8px 0;
}

.booking-box p {
  font-size: 1rem;
  font-weight: 500;
}

.booking-box.approved {
  background-color: #28a745;
}

.booking-box.pending {
  background-color: #ffc107;
  color: #111;
}

.booking-box.denied {
  background-color: #dc3545;
}
</style>