<template>
  <div class="super-admin-page">
    <header class="platform-topbar">
      <router-link to="/super-admin/dashboard" class="platform-brand" aria-label="CarRent platform dashboard">
        <span class="platform-brand-mark" aria-hidden="true">P</span>
        <span class="brand-copy"><strong>CarRent</strong><small>Platform control</small></span>
      </router-link>

      <nav class="platform-nav" aria-label="Platform navigation">
        <router-link to="/super-admin/dashboard">Overview</router-link>
        <router-link to="/super-admin/clients">Clients</router-link>
      </nav>

      <div class="topbar-actions">
        <span class="role-chip"><i></i> Super admin</span>
        <ThemeToggle />
        <button type="button" class="logout-button" @click="logout">Log out</button>
      </div>
    </header>

    <main class="platform-content">
      <section class="page-heading">
        <div>
          <p class="eyebrow">PLATFORM OVERVIEW</p>
          <h1>Rental activity at a glance</h1>
        <p>Subscriptions and rental activity across every client business.</p>
        </div>
        <button type="button" class="refresh-button" :disabled="loading" @click="fetchDashboard">
          <span aria-hidden="true">↻</span> {{ loading ? 'Refreshing…' : 'Refresh' }}
        </button>
      </section>

      <div class="scope-note">
        <span class="scope-icon" aria-hidden="true">i</span>
        <p>Manage business accounts, review their rental totals, and record monthly subscription payments from the Clients page.</p>
      </div>

      <div v-if="loading && !loaded" class="status-message">Loading platform overview…</div>
      <div v-else-if="error" class="status-message error-message" role="alert">{{ error }}</div>

      <template v-else>
        <section class="metric-grid" aria-label="Platform totals">
          <article v-for="item in metrics" :key="item.key" class="metric-card" :class="`tone-${item.tone}`">
            <div class="metric-top"><span class="metric-icon" aria-hidden="true">{{ item.icon }}</span><span>{{ item.label }}</span></div>
            <strong>{{ item.key === 'active_monthly_value' ? formatMoney(data.metrics[item.key]) : formatNumber(data.metrics[item.key]) }}</strong>
            <small>{{ item.note }}</small>
          </article>
        </section>

        <section class="overview-grid">
          <article class="panel activity-panel">
            <div class="panel-heading">
              <div><p class="eyebrow">BOOKING HEALTH</p><h2>Rental status overview</h2></div>
              <span class="total-chip">{{ formatNumber(data.metrics.rentals) }} total</span>
            </div>
            <div v-if="statusItems.length" class="status-list">
              <div v-for="item in statusItems" :key="item.name" class="status-row">
                <div class="status-label"><i :class="`status-dot ${toneFor(item.name)}`"></i><span>{{ labelFor(item.name) }}</span><strong>{{ formatNumber(item.total) }}</strong></div>
                <div class="progress-track"><span :class="toneFor(item.name)" :style="{ width: `${item.percent}%` }"></span></div>
              </div>
            </div>
            <div v-else class="empty-state">No rental transactions have been recorded yet.</div>
          </article>

          <article class="panel month-panel">
            <p class="eyebrow">CURRENT MONTH</p>
            <h2>New rental activity</h2>
            <div class="month-count"><strong>{{ formatNumber(data.metrics.rentals_this_month) }}</strong><span>transactions created this month</span></div>
            <div class="month-foot"><span class="month-mark" aria-hidden="true">↗</span><span>Updated {{ lastUpdated }}</span></div>
          </article>
        </section>

        <p class="data-footnote">Client rental records are separated. Monthly value is the sum of active plan prices; payment collections are recorded separately on the Clients page.</p>
      </template>
    </main>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/api'
import ThemeToggle from '@/components/ThemeToggle.vue'

const router = useRouter()
const loading = ref(true)
const loaded = ref(false)
const error = ref('')
const data = ref({
  generated_at: null,
  metrics: { clients: 0, past_due_clients: 0, suspended_clients: 0, active_monthly_value: 0, admin_accounts: 0, customers: 0, vehicles: 0, rentals: 0, rentals_this_month: 0 },
  rental_statuses: {},
})

const metrics = [
  { key: 'clients', label: 'Client businesses', icon: 'C', tone: 'blue', note: 'Active rental businesses' },
  { key: 'past_due_clients', label: 'Past due', icon: '!', tone: 'amber', note: 'In the seven-day grace period' },
  { key: 'suspended_clients', label: 'Suspended', icon: 'S', tone: 'red', note: 'Read-only until renewed' },
  { key: 'active_monthly_value', label: 'Active monthly value', icon: '₱', tone: 'violet', note: 'Plan value, not confirmed collections' },
  { key: 'customers', label: 'Registered customers', icon: 'U', tone: 'green', note: 'Customer accounts on this system' },
  { key: 'vehicles', label: 'Vehicles', icon: 'V', tone: 'violet', note: 'Vehicles recorded in the fleet' },
  { key: 'rentals', label: 'Rental transactions', icon: 'R', tone: 'amber', note: 'All recorded rental requests' },
]

const statusItems = computed(() => {
  const statuses = Object.entries(data.value.rental_statuses || {}).map(([name, total]) => ({ name, total: Number(total || 0) }))
  const count = statuses.reduce((sum, item) => sum + item.total, 0)
  return statuses
    .sort((a, b) => b.total - a.total)
    .map((item) => ({ ...item, percent: count ? Math.round(item.total / count * 100) : 0 }))
})

const lastUpdated = computed(() => {
  if (!data.value.generated_at) return 'not yet'
  return new Intl.DateTimeFormat(undefined, { hour: 'numeric', minute: '2-digit' }).format(new Date(data.value.generated_at))
})

const formatNumber = (value) => new Intl.NumberFormat().format(Number(value || 0))
const formatMoney = (value) => new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP', maximumFractionDigits: 0 }).format(Number(value || 0))
const labelFor = (value) => value.replaceAll('_', ' ').replace(/\b\w/g, (letter) => letter.toUpperCase())
const toneFor = (status) => {
  const normalized = status.toLowerCase()
  if (normalized.includes('approv') || normalized.includes('complet') || normalized.includes('return')) return 'green'
  if (normalized.includes('deni') || normalized.includes('cancel')) return 'red'
  if (normalized.includes('pend')) return 'amber'
  return 'blue'
}

const fetchDashboard = async () => {
  loading.value = true
  error.value = ''
  try {
    const response = await api.get('/super-admin/dashboard')
    data.value = response.data.data
    loaded.value = true
  } catch (err) {
    error.value = err.response?.data?.message || 'Could not load the platform overview.'
  } finally {
    loading.value = false
  }
}

const logout = async () => {
  try {
    await api.post('/super-admin/logout')
  } catch (err) {
    // Clear the local session even if the token has expired.
  } finally {
    sessionStorage.removeItem('super_admin_token')
    sessionStorage.removeItem('super_admin')
    await router.replace('/super-admin/login')
  }
}

onMounted(fetchDashboard)
</script>

<style scoped>
.super-admin-page { min-height: 100vh; padding-top: var(--app-header-height, 76px); color: var(--text-primary); background: var(--surface-page); }
.platform-topbar { position: fixed; z-index: 30; inset: 0 0 auto; display: flex; height: var(--app-header-height, 76px); align-items: center; gap: 20px; padding: 0 clamp(18px, 4vw, 54px); border-bottom: 1px solid var(--line-subtle); background: color-mix(in srgb, var(--surface-card) 95%, transparent); box-shadow: var(--shadow-card); backdrop-filter: blur(14px); }
.platform-brand { display: inline-flex; min-width: max-content; align-items: center; gap: 11px; color: var(--text-primary); text-decoration: none; }
.platform-brand-mark { display: grid; width: 40px; height: 40px; place-items: center; border-radius: 13px; color: #fff; background: linear-gradient(145deg, var(--blue), #78aaff); font-weight: 850; }
.brand-copy { display: grid; gap: 2px; }
.brand-copy strong { font-size: 1rem; letter-spacing: -.03em; }
.brand-copy small { color: var(--text-muted); font-size: .65rem; letter-spacing: .1em; text-transform: uppercase; }
.platform-nav { display: flex; align-items: center; gap: 4px; }
.platform-nav a { padding: 9px 12px; border-radius: 9px; color: var(--text-secondary); font-size: .86rem; font-weight: 700; text-decoration: none; }
.platform-nav a:hover, .platform-nav a.router-link-active { color: var(--blue-ink); background: var(--blue-soft); }
.topbar-actions { display: flex; align-items: center; gap: 15px; margin-left: auto; }
.role-chip { display: inline-flex; align-items: center; gap: 8px; padding: 8px 12px; border: 1px solid var(--line-subtle); border-radius: 999px; color: var(--text-secondary); font-size: .8rem; font-weight: 700; }
.role-chip i { width: 8px; height: 8px; border-radius: 50%; background: var(--blue); }
.logout-button, .refresh-button { min-height: 38px; padding: 0 13px; border: 1px solid var(--line-subtle); border-radius: 10px; color: var(--text-primary); background: var(--surface-card); font-weight: 700; cursor: pointer; }
.logout-button:hover, .refresh-button:hover { border-color: var(--blue-border); color: var(--blue-ink); }
.platform-content { width: min(100% - 48px, 1400px); margin: 0 auto; padding: clamp(24px, 4vw, 48px) 0 42px; }
.page-heading { display: flex; align-items: flex-end; justify-content: space-between; gap: 24px; margin-bottom: 22px; }
.eyebrow { margin: 0 0 7px; color: var(--blue); font-size: .68rem; font-weight: 850; letter-spacing: .14em; }
.page-heading h1 { margin: 0; font-size: clamp(1.8rem, 3.4vw, 2.6rem); letter-spacing: -.055em; }
.page-heading p:not(.eyebrow) { margin: 8px 0 0; color: var(--text-secondary); }
.refresh-button { display: inline-flex; align-items: center; gap: 8px; white-space: nowrap; }
.refresh-button span { color: var(--blue); font-size: 1.2rem; }
.refresh-button:disabled { opacity: .65; cursor: wait; }
.scope-note { display: flex; align-items: flex-start; gap: 12px; margin-bottom: 18px; padding: 14px 16px; border: 1px solid var(--blue-border); border-radius: 14px; color: var(--text-secondary); background: var(--blue-soft); }
.scope-note p { margin: 0; font-size: .83rem; line-height: 1.55; }
.scope-icon { display: grid; width: 21px; height: 21px; flex: 0 0 21px; place-items: center; border-radius: 50%; color: #fff; background: var(--blue); font-size: .75rem; font-weight: 850; }
.status-message { padding: 46px 20px; color: var(--text-secondary); text-align: center; }
.error-message { color: var(--danger); }
.metric-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 15px; }
.metric-card, .panel { min-width: 0; border: 1px solid var(--line-subtle); border-radius: 18px; background: var(--surface-card); box-shadow: var(--shadow-card); }
.metric-card { position: relative; overflow: hidden; padding: 19px 20px; }
.metric-card::after { position: absolute; right: -35px; bottom: -55px; width: 125px; height: 125px; border: 1px solid var(--tone-border); border-radius: 50%; content: ''; }
.tone-blue { --tone: var(--blue-soft); --tone-ink: var(--blue); --tone-border: var(--blue-border); }
.tone-green { --tone: var(--success-soft); --tone-ink: var(--success); --tone-border: var(--success); }
.tone-violet { --tone: #f0edfc; --tone-ink: #7659c7; --tone-border: #d9d0f6; }
.tone-amber { --tone: var(--warning-soft); --tone-ink: var(--warning); --tone-border: var(--warning); }
.tone-red { --tone: var(--danger-soft); --tone-ink: var(--danger); --tone-border: var(--danger); }
:global(html[data-theme="dark"]) .tone-violet { --tone: #30294d; --tone-ink: #b4a2ff; --tone-border: #695d91; }
.metric-top { display: flex; align-items: center; gap: 10px; color: var(--text-secondary); font-size: .78rem; font-weight: 700; }
.metric-icon { display: grid; width: 35px; height: 35px; flex: 0 0 35px; place-items: center; border: 1px solid var(--tone-border); border-radius: 50%; color: var(--tone-ink); background: var(--tone); font-size: .76rem; font-weight: 850; }
.metric-card > strong { display: block; margin-top: 16px; font-size: 2rem; letter-spacing: -.06em; line-height: 1; }
.metric-card > small { display: block; margin-top: 8px; color: var(--text-muted); font-size: .72rem; }
.overview-grid { display: grid; grid-template-columns: minmax(0, 1.35fr) minmax(260px, .65fr); gap: 16px; margin-top: 16px; }
.panel { padding: clamp(18px, 2.5vw, 25px); }
.panel-heading { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; margin-bottom: 19px; }
.panel-heading .eyebrow, .month-panel .eyebrow { margin-bottom: 5px; }
.panel h2 { margin: 0; font-size: 1.08rem; letter-spacing: -.025em; }
.total-chip { padding: 7px 10px; border: 1px solid var(--line-subtle); border-radius: 999px; color: var(--text-secondary); background: var(--surface-raised); font-size: .72rem; font-weight: 750; white-space: nowrap; }
.status-list { display: grid; gap: 15px; }
.status-label { display: grid; grid-template-columns: 9px minmax(0, 1fr) auto; align-items: center; gap: 9px; margin-bottom: 7px; }
.status-label span { color: var(--text-secondary); font-size: .8rem; }
.status-label strong { font-size: .82rem; }
.status-dot { width: 9px; height: 9px; border-radius: 50%; }
.green { background: var(--success); }
.amber { background: var(--warning); }
.red { background: var(--danger); }
.blue { background: var(--blue); }
.progress-track { height: 7px; overflow: hidden; border-radius: 20px; background: var(--surface-muted); }
.progress-track span { display: block; height: 100%; min-width: 3px; border-radius: inherit; transition: width .25s ease; }
.empty-state { padding: 30px 8px; color: var(--text-muted); font-size: .86rem; text-align: center; }
.month-panel { display: flex; flex-direction: column; }
.month-count { display: flex; flex-direction: column; gap: 6px; margin: auto 0; padding: 28px 0; }
.month-count strong { color: var(--blue); font-size: clamp(2.7rem, 5vw, 4rem); letter-spacing: -.07em; line-height: 1; }
.month-count span { color: var(--text-secondary); font-size: .84rem; }
.month-foot { display: flex; align-items: center; gap: 9px; padding-top: 14px; border-top: 1px solid var(--line-subtle); color: var(--text-muted); font-size: .75rem; }
.month-mark { display: grid; width: 25px; height: 25px; place-items: center; border-radius: 50%; color: var(--success); background: var(--success-soft); font-weight: 850; }
.data-footnote { margin: 17px 2px 0; color: var(--text-muted); font-size: .75rem; line-height: 1.55; }
@media (max-width: 900px) { .metric-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } .overview-grid { grid-template-columns: 1fr; } }
@media (max-width: 580px) { .platform-content { width: min(100% - 28px, 1400px); padding-top: 24px; } .platform-topbar { padding-inline: 14px; gap: 9px; } .platform-brand { gap: 7px; } .platform-brand-mark { width: 34px; height: 34px; } .brand-copy small { display: none; } .platform-nav a { padding: 8px; font-size: .76rem; } .topbar-actions { gap: 8px; } .role-chip { display: none; } .logout-button { min-height: 35px; padding-inline: 10px; font-size: .78rem; } .page-heading { align-items: flex-start; flex-direction: column; } .metric-card { padding: 15px; } .metric-top { align-items: flex-start; } }
</style>
