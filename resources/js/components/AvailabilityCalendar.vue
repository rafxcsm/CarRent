<template>
  <section class="availability-calendar" aria-label="Vehicle availability calendar">
    <div class="calendar-heading">
      <button type="button" class="month-arrow" aria-label="Previous month" @click="moveMonth(-1)">‹</button>
      <strong>{{ monthTitle }}</strong>
      <button type="button" class="month-arrow" aria-label="Next month" @click="moveMonth(1)">›</button>
    </div>
    <p class="calendar-hint">
      {{ mode === 'admin'
        ? 'Select a future date to schedule or remove maintenance. Bookings that conflict will need a decision in Rentals.'
        : `Choose an available pickup date. Dates are clickable only when the full ${durationDays}-day rental is clear. The return date is shown in blue.` }}
    </p>
    <div class="calendar-grid weekday-row" aria-hidden="true">
      <span v-for="day in weekdays" :key="day">{{ day }}</span>
    </div>
    <div class="calendar-grid days-grid">
      <button
        v-for="day in days"
        :key="day.iso"
        type="button"
        class="calendar-day"
        :class="{
          outside: !day.inMonth,
          past: day.past,
          maintenance: day.entry?.type === 'maintenance',
          booked: day.entry?.type === 'booked',
          available: day.available,
          unavailable: day.unavailable,
          selected: day.selected,
          inRange: day.inSelectedRange && !day.selected,
          returnDay: day.isReturnDay,
          today: day.today,
        }"
        :disabled="day.disabled || loading || saving"
        :title="day.entry?.label || (day.past ? 'Past date' : day.available ? 'Available pickup date' : day.unavailable ? 'Unavailable for this rental length' : '')"
        @click="selectDay(day)"
      >
        <span>{{ day.number }}</span>
        <small v-if="day.entry">{{ day.entry.type === 'maintenance' ? 'Service' : 'Booked' }}</small>
        <small v-else-if="mode === 'user' && day.selected">Pickup</small>
        <small v-else-if="mode === 'user' && day.isReturnDay">Return</small>
        <small v-else-if="mode === 'user' && day.inSelectedRange">Day {{ day.rangeDay }}</small>
      </button>
    </div>
    <div class="calendar-legend">
      <span><i class="legend-dot available-dot"></i>Available pickup</span>
      <span><i class="legend-dot booked-dot"></i>Booked</span>
      <span><i class="legend-dot maintenance-dot"></i>Maintenance</span>
      <span v-if="mode === 'user'"><i class="legend-dot selected-dot"></i>Rental dates</span>
    </div>
    <p v-if="loading" class="calendar-message">Loading dates…</p>
    <p v-else-if="message" class="calendar-message" :class="{ error: isError }">{{ message }}</p>
  </section>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import api from '@/api'

const props = defineProps({
  vehicleId: { type: [Number, String], required: true },
  mode: { type: String, default: 'user' },
  vehicleStatus: { type: String, default: 'Available' },
  modelValue: { type: String, default: '' },
  durationDays: { type: Number, default: 1 },
})
const emit = defineEmits(['update:modelValue', 'availability-change'])

const today = new Date()
today.setHours(0, 0, 0, 0)
const month = ref(new Date(today.getFullYear(), today.getMonth(), 1))
const entries = ref([])
const loading = ref(false)
const saving = ref(false)
const message = ref('')
const isError = ref(false)
const weekdays = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']
let requestId = 0

const monthKey = computed(() => `${month.value.getFullYear()}-${String(month.value.getMonth() + 1).padStart(2, '0')}`)
const monthTitle = computed(() => month.value.toLocaleDateString('en', { month: 'long', year: 'numeric' }))
const firstGridDate = computed(() => {
  const first = new Date(month.value.getFullYear(), month.value.getMonth(), 1)
  const offset = (first.getDay() + 6) % 7
  first.setDate(first.getDate() - offset)
  return first
})
const requestRange = computed(() => ({
  from: formatIsoDate(firstGridDate.value),
  to: formatIsoDate(addDays(firstGridDate.value, 41 + Math.max(1, props.durationDays))),
}))
const entryMap = computed(() => {
  const map = new Map()
  entries.value.forEach((entry) => {
    const current = map.get(entry.date)
    if (!current || entry.type === 'maintenance') map.set(entry.date, entry)
  })
  return map
})

function addDays(date, amount) {
  const result = new Date(date)
  result.setDate(result.getDate() + Number(amount || 0))
  return result
}

function parseIsoDate(iso) {
  const [year, monthNumber, day] = iso.split('-').map(Number)
  return new Date(year, monthNumber - 1, day)
}

function formatIsoDate(date) {
  return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`
}

function canStartRental(iso) {
  if (!iso || isError.value || loading.value || (props.mode !== 'admin' && props.vehicleStatus !== 'Available')) return false
  const pickup = parseIsoDate(iso)
  const duration = Math.min(30, Math.max(1, Number(props.durationDays) || 1))

  // The date-only calendar reserves the return date too, so a same-day return
  // cannot be accidentally overlapped by another pickup at a different time.
  for (let offset = 0; offset <= duration; offset++) {
    if (entryMap.value.has(formatIsoDate(addDays(pickup, offset)))) return false
  }
  return true
}

const days = computed(() => Array.from({ length: 42 }, (_, index) => {
  const date = addDays(firstGridDate.value, index)
  const iso = formatIsoDate(date)
  const entry = entryMap.value.get(iso)
  const past = date < today
  const admin = props.mode === 'admin'
  const selectedRangeValid = !admin && canStartRental(props.modelValue)
  const selected = selectedRangeValid && props.modelValue === iso
  const returnDate = selectedRangeValid && props.modelValue
    ? formatIsoDate(addDays(parseIsoDate(props.modelValue), Math.min(30, Math.max(1, Number(props.durationDays) || 1))))
    : ''
  const inSelectedRange = !admin && props.modelValue && iso > props.modelValue && iso <= returnDate
  const available = !admin && !past && !entry && canStartRental(iso)

  return {
    iso,
    number: date.getDate(),
    inMonth: date.getMonth() === month.value.getMonth(),
    past,
    today: date.getTime() === today.getTime(),
    entry,
    selected,
    inSelectedRange,
    isReturnDay: !admin && iso === returnDate,
    rangeDay: props.modelValue && inSelectedRange
      ? Math.round((parseIsoDate(iso) - parseIsoDate(props.modelValue)) / 86400000)
      : null,
    available,
    unavailable: !admin && !past && !entry && !available,
    disabled: past || (admin ? false : !available),
  }
}))

const validSelection = computed(() => props.mode === 'admin' || canStartRental(props.modelValue))

const fetchEntries = async () => {
  const currentRequest = ++requestId
  const range = { ...requestRange.value }
  loading.value = true
  message.value = ''
  isError.value = false
  try {
    const prefix = props.mode === 'admin' ? '/admin' : '/user'
    const response = await api.get(`${prefix}/vehicles/${props.vehicleId}/availability`, { params: range })
    if (currentRequest === requestId) {
      const outsideRange = entries.value.filter((entry) => entry.date < range.from || entry.date > range.to)
      entries.value = [...outsideRange, ...(response.data.entries || [])]
    }
  } catch (error) {
    if (currentRequest === requestId) {
      message.value = error.response?.data?.message || 'Could not load availability.'
      isError.value = true
    }
  } finally {
    if (currentRequest === requestId) {
      loading.value = false
      emit('availability-change', Boolean(props.mode === 'admin' || (props.modelValue && validSelection.value)))
    }
  }
}

watch(() => [props.vehicleId, props.mode, monthKey.value, props.durationDays], fetchEntries, { immediate: true })
watch(() => [props.modelValue, props.durationDays, loading.value, validSelection.value], () => {
  emit('availability-change', Boolean(!loading.value && props.modelValue && validSelection.value))
}, { immediate: true })

const moveMonth = (amount) => {
  month.value = new Date(month.value.getFullYear(), month.value.getMonth() + amount, 1)
}

const selectDay = async (day) => {
  if (day.disabled || loading.value || saving.value) return
  if (props.mode !== 'admin') {
    emit('update:modelValue', day.iso)
    return
  }

  saving.value = true
  message.value = ''
  isError.value = false
  try {
    let successMessage = ''
    if (day.entry?.type === 'maintenance') {
      await api.delete(`/admin/vehicles/${props.vehicleId}/maintenance`, { params: { date: day.iso } })
      successMessage = `Maintenance removed for ${day.iso}.`
    } else {
      const response = await api.post(`/admin/vehicles/${props.vehicleId}/maintenance`, { date: day.iso })
      const conflicts = response.data.conflicts || []
      successMessage = conflicts.length
        ? `Maintenance scheduled. ${conflicts.length} booking${conflicts.length === 1 ? '' : 's'} need resolution in Rentals.`
        : `Maintenance scheduled for ${day.iso}.`
    }
    await fetchEntries()
    message.value = successMessage
  } catch (error) {
    const validationMessage = Object.values(error.response?.data?.errors || {})[0]?.[0]
    message.value = validationMessage || error.response?.data?.message || 'Could not update maintenance schedule.'
    isError.value = true
  } finally {
    saving.value = false
  }
}
</script>

<style scoped>
.availability-calendar { color: var(--text-primary, #152b45); }
.calendar-heading { display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:5px; }
.calendar-heading strong { font-size:1rem; }
.month-arrow { width:34px; height:34px; border:1px solid var(--line-subtle); border-radius:9px; background:var(--surface-card); color:var(--text-primary); font-size:1.45rem; cursor:pointer; }
.calendar-hint,.calendar-message { margin:6px 0 12px; color:var(--text-secondary); font-size:.82rem; line-height:1.45; }
.calendar-message.error { color:var(--danger); }
.calendar-grid { display:grid; grid-template-columns:repeat(7,minmax(0,1fr)); gap:5px; }
.weekday-row { margin-bottom:5px; text-align:center; color:var(--text-secondary); font-size:.72rem; font-weight:700; }
.calendar-day { min-height:48px; display:flex; flex-direction:column; align-items:center; justify-content:center; gap:1px; border:1px solid var(--line-subtle); border-radius:8px; background:var(--surface-card); color:var(--text-primary); cursor:pointer; }
.calendar-day small { font-size:.55rem; line-height:1; }
.calendar-day.available { border-color:var(--success); background:var(--success-soft); color:var(--success); }
.calendar-day:hover:not(:disabled) { border-color:var(--blue); box-shadow:var(--focus-ring); }
.calendar-day:disabled { cursor:not-allowed; }
.calendar-day.outside { opacity:.42; }
.calendar-day.past { opacity:.4; }
.calendar-day.today { outline:2px solid var(--blue); outline-offset:-2px; }
.calendar-day.booked { background:var(--danger-soft); color:var(--danger); border-color:var(--danger); }
.calendar-day.maintenance { background:var(--warning-soft); color:var(--warning); border-color:var(--warning); }
.calendar-day.unavailable { background:var(--surface-muted); color:var(--text-muted); }
.calendar-day.selected,.calendar-day.inRange { background:var(--blue-soft); color:var(--blue-ink); border-color:var(--blue); }
.calendar-day.selected { color:#fff; background:var(--blue); }
.calendar-day.returnDay { border-style:dashed; }
.calendar-legend { display:flex; flex-wrap:wrap; gap:12px; margin-top:13px; color:var(--text-secondary); font-size:.72rem; }
.calendar-legend span { display:flex; align-items:center; gap:5px; }
.legend-dot { width:9px; height:9px; display:inline-block; border-radius:50%; background:var(--success-soft); border:1px solid var(--success); }
.booked-dot { background:var(--danger-soft); border-color:var(--danger); }
.maintenance-dot { background:var(--warning-soft); border-color:var(--warning); }
.selected-dot { background:var(--blue); border-color:var(--blue); }
</style>
