import React from 'react';
import BaseNode from './BaseNode';
import { FileCode2 } from 'lucide-react';

export default function RenderTemplateNode(props) {
    const { data } = props;
    
    // Tùy chọn hiển thị nội dung trên Node
    let customContent = null;
    const templateHtml = data.template_html || data.raw_template || data.organism_id || '';
    let displayTemplate = '';
    
    if (templateHtml) {
        const trimmed = templateHtml.trim();
        if (trimmed.length > 25) {
            displayTemplate = trimmed.substring(0, 25) + '...';
        } else {
            displayTemplate = trimmed;
        }
        
        customContent = (
            <div className="px-3 pb-3 text-xs text-sky-600 max-w-[260px] overflow-hidden text-ellipsis whitespace-nowrap">
                <span className="font-medium">Template:</span> <span className="font-mono bg-sky-50 px-1 rounded" title={templateHtml}>{displayTemplate}</span>
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
