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
import DBQueryNode from './nodes/DBQueryNode';
import BaseNode from './nodes/BaseNode';
import HttpRequestNode from './nodes/HttpRequestNode';
import ClientResponseNode from './nodes/ClientResponseNode';
import RenderTemplateNode from './nodes/RenderTemplateNode';
import IteratorNode from './nodes/IteratorNode';
import Sidebar from './components/Sidebar';
import SettingsPanel from './components/SettingsPanel';

const nodeTypes = {
  TriggerNode: TriggerNode,
  ActionNode: ActionNode,
  SetDataNode: SetDataNode,
  ConditionNode: ConditionNode,
  SwitchNode: SwitchNode,
  DBActionNode: DBActionNode,
  DBQueryNode: DBQueryNode,
  ApiNode: HttpRequestNode,
  ClientResponseNode: ClientResponseNode,
  RenderTemplateNode: RenderTemplateNode,
  IteratorNode: IteratorNode,
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
  const [viewMode, setViewMode] = useState('graph');
  const [jsonInput, setJsonInput] = useState('');
  const [jsonError, setJsonError] = useState(null);
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

  // Helper: find if a flow-coordinate point is inside any IteratorNode
  const findParentIterator = useCallback((flowPos, currentNodes, excludeId) => {
    return currentNodes.find((n) => {
      if (n.type !== 'IteratorNode') return false;
      if (n.id === excludeId) return false;
      const w = n.measured?.width ?? n.width ?? 280; // Default width for Iterator
      const h = n.measured?.height ?? n.height ?? 180; // Default height for Iterator
      
      // Slight padding to make it easier to drop inside
      const padding = 10;
      return (
        flowPos.x >= n.position.x - padding &&
        flowPos.x <= n.position.x + w + padding &&
        flowPos.y >= n.position.y - padding &&
        flowPos.y <= n.position.y + h + padding
      );
    });
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

      setNodes((nds) => {
        // Check if drop position is inside an IteratorNode
        const parent = findParentIterator(position, nds, newNode.id);
        if (parent && type !== 'IteratorNode') {
          // Convert absolute position to relative (inside parent)
          newNode.position = {
            x: position.x - parent.position.x,
            y: position.y - parent.position.y,
          };
          newNode.parentId = parent.id;
          newNode.extent = 'parent';

          // Auto-connect to previous child if exists
          const children = nds.filter(n => n.parentId === parent.id);
          if (children.length > 0) {
            const lastChild = children[children.length - 1];
            setEdges((eds) => addEdge({ 
              id: `e-${lastChild.id}-${newNode.id}`,
              source: lastChild.id, 
              target: newNode.id, 
              animated: true 
            }, eds));
          }
        }
        return nds.concat(newNode);
      });
      setSelectedNodeId(newNode.id); // Auto-select on drop
    },
    [screenToFlowPosition, setNodes, setEdges, findParentIterator]
  );

  // When a node stops being dragged, check if it landed inside/outside an Iterator
  const onNodeDragStop = useCallback((_event, draggedNode) => {
    if (draggedNode.type === 'IteratorNode') return; // Don't nest iterators

    setNodes((nds) => {
      // Get the dragged node's absolute position
      const dn = nds.find((n) => n.id === draggedNode.id);
      if (!dn) return nds;

      let absPos = { ...dn.position };
      // If it already has a parent, convert to absolute first
      if (dn.parentId) {
        const oldParent = nds.find((n) => n.id === dn.parentId);
        if (oldParent) {
          absPos = {
            x: dn.position.x + oldParent.position.x,
            y: dn.position.y + oldParent.position.y,
          };
        }
      }

      const newParent = findParentIterator(absPos, nds, dn.id);

      if (newParent) {
        // Dropped inside an Iterator → attach
        if (dn.parentId === newParent.id) return nds; // Already parented, no change
        return nds.map((n) => {
          if (n.id !== dn.id) return n;
          return {
            ...n,
            position: {
              x: absPos.x - newParent.position.x,
              y: absPos.y - newParent.position.y,
            },
            parentId: newParent.id,
            extent: 'parent',
          };
        });
      } else {
        // Dropped outside → detach from any parent
        if (!dn.parentId) return nds; // Already free, no change
        return nds.map((n) => {
          if (n.id !== dn.id) return n;
          const { parentId, extent, ...rest } = n;
          return { ...rest, position: absPos };
        });
      }
    });
  }, [setNodes, findParentIterator]);

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
          const updatedNode = { ...node };
          const cleanData = { ...newData };
          
          if (cleanData._parentNodeUpdate !== undefined) {
            const newParentId = cleanData._parentNodeUpdate;
            const oldParentId = node.parentId;

            if (newParentId !== oldParentId) {
              // Get absolute position of the node before change
              let absX = node.position.x;
              let absY = node.position.y;
              if (oldParentId) {
                const oldParent = nds.find(n => n.id === oldParentId);
                if (oldParent) {
                  absX += oldParent.position.x;
                  absY += oldParent.position.y;
                }
              }

              if (newParentId === '') {
                // To Absolute
                updatedNode.position = { x: absX, y: absY };
                delete updatedNode.parentId;
                delete updatedNode.extent;
              } else {
                // To Relative
                const newParent = nds.find(n => n.id === newParentId);
                if (newParent) {
                  updatedNode.position = {
                    x: absX - newParent.position.x,
                    y: absY - newParent.position.y
                  };
                  updatedNode.parentId = newParentId;
                  updatedNode.extent = 'parent';
                }
              }
            }
            delete cleanData._parentNodeUpdate;
          }
          
          updatedNode.data = cleanData;
          return updatedNode;
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
    <div className="flex h-full w-full relative">
      <Sidebar />
      <div className="flex-1 h-full relative flex flex-col" ref={reactFlowWrapper}>
        {/* Nút Switch Graph / JSON View */}
        <div className="absolute top-4 right-4 z-10 flex bg-white/80 backdrop-blur border border-slate-200 rounded-lg p-1 shadow-sm gap-1">
          <button
            onClick={() => {
              setViewMode('graph');
              setSelectedNodeId(null);
            }}
            className={`px-3 py-1.5 text-xs font-semibold rounded-md transition-all cursor-pointer ${
              viewMode === 'graph'
                ? 'bg-indigo-600 text-white shadow-sm'
                : 'text-slate-600 hover:text-slate-900'
            }`}
          >
            Graph View
          </button>
          <button
            onClick={() => {
              setViewMode('json');
              setJsonInput(JSON.stringify({ nodes, edges }, null, 2));
              setJsonError(null);
              setSelectedNodeId(null);
            }}
            className={`px-3 py-1.5 text-xs font-semibold rounded-md transition-all cursor-pointer ${
              viewMode === 'json'
                ? 'bg-indigo-600 text-white shadow-sm'
                : 'text-slate-600 hover:text-slate-900'
            }`}
          >
            JSON View
          </button>
        </div>

        {viewMode === 'graph' ? (
          <ReactFlow
            nodes={nodes}
            edges={edges}
            onNodesChange={onNodesChange}
            onEdgesChange={onEdgesChange}
            onConnect={onConnect}
            onDrop={onDrop}
            onDragOver={onDragOver}
            onNodeClick={onNodeClick}
            onNodeDragStop={onNodeDragStop}
            onPaneClick={onPaneClick}
            nodeTypes={nodeTypes}
            fitView
          >
            <Controls />
            <MiniMap />
            <Background variant="dots" gap={12} size={1} />
          </ReactFlow>
        ) : (
          <div className="h-full w-full p-6 flex flex-col bg-slate-50 pt-20">
            <div className="flex justify-between items-center mb-3">
              <span className="text-sm font-semibold text-slate-700">JSON Blueprint Editor</span>
              <div className="flex gap-2">
                <button
                  type="button"
                  onClick={() => {
                    navigator.clipboard.writeText(jsonInput);
                  }}
                  className="px-3 py-1.5 text-xs bg-slate-200 hover:bg-slate-300 text-slate-800 rounded font-medium transition cursor-pointer"
                >
                  Copy JSON
                </button>
                <button
                  type="button"
                  onClick={() => {
                    try {
                      const parsed = JSON.parse(jsonInput);
                      if (!parsed || !Array.isArray(parsed.nodes)) {
                        throw new Error("Invalid structure: 'nodes' array is required.");
                      }
                      setNodes(parsed.nodes);
                      if (Array.isArray(parsed.edges)) {
                        setEdges(parsed.edges);
                      }
                      setJsonError(null);
                      setViewMode('graph');
                    } catch (e) {
                      setJsonError("⚠️ Invalid JSON syntax: " + e.message);
                    }
                  }}
                  className="px-3 py-1.5 text-xs bg-indigo-600 hover:bg-indigo-700 text-white rounded font-medium transition cursor-pointer"
                >
                  Apply & Return
                </button>
              </div>
            </div>
            {jsonError && (
              <div className="mb-3 p-3 bg-rose-50 border border-rose-200 text-rose-700 rounded text-xs font-semibold">
                {jsonError}
              </div>
            )}
            <textarea
              value={jsonInput}
              onChange={(e) => setJsonInput(e.target.value)}
              className="flex-1 w-full font-mono text-sm p-4 bg-slate-900 text-slate-100 rounded-lg border border-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-inner resize-none"
              placeholder="Paste your JSON Workflow Blueprint here..."
            />
          </div>
        )}
      </div>
      {selectedNode && viewMode === 'graph' && (
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
