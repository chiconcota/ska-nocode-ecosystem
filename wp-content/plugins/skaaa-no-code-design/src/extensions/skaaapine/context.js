import { createContext, useContext, useState, useMemo } from '@wordpress/element';

const SkaaapineContext = createContext(null);

export function useSkaaapine() {
    return useContext(SkaaapineContext);
}

export function SkaaapineProvider({ initialState, isPreviewMode, children }) {
    const [state, setState] = useState(initialState || {});

    // Cung cấp API để merge state mới vào
    const updateState = (newStateOrUpdater) => {
        setState((prevState) => {
            if (typeof newStateOrUpdater === 'function') {
                return { ...prevState, ...newStateOrUpdater(prevState) };
            }
            return { ...prevState, ...newStateOrUpdater };
        });
    };
    
    // Gói vào useMemo để tránh re-render không cần thiết
    const contextValue = useMemo(() => ({ state, updateState, isPreviewMode }), [state, isPreviewMode]);

    return (
        <SkaaapineContext.Provider value={contextValue}>
            {children}
        </SkaaapineContext.Provider>
    );
}
