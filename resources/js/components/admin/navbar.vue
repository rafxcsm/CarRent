<template>
  <header class="navbar">
    <div class="navbar-left">
      <h1>{{ pageTitle }}</h1>
    </div>

    <div class="navbar-right">
      <span class="admin-name">Admin</span>
    </div>
  </header>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'

const route = useRoute()

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
    default:
      return 'Admin Panel'
  }
})
</script>

<style scoped>
.navbar {
  position: sticky;
  top: 0;
  height: 80px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 30px;
  background: rgba(0, 0, 0, 0.95);
  border-bottom: 1px solid rgba(255, 215, 0, 0.15);
  z-index: 999;
}

.navbar-left h1 {
  font-size: 1.5rem;
  font-weight: 700;
  color: #ffd700;
  margin: 0px;
  margin-left: 308px;
}

.admin-name {
  font-size: 1rem;
  font-weight: 600;
  color: #ffd700;
}
</style>