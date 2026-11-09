<template>
  <header class="topbar admin-topbar">
    <router-link to="/admin/dashboard" class="brand" aria-label="CarRent admin home">
      <img :src="logoImg" alt="" />
      <span class="brand-copy"><strong>CarRent</strong><small>Admin portal</small></span>
    </router-link>

    <nav class="topbar-nav" aria-label="Admin navigation">
      <router-link to="/admin/dashboard" :class="{ active: isActive('/admin/dashboard') }">Dashboard</router-link>
      <router-link v-if="isBusinessAdmin" to="/admin/vehicles" :class="{ active: isActive('/admin/vehicles') }">Vehicles</router-link>
      <router-link to="/admin/rent" :class="{ active: isActive('/admin/rent') }">Rentals</router-link>
      <router-link v-if="isBusinessAdmin" to="/admin/ownerslist" :class="{ active: isActive('/admin/ownerslist') }">Customers</router-link>
      <router-link v-if="isBusinessAdmin" to="/admin/business" :class="{ active: isActive('/admin/business') }">Business details</router-link>
      <router-link v-if="isBusinessAdmin" to="/admin/staff" :class="{ active: isActive('/admin/staff') }">Staff</router-link>
    </nav>

    <div class="topbar-account">
      <ThemeToggle />
      <NotificationBell audience="admin" />
      <span class="current-page">{{ pageTitle }}</span>
      <span class="account-chip"><span class="account-dot"></span> {{ isBusinessAdmin ? 'Admin' : 'Staff' }}</span>
      <button class="admin-logout button-secondary" type="button" @click="logout">Log out</button>
    </div>
  </header>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '@/api'
import ThemeToggle from '@/components/ThemeToggle.vue'
import NotificationBell from '@/components/NotificationBell.vue'
import logoImg from '@/assets/img/logocar.png.png'

const route = useRoute()
const router = useRouter()

const currentAdmin = (() => {
  try { return JSON.parse(sessionStorage.getItem('admin') || 'null') } catch { return null }
})()
const isBusinessAdmin = computed(() => currentAdmin?.role === 'admin')

const isActive = (path) => route.path === path || route.path.startsWith(path + '/')

const pageTitle = computed(() => {
  switch (route.path) {
    case '/admin/dashboard':
      return 'Dashboard'
    case '/admin/vehicles':
      return 'Vehicles'
    case '/admin/rent':
      return 'Rent'
    case '/admin/ownerslist':
      return 'Ownerslist'
    case '/admin/business':
      return 'Business details'
    case '/admin/staff':
      return 'Staff'
    default:
      return 'Admin Panel'
  }
})

const logout = async () => {
  try {
    await api.post('/admin/logout')
  } catch (error) {
    // Clear the browser session even if the server token already expired.
  } finally {
    sessionStorage.removeItem('admin_token')
    sessionStorage.removeItem('admin')
    router.replace('/admin/login')
  }
}

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
  min-width: 0;
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
  gap: 16px;
  margin-left: auto;
  min-width: max-content;
}

.current-page {
  color: var(--text-muted);
  font-size: .88rem;
}

.account-chip {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 8px 12px;
  border: 1px solid var(--line-subtle);
  border-radius: 999px;
  color: var(--text-secondary);
  font-size: .83rem;
  font-weight: 600;
}

.account-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: var(--blue);
}

.admin-logout {
  padding: 8px 13px;
  white-space: nowrap;
}

@media (max-width: 900px) {
  .topbar {
    gap: 14px;
    padding-inline: 18px;
  }

  .current-page {
    display: none;
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
    width: 100%;
    overflow-x: auto;
    scrollbar-width: none;
  }

  .topbar-nav::-webkit-scrollbar {
    display: none;
  }

  .topbar-nav a {
    flex: 0 0 auto;
    padding: 8px 11px;
    font-size: .84rem;
  }

  .topbar-account {
    grid-column: 2;
    grid-row: 1;
    gap: 8px;
  }

  .account-chip {
    display: none;
  }

  .admin-logout {
    padding: 7px 10px;
    font-size: .8rem;
  }
}
</style>
