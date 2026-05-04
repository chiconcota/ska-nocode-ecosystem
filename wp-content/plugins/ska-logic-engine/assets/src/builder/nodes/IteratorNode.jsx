import React from 'react';
import { Handle, Position, NodeResizer } from '@xyflow/react';
import { Repeat } from 'lucide-react';

export default function IteratorNode({ data, selected }) {
    return (
        <>
            <NodeResizer color="#d946ef" isVisible={selected} minWidth={250} minHeight={150} />
            <div className={`w-full h-full border-2 rounded-lg shadow-sm bg-fuchsia-50/30 border-fuchsia-300 relative`}>
                <div className="bg-fuchsia-100 text-fuchsia-900 border-fuchsia-200 px-3 py-2 flex items-center gap-2 font-semibold text-sm border-b rounded-t-md">
                    <Repeat size={16} />
                    {data.label || 'Iterator / Loop'}
                </div>
                
                <div className="p-3 text-sm text-gray-600">
                    <div className="mb-2">
                        <span className="text-xs font-medium text-gray-500">Array Source:</span>
                        <div className="font-mono bg-white px-2 py-1 rounded text-slate-800 border border-slate-200 mt-1 truncate">
                            {data.array_source || 'N/A'}
                        </div>
                    </div>
                </div>

                <Handle 
                    type="target" 
                    position={Position.Top} 
                    className="w-3 h-3 border-2 border-white"
                    style={{ background: '#d946ef' }}
                />
                
                <Handle 
                    type="source" 
                    position={Position.Bottom} 
                    className="w-3 h-3 border-2 border-white"
                    style={{ background: '#d946ef' }}
                />
            </div>
        </>
    );
}
