import React from 'react';
import BaseNode from './BaseNode';
import { Zap } from 'lucide-react';

export default function TriggerNode(props) {
    return (
        <BaseNode 
            {...props} 
            icon={<Zap size={16} />} 
            title="Trigger Node" 
            colorClass="bg-red-50" 
            borderClass="border-red-200" 
            headerClass="bg-red-100 text-red-800 border-red-200" 
            hideInput={true} 
        />
    );
}
