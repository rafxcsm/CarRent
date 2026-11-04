<template>
  <div class="admin-login-page">
    <header class="admin-login-header">
      <router-link to="/admin/login" class="admin-login-brand">
        <span class="admin-brand-mark" aria-hidden="true">R</span>
        <span><strong>4K's Car Rental</strong><small>Admin portal</small></span>
      </router-link>
      <ThemeToggle />
    </header>

    <main class="admin-login-main">
      <section class="admin-login-intro">
        <p class="eyebrow">Operations workspace</p>
        <h1>Keep every rental moving smoothly.</h1>
        <p>Review requests, manage the fleet, and verify pickup and return from one clear workspace.</p>
        <router-link to="/user">Visit the customer portal <span aria-hidden="true">→</span></router-link>
      </section>

      <section class="login-box admin-login-box" aria-labelledby="adminLoginTitle">
        <p class="login-kicker">Team access</p>
        <h2 id="adminLoginTitle">Team sign in</h2>
        <p class="admin-login-helper">Use your rental admin or staff account to continue.</p>

        <form @submit.prevent="handleLogin">
          <label for="adminEmail">Email address</label>
          <input id="adminEmail" v-model="form.email" type="email" autocomplete="username" placeholder="admin@example.com" required />

          <label for="adminPassword">Password</label>
          <input id="adminPassword" v-model="form.password" type="password" autocomplete="current-password" placeholder="Enter your password" required />

          <button type="submit" class="btn-link" :disabled="loading">
            {{ loading ? 'Logging in...' : 'Log in to admin' }}
          </button>
        </form>

        <router-link class="super-admin-entry" to="/super-admin/login">Platform super admin access</router-link>

        <div v-if="error" class="error" role="alert">{{ error }}</div>
      </section>
    </main>
  </div>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/api'
import ThemeToggle from '@/components/ThemeToggle.vue'

const router = useRouter()

const form = reactive({
  email: '',
  password: '',
})

const loading = ref(false)
const error = ref('')

// ✅ OPTIONAL:
// Remove old admin session every time login page opens
onMounted(() => {
  sessionStorage.removeItem('admin_token')
  sessionStorage.removeItem('admin')
})

const handleLogin = async () => {
  loading.value = true
  error.value = ''

  try {
    // ✅ USE API INSTANCE
    const response = await api.post('/admin/login', form)

    const token = response.data.token
    const admin = response.data.admin

    // ✅ STORE SESSION ONLY
    sessionStorage.setItem('admin_token', token)
    sessionStorage.setItem('admin', JSON.stringify(admin))

    // ✅ REDIRECT
    router.push('/admin/dashboard')

  } catch (err) {
    error.value =
      err.response?.data?.message || 'Invalid email or password.'
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

.admin-login-page {
  min-height: 100vh;
  width: 100%;
  position: relative;
  background-repeat: no-repeat;
  background-position: center;
  background-size: cover;
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

<style scoped>
.admin-login-page {
  min-height: 100vh;
  color: var(--text-primary);
  background: var(--surface-page);
}

.admin-login-page .admin-login-header {
  width: 100%;
  min-height: 76px;
  justify-content: space-between;
  padding: 12px clamp(20px, 5vw, 72px);
  border-bottom: 1px solid var(--line-subtle);
  background: var(--surface-card);
}

.admin-login-brand {
  display: inline-flex;
  align-items: center;
  gap: 11px;
  color: var(--text-primary);
  text-decoration: none;
  text-align: left;
}

.admin-login-brand > span:last-child {
  display: grid;
  gap: 2px;
}

.admin-login-brand strong {
  font-size: 1rem;
}

.admin-login-brand small {
  color: var(--text-muted);
  font-size: 0.7rem;
}

.admin-brand-mark {
  display: grid;
  place-items: center;
  width: 42px;
  height: 42px;
  border-radius: 14px;
  color: #fff;
  background: linear-gradient(145deg, var(--blue), #78aaff);
  font-weight: 800;
}

.admin-login-page .admin-login-main {
  width: min(100% - 40px, 1120px);
  min-height: calc(100vh - 76px);
  margin: 0 auto;
  display: grid;
  grid-template-columns: minmax(0, 1fr) minmax(320px, 430px);
  align-items: center;
  justify-content: space-between;
  gap: clamp(36px, 8vw, 100px);
  padding: 56px 0;
  text-align: left;
}

.admin-login-intro {
  max-width: 620px;
}

.admin-login-intro .eyebrow,
.login-kicker {
  margin: 0 0 12px;
  color: var(--blue);
  font-size: 0.74rem;
  font-weight: 800;
  letter-spacing: 0.12em;
  text-transform: uppercase;
}

.admin-login-intro h1 {
  margin: 0;
  color: var(--text-primary);
  font-size: clamp(2.5rem, 5vw, 4.5rem);
  font-weight: 800;
  letter-spacing: -0.065em;
  line-height: 1.02;
}

.admin-login-intro > p:not(.eyebrow) {
  max-width: 540px;
  margin: 22px 0;
  color: var(--text-secondary);
  font-size: 1.03rem;
  line-height: 1.75;
}

.admin-login-intro > a {
  color: var(--blue);
  font-weight: 750;
  text-decoration: none;
}

.admin-login-intro > a:hover {
  text-decoration: underline;
}

.admin-login-page .admin-login-box {
  padding: clamp(24px, 3vw, 34px);
  text-align: left;
}

.admin-login-box h2 {
  margin: 0;
  color: var(--text-primary);
  font-size: 1.55rem;
  letter-spacing: -0.04em;
}

.admin-login-helper {
  margin: 9px 0 24px;
  color: var(--text-secondary);
  font-size: 0.88rem;
  line-height: 1.6;
}

.admin-login-box form {
  display: grid;
  gap: 9px;
}

.admin-login-box label {
  margin-top: 5px;
  color: var(--text-primary);
  font-size: 0.82rem;
  font-weight: 700;
}

.admin-login-box input {
  width: 100%;
  min-height: 46px;
  margin: 0 0 6px;
  padding: 0 13px;
}

.admin-login-box .btn-link {
  width: 100%;
  min-height: 46px;
  margin-top: 8px;
}

.admin-login-box .error {
  margin-top: 14px;
}

.super-admin-entry {
  display: inline-block;
  margin-top: 18px;
  color: var(--text-muted);
  font-size: .78rem;
  text-decoration: none;
}

.super-admin-entry:hover {
  color: var(--blue);
  text-decoration: underline;
}

@media (max-width: 760px) {
  .admin-login-page .admin-login-main {
    grid-template-columns: 1fr;
    gap: 34px;
    width: min(100% - 32px, 560px);
    padding: 46px 0;
  }

  .admin-login-intro h1 {
    font-size: clamp(2.5rem, 12vw, 3.8rem);
  }
}
</style>
