<template>
  <div ref="root" class="notification-root">
    <button
      class="notification-trigger"
      type="button"
      aria-label="Notifications"
      :aria-expanded="isOpen"
      @click.stop="togglePanel"
    >
      <svg viewBox="0 0 24 24" aria-hidden="true">
        <path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4" />
      </svg>
      <span v-if="unreadCount" class="notification-count">{{ unreadCount > 99 ? '99+' : unreadCount }}</span>
    </button>

    <section v-if="isOpen" class="notification-panel" aria-label="Notifications">
      <header class="notification-panel-header">
        <div>
          <h2>Notifications</h2>
          <p>{{ unreadCount ? `${unreadCount} unread` : 'You’re all caught up' }}</p>
        </div>
        <button
          v-if="unreadCount"
          class="mark-all-button"
          type="button"
          @click="markAllRead"
        >Mark all read</button>
      </header>

      <p v-if="loadError" class="notification-error">Couldn’t load notifications. Please try again.</p>
      <div v-else-if="!items.length" class="notification-empty">
        New booking and account updates will appear here.
      </div>
      <div v-else class="notification-list">
        <button
          v-for="item in items"
          :key="item.id"
          class="notification-item"
          :class="{ unread: !item.read_at }"
          type="button"
          @click="openNotification(item)"
        >
          <span class="notification-item-dot" :class="`kind-${item.data?.type || 'general'}`"></span>
          <span class="notification-item-copy">
            <strong>{{ item.data?.title || 'Update' }}</strong>
            <span>{{ item.data?.message || '' }}</span>
            <small>{{ formatDate(item.created_at) }}</small>
          </span>
        </button>
      </div>
    </section>

    <Transition name="notification-toast">
      <div v-if="toast" class="notification-toast-card" role="status">
        <span class="toast-mark">✓</span>
        <span><strong>{{ toast.title }}</strong><small>{{ toast.message }}</small></span>
        <button type="button" aria-label="Dismiss notification" @click="toast = null">×</button>
      </div>
    </Transition>
  </div>
</template>

<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/api'

const props = defineProps({
  audience: { type: String, required: true, validator: value => ['admin', 'user'].includes(value) },
})

const router = useRouter()
const root = ref(null)
const isOpen = ref(false)
const items = ref([])
const unreadCount = ref(0)
const loadError = ref(false)
const toast = ref(null)
let pollTimer = null
let toastTimer = null
let newestId = null
let hasLoadedOnce = false

const endpoint = `/${props.audience}/notifications`

const loadNotifications = async () => {
  const tokenKey = props.audience === 'admin' ? 'admin_token' : 'token'
  if (!sessionStorage.getItem(tokenKey)) return

  try {
    const response = await api.get(endpoint)
    const nextItems = response.data?.notifications || []
    const nextNewestId = nextItems[0]?.id || null

    if (hasLoadedOnce && nextNewestId && newestId && nextNewestId !== newestId) {
      const newest = nextItems[0]
      toast.value = {
        title: newest.data?.title || 'New notification',
        message: newest.data?.message || '',
      }
      window.clearTimeout(toastTimer)
      toastTimer = window.setTimeout(() => { toast.value = null }, 6500)
    }

    newestId = nextNewestId
    hasLoadedOnce = true
    items.value = nextItems
    unreadCount.value = Number(response.data?.unread_count || 0)
    loadError.value = false
  } catch (error) {
    loadError.value = true
  }
}

const togglePanel = async () => {
  isOpen.value = !isOpen.value
  if (isOpen.value) await loadNotifications()
}

const markAllRead = async () => {
  try {
    await api.post(`${endpoint}/read-all`)
    items.value = items.value.map(item => ({ ...item, read_at: item.read_at || new Date().toISOString() }))
    unreadCount.value = 0
  } catch (error) {
    await loadNotifications()
  }
}

const openNotification = async (item) => {
  try {
    if (!item.read_at) await api.post(`${endpoint}/${encodeURIComponent(item.id)}/read`)
  } catch (error) {
    // Keep navigation available if marking the notification read fails.
  }

  if (!item.read_at) unreadCount.value = Math.max(0, unreadCount.value - 1)
  item.read_at = item.read_at || new Date().toISOString()
  isOpen.value = false

  const path = item.data?.url
  if (typeof path === 'string' && path.startsWith(props.audience === 'admin' ? '/admin/' : '/user/')) {
    router.push(path)
  }
}

const formatDate = (value) => {
  if (!value) return ''
  const date = new Date(value)
  return Number.isNaN(date.getTime()) ? '' : date.toLocaleString([], { dateStyle: 'medium', timeStyle: 'short' })
}

const handleOutsideClick = (event) => {
  if (root.value && !root.value.contains(event.target)) isOpen.value = false
}

onMounted(() => {
  loadNotifications()
  pollTimer = window.setInterval(() => {
    if (document.visibilityState === 'visible') loadNotifications()
  }, 15000)
  document.addEventListener('click', handleOutsideClick)
})

onBeforeUnmount(() => {
  window.clearInterval(pollTimer)
  window.clearTimeout(toastTimer)
  document.removeEventListener('click', handleOutsideClick)
})
</script>

<style scoped>
.notification-root { position: relative; display: inline-flex; align-items: center; }
.notification-trigger { position: relative; display: grid; place-items: center; width: 40px; height: 40px; padding: 0; border: 1px solid var(--line-subtle); border-radius: 50%; background: var(--surface-card); color: var(--text-secondary); cursor: pointer; transition: background .18s ease, color .18s ease, border-color .18s ease; }
.notification-trigger:hover { background: var(--blue-soft); color: var(--blue-ink); border-color: var(--blue); }
.notification-trigger svg { width: 19px; height: 19px; fill: none; stroke: currentColor; stroke-width: 1.8; stroke-linecap: round; stroke-linejoin: round; }
.notification-count { position: absolute; top: -4px; right: -5px; min-width: 18px; height: 18px; display: grid; place-items: center; padding: 0 4px; border: 2px solid var(--surface-card); border-radius: 99px; background: #e64e63; color: #fff; font-size: .65rem; font-weight: 800; line-height: 1; }
.notification-panel { position: absolute; top: calc(100% + 12px); right: 0; width: min(380px, calc(100vw - 24px)); max-height: min(480px, calc(100vh - 130px)); display: flex; flex-direction: column; overflow: hidden; border: 1px solid var(--line-subtle); border-radius: 16px; background: var(--surface-card); box-shadow: var(--shadow-popover); z-index: 1100; }
.notification-panel-header { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 16px 18px; border-bottom: 1px solid var(--line-subtle); }
.notification-panel-header h2 { margin: 0; color: var(--text-primary); font-size: 1rem; }
.notification-panel-header p { margin: 4px 0 0; color: var(--text-muted); font-size: .8rem; }
.mark-all-button { padding: 7px 9px; border: 0; border-radius: 8px; background: var(--blue-soft); color: var(--blue-ink); font-size: .77rem; font-weight: 700; cursor: pointer; }
.notification-list { overflow-y: auto; }
.notification-item { width: 100%; display: flex; align-items: flex-start; gap: 12px; padding: 14px 18px; border: 0; border-bottom: 1px solid var(--line-subtle); background: transparent; color: inherit; text-align: left; cursor: pointer; }
.notification-item:hover { background: var(--surface-muted); }
.notification-item.unread { background: color-mix(in srgb, var(--blue-soft) 55%, transparent); }
.notification-item-dot { width: 9px; height: 9px; flex: 0 0 9px; margin-top: 5px; border-radius: 50%; background: var(--blue); }
.notification-item-dot.kind-subscription_due { background: #edaa32; }
.notification-item-copy { min-width: 0; display: grid; gap: 4px; }
.notification-item-copy strong { color: var(--text-primary); font-size: .86rem; }
.notification-item-copy span { color: var(--text-secondary); font-size: .81rem; line-height: 1.45; }
.notification-item-copy small { color: var(--text-muted); font-size: .72rem; }
.notification-empty, .notification-error { margin: 0; padding: 28px 20px; color: var(--text-muted); font-size: .86rem; text-align: center; }
.notification-error { color: #cc5364; }
.notification-toast-card { position: fixed; top: calc(var(--app-header-height, 76px) + 14px); right: 22px; z-index: 1500; width: min(390px, calc(100vw - 28px)); display: grid; grid-template-columns: 30px 1fr 26px; align-items: start; gap: 11px; padding: 14px; border: 1px solid var(--line-subtle); border-radius: 14px; background: var(--surface-card); box-shadow: var(--shadow-popover); color: var(--text-primary); }
.toast-mark { display: grid; place-items: center; width: 28px; height: 28px; border-radius: 50%; background: var(--blue-soft); color: var(--blue-ink); font-weight: 800; }
.notification-toast-card > span:nth-child(2) { display: grid; gap: 3px; }
.notification-toast-card strong { font-size: .86rem; }
.notification-toast-card small { color: var(--text-secondary); font-size: .78rem; line-height: 1.4; }
.notification-toast-card button { padding: 0; border: 0; background: transparent; color: var(--text-muted); font-size: 1.2rem; cursor: pointer; }
.notification-toast-enter-active, .notification-toast-leave-active { transition: opacity .18s ease, transform .18s ease; }
.notification-toast-enter-from, .notification-toast-leave-to { opacity: 0; transform: translateY(-8px); }
@media (max-width: 680px) {
  .notification-trigger { width: 36px; height: 36px; }
  .notification-panel { position: fixed; top: 62px; right: 12px; max-height: calc(100vh - 138px); }
  .notification-toast-card { top: 122px; right: 12px; }
}
</style>
