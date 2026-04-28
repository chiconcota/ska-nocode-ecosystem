import React from 'react';
import BaseNode from './BaseNode';
import { Search } from 'lucide-react';
import { Handle, Position } from '@xyflow/react';

export default function DBQueryNode({ data, isConnectable }) {
  const table = data.table || '';

  return (
    <BaseNode
      icon={<Search size={16} />}
      title="DB Query"
      colorClass="bg-slate-50"
      borderClass="border-slate-300"
      headerClass="bg-cyan-100 text-cyan-800 border-cyan-200"
      data={data}
    >
      <Handle
        type="target"
        position={Position.Top}
        isConnectable={isConnectable}
        className="w-3 h-3 bg-slate-400 border-2 border-white"
      />
      
      <div className="text-xs text-slate-600">
        <div className="font-semibold mb-1 border-b border-slate-200 pb-1">
          Loại: Lấy dữ liệu (Fetch)
        </div>
        <div className="truncate mb-1">
          Bảng: <span className="font-mono text-cyan-600 bg-cyan-50 px-1 rounded">{table || 'Chưa chọn'}</span>
        </div>
        {data.resultVar && (
          <div className="truncate text-[10px] text-slate-500">
            Lưu vào: <span className="font-mono text-slate-700 bg-slate-100 px-1 rounded">{data.resultVar}</span>
          </div>
        )}
      </div>

      <Handle
        type="source"
        position={Position.Bottom}
        isConnectable={isConnectable}
        className="w-3 h-3 bg-slate-400 border-2 border-white"
      />
    </BaseNode>
  );
}
