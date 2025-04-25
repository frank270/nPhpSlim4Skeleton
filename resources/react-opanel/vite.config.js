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
        accessRoles: resolve(__dirname, 'src/pages/AccessRoles.jsx'),
        adminUsers: resolve(__dirname, 'src/pages/AdminUsers.jsx'),
        adminUsersCreate: resolve(__dirname, 'src/pages/AdminUsersCreate.jsx'),
        adminUsersEdit: resolve(__dirname, 'src/pages/AdminUsersEdit.jsx')
      },
      output: {
        entryFileNames: '[name].bundle.js'
      }
    }
  }
})
