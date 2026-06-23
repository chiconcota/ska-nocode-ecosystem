/**
 * Ska Code Block Edit Component
 */
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, SelectControl, TextareaControl, TextControl, Button, Modal, Notice } from '@wordpress/components';
import { useState, useEffect } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import { __ } from '@wordpress/i18n';

export default function Edit(props) {
    const { attributes, setAttributes } = props;
    const { codeType, libraryScriptId, inlineCode, location } = attributes;

    const [scripts, setScripts] = useState([]);
    const [isLoading, setIsLoading] = useState(false);
    const [isModalOpen, setIsModalOpen] = useState(false);
    
    // Modal Form States
    const [newScriptName, setNewScriptName] = useState('');
    const [newScriptId, setNewScriptId] = useState('');
    const [newScriptType, setNewScriptType] = useState('js_inline');
    const [modalError, setModalError] = useState('');
    const [isSavingScript, setIsSavingScript] = useState(false);

    // Fetch scripts library
    const fetchScripts = () => {
        setIsLoading(true);
        apiFetch({ path: '/ska-data/v1/scripts' })
            .then((res) => {
                if (res && res.success && Array.isArray(res.data)) {
                    setScripts(res.data);
                }
                setIsLoading(false);
            })
            .catch((err) => {
                console.error('Failed to fetch scripts library', err);
                setIsLoading(false);
            });
    };

    useEffect(() => {
        fetchScripts();
    }, []);

    const handleSaveToLibrary = (e) => {
        e.preventDefault();
        if (!newScriptName || !newScriptId) {
            setModalError(__('Please fill in all required fields.', 'ska-no-code-design'));
            return;
        }

        setIsSavingScript(true);
        setModalError('');

        apiFetch({
            path: '/ska-data/v1/scripts',
            method: 'POST',
            data: {
                script_id: newScriptId,
                name: newScriptName,
                type: newScriptType,
                content: inlineCode,
                location: location === 'inline' ? 'footer' : location // fallback inline to footer for library scripts
            }
        })
        .then((res) => {
            setIsSavingScript(false);
            if (res && res.success) {
                // Update block attributes to connect to the new script
                setAttributes({
                    codeType: 'library',
                    libraryScriptId: newScriptId
                });
                setIsModalOpen(false);
                // Reset form
                setNewScriptName('');
                setNewScriptId('');
                setNewScriptType('js_inline');
                // Refresh list
                fetchScripts();
            } else {
                setModalError(res.message || __('Failed to save script.', 'ska-no-code-design'));
            }
        })
        .catch((err) => {
            setIsSavingScript(false);
            setModalError(err.message || __('An error occurred while saving the script.', 'ska-no-code-design'));
        });
    };

    const blockProps = useBlockProps({
        className: 'ska-code-block-wrapper'
    });

    // Find current selected script details
    const selectedScript = scripts.find(s => s.script_id === libraryScriptId);

    // Build library scripts options
    const scriptOptions = [
        { label: __('--- Select Script ---', 'ska-no-code-design'), value: '' },
        ...scripts.map(s => ({
            label: `${s.name} (${s.type})`,
            value: s.script_id
        }))
    ];

    const containerStyle = {
        background: '#0f172a',
        border: '1px solid #1e293b',
        borderRadius: '12px',
        padding: '20px',
        color: '#94a3b8',
        fontFamily: 'system-ui, -apple-system, sans-serif',
        boxShadow: '0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.1)'
    };

    const headerStyle = {
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'between',
        borderBottom: '1px solid #1e293b',
        paddingBottom: '12px',
        marginBottom: '16px'
    };

    const previewCode = inlineCode 
        ? (inlineCode.length > 150 ? inlineCode.substring(0, 150) + '...' : inlineCode)
        : __('// Write your custom code here...', 'ska-no-code-design');

    return (
        <div {...blockProps}>
            <InspectorControls>
                <PanelBody title={__('Ska Code Settings', 'ska-no-code-design')} initialOpen={true}>
                    <SelectControl
                        label={__('Source Mode', 'ska-no-code-design')}
                        value={codeType}
                        options={[
                            { label: __('Inline Code (Direct write)', 'ska-no-code-design'), value: 'inline' },
                            { label: __('Scripts Library (Centralized)', 'ska-no-code-design'), value: 'library' }
                        ]}
                        onChange={(val) => setAttributes({ codeType: val })}
                    />

                    {codeType === 'inline' && (
                        <>
                            <div style={{ position: 'relative' }}>
                                <TextareaControl
                                    label={__('Code Editor', 'ska-no-code-design')}
                                    value={inlineCode}
                                    onChange={(val) => setAttributes({ inlineCode: val })}
                                    rows={10}
                                    help={__('Supports raw HTML, CSS styles, or Javascript code.', 'ska-no-code-design')}
                                    style={{
                                        fontFamily: 'monospace',
                                        backgroundColor: '#0f172a',
                                        color: '#e2e8f0',
                                        padding: '12px',
                                        fontSize: '13px'
                                    }}
                                />
                            </div>

                            <SelectControl
                                label={__('Inject Location', 'ska-no-code-design')}
                                value={location}
                                options={[
                                    { label: __('Inline (Render at block position)', 'ska-no-code-design'), value: 'inline' },
                                    { label: __('Header (wp_head)', 'ska-no-code-design'), value: 'header' },
                                    { label: __('Footer (wp_footer)', 'ska-no-code-design'), value: 'footer' }
                                ]}
                                onChange={(val) => setAttributes({ location: val })}
                                help={__('Choose where the code will be loaded in the page DOM.', 'ska-no-code-design')}
                            />

                            {inlineCode && (
                                <div style={{ marginTop: '16px', borderTop: '1px solid #e2e8f0', paddingTop: '16px' }}>
                                    <Button 
                                        variant="secondary" 
                                        isDestructive={false}
                                        style={{ width: '100%', justifyContent: 'center' }}
                                        onClick={() => {
                                            setNewScriptName('');
                                            setNewScriptId('');
                                            setModalError('');
                                            setIsModalOpen(true);
                                        }}
                                    >
                                        <span className="dashicons dashicons-upload" style={{ marginRight: '6px' }}></span>
                                        {__('Save to Scripts Library', 'ska-no-code-design')}
                                    </Button>
                                </div>
                            )}
                        </>
                    )}

                    {codeType === 'library' && (
                        <>
                            <SelectControl
                                label={__('Select Script', 'ska-no-code-design')}
                                value={libraryScriptId}
                                options={scriptOptions}
                                onChange={(val) => setAttributes({ libraryScriptId: val })}
                                help={isLoading ? __('Loading scripts...', 'ska-no-code-design') : __('Choose a script from central library.', 'ska-no-code-design')}
                            />
                            
                            {selectedScript && (
                                <div style={{ 
                                    padding: '12px', 
                                    backgroundColor: '#f1f5f9', 
                                    borderRadius: '6px', 
                                    borderLeft: '4px solid #6366f1',
                                    fontSize: '12px',
                                    color: '#475569',
                                    marginTop: '8px'
                                }}>
                                    <div style={{ fontWeight: '600', marginBottom: '4px', color: '#1e293b' }}>
                                        {__('Script Metadata:', 'ska-no-code-design')}
                                    </div>
                                    <div><b>Type:</b> {selectedScript.type.toUpperCase().replace('_', ' ')}</div>
                                    <div><b>Original Position:</b> {selectedScript.location.toUpperCase()}</div>
                                    <div style={{ fontSize: '11px', color: '#64748b', marginTop: '6px', fontStyle: 'italic' }}>
                                        {__('* Loaded dynamically & deduplicated at runtime.', 'ska-no-code-design')}
                                    </div>
                                </div>
                            )}

                            <div style={{ marginTop: '16px' }}>
                                <Button 
                                    variant="link" 
                                    style={{ padding: 0, height: 'auto', fontSize: '12px' }}
                                    onClick={fetchScripts}
                                >
                                    <span className="dashicons dashicons-update" style={{ marginRight: '4px', fontSize: '14px', width: '14px', height: '14px' }}></span>
                                    {__('Refresh scripts list', 'ska-no-code-design')}
                                </Button>
                            </div>
                        </>
                    )}
                </PanelBody>
            </InspectorControls>

            {/* Canvas Block UI */}
            <div style={containerStyle}>
                <div style={headerStyle}>
                    <div style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
                        <span className="dashicons dashicons-code" style={{ fontSize: '20px', width: '20px', height: '20px', color: '#6366f1' }}></span>
                        <strong style={{ color: '#f8fafc', fontSize: '15px' }}>{__('Ska Code Block', 'ska-no-code-design')}</strong>
                    </div>
                    <div style={{ 
                        fontSize: '11px', 
                        padding: '3px 8px', 
                        background: codeType === 'inline' ? '#1e293b' : '#312e81', 
                        color: codeType === 'inline' ? '#94a3b8' : '#e0e7ff',
                        borderRadius: '20px',
                        fontWeight: '500',
                        textTransform: 'uppercase',
                        letterSpacing: '0.05em'
                    }}>
                        {codeType === 'inline' ? __('Inline', 'ska-no-code-design') : __('Library', 'ska-no-code-design')}
                    </div>
                </div>

                {codeType === 'inline' ? (
                    <div>
                        <div style={{ fontSize: '12px', color: '#64748b', marginBottom: '8px' }}>
                            <b>{__('Inject Location:', 'ska-no-code-design')}</b> <span style={{ color: '#cbd5e1', textTransform: 'capitalize' }}>{location}</span>
                        </div>
                        <pre style={{ 
                            margin: 0, 
                            padding: '12px', 
                            background: '#020617', 
                            borderRadius: '6px', 
                            color: '#34d399', 
                            fontFamily: 'monospace', 
                            fontSize: '12px',
                            overflow: 'hidden',
                            whiteSpace: 'pre-wrap',
                            maxHeight: '120px'
                        }}>
                            {previewCode}
                        </pre>
                    </div>
                ) : (
                    <div>
                        {libraryScriptId ? (
                            <div>
                                <div style={{ fontSize: '13px', color: '#cbd5e1', fontWeight: '500', marginBottom: '4px' }}>
                                    {selectedScript ? selectedScript.name : libraryScriptId}
                                </div>
                                <div style={{ fontSize: '11px', color: '#64748b' }}>
                                    ID: <code style={{ color: '#94a3b8', background: '#1e293b', padding: '2px 4px', borderRadius: '4px' }}>{libraryScriptId}</code>
                                </div>
                                <div style={{ fontSize: '11px', color: '#475569', marginTop: '12px', fontStyle: 'italic' }}>
                                    {__('// Linked to Scripts Library. Actual code renders at frontend.', 'ska-no-code-design')}
                                </div>
                            </div>
                        ) : (
                            <div style={{ color: '#f43f5e', fontSize: '12px', fontStyle: 'italic' }}>
                                {__('⚠️ Please select a script from the sidebar panel.', 'ska-no-code-design')}
                            </div>
                        )}
                    </div>
                )}
            </div>

            {/* Quick Save Modal */}
            {isModalOpen && (
                <Modal
                    title={__('Save to Scripts Library', 'ska-no-code-design')}
                    onRequestClose={() => setIsModalOpen(false)}
                    style={{ maxWidth: '450px' }}
                >
                    <form onSubmit={handleSaveToLibrary} style={{ display: 'flex', flexDirection: 'column', gap: '16px', marginTop: '8px' }}>
                        {modalError && (
                            <Notice status="error" isDismissible={false}>
                                {modalError}
                            </Notice>
                        )}

                        <TextControl
                            label={__('Script Name', 'ska-no-code-design')}
                            value={newScriptName}
                            onChange={(val) => setNewScriptName(val)}
                            placeholder={__('e.g. Chart Loader', 'ska-no-code-design')}
                            required
                        />

                        <TextControl
                            label={__('Script ID (Unique Slug)', 'ska-no-code-design')}
                            value={newScriptId}
                            onChange={(val) => setNewScriptId(val.toLowerCase().replace(/[^a-z0-9_-]/g, ''))}
                            placeholder={__('e.g. chart-loader', 'ska-no-code-design')}
                            help={__('Lowercase alphanumeric characters, hyphens, and underscores only.', 'ska-no-code-design')}
                            required
                        />

                        <SelectControl
                            label={__('Script Type', 'ska-no-code-design')}
                            value={newScriptType}
                            options={[
                                { label: __('JS Inline code', 'ska-no-code-design'), value: 'js_inline' },
                                { label: __('CSS Inline code', 'ska-no-code-design'), value: 'css_inline' }
                            ]}
                            onChange={(val) => setNewScriptType(val)}
                        />

                        <div style={{ 
                            fontSize: '11px', 
                            color: '#64748b', 
                            backgroundColor: '#f8fafc', 
                            padding: '8px 12px', 
                            borderRadius: '6px',
                            border: '1px solid #e2e8f0',
                            lineHeight: '1.4'
                        }}>
                            {__('Note: The current inline code will be moved to this new library entry. This block will switch to Library mode automatically.', 'ska-no-code-design')}
                        </div>

                        <div style={{ display: 'flex', justifyContent: 'flex-end', gap: '10px', marginTop: '8px' }}>
                            <Button variant="tertiary" onClick={() => setIsModalOpen(false)} disabled={isSavingScript}>
                                {__('Cancel', 'ska-no-code-design')}
                            </Button>
                            <Button variant="primary" type="submit" disabled={isSavingScript}>
                                {isSavingScript ? __('Saving...', 'ska-no-code-design') : __('Save Script', 'ska-no-code-design')}
                            </Button>
                        </div>
                    </form>
                </Modal>
            )}
        </div>
    );
}
