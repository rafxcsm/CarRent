import { createApp } from 'vue'
import App from './app.vue'
import router from './user-mobile-router.js'
import axios from 'axios'
import '../css/system-theme.css'
import '../css/user-mobile.css'
import { initializeTheme } from './composables/useTheme'

document.documentElement.classList.add('drivora-mobile-app')
initializeTheme()

axios.defaults.baseURL = import.meta.env.VITE_API_BASE_URL || '/api'
axios.defaults.withCredentials = true
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'

createApp(App).use(router).mount('#app')
