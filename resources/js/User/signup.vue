<template>
  <div class="signup-page" :style="{ backgroundImage: `url(${bgImage})` }">
    <div class="overlay"></div>

    <header>
      <nav>
        <ul>
          <li>
            <router-link to="/" >LOGIN</router-link>
          </li>
          <li>
            <router-link to="/signup" class="active">SIGN UP</router-link>
          </li>
        </ul>
      </nav>
    </header>

    <main>
      <div class="left-section">
        <h2 class="logo-signup">
          RAFAKIJAY CAR RENTAL <br />
          MANAGEMENT SYSTEM
        </h2>

        <p class="tagline-signup">Join us today for hassle-free rentals!</p>

        <div class="signup-box">
          <h3>Create your account</h3>

          <form @submit.prevent="handleSignup">
            <input v-model="form.name" type="text" placeholder="Full Name" required />
            <input v-model="form.email" type="email" placeholder="Email" required />
            <input v-model="form.contact" type="text" placeholder="Phone Number" required />
            <input v-model="form.address" type="text" placeholder="Address" required />
            <input v-model="form.password" type="password" placeholder="Password" required />
            <input
              v-model="form.password_confirmation"
              type="password"
              placeholder="Confirm Password"
              required
            />

            <button type="submit">Sign Up</button>
          </form>
        </div>
      </div>
    </main>

    <div v-if="showSuccessModal" class="modal">
      <div class="modal-content">
        <h3>Success</h3>
        <p>Account created successfully.</p>
        <button @click="goToLogin">OK</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import bgImage from '@/assets/img/cimage.png.png'

const router = useRouter()

const showSuccessModal = ref(false)

const form = reactive({
  name: '',
  email: '',
  contact: '',
  address: '',
  password: '',
  password_confirmation: '',
})

const handleSignup = () => {
  showSuccessModal.value = true
}

const goToLogin = () => {
  showSuccessModal.value = false
  router.push('/')
}
</script>

<style scoped>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
  font-family: "Poppins", sans-serif;
}

.signup-page {
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
  background-color: rgba(0,0,0,0.6);
  z-index: 1;
}

header {
  position: relative;
  z-index: 2;
  display: flex;
  justify-content: flex-end;
  align-items: center;
  padding: 30px 80px;
}

nav ul {
  list-style: none;
  display: flex;
  gap: 30px;
}

nav ul li a {
  color: #fff;
  text-decoration: none;
  font-weight: 600;
  letter-spacing: 1px;
  transition: 0.3s;
}

nav ul li a:hover,
nav ul li a.active {
  color: #f1c40f;
}

main {
  position: relative;
  z-index: 2;
  min-height: calc(100vh - 100px);
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

.logo-signup {
  font-size: 32px;
  font-weight: 800;
  font-style: italic;
  line-height: 1.2;
  color: #fff;
  margin-bottom: 15px;
  text-shadow: 2px 2px 8px rgba(0,0,0,0.7);
}

.tagline-signup {
  font-weight: 300;
  font-size: 18px;
  margin-bottom: 30px;
}

.signup-box {
  background: rgba(255,255,255,0.15);
  padding: 30px;
  border-radius: 15px;
  backdrop-filter: blur(7px);
  max-width: 400px;
  width: 100%;
  margin: 0 auto;
}

.signup-box h3 {
  margin-bottom: 15px;
  font-weight: 600;
  color: #fff;
}

.signup-box input {
  width: 100%;
  padding: 10px;
  margin: 8px 0;
  border: none;
  border-radius: 6px;
  outline: none;
  background-color: rgba(255,255,255,0.25);
  color: #fff;
}

.signup-box input::placeholder {
  color: #eee;
}

.signup-box button {
  width: 100%;
  padding: 10px;
  background-color: rgba(255,255,255,0.7);
  color: #000;
  font-weight: 600;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  transition: 0.3s;
}

.signup-box button:hover {
  background-color: #fff;
}

.modal {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0,0,0,0.6);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 9999;
}

.modal-content {
  background: #fff;
  padding: 25px;
  border-radius: 10px;
  text-align: center;
  width: 300px;
  color: #111;
}

.modal-content button {
  margin-top: 15px;
  padding: 8px 20px;
  cursor: pointer;
  border: none;
  border-radius: 6px;
  background: #f1c40f;
  font-weight: 600;
}
</style>