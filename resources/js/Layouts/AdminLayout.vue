<template>
  <div class="menu-container">
    <AdminSidebar />

    <div class="main-content">
      <AdminNavbar />

      <main class="page-content">
        <router-view />
      </main>
    </div>
  </div>
</template>

<script setup>
import AdminSidebar from '../components/admin/sidebar.vue'
import AdminNavbar from '../components/admin/navbar.vue'

</script>
