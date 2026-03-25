<template>
  <aside class="sidebar">
    <img :src="logoImg" class="logo" alt="Logo" />

    <ul>
      <li :class="{ active: isActive('/user/vehicles') }">
        <img :src="vehiclesImg" alt="Vehicles Icon" />
        <router-link to="/user/vehicles" class="nav-link">
          Vehicles
        </router-link>
      </li>

      <li :class="{ active: isActive('/user/receipts') }">
        <img :src="receiptsImg" alt="Receipts Icon" />
        <router-link to="/user/receipts" class="nav-link">
          Receipts
        </router-link>
      </li>
    </ul>
  </aside>
</template>

<script setup>
import { useRoute } from 'vue-router'
import logoImg from '@/assets/img/logocar.png.png'
import vehiclesImg from '@/assets/img/cars.png.png'
import receiptsImg from '@/assets/img/receipts.png.png'

const route = useRoute()

const isActive = (path) => {
  return route.path === path || route.path.startsWith(path + '/')
}
</script>

<style scoped>
.sidebar {
  position: fixed;
  top: 0;
  left: 0;
  width: 317px;
  height: 100vh;
  background: black;
  padding: 40px 20px;
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  z-index: 1000;
}

.logo {
  width: 150px;
  margin-bottom: 40px;
  align-self: center;
}

.sidebar ul {
  list-style: none;
  width: 100%;
  padding: 0;
  margin: 0;
}

.sidebar ul li {
  display: flex;
  align-items: center;
  gap: 15px;
  font-size: 1.1rem;
  font-weight: 600;
  margin-bottom: 15px;
  padding: 10px 15px;
  border-radius: 8px;
  transition: 0.3s;
}

.sidebar ul li img {
  width: 28px;
  filter: brightness(0) invert(1);
}

.sidebar ul li:hover,
.sidebar ul li.active {
  background: rgba(255, 255, 255, 0.1);
}

.nav-link {
  color: white;
  text-decoration: none;
  transition: 0.3s;
  display: block;
  width: 100%;
}

.sidebar ul li:hover .nav-link,
.sidebar ul li.active .nav-link {
  color: #ffd700;
}

* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
  font-family: "Poppins", sans-serif;
}
</style>