<template>
  <div class="client-page">
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

    <main class="client-content">
      <section class="page-heading">
        <div>
          <p class="eyebrow">CLIENT MANAGEMENT</p>
          <h1>Rental businesses</h1>
          <p>Create client workspaces, review their rental activity, and manage renewals.</p>
        </div>
        <button class="primary-button" type="button" @click="startCreate">
          {{ showCreate ? 'Close form' : '+ Add client' }}
        </button>
      </section>

      <section v-if="editingClient" class="create-panel panel">
        <div class="panel-heading"><div><p class="eyebrow">CLIENT SETTINGS</p><h2>Edit {{ editingClient.name }}</h2></div></div>
        <form class="client-form compact-form" @submit.prevent="saveClient">
          <label>Business name<input v-model.trim="editForm.name" required maxlength="160" /></label>
          <label>Client code<input v-model.trim="editForm.slug" required maxlength="80" pattern="[A-Za-z0-9_-]+" /></label>
          <label>Contact email<input v-model.trim="editForm.contact_email" type="email" maxlength="255" /></label>
          <label>Plan name<input v-model.trim="editForm.plan" required maxlength="80" /></label>
          <label>Monthly price (PHP)<input v-model.number="editForm.monthly_fee" type="number" min="0" step="0.01" required /></label>
          <div class="form-actions">
            <button class="secondary-button" type="button" @click="editingClient = null">Cancel</button>
            <button class="primary-button" type="submit" :disabled="saving">{{ saving ? 'Saving…' : 'Save changes' }}</button>
          </div>
        </form>
      </section>

      <div v-if="notice" class="notice success-notice" role="status">{{ notice }}</div>
      <div v-if="error" class="notice error-notice" role="alert">{{ error }}</div>

      <section v-if="showCreate" class="create-panel panel">
        <div class="panel-heading">
          <div><p class="eyebrow">NEW WORKSPACE</p><h2>Create a rental business</h2></div>
          <span class="plan-chip">One month to start</span>
        </div>
        <p class="helper-copy">This creates the client and its first rental admin. Share the selected client code and admin login with the business securely.</p>

        <form class="client-form" @submit.prevent="createClient">
          <div class="form-section-title">Business details</div>
          <label>Business name<input v-model.trim="form.name" required maxlength="160" placeholder="Example Car Rental" /></label>
          <label>Client code <small>Leave blank to generate from the business name.</small><input v-model.trim="form.slug" maxlength="80" pattern="[A-Za-z0-9_-]+" placeholder="example-car-rental" /></label>
          <label>Contact email<input v-model.trim="form.contact_email" type="email" required maxlength="255" placeholder="contact@example.com" /></label>
          <label>Plan name<input v-model.trim="form.plan" required maxlength="80" placeholder="Monthly" /></label>
          <label>Monthly price (PHP)<input v-model.number="form.monthly_fee" type="number" min="0" step="0.01" required /></label>

          <div class="form-section-title admin-section-title">First rental admin</div>
          <label>Admin name<input v-model.trim="form.admin_name" required maxlength="160" placeholder="Business administrator" /></label>
          <label>Admin email<input v-model.trim="form.admin_email" type="email" required maxlength="255" placeholder="admin@example.com" /></label>
          <label>Temporary password<input v-model="form.admin_password" type="password" required minlength="12" autocomplete="new-password" placeholder="At least 12 characters" /></label>
          <label>Confirm password<input v-model="form.admin_password_confirmation" type="password" required minlength="12" autocomplete="new-password" placeholder="Enter it again" /></label>

          <div class="form-actions">
            <button class="secondary-button" type="button" @click="showCreate = false">Cancel</button>
            <button class="primary-button" type="submit" :disabled="saving">{{ saving ? 'Creating…' : 'Create client' }}</button>
          </div>
        </form>
      </section>

      <section class="panel client-list-panel">
        <div class="list-heading">
          <div><p class="eyebrow">SUBSCRIPTIONS AND ACTIVITY</p><h2>All clients <span class="count-bubble">{{ filteredClients.length }}</span></h2></div>
          <label class="search-box"><span class="sr-only">Search clients</span><input v-model="search" type="search" placeholder="Search business or client code" /></label>
        </div>

        <div v-if="loading" class="empty-state">Loading client accounts…</div>
        <div v-else-if="!filteredClients.length" class="empty-state">{{ clients.length ? 'No clients match your search.' : 'No client businesses yet. Add the first client to begin.' }}</div>
        <div v-else class="table-scroll">
          <table>
            <thead><tr><th>Business</th><th>Subscription</th><th>Rental activity</th><th>Monthly price</th><th>Actions</th></tr></thead>
            <tbody>
              <template v-for="client in filteredClients" :key="client.id">
              <tr>
                <td>
                  <strong class="business-name">{{ client.name }}</strong>
                  <span class="client-code">Code: {{ client.slug }}</span>
                  <span class="client-code">{{ client.contact_email || 'No contact email' }}</span>
                </td>
                <td>
                  <span class="subscription-badge" :class="`state-${client.subscription_status}`">{{ statusLabel(client.subscription_status) }}</span>
                  <span class="client-code">Due {{ formatDate(client.subscription_due_at) }}</span>
                  <span v-if="client.grace_ends_at && client.subscription_status === 'past_due'" class="grace-copy">Grace ends {{ formatDate(client.grace_ends_at) }}</span>
                  <span v-if="client.last_paid_at" class="client-code">Paid {{ formatDate(client.last_paid_at) }}</span>
                  <span v-if="client.latest_payment" class="client-code">Last payment {{ formatMoney(client.latest_payment.amount) }}<template v-if="client.latest_payment.reference"> · Ref {{ client.latest_payment.reference }}</template></span>
                </td>
                <td>
                  <div class="activity-counts"><span><strong>{{ client.vehicles_count || 0 }}</strong> vehicles</span><span><strong>{{ client.users_count || 0 }}</strong> customers</span><span><strong>{{ client.rentals_count || 0 }}</strong> rentals</span><span><strong>{{ client.rentals_this_month_count || 0 }}</strong> this month</span></div>
                  <div class="rental-statuses"><span v-for="(count, status) in client.rental_statuses" :key="status">{{ statusLabel(status) }} {{ count }}</span></div>
                  <span class="client-code">{{ client.admin_accounts_count || 0 }} rental admin account(s)</span>
                </td>
                <td class="price-cell">{{ formatMoney(client.monthly_fee) }}<small>/ month · {{ client.plan }}</small></td>
                <td>
                  <div class="row-actions">
                    <button type="button" class="small-button edit-button" :disabled="busyClient === client.id" @click="startEdit(client)">Edit</button>
                    <button type="button" class="small-button report-button" :disabled="reportLoadingClientId === client.id" @click="loadReport(client)">{{ reportLoadingClientId === client.id ? 'Loading…' : selectedReport?.client?.id === client.id ? 'Hide report' : 'View report' }}</button>
                    <button type="button" class="small-button pay-button" :disabled="busyClient === client.id" @click="changeSubscription(client, 'mark_paid')">Record payment</button>
                    <button v-if="client.subscription_status !== 'suspended'" type="button" class="small-button suspend-button" :disabled="busyClient === client.id" @click="changeSubscription(client, 'suspend')">Suspend</button>
                  </div>
                </td>
              </tr>
              <tr v-if="selectedReport?.client?.id === client.id || reportLoadingClientId === client.id" class="report-table-row">
                <td colspan="5">
                  <div v-if="reportLoadingClientId === client.id" class="report-loading">Loading {{ client.name }} report…</div>
                  <section v-else-if="selectedReport" class="client-report" :aria-label="`${client.name} rental report`">
                    <div class="report-heading"><div><p class="eyebrow">CLIENT REPORT</p><h3>{{ selectedReport.client.name }} rental activity</h3></div><span class="report-updated">Showing latest {{ selectedReport.transactions.length }} transactions</span></div>
                    <div class="report-metrics">
                      <span><strong>{{ selectedReport.client.counts.customers }}</strong> customers</span>
                      <span><strong>{{ selectedReport.client.counts.vehicles }}</strong> vehicles</span>
                      <span><strong>{{ selectedReport.client.counts.rentals }}</strong> rentals</span>
                      <span><strong>{{ selectedReport.client.counts.rentals_this_month }}</strong> this month</span>
                    </div>
                    <div class="report-statuses"><span v-for="(count, status) in selectedReport.client.rental_statuses" :key="status">{{ statusLabel(status) }}: {{ count }}</span></div>
                    <div v-if="selectedReport.transactions.length" class="report-transactions">
                      <table><thead><tr><th>Customer</th><th>Vehicle</th><th>Pickup</th><th>Status</th><th>Submitted</th></tr></thead>
                        <tbody><tr v-for="transaction in selectedReport.transactions" :key="transaction.id"><td>{{ transaction.customer }}</td><td>{{ transaction.vehicle || 'Vehicle removed' }}</td><td>{{ formatDateTime(transaction.rental_start) }}</td><td><span class="subscription-badge" :class="`state-${statusTone(transaction.status)}`">{{ statusLabel(transaction.status) }}</span></td><td>{{ formatDateTime(transaction.created_at) }}</td></tr></tbody>
                      </table>
                    </div>
                    <div v-else class="empty-report">This client has no rental transactions yet.</div>
                  </section>
                </td>
              </tr>
              </template>
            </tbody>
          </table>
        </div>
      </section>

      <p class="footnote">Overdue clients receive a seven-day grace period. After that, customer and rental-admin accounts can still sign in and view existing records, but changes and new bookings are blocked until payment is recorded.</p>
    </main>
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/api'
import ThemeToggle from '@/components/ThemeToggle.vue'

const router = useRouter()
const clients = ref([])
const loading = ref(true)
const saving = ref(false)
const busyClient = ref(null)
const reportLoadingClientId = ref(null)
const selectedReport = ref(null)
const showCreate = ref(false)
const search = ref('')
const error = ref('')
const notice = ref('')
const form = reactive({
  name: '', slug: '', contact_email: '', plan: 'Monthly', monthly_fee: 0,
  admin_name: '', admin_email: '', admin_password: '', admin_password_confirmation: '',
})
const editingClient = ref(null)
const editForm = reactive({ name: '', slug: '', contact_email: '', plan: '', monthly_fee: 0 })

const filteredClients = computed(() => {
  const term = search.value.trim().toLowerCase()
  if (!term) return clients.value
  return clients.value.filter((client) => `${client.name} ${client.slug} ${client.contact_email || ''}`.toLowerCase().includes(term))
})

const formatMoney = (value) => new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(Number(value || 0))
const formatDate = (value) => value ? new Intl.DateTimeFormat(undefined, { dateStyle: 'medium' }).format(new Date(value)) : 'Not set'
const formatDateTime = (value) => value ? new Intl.DateTimeFormat(undefined, { dateStyle: 'medium', timeStyle: 'short' }).format(new Date(value)) : '—'
const statusLabel = (value) => ({ active: 'Active', past_due: 'Past due · grace period', suspended: 'Suspended' }[value] || String(value).replaceAll('_', ' ').replace(/\b\w/g, (letter) => letter.toUpperCase()))
const statusTone = (value) => ['approved', 'completed', 'returned'].includes(String(value).toLowerCase()) ? 'active' : ['denied', 'cancelled'].includes(String(value).toLowerCase()) ? 'suspended' : String(value).toLowerCase() === 'pending' ? 'past_due' : 'active'

const loadClients = async () => {
  loading.value = true
  error.value = ''
  try {
    const { data } = await api.get('/super-admin/clients')
    clients.value = data.clients || []
  } catch (err) {
    error.value = err.response?.data?.message || 'Could not load client accounts.'
  } finally {
    loading.value = false
  }
}

const loadReport = async (client) => {
  if (selectedReport.value?.client?.id === client.id) {
    selectedReport.value = null
    return
  }
  reportLoadingClientId.value = client.id
  error.value = ''
  try {
    const { data } = await api.get(`/super-admin/clients/${client.id}/report`)
    selectedReport.value = data
  } catch (err) {
    error.value = err.response?.data?.message || 'Could not load this client report.'
  } finally {
    reportLoadingClientId.value = null
  }
}

const clearForm = () => {
  Object.assign(form, { name: '', slug: '', contact_email: '', plan: 'Monthly', monthly_fee: 0, admin_name: '', admin_email: '', admin_password: '', admin_password_confirmation: '' })
}

const startCreate = () => {
  editingClient.value = null
  if (showCreate.value) {
    showCreate.value = false
    return
  }
  clearForm()
  showCreate.value = true
}

const startEdit = (client) => {
  showCreate.value = false
  editingClient.value = client
  Object.assign(editForm, {
    name: client.name,
    slug: client.slug,
    contact_email: client.contact_email || '',
    plan: client.plan,
    monthly_fee: Number(client.monthly_fee || 0),
  })
  error.value = ''
  notice.value = ''
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

const saveClient = async () => {
  if (!editingClient.value) return
  saving.value = true
  error.value = ''
  notice.value = ''
  try {
    const { data } = await api.put(`/super-admin/clients/${editingClient.value.id}`, editForm)
    notice.value = data.message
    editingClient.value = null
    await loadClients()
  } catch (err) {
    const firstError = Object.values(err.response?.data?.errors || {})[0]
    error.value = firstError?.[0] || err.response?.data?.message || 'Could not update the client.'
  } finally {
    saving.value = false
  }
}

const createClient = async () => {
  saving.value = true
  error.value = ''
  notice.value = ''
  try {
    const { data } = await api.post('/super-admin/clients', form)
    notice.value = `${data.client.name} was created. Client code: ${data.client.slug}. Rental admin: ${data.rental_admin.email}. Share the code and login details with the client securely.`
    clearForm()
    showCreate.value = false
    await loadClients()
  } catch (err) {
    const firstError = Object.values(err.response?.data?.errors || {})[0]
    error.value = firstError?.[0] || err.response?.data?.message || 'Could not create the client.'
  } finally {
    saving.value = false
  }
}

const changeSubscription = async (client, action) => {
  const prompt = action === 'mark_paid'
    ? `Record this month's payment for ${client.name}? This adds one month to the current due date.`
    : `Suspend ${client.name} now? Its customers and admin will have read-only access.`
  if (!window.confirm(prompt)) return
  const reference = action === 'mark_paid'
    ? window.prompt('Optional payment reference (leave blank if none):', '')
    : null
  if (reference === null && action === 'mark_paid') return

  busyClient.value = client.id
  error.value = ''
  notice.value = ''
  try {
    const { data } = await api.put(`/super-admin/clients/${client.id}/subscription`, { action, reference })
    notice.value = data.message
    await loadClients()
  } catch (err) {
    error.value = err.response?.data?.message || 'Could not update the subscription.'
  } finally {
    busyClient.value = null
  }
}

const logout = async () => {
  try { await api.post('/super-admin/logout') } catch (err) { /* expire the local session either way */ }
  sessionStorage.removeItem('super_admin_token')
  sessionStorage.removeItem('super_admin')
  await router.replace('/super-admin/login')
}

onMounted(loadClients)
</script>

<style scoped>
.client-page { min-height: 100vh; padding-top: var(--app-header-height, 76px); color: var(--text-primary); background: var(--surface-page); }
.platform-topbar { position: fixed; z-index: 30; inset: 0 0 auto; display: flex; height: var(--app-header-height, 76px); align-items: center; gap: 20px; padding: 0 clamp(18px, 4vw, 54px); border-bottom: 1px solid var(--line-subtle); background: color-mix(in srgb, var(--surface-card) 95%, transparent); box-shadow: var(--shadow-card); backdrop-filter: blur(14px); }
.platform-brand { display: inline-flex; min-width: max-content; align-items: center; gap: 11px; color: var(--text-primary); text-decoration: none; }
.platform-brand-mark { display: grid; width: 40px; height: 40px; place-items: center; border-radius: 13px; color: #fff; background: linear-gradient(145deg, var(--blue), #78aaff); font-weight: 850; }
.brand-copy { display: grid; gap: 2px; }.brand-copy strong { font-size: 1rem; }.brand-copy small { color: var(--text-muted); font-size: .65rem; letter-spacing: .1em; text-transform: uppercase; }
.platform-nav { display: flex; align-items: center; gap: 4px; }.platform-nav a { padding: 9px 12px; border-radius: 9px; color: var(--text-secondary); font-size: .86rem; font-weight: 700; text-decoration: none; }.platform-nav a:hover, .platform-nav a.router-link-active { color: var(--blue-ink); background: var(--blue-soft); }
.topbar-actions { display: flex; align-items: center; gap: 15px; margin-left: auto; }.role-chip { display: inline-flex; align-items: center; gap: 8px; padding: 8px 12px; border: 1px solid var(--line-subtle); border-radius: 999px; color: var(--text-secondary); font-size: .8rem; font-weight: 700; }.role-chip i { width: 8px; height: 8px; border-radius: 50%; background: var(--blue); }
.logout-button, .secondary-button { min-height: 38px; padding: 0 13px; border: 1px solid var(--line-subtle); border-radius: 10px; color: var(--text-primary); background: var(--surface-card); font-weight: 700; cursor: pointer; }
.client-content { width: min(100% - 48px, 1450px); margin: 0 auto; padding: clamp(24px, 4vw, 46px) 0 42px; }
.page-heading, .list-heading, .panel-heading { display: flex; align-items: flex-end; justify-content: space-between; gap: 18px; }.page-heading { margin-bottom: 20px; }
.eyebrow { margin: 0 0 7px; color: var(--blue); font-size: .68rem; font-weight: 850; letter-spacing: .14em; }.page-heading h1 { margin: 0; font-size: clamp(1.8rem, 3vw, 2.5rem); letter-spacing: -.055em; }.page-heading p:not(.eyebrow) { margin: 8px 0 0; color: var(--text-secondary); }
.primary-button { min-height: 42px; padding: 0 16px; border: 0; border-radius: 10px; color: #fff; background: var(--blue); font-weight: 750; cursor: pointer; }.primary-button:hover { background: var(--blue-hover); }.primary-button:disabled { opacity: .6; cursor: wait; }
.panel { border: 1px solid var(--line-subtle); border-radius: 18px; background: var(--surface-card); box-shadow: var(--shadow-card); }.create-panel { margin-bottom: 18px; padding: clamp(18px, 3vw, 28px); }.panel-heading { align-items: flex-start; margin-bottom: 10px; }.panel h2 { margin: 0; font-size: 1.12rem; }.plan-chip, .count-bubble { padding: 7px 10px; border: 1px solid var(--line-subtle); border-radius: 999px; color: var(--text-secondary); background: var(--surface-raised); font-size: .72rem; font-weight: 700; }.helper-copy { margin: 0 0 20px; color: var(--text-secondary); font-size: .82rem; line-height: 1.5; }
.client-form { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; }.form-section-title { grid-column: 1 / -1; padding-top: 10px; border-top: 1px solid var(--line-subtle); color: var(--text-primary); font-size: .88rem; font-weight: 800; }.client-form > label { display: grid; gap: 6px; color: var(--text-secondary); font-size: .76rem; font-weight: 700; }.client-form label small { color: var(--text-muted); font-weight: 500; }.client-form input { width: 100%; min-height: 42px; padding: 0 11px; border: 1px solid var(--line-subtle); border-radius: 9px; color: var(--text-primary); background: var(--surface-raised); }.admin-section-title { margin-top: 4px; }.form-actions { display: flex; grid-column: 1 / -1; justify-content: flex-end; gap: 10px; padding-top: 5px; }.form-actions .secondary-button { min-height: 42px; }.form-actions .primary-button { min-width: 140px; }.compact-form { max-width: 900px; }
.notice { margin: 0 0 15px; padding: 12px 15px; border: 1px solid var(--line-subtle); border-radius: 12px; font-size: .83rem; line-height: 1.5; }.success-notice { border-color: var(--blue-border); color: var(--blue-ink); background: var(--blue-soft); }.error-notice { border-color: var(--danger); color: var(--danger); background: var(--danger-soft); }
.client-list-panel { padding: clamp(16px, 2.5vw, 24px); }.list-heading { align-items: center; margin-bottom: 16px; }.list-heading h2 { display: flex; align-items: center; gap: 9px; margin: 0; font-size: 1.13rem; }.count-bubble { padding: 4px 8px; }.search-box input { width: min(100%, 320px); min-height: 40px; padding: 0 12px; border: 1px solid var(--line-subtle); border-radius: 10px; color: var(--text-primary); background: var(--surface-raised); }
.table-scroll { overflow-x: auto; }table { width: 100%; border-collapse: collapse; text-align: left; }th { padding: 11px 12px; border-bottom: 1px solid var(--line-subtle); color: var(--text-muted); font-size: .67rem; letter-spacing: .08em; text-transform: uppercase; white-space: nowrap; }td { padding: 15px 12px; border-bottom: 1px solid var(--line-subtle); vertical-align: top; font-size: .82rem; }tbody tr:last-child td { border-bottom: 0; }
.business-name { display: block; color: var(--text-primary); font-size: .9rem; }.client-code { display: block; margin-top: 5px; color: var(--text-muted); font-size: .71rem; }.subscription-badge { display: inline-flex; padding: 6px 9px; border-radius: 999px; font-size: .7rem; font-weight: 800; white-space: nowrap; }.state-active { color: var(--success); background: var(--success-soft); }.state-past_due { color: var(--warning); background: var(--warning-soft); }.state-suspended { color: var(--danger); background: var(--danger-soft); }.grace-copy { display: block; margin-top: 5px; color: var(--warning); font-size: .7rem; }
.activity-counts { display: grid; gap: 5px; color: var(--text-secondary); font-size: .75rem; }.activity-counts strong { color: var(--text-primary); }.rental-statuses { display: flex; flex-wrap: wrap; gap: 4px; margin-top: 7px; }.rental-statuses span { padding: 3px 6px; border-radius: 999px; color: var(--text-muted); background: var(--surface-muted); font-size: .64rem; }.price-cell { font-weight: 800; white-space: nowrap; }.price-cell small { display: block; margin-top: 5px; color: var(--text-muted); font-size: .7rem; font-weight: 500; }.row-actions { display: flex; flex-wrap: wrap; gap: 6px; }.small-button { min-height: 31px; padding: 0 9px; border: 1px solid var(--line-subtle); border-radius: 8px; background: var(--surface-card); font-size: .7rem; font-weight: 750; cursor: pointer; white-space: nowrap; }.small-button:disabled { opacity: .5; cursor: wait; }.pay-button { color: var(--blue-ink); border-color: var(--blue-border); background: var(--blue-soft); }.edit-button { color: var(--text-secondary); }.suspend-button { color: var(--danger); }.empty-state { padding: 42px 12px; color: var(--text-muted); font-size: .87rem; text-align: center; }.footnote { margin: 14px 2px 0; color: var(--text-muted); font-size: .75rem; line-height: 1.55; }.sr-only { position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0, 0, 0, 0); white-space: nowrap; border: 0; }
.activity-counts { display: grid; gap: 5px; color: var(--text-secondary); font-size: .75rem; }.activity-counts strong { color: var(--text-primary); }.rental-statuses { display: flex; flex-wrap: wrap; gap: 4px; margin-top: 7px; }.rental-statuses span { padding: 3px 6px; border-radius: 999px; color: var(--text-muted); background: var(--surface-muted); font-size: .64rem; }.price-cell { font-weight: 800; white-space: nowrap; }.price-cell small { display: block; margin-top: 5px; color: var(--text-muted); font-size: .7rem; font-weight: 500; }.row-actions { display: flex; flex-wrap: wrap; gap: 6px; }.small-button { min-height: 31px; padding: 0 9px; border: 1px solid var(--line-subtle); border-radius: 8px; background: var(--surface-card); font-size: .7rem; font-weight: 750; cursor: pointer; white-space: nowrap; }.small-button:disabled { opacity: .5; cursor: wait; }.pay-button { color: var(--blue-ink); border-color: var(--blue-border); background: var(--blue-soft); }.edit-button { color: var(--text-secondary); }.report-button { color: var(--blue-ink); }.suspend-button { color: var(--danger); }.empty-state { padding: 42px 12px; color: var(--text-muted); font-size: .87rem; text-align: center; }.footnote { margin: 14px 2px 0; color: var(--text-muted); font-size: .75rem; line-height: 1.55; }.sr-only { position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0, 0, 0, 0); white-space: nowrap; border: 0; }
.report-table-row > td { padding: 0; background: var(--surface-raised); }
.client-report { padding: 18px; border-bottom: 1px solid var(--line-subtle); }
.report-heading { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; }
.report-heading h3 { margin: 0; color: var(--text-primary); font-size: .98rem; }
.report-updated { color: var(--text-muted); font-size: .7rem; }
.report-metrics { display: flex; flex-wrap: wrap; gap: 8px; margin: 14px 0 7px; }
.report-metrics span, .report-statuses span { padding: 7px 9px; border: 1px solid var(--line-subtle); border-radius: 9px; color: var(--text-secondary); background: var(--surface-card); font-size: .72rem; }
.report-metrics strong { color: var(--text-primary); }
.report-statuses { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 8px; }
.report-transactions { overflow-x: auto; }
.report-transactions table { min-width: 720px; margin-top: 10px; background: var(--surface-card); }
.report-transactions th, .report-transactions td { padding: 9px 10px; font-size: .72rem; }
.report-loading, .empty-report { padding: 20px; color: var(--text-muted); text-align: center; }
@media (max-width: 700px) { .platform-topbar { padding-inline: 12px; gap: 8px; }.platform-brand { gap: 7px; }.platform-brand-mark { width: 34px; height: 34px; }.brand-copy small, .role-chip { display: none; }.platform-nav a { padding: 8px; font-size: .75rem; }.topbar-actions { gap: 7px; }.logout-button { min-height: 35px; padding-inline: 9px; font-size: .75rem; }.client-content { width: min(100% - 28px, 1450px); }.page-heading { align-items: flex-start; flex-direction: column; }.client-form { grid-template-columns: 1fr; }.client-form > label, .form-section-title, .form-actions { grid-column: 1; }.list-heading { align-items: flex-start; flex-direction: column; }.search-box, .search-box input { width: 100%; } }
</style>
