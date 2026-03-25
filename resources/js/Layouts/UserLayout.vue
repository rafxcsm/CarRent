<template>
  <div class="menu-container">
    <UserSidebar />

    <div class="main-content">
      <UserNavBar />

      <main class="page-content">
        <router-view />
      </main>
    </div>
  </div>
</template>

<script setup>
import UserSidebar from '@/components/user/sidebar.vue'
import UserNavBar from '@/components/user/navbar.vue'
</script>

<style scoped>
.menu-container {
  display: flex;
  min-height: 100vh;
  background: #0e0e0e;
}

.main-content {
  margin-left: 300px;
  width: calc(100% - 300px);
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  background: #0e0e0e;
}

.page-content {
  flex: 1;
  position: relative;
  background: #0e0e0e;
}
</style>