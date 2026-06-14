import { defineConfig } from 'vite';
import { resolve } from 'path';

export default defineConfig({
  build: {
    outDir: 'assets/js',
    emptyOutDir: false, // Để không xóa các asset khác trong /js
    lib: {
      entry: resolve(__dirname, 'assets/js/src/index.js'),
      name: 'SkaDataGrid',
      fileName: () => 'admin-datagrid.bundle.js',
      formats: ['iife']
    },
    rollupOptions: {
      external: ['@wordpress/i18n'],
      output: {
        globals: {
          '@wordpress/i18n': 'wp.i18n'
        },
        assetFileNames: (assetInfo) => {
          if (assetInfo.name === 'style.css') return 'admin-datagrid.bundle.css';
          return assetInfo.name;
        }
      }
    }
  }
});
