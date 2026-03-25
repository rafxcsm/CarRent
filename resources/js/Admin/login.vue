<template>
  <div class="background" :style="{ backgroundImage: `url(${bgImage})` }">
    <div class="overlay"></div>

    <header>
      <h2 class="logo">
        RAFAKIJAY CAR RENTAL <br />
        MANAGEMENT SYSTEM
      </h2>
    </header>

    <main>
      <div class="left-section">
        <p class="tagline">The Key to Hassle-Free Car Rentals</p>
        <p class="tagline">Admin Access Panel</p>

        <div class="login-box">
          <h3>Admin Login</h3>

          <form @submit.prevent="handleLogin">
            <input
              v-model="form.email"
              type="email"
              name="email"
              placeholder="Email"
              required
            />

            <input
              v-model="form.password"
              type="password"
              name="password"
              placeholder="Password"
              required
            />

            <button type="submit" class="btn-link" :disabled="loading">
              {{ loading ? 'Logging in...' : 'Login' }}
            </button>
          </form>

          <div v-if="error" class="error">
            {{ error }}
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue'
import axios from 'axios'
import bgImage from '@/assets/img/cimage.png.png'

const form = reactive({
  email: '',
  password: '',
})

const loading = ref(false)
const error = ref('')

const handleLogin = async () => {
  loading.value = true
  error.value = ''

  try {
    const response = await axios.post('/admin/login', form)

    if (response.data?.redirect) {
      window.location.href = response.data.redirect
      return
    }

    window.location.href = '/admin/dashboard'
  } catch (err) {
    error.value =
      err.response?.data?.message ||
      err.response?.data?.errors?.email?.[0] ||
      'Invalid email or password.'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
  font-family: "Poppins", sans-serif;
}

.background {
  min-height: 100vh;
  width: 100%;
  position: relative;
  background-repeat: no-repeat;
  background-position: center;
  background-size: cover;
}

.overlay {
  position: absolute;
  inset: 0;
  background-color: rgba(0, 0, 0, 0.6);
  z-index: 1;
}

header {
  position: relative;
  z-index: 2;
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 30px 80px;
}

.logo {
  font-size: 28px;
  font-weight: 800;
  line-height: 1.2;
  text-align: center;
  color: #fff;
}

main {
  position: relative;
  z-index: 2;
  min-height: calc(100vh - 100px);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
}

.left-section {
  max-width: 400px;
  width: 100%;
  text-align: center;
}

.tagline {
  font-weight: 300;
  margin-bottom: 20px;
  font-size: 16px;
  color: #fff;
}

.login-box {
  background: rgba(255, 255, 255, 0.1);
  padding: 30px;
  border-radius: 10px;
  backdrop-filter: blur(5px);
}

.login-box h3 {
  margin-bottom: 15px;
  font-weight: 600;
  color: #fff;
}

.login-box input {
  width: 100%;
  padding: 10px;
  margin: 8px 0;
  border: none;
  border-radius: 6px;
  outline: none;
  background-color: rgba(255, 255, 255, 0.3);
  color: #fff;
}

.login-box input::placeholder {
  color: #ddd;
}

.login-box button {
  width: 100%;
  padding: 10px;
  font-weight: 600;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  transition: 0.3s;
}

.btn-link {
  display: inline-block;
  padding: 10px 20px;
  background: #ffd700;
  color: black;
  text-decoration: none;
  border-radius: 5px;
}

.btn-link:hover {
  background: white;
}

.btn-link:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.error {
  margin-top: 12px;
  color: #ffb3b3;
  font-size: 14px;
}
</style>