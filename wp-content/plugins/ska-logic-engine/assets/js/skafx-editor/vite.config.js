import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';
import path from 'path';

export default defineConfig({
  plugins: [react({
    jsxRuntime: 'classic',
    babel: {
      plugins: [
        ['@babel/plugin-transform-react-jsx', { pragma: 'wp.element.createElement', pragmaFrag: 'wp.element.Fragment' }]
      ]
    }
  })],
  define: {
    'process.env.NODE_ENV': JSON.stringify('production'),
    'process.env': {}
  },
  build: {
    outDir: '../dist', // Build straight to assets/js/dist
    emptyOutDir: true,
    lib: {
      entry: path.resolve(__dirname, 'src/index.jsx'),
      name: 'SkaEditorLogic',
      formats: ['iife'],
      fileName: () => 'ska-editor-logic.bundle.js'
    },
    rollupOptions: {
      external: ['react', 'react-dom', /@wordpress\/*/],
      output: {
        globals: {
          'react': 'wp.element',
          'react-dom': 'wp.element',
          '@wordpress/element': 'wp.element',
          '@wordpress/components': 'wp.components',
          '@wordpress/hooks': 'wp.hooks',
          '@wordpress/compose': 'wp.compose',
          '@wordpress/block-editor': 'wp.blockEditor',
          '@wordpress/plugins': 'wp.plugins',
          '@wordpress/edit-post': 'wp.editPost'
        }
      }
    }
  }
});
