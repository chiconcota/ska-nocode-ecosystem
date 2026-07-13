import React from 'react';
import { Handle, Position } from '@xyflow/react';
import { GitBranch } from 'lucide-react';
import BaseNode from './BaseNode';

export default function ConditionNode({ data }) {
    return (
        <BaseNode
            data={data}
            icon={<GitBranch size={16} />}
            title="If/Else Condition"
            colorClass="bg-amber-50 text-amber-700"
            borderClass="border-amber-200"
            headerClass="bg-amber-100/50 text-amber-800"
            hideOutput={true}
        >
            <div className="px-3 pb-3">
                <div className="bg-amber-50/80 border border-amber-100 rounded p-2 text-xs font-mono text-amber-900 break-words">
                    <span className="font-semibold text-amber-700 block mb-1">If condition:</span>
                    {data.expression || 'No condition set'}
                </div>
            </div>
            
            {/* Custom Output Handles for Condition */}
            <Handle 
                type="source" 
                position={Position.Bottom} 
                id="true"
                className="w-3 h-3 border-2 border-white"
                style={{ background: '#10b981', left: '30%' }} // Green for True
            />
            <div 
                className="absolute text-[10px] font-bold text-emerald-600"
                style={{ bottom: '-18px', left: '30%', transform: 'translateX(-50%)' }}
            >
                TRUE
            </div>

            <Handle 
                type="source" 
                position={Position.Bottom} 
                id="false"
                className="w-3 h-3 border-2 border-white"
                style={{ background: '#f43f5e', left: '70%' }} // Red for False
            />
            <div 
                className="absolute text-[10px] font-bold text-rose-600"
                style={{ bottom: '-18px', left: '70%', transform: 'translateX(-50%)' }}
            >
                FALSE
            </div>
        </BaseNode>
    );
}
