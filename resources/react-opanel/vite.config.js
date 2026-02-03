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
        adminUsersEdit: resolve(__dirname, 'src/pages/AdminUsersEdit.jsx'),

        cmsApp: resolve(__dirname, 'src/pages/cms/CmsApp.jsx'),
        externalLinks: resolve(__dirname, 'src/pages/ExternalLinks.jsx'),
        mediaAssets: resolve(__dirname, 'src/pages/MediaLibrary.jsx'),
        locations: resolve(__dirname, 'src/pages/Locations.jsx'),
        faqs: resolve(__dirname, 'src/pages/Faqs.jsx'),
        menuCategories: resolve(__dirname, 'src/pages/MenuCategories.jsx'),
        menuItems: resolve(__dirname, 'src/pages/MenuItems.jsx'),
        foodSafetyCategories: resolve(__dirname, 'src/pages/FoodSafetyCategories.jsx'),
        foodSafetyItems: resolve(__dirname, 'src/pages/FoodSafetyItems.jsx'),
        franchiseInquiries: resolve(__dirname, 'src/pages/FranchiseInquiries.jsx'),
        contentBlocks: resolve(__dirname, 'src/pages/ContentBlocks.jsx'),
      },
      output: {
        entryFileNames: '[name].bundle.js',
        assetFileNames: (assetInfo) => {
          if (assetInfo.name === 'style.css') return 'cmsApp.css'; // Hacky but might work if vite bundles it as style.css first
          return 'assets/[name][extname]';
        }
      }
    }
  }
})
