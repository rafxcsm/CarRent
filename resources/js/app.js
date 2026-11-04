import { createApp } from 'vue'
import App from './app.vue'
import router from './router'
import axios from 'axios'
import '../css/system-theme.css'
import { initializeTheme } from './composables/useTheme'

initializeTheme()

axios.defaults.baseURL = '/api'
axios.defaults.withCredentials = true
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'

createApp(App)
  .use(router)
  .mount('#app')
