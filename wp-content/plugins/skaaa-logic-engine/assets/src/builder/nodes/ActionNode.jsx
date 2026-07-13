import React from 'react';
import { Handle, Position } from '@xyflow/react';
import { Settings2 } from 'lucide-react';

export default function ActionNode({ data, selected }) {
    const isProcessor = data.nodeType === 'processor';
    const colorHeader = isProcessor ? 'bg-amber-100 text-amber-800 border-amber-200' : 'bg-emerald-100 text-emerald-800 border-emerald-200';
    const borderColor = selected ? 'border-blue-500 shadow-md ring-2 ring-blue-200' : 'border-gray-200 shadow-sm';
    const iconColor = isProcessor ? '#d97706' : '#059669';

    return (
        <div className={`bg-white border-2 rounded-lg min-w-[250px] transition-all ${borderColor}`}>
            {/* Cổng Nhận Data */}
            <Handle 
                type="target" 
                position={Position.Top} 
                id="input"
                className="w-3 h-3 bg-blue-500 border-2 border-white"
            />

            <div className={`${colorHeader} px-3 py-2 flex items-center justify-between text-sm font-semibold border-b`}>
                <div className="flex items-center gap-2">
                    <span style={{color: iconColor}}><Settings2 size={16} /></span>
                    {data.label || 'Action'}
                </div>
                {/* Async badge */}
                {data.async && (
                    <span className="bg-purple-100 text-purple-700 text-[10px] px-1.5 py-0.5 rounded font-bold uppercase">Async</span>
                )}
            </div>

            <div className="p-3 text-xs text-gray-500 bg-white">
                <div>Class: <span className="font-mono text-gray-800">{data.class}</span></div>
                {data.tableName && (
                    <div className="mt-1">DB: <span className="font-mono text-blue-600 font-bold">{data.tableName}</span></div>
                )}
            </div>

            {/* Cổng Thành Công (Main) */}
            <Handle 
                type="source" 
                position={Position.Bottom} 
                id="main"
                className="w-3 h-3 bg-green-500 border-2 border-white"
                style={{ left: '30%' }}
            />
            {/* Cổng Lỗi (Error) */}
            <Handle 
                type="source" 
                position={Position.Bottom} 
                id="error"
                className="w-3 h-3 bg-red-500 border-2 border-white"
                style={{ left: '70%' }}
            />
        </div>
    );
}
