<template>
  <div class="dashboard-page">
    <div v-if="loading" class="status-message">
      Loading dashboard...
    </div>

    <div v-else-if="error" class="status-message error-message">
      {{ error }}
    </div>

    <section v-else class="dashboard-layout">
      <div class="dashboard-map">
        <h2>{{ dashboard.location_title }}</h2>
        <iframe
          :src="dashboard.map_url"
          allowfullscreen
          loading="lazy"
        ></iframe>
      </div>

      <div class="dashboard-cards">
        <div
          class="card"
          v-for="(card, index) in dashboard.cards"
          :key="index"
        >
          <div class="circle" :style="{ '--value': card.value }">
            <span>{{ card.value }}%</span>
          </div>
          <p>{{ card.title }}</p>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const dashboard = ref({
  location_title: '',
  map_url: '',
  cards: [],
})

const loading = ref(true)
const error = ref('')

const fetchDashboard = async () => {
  try {
    const response = await axios.get('/api/admin/dashboard')

    if (response.data?.success) {
      dashboard.value = response.data.data
    } else {
      error.value = 'Failed to load dashboard data.'
    }
  } catch (err) {
    error.value =
      err.response?.data?.message || 'Failed to load dashboard data.'
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

.status-message {
  color: white;
  font-size: 18px;
  text-align: center;
  margin-top: 50px;
}

.error-message {
  color: #ffb3b3;
}

.dashboard-layout {
  display: grid;
  grid-template-columns: 2fr 1fr;
  gap: 25px;
  align-items: stretch;
}

.dashboard-map {
  background: #111;
  padding: 40px;
  border-radius: 12px;
}

.dashboard-map h2 {
  font-size: 1.3rem;
  font-weight: 700;
  margin-top: 0;
  margin-bottom: 20px;
  color: white;
}

.dashboard-map iframe {
  width: 100%;
  height: 100%;
  min-height: 350px;
  border-radius: 10px;
  border: none;
}

.dashboard-cards {
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  gap: 20px;
}

.card {
  background: #111;
  padding: 20px;
  border-radius: 12px;
  text-align: center;
}

.card p {
  font-size: 1rem;
  color: #ccc;
}

.circle {
  --size: 120px;
  width: var(--size);
  height: var(--size);
  border-radius: 50%;
  background: conic-gradient(#ffd700 calc(var(--value) * 1%), #333 0%);
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 15px auto;
  position: relative;
  color: #ffd700;
  font-weight: 700;
  font-size: 1.4rem;
}

.circle::before {
  content: '';
  position: absolute;
  width: calc(var(--size) - 20px);
  height: calc(var(--size) - 20px);
  background: #0e0e0e;
  border-radius: 50%;
  z-index: 0;
}

.circle span {
  position: relative;
  z-index: 1;
}

@media (max-width: 1200px) {
  .navbar {
    left: 0;
  }

  .dashboard-page {
    margin-left: 0;
  }
}

@media (max-width: 992px) {
  .dashboard-layout {
    grid-template-columns: 1fr;
  }
}
</style>