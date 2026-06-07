import React from 'react';
import { 
  Zap, 
  GitBranch, 
  GitMerge,
  Globe, 
  AlertTriangle, 
  ServerCog,
  Database,
  Search,
  Mail,
  Variable,
  FileCode2,
  Repeat
} from 'lucide-react';

const { __ } = window.wp?.i18n || { __: (text) => text };

const NODE_TYPES = [
  {
    type: 'TriggerNode',
    backendClass: 'Ska_Logic_Trigger_Node',
    label: __( 'Trigger Node', 'ska-logic-engine' ),
    icon: <Zap size={16} />,
    description: __( 'Start workflow on event', 'ska-logic-engine' ),
    color: 'bg-red-50 text-red-700 border-red-200'
  },
  {
    type: 'SetDataNode',
    backendClass: 'Ska_Logic_Set_Data',
    label: __( 'Set Data', 'ska-logic-engine' ),
    icon: <Variable size={16} />,
    description: __( 'Assign variables', 'ska-logic-engine' ),
    color: 'bg-indigo-50 text-indigo-700 border-indigo-200'
  },
  {
    type: 'DBActionNode',
    backendClass: 'Ska_Logic_DB_Action',
    label: __( 'DB CRUD Action', 'ska-logic-engine' ),
    icon: <Database size={16} />,
    description: __( 'Read/Write to DB', 'ska-logic-engine' ),
    color: 'bg-emerald-50 text-emerald-700 border-emerald-200'
  },
  {
    type: 'DBQueryNode',
    backendClass: 'Ska_Logic_DB_Query',
    label: __( 'DB Query', 'ska-logic-engine' ),
    icon: <Search size={16} />,
    description: __( 'Fetch from DB', 'ska-logic-engine' ),
    color: 'bg-cyan-50 text-cyan-700 border-cyan-200'
  },
  {
    type: 'ConditionNode',
    backendClass: 'Ska_Condition_Node',
    label: __( 'If/Else', 'ska-logic-engine' ),
    icon: <GitBranch size={16} />,
    description: __( 'Branching logic', 'ska-logic-engine' ),
    color: 'bg-amber-50 text-amber-700 border-amber-200'
  },
  {
    type: 'SwitchNode',
    backendClass: 'Ska_Logic_Switch',
    label: __( 'Switch Router', 'ska-logic-engine' ),
    icon: <GitMerge size={16} />,
    description: __( 'Multi-branch routing', 'ska-logic-engine' ),
    color: 'bg-purple-50 text-purple-700 border-purple-200'
  },
  {
    type: 'IteratorNode',
    backendClass: 'Ska_Logic_Iterator',
    label: __( 'Iterator / Loop', 'ska-logic-engine' ),
    icon: <Repeat size={16} />,
    description: __( 'Loop over items', 'ska-logic-engine' ),
    color: 'bg-fuchsia-50 text-fuchsia-700 border-fuchsia-200'
  },
  {
    type: 'ApiNode',
    backendClass: 'Ska_Logic_Http_Request',
    label: __( 'HTTP Request', 'ska-logic-engine' ),
    icon: <Globe size={16} />,
    description: __( 'Call external API', 'ska-logic-engine' ),
    color: 'bg-emerald-50 text-emerald-700 border-emerald-200'
  },
  {
    type: 'ErrorNode',
    backendClass: 'Ska_Error_Node',
    label: __( 'Catch Error', 'ska-logic-engine' ),
    icon: <AlertTriangle size={16} />,
    description: __( 'Handle exceptions', 'ska-logic-engine' ),
    color: 'bg-slate-50 text-slate-400 border-slate-200',
    disabled: true
  },
  {
    type: 'BackgroundNode',
    backendClass: 'Ska_Background_Node',
    label: __( 'Background Job', 'ska-logic-engine' ),
    icon: <ServerCog size={16} />,
    description: __( 'Run asynchronously', 'ska-logic-engine' ),
    color: 'bg-slate-50 text-slate-400 border-slate-200',
    disabled: true
  },
  {
    type: 'ClientResponseNode',
    backendClass: 'Ska_Logic_Client_Response',
    label: __( 'Client Response', 'ska-logic-engine' ),
    icon: <span className="material-symbols-outlined text-[16px]">reply</span>,
    description: __( 'Send UI commands (Toast/Modal)', 'ska-logic-engine' ),
    color: 'bg-teal-50 text-teal-700 border-teal-200'
  },
  {
    type: 'RenderTemplateNode',
    backendClass: 'Ska_Logic_Render_Template',
    label: __( 'Render Template', 'ska-logic-engine' ),
    icon: <FileCode2 size={16} />,
    description: __( 'Interpolate data into HTML template', 'ska-logic-engine' ),
    color: 'bg-sky-50 text-sky-700 border-sky-200'
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
          {__( 'Nodes', 'ska-logic-engine' )}
        </h3>
        <p className="text-xs text-slate-500 mt-1">
          {__( 'Drag and drop nodes into the graph', 'ska-logic-engine' )}
        </p>
      </div>
      
      <div className="p-3 flex-1 overflow-y-auto space-y-3">
        {NODE_TYPES.map((node) => (
          <div
            key={node.type}
            className={`p-3 border rounded-lg transition-shadow relative ${node.disabled ? 'cursor-not-allowed opacity-60 grayscale' : 'cursor-grab hover:shadow-md'} ${node.color}`}
            onDragStart={(event) => !node.disabled && onDragStart(event, node)}
            draggable={!node.disabled}
          >
            {node.disabled && (
              <span className="absolute top-2 right-2 text-[8px] bg-slate-200 text-slate-500 px-1 rounded uppercase font-bold">{__( 'Soon', 'ska-logic-engine' )}</span>
            )}
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
