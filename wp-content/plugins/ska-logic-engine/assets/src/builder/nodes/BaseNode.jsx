import React from 'react';
import { Handle, Position } from '@xyflow/react';

export default function BaseNode({ data, icon, title, colorClass, borderClass, headerClass }) {
    return (
        <div className={`border-2 rounded-lg shadow-sm min-w-[220px] overflow-hidden bg-white ${borderClass}`}>
            <div className={`${headerClass} px-3 py-2 flex items-center gap-2 font-semibold text-sm border-b`}>
                {icon}
                {data.label || title}
            </div>
            
            <div className="p-3 text-sm text-gray-600">
                {data.description && <p className="text-xs mb-2">{data.description}</p>}
                {Object.entries(data).map(([key, value]) => {
                    if (key === 'label' || key === 'description') return null;
                    return (
                        <div key={key} className="mb-2 last:mb-0">
                            <span className="text-xs font-medium text-gray-500 capitalize">{key}:</span>
                            <div className="font-mono bg-slate-50 px-2 py-1 rounded text-slate-800 border border-slate-200 mt-1 truncate max-w-[200px]">
                                {value || 'N/A'}
                            </div>
                        </div>
                    );
                })}
            </div>

            {/* Input Handle */}
            <Handle 
                type="target" 
                position={Position.Top} 
                className="w-3 h-3 border-2 border-white"
                style={{ background: '#94a3b8' }}
            />
            
            {/* Output Handle */}
            <Handle 
                type="source" 
                position={Position.Bottom} 
                className="w-3 h-3 border-2 border-white"
                style={{ background: '#94a3b8' }}
            />
        </div>
    );
}
