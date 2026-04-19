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
