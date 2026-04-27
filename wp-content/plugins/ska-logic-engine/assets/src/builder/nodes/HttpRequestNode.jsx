import React from 'react';
import BaseNode from './BaseNode';
import { Globe } from 'lucide-react';
import { Handle, Position } from '@xyflow/react';

export default function HttpRequestNode({ data, isConnectable }) {
  const method = data.method || 'GET';
  const url = data.url || 'https://...';

  const methodColors = {
    GET: 'text-blue-700 bg-blue-100',
    POST: 'text-emerald-700 bg-emerald-100',
    PUT: 'text-amber-700 bg-amber-100',
    DELETE: 'text-rose-700 bg-rose-100'
  };

  const currentMethodClass = methodColors[method] || 'text-slate-700 bg-slate-100';

  return (
    <BaseNode
      icon={<Globe size={16} />}
      title="HTTP Request"
      colorClass="bg-slate-50"
      borderClass="border-slate-300"
      headerClass="bg-slate-100 text-slate-800 border-slate-200"
      data={data}
      hideInput={true}
      hideOutput={true}
    >
      {/* Cổng vào */}
      <Handle
        type="target"
        position={Position.Top}
        isConnectable={isConnectable}
        className="w-3 h-3 border-2 border-white"
        style={{ background: '#94a3b8' }}
      />
      
      <div className="text-xs text-slate-600">
        <div className="flex items-center gap-2 mb-1">
          <span className={`font-mono text-[10px] px-1.5 py-0.5 rounded font-bold ${currentMethodClass}`}>
            {method}
          </span>
          <span className="truncate flex-1 font-mono text-slate-500" title={url}>
            {url}
          </span>
        </div>
      </div>

      {/* Cổng Thành Công (Main) */}
      <Handle 
          type="source" 
          position={Position.Bottom} 
          id="main"
          className="w-3 h-3 border-2 border-white"
          style={{ left: '30%', background: '#10b981' }}
          isConnectable={isConnectable}
      />
      {/* Cổng Lỗi (Error) */}
      <Handle 
          type="source" 
          position={Position.Bottom} 
          id="error"
          className="w-3 h-3 border-2 border-white"
          style={{ left: '70%', background: '#f43f5e' }}
          isConnectable={isConnectable}
      />
    </BaseNode>
  );
}
