import { computed, ref } from 'vue'

const storageKey = 'carrent-theme'
const readStoredTheme = () => {
  try {
    return typeof window !== 'undefined'
      ? window.localStorage.getItem(storageKey)
      : null
  } catch {
    return null
  }
}

const theme = ref(readStoredTheme() === 'dark' ? 'dark' : 'light')

export const applyTheme = (value = theme.value) => {
  theme.value = value === 'dark' ? 'dark' : 'light'

  if (typeof document !== 'undefined') {
    document.documentElement.dataset.theme = theme.value
  }

  if (typeof window !== 'undefined') {
    try {
      window.localStorage.setItem(storageKey, theme.value)
    } catch {
      // The selected theme still applies for this page view if storage is unavailable.
    }
  }
}

export const initializeTheme = () => applyTheme(theme.value)

export const useTheme = () => {
  const isDark = computed(() => theme.value === 'dark')

  const toggleTheme = () => {
    applyTheme(isDark.value ? 'light' : 'dark')
  }

  return { theme, isDark, toggleTheme }
}
