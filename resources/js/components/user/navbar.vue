<template>
  <header class="navbar">
    <div class="navbar-left">
      <h1>{{ pageTitle }}</h1>
    </div>

    <div class="navbar-right">
      <img :src="profileImg" id="profileIcon" alt="Profile" />
    </div>
  </header>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import profileImg from '@/assets/img/profile.png.png'

const route = useRoute()

const pageTitle = computed(() => {
  switch (route.path) {
    case '/user/vehicles':
      return 'Vehicles'
    case '/user/receipts':
      return 'Receipts'
    default:
      return 'User Panel'
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
  margin: 0;
}

.navbar-right img {
  width: 25px;
  cursor: pointer;
  transition: transform 0.3s ease, filter 0.3s ease;
  filter: brightness(0) invert(1);
}

.navbar-right img:hover {
  transform: scale(1.1);
  filter: brightness(1.5) invert(1) drop-shadow(0 0 5px #ffd700);
}
</style>