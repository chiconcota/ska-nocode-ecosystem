import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';
import tailwindcss from '@tailwindcss/vite';
import { resolve } from 'path';

export default defineConfig({
  plugins: [
    tailwindcss(),
    react()
  ],
  define: {
    'process.env.NODE_ENV': JSON.stringify(process.env.NODE_ENV || 'production')
  },
  build: {
    outDir: 'assets/js',
    emptyOutDir: false,
    lib: {
      entry: resolve(__dirname, 'assets/src/builder/index.jsx'),
      name: 'SkaaaLogicBuilder',
      fileName: () => 'admin-dag-builder.bundle.js',
      formats: ['iife']
    },
    rollupOptions: {
      output: {
        assetFileNames: (assetInfo) => {
          const name = assetInfo.name || (assetInfo.names ? assetInfo.names[0] : 'asset.css');
          if (name === 'style.css' || name === 'skaaa-logic-engine.css' || name === 'index.css') return 'admin-dag-builder.bundle.css';
          return name || 'assets/[name]-[hash][extname]';
        }
      }
    }
  }
});
