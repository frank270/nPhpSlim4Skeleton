import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'
import { resolve } from 'path'

export default defineConfig({
  plugins: [react()],
  build: {
    outDir: resolve(__dirname, '../../public/js/opanel'),
    emptyOutDir: true,
    rollupOptions: {
      input: {
        accessRoles: resolve(__dirname, 'src/main.jsx')  // ← 暫時先以這為入口
      },
      output: {
        entryFileNames: '[name].bundle.js'
      }
    }
  }
})
