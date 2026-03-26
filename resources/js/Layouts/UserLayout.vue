<template>
  <div class="user-layout">
    <UserSidebar />
    <div class="main-wrapper">
      <UserNavbar />
      <main class="page-content">
        <router-view />
      </main>
    </div>
  </div>
</template>

<script setup>
import UserSidebar from '../components/user/sidebar.vue'
import UserNavbar from '../components/user/navbar.vue'
</script>

<style scoped>
.user-layout {
  display: flex;
  min-height: 100vh;
  background: #0e0e0e;
}

.main-wrapper {
  margin-left: 317px;
  width: calc(100% - 317px);
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

.page-content {
  flex: 1;
}
</style>