import { useState, useEffect } from '@wordpress/element';

const globalStores = {};
const listeners = new Set();

export const SkapineStore = {
    init(name, initialValue) {
        if (globalStores[name] === undefined) {
            globalStores[name] = initialValue;
            this.notify();
        }
    },
    get() {
        return globalStores;
    },
    notify() {
        listeners.forEach(fn => fn());
    },
    subscribe(fn) {
        listeners.add(fn);
        return () => listeners.delete(fn);
    }
};

// Khởi tạo trước các Global Store cơ sở
SkapineStore.init('skaTheme', {
    isDark: false,
    toggle() {
        this.isDark = !this.isDark;
        this.applyTheme();
        SkapineStore.notify(); // Force react update
    },
    applyTheme() {
        // Tìm iframe của Gutenberg Canvas (Modern WP)
        const canvasIframe = document.querySelector('iframe[name="editor-canvas"]');
        if (canvasIframe && canvasIframe.contentDocument) {
            if (this.isDark) {
                canvasIframe.contentDocument.documentElement.classList.add('dark');
            } else {
                canvasIframe.contentDocument.documentElement.classList.remove('dark');
            }
        } else {
            // Fallback cho WP cũ hoặc Full Site Editor không dùng iframe
            const wrapper = document.querySelector('.editor-styles-wrapper');
            if (wrapper) {
                if (this.isDark) wrapper.classList.add('dark');
                else wrapper.classList.remove('dark');
            }
        }
    }
});

// Polyfill minimal Alpine.store API for Preview Mode
if (typeof window !== 'undefined') {
    window.Alpine = window.Alpine || {};
    window.Alpine.store = (name, initialValue) => {
        if (initialValue === undefined) {
            return SkapineStore.get()[name];
        }
        SkapineStore.init(name, initialValue);
    };
    window.SkapineStore = SkapineStore;
}

export function useSkapineStore() {
    const [, setTick] = useState(0);
    useEffect(() => {
        return SkapineStore.subscribe(() => setTick(t => t + 1));
    }, []);
    return SkapineStore.get();
}
