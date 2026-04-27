import React, { useState, useCallback, useRef } from 'react';
import {
  ReactFlow,
  ReactFlowProvider,
  MiniMap,
  Controls,
  Background,
  useNodesState,
  useEdgesState,
  addEdge,
  useReactFlow,
} from '@xyflow/react';
import '@xyflow/react/dist/style.css';
import { GitBranch, Globe, AlertTriangle, ServerCog } from 'lucide-react';

import TriggerNode from './nodes/TriggerNode';
import ActionNode from './nodes/ActionNode';
import SetDataNode from './nodes/SetDataNode';
import ConditionNode from './nodes/ConditionNode';
import SwitchNode from './nodes/SwitchNode';
import DBActionNode from './nodes/DBActionNode';
import BaseNode from './nodes/BaseNode';
import HttpRequestNode from './nodes/HttpRequestNode';
import Sidebar from './components/Sidebar';
import SettingsPanel from './components/SettingsPanel';

const nodeTypes = {
  TriggerNode: TriggerNode,
  ActionNode: ActionNode,
  SetDataNode: SetDataNode,
  ConditionNode: ConditionNode,
  SwitchNode: SwitchNode,
  DBActionNode: DBActionNode,
  ApiNode: HttpRequestNode,
  ErrorNode: (props) => <BaseNode {...props} icon={<AlertTriangle size={16} />} title="Catch Error" colorClass="bg-rose-50" borderClass="border-rose-200" headerClass="bg-rose-100 text-rose-800 border-rose-200" />,
  BackgroundNode: (props) => <BaseNode {...props} icon={<ServerCog size={16} />} title="Background Job" colorClass="bg-purple-50" borderClass="border-purple-200" headerClass="bg-purple-100 text-purple-800 border-purple-200" />
};

let id = 0;
const getId = () => `dndnode_${id++}`;

function DnDFlow() {
  const reactFlowWrapper = useRef(null);
  const [nodes, setNodes, onNodesChange] = useNodesState([]);
  const [edges, setEdges, onEdgesChange] = useEdgesState([]);
  const [selectedNodeId, setSelectedNodeId] = useState(null);
  const { screenToFlowPosition } = useReactFlow();

  // Load context from WordPress
  React.useEffect(() => {
    const ctx = window.SKA_DAG_CONTEXT || {};
    const graphData = ctx.CURRENT_GRAPH;
    
    if (graphData && graphData.nodes && graphData.edges) {
      setNodes(graphData.nodes);
      setEdges(graphData.edges);
      
      // Update our ID counter so we don't conflict
      const maxId = graphData.nodes.reduce((max, node) => {
        const num = parseInt(node.id.replace('dndnode_', ''), 10);
        return (!isNaN(num) && num > max) ? num : max;
      }, 0);
      id = maxId + 1;
    } else {
      setNodes([
        {
          id: 'trigger_1',
          type: 'TriggerNode',
          class: 'Ska_Logic_Trigger_Node',
          position: { x: 250, y: 50 },
          data: { label: 'Trigger Node', workflowId: ctx.CURRENT_WF_ID || 'default' }
        }
      ]);
    }
  }, [setNodes, setEdges]);

  // Sync state back to hidden input for saving
  React.useEffect(() => {
    const input = document.getElementById('skaLinearGraphInput');
    if (input) {
      input.value = JSON.stringify({ nodes, edges });
    }
  }, [nodes, edges]);

  const onConnect = useCallback(
    (params) => setEdges((eds) => addEdge({ ...params, animated: true }, eds)),
    [setEdges]
  );

  const onDragOver = useCallback((event) => {
    event.preventDefault();
    event.dataTransfer.dropEffect = 'move';
  }, []);

  const onDrop = useCallback(
    (event) => {
      event.preventDefault();

      const type = event.dataTransfer.getData('application/reactflow');
      const label = event.dataTransfer.getData('application/reactflow-label');
      const backendClass = event.dataTransfer.getData('application/reactflow-class');

      if (typeof type === 'undefined' || !type) {
        return;
      }

      // Project pixel drop position to React Flow coordinates
      const position = screenToFlowPosition({
        x: event.clientX,
        y: event.clientY,
      });
      
      const newNode = {
        id: getId(),
        type,
        class: backendClass || '',
        position,
        data: { label: label },
      };

      setNodes((nds) => nds.concat(newNode));
      setSelectedNodeId(newNode.id); // Auto-select on drop
    },
    [screenToFlowPosition, setNodes]
  );

  const onNodeClick = useCallback((event, node) => {
    setSelectedNodeId(node.id);
  }, []);

  const onPaneClick = useCallback(() => {
    setSelectedNodeId(null);
  }, []);

  const handleUpdateNode = useCallback((id, newData) => {
    setNodes((nds) =>
      nds.map((node) => {
        if (node.id === id) {
          return { ...node, data: newData };
        }
        return node;
      })
    );
  }, [setNodes]);

  const handleDeleteNode = useCallback((id) => {
    setNodes((nds) => nds.filter((node) => node.id !== id));
    setEdges((eds) => eds.filter((edge) => edge.source !== id && edge.target !== id));
    setSelectedNodeId(null);
  }, [setNodes, setEdges]);

  const selectedNode = nodes.find(n => n.id === selectedNodeId);

  return (
    <div className="flex h-full w-full">
      <Sidebar />
      <div className="flex-1 h-full" ref={reactFlowWrapper}>
        <ReactFlow
          nodes={nodes}
          edges={edges}
          onNodesChange={onNodesChange}
          onEdgesChange={onEdgesChange}
          onConnect={onConnect}
          onDrop={onDrop}
          onDragOver={onDragOver}
          onNodeClick={onNodeClick}
          onPaneClick={onPaneClick}
          nodeTypes={nodeTypes}
          fitView
        >
          <Controls />
          <MiniMap />
          <Background variant="dots" gap={12} size={1} />
        </ReactFlow>
      </div>
      {selectedNode && (
        <SettingsPanel 
          selectedNode={selectedNode} 
          onUpdateNode={handleUpdateNode}
          onDeleteNode={handleDeleteNode}
          onClose={() => setSelectedNodeId(null)}
        />
      )}
    </div>
  );
}

export default function App() {
  return (
    <ReactFlowProvider>
      <DnDFlow />
    </ReactFlowProvider>
  );
}
