<template>
  <div class="user-layout">
    <UserSidebar v-if="showLayoutParts" />
    <div :class="showLayoutParts ? 'main-wrapper' : 'full-wrapper'">
      <UserNavbar v-if="showLayoutParts" />
      <main class="page-content">
        <router-view />
      </main>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import UserSidebar from '../components/user/sidebar.vue'
import UserNavbar from '../components/user/navbar.vue'

const route = useRoute()

const showLayoutParts = computed(() => {
  return route.path !== '/user' && route.path !== '/user/signup'
})
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

.full-wrapper {
  width: 100%;
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

.page-content {
  flex: 1;
}
</style>