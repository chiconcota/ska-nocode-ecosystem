import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl, SelectControl, Button, Placeholder, ToggleControl } from '@wordpress/components';
import { useState, useEffect, useMemo } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import ServerSideRender from '@wordpress/server-side-render';
import { TailwindPanel } from '../components/TailwindPanel.js';
export default function Edit( { attributes, setAttributes } ) {
    const { sourceTable, limit, slots, filters, tailwindClasses = '' } = attributes;
    const [organisms, setOrganisms] = useState([]);
    
    // Load organisms for dropdown from global cache
    useEffect(() => {
        const data = window.skaaaOrganismsCache || {};
        const options = Object.values(data).map(org => ({
            label: org.name || org.id,
            value: org.id
        }));
        setOrganisms([
            { label: __(__( 'Select Symbol...', 'skaaa-no-code-design' ), 'skaaa-no-code-design'), value: '' },
            ...options
        ]);
    }, []);

    const isCurrentTableSystem = Boolean(sourceTable && (sourceTable.includes('_sys_') || sourceTable.startsWith('sys_')));
    const [showSystemTables, setShowSystemTables] = useState(isCurrentTableSystem);

    const tableOptions = useMemo(() => {
        const defaultOpt = [{ label: __( '-- Select Data Table --', 'skaaa-no-code-design' ), value: '' }];
        const appOpts = [];
        const sysOpts = [];

        if (window.skaaaDataDictionary) {
            Object.keys(window.skaaaDataDictionary).forEach(key => {
                const table = window.skaaaDataDictionary[key];
                const val = key.replace(/^wp_/, '');
                const isSys = val.includes('_sys_') || val.startsWith('sys_') || Boolean(table.__table_info?.is_system);
                const rawLabelName = table.__table_info ? table.__table_info.name : val;
                
                if (isSys) {
                    sysOpts.push({ label: `⚙️ ${rawLabelName} (${val})`, value: val });
                } else {
                    appOpts.push({ label: `📦 ${rawLabelName} (${val})`, value: val });
                }
            });
        }

        let combined = [...defaultOpt, ...appOpts];
        if (showSystemTables) {
            combined = [...combined, ...sysOpts];
        }

        if (sourceTable && !combined.find(o => o.value === sourceTable)) {
            combined.push({ label: `[Custom] ${sourceTable}`, value: sourceTable });
        }
        return combined;
    }, [sourceTable, showSystemTables]);

    const blockProps = useBlockProps();

    const addSlot = () => {
        const newSlots = [...(slots || []), { organismId: '', condition: '' }];
        setAttributes({ slots: newSlots });
    };

    const removeSlot = (indexToRemove) => {
        const newSlots = slots.filter((_, index) => index !== indexToRemove);
        setAttributes({ slots: newSlots });
    };

    const updateSlot = (index, key, value) => {
        const newSlots = [...slots];
        // Ensure organismId is always a string to satisfy block.json strict schema validation
        const finalValue = key === 'organismId' ? String(value) : value;
        newSlots[index] = { ...newSlots[index], [key]: finalValue };
        setAttributes({ slots: newSlots });
    };

    const addFilter = () => {
        const newFilters = [...(filters || []), { column: '', operator: '=', value: '' }];
        setAttributes({ filters: newFilters });
    };

    const removeFilter = (indexToRemove) => {
        const newFilters = filters.filter((_, index) => index !== indexToRemove);
        setAttributes({ filters: newFilters });
    };

    const updateFilter = (index, key, value) => {
        const newFilters = [...filters];
        newFilters[index] = { ...newFilters[index], [key]: value };
        setAttributes({ filters: newFilters });
    };

    const hasValidConfig = sourceTable && slots && slots.length > 0 && slots.some(s => s.organismId);

    return (
        <div { ...blockProps }>
            <InspectorControls>
                <PanelBody title={__( 'Data configuration', 'skaaa-no-code-design' )} initialOpen={true}>
                    {window.skaaaDataDictionary ? (
                        <>
                            <SelectControl
                                label={__( 'Source Table (Flat Table)', 'skaaa-no-code-design' )}
                                value={sourceTable}
                                options={tableOptions}
                                onChange={(val) => setAttributes({ sourceTable: val })}
                                help={__( 'Select the data table provided by Skaaa Data Pro', 'skaaa-no-code-design' )}
                            />
                            <ToggleControl
                                label={__( 'Show System Tables (Internal)', 'skaaa-no-code-design' )}
                                help={__( 'Enable to view internal engine tables (sys_apps, sys_workflows, etc.)', 'skaaa-no-code-design' )}
                                checked={showSystemTables}
                                onChange={(val) => setShowSystemTables(val)}
                            />
                        </>
                    ) : (
                        <TextControl
                            label={__( 'Source Table (Flat Table)', 'skaaa-no-code-design' )}
                            value={sourceTable}
                            onChange={(val) => setAttributes({ sourceTable: val })}
                            help={__( 'For example: skaaa_data_doctors', 'skaaa-no-code-design' )}
                        />
                    )}
                    <TextControl
                        label={__( 'Limit', 'skaaa-no-code-design' )}
                        type="number"
                        value={limit}
                        onChange={(val) => setAttributes({ limit: parseInt(val, 10) || 10 })}
                        min={1}
                        max={100}
                    />
                </PanelBody>

                <PanelBody title={__(__( 'Filters', 'skaaa-no-code-design' ), 'skaaa-no-code-design')} initialOpen={false}>
                    {filters && filters.map((filter, index) => (
                        <div key={index} style={{ marginBottom: '16px', padding: '12px', border: '1px solid #e2e8f0', borderRadius: '6px', backgroundColor: '#f8fafc' }}>
                            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '8px' }}>
                                <strong style={{ fontSize: '13px' }}>Filter #{index + 1}</strong>
                                <Button 
                                    isDestructive 
                                    isSmall 
                                    icon="trash" 
                                    onClick={() => removeFilter(index)}
                                    label={__(__( 'Delete Filter', 'skaaa-no-code-design' ), 'skaaa-no-code-design')}
                                />
                            </div>
                            
                            <TextControl
                                label={__(__( 'Data field (Column)', 'skaaa-no-code-design' ), 'skaaa-no-code-design')}
                                value={filter.column}
                                onChange={(val) => updateFilter(index, 'column', val)}
                                help={__('VD: teacher_id', 'skaaa-no-code-design')}
                            />

                            <SelectControl
                                label={__(__( 'Operator', 'skaaa-no-code-design' ), 'skaaa-no-code-design')}
                                value={filter.operator}
                                options={[
                                    { label: '=', value: '=' },
                                    { label: '!=', value: '!=' },
                                    { label: '>', value: '>' },
                                    { label: '>=', value: '>=' },
                                    { label: '<', value: '<' },
                                    { label: '<=', value: '<=' },
                                    { label: 'LIKE', value: 'LIKE' },
                                    { label: 'IN', value: 'IN' },
                                    { label: 'JSON_CONTAINS', value: 'JSON_CONTAINS' }
                                ]}
                                onChange={(val) => updateFilter(index, 'operator', val)}
                            />
                            
                            <TextControl
                                label={__(__( 'Value', 'skaaa-no-code-design' ), 'skaaa-no-code-design')}
                                value={filter.value}
                                onChange={(val) => updateFilter(index, 'value', val)}
                                help={__(__( 'For example: {url:id} or static value', 'skaaa-no-code-design' ), 'skaaa-no-code-design')}
                            />
                        </div>
                    ))}
                    
                    <Button 
                        variant="secondary" 
                        onClick={addFilter}
                        style={{ width: '100%', justifyContent: 'center', marginTop: '8px' }}
                    >
                        {__(__( '+ Add Filter', 'skaaa-no-code-design' ), 'skaaa-no-code-design')}
                    </Button>
                </PanelBody>

                <PanelBody title={__(__( 'Display conditions (Slots)', 'skaaa-no-code-design' ), 'skaaa-no-code-design')} initialOpen={true}>
                    {slots && slots.map((slot, index) => (
                        <div key={index} style={{ marginBottom: '16px', padding: '12px', border: '1px solid #e2e8f0', borderRadius: '6px', backgroundColor: '#f8fafc' }}>
                            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '8px' }}>
                                <strong style={{ fontSize: '13px' }}>Slot #{index + 1}</strong>
                                <Button 
                                    isDestructive 
                                    isSmall 
                                    icon="trash" 
                                    onClick={() => removeSlot(index)}
                                    label={__(__( 'Delete Slots', 'skaaa-no-code-design' ), 'skaaa-no-code-design')}
                                />
                            </div>
                            
                            <SelectControl
                                label={__(__( 'Select Symbol', 'skaaa-no-code-design' ), 'skaaa-no-code-design')}
                                value={slot.organismId}
                                options={organisms.length > 0 ? organisms : [{label: slot.organismId ? slot.organismId : __(__( 'Select Symbol...', 'skaaa-no-code-design' ), 'skaaa-no-code-design'), value: slot.organismId || ''}]}
                                onChange={(val) => updateSlot(index, 'organismId', val)}
                            />
                            
                            <TextControl
                                label={__(__( 'Conditions (SkaaaFX)', 'skaaa-no-code-design' ), 'skaaa-no-code-design')}
                                value={slot.condition}
                                onChange={(val) => updateSlot(index, 'condition', val)}
                                help={__(__( 'Leave blank or type \"default\" if it is default. ', 'skaaa-no-code-design' ), 'skaaa-no-code-design')}
                            />
                        </div>
                    ))}
                    
                    <Button 
                        variant="secondary" 
                        onClick={addSlot}
                        style={{ width: '100%', justifyContent: 'center', marginTop: '8px' }}
                    >
                        {__(__( '+ Add New Slot', 'skaaa-no-code-design' ), 'skaaa-no-code-design')}
                    </Button>
                </PanelBody>

                <TailwindPanel
                    className={tailwindClasses || ''}
                    setClassName={(allClasses) => setAttributes({ tailwindClasses: allClasses })}
                />
            </InspectorControls>

            {hasValidConfig ? (
                <div className="skaaa-loop-editor-wrapper" style={{ pointerEvents: 'none', display: 'contents' }}>
                    <ServerSideRender
                        block="skaaaaa-builder/loop"
                        attributes={{ sourceTable, limit, slots, tailwindClasses }}
                        httpMethod="POST"
                    />
                </div>
            ) : (
                <Placeholder
                    icon="update"
                    label={__('Skaaa Query Loop', 'skaaa-no-code-design')}
                    instructions={__(__( 'Please set up a Source Table and at least 1 Slot to display data.', 'skaaa-no-code-design' ), 'skaaa-no-code-design')}
                >
                    <div style={{ textAlign: 'left', width: '100%', marginTop: '16px' }}>
                        <ul style={{ listStyle: 'disc', paddingLeft: '20px', fontSize: '13px', color: '#475569' }}>
                            <li>Bảng nguồn hiện tại: <strong>{__( '{sourceTable || ', 'skaaa-no-code-design' )}</strong></li>
                            <li>Số lượng Slot: <strong>{slots ? slots.length : 0}</strong></li>
                        </ul>
                    </div>
                </Placeholder>
            )}
        </div>
    );
}
