<template>
  <div class="app-shell user-layout">
    <UserNavbar v-if="showLayoutParts" />
    <main class="page-content">
      <router-view />
    </main>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import UserNavbar from '../components/user/navbar.vue'

const route = useRoute()

const showLayoutParts = computed(() => {
  return route.path !== '/user' && route.path !== '/user/signup'
})
</script>

<style scoped>
.user-layout {
  display: block;
  min-height: 100vh;
  background: #f3f4f6;
}

.page-content {
  min-height: 100vh;
}
</style>
