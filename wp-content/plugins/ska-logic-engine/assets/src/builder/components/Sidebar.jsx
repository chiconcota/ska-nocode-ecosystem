import React from 'react';
import { 
  Zap, 
  GitBranch, 
  GitMerge,
  Globe, 
  AlertTriangle, 
  ServerCog,
  Database,
  Mail,
  Variable
} from 'lucide-react';

const NODE_TYPES = [
  {
    type: 'TriggerNode',
    backendClass: 'Ska_Logic_Trigger_Node',
    label: 'Trigger Node',
    icon: <Zap size={16} />,
    description: 'Start workflow on event',
    color: 'bg-red-50 text-red-700 border-red-200'
  },
  {
    type: 'SetDataNode',
    backendClass: 'Ska_Logic_Set_Data',
    label: 'Set Data',
    icon: <Variable size={16} />,
    description: 'Assign variables',
    color: 'bg-indigo-50 text-indigo-700 border-indigo-200'
  },
  {
    type: 'DBActionNode',
    backendClass: 'Ska_Logic_DB_Action',
    label: 'DB CRUD Action',
    icon: <Database size={16} />,
    description: 'Read/Write to DB',
    color: 'bg-emerald-50 text-emerald-700 border-emerald-200'
  },
  {
    type: 'ConditionNode',
    backendClass: 'Ska_Condition_Node',
    label: 'If/Else',
    icon: <GitBranch size={16} />,
    description: 'Branching logic',
    color: 'bg-amber-50 text-amber-700 border-amber-200'
  },
  {
    type: 'SwitchNode',
    backendClass: 'Ska_Logic_Switch',
    label: 'Switch Router',
    icon: <GitMerge size={16} />,
    description: 'Multi-branch routing',
    color: 'bg-purple-50 text-purple-700 border-purple-200'
  },
  {
    type: 'ApiNode',
    backendClass: 'Ska_Logic_Http_Request',
    label: 'HTTP Request',
    icon: <Globe size={16} />,
    description: 'Call external API',
    color: 'bg-emerald-50 text-emerald-700 border-emerald-200'
  },
  {
    type: 'ErrorNode',
    backendClass: 'Ska_Error_Node',
    label: 'Catch Error',
    icon: <AlertTriangle size={16} />,
    description: 'Handle exceptions',
    color: 'bg-rose-50 text-rose-700 border-rose-200'
  },
  {
    type: 'BackgroundNode',
    backendClass: 'Ska_Background_Node',
    label: 'Background Job',
    icon: <ServerCog size={16} />,
    description: 'Run asynchronously',
    color: 'bg-purple-50 text-purple-700 border-purple-200'
  },
  {
    type: 'ClientResponseNode',
    backendClass: 'Ska_Logic_Client_Response',
    label: 'Client Response',
    icon: <span className="material-symbols-outlined text-[16px]">reply</span>,
    description: 'Send UI commands (Toast/Modal)',
    color: 'bg-teal-50 text-teal-700 border-teal-200'
  }
];

export default function Sidebar() {
  const onDragStart = (event, node) => {
    event.dataTransfer.setData('application/reactflow', node.type);
    event.dataTransfer.setData('application/reactflow-label', node.label);
    event.dataTransfer.setData('application/reactflow-class', node.backendClass || '');
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
            onDragStart={(event) => onDragStart(event, node)}
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
