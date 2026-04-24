import React from 'react';
import { 
  Zap, 
  GitBranch, 
  Globe, 
  AlertTriangle, 
  ServerCog,
  Database,
  Mail
} from 'lucide-react';

const NODE_TYPES = [
  {
    type: 'TriggerNode',
    label: 'Trigger Event',
    icon: <Zap size={16} />,
    description: 'Start workflow on event',
    color: 'bg-red-50 text-red-700 border-red-200'
  },
  {
    type: 'ActionNode',
    label: 'Database Action',
    icon: <Database size={16} />,
    description: 'Read/Write to DB',
    color: 'bg-blue-50 text-blue-700 border-blue-200'
  },
  {
    type: 'ConditionNode',
    label: 'If/Else',
    icon: <GitBranch size={16} />,
    description: 'Branching logic',
    color: 'bg-amber-50 text-amber-700 border-amber-200'
  },
  {
    type: 'ApiNode',
    label: 'HTTP Request',
    icon: <Globe size={16} />,
    description: 'Call external API',
    color: 'bg-emerald-50 text-emerald-700 border-emerald-200'
  },
  {
    type: 'ErrorNode',
    label: 'Catch Error',
    icon: <AlertTriangle size={16} />,
    description: 'Handle exceptions',
    color: 'bg-rose-50 text-rose-700 border-rose-200'
  },
  {
    type: 'BackgroundNode',
    label: 'Background Job',
    icon: <ServerCog size={16} />,
    description: 'Run asynchronously',
    color: 'bg-purple-50 text-purple-700 border-purple-200'
  }
];

export default function Sidebar() {
  const onDragStart = (event, nodeType, label) => {
    event.dataTransfer.setData('application/reactflow', nodeType);
    event.dataTransfer.setData('application/reactflow-label', label);
    event.dataTransfer.effectAllowed = 'move';
  };

  return (
    <aside className="w-64 bg-white border-r border-slate-200 flex flex-col h-full shadow-sm z-10">
      <div className="p-4 border-b border-slate-100 bg-slate-50">
        <h3 className="font-semibold text-slate-800 flex items-center gap-2">
          <Zap size={18} className="text-amber-500" />
          Nodes
        </h3>
        <p className="text-xs text-slate-500 mt-1">
          Kéo thả node vào đồ thị
        </p>
      </div>
      
      <div className="p-3 flex-1 overflow-y-auto space-y-3">
        {NODE_TYPES.map((node) => (
          <div
            key={node.type}
            className={`p-3 border rounded-lg cursor-grab hover:shadow-md transition-shadow ${node.color}`}
            onDragStart={(event) => onDragStart(event, node.type, node.label)}
            draggable
          >
            <div className="flex items-center gap-2 font-medium text-sm mb-1">
              {node.icon}
              {node.label}
            </div>
            <div className="text-xs opacity-80">{node.description}</div>
          </div>
        ))}
      </div>
    </aside>
  );
}
