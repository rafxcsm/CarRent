<template>
  <div class="signup-page">
    <header class="signup-header">
      <router-link to="/user" class="signup-brand">
        <span class="signup-brand-mark" aria-hidden="true">R</span>
        <span><strong>Rafakijay</strong><small>Car rental management</small></span>
      </router-link>

      <nav aria-label="Account navigation">
        <ul>
          <li>
            <router-link to="/user">LOGIN</router-link>
          </li>
          <li>
            <router-link to="/user/signup" class="active">SIGN UP</router-link>
          </li>
        </ul>
      </nav>
      <ThemeToggle />
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
            <select v-model="form.client_slug" aria-label="Rental business" required :disabled="loadingClients">
              <option value="" disabled>{{ loadingClients ? 'Loading businesses…' : 'Choose your rental business' }}</option>
              <option v-for="client in clients" :key="client.slug" :value="client.slug">{{ client.name }}</option>
            </select>
            <input v-model="form.name" type="text" placeholder="Full Name" required />
            <input v-model="form.email" type="email" placeholder="Email" required />
            <input v-model="form.contact" type="text" placeholder="Phone Number" required />
            <input v-model="form.license" type="text" placeholder="License No." required />
            <input v-model="form.address" type="text" placeholder="Address" required />
            <input v-model="form.password" type="password" placeholder="Password" required />
            <input
              v-model="form.password_confirmation"
              type="password"
              placeholder="Confirm Password"
              required
            />

            <div class="terms-consent">
              <input id="accepted-terms" v-model="form.accepted_terms" type="checkbox" required />
              <div>
                <label for="accepted-terms">I have read and agree to the</label>
                <button class="terms-link" type="button" @click="showTermsModal = true">Terms and Conditions</button>
              </div>
            </div>

            <button type="submit" :disabled="loading">
              {{ loading ? 'Signing Up...' : 'Sign Up' }}
            </button>
          </form>

          <p v-if="errorMessage" class="error-message">{{ errorMessage }}</p>
          <button
            v-if="errorMessage && !clients.length && !loadingClients"
            class="retry-businesses"
            type="button"
            @click="loadClients"
          >
            Try again
          </button>
        </div>
      </div>
    </main>

    <div v-if="showTermsModal" class="terms-modal" @click.self="showTermsModal = false">
      <section class="terms-panel" role="dialog" aria-modal="true" aria-labelledby="terms-title">
        <button class="terms-close" type="button" aria-label="Close terms" @click="showTermsModal = false">&times;</button>
        <p class="terms-kicker">RAFAKIJAY CAR RENTAL MANAGEMENT SYSTEM</p>
        <h2 id="terms-title">Terms and Conditions</h2>
        <p class="terms-intro">Please review these conditions before creating an account or requesting a vehicle.</p>

        <div class="terms-body">
          <section>
            <h3>1. Account information</h3>
            <p>Provide accurate name, email, phone, address, driver’s license, and account details. Keep your password private and use your account only for your own rental requests.</p>
          </section>
          <section>
            <h3>2. Booking requests and availability</h3>
            <p>A booking request records your chosen pickup date and time. A request is not confirmed until an administrator approves it. The availability calendar shows dates already booked and scheduled maintenance; dates can change until your booking is approved.</p>
            <p>The system records a pickup date and time, but does not collect a return date. The rental period and return arrangements must be confirmed with the administrator. Rates shown are daily rates.</p>
          </section>
          <section>
            <h3>3. Payment proof</h3>
            <p>Attach clear, genuine payment proof when submitting a booking. The administrator reviews it with your request. Submitting proof does not itself confirm a booking or constitute an online payment through this system.</p>
          </section>
          <section>
            <h3>4. Vehicle maintenance and booking changes</h3>
            <p>A vehicle may be unavailable on dates set aside for maintenance. If maintenance conflicts with a pending or approved booking, the administrator may contact you to agree on another date, an available replacement vehicle, or cancellation. Any payment adjustment or refund must be discussed directly with the administrator; the system does not process refunds.</p>
          </section>
          <section>
            <h3>5. Pickup and return verification</h3>
            <p>For an approved booking, show its booking QR code to the administrator at pickup and again at return. The administrator records each handover by scanning the code.</p>
          </section>
          <section>
            <h3>6. Senior and PWD discounts</h3>
            <p>Discount requests are optional and remain pending until the administrator checks the applicable valid ID at pickup. A discount is applied only after verification.</p>
          </section>
          <section>
            <h3>7. Safe and lawful use</h3>
            <p>Use the vehicle lawfully and carefully, follow the administrator’s pickup and return instructions, and promptly tell the administrator about a delay, damage, or safety concern.</p>
          </section>
          <section>
            <h3>8. Personal information</h3>
            <p>The system stores the account details and booking or payment-proof information you submit so the rental team can manage your account and requests. Submit only information needed for these purposes and contact the administrator if you need help with your records.</p>
          </section>
        </div>

        <div class="terms-actions">
          <button class="terms-accept" type="button" @click="showTermsModal = false">Close</button>
        </div>
      </section>
    </div>

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
import { onMounted, reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/api'
import ThemeToggle from '@/components/ThemeToggle.vue'

const router = useRouter()

const showSuccessModal = ref(false)
const showTermsModal = ref(false)
const loading = ref(false)
const errorMessage = ref('')
const loadingClients = ref(true)
const clients = ref([])

const form = reactive({
  client_slug: sessionStorage.getItem('client_slug') || 'rafakijay',
  name: '',
  email: '',
  contact: '',
  license: '',
  address: '',
  password: '',
  password_confirmation: '',
  accepted_terms: false,
})

const loadClients = async () => {
  loadingClients.value = true
  errorMessage.value = ''

  try {
    const response = await api.get('/clients')
    clients.value = response.data.clients || []
    if (!clients.value.some((client) => client.slug === form.client_slug)) {
      form.client_slug = clients.value[0]?.slug || ''
    }
  } catch (err) {
    errorMessage.value = !err.response
      ? 'Could not connect to Drivora. Check your internet connection and try again. The free server may take a little time to wake.'
      : err.response.status >= 500
        ? 'The server could not load rental businesses. Check that its database is running, then try again.'
        : 'Could not load rental businesses. Please try again.'
  } finally {
    loadingClients.value = false
  }
}

onMounted(loadClients)

const resetForm = () => {
  form.name = ''
  form.email = ''
  form.contact = ''
  form.license = ''
  form.address = ''
  form.password = ''
  form.password_confirmation = ''
  form.accepted_terms = false
}

const handleSignup = async () => {
  if (!form.accepted_terms) {
    errorMessage.value = 'Please accept the Terms and Conditions to create an account.'
    return
  }

  loading.value = true
  errorMessage.value = ''

  try {
    const payload = { ...form }
    const response = await api.post('/signup', payload)

    if (response.data?.success) {
      sessionStorage.setItem('client_slug', form.client_slug)
      const profileData = {
        name: form.name,
        email: form.email,
        contact: form.contact,
        license: form.license,
        address: form.address,
      }

      localStorage.setItem('profileInfo', JSON.stringify(profileData))

      showSuccessModal.value = true
      resetForm()
    } else {
      errorMessage.value = 'Signup failed.'
    }
  } catch (err) {
    const errors = err.response?.data?.errors

    if (errors) {
      errorMessage.value = Object.values(errors)[0][0]
    } else {
      errorMessage.value =
        err.response?.data?.message || 'Unable to create account.'
    }
  } finally {
    loading.value = false
  }
}

const goToLogin = () => {
  showSuccessModal.value = false
  router.push('/user')
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

.signup-box button:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.error-message {
  margin-top: 12px;
  color: #ffb3b3;
  font-size: 14px;
  text-align: center;
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

<style scoped>
.signup-page {
  min-height: 100vh;
  color: var(--text-primary);
  background: var(--surface-page);
}

.signup-page .signup-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 18px;
  min-height: 76px;
  padding: 12px clamp(20px, 5vw, 72px);
  border-bottom: 1px solid var(--line-subtle);
  background: var(--surface-card);
}

.signup-brand {
  display: inline-flex;
  align-items: center;
  gap: 11px;
  color: var(--text-primary);
  text-decoration: none;
  text-align: left;
}

.signup-brand > span:last-child {
  display: grid;
  gap: 2px;
}

.signup-brand strong {
  font-size: 1rem;
}

.signup-brand small {
  color: var(--text-muted);
  font-size: 0.68rem;
}

.signup-brand-mark {
  display: grid;
  place-items: center;
  width: 42px;
  height: 42px;
  border-radius: 14px;
  color: #fff;
  background: linear-gradient(145deg, var(--blue), #78aaff);
  font-weight: 800;
}

.signup-page nav ul {
  align-items: center;
  gap: 18px;
  margin: 0;
  padding: 0;
}

.signup-page nav ul li a {
  color: var(--text-secondary);
  font-size: 0.82rem;
  letter-spacing: 0.04em;
}

.signup-page nav ul li a:hover,
.signup-page nav ul li a.active {
  color: var(--blue);
}

.signup-page main {
  min-height: calc(100vh - 76px);
  padding: 32px 16px 54px;
}

.signup-page .logo-signup {
  font-size: clamp(1.8rem, 5vw, 2.5rem);
}

.signup-page .tagline-signup {
  color: var(--text-secondary);
  font-size: 0.95rem;
}

.signup-page .signup-box {
  padding: 28px;
}

.signup-page .signup-box h3 {
  color: var(--text-primary);
}

.signup-page .signup-box input {
  min-height: 44px;
  padding: 0 12px;
}

.signup-page .signup-box select {
  width: 100%;
  min-height: 44px;
  margin: 8px 0;
  padding: 0 12px;
  border: 1px solid var(--line-subtle);
  border-radius: 8px;
  color: var(--text-primary);
  background: var(--surface-raised);
}

.terms-consent {
  display: flex;
  align-items: flex-start;
  gap: 10px;
  margin: 12px 0 17px;
  color: var(--text-secondary);
  font-size: .82rem;
  line-height: 1.5;
  text-align: left;
}

.signup-page .signup-box .terms-consent #accepted-terms {
  width: 17px !important;
  min-width: 17px;
  height: 17px;
  min-height: 17px !important;
  margin: 2px 0 0 !important;
  padding: 0 !important;
  accent-color: var(--blue);
}

.terms-consent label {
  margin-right: 4px;
  cursor: pointer;
}

.signup-page .signup-box .terms-link {
  width: auto !important;
  padding: 0 !important;
  border: 0 !important;
  color: var(--blue) !important;
  background: transparent !important;
  box-shadow: none !important;
  font-weight: 750;
  text-decoration: underline;
  text-underline-offset: 3px;
}

.signup-page .signup-box .terms-link:hover {
  color: var(--blue-hover) !important;
  background: transparent !important;
}

.terms-modal {
  position: fixed;
  inset: 0;
  z-index: 10000;
  display: grid;
  place-items: center;
  padding: 18px;
  background: var(--overlay);
  backdrop-filter: blur(6px);
}

.terms-panel {
  position: relative;
  display: flex;
  width: min(100%, 720px);
  max-height: min(88vh, 850px);
  flex-direction: column;
  padding: clamp(22px, 4vw, 34px);
  border: 1px solid var(--line-subtle);
  border-radius: 20px;
  color: var(--text-primary);
  background: var(--surface-card);
  box-shadow: var(--shadow-popover);
  text-align: left;
}

.terms-panel h2 { margin: 0; color: var(--text-primary); font-size: 1.6rem; letter-spacing: -.04em; }
.terms-kicker { margin: 0 0 7px; color: var(--blue); font-size: .68rem; font-weight: 800; letter-spacing: .12em; }
.terms-intro { margin: 8px 0 18px; color: var(--text-secondary); font-size: .88rem; }
.terms-body { overflow: auto; padding: 0 10px 3px 0; }
.terms-body section + section { margin-top: 16px; }
.terms-body h3 { margin: 0 0 5px; color: var(--text-primary); font-size: .92rem; }
.terms-body p { margin: 0; color: var(--text-secondary); font-size: .82rem; line-height: 1.6; }
.terms-body p + p { margin-top: 7px; }
.terms-close { position: absolute; top: 14px; right: 15px; width: 34px; height: 34px; border: 1px solid var(--line-subtle); border-radius: 50%; color: var(--text-secondary); background: var(--surface-raised); font-size: 1.3rem; cursor: pointer; }
.terms-actions { display: flex; justify-content: flex-end; padding-top: 17px; }
.terms-accept { padding: 9px 18px; border: 1px solid var(--blue); border-radius: 10px; color: #fff; background: var(--blue); font-weight: 700; cursor: pointer; }

@media (max-width: 680px) {
  .signup-page .signup-header {
    display: grid;
    grid-template-columns: minmax(0, 1fr) auto;
    gap: 10px;
    padding: 10px 14px;
  }

  .signup-page nav {
    grid-column: 1 / -1;
    grid-row: 2;
    justify-self: start;
  }

  .signup-page .theme-toggle {
    grid-column: 2;
    grid-row: 1;
  }

  .signup-page main {
    min-height: calc(100vh - 110px);
  }
}
</style>
