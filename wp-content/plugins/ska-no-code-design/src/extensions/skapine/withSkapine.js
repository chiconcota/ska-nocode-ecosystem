import { createHigherOrderComponent } from '@wordpress/compose';
import { BlockControls } from '@wordpress/block-editor';
import { ToolbarGroup, ToolbarButton } from '@wordpress/components';
import { useState, useEffect, useRef } from '@wordpress/element';
import { addFilter } from '@wordpress/hooks';
import { SkapineProvider, useSkapine } from './context';
import { parseInitialState, evaluateExpression } from './parser';

const SkapineEngineChild = ({ blockProps, BlockEdit, htmlAttributes, isPreviewMode }) => {
    const skapine = useSkapine();
    const { state, updateState, isPreviewMode: contextPreviewMode } = skapine || {};

    const effectivePreviewMode = isPreviewMode || contextPreviewMode || false;

    const [transitionClass, setTransitionClass] = useState('');

    const hasXShow = htmlAttributes.find(a => a.key === 'x-show');
    const onClickAttr = htmlAttributes.find(a => a.key.startsWith('@click') || a.key.startsWith('x-on:click'));
    const onMouseEnterAttr = htmlAttributes.find(a => a.key.startsWith('@mouseenter') || a.key.startsWith('x-on:mouseenter'));
    const onMouseLeaveAttr = htmlAttributes.find(a => a.key.startsWith('@mouseleave') || a.key.startsWith('x-on:mouseleave'));
    
    // Evaluate x-show
    const isVisible = hasXShow ? evaluateExpression(hasXShow.value, state || {}) : true;

    const [renderVisible, setRenderVisible] = useState(isVisible);

    // Initial sync and non-preview sync
    useEffect(() => {
        if (!effectivePreviewMode) {
            setRenderVisible(isVisible);
        }
    }, [isVisible, effectivePreviewMode]);

    // Simulate Alpine's Transition Lifecycle
    const prevVisibleRef = useRef(isVisible);
    useEffect(() => {
        if (!effectivePreviewMode) return;

        if (isVisible !== prevVisibleRef.current) { 
            const getDuration = (cls) => {
                const match = cls.match(/duration-(\d+)/);
                return match ? parseInt(match[1], 10) : 300;
            };

            const enterCls = htmlAttributes.find(a => a.key === 'x-transition:enter')?.value || '';
            const enterStartCls = htmlAttributes.find(a => a.key === 'x-transition:enter-start')?.value || '';
            const enterEndCls = htmlAttributes.find(a => a.key === 'x-transition:enter-end')?.value || '';
            const leaveCls = htmlAttributes.find(a => a.key === 'x-transition:leave')?.value || '';
            const leaveStartCls = htmlAttributes.find(a => a.key === 'x-transition:leave-start')?.value || '';
            const leaveEndCls = htmlAttributes.find(a => a.key === 'x-transition:leave-end')?.value || '';

            if (isVisible) {
                // Show Animation
                setRenderVisible(true);
                const duration = getDuration(enterCls);
                
                setTransitionClass(`${enterCls} ${enterStartCls}`);
                setTimeout(() => {
                    setTransitionClass(`${enterCls} ${enterEndCls}`);
                }, 30); // Delay cho RAF để browser kịp paint enterStartCls

                setTimeout(() => {
                    setTransitionClass('');
                }, duration + 30); 
            } else {
                // Hide Animation
                const duration = getDuration(leaveCls);

                setTransitionClass(`${leaveCls} ${leaveStartCls}`);
                setTimeout(() => {
                    setTransitionClass(`${leaveCls} ${leaveEndCls}`);
                }, 30);

                setTimeout(() => {
                    setRenderVisible(false);
                    setTransitionClass('');
                }, duration + 30);
            }
            prevVisibleRef.current = isVisible;
        }
    }, [isVisible, htmlAttributes, effectivePreviewMode]);

    const wrapperStyle = {};
    if (!renderVisible) {
        wrapperStyle.opacity = effectivePreviewMode ? 0 : 0.4;
        wrapperStyle.pointerEvents = effectivePreviewMode ? 'none' : 'auto';
        if (effectivePreviewMode) wrapperStyle.display = 'none'; // Ẩn hẳn để test layout
    }

    if (effectivePreviewMode) {
        wrapperStyle.outline = '2px solid #eab308'; // Viền vàng báo hiệu
        wrapperStyle.outlineOffset = '2px';
        // Đã gỡ bỏ wrapperStyle.transition vì nó đè mất Tailwind transition classes
    }

    // Capture Clicks in preview mode
    const lastInteractionRef = wp.element.useRef(0);

    const handleWrapperClick = (e) => {
        if (!effectivePreviewMode) return;
        
        if (onClickAttr) {
            console.log('Skapine clicked block:', {
                name,
                clickValue: onClickAttr.value,
                hasUpdateState: !!updateState,
                state
            });
            
            if (updateState) {
                e.stopPropagation();
                e.preventDefault();
                
                // Xử lý logic gán (VD: open = !open)
                const result = evaluateExpression(onClickAttr.value, state || {});
                
                if (result && typeof result === 'object' && !Array.isArray(result)) {
                    updateState(result);
                }
            }
        }
        
        // Hỗ trợ x-model cho các input (ví dụ checkbox/toggle)
        const xModelAttr = htmlAttributes.find(a => a.key === 'x-model');
        if (xModelAttr && updateState && e.target.tagName === 'INPUT' && (e.target.type === 'checkbox' || e.target.type === 'radio')) {
            e.stopPropagation();
            e.preventDefault();
            const newVal = !e.target.checked;
            e.target.checked = newVal; // Force UI update trong edit mode (do readOnly cản trở)
            updateState({ [xModelAttr.value]: newVal });
        }
    };

    const handleMouseEnter = (e) => {
        if (!effectivePreviewMode) return;
        if (onMouseEnterAttr && updateState) {
            const result = evaluateExpression(onMouseEnterAttr.value, state || {});
            if (result && typeof result === 'object' && !Array.isArray(result)) updateState(result);
        }
    };

    const handleMouseLeave = (e) => {
        if (!effectivePreviewMode) return;
        if (onMouseLeaveAttr && updateState) {
            const result = evaluateExpression(onMouseLeaveAttr.value, state || {});
            if (result && typeof result === 'object' && !Array.isArray(result)) updateState(result);
        }
    };

    return (
        <div 
            style={wrapperStyle} 
            className={`skapine-wrapper ${transitionClass} ${effectivePreviewMode ? 'skapine-is-preview' : ''}`} 
            onMouseDownCapture={handleWrapperClick}
            onClickCapture={(e) => {
                if (effectivePreviewMode && onClickAttr) {
                    e.stopPropagation();
                    e.preventDefault();
                }
            }}
            onMouseEnter={handleMouseEnter}
            onMouseLeave={handleMouseLeave}
        >
            {effectivePreviewMode && (
                <style>{`
                    .skapine-is-preview .block-list-appender { display: none !important; }
                    .skapine-is-preview .block-editor-button-block-appender { display: none !important; }
                    .skapine-is-preview .block-editor-block-list__insertion-point { display: none !important; }
                `}</style>
            )}
            <BlockEdit {...blockProps} />
        </div>
    );
};

const withSkapineEngine = createHigherOrderComponent((BlockEdit) => {
    return (props) => {
        if (!props.name || !props.name.startsWith('ska-builder/')) {
            return <BlockEdit {...props} />;
        }

        const { attributes } = props;
        const htmlAttributes = attributes.htmlAttributes || [];
        const isDefaultPreview = htmlAttributes.some(a => a.key === 'data-skapine-default-preview' && a.value === 'true');
        const [isPreviewMode, setIsPreviewMode] = useState(isDefaultPreview);
        
        // Find x-data
        const xDataAttr = htmlAttributes.find(a => a.key === 'x-data');

        let content = (
            <SkapineEngineChild 
                blockProps={props} 
                BlockEdit={BlockEdit}
                htmlAttributes={htmlAttributes} 
                isPreviewMode={isPreviewMode} 
            />
        );

        if (xDataAttr) {
            const initialState = parseInitialState(xDataAttr.value);
            content = (
                <SkapineProvider initialState={initialState} isPreviewMode={isPreviewMode}>
                    {content}
                </SkapineProvider>
            );
        }

        return (
            <>
                <BlockControls>
                    <ToolbarGroup>
                        <ToolbarButton
                            icon={
                                <svg width="24" height="24" viewBox="0 0 24 24" style={{ fill: isPreviewMode ? '#eab308' : 'currentColor' }}>
                                    <path d="M13 2.05v3.03c3.39.49 6 3.39 6 6.92 0 .9-.18 1.75-.5 2.54l2.27 2.27c.78-1.4 1.23-3.04 1.23-4.81 0-5.17-4.36-9.32-9-9.95zM4.12 3.84L2.71 5.25l3.22 3.22C5.35 9.54 5 10.73 5 12c0 3.87 3.13 7 7 7 1.27 0 2.46-.35 3.53-.93l3.22 3.22 1.41-1.41L4.12 3.84zM12 17c-2.76 0-5-2.24-5-5 0-.89.24-1.72.64-2.45l6.81 6.81c-.73.4-1.56.64-2.45.64zm6.06-8.941c-.41-1.12-1.07-2.12-1.92-2.92l-2.07 2.071c.54.49.97 1.09 1.21 1.751l2.78-.902z" />
                                </svg>
                            }
                            title={isPreviewMode ? "Tắt Preview X-State" : "Bật Preview X-State"}
                            isActive={isPreviewMode}
                            onClick={() => setIsPreviewMode(!isPreviewMode)}
                        />
                    </ToolbarGroup>
                </BlockControls>
                {content}
            </>
        );
    };
}, 'withSkapineEngine');

addFilter('editor.BlockEdit', 'ska-builder/extensions/skapine-engine', withSkapineEngine);

export default withSkapineEngine;
