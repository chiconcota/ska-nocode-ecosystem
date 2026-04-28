import React from 'react';
import BaseNode from './BaseNode';
import { FileCode2 } from 'lucide-react';

export default function RenderTemplateNode(props) {
    const { data } = props;
    
    // Tùy chọn hiển thị nội dung trên Node
    let customContent = null;
    const sourceType = data.source_type || 'system';
    
    if (sourceType === 'system' && data.organism_id) {
        customContent = (
            <div className="px-3 pb-3 text-xs text-sky-600 truncate">
                <span className="font-medium">Template:</span> <span className="font-mono bg-sky-50 px-1 rounded">{data.organism_id}</span>
            </div>
        );
    } else if (sourceType === 'raw' && data.raw_template) {
        customContent = (
            <div className="px-3 pb-3 text-xs text-sky-600 truncate">
                <span className="font-medium">Từ biến:</span> <span className="font-mono bg-sky-50 px-1 rounded">{data.raw_template}</span>
            </div>
        );
    }

    return (
        <BaseNode 
            {...props} 
            data={data}
            icon={<FileCode2 size={16} />} 
            title="Render Template" 
            colorClass="bg-sky-50" 
            borderClass="border-sky-200" 
            headerClass="bg-sky-100 text-sky-800 border-sky-200" 
        >
            {customContent}
        </BaseNode>
    );
}
