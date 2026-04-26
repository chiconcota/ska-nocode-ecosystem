import React from 'react';
import { Handle, Position } from '@xyflow/react';
import { GitMerge } from 'lucide-react';
import BaseNode from './BaseNode';

export default function SwitchNode({ data }) {
    const routes = data.routes || [];
    
    // We have N routes + 1 default route
    const totalHandles = routes.length + 1;
    
    // Calculate evenly spaced positions (percentages) for handles
    const getPosition = (index) => {
        if (totalHandles === 1) return '50%';
        // Calculate spread so handles aren't at the very edges
        const spacing = 100 / totalHandles;
        return `${(spacing / 2) + (index * spacing)}%`;
    };

    return (
        <BaseNode
            data={data}
            icon={<GitMerge size={16} />}
            title="Switch Router"
            colorClass="bg-purple-50 text-purple-700"
            borderClass="border-purple-200"
            headerClass="bg-purple-100/50 text-purple-800"
            hideOutput={true}
        >
            <div className="px-3 pb-3">
                <div className="bg-purple-50/80 border border-purple-100 rounded p-2 text-xs font-mono text-purple-900 flex flex-col gap-1">
                    {routes.length === 0 ? (
                        <div className="text-slate-500 italic">No routes configured</div>
                    ) : (
                        routes.map((route, idx) => (
                            <div key={route.id || idx} className="flex justify-between items-start gap-2 border-b border-purple-100/50 last:border-0 pb-1 last:pb-0">
                                <span className="font-semibold text-purple-700 whitespace-nowrap">{route.name || `Route ${idx + 1}`}:</span>
                                <span className="truncate text-right opacity-80" title={route.expression}>{route.expression || 'empty'}</span>
                            </div>
                        ))
                    )}
                </div>
            </div>
            
            {/* Custom Output Handles for Switch Routes */}
            {routes.map((route, idx) => (
                <div key={route.id || `route_${idx}`}>
                    <Handle 
                        type="source" 
                        position={Position.Bottom} 
                        id={route.id}
                        className="w-3 h-3 border-2 border-white"
                        style={{ background: '#a855f7', left: getPosition(idx) }} // Purple for conditions
                    />
                    <div 
                        className="absolute text-[9px] font-bold text-purple-600 truncate max-w-[40px] text-center"
                        style={{ bottom: '-20px', left: getPosition(idx), transform: 'translateX(-50%)' }}
                        title={route.name || `Route ${idx + 1}`}
                    >
                        {route.name || `R${idx + 1}`}
                    </div>
                </div>
            ))}

            {/* Default Handle */}
            <Handle 
                type="source" 
                position={Position.Bottom} 
                id="default"
                className="w-3 h-3 border-2 border-white"
                style={{ background: '#64748b', left: getPosition(routes.length) }} // Slate for default
            />
            <div 
                className="absolute text-[9px] font-bold text-slate-500"
                style={{ bottom: '-20px', left: getPosition(routes.length), transform: 'translateX(-50%)' }}
            >
                Default
            </div>
        </BaseNode>
    );
}
