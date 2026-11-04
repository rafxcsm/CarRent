<template>
  <button
    class="theme-toggle"
    type="button"
    :aria-label="isDark ? 'Switch to light mode' : 'Switch to dark mode'"
    :aria-pressed="isDark"
    @click="toggleTheme"
  >
    <span class="theme-toggle-icon" aria-hidden="true">{{ isDark ? '☀' : '☾' }}</span>
    <span class="theme-toggle-label">{{ isDark ? 'Light mode' : 'Dark mode' }}</span>
  </button>
</template>

<script setup>
import { useTheme } from '../composables/useTheme'

const { isDark, toggleTheme } = useTheme()
</script>
