import React from 'react';
import { Settings, X } from 'lucide-react';

export default function SettingsPanel({ selectedNode, onUpdateNode, onClose }) {
  if (!selectedNode) return null;

  const handleChange = (key, value) => {
    onUpdateNode(selectedNode.id, {
      ...selectedNode.data,
      [key]: value
    });
  };

  return (
    <aside className="w-80 bg-white border-l border-slate-200 flex flex-col h-full shadow-sm z-10">
      <div className="p-4 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
        <h3 className="font-semibold text-slate-800 flex items-center gap-2">
          <Settings size={18} className="text-blue-500" />
          Settings
        </h3>
        <button 
          onClick={onClose}
          className="p-1 hover:bg-slate-200 rounded text-slate-500 transition-colors"
        >
          <X size={16} />
        </button>
      </div>
      
      <div className="p-4 flex-1 overflow-y-auto space-y-4">
        <div>
          <label className="block text-sm font-medium text-slate-700 mb-1">Node ID</label>
          <input 
            type="text" 
            value={selectedNode.id} 
            disabled
            className="w-full text-sm p-2 border border-slate-200 rounded bg-slate-50 text-slate-500 font-mono cursor-not-allowed"
          />
        </div>
        
        <div>
          <label className="block text-sm font-medium text-slate-700 mb-1">Label</label>
          <input 
            type="text" 
            value={selectedNode.data.label || ''} 
            onChange={(e) => handleChange('label', e.target.value)}
            className="w-full text-sm p-2 border border-slate-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
            placeholder="Enter node label"
          />
        </div>

        <div>
          <label className="block text-sm font-medium text-slate-700 mb-1">Description</label>
          <textarea 
            value={selectedNode.data.description || ''} 
            onChange={(e) => handleChange('description', e.target.value)}
            className="w-full text-sm p-2 border border-slate-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none resize-none h-24"
            placeholder="Enter description"
          />
        </div>

        {selectedNode.type === 'TriggerNode' && (
          <div>
            <label className="block text-sm font-medium text-slate-700 mb-1">Workflow ID</label>
            <input 
              type="text" 
              value={selectedNode.data.workflowId || ''} 
              onChange={(e) => handleChange('workflowId', e.target.value)}
              className="w-full text-sm p-2 border border-slate-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
            />
          </div>
        )}
        
        {selectedNode.type === 'ApiNode' && (
          <div>
            <label className="block text-sm font-medium text-slate-700 mb-1">Endpoint URL</label>
            <input 
              type="text" 
              value={selectedNode.data.endpointUrl || ''} 
              onChange={(e) => handleChange('endpointUrl', e.target.value)}
              className="w-full text-sm p-2 border border-slate-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
              placeholder="https://api.example.com/v1"
            />
            
            <label className="block text-sm font-medium text-slate-700 mt-3 mb-1">Method</label>
            <select 
              value={selectedNode.data.method || 'GET'} 
              onChange={(e) => handleChange('method', e.target.value)}
              className="w-full text-sm p-2 border border-slate-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
            >
              <option value="GET">GET</option>
              <option value="POST">POST</option>
              <option value="PUT">PUT</option>
              <option value="DELETE">DELETE</option>
            </select>
          </div>
        )}
        
        {/* Dynamic Fields Example for other node types */}
        {selectedNode.type === 'DatabaseAction' && (
          <div>
            <label className="block text-sm font-medium text-slate-700 mb-1">Table Name</label>
            <input 
              type="text" 
              value={selectedNode.data.tableName || ''} 
              onChange={(e) => handleChange('tableName', e.target.value)}
              className="w-full text-sm p-2 border border-slate-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
              placeholder="e.ska_data_..."
            />
          </div>
        )}
      </div>
    </aside>
  );
}
