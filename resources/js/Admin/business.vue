<template>
  <section class="business-page">
    <header class="page-heading">
      <div>
        <p class="eyebrow">BUSINESS SETTINGS</p>
        <h1>Pickup & contact details</h1>
        <p>These details appear to your renters during booking and on their receipts. The contact email receives subscription reminders.</p>
      </div>
    </header>

    <form class="settings-card" @submit.prevent="saveBusiness">
      <div class="business-name"><span class="business-mark">⌖</span><div><strong>{{ form.name || 'Rental business' }}</strong><small>One main pickup location</small></div></div>

      <label for="businessEmail">Subscription contact email</label>
      <input id="businessEmail" v-model.trim="form.contact_email" type="email" maxlength="255" required autocomplete="email" />
      <small class="field-help">Automated subscription due reminders will be sent here.</small>

      <label for="pickupAddress">Pickup address</label>
      <textarea id="pickupAddress" v-model.trim="form.pickup_address" maxlength="255" rows="3" required placeholder="Street, barangay, city, province" />

      <label for="pickupPhone">Pickup contact number</label>
      <input id="pickupPhone" v-model.trim="form.pickup_phone" type="tel" maxlength="50" required placeholder="+63..." />

      <label for="pickupInstructions">Pickup instructions <span>(optional)</span></label>
      <textarea id="pickupInstructions" v-model.trim="form.pickup_instructions" maxlength="2000" rows="4" placeholder="Landmarks, opening hours, or what renters should bring" />

      <p v-if="notice" class="notice success" role="status">{{ notice }}</p>
      <p v-if="error" class="notice error" role="alert">{{ error }}</p>
      <button class="save-button" type="submit" :disabled="saving">{{ saving ? 'Saving…' : 'Save business details' }}</button>
    </form>
  </section>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue'
import api from '@/api'

const form = reactive({ name: '', contact_email: '', pickup_address: '', pickup_phone: '', pickup_instructions: '' })
const saving = ref(false)
const notice = ref('')
const error = ref('')

const loadBusiness = async () => {
  try {
    const { data } = await api.get('/admin/business')
    Object.assign(form, data.business || {})
  } catch (err) {
    error.value = err.response?.data?.message || 'Could not load business details.'
  }
}

const saveBusiness = async () => {
  saving.value = true
  notice.value = ''
  error.value = ''
  try {
    const { data } = await api.put('/admin/business', form)
    Object.assign(form, data.business || {})
    notice.value = data.message || 'Business details saved.'
  } catch (err) {
    error.value = Object.values(err.response?.data?.errors || {})[0]?.[0] || err.response?.data?.message || 'Could not save business details.'
  } finally {
    saving.value = false
  }
}

onMounted(loadBusiness)
</script>

<style scoped>
.business-page { width:min(100%,1000px); margin:0 auto; padding:10px 0 36px; color:var(--text-primary); }
.page-heading { margin-bottom:24px; }
.eyebrow { margin:0 0 7px; color:var(--blue); font-size:.7rem; font-weight:800; letter-spacing:.13em; }
h1 { margin:0; font-size:clamp(1.7rem,3vw,2.3rem); letter-spacing:-.05em; }
.page-heading p:last-child { max-width:720px; margin:10px 0 0; color:var(--text-secondary); line-height:1.6; }
.settings-card { display:grid; gap:10px; padding:clamp(20px,4vw,34px); border:1px solid var(--line-subtle); border-radius:18px; background:var(--surface-card); box-shadow:var(--shadow-card); }
.business-name { display:flex; align-items:center; gap:12px; margin-bottom:16px; padding-bottom:18px; border-bottom:1px solid var(--line-subtle); }
.business-name div { display:grid; gap:4px; }
.business-name strong { font-size:1.05rem; }
.business-name small,.field-help,label span { color:var(--text-muted); font-size:.79rem; }
.business-mark { display:grid; width:44px; height:44px; place-items:center; border-radius:14px; color:#fff; background:var(--blue); font-size:1.3rem; }
label { margin-top:8px; color:var(--text-primary); font-size:.83rem; font-weight:750; }
input,textarea { width:100%; min-height:44px; padding:11px 13px; border:1px solid var(--line-subtle); border-radius:10px; color:var(--text-primary); background:var(--surface-raised); resize:vertical; }
.field-help { margin-top:-7px; }
.notice { margin:6px 0 0; font-size:.85rem; }
.success { color:var(--success); }
.error { color:var(--danger); }
.save-button { justify-self:start; min-height:44px; margin-top:8px; padding:0 18px; border:1px solid var(--blue); border-radius:10px; color:#fff; background:var(--blue); font-weight:750; }
.save-button:disabled { opacity:.6; cursor:wait; }
</style>
