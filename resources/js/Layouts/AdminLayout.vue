<template>
  <div class="app-shell admin-shell">
    <AdminNavbar />
    <main class="page-content">
      <router-view />
    </main>
  </div>
</template>

<script setup>
import AdminNavbar from '../components/admin/navbar.vue'

import { onMounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

onMounted(() => {
  const token = sessionStorage.getItem('admin_token')

  if (!token) {
    router.push('/admin/login')
  }
})
</script>

<style scoped>
.admin-shell {
  min-height: 100vh;
  width: 100%;
}

.page-content {
  box-sizing: border-box;
  width: 100%;
  min-height: 100vh;
  padding-top: var(--app-header-height, 76px);
}
</style>
