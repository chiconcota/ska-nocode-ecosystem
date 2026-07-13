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

// Map icon name from PHP to React Component
const iconMap = {
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
};

const getIconComponent = (iconName) => {
  if (iconName === 'reply') {
    return <span className="material-symbols-outlined text-[16px]">reply</span>;
  }
  const IconComponent = iconMap[iconName];
  if (IconComponent) {
    return <IconComponent size={16} />;
  }
  return <ServerCog size={16} />; // Fallback default icon
};

// Fallback nodes array if context is not available
const DEFAULT_NODE_TYPES = [
  {
    type: 'TriggerNode',
    backendClass: 'Skaaa_Logic_Trigger_Node',
    label: __( 'Trigger Node', 'skaaa-logic-engine' ),
    icon: 'Zap',
    description: __( 'Start workflow on event', 'skaaa-logic-engine' ),
    color: 'bg-red-50 text-red-700 border-red-200',
    category: 'trigger'
  },
  {
    type: 'SetDataNode',
    backendClass: 'Skaaa_Logic_Set_Data',
    label: __( 'Set Data', 'skaaa-logic-engine' ),
    icon: 'Variable',
    description: __( 'Assign variables', 'skaaa-logic-engine' ),
    color: 'bg-indigo-50 text-indigo-700 border-indigo-200',
    category: 'data'
  },
  {
    type: 'DBActionNode',
    backendClass: 'Skaaa_Logic_DB_Action',
    label: __( 'DB CRUD Action', 'skaaa-logic-engine' ),
    icon: 'Database',
    description: __( 'Read/Write to DB', 'skaaa-logic-engine' ),
    color: 'bg-emerald-50 text-emerald-700 border-emerald-200',
    category: 'data'
  },
  {
    type: 'DBQueryNode',
    backendClass: 'Skaaa_Logic_DB_Query',
    label: __( 'DB Query', 'skaaa-logic-engine' ),
    icon: 'Search',
    description: __( 'Fetch from DB', 'skaaa-logic-engine' ),
    color: 'bg-cyan-50 text-cyan-700 border-cyan-200',
    category: 'data'
  },
  {
    type: 'ConditionNode',
    backendClass: 'Skaaa_Condition_Node',
    label: __( 'If/Else', 'skaaa-logic-engine' ),
    icon: 'GitBranch',
    description: __( 'Branching logic', 'skaaa-logic-engine' ),
    color: 'bg-amber-50 text-amber-700 border-amber-200',
    category: 'logic'
  },
  {
    type: 'SwitchNode',
    backendClass: 'Skaaa_Logic_Switch',
    label: __( 'Switch Router', 'skaaa-logic-engine' ),
    icon: 'GitMerge',
    description: __( 'Multi-branch routing', 'skaaa-logic-engine' ),
    color: 'bg-purple-50 text-purple-700 border-purple-200',
    category: 'logic'
  },
  {
    type: 'IteratorNode',
    backendClass: 'Skaaa_Logic_Iterator',
    label: __( 'Iterator / Loop', 'skaaa-logic-engine' ),
    icon: 'Repeat',
    description: __( 'Loop over items', 'skaaa-logic-engine' ),
    color: 'bg-fuchsia-50 text-fuchsia-700 border-fuchsia-200',
    category: 'logic'
  },
  {
    type: 'ApiNode',
    backendClass: 'Skaaa_Logic_Http_Request',
    label: __( 'HTTP Request', 'skaaa-logic-engine' ),
    icon: 'Globe',
    description: __( 'Call external API', 'skaaa-logic-engine' ),
    color: 'bg-emerald-50 text-emerald-700 border-emerald-200',
    category: 'data'
  },
  {
    type: 'ClientResponseNode',
    backendClass: 'Skaaa_Logic_Client_Response',
    label: __( 'Client Response', 'skaaa-logic-engine' ),
    icon: 'reply',
    description: __( 'Send UI commands (Toast/Modal)', 'skaaa-logic-engine' ),
    color: 'bg-teal-50 text-teal-700 border-teal-200',
    category: 'presentation'
  },
  {
    type: 'RenderTemplateNode',
    backendClass: 'Skaaa_Logic_Render_Template',
    label: __( 'Render Template', 'skaaa-logic-engine' ),
    icon: 'FileCode2',
    description: __( 'Interpolate data into HTML template', 'skaaa-logic-engine' ),
    color: 'bg-sky-50 text-sky-700 border-sky-200',
    category: 'presentation'
  }
];

export default function Sidebar() {
  const rawNodes = window.SKAAA_DAG_CONTEXT?.AVAILABLE_NODES || [];
  const nodesList = rawNodes.length > 0 ? rawNodes : DEFAULT_NODE_TYPES;

  const onDragStart = (event, node) => {
    event.dataTransfer.setData('application/reactflow', node.type);
    event.dataTransfer.setData('application/reactflow-label', node.label);
    event.dataTransfer.setData('application/reactflow-class', node.class || node.backendClass || '');
    event.dataTransfer.effectAllowed = 'move';
  };

  return (
    <aside className="w-64 bg-white border-r border-slate-200 flex flex-col h-full shadow-sm z-10">
      <div className="p-4 border-b border-slate-100 bg-slate-50">
        <h3 className="font-semibold text-slate-800 flex items-center gap-2">
          <Zap size={18} className="text-amber-500" />
          {__( 'Nodes', 'skaaa-logic-engine' )}
        </h3>
        <p className="text-xs text-slate-500 mt-1">
          {__( 'Drag and drop nodes into the graph', 'skaaa-logic-engine' )}
        </p>
      </div>
      
      <div className="p-3 flex-1 overflow-y-auto space-y-3">
        {nodesList.map((node) => (
          <div
            key={node.type}
            className={`p-3 border rounded-lg transition-shadow relative ${node.disabled ? 'cursor-not-allowed opacity-60 grayscale' : 'cursor-grab hover:shadow-md'} ${node.color}`}
            onDragStart={(event) => !node.disabled && onDragStart(event, node)}
            draggable={!node.disabled}
          >
            {node.disabled && (
              <span className="absolute top-2 right-2 text-[8px] bg-slate-200 text-slate-500 px-1 rounded uppercase font-bold">{__( 'Soon', 'skaaa-logic-engine' )}</span>
            )}
            <div className="flex items-center gap-2 font-medium text-sm mb-1">
              {getIconComponent(node.icon)}
              {node.label}
            </div>
            <div className="text-xs opacity-80">{node.description}</div>
          </div>
        ))}
      </div>
    </aside>
  );
}
