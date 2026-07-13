import React from 'react';
import BaseNode from './BaseNode';
import { Variable } from 'lucide-react';

export default function SetDataNode(props) {
    const { data } = props;
    
    // Filter out assignments from being displayed as generic Object.entries in BaseNode
    const filteredData = Object.keys(data).reduce((acc, key) => {
        if (key !== 'assignments') {
            acc[key] = data[key];
        }
        return acc;
    }, {});

    let customContent = null;
    if (data.assignments && data.assignments.length > 0) {
        const preview = data.assignments.slice(0, 2).map(a => `${a.key}: ${a.value}`).join(', ');
        const more = data.assignments.length > 2 ? '...' : '';
        customContent = (
            <div className="px-3 pb-3 text-xs text-indigo-600 truncate">
                <span className="font-medium">Sets:</span> <span className="font-mono bg-indigo-50 px-1 rounded">{preview}{more}</span>
            </div>
        );
    }

    return (
        <BaseNode 
            {...props} 
            data={filteredData}
            icon={<Variable size={16} />} 
            title="Set Data" 
            colorClass="bg-indigo-50" 
            borderClass="border-indigo-200" 
            headerClass="bg-indigo-100 text-indigo-800 border-indigo-200" 
        >
            {customContent}
        </BaseNode>
    );
}
