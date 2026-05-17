import { useBlockProps, InspectorControls } from '@wordpress/block-editor';
import { PanelBody, TextControl, SelectControl, Button, Placeholder } from '@wordpress/components';
import { useState, useEffect, useMemo } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import ServerSideRender from '@wordpress/server-side-render';
import { TailwindPanel } from '../components/TailwindPanel.js';
export default function Edit( { attributes, setAttributes } ) {
    const { sourceTable, limit, slots, filters, tailwindClasses = '' } = attributes;
    const [organisms, setOrganisms] = useState([]);
    
    // Load organisms for dropdown from global cache
    useEffect(() => {
        const data = window.skaOrganismsCache || {};
        const options = Object.values(data).map(org => ({
            label: org.name || org.id,
            value: org.id
        }));
        setOrganisms([
            { label: __('Chọn Symbol...', 'ska-no-code-design'), value: '' },
            ...options
        ]);
    }, []);

    const tableOptions = useMemo(() => {
        const opts = [{ label: '-- Chọn Bảng Dữ Liệu --', value: '' }];
        if (window.skaDataDictionary) {
            Object.keys(window.skaDataDictionary).forEach(key => {
                const table = window.skaDataDictionary[key];
                const val = key.replace(/^wp_/, '');
                const labelName = table.__table_info ? table.__table_info.name : val;
                opts.push({ label: `${labelName} (${val})`, value: val });
            });
        }
        if (sourceTable && !opts.find(o => o.value === sourceTable)) {
            opts.push({ label: `[Custom] ${sourceTable}`, value: sourceTable });
        }
        return opts;
    }, [sourceTable]);

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
                <PanelBody title={__('Cấu hình dữ liệu', 'ska-no-code-design')} initialOpen={true}>
                    {window.skaDataDictionary ? (
                        <SelectControl
                            label={__('Source Table (Bảng phẳng)', 'ska-no-code-design')}
                            value={sourceTable}
                            options={tableOptions}
                            onChange={(val) => setAttributes({ sourceTable: val })}
                            help={__('Chọn bảng dữ liệu được cung cấp bởi Ska Data Pro', 'ska-no-code-design')}
                        />
                    ) : (
                        <TextControl
                            label={__('Source Table (Bảng phẳng)', 'ska-no-code-design')}
                            value={sourceTable}
                            onChange={(val) => setAttributes({ sourceTable: val })}
                            help={__('Ví dụ: ska_data_doctors', 'ska-no-code-design')}
                        />
                    )}
                    <TextControl
                        label={__('Giới hạn (Limit)', 'ska-no-code-design')}
                        type="number"
                        value={limit}
                        onChange={(val) => setAttributes({ limit: parseInt(val, 10) || 10 })}
                        min={1}
                        max={100}
                    />
                </PanelBody>

                <PanelBody title={__('Điều kiện lọc (Filters)', 'ska-no-code-design')} initialOpen={false}>
                    {filters && filters.map((filter, index) => (
                        <div key={index} style={{ marginBottom: '16px', padding: '12px', border: '1px solid #e2e8f0', borderRadius: '6px', backgroundColor: '#f8fafc' }}>
                            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '8px' }}>
                                <strong style={{ fontSize: '13px' }}>Filter #{index + 1}</strong>
                                <Button 
                                    isDestructive 
                                    isSmall 
                                    icon="trash" 
                                    onClick={() => removeFilter(index)}
                                    label={__('Xóa Filter', 'ska-no-code-design')}
                                />
                            </div>
                            
                            <TextControl
                                label={__('Trường dữ liệu (Column)', 'ska-no-code-design')}
                                value={filter.column}
                                onChange={(val) => updateFilter(index, 'column', val)}
                                help={__('VD: teacher_id', 'ska-no-code-design')}
                            />

                            <SelectControl
                                label={__('Toán tử (Operator)', 'ska-no-code-design')}
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
                                label={__('Giá trị (Value)', 'ska-no-code-design')}
                                value={filter.value}
                                onChange={(val) => updateFilter(index, 'value', val)}
                                help={__('VD: {url:id} hoặc giá trị tĩnh', 'ska-no-code-design')}
                            />
                        </div>
                    ))}
                    
                    <Button 
                        variant="secondary" 
                        onClick={addFilter}
                        style={{ width: '100%', justifyContent: 'center', marginTop: '8px' }}
                    >
                        {__('+ Thêm Filter', 'ska-no-code-design')}
                    </Button>
                </PanelBody>

                <PanelBody title={__('Điều kiện hiển thị (Slots)', 'ska-no-code-design')} initialOpen={true}>
                    {slots && slots.map((slot, index) => (
                        <div key={index} style={{ marginBottom: '16px', padding: '12px', border: '1px solid #e2e8f0', borderRadius: '6px', backgroundColor: '#f8fafc' }}>
                            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '8px' }}>
                                <strong style={{ fontSize: '13px' }}>Slot #{index + 1}</strong>
                                <Button 
                                    isDestructive 
                                    isSmall 
                                    icon="trash" 
                                    onClick={() => removeSlot(index)}
                                    label={__('Xóa Slot', 'ska-no-code-design')}
                                />
                            </div>
                            
                            <SelectControl
                                label={__('Chọn Symbol', 'ska-no-code-design')}
                                value={slot.organismId}
                                options={organisms.length > 0 ? organisms : [{label: slot.organismId ? slot.organismId : __('Chọn Symbol...', 'ska-no-code-design'), value: slot.organismId || ''}]}
                                onChange={(val) => updateSlot(index, 'organismId', val)}
                            />
                            
                            <TextControl
                                label={__('Điều kiện (SkaFX)', 'ska-no-code-design')}
                                value={slot.condition}
                                onChange={(val) => updateSlot(index, 'condition', val)}
                                help={__('Để trống hoặc gõ "default" nếu là mặc định. Vd: $index == 0', 'ska-no-code-design')}
                            />
                        </div>
                    ))}
                    
                    <Button 
                        variant="secondary" 
                        onClick={addSlot}
                        style={{ width: '100%', justifyContent: 'center', marginTop: '8px' }}
                    >
                        {__('+ Thêm Slot Mới', 'ska-no-code-design')}
                    </Button>
                </PanelBody>

                <TailwindPanel
                    className={tailwindClasses || ''}
                    setClassName={(allClasses) => setAttributes({ tailwindClasses: allClasses })}
                />
            </InspectorControls>

            {hasValidConfig ? (
                <div className="ska-loop-editor-wrapper" style={{ pointerEvents: 'none', display: 'contents' }}>
                    <ServerSideRender
                        block="ska-builder/loop"
                        attributes={{ sourceTable, limit, slots, tailwindClasses }}
                        httpMethod="POST"
                    />
                </div>
            ) : (
                <Placeholder
                    icon="update"
                    label={__('Ska Query Loop', 'ska-no-code-design')}
                    instructions={__('Vui lòng thiết lập Bảng Nguồn (Source Table) và ít nhất 1 Slot để hiển thị dữ liệu.', 'ska-no-code-design')}
                >
                    <div style={{ textAlign: 'left', width: '100%', marginTop: '16px' }}>
                        <ul style={{ listStyle: 'disc', paddingLeft: '20px', fontSize: '13px', color: '#475569' }}>
                            <li>Bảng nguồn hiện tại: <strong>{sourceTable || 'Chưa có'}</strong></li>
                            <li>Số lượng Slot: <strong>{slots ? slots.length : 0}</strong></li>
                        </ul>
                    </div>
                </Placeholder>
            )}
        </div>
    );
}
