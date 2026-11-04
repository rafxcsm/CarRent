import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import { fileURLToPath, URL } from 'node:url'

export default defineConfig({
  root: fileURLToPath(new URL('./android-user', import.meta.url)),
  envDir: fileURLToPath(new URL('.', import.meta.url)),
  base: './',
  publicDir: false,
  plugins: [vue()],
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./resources/js', import.meta.url)),
    },
  },
  build: {
    outDir: 'www',
    emptyOutDir: true,
    rollupOptions: {
      input: fileURLToPath(new URL('./android-user/index.html', import.meta.url)),
    },
  },
})
