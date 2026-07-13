import React from 'react';
import BaseNode from './BaseNode';
import { Database } from 'lucide-react';
import { Handle, Position } from '@xyflow/react';

const { __ } = window.wp?.i18n || { __: (text) => text };

export default function DBActionNode({ data, isConnectable }) {
  const actionType = data.actionType || 'insert';
  const table = data.table || '';

  const actionLabels = {
    insert: __( 'Insert', 'skaaa-logic-engine' ),
    update: __( 'Update', 'skaaa-logic-engine' ),
    delete: __( 'Delete', 'skaaa-logic-engine' )
  };

  const actionColors = {
    insert: 'bg-emerald-100 text-emerald-800 border-emerald-200',
    update: 'bg-blue-100 text-blue-800 border-blue-200',
    delete: 'bg-rose-100 text-rose-800 border-rose-200'
  };

  const currentHeaderClass = actionColors[actionType] || 'bg-slate-100 text-slate-800 border-slate-200';

  return (
    <BaseNode
      icon={<Database size={16} />}
      title={__( 'DB Action', 'skaaa-logic-engine' )}
      colorClass="bg-slate-50"
      borderClass="border-slate-300"
      headerClass={currentHeaderClass}
      data={data}
    >
      {/* Input Handle */}
      <Handle
        type="target"
        position={Position.Top}
        isConnectable={isConnectable}
        className="w-3 h-3 bg-slate-400 border-2 border-white"
      />
      
      <div className="text-xs text-slate-600">
        <div className="font-semibold mb-1 border-b border-slate-200 pb-1">
          {__( 'Action', 'skaaa-logic-engine' )}: {actionLabels[actionType]}
        </div>
        <div className="truncate">
          {__( 'Table', 'skaaa-logic-engine' )}: <span className="font-mono text-blue-600 bg-blue-50 px-1 rounded">{table || __( 'Not selected', 'skaaa-logic-engine' )}</span>
        </div>
      </div>

      {/* Cổng ra */}
      <Handle
        type="source"
        position={Position.Bottom}
        isConnectable={isConnectable}
        className="w-3 h-3 bg-slate-400 border-2 border-white"
      />
    </BaseNode>
  );
}
