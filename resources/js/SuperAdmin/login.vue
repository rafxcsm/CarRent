<template>
  <div class="platform-login-page">
    <header class="platform-login-header">
      <router-link to="/super-admin/login" class="platform-brand">
        <span class="platform-brand-mark" aria-hidden="true">P</span>
        <span><strong>CarRent</strong><small>Platform control</small></span>
      </router-link>
      <ThemeToggle />
    </header>

    <main class="platform-login-main">
      <section class="platform-intro">
        <p class="eyebrow">PLATFORM ADMINISTRATION</p>
        <h1>One clear view of the rental platform.</h1>
        <p>Review system-wide rental activity and the accounts managing the service.</p>
        <router-link to="/admin/login">Go to rental admin sign in <span aria-hidden="true">→</span></router-link>
      </section>

      <section class="platform-login-card" aria-labelledby="platformLoginTitle">
        <p class="login-kicker">Restricted access</p>
        <h2 id="platformLoginTitle">Super admin sign in</h2>
        <p class="login-helper">Sign in with your private platform administrator account.</p>

        <form @submit.prevent="handleLogin">
          <label for="superAdminEmail">Email address</label>
          <input id="superAdminEmail" v-model="form.email" type="email" autocomplete="username" placeholder="you@example.com" required />

          <label for="superAdminPassword">Password</label>
          <input id="superAdminPassword" v-model="form.password" type="password" autocomplete="current-password" placeholder="Enter your password" required />

          <button type="submit" :disabled="loading">{{ loading ? 'Signing in…' : 'Open platform dashboard' }}</button>
        </form>

        <p v-if="error" class="login-error" role="alert">{{ error }}</p>
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
const form = reactive({ email: '', password: '' })
const loading = ref(false)
const error = ref('')

onMounted(() => {
  sessionStorage.removeItem('super_admin_token')
  sessionStorage.removeItem('super_admin')
})

const handleLogin = async () => {
  loading.value = true
  error.value = ''

  try {
    const { data } = await api.post('/super-admin/login', form)
    sessionStorage.setItem('super_admin_token', data.token)
    sessionStorage.setItem('super_admin', JSON.stringify(data.super_admin))
    await router.replace('/super-admin/dashboard')
  } catch (err) {
    error.value = err.response?.data?.message || 'Unable to sign in. Please try again.'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.platform-login-page { min-height: 100vh; color: var(--text-primary); background: var(--surface-page); }
.platform-login-header { display: flex; min-height: 76px; align-items: center; justify-content: space-between; padding: 12px clamp(20px, 5vw, 72px); border-bottom: 1px solid var(--line-subtle); background: var(--surface-card); }
.platform-brand { display: inline-flex; align-items: center; gap: 11px; color: var(--text-primary); text-decoration: none; }
.platform-brand > span:last-child { display: grid; gap: 2px; }
.platform-brand strong { font-size: 1rem; }
.platform-brand small { color: var(--text-muted); font-size: .7rem; }
.platform-brand-mark { display: grid; width: 42px; height: 42px; place-items: center; border-radius: 14px; color: #fff; background: linear-gradient(145deg, var(--blue), #78aaff); font-weight: 850; }
.platform-login-main { display: grid; width: min(100% - 40px, 1120px); min-height: calc(100vh - 76px); grid-template-columns: minmax(0, 1fr) minmax(320px, 430px); align-items: center; gap: clamp(36px, 8vw, 100px); margin: 0 auto; padding: 56px 0; }
.platform-intro { max-width: 620px; }
.eyebrow, .login-kicker { margin: 0 0 12px; color: var(--blue); font-size: .74rem; font-weight: 800; letter-spacing: .12em; }
.platform-intro h1 { margin: 0; font-size: clamp(2.5rem, 5vw, 4.4rem); font-weight: 800; letter-spacing: -.065em; line-height: 1.02; }
.platform-intro > p:not(.eyebrow) { max-width: 540px; margin: 22px 0; color: var(--text-secondary); font-size: 1.03rem; line-height: 1.75; }
.platform-intro > a { color: var(--blue); font-weight: 750; text-decoration: none; }
.platform-intro > a:hover { text-decoration: underline; }
.platform-login-card { padding: clamp(24px, 3vw, 34px); border: 1px solid var(--line-subtle); border-radius: 20px; background: var(--surface-card); box-shadow: var(--shadow-card); }
.login-kicker { margin-bottom: 10px; }
.platform-login-card h2 { margin: 0; font-size: 1.55rem; letter-spacing: -.04em; }
.login-helper { margin: 9px 0 24px; color: var(--text-secondary); font-size: .88rem; line-height: 1.6; }
.platform-login-card form { display: grid; gap: 9px; }
.platform-login-card label { margin-top: 5px; font-size: .82rem; font-weight: 700; }
.platform-login-card input { width: 100%; min-height: 46px; margin-bottom: 6px; padding: 0 13px; border: 1px solid var(--line-subtle); border-radius: 10px; color: var(--text-primary); background: var(--surface-raised); }
.platform-login-card button { min-height: 47px; margin-top: 8px; border: 0; border-radius: 10px; color: #fff; background: var(--blue); font-weight: 750; cursor: pointer; }
.platform-login-card button:hover { background: var(--blue-hover); }
.platform-login-card button:disabled { opacity: .65; cursor: wait; }
.login-error { margin: 14px 0 0; color: var(--danger); font-size: .86rem; }
@media (max-width: 760px) { .platform-login-main { width: min(100% - 32px, 560px); grid-template-columns: 1fr; gap: 34px; padding: 46px 0; } .platform-intro h1 { font-size: clamp(2.5rem, 12vw, 3.8rem); } }
</style>
