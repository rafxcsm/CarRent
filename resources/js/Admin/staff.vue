<template>
  <section class="staff-page">
    <header class="page-heading">
      <div><p class="eyebrow">TEAM ACCESS</p><h1>Staff accounts</h1><p>Staff can view the dashboard and manage rental requests. They cannot manage vehicles, customer records, business settings, or staff accounts.</p></div>
      <button class="primary-button" type="button" @click="showCreate = !showCreate">{{ showCreate ? 'Close form' : '+ Add staff' }}</button>
    </header>

    <form v-if="showCreate" class="staff-form panel" @submit.prevent="createStaff">
      <h2>Create staff account</h2>
      <div class="form-grid">
        <label>Staff name<input v-model.trim="form.name" required maxlength="160" /></label>
        <label>Staff email<input v-model.trim="form.email" type="email" required maxlength="255" autocomplete="email" /></label>
        <label>Password<input v-model="form.password" type="password" required minlength="12" autocomplete="new-password" /></label>
        <label>Confirm password<input v-model="form.password_confirmation" type="password" required minlength="12" autocomplete="new-password" /></label>
      </div>
      <p class="form-help">Use at least 12 characters. Share the credentials with the staff member securely.</p>
      <button class="primary-button" type="submit" :disabled="saving">{{ saving ? 'Creating…' : 'Create account' }}</button>
    </form>

    <p v-if="notice" class="notice success" role="status">{{ notice }}</p>
    <p v-if="error" class="notice error" role="alert">{{ error }}</p>

    <section class="panel staff-list">
      <div class="list-heading"><div><p class="eyebrow">YOUR BUSINESS</p><h2>Staff members <span>{{ staff.length }}</span></h2></div><button class="refresh-button" type="button" :disabled="loading" @click="loadStaff">{{ loading ? 'Loading…' : 'Refresh' }}</button></div>
      <div v-if="loading" class="empty-state">Loading staff accounts…</div>
      <div v-else-if="!staff.length" class="empty-state">No staff accounts yet. Add an account for a team member who handles rentals.</div>
      <div v-else class="table-scroll"><table><thead><tr><th>Name</th><th>Email</th><th>Status</th><th>Action</th></tr></thead><tbody>
        <tr v-for="member in staff" :key="member.id"><td><strong>{{ member.name }}</strong></td><td>{{ member.email }}</td><td><span class="status" :class="member.is_active ? 'active' : 'inactive'">{{ member.is_active ? 'Active' : 'Disabled' }}</span></td><td><button class="status-button" :class="member.is_active ? 'disable' : 'enable'" :disabled="busyId === member.id" @click="toggleStaff(member)">{{ busyId === member.id ? 'Saving…' : member.is_active ? 'Disable account' : 'Enable account' }}</button></td></tr>
      </tbody></table></div>
    </section>
  </section>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue'
import api from '@/api'

const staff = ref([])
const showCreate = ref(false)
const loading = ref(true)
const saving = ref(false)
const busyId = ref(null)
const error = ref('')
const notice = ref('')
const form = reactive({ name: '', email: '', password: '', password_confirmation: '' })

const loadStaff = async () => {
  loading.value = true
  error.value = ''
  try {
    const { data } = await api.get('/admin/staff')
    staff.value = data.staff || []
  } catch (err) {
    error.value = err.response?.data?.message || 'Could not load staff accounts.'
  } finally {
    loading.value = false
  }
}

const createStaff = async () => {
  saving.value = true
  error.value = ''
  notice.value = ''
  try {
    const { data } = await api.post('/admin/staff', form)
    notice.value = data.message || 'Staff account created.'
    Object.assign(form, { name: '', email: '', password: '', password_confirmation: '' })
    showCreate.value = false
    await loadStaff()
  } catch (err) {
    error.value = Object.values(err.response?.data?.errors || {})[0]?.[0] || err.response?.data?.message || 'Could not create staff account.'
  } finally {
    saving.value = false
  }
}

const toggleStaff = async (member) => {
  busyId.value = member.id
  error.value = ''
  notice.value = ''
  try {
    const { data } = await api.put(`/admin/staff/${member.id}`, { is_active: !member.is_active })
    Object.assign(member, data.staff)
    notice.value = data.message || 'Staff account updated.'
  } catch (err) {
    error.value = err.response?.data?.message || 'Could not update the staff account.'
  } finally {
    busyId.value = null
  }
}

onMounted(loadStaff)
</script>

<style scoped>
.staff-page { width:min(100%,1400px); margin:0 auto; padding:10px 0 36px; color:var(--text-primary); }
.page-heading,.list-heading { display:flex; align-items:center; justify-content:space-between; gap:18px; }
.page-heading { margin-bottom:24px; }
.eyebrow { margin:0 0 7px; color:var(--blue); font-size:.7rem; font-weight:800; letter-spacing:.13em; }
h1 { margin:0; font-size:clamp(1.7rem,3vw,2.3rem); letter-spacing:-.05em; }
.page-heading p:last-child { max-width:780px; margin:10px 0 0; color:var(--text-secondary); line-height:1.6; }
.primary-button,.refresh-button,.status-button { min-height:40px; padding:0 14px; border:1px solid var(--line-subtle); border-radius:10px; color:var(--text-primary); background:var(--surface-card); font-weight:750; cursor:pointer; white-space:nowrap; }
.primary-button { border-color:var(--blue); color:#fff; background:var(--blue); }
.primary-button:disabled,.refresh-button:disabled,.status-button:disabled { opacity:.58; cursor:wait; }
.panel { border:1px solid var(--line-subtle); border-radius:17px; background:var(--surface-card); box-shadow:var(--shadow-card); }
.staff-form { margin-bottom:18px; padding:24px; }
.staff-form h2,.list-heading h2 { margin:0; font-size:1.1rem; }
.form-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:14px; margin:16px 0 8px; }
label { display:grid; gap:6px; color:var(--text-secondary); font-size:.8rem; font-weight:700; }
input { width:100%; min-height:44px; padding:0 12px; border:1px solid var(--line-subtle); border-radius:10px; color:var(--text-primary); background:var(--surface-raised); }
.form-help { margin:8px 0 16px; color:var(--text-muted); font-size:.8rem; }
.notice { margin:10px 0; font-size:.85rem; }
.success { color:var(--success); }
.error { color:var(--danger); }
.staff-list { padding:22px; }
.list-heading { margin-bottom:18px; }
.list-heading h2 span { display:inline-grid; min-width:24px; height:24px; place-items:center; margin-left:5px; border-radius:99px; color:var(--blue-ink); background:var(--blue-soft); font-size:.72rem; vertical-align:2px; }
.table-scroll { overflow:auto; }
table { width:100%; border-collapse:collapse; }
th,td { padding:13px 12px; border-bottom:1px solid var(--line-subtle); text-align:left; font-size:.84rem; }
th { color:var(--text-muted); font-size:.72rem; letter-spacing:.04em; text-transform:uppercase; }
.status { display:inline-block; padding:5px 9px; border-radius:999px; font-size:.73rem; font-weight:800; }
.status.active { color:var(--success); background:var(--success-soft); }
.status.inactive { color:var(--danger); background:var(--danger-soft); }
.disable { color:var(--danger); border-color:var(--danger); }
.enable { color:var(--success); border-color:var(--success); }
.empty-state { padding:35px 18px; color:var(--text-muted); text-align:center; }
@media(max-width:650px) { .page-heading { align-items:flex-start; flex-direction:column; }.form-grid { grid-template-columns:1fr; }.staff-list { padding:16px; } }
</style>
