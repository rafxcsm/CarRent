<template>
  <header class="topbar user-topbar">
    <router-link to="/user/vehicles" class="brand" aria-label="CarRent home">
      <img :src="logoImg" alt="" />
      <span class="brand-copy"><strong>CarRent</strong><small>Rental service</small></span>
    </router-link>

    <nav class="topbar-nav" aria-label="Customer navigation">
      <router-link to="/user/vehicles" :class="{ active: isActive('/user/vehicles') }">Vehicles</router-link>
      <router-link to="/user/receipts" :class="{ active: isActive('/user/receipts') }">My bookings</router-link>
    </nav>

    <div class="topbar-account profile-wrapper" ref="profileWrapper">
      <ThemeToggle />
      <NotificationBell audience="user" />
      <img
        :src="profileImg"
        alt="Profile"
        class="profile-avatar"
        @click.stop.prevent="toggleProfileMenu"
      />
      <span class="account-name">{{ profile.name || 'Account' }}</span>

      <div v-if="showProfileMenu" class="profile-dropdown">
        <h3>Profile Information</h3>

        <div class="profile-info">
          <p><strong>Name:</strong> {{ profile.name || 'N/A' }}</p>
          <p><strong>Email:</strong> {{ profile.email || 'N/A' }}</p>
          <p><strong>Contact:</strong> {{ profile.contact || 'N/A' }}</p>
          <p><strong>License:</strong> {{ profile.license || 'N/A' }}</p>
          <p><strong>Address:</strong> {{ profile.address || 'N/A' }}</p>
        </div>

        <button class="logout-btn" @click="logout">
          Logout
        </button>
      </div>
    </div>
  </header>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '@/api'
import ThemeToggle from '@/components/ThemeToggle.vue'
import NotificationBell from '@/components/NotificationBell.vue'
import profileImg from '@/assets/img/profile.png.png'
import logoImg from '@/assets/img/logocar.png.png'

const route = useRoute()
const router = useRouter()

const showProfileMenu = ref(false)
const profileWrapper = ref(null)

/* ✅ NEVER allow null */
const profile = ref({
  name: '',
  email: '',
  contact: '',
  license: '',
  address: '',
})

const isActive = (path) => route.path === path || route.path.startsWith(path + '/')

/* ================= SESSION SAFE ================= */
const getUser = () => {
  try {
    return JSON.parse(sessionStorage.getItem('user')) || null
  } catch {
    return null
  }
}

/* ================= LOAD PROFILE (FIXED) ================= */
const loadProfile = async () => {
  try {
    const response = await api.get('/user/profile')

    const user = response.data?.user

    if (response.data?.success && user) {
      profile.value = {
        name: user.name || '',
        email: user.email || '',
        contact: user.contact || '',
        license: user.license || '',
        address: user.address || '',
      }

      sessionStorage.setItem('user', JSON.stringify(profile.value))
    }

  } catch (err) {
    console.error("Failed to load profile:", err)

    const stored = getUser()

    if (stored) {
      profile.value = {
        name: stored.name || '',
        email: stored.email || '',
        contact: stored.contact || '',
        license: stored.license || '',
        address: stored.address || '',
      }
    }
  }
}

/* ================= UI ================= */
const toggleProfileMenu = (e) => {
  if (e) e.stopPropagation()
  showProfileMenu.value = !showProfileMenu.value
}

const handleClickOutside = (event) => {
  if (
    profileWrapper.value &&
    !profileWrapper.value.contains(event.target)
  ) {
    showProfileMenu.value = false
  }
}

/* ================= LOGOUT ================= */
const logout = () => {
  sessionStorage.removeItem('user')
  sessionStorage.removeItem('token')

  showProfileMenu.value = false
  router.push('/user')
}

/* ================= LIFECYCLE ================= */
onMounted(() => {
  loadProfile()
  document.addEventListener('click', handleClickOutside)
})

onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside)
})

watch(
  () => route.path,
  () => {
    showProfileMenu.value = false
    loadProfile()
  }
)
</script>

<style scoped>
.topbar {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  height: var(--app-header-height, 76px);
  display: flex;
  align-items: center;
  gap: 30px;
  padding: 0 clamp(18px, 3vw, 44px);
  background: var(--surface-card);
  background: color-mix(in srgb, var(--surface-card) 94%, transparent);
  border-bottom: 1px solid var(--line-subtle);
  box-shadow: var(--shadow-card);
  backdrop-filter: blur(14px);
  z-index: 999;
}

.brand {
  display: inline-flex;
  align-items: center;
  gap: 11px;
  min-width: max-content;
  color: var(--text-primary);
  text-decoration: none;
}

.brand img {
  width: 42px;
  height: 42px;
  object-fit: contain;
}

.brand-copy {
  display: grid;
  gap: 1px;
}

.brand-copy strong {
  font-size: 1.08rem;
  letter-spacing: -0.03em;
}

.brand-copy small {
  color: var(--text-muted);
  font-size: 0.68rem;
  letter-spacing: 0.1em;
  text-transform: uppercase;
}

.topbar-nav {
  display: flex;
  align-items: center;
  gap: 5px;
}

.topbar-nav a {
  padding: 10px 14px;
  border-radius: 9px;
  color: var(--text-secondary);
  font-size: 0.91rem;
  font-weight: 600;
  text-decoration: none;
  transition: color .18s ease, background .18s ease;
}

.topbar-nav a:hover,
.topbar-nav a.active {
  background: var(--blue-soft);
  color: var(--blue-ink);
}

.topbar-account {
  display: flex;
  align-items: center;
  gap: 9px;
  margin-left: auto;
  min-width: max-content;
}

.profile-avatar {
  width: 36px;
  height: 36px;
  padding: 5px;
  border: 1px solid var(--line-subtle);
  border-radius: 50%;
  background: var(--surface-muted);
  cursor: pointer;
  object-fit: cover;
  transition: background .18s ease;
}

.profile-avatar:hover {
  background: var(--blue-soft);
}

.account-name {
  max-width: 140px;
  overflow: hidden;
  color: var(--text-secondary);
  font-size: .87rem;
  font-weight: 600;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.profile-wrapper {
  position: relative;
}

.profile-dropdown {
  position: absolute;
  top: 52px;
  right: 0;
  width: min(320px, calc(100vw - 28px));
  background: var(--surface-card);
  border: 1px solid var(--line-subtle);
  border-radius: 14px;
  padding: 18px;
  box-shadow: var(--shadow-popover);
  z-index: 1000;
}

.profile-dropdown h3 {
  margin: 0 0 12px;
  color: var(--text-primary);
  font-size: 1rem;
}

.profile-info p {
  margin: 9px 0;
  color: var(--text-secondary);
  font-size: 0.9rem;
  word-break: break-word;
}

.profile-info strong {
  color: var(--text-primary);
}

.logout-btn {
  margin-top: 15px;
  width: 100%;
  padding: 10px;
  border: 0;
  border-radius: 9px;
  background: var(--blue);
  color: #fff;
  font-weight: 700;
  cursor: pointer;
  transition: background .18s ease;
}

.logout-btn:hover {
  background: var(--blue-hover);
}

@media (max-width: 900px) {
  .topbar {
    gap: 14px;
    padding-inline: 18px;
  }
}

@media (max-width: 680px) {
  .topbar {
    height: 112px;
    display: grid;
    grid-template-columns: 1fr auto;
    grid-template-rows: 58px 46px;
    gap: 0 10px;
    padding: 0 14px;
  }

  .brand img {
    width: 34px;
    height: 34px;
  }

  .topbar-nav {
    grid-column: 1 / -1;
    grid-row: 2;
  }

  .topbar-nav a {
    padding: 8px 11px;
    font-size: .84rem;
  }

  .topbar-account {
    grid-column: 2;
    grid-row: 1;
  }

  .account-name {
    display: none;
  }
}
</style>
