import { createContext, useContext, useState, useMemo } from '@wordpress/element';

const SkapineContext = createContext(null);

export function useSkapine() {
    return useContext(SkapineContext);
}

export function SkapineProvider({ initialState, isPreviewMode, children }) {
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
        <SkapineContext.Provider value={contextValue}>
            {children}
        </SkapineContext.Provider>
    );
}
