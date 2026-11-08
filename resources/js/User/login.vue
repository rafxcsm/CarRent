<template>
  <div id="home" class="login-page">
    <header class="mobile-app-header">
      <span class="mobile-app-mark" aria-hidden="true">D</span>
      <span class="mobile-app-copy"><strong>Drivora</strong><small>Car rental bookings</small></span>
      <ThemeToggle />
    </header>

    <header class="landing-header">
      <router-link to="/user" class="landing-brand" aria-label="Rafakijay Car Rental home">
        <span class="landing-brand-mark" aria-hidden="true">R</span>
        <span class="landing-brand-copy">
          <strong>Rafakijay</strong>
          <small>Car rental management</small>
        </span>
      </router-link>

      <nav class="landing-nav" aria-label="Main navigation">
        <a href="#overview" @click.prevent="scrollToSection('overview')">Overview</a>
        <a href="#about" @click.prevent="scrollToSection('about')">About us</a>
        <a href="#services" @click.prevent="scrollToSection('services')">Services</a>
        <a href="#contact" @click.prevent="scrollToSection('contact')">Contact</a>
      </nav>

      <ThemeToggle />
    </header>

    <main class="landing-main">
      <section class="login-hero" aria-labelledby="heroTitle">
        <div class="hero-copy">
          <p class="eyebrow">A smoother way to rent</p>
          <h1 id="heroTitle">Your next drive<br /><span>starts here.</span></h1>
          <p class="hero-description">
            Find a vehicle, send a booking request, and keep track of each step in one simple place.
          </p>

          <div class="hero-actions">
            <a class="hero-primary" href="#login" @click.prevent="scrollToSection('login')">Sign in to get started</a>
            <a class="hero-secondary" href="#overview" @click.prevent="scrollToSection('overview')">Explore the system</a>
          </div>

          <div class="hero-highlights" aria-label="System highlights">
            <span><i aria-hidden="true"></i> Browse the fleet</span>
            <span><i aria-hidden="true"></i> Track your booking</span>
            <span><i aria-hidden="true"></i> QR pickup and return</span>
          </div>
        </div>

        <aside id="login" class="login-box" aria-labelledby="loginTitle">
          <p class="login-kicker">Welcome back</p>
          <h2 id="loginTitle">Log in to your account</h2>
          <p class="login-intro">Sign in to view vehicles and manage your bookings.</p>

          <form @submit.prevent="handleLogin">
            <label for="userBusiness">Rental business</label>
            <select id="userBusiness" v-model="form.client_slug" required :disabled="loadingClients">
              <option value="" disabled>{{ loadingClients ? 'Loading businesses…' : 'Choose your rental business' }}</option>
              <option v-for="client in clients" :key="client.slug" :value="client.slug">{{ client.name }}</option>
            </select>

            <label for="userEmail">Email address</label>
            <input
              id="userEmail"
              v-model="form.email"
              type="email"
              name="email"
              autocomplete="email"
              placeholder="you@example.com"
              required
            />

            <label for="userPassword">Password</label>
            <input
              id="userPassword"
              v-model="form.password"
              type="password"
              name="password"
              autocomplete="current-password"
              placeholder="Enter your password"
              required
            />

            <button type="submit" class="btn-link" :disabled="loading">
              {{ loading ? 'Logging in...' : 'Log in' }}
            </button>
          </form>

          <p v-if="errorMessage" class="error-message" role="alert">
            {{ errorMessage }}
          </p>
          <button
            v-if="errorMessage && !clients.length && !loadingClients"
            class="retry-businesses"
            type="button"
            @click="loadClients"
          >
            Try again
          </button>

          <p class="signup-text">
            New to Rafakijay?
            <router-link to="/user/signup">Create an account</router-link>
          </p>
        </aside>
      </section>

      <section id="overview" class="landing-section overview-section" aria-labelledby="overviewTitle">
        <div class="section-heading">
          <p class="eyebrow">System overview</p>
          <h2 id="overviewTitle">Your rental journey, all in one place</h2>
          <p>
            Rafakijay Car Rental brings vehicle browsing, booking requests, payment proof, and pickup or return verification together.
          </p>
        </div>

        <div class="overview-grid">
          <article class="overview-card">
            <span class="overview-number">01</span>
            <h3>Choose a vehicle</h3>
            <p>Browse the fleet and review each vehicle's daily rate before sending a request.</p>
          </article>
          <article class="overview-card">
            <span class="overview-number">02</span>
            <h3>Follow your booking</h3>
            <p>Check approval status, discount review, and booking details from your account.</p>
          </article>
          <article class="overview-card">
            <span class="overview-number">03</span>
            <h3>Verify pickup and return</h3>
            <p>Use your booking QR code with the admin to record vehicle pickup and return.</p>
          </article>
        </div>
      </section>

      <section id="about" class="landing-section about-section" aria-labelledby="aboutTitle">
        <div class="about-copy">
          <p class="eyebrow">About us</p>
          <h2 id="aboutTitle">Car rental with a clearer process</h2>
          <p>
            Rafakijay Car Rental is designed to make renting easier to manage from the first vehicle search through return. Customers can submit requests and follow their status, while staff review bookings and keep vehicle records organized.
          </p>
        </div>
        <div class="about-note">
          <span class="about-note-mark" aria-hidden="true">R</span>
          <p>Simple booking. Clear updates. A more organized rental experience.</p>
        </div>
      </section>

      <section id="services" class="landing-section services-section" aria-labelledby="servicesTitle">
        <div class="section-heading">
          <p class="eyebrow">Services</p>
          <h2 id="servicesTitle">Support for every step</h2>
        </div>

        <div class="service-grid">
          <article class="service-card">
            <span class="service-icon" aria-hidden="true">01</span>
            <h3>Vehicle browsing</h3>
            <p>Explore available cars and compare daily rates before you book.</p>
          </article>
          <article class="service-card">
            <span class="service-icon" aria-hidden="true">02</span>
            <h3>Booking requests</h3>
            <p>Submit your preferred schedule and payment proof for staff review.</p>
          </article>
          <article class="service-card">
            <span class="service-icon" aria-hidden="true">03</span>
            <h3>Pickup and return checks</h3>
            <p>Present your approved booking QR code for pickup and return verification.</p>
          </article>
        </div>
      </section>

      <section id="contact" class="landing-section contact-section" aria-labelledby="contactTitle">
        <div class="contact-copy">
          <p class="eyebrow">Contact us</p>
          <h2 id="contactTitle">We’re here to help</h2>
          <p>Reach out if you have a question about vehicles, booking requests, or your account.</p>
        </div>
        <div class="contact-details">
          <a href="mailto:support@rafakijay.com">
            <span>Email</span>
            <strong>support@rafakijay.com</strong>
          </a>
          <a href="tel:+639123456789">
            <span>Phone</span>
            <strong>+63 912 345 6789</strong>
          </a>
          <div>
            <span>Address</span>
            <strong>123 Main Street, Cebu City, Philippines</strong>
          </div>
        </div>
      </section>
    </main>

    <footer class="landing-footer">
      <span>© {{ new Date().getFullYear() }} Rafakijay Car Rental</span>
      <a href="#home">Back to top ↑</a>
    </footer>
  </div>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/api'
import ThemeToggle from '@/components/ThemeToggle.vue'

const router = useRouter()

const loading = ref(false)
const errorMessage = ref('')
const loadingClients = ref(true)
const clients = ref([])

const scrollToSection = (id) => {
  document.getElementById(id)?.scrollIntoView({ behavior: 'smooth', block: 'start' })
}

const form = reactive({
  client_slug: sessionStorage.getItem('client_slug') || 'rafakijay',
  email: '',
  password: '',
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

const handleLogin = async () => {
  loading.value = true
  errorMessage.value = ''

  try {
    const response = await api.post('/login', form)
    const token = response.data.token
    const user = response.data.user

    sessionStorage.setItem('client_slug', response.data.client.slug)
    sessionStorage.setItem('client', JSON.stringify(response.data.client))
    sessionStorage.setItem('token', token)
    sessionStorage.setItem('user', JSON.stringify(user))

    router.push('/user/vehicles')
  } catch (err) {
    console.error(err)
    errorMessage.value = err.response?.data?.message || 'Invalid email or password.'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.mobile-app-header {
  display: none;
}

.login-page {
  min-height: 100vh;
  color: var(--text-primary);
  background: var(--surface-page);
}

.landing-header {
  position: sticky;
  top: 0;
  z-index: 20;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 24px;
  min-height: 76px;
  padding: 12px clamp(20px, 5vw, 72px);
  border-bottom: 1px solid var(--line-subtle);
  background: var(--surface-card);
  background: color-mix(in srgb, var(--surface-card) 94%, transparent);
  backdrop-filter: blur(16px);
}

.landing-brand {
  display: inline-flex;
  align-items: center;
  gap: 11px;
  flex: 0 0 auto;
  color: var(--text-primary);
  text-decoration: none;
}

.landing-brand-mark,
.about-note-mark {
  display: grid;
  place-items: center;
  width: 42px;
  height: 42px;
  border-radius: 14px;
  color: #fff;
  background: linear-gradient(145deg, var(--blue), #78aaff);
  font-weight: 800;
  box-shadow: 0 8px 18px rgba(37, 99, 235, 0.2);
}

.landing-brand-copy {
  display: grid;
  gap: 2px;
}

.landing-brand-copy strong {
  font-size: 1rem;
  letter-spacing: -0.02em;
}

.landing-brand-copy small {
  color: var(--text-muted);
  font-size: 0.68rem;
}

.landing-nav {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: clamp(8px, 2vw, 26px);
}

.landing-nav a {
  padding: 9px 3px;
  color: var(--text-secondary);
  font-size: 0.86rem;
  font-weight: 650;
  text-decoration: none;
  transition: color 160ms ease;
}

.landing-nav a:hover {
  color: var(--blue);
}

.landing-main {
  width: min(100% - 40px, 1180px);
  margin: 0 auto;
}

.login-hero {
  display: grid;
  grid-template-columns: minmax(0, 1.2fr) minmax(320px, 0.8fr);
  align-items: center;
  gap: clamp(36px, 8vw, 100px);
  min-height: min(720px, calc(100vh - 76px));
  padding: 64px 0 76px;
}

.hero-copy {
  max-width: 650px;
}

.eyebrow {
  margin: 0 0 14px;
  color: var(--blue);
  font-size: 0.74rem;
  font-weight: 800;
  letter-spacing: 0.13em;
  text-transform: uppercase;
}

.hero-copy h1 {
  margin: 0;
  color: var(--text-primary);
  font-size: clamp(2.7rem, 6vw, 5.2rem);
  font-weight: 800;
  letter-spacing: -0.07em;
  line-height: 0.98;
}

.hero-copy h1 span {
  color: var(--blue);
}

.hero-description {
  max-width: 530px;
  margin: 24px 0 0;
  color: var(--text-secondary);
  font-size: clamp(1rem, 1.7vw, 1.14rem);
  line-height: 1.75;
}

.hero-actions {
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
  margin-top: 28px;
}

.hero-primary,
.hero-secondary {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-height: 46px;
  padding: 0 18px;
  border: 1px solid transparent;
  border-radius: 11px;
  font-size: 0.88rem;
  font-weight: 750;
  text-decoration: none;
  transition: transform 160ms ease, background-color 160ms ease, border-color 160ms ease;
}

.hero-primary {
  color: #fff;
  background: var(--blue);
  box-shadow: 0 10px 22px rgba(37, 99, 235, 0.2);
}

.hero-primary:hover {
  color: #fff;
  background: var(--blue-hover);
  transform: translateY(-1px);
}

.hero-secondary {
  color: var(--blue-ink);
  border-color: var(--blue-border);
  background: var(--blue-soft);
}

.hero-secondary:hover {
  color: var(--blue-ink);
  border-color: var(--blue);
  transform: translateY(-1px);
}

.hero-highlights {
  display: flex;
  flex-wrap: wrap;
  gap: 10px 20px;
  margin-top: 34px;
  color: var(--text-secondary);
  font-size: 0.78rem;
  font-weight: 650;
}

.hero-highlights span {
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

.hero-highlights i {
  width: 7px;
  height: 7px;
  border-radius: 50%;
  background: var(--blue);
  box-shadow: 0 0 0 4px var(--blue-soft);
}

.login-box {
  width: 100%;
  padding: clamp(24px, 3vw, 34px);
}

.login-kicker {
  margin: 0 0 8px;
  color: var(--blue);
  font-size: 0.75rem;
  font-weight: 800;
  letter-spacing: 0.1em;
  text-transform: uppercase;
}

.login-box h2 {
  margin: 0;
  color: var(--text-primary);
  font-size: 1.55rem;
  letter-spacing: -0.04em;
}

.login-intro {
  margin: 9px 0 24px;
  color: var(--text-secondary);
  font-size: 0.88rem;
  line-height: 1.6;
}

.login-box form {
  display: grid;
  gap: 9px;
}

.login-box label {
  margin-top: 5px;
  color: var(--text-primary);
  font-size: 0.82rem;
  font-weight: 700;
}

.login-box input,
.login-box select {
  width: 100%;
  min-height: 46px;
  margin: 0 0 6px;
  padding: 0 13px;
  border: 1px solid var(--line-subtle);
  border-radius: 10px;
  color: var(--text-primary);
  background: var(--surface-raised);
}

.login-box .btn-link {
  width: 100%;
  min-height: 46px;
  margin-top: 8px;
  font-size: 0.9rem;
}

.signup-text {
  margin: 19px 0 0;
  color: var(--text-secondary);
  font-size: 0.82rem;
  text-align: center;
}

.signup-text a {
  margin-left: 4px;
  color: var(--blue);
  font-weight: 750;
  text-decoration: none;
}

.signup-text a:hover {
  text-decoration: underline;
}

.landing-section {
  padding: 78px 0;
  border-top: 1px solid var(--line-subtle);
  scroll-margin-top: 90px;
}

.section-heading {
  max-width: 680px;
  margin: 0 auto 32px;
  text-align: center;
}

.section-heading h2,
.about-copy h2,
.contact-copy h2 {
  margin: 0;
  color: var(--text-primary);
  font-size: clamp(1.8rem, 4vw, 2.65rem);
  letter-spacing: -0.055em;
  line-height: 1.12;
}

.section-heading > p:last-child,
.about-copy > p:last-child,
.contact-copy > p:last-child {
  margin: 14px 0 0;
  color: var(--text-secondary);
  font-size: 0.96rem;
  line-height: 1.75;
}

.overview-grid,
.service-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 16px;
}

.overview-card,
.service-card {
  min-height: 210px;
  padding: 24px;
  border: 1px solid var(--line-subtle);
  border-radius: 16px;
  background: var(--surface-card);
  box-shadow: var(--shadow-card);
}

.overview-number,
.service-icon {
  display: inline-grid;
  place-items: center;
  width: 38px;
  height: 38px;
  border-radius: 12px;
  color: var(--blue-ink);
  background: var(--blue-soft);
  font-size: 0.72rem;
  font-weight: 800;
}

.overview-card h3,
.service-card h3 {
  margin: 20px 0 8px;
  color: var(--text-primary);
  font-size: 1.05rem;
}

.overview-card p,
.service-card p {
  margin: 0;
  color: var(--text-secondary);
  font-size: 0.88rem;
  line-height: 1.7;
}

.about-section {
  display: grid;
  grid-template-columns: minmax(0, 1fr) minmax(260px, 0.72fr);
  align-items: center;
  gap: clamp(30px, 7vw, 90px);
}

.about-copy {
  max-width: 690px;
}

.about-note {
  display: flex;
  align-items: center;
  gap: 18px;
  padding: 25px;
  border: 1px solid var(--blue-border);
  border-radius: 18px;
  background: var(--blue-soft);
}

.about-note-mark {
  flex: 0 0 auto;
}

.about-note p {
  margin: 0;
  color: var(--blue-ink);
  font-size: 1rem;
  font-weight: 700;
  line-height: 1.55;
}

.contact-section {
  display: grid;
  grid-template-columns: 0.85fr 1.15fr;
  gap: 40px;
  align-items: start;
}

.contact-details {
  display: grid;
  gap: 10px;
}

.contact-details a,
.contact-details > div {
  display: grid;
  gap: 5px;
  padding: 17px 19px;
  border: 1px solid var(--line-subtle);
  border-radius: 13px;
  color: var(--text-primary);
  background: var(--surface-card);
  text-decoration: none;
  transition: border-color 160ms ease, transform 160ms ease;
}

.contact-details a:hover {
  border-color: var(--blue-border);
  transform: translateX(2px);
}

.contact-details span {
  color: var(--text-muted);
  font-size: 0.73rem;
  font-weight: 750;
  letter-spacing: 0.08em;
  text-transform: uppercase;
}

.contact-details strong {
  color: var(--text-primary);
  font-size: 0.9rem;
  font-weight: 650;
  overflow-wrap: anywhere;
}

.landing-footer {
  display: flex;
  justify-content: space-between;
  gap: 18px;
  padding: 22px clamp(20px, 5vw, 72px);
  border-top: 1px solid var(--line-subtle);
  color: var(--text-muted);
  font-size: 0.8rem;
}

.landing-footer a {
  color: var(--blue);
  font-weight: 700;
  text-decoration: none;
}

@media (max-width: 900px) {
  .landing-header {
    gap: 14px;
    padding-inline: 20px;
  }

  .landing-nav {
    gap: 13px;
  }

  .landing-nav a {
    font-size: 0.78rem;
  }

  .login-hero {
    grid-template-columns: minmax(0, 1fr) minmax(290px, 0.8fr);
    gap: 36px;
  }
}

@media (max-width: 700px) {
  .landing-header {
    display: grid;
    grid-template-columns: 1fr auto;
    min-height: auto;
    padding: 11px 16px 0;
  }

  .landing-header > .theme-toggle {
    grid-column: 2;
    grid-row: 1;
  }

  .landing-nav {
    grid-column: 1 / -1;
    grid-row: 2;
    justify-content: flex-start;
    gap: 20px;
    overflow-x: auto;
    padding: 8px 0 10px;
    white-space: nowrap;
  }

  .landing-main {
    width: min(100% - 32px, 560px);
  }

  .login-hero {
    grid-template-columns: 1fr;
    gap: 35px;
    min-height: 0;
    padding: 52px 0 56px;
  }

  .hero-copy h1 {
    font-size: clamp(2.7rem, 13vw, 4.1rem);
  }

  .hero-highlights {
    gap: 12px 16px;
  }

  .landing-section {
    padding: 58px 0;
  }

  .overview-grid,
  .service-grid,
  .about-section,
  .contact-section {
    grid-template-columns: 1fr;
  }

  .overview-card,
  .service-card {
    min-height: auto;
  }
}

@media (prefers-reduced-motion: reduce) {
  .hero-primary,
  .hero-secondary,
  .contact-details a {
    transition: none;
  }
}
</style>
