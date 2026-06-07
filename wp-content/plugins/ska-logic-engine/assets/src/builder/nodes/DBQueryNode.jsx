import React from 'react';
import BaseNode from './BaseNode';
import { Search } from 'lucide-react';
import { Handle, Position } from '@xyflow/react';

const { __ } = window.wp?.i18n || { __: (text) => text };

export default function DBQueryNode({ data, isConnectable }) {
  const table = data.table || '';

  return (
    <BaseNode
      icon={<Search size={16} />}
      title={__( 'DB Query', 'ska-logic-engine' )}
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
          {__( 'Type', 'ska-logic-engine' )}: {__( 'Fetch Data', 'ska-logic-engine' )}
        </div>
        <div className="truncate mb-1">
          {__( 'Table', 'ska-logic-engine' )}: <span className="font-mono text-cyan-600 bg-cyan-50 px-1 rounded">{table || __( 'Not selected', 'ska-logic-engine' )}</span>
        </div>
        {data.resultVar && (
          <div className="truncate text-[10px] text-slate-500">
            {__( 'Save to', 'ska-logic-engine' )}: <span className="font-mono text-slate-700 bg-slate-100 px-1 rounded">{data.resultVar}</span>
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
