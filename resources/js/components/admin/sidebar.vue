<template>
  <aside class="sidebar">
    <img :src="logoImg" alt="Logo" class="logo" />

    <ul>
      <li :class="{ active: isActive('/admin/dashboard') }">
        <img :src="dashboardImg" alt="Dashboard" />
        <router-link to="/admin/dashboard" class="nav-link">
          Dashboard
        </router-link>
      </li>

      <li :class="{ active: isActive('/admin/vehicles') }">
        <img :src="carsImg" alt="Vehicles" />
        <router-link to="/admin/vehicles" class="nav-link">
          Vehicles
        </router-link>
      </li>

      <li :class="{ active: isActive('/admin/rent') }">
        <img :src="rentImg" alt="Rent" />
        <router-link to="/admin/rent" class="nav-link">
          Rent
        </router-link>
      </li>

      <li :class="{ active: isActive('/admin/ownerslist') }">
        <img :src="ownersImg" alt="Ownerslist" />
        <router-link to="/admin/ownerslist" class="nav-link">
          Ownerslist
        </router-link>
      </li>
    </ul>
  </aside>
</template>

<script setup>
import { useRoute } from 'vue-router'

import logoImg from '@/assets/img/logocar.png.png'
import dashboardImg from '@/assets/img/dashboard.png.png'
import carsImg from '@/assets/img/cars.png.png'
import rentImg from '@/assets/img/annotation.png'
import ownersImg from '@/assets/img/profile2.png'

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
  box-sizing: border-box;
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