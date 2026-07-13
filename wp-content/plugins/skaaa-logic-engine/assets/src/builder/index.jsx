import React from 'react';
import { createRoot } from 'react-dom/client';
import App from './App';
import './index.css';

function initDagBuilder() {
    const rootEl = document.getElementById('skaaa-dag-root');
    if (rootEl && !rootEl._reactRootContainer) {
        const root = createRoot(rootEl);
        rootEl._reactRootContainer = root;
        root.render(<App />);
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initDagBuilder);
} else {
    initDagBuilder();
}
