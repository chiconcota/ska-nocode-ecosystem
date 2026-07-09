import React from 'react';
import { SkaFXInput, SkaFXTextarea } from './SkaFXAutocomplete';

const { __ } = window.wp?.i18n || { __: (text) => text };

/**
 * Component render tự động Form Settings cho các pluggable nodes ngoài Core
 * dựa trên cấu trúc JSON Schema (settings_schema) do PHP Registry cung cấp.
 * 
 * @param {Object} props
 * @param {Object} props.selectedNode Node đang được click cấu hình
 * @param {Array} props.nodes Danh sách tất cả các nodes trên Canvas
 * @param {Function} props.onUpdateNode Callback cập nhật dữ liệu node
 * @param {string} props.mockPayload JSON payload để autocompletion hoạt động
 */
export default function DynamicNodeSettings({ selectedNode, nodes, onUpdateNode, mockPayload }) {
  // Tìm kiếm metadata của node trong registry để lấy schema cấu hình
  const nodeMeta = (window.SKA_DAG_CONTEXT?.AVAILABLE_NODES || []).find(
    (n) => n.type === selectedNode.type
  );
  
  const schema = nodeMeta?.settings_schema || [];

  const handleChange = (key, value) => {
    onUpdateNode(selectedNode.id, {
      ...selectedNode.data,
      [key]: value
    });
  };

  if (!schema || schema.length === 0) {
    return (
      <div className="p-3 bg-amber-50 border border-amber-200 rounded text-sm text-amber-800">
        {__( 'No configuration options available for this node.', 'ska-logic-engine' )}
      </div>
    );
  }

  return (
    <div className="space-y-4">
      {schema.map((field) => {
        const fieldName = field.name;
        // Lấy giá trị hiện tại của field, fallback về default từ schema hoặc chuỗi rỗng
        const fieldValue = selectedNode.data[fieldName] !== undefined 
          ? selectedNode.data[fieldName] 
          : (field.default !== undefined ? field.default : '');

        switch (field.type) {
          case 'textarea':
            return (
              <div key={fieldName}>
                <label className="block text-sm font-medium text-slate-700 mb-1">
                  {field.label || fieldName}
                </label>
                <SkaFXTextarea
                  nodes={nodes}
                  selectedNode={selectedNode}
                  mockPayload={mockPayload}
                  value={fieldValue}
                  onChange={(val) => handleChange(fieldName, val)}
                  className="w-full text-sm p-2 border border-slate-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none h-24 font-mono text-slate-800"
                  placeholder={field.placeholder || ''}
                />
                {field.help && (
                  <p className="text-[10px] text-slate-500 mt-1">{field.help}</p>
                )}
              </div>
            );

          case 'select':
            const options = field.options || [];
            return (
              <div key={fieldName}>
                <label className="block text-sm font-medium text-slate-700 mb-1">
                  {field.label || fieldName}
                </label>
                <select
                  value={fieldValue}
                  onChange={(e) => handleChange(fieldName, e.target.value)}
                  className="w-full text-sm p-2 border border-slate-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none bg-white text-slate-800"
                >
                  {options.map((opt) => (
                    <option key={opt.value} value={opt.value}>
                      {opt.label || opt.value}
                    </option>
                  ))}
                </select>
                {field.help && (
                  <p className="text-[10px] text-slate-500 mt-1">{field.help}</p>
                )}
              </div>
            );

          case 'toggle':
          case 'checkbox':
            return (
              <div key={fieldName} className="flex items-center gap-2 py-1">
                <input
                  type="checkbox"
                  id={`field-${fieldName}`}
                  checked={!!fieldValue}
                  onChange={(e) => handleChange(fieldName, e.target.checked)}
                  className="h-4 w-4 text-blue-600 border-slate-300 rounded focus:ring-blue-500"
                />
                <div className="text-sm">
                  <label htmlFor={`field-${fieldName}`} className="font-medium text-slate-700 cursor-pointer select-none">
                    {field.label || fieldName}
                  </label>
                  {field.help && (
                    <p className="text-[10px] text-slate-500">{field.help}</p>
                  )}
                </div>
              </div>
            );

          case 'text':
          case 'number':
          case 'password':
          default:
            return (
              <div key={fieldName}>
                <label className="block text-sm font-medium text-slate-700 mb-1">
                  {field.label || fieldName}
                </label>
                <SkaFXInput
                  nodes={nodes}
                  selectedNode={selectedNode}
                  mockPayload={mockPayload}
                  type={field.type || 'text'}
                  value={fieldValue}
                  onChange={(val) => handleChange(fieldName, val)}
                  className="w-full text-sm p-2 border border-slate-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none font-mono text-slate-800"
                  placeholder={field.placeholder || ''}
                />
                {field.help && (
                  <p className="text-[10px] text-slate-500 mt-1">{field.help}</p>
                )}
              </div>
            );
        }
      })}
    </div>
  );
}
