import React from 'react';
import { Handle, Position } from '@xyflow/react';
import { Zap } from 'lucide-react';

export default function TriggerNode({ data }) {
    return (
        <div className="bg-red-50 border-2 border-red-200 rounded-lg shadow-sm min-w-[250px] overflow-hidden">
            <div className="bg-red-100 text-red-800 px-3 py-2 flex items-center gap-2 font-semibold text-sm border-b border-red-200">
                <Zap size={16} />
                {data.label || 'Trigger'}
            </div>
            <div className="p-3 text-sm text-gray-600 bg-white">
                <p className="mb-2 text-xs">Mã định danh (Form ID):</p>
                <div className="font-mono bg-gray-50 px-2 py-1 rounded text-gray-800 border border-gray-200">
                    {data.workflowId || 'default'}
                </div>
            </div>
            {/* Cổng ra chính - xanh lá */}
            <Handle 
                type="source" 
                position={Position.Bottom} 
                id="main" 
                className="w-3 h-3 bg-green-500 border-2 border-white"
            />
        </div>
    );
}
