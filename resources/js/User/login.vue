<template>
  <div class="login-page" :style="{ backgroundImage: `url(${bgImage})` }">
    <div class="overlay"></div>

    <main>
      <div class="left-section">
        <h2 class="logo-login">
          RAFAKIJAY CAR RENTAL <br />
          MANAGEMENT SYSTEM
        </h2>

        <p class="tagline">The Key to Hassle-Free Car Rentals</p>

        <div class="login-box">
          <h3>Log in to your account</h3>

          <form @submit.prevent="handleLogin">
            <input
              v-model="form.email"
              type="email"
              placeholder="Email"
              required
            />

            <input
              v-model="form.password"
              type="password"
              placeholder="Password"
              required
            />

            <button type="submit" class="btn-link">Login</button>
          </form>

          <p class="signup-text">
            Don’t have an account?
            <router-link to="/signup">Sign up</router-link>
          </p>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { reactive } from 'vue'
import bgImage from '@/assets/img/cimage.png.png'

const form = reactive({
  email: '',
  password: '',
})

const handleLogin = () => {
  alert('UI only: login not connected yet.')
}
</script>

<style scoped>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
  font-family: "Poppins", sans-serif;
}

.login-page {
  min-height: 100vh;
  width: 100%;
  color: #fff;
  position: relative;
  background-repeat: no-repeat;
  background-position: center;
  background-size: cover;
}

.overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.6);
  z-index: 1;
}

main {
  position: relative;
  z-index: 2;
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-direction: column;
  text-align: center;
  padding: 0 20px;
}

.left-section {
  width: 100%;
  max-width: 500px;
}

.logo-login {
  font-size: 32px;
  font-weight: 800;
  font-style: italic;
  line-height: 1.2;
  color: #fff;
  margin-bottom: 15px;
  text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.7);
}

.tagline {
  font-weight: 300;
  font-size: 18px;
  margin-bottom: 30px;
}

.login-box {
  background: rgba(255, 255, 255, 0.1);
  padding: 30px;
  border-radius: 10px;
  backdrop-filter: blur(5px);
  max-width: 400px;
  width: 100%;
  margin: 0 auto;
}

.login-box h3 {
  margin-bottom: 15px;
  font-weight: 600;
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
  background-color: rgba(255, 255, 255, 0.7);
  color: #000;
  font-weight: 600;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  transition: 0.3s;
}

.login-box button:hover {
  background-color: #fff;
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

.signup-text {
  text-align: center;
  font-size: 13px;
  margin-top: 15px;
}

.signup-text a {
  color: #f1c40f;
  text-decoration: none;
  font-weight: 600;
}

.signup-text a:hover {
  color: white;
}
</style>