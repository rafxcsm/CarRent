<template>
  <div class="dashboard-page">
    <main class="dashboard-layout">
      <header class="dashboard-heading">
        <div>
          <p class="eyebrow">CARRENT ADMIN</p>
          <h1>Your fleet at a glance</h1>
          <p>See vehicle availability and booking activity in one place.</p>
        </div>
        <span class="overview-chip"><i></i> Overview</span>
      </header>

      <div v-if="dashboard.client?.subscription_status === 'past_due'" class="subscription-banner past-due-banner" role="status">
        Subscription payment is overdue. Grace access ends {{ formatDate(dashboard.client.grace_ends_at) }}; the workspace will then become read-only.
      </div>
      <div v-else-if="dashboard.client?.subscription_status === 'suspended'" class="subscription-banner suspended-banner" role="status">
        This workspace is read-only while the subscription is suspended. Contact the platform administrator to renew access.
      </div>

      <div v-if="loading" class="status-message">Loading dashboard...</div>
      <div v-else-if="error" class="status-message error-message">{{ error }}</div>

      <section v-else class="dashboard-content">
        <div class="stat-grid" aria-label="Summary figures">
          <article v-for="stat in statCards" :key="stat.title" class="stat-card" :class="`tone-${stat.tone}`">
            <div class="stat-card-top">
              <span class="stat-orb" aria-hidden="true">{{ stat.mark }}</span>
              <span class="stat-caption">{{ stat.title }}</span>
            </div>
            <strong class="stat-value">{{ stat.value }}</strong>
            <span class="stat-footnote">{{ stat.note }}</span>
          </article>
        </div>

        <div class="visual-grid">
          <article class="visual-card">
            <div class="card-heading">
              <div><p class="eyebrow">FLEET STATUS</p><h2>Vehicle availability</h2></div>
              <span class="count-chip">{{ totalCars }} total cars</span>
            </div>
            <div class="chart-area">
              <div class="availability-ring" :style="{ '--progress': `${availablePercent}%` }" role="img" :aria-label="`${availablePercent}% of cars are available`">
                <div class="ring-center"><strong>{{ availablePercent }}<small>%</small></strong><span>available</span></div>
              </div>
              <div class="chart-legend">
                <div class="legend-item"><i class="available-dot"></i><span>Available</span><strong>{{ availableCars }}</strong><small>{{ availablePercent }}%</small></div>
                <div class="legend-item"><i class="rented-dot"></i><span>Rented</span><strong>{{ rentedCars }}</strong><small>{{ rentedPercent }}%</small></div>
              </div>
            </div>
            <p class="chart-note">Vehicles ready to book compared with cars currently in use.</p>
          </article>

          <article class="visual-card">
            <div class="card-heading">
              <div><p class="eyebrow">BOOKING ACTIVITY</p><h2>Request breakdown</h2></div>
              <span class="count-chip">{{ totalBookings }} requests</span>
            </div>
            <div class="chart-area">
              <div class="booking-ring" :style="{ background: bookingDonutBackground }" role="img" :aria-label="bookingChartLabel">
                <div class="ring-center booking-center"><strong>{{ totalBookings }}</strong><span>bookings</span></div>
              </div>
              <div class="chart-legend booking-legend">
                <div v-for="item in bookingItems" :key="item.key" class="legend-item">
                  <i :class="`${item.key}-dot`"></i><span>{{ item.label }}</span><strong>{{ item.value }}</strong><small>{{ item.percent }}%</small>
                </div>
              </div>
            </div>
            <p class="chart-note">A quick view of approved, pending, and denied customer requests.</p>
          </article>
        </div>
      </section>
    </main>
  </div>
</template>

<script setup>
import { computed, ref, onMounted } from 'vue'
import api from '@/api'

const dashboard = ref({ stats: [], client: null, bookings: { approved: 0, pending: 0, denied: 0 } })
const loading = ref(true)
const error = ref('')

const readStat = (title) => Number(dashboard.value.stats.find((item) => item.title?.toLowerCase() === title.toLowerCase())?.value || 0)
const statCards = computed(() => [
  { title: 'Total cars', source: 'Total Cars', mark: 'F', tone: 'blue', note: 'Vehicles in your fleet' },
  { title: 'Available cars', source: 'Available Cars', mark: 'A', tone: 'green', note: 'Ready for a new booking' },
  { title: 'Rented cars', source: 'Rented Cars', mark: 'R', tone: 'violet', note: 'Currently in use' },
  { title: 'Customers', source: 'Total Customers', mark: 'C', tone: 'amber', note: 'Registered accounts' },
].map((stat) => ({ ...stat, value: readStat(stat.source) })))

const totalCars = computed(() => readStat('Total Cars'))
const availableCars = computed(() => readStat('Available Cars'))
const rentedCars = computed(() => readStat('Rented Cars'))
const availablePercent = computed(() => totalCars.value ? Math.round(availableCars.value / totalCars.value * 100) : 0)
const rentedPercent = computed(() => totalCars.value ? Math.round(rentedCars.value / totalCars.value * 100) : 0)
const totalBookings = computed(() => Object.values(dashboard.value.bookings).reduce((sum, value) => sum + Number(value || 0), 0))
const bookingItems = computed(() => [
  { key: 'approved', label: 'Approved', value: Number(dashboard.value.bookings.approved || 0) },
  { key: 'pending', label: 'Pending', value: Number(dashboard.value.bookings.pending || 0) },
  { key: 'denied', label: 'Denied', value: Number(dashboard.value.bookings.denied || 0) },
].map((item) => ({ ...item, percent: totalBookings.value ? Math.round(item.value / totalBookings.value * 100) : 0 })))
const bookingDonutBackground = computed(() => {
  if (!totalBookings.value) return 'conic-gradient(var(--surface-muted) 0deg 360deg)'
  const approvedStop = dashboard.value.bookings.approved / totalBookings.value * 360
  const pendingStop = approvedStop + dashboard.value.bookings.pending / totalBookings.value * 360
  return `conic-gradient(var(--success) 0deg ${approvedStop}deg, var(--warning) ${approvedStop}deg ${pendingStop}deg, var(--danger) ${pendingStop}deg 360deg)`
})
const bookingChartLabel = computed(() => bookingItems.value.map((item) => `${item.label}: ${item.value}`).join(', '))
const formatDate = (value) => value ? new Intl.DateTimeFormat(undefined, { dateStyle: 'medium' }).format(new Date(value)) : 'the end of the grace period'

const fetchDashboard = async () => {
  try {
    const response = await api.get('/admin/dashboard')
    if (response.data?.success) {
      const data = response.data.data
      dashboard.value.client = data.client || null
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
    error.value = err.response?.data?.message || 'Unauthorized or failed request.'
  } finally {
    loading.value = false
  }
}

onMounted(fetchDashboard)
</script>

<style scoped>
.dashboard-layout { padding: 8px 0 30px; }
.subscription-banner { margin: 0 0 18px; padding: 13px 16px; border: 1px solid var(--line-subtle); border-radius: 13px; font-size: .84rem; line-height: 1.5; }
.past-due-banner { border-color: var(--warning); color: var(--warning); background: var(--warning-soft); }
.suspended-banner { border-color: var(--danger); color: var(--danger); background: var(--danger-soft); }
.dashboard-heading, .card-heading { display: flex; align-items: center; justify-content: space-between; gap: 18px; }
.dashboard-heading { margin-bottom: 25px; }
.eyebrow { margin: 0 0 7px; color: var(--blue); font-size: .69rem; font-weight: 800; letter-spacing: .14em; }
.dashboard-heading h1 { margin: 0; color: var(--text-primary); font-size: clamp(1.7rem, 3vw, 2.25rem); letter-spacing: -.05em; }
.dashboard-heading p:not(.eyebrow) { margin: 8px 0 0; color: var(--text-secondary); }
.overview-chip, .count-chip { display: inline-flex; align-items: center; gap: 8px; padding: 8px 12px; border: 1px solid var(--line-subtle); border-radius: 999px; color: var(--text-secondary); background: var(--surface-card); font-size: .77rem; font-weight: 700; white-space: nowrap; }
.overview-chip i { width: 8px; height: 8px; border-radius: 50%; background: var(--blue); box-shadow: 0 0 0 4px var(--blue-soft); }
.status-message { padding: 44px 20px; color: var(--text-secondary); text-align: center; }
.error-message { color: var(--danger); }
.stat-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 15px; }
.stat-card, .visual-card { border: 1px solid var(--line-subtle); border-radius: 18px; background: var(--surface-card); box-shadow: var(--shadow-card); }
.stat-card { position: relative; min-width: 0; overflow: hidden; padding: 18px 20px; }
.stat-card::after { position: absolute; right: -27px; bottom: -42px; width: 110px; height: 110px; border: 1px solid var(--orb-border); border-radius: 50%; content: ''; }
.tone-blue { --orb: var(--blue-soft); --orb-ink: var(--blue); --orb-border: var(--blue-border); }
.tone-green { --orb: var(--success-soft); --orb-ink: var(--success); --orb-border: var(--success); }
.tone-violet { --orb: #f0edfc; --orb-ink: #7659c7; --orb-border: #d9d0f6; }
.tone-amber { --orb: var(--warning-soft); --orb-ink: var(--warning); --orb-border: var(--warning); }
:global(html[data-theme="dark"]) .tone-violet { --orb: #30294d; --orb-ink: #b4a2ff; --orb-border: #695d91; }
.stat-card-top { display: flex; align-items: center; gap: 10px; }
.stat-orb { display: grid; width: 36px; height: 36px; flex: 0 0 36px; place-items: center; border: 1px solid var(--orb-border); border-radius: 50%; color: var(--orb-ink); background: var(--orb); font-size: .78rem; font-weight: 850; }
.stat-caption { overflow: hidden; color: var(--text-secondary); font-size: .82rem; font-weight: 700; text-overflow: ellipsis; white-space: nowrap; }
.stat-value { display: block; margin-top: 17px; color: var(--text-primary); font-size: 2rem; letter-spacing: -.06em; line-height: 1; }
.stat-footnote { display: block; margin-top: 7px; color: var(--text-muted); font-size: .75rem; }
.visual-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; margin-top: 16px; }
.visual-card { min-width: 0; padding: 22px; }
.card-heading { align-items: flex-start; margin-bottom: 18px; }
.card-heading .eyebrow { margin-bottom: 5px; }
.card-heading h2 { margin: 0; color: var(--text-primary); font-size: 1.08rem; letter-spacing: -.025em; }
.count-chip { padding: 7px 10px; background: var(--surface-raised); font-size: .72rem; }
.chart-area { display: flex; min-height: 190px; align-items: center; justify-content: center; gap: clamp(24px, 5vw, 52px); }
.availability-ring, .booking-ring { position: relative; width: 164px; height: 164px; flex: 0 0 164px; border-radius: 50%; }
.availability-ring { padding: 12px; background: conic-gradient(var(--blue) 0 var(--progress), var(--surface-muted) var(--progress) 100%); }
.availability-ring::before, .booking-ring::before { position: absolute; inset: 12px; border: 1px solid var(--line-subtle); border-radius: 50%; content: ''; }
.ring-center { position: absolute; inset: 22px; display: flex; flex-direction: column; align-items: center; justify-content: center; border: 1px solid var(--line-subtle); border-radius: 50%; background: var(--surface-card); }
.ring-center strong { color: var(--text-primary); font-size: 2rem; letter-spacing: -.06em; line-height: 1; }
.ring-center strong small { margin-left: 2px; color: var(--blue); font-size: .95rem; }
.ring-center span { margin-top: 5px; color: var(--text-muted); font-size: .72rem; }
.booking-center strong { font-size: 1.8rem; }
.chart-legend { display: grid; width: min(100%, 205px); gap: 15px; }
.legend-item { display: grid; grid-template-columns: 9px minmax(0, 1fr) auto auto; align-items: center; gap: 9px; }
.legend-item i { width: 9px; height: 9px; border-radius: 50%; }
.legend-item span { color: var(--text-secondary); font-size: .77rem; }
.legend-item strong { color: var(--text-primary); font-size: .84rem; text-align: right; }
.legend-item small { min-width: 34px; color: var(--text-muted); font-size: .71rem; text-align: right; }
.available-dot { background: var(--blue); }
.rented-dot { background: #9bbdf5; }
.approved-dot { background: var(--success); }
.pending-dot { background: var(--warning); }
.denied-dot { background: var(--danger); }
.chart-note { margin: 14px 0 0; padding-top: 12px; border-top: 1px solid var(--line-subtle); color: var(--text-muted); font-size: .75rem; line-height: 1.45; }
@media (max-width: 980px) { .stat-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } .visual-grid { grid-template-columns: 1fr; } }
@media (max-width: 600px) { .dashboard-heading { align-items: flex-start; flex-direction: column; } .chart-area { flex-wrap: wrap; gap: 18px; } .availability-ring, .booking-ring { width: 148px; height: 148px; flex-basis: 148px; } .visual-card { padding: 17px; } .stat-card { padding: 15px; } }
</style>
