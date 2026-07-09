import React, { useState, useEffect } from 'react';
import { Settings, X, Trash2, Code, Eye, FileJson } from 'lucide-react';
import TablePicker from './TablePicker';
import { SkaFXInput, SkaFXTextarea } from './SkaFXAutocomplete';
import DynamicNodeSettings from './DynamicNodeSettings';

const { __ } = window.wp?.i18n || { __: (text) => text };

const defaultMockPayload = JSON.stringify({
  payload: {
    user: {
      name: "Alex Johnson",
      email: "alex@example.com",
      membership: "Gold"
    },
    promo_code: "WELCOME2026",
    dynamic_html: "<div class=\"p-4 bg-amber-50 rounded border border-amber-200\">\n  <h3 class=\"text-amber-800 font-semibold\">Welcome VIP!</h3>\n  <p class=\"text-sm\">Hello {{ payload.user.name }} ({{ payload.user.email }}), your {{ payload.user.membership }} status is active.</p>\n  <p class=\"text-xs text-slate-500 mt-2\">Use coupon: <strong>{{ payload.promo_code }}</strong></p>\n</div>"
  }
}, null, 2);

export default function SettingsPanel({ selectedNode, nodes, onUpdateNode, onDeleteNode, onClose }) {
  if (!selectedNode) return null;

  const triggerNode = nodes.find(n => n.type === 'TriggerNode');

  // Khởi tạo state cho RenderTemplateNode Live Testing
  const [mockPayload, setMockPayload] = useState(() => {
    return triggerNode?.data?.mockPayload || defaultMockPayload;
  });

  // Keep mockPayload in sync when switching nodes or if the trigger node updates
  useEffect(() => {
    setMockPayload(triggerNode?.data?.mockPayload || defaultMockPayload);
  }, [selectedNode.id, triggerNode?.data?.mockPayload]);

  const handleMockPayloadChange = (newVal) => {
    setMockPayload(newVal);
    if (triggerNode) {
      onUpdateNode(triggerNode.id, {
        ...triggerNode.data,
        mockPayload: newVal
      });
    }
  };
  
  const [activeTab, setActiveTab] = useState('visual'); // 'visual' | 'raw'

  const handleChange = (key, value) => {
    onUpdateNode(selectedNode.id, {
      ...selectedNode.data,
      [key]: value
    });
  };

  const isWideNode = selectedNode.type === 'RenderTemplateNode' || selectedNode.type === 'ApiNode' || selectedNode.type === 'ClientResponseNode';

  // Hàm nội suy template string phía client-side (Two-Pass Interpolation)
  const evaluateTemplateClient = (template, payloadObj) => {
    if (!template || typeof template !== 'string') return '';
    if (!template.includes('{{')) return template;

    const resolvePath = (path, obj) => {
      let cleanPath = path.trim();
      if (cleanPath.startsWith('payload.')) {
        cleanPath = cleanPath.substring(8);
      }
      
      const keys = cleanPath.split('.');
      let current = obj?.payload || obj;
      
      for (const key of keys) {
        if (current && typeof current === 'object' && key in current) {
          current = current[key];
        } else {
          // Fallback to root search
          let rootCurrent = obj;
          let found = true;
          for (const k of keys) {
            if (rootCurrent && typeof rootCurrent === 'object' && k in rootCurrent) {
              rootCurrent = rootCurrent[k];
            } else {
              found = false;
              break;
            }
          }
          if (found) return rootCurrent;
          return '';
        }
      }
      
      if (typeof current === 'object') {
        return JSON.stringify(current);
      }
      return current !== undefined && current !== null ? String(current) : '';
    };

    try {
      // Pass 1: Parse outer template
      const pass1 = template.replace(/\{\{\s*(.+?)\s*\}\}/g, (match, expression) => {
        return resolvePath(expression, payloadObj);
      });

      // Pass 2: Parse inner template (if any)
      if (pass1.includes('{{')) {
        return pass1.replace(/\{\{\s*(.+?)\s*\}\}/g, (match, expression) => {
          return resolvePath(expression, payloadObj);
        });
      }
      return pass1;
    } catch (e) {
      return `Error: ${e.message}`;
    }
  };

  return (
    <aside className={`${isWideNode ? 'w-[400px]' : 'w-80'} flex-shrink-0 bg-white border-l border-slate-200 flex flex-col h-full shadow-sm z-10 transition-all duration-300`}>
      <div className="p-4 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
        <h3 className="font-semibold text-slate-800 flex items-center gap-2">
          <Settings size={18} className="text-blue-500" />
          {__( 'Settings', 'ska-logic-engine' )}
        </h3>
        <button 
          onClick={onClose}
          className="p-1 hover:bg-slate-200 rounded text-slate-500 transition-colors"
        >
          <X size={16} />
        </button>
      </div>
      
      <div className="p-4 flex-1 overflow-y-auto space-y-4">
        <div>
          <label className="block text-sm font-medium text-slate-700 mb-1">{__( 'Node ID', 'ska-logic-engine' )}</label>
          <input 
            type="text" 
            value={selectedNode.id} 
            disabled
            className="w-full text-sm p-2 border border-slate-200 rounded bg-slate-50 text-slate-500 font-mono cursor-not-allowed"
          />
        </div>
        
        {selectedNode.type !== 'TriggerNode' && (
          <div>
            <label className="block text-sm font-medium text-slate-700 mb-1">{__( 'Parent Node (Iterator)', 'ska-logic-engine' )}</label>
            <input 
              type="text" 
              value={selectedNode.parentId || selectedNode.parentNode || ''} 
              onChange={(e) => {
                onUpdateNode(selectedNode.id, {
                  ...selectedNode.data,
                  _parentNodeUpdate: e.target.value
                });
              }}
              className="w-full text-sm p-2 border border-slate-300 rounded focus:ring-2 focus:ring-fuchsia-500 focus:border-fuchsia-500 outline-none font-mono"
              placeholder={__( 'Auto-assigned when dragged into Iterator', 'ska-logic-engine' )}
            />
            <p className="text-[10px] text-slate-500 mt-1">{__( 'Auto-assigned when node is dragged into Iterator. Clear to detach.', 'ska-logic-engine' )}</p>
          </div>
        )}
        
        <div>
          <label className="block text-sm font-medium text-slate-700 mb-1">{__( 'Label', 'ska-logic-engine' )}</label>
          <input 
            type="text" 
            value={selectedNode.data.label || ''} 
            onChange={(e) => handleChange('label', e.target.value)}
            className="w-full text-sm p-2 border border-slate-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
            placeholder={__( 'Enter node label', 'ska-logic-engine' )}
          />
        </div>

        <div>
          <label className="block text-sm font-medium text-slate-700 mb-1">{__( 'Description', 'ska-logic-engine' )}</label>
          <textarea 
            value={selectedNode.data.description || ''} 
            onChange={(e) => handleChange('description', e.target.value)}
            className="w-full text-sm p-2 border border-slate-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none resize-none h-24"
            placeholder={__( 'Enter description', 'ska-logic-engine' )}
          />
        </div>

        {selectedNode.type === 'TriggerNode' && (
          <div className="space-y-3">
            <div>
              <label className="block text-sm font-medium text-slate-700 mb-1">Trigger Type</label>
              <select 
                value={selectedNode.data.triggerType || 'form_submit'} 
                onChange={(e) => handleChange('triggerType', e.target.value)}
                className="w-full text-sm p-2 border border-slate-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none bg-white"
              >
                <option value="form_submit">Logic Trigger (AJAX / Form)</option>
                <option value="webhook">Webhook URL</option>
                <option value="cron">Schedule (Cron)</option>
              </select>
            </div>
            
            {(selectedNode.data.triggerType === 'form_submit' || !selectedNode.data.triggerType) && (
              <div>
                <label className="block text-sm font-medium text-slate-700 mb-1">Workflow ID / Action Hook</label>
                <input 
                  type="text" 
                  value={selectedNode.data.workflowId || ''} 
                  onChange={(e) => handleChange('workflowId', e.target.value)}
                  className="w-full text-sm p-2 border border-slate-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                  placeholder="e.g. user_registration"
                />
                <p className="text-xs text-slate-500 mt-1">This ID must match your Trigger Button or Form's action ID.</p>
              </div>
            )}

            {selectedNode.data.triggerType === 'webhook' && (
              <div>
                <label className="block text-sm font-medium text-slate-700 mb-1">Webhook URL (Auto-generated)</label>
                <input 
                  type="text" 
                  value={`/wp-json/ska-logic/v1/webhook/${selectedNode.data.workflowId || 'default'}`} 
                  readOnly
                  className="w-full text-sm p-2 border border-slate-200 rounded bg-slate-50 text-slate-500 outline-none font-mono"
                />
              </div>
            )}

            {selectedNode.data.triggerType === 'cron' && (
              <div>
                <label className="block text-sm font-medium text-slate-700 mb-1">Cron Expression</label>
                <input 
                  type="text" 
                  value={selectedNode.data.cronExpression || '* * * * *'} 
                  onChange={(e) => handleChange('cronExpression', e.target.value)}
                  className="w-full text-sm p-2 border border-slate-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none font-mono"
                  placeholder="* * * * *"
                />
              </div>
            )}
          </div>
        )}
        
        {selectedNode.type === 'SetDataNode' && (
          <div className="space-y-3">
            <label className="block text-sm font-medium text-slate-700 mb-1">Variable Assignments</label>
            {(selectedNode.data.assignments || []).map((assignment, index) => (
              <div key={index} className="flex gap-2 items-start mb-2">
                <div className="flex-1 space-y-2">
                  <input 
                    type="text" 
                    value={assignment.key || ''} 
                    onChange={(e) => {
                      const newAssignments = [...(selectedNode.data.assignments || [])];
                      newAssignments[index] = { ...newAssignments[index], key: e.target.value };
                      handleChange('assignments', newAssignments);
                    }}
                    className="w-full text-xs p-1.5 border border-slate-300 rounded outline-none"
                    placeholder="Key (e.g. status)"
                  />
                  <SkaFXInput 
                    nodes={nodes}
                    selectedNode={selectedNode}
                    mockPayload={mockPayload}
                    type="text" 
                    value={assignment.value || ''} 
                    onChange={(val) => {
                      const newAssignments = [...(selectedNode.data.assignments || [])];
                      newAssignments[index] = { ...newAssignments[index], value: val };
                      handleChange('assignments', newAssignments);
                    }}
                    className="w-full text-xs p-1.5 border border-slate-300 rounded outline-none font-mono text-blue-600 bg-slate-50"
                    placeholder="Value (e.g. [a] + [b])"
                  />
                </div>
                <button 
                  onClick={() => {
                    const newAssignments = [...(selectedNode.data.assignments || [])];
                    newAssignments.splice(index, 1);
                    handleChange('assignments', newAssignments);
                  }}
                  className="p-1 text-slate-400 hover:text-red-500 rounded bg-slate-100 mt-1"
                >
                  <X size={14} />
                </button>
              </div>
            ))}
            <p className="text-[10px] text-slate-400 italic">{__( 'Hint: Use [...] for calculations or {{ ... }} to concatenate strings.', 'ska-logic-engine' )}</p>
            <button 
              onClick={() => {
                const newAssignments = [...(selectedNode.data.assignments || []), { key: '', value: '' }];
                handleChange('assignments', newAssignments);
              }}
              className="w-full py-1.5 text-xs font-medium text-indigo-600 border border-indigo-200 rounded bg-indigo-50 hover:bg-indigo-100 transition-colors"
            >
              {__( '+ Add Variable', 'ska-logic-engine' )}
            </button>
          </div>
        )}
        
        {selectedNode.type === 'ConditionNode' && (
          <div>
            <label className="block text-sm font-medium text-slate-700 mb-1">{__( 'If condition', 'ska-logic-engine' )}</label>
            <SkaFXTextarea 
              nodes={nodes}
              selectedNode={selectedNode}
              mockPayload={mockPayload}
              value={selectedNode.data.expression || ''} 
              onChange={(val) => handleChange('expression', val)}
              className="w-full text-sm p-2 border border-slate-300 rounded focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none font-mono text-amber-700 bg-amber-50/30 resize-none h-20"
              placeholder="[trigger.age] > 18 AND [status] == 'active'"
            />
            <p className="text-xs text-slate-500 mt-1">{__( 'Use SkaFX syntax. Type [ for variables, { or {{ for templates, and letters for functions.', 'ska-logic-engine' )}</p>
          </div>
        )}

        {selectedNode.type === 'SwitchNode' && (
          <div className="space-y-4">
            <div className="flex items-center justify-between border-b pb-2">
              <label className="block text-sm font-medium text-slate-700">Routes</label>
            </div>
            {(selectedNode.data.routes || []).map((route, index) => (
              <div key={route.id || index} className="p-3 border border-purple-200 bg-purple-50 rounded space-y-2 relative">
                <button 
                  onClick={() => {
                    const newRoutes = [...(selectedNode.data.routes || [])];
                    newRoutes.splice(index, 1);
                    handleChange('routes', newRoutes);
                  }}
                  className="absolute top-2 right-2 p-1 text-purple-400 hover:text-red-500 rounded bg-white shadow-sm"
                >
                  <X size={14} />
                </button>
                <div>
                  <label className="block text-xs font-medium text-purple-800 mb-1">Route Name</label>
                  <input 
                    type="text" 
                    value={route.name || ''} 
                    onChange={(e) => {
                      const newRoutes = [...(selectedNode.data.routes || [])];
                      newRoutes[index] = { ...newRoutes[index], name: e.target.value };
                      handleChange('routes', newRoutes);
                    }}
                    className="w-full text-xs p-1.5 border border-purple-200 rounded outline-none"
                    placeholder="E.g., If Status == VIP"
                  />
                </div>
                <div>
                  <label className="block text-xs font-medium text-purple-800 mb-1">SkaFX Expression</label>
                  <SkaFXInput 
                    nodes={nodes}
                    selectedNode={selectedNode}
                    mockPayload={mockPayload}
                    type="text" 
                    value={route.expression || ''} 
                    onChange={(val) => {
                      const newRoutes = [...(selectedNode.data.routes || [])];
                      newRoutes[index] = { ...newRoutes[index], expression: val };
                      handleChange('routes', newRoutes);
                    }}
                    className="w-full text-xs p-1.5 border border-purple-200 rounded outline-none font-mono text-purple-700"
                    placeholder="[status] == 'vip'"
                  />
                </div>
                <div>
                  <label className="block text-xs font-medium text-purple-800 mb-1">Handle ID (auto)</label>
                  <input 
                    type="text" 
                    value={route.id || ''} 
                    readOnly
                    className="w-full text-[10px] p-1 border border-purple-200/50 bg-purple-100/50 text-purple-600 rounded outline-none font-mono cursor-not-allowed"
                  />
                </div>
              </div>
            ))}
            <button 
              onClick={() => {
                const newRoutes = [...(selectedNode.data.routes || [])];
                const newId = `route_${Date.now().toString(36)}`;
                newRoutes.push({ id: newId, name: `Route ${newRoutes.length + 1}`, expression: '' });
                handleChange('routes', newRoutes);
              }}
              className="w-full py-1.5 text-xs font-medium text-purple-600 border border-purple-200 rounded bg-purple-50 hover:bg-purple-100 transition-colors shadow-sm"
            >
              + Add Route
            </button>
            <p className="text-xs text-slate-500 mt-2">Note: 'Default' port is always auto-generated on Canvas.</p>
          </div>
        )}

        {selectedNode.type === 'DBActionNode' && (
          <div className="space-y-4">
            <div>
              <label className="block text-sm font-medium text-slate-700 mb-1">Action</label>
              <select 
                value={selectedNode.data.actionType || 'insert'} 
                onChange={(e) => handleChange('actionType', e.target.value)}
                className="w-full text-sm p-2 border border-slate-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none bg-white"
              >
                <option value="insert">Insert</option>
                <option value="update">Update</option>
                <option value="delete">Delete</option>
              </select>
            </div>
            
            <div>
              <label className="block text-sm font-medium text-slate-700 mb-1">Table</label>
              <TablePicker 
                value={selectedNode.data.table || ''}
                onChange={(val) => handleChange('table', val)}
              />
            </div>

            {(selectedNode.data.actionType === 'update' || selectedNode.data.actionType === 'delete') && (
              <div>
                <label className="block text-sm font-medium text-slate-700 mb-1">Record ID (SkaFX var)</label>
                <SkaFXInput 
                  nodes={nodes}
                  selectedNode={selectedNode}
                  mockPayload={mockPayload}
                  type="text" 
                  value={selectedNode.data.recordId || ''} 
                  onChange={(val) => handleChange('recordId', val)}
                  className="w-full text-sm p-2 border border-rose-300 rounded focus:ring-2 focus:ring-rose-500 focus:border-rose-500 outline-none font-mono"
                  placeholder="[payload.id]"
                />
                <p className="text-xs text-slate-500 mt-1">Pass record ID variable to search.</p>
              </div>
            )}

            {selectedNode.data.table && selectedNode.data.actionType !== 'delete' && (
              <div className="mt-4 border-t border-slate-200 pt-4">
                <label className="block text-sm font-medium text-slate-700 mb-2">Auto-map Data</label>
                <div className="space-y-2">
                  {(() => {
                    const tableObj = (window.SKA_DAG_CONTEXT?.AVAILABLE_TABLES || []).find(t => t.id === selectedNode.data.table);
                    if (!tableObj || !tableObj.columns) return <p className="text-xs text-slate-500">No column schema found.</p>;
                    
                    const mappings = selectedNode.data.mappings || {};
                    const dynamicModes = selectedNode.data.dynamicModes || {};

                    return tableObj.columns.map(col => {
                      const isSelectType = col.type === 'select' || col.type === 'multi_select';
                      // If it's a select type, check if dynamic mode is enabled (default is false)
                      const isDynamic = isSelectType ? !!dynamicModes[col.id] : true; 
                      
                      // Safely parse options if they exist
                      let options = [];
                      if (isSelectType && col.options) {
                        try {
                           options = typeof col.options === 'string' ? JSON.parse(col.options) : col.options;
                           // ensure it's an array
                           if (!Array.isArray(options)) {
                              // maybe it's object like { "vip": "Khách VIP" }
                              if (typeof options === 'object' && options !== null) {
                                 options = Object.entries(options).map(([val, label]) => ({value: val, label: label}));
                              } else {
                                 options = [];
                              }
                           }
                        } catch(e) {
                           // If JSON parse fails, it might just be a comma-separated string
                           if (typeof col.options === 'string') {
                               options = col.options.split(',').map(s => s.trim()).filter(Boolean);
                           } else {
                               options = [];
                           }
                        }
                      }

                      return (
                        <div key={col.id} className="flex flex-col gap-1 mb-2">
                          <div className="flex items-center justify-between">
                            <span className="text-xs font-semibold text-slate-600 break-words">{col.name} ({col.id})</span>
                            {isSelectType && (
                               <button 
                                  onClick={() => {
                                      handleChange('dynamicModes', {
                                          ...dynamicModes,
                                          [col.id]: !isDynamic
                                      });
                                  }}
                                  title={isDynamic ? "Switch to Dropdown Selection" : "Switch to Dynamic SkaFX Input"}
                                  className={`p-1 rounded text-xs transition-colors ${isDynamic ? 'bg-blue-100 text-blue-600' : 'bg-slate-100 text-slate-400 hover:bg-slate-200'}`}
                               >
                                  <Code size={14} />
                               </button>
                            )}
                          </div>
                          
                          {(!isDynamic && isSelectType) ? (
                            <select
                              value={mappings[col.id] || ''}
                              onChange={(e) => {
                                handleChange('mappings', {
                                  ...mappings,
                                  [col.id]: e.target.value
                                });
                              }}
                              className="w-full text-sm p-1.5 border border-slate-200 rounded focus:border-blue-400 focus:ring-1 focus:ring-blue-400 outline-none bg-white"
                            >
                               <option value="">-- Select a value --</option>
                               {options.map((opt, idx) => (
                                  <option key={idx} value={opt.value || opt}>{opt.label || opt.value || opt}</option>
                               ))}
                            </select>
                          ) : (
                            <SkaFXInput
                              nodes={nodes}
                              selectedNode={selectedNode}
                              mockPayload={mockPayload}
                              type="text"
                              value={mappings[col.id] || ''}
                              onChange={(val) => {
                                handleChange('mappings', {
                                  ...mappings,
                                  [col.id]: val
                                });
                              }}
                              className="w-full text-sm p-1.5 border border-slate-200 rounded focus:border-blue-400 focus:ring-1 focus:ring-blue-400 outline-none font-mono text-xs"
                              placeholder={`[form.${col.id}]`}
                            />
                          )}
                        </div>
                      );
                    });
                  })()}
                </div>
              </div>
            )}
          </div>
        )}

        {selectedNode.type === 'DBQueryNode' && (
          <div className="space-y-4">
            <div>
              <label className="block text-sm font-medium text-slate-700 mb-1">Table</label>
              <TablePicker 
                value={selectedNode.data.table || ''}
                onChange={(val) => handleChange('table', val)}
              />
            </div>
            
            <div>
              <label className="block text-sm font-medium text-slate-700 mb-1">Return Type</label>
              <select 
                value={selectedNode.data.returnType || 'multiple'} 
                onChange={(e) => handleChange('returnType', e.target.value)}
                className="w-full text-sm p-2 border border-slate-300 rounded focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 outline-none bg-white"
              >
                <option value="multiple">Multiple rows (Array)</option>
                <option value="single">Single row (Object)</option>
              </select>
            </div>

            <div>
              <label className="block text-sm font-medium text-slate-700 mb-1">Where Conditions</label>
              {(selectedNode.data.conditions || []).map((cond, index) => (
                <div key={index} className="flex gap-2 items-start mb-2">
                  <div className="flex-1 space-y-2">
                    <div className="flex gap-2">
                      <input 
                        type="text" 
                        value={cond.column || ''} 
                        onChange={(e) => {
                          const newConds = [...(selectedNode.data.conditions || [])];
                          newConds[index] = { ...newConds[index], column: e.target.value };
                          handleChange('conditions', newConds);
                        }}
                        className="w-1/2 text-xs p-1.5 border border-slate-300 rounded outline-none"
                        placeholder="Column"
                      />
                      <select
                        value={cond.operator || '='}
                        onChange={(e) => {
                          const newConds = [...(selectedNode.data.conditions || [])];
                          newConds[index] = { ...newConds[index], operator: e.target.value };
                          handleChange('conditions', newConds);
                        }}
                        className="w-1/2 text-xs p-1.5 border border-slate-300 rounded outline-none bg-white"
                      >
                        <option value="=">=</option>
                        <option value="!=">!=</option>
                        <option value=">">&gt;</option>
                        <option value="<">&lt;</option>
                        <option value=">=">&gt;=</option>
                        <option value="<=">&lt;=</option>
                        <option value="LIKE">LIKE</option>
                        <option value="IN">IN</option>
                      </select>
                    </div>
                    <SkaFXInput 
                      nodes={nodes}
                      selectedNode={selectedNode}
                      mockPayload={mockPayload}
                      type="text" 
                      value={cond.value || ''} 
                      onChange={(val) => {
                        const newConds = [...(selectedNode.data.conditions || [])];
                        newConds[index] = { ...newConds[index], value: val };
                        handleChange('conditions', newConds);
                      }}
                      className="w-full text-xs p-1.5 border border-slate-300 rounded outline-none font-mono text-cyan-600 bg-slate-50"
                      placeholder="Value or [payload.id]"
                    />
                  </div>
                  <button 
                    onClick={() => {
                      const newConds = [...(selectedNode.data.conditions || [])];
                      newConds.splice(index, 1);
                      handleChange('conditions', newConds);
                    }}
                    className="p-1 text-slate-400 hover:text-red-500 rounded bg-slate-100 mt-1"
                  >
                    <X size={14} />
                  </button>
                </div>
              ))}
              <button 
                onClick={() => {
                  const newConds = [...(selectedNode.data.conditions || []), { column: '', operator: '=', value: '' }];
                  handleChange('conditions', newConds);
                }}
                className="w-full py-1.5 text-xs font-medium text-cyan-600 border border-cyan-200 rounded bg-cyan-50 hover:bg-cyan-100 transition-colors mt-1"
              >
                + Add Condition
              </button>
            </div>

            <div className="flex gap-2">
              <div className="flex-1">
                <label className="block text-sm font-medium text-slate-700 mb-1">Order By</label>
                <input 
                  type="text" 
                  value={selectedNode.data.orderBy || ''} 
                  onChange={(e) => handleChange('orderBy', e.target.value)}
                  className="w-full text-sm p-2 border border-slate-300 rounded focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 outline-none"
                  placeholder="e.g. created_at"
                />
              </div>
              <div className="w-24">
                <label className="block text-sm font-medium text-slate-700 mb-1">Direction</label>
                <select 
                  value={selectedNode.data.orderDir || 'DESC'} 
                  onChange={(e) => handleChange('orderDir', e.target.value)}
                  className="w-full text-sm p-2 border border-slate-300 rounded focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 outline-none bg-white"
                >
                  <option value="DESC">DESC</option>
                  <option value="ASC">ASC</option>
                </select>
              </div>
            </div>

            {selectedNode.data.returnType !== 'single' && (
              <div>
                <label className="block text-sm font-medium text-slate-700 mb-1">Limit</label>
                <input 
                  type="text" 
                  value={selectedNode.data.limit || ''} 
                  onChange={(e) => handleChange('limit', e.target.value)}
                  className="w-full text-sm p-2 border border-slate-300 rounded focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 outline-none"
                  placeholder="Number or [var]"
                />
              </div>
            )}

            <div className="border-t border-slate-200 pt-4 mt-4">
              <label className="block text-sm font-medium text-slate-700 mb-1">Result Variable</label>
              <input 
                type="text" 
                value={selectedNode.data.resultVar || ''} 
                onChange={(e) => handleChange('resultVar', e.target.value)}
                className="w-full text-sm p-2 border border-slate-300 rounded focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 outline-none font-mono"
                placeholder="payload.query_results"
              />
              <p className="text-xs text-slate-500 mt-1">e.g. <code>payload.users</code>. Data will be saved in this variable for subsequent nodes.</p>
            </div>
          </div>
        )}

        {selectedNode.type === 'ApiNode' && (
          <div className="space-y-4">
            <div>
              <label className="block text-sm font-medium text-slate-700 mb-1">Method</label>
              <select 
                value={selectedNode.data.method || 'GET'} 
                onChange={(e) => handleChange('method', e.target.value)}
                className="w-full text-sm p-2 border border-slate-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none bg-white"
              >
                <option value="GET">GET</option>
                <option value="POST">POST</option>
                <option value="PUT">PUT</option>
                <option value="PATCH">PATCH</option>
                <option value="DELETE">DELETE</option>
              </select>
            </div>

            <div>
              <label className="block text-sm font-medium text-slate-700 mb-1">Endpoint URL</label>
              <SkaFXInput 
                nodes={nodes}
                selectedNode={selectedNode}
                mockPayload={mockPayload}
                type="text" 
                value={selectedNode.data.url || ''} 
                onChange={(val) => handleChange('url', val)}
                className="w-full text-sm p-2 border border-slate-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none font-mono text-xs"
                placeholder="https://api.example.com/v1/users/{{payload.id}}"
              />
              <p className="text-[10px] text-slate-500 mt-1">Supports SkaFX using syntax &#123;&#123; payload.abc &#125;&#125;</p>
            </div>

            <div>
              <label className="block text-sm font-medium text-slate-700 mb-1">Headers</label>
              {(selectedNode.data.headers || []).map((header, index) => (
                <div key={index} className="flex gap-2 items-start mb-2">
                  <div className="flex-1 space-y-2">
                    <input 
                      type="text" 
                      value={header.key || ''} 
                      onChange={(e) => {
                        const newHeaders = [...(selectedNode.data.headers || [])];
                        newHeaders[index] = { ...newHeaders[index], key: e.target.value };
                        handleChange('headers', newHeaders);
                      }}
                      className="w-full text-xs p-1.5 border border-slate-300 rounded outline-none"
                      placeholder="Header-Name"
                    />
                    <SkaFXInput 
                      nodes={nodes}
                      selectedNode={selectedNode}
                      mockPayload={mockPayload}
                      type="text" 
                      value={header.value || ''} 
                      onChange={(val) => {
                        const newHeaders = [...(selectedNode.data.headers || [])];
                        newHeaders[index] = { ...newHeaders[index], value: val };
                        handleChange('headers', newHeaders);
                      }}
                      className="w-full text-xs p-1.5 border border-slate-300 rounded outline-none font-mono text-blue-600 bg-slate-50"
                      placeholder="Value or {{ expr }}"
                    />
                  </div>
                  <button 
                    onClick={() => {
                      const newHeaders = [...(selectedNode.data.headers || [])];
                      newHeaders.splice(index, 1);
                      handleChange('headers', newHeaders);
                    }}
                    className="p-1 text-slate-400 hover:text-red-500 rounded bg-slate-100 mt-1"
                  >
                    <X size={14} />
                  </button>
                </div>
              ))}
              <button 
                onClick={() => {
                  const newHeaders = [...(selectedNode.data.headers || []), { key: '', value: '' }];
                  handleChange('headers', newHeaders);
                }}
                className="w-full py-1.5 text-xs font-medium text-emerald-600 border border-emerald-200 rounded bg-emerald-50 hover:bg-emerald-100 transition-colors mt-1"
              >
                + Add Header
              </button>
            </div>

            {(selectedNode.data.method !== 'GET' && selectedNode.data.method !== 'DELETE') && (
              <div>
                <label className="block text-sm font-medium text-slate-700 mb-1">Body (JSON/String)</label>
                <SkaFXTextarea 
                  nodes={nodes}
                  selectedNode={selectedNode}
                  mockPayload={mockPayload}
                  value={selectedNode.data.body || ''} 
                  onChange={(val) => handleChange('body', val)}
                  className="w-full text-xs p-2 border border-slate-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none font-mono bg-slate-50 min-h-[100px]"
                  placeholder='{ "key": "{{payload.value}}" }'
                />
              </div>
            )}
          </div>
        )}
        
        {selectedNode.type === 'ClientResponseNode' && (
          <div className="space-y-4">
            <div>
              <label className="block text-sm font-medium text-slate-700 mb-1">Response Type</label>
              <select 
                value={selectedNode.data.response_type || 'toast'} 
                onChange={(e) => handleChange('response_type', e.target.value)}
                className="w-full text-sm p-2 border border-slate-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none bg-white"
              >
                <option value="toast">Show Toast Message</option>
                <option value="remove_row">Remove Row</option>
                <option value="redirect">Redirect / Navigate</option>
                <option value="open_modal">Open Modal Popup</option>
                <option value="fire_event">Fire Custom Event</option>
              </select>
            </div>

            {selectedNode.data.response_type === 'redirect' && (
              <div>
                <label className="block text-sm font-medium text-slate-700 mb-1">Redirect URL</label>
                <SkaFXInput 
                  nodes={nodes}
                  selectedNode={selectedNode}
                  mockPayload={mockPayload}
                  type="text" 
                  value={selectedNode.data.url || ''} 
                  onChange={(val) => handleChange('url', val)}
                  className="w-full text-sm p-2 border border-slate-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none font-mono"
                  placeholder="https://example.com/success"
                />
                 <p className="text-[10px] text-slate-500 mt-1">Supports SkaFX (e.g. [payload.next_url])</p>
              </div>
            )}

            {selectedNode.data.response_type === 'open_modal' && (
              <div className="space-y-3">
                <div>
                  <label className="block text-sm font-medium text-slate-700 mb-1">Modal Content (HTML)</label>
                  <SkaFXTextarea 
                    nodes={nodes}
                    selectedNode={selectedNode}
                    mockPayload={mockPayload}
                    value={selectedNode.data.modal_content || ''} 
                    onChange={(val) => handleChange('modal_content', val)}
                    className="w-full text-sm p-2 border border-slate-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none font-mono h-24"
                    placeholder="{{payload.rendered_template}}"
                  />
                  <p className="text-[10px] text-slate-500 mt-1">Enter HTML content or SkaFX variable (e.g. &#123;&#123;payload.rendered_template&#125;&#125;)</p>
                </div>
                <div>
                  <label className="block text-sm font-medium text-slate-700 mb-1">Modal ID (Optional)</label>
                  <input 
                    type="text" 
                    value={selectedNode.data.modal_id || ''} 
                    onChange={(e) => handleChange('modal_id', e.target.value)}
                    className="w-full text-sm p-2 border border-slate-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none font-mono"
                    placeholder="login_modal"
                  />
                  <p className="text-[10px] text-slate-500 mt-1">Modal Element ID (e.g. my_modal) to open a static modal if applicable.</p>
                </div>
              </div>
            )}

            {selectedNode.data.response_type === 'fire_event' && (
              <div>
                <label className="block text-sm font-medium text-slate-700 mb-1">Event Name</label>
                <input 
                  type="text" 
                  value={selectedNode.data.event_name || ''} 
                  onChange={(e) => handleChange('event_name', e.target.value)}
                  className="w-full text-sm p-2 border border-slate-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none font-mono"
                  placeholder="custom-success-event"
                />
                 <p className="text-[10px] text-slate-500 mt-1">Will trigger: window.dispatchEvent(...) with detail containing payload.</p>
              </div>
            )}

            {(!selectedNode.data.response_type || selectedNode.data.response_type === 'toast' || selectedNode.data.response_type === 'remove_row') && (
              <div className="space-y-4">
                <div>
                  <label className="block text-sm font-medium text-slate-700 mb-1">Toast Message</label>
                  <SkaFXTextarea 
                    nodes={nodes}
                    selectedNode={selectedNode}
                    mockPayload={mockPayload}
                    value={selectedNode.data.message || ''} 
                    onChange={(val) => handleChange('message', val)}
                    className="w-full text-sm p-2 border border-slate-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                    placeholder="Action successful!"
                  />
                  <p className="text-[10px] text-slate-500 mt-1">Supports SkaFX (e.g. Thanks [payload.name])</p>
                </div>
                <div>
                  <label className="block text-sm font-medium text-slate-700 mb-1">Toast Type</label>
                  <select 
                    value={selectedNode.data.toast_type || 'success'} 
                    onChange={(e) => handleChange('toast_type', e.target.value)}
                    className="w-full text-sm p-2 border border-slate-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none bg-white"
                  >
                    <option value="success">Success</option>
                    <option value="error">Error</option>
                  </select>
                </div>
              </div>
            )}
          </div>
        )}
        
        {selectedNode.type === 'RenderTemplateNode' && (() => {
          const currentTemplateHtml = selectedNode.data.template_html || selectedNode.data.raw_template || selectedNode.data.organism_id || '';
          
          let parsedPayload = {};
          let parseError = null;
          try {
            parsedPayload = JSON.parse(mockPayload);
          } catch (e) {
            parseError = e.message;
          }

          const renderedResult = evaluateTemplateClient(currentTemplateHtml, parsedPayload);

          return (
            <div className="space-y-4">
              <div>
                <label className="block text-sm font-medium text-slate-700 mb-1">Template HTML / Variable</label>
                <SkaFXTextarea 
                  nodes={nodes}
                  selectedNode={selectedNode}
                  mockPayload={mockPayload}
                  value={currentTemplateHtml} 
                  onChange={(val) => handleChange('template_html', val)}
                  className="w-full text-xs p-2 border border-slate-300 rounded focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none font-mono min-h-[120px]"
                  placeholder="<h1>Hello {{ payload.user.name }}</h1> or {{ payload.db_result.html_content }}"
                />
                <p className="text-[10px] text-slate-500 mt-1">
                  Enter raw HTML or variable containing dynamic HTML (Two-Pass Interpolation).
                </p>
              </div>
              
              <div>
                <label className="block text-sm font-medium text-slate-700 mb-1">Result Variable</label>
                <input 
                  type="text" 
                  value={selectedNode.data.result_var || 'payload.rendered_template'} 
                  onChange={(e) => handleChange('result_var', e.target.value)}
                  className="w-full text-sm p-2 border border-slate-300 rounded focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none font-mono"
                  placeholder="payload.rendered_template"
                />
                <p className="text-xs text-slate-500 mt-1">Variable that will contain the interpolated HTML.</p>
              </div>

              {/* Phân hệ Live Testing & Preview Glassmorphism */}
              <div className="backdrop-blur-sm bg-slate-50/50 border border-slate-200/80 rounded-xl p-3.5 mt-5 space-y-3 shadow-inner">
                <div className="flex justify-between items-center pb-1.5 border-b border-slate-200/60">
                  <span className="text-xs font-bold text-slate-700 flex items-center gap-1.5">
                    <Eye size={14} className="text-sky-500" />
                    Live Testing & Preview
                  </span>
                  <span className="text-[10px] text-slate-400 font-mono">Client Sandbox</span>
                </div>

                <div className="space-y-1">
                  <label className="block text-[10px] font-bold text-slate-500 uppercase tracking-wider flex items-center gap-1">
                    <FileJson size={11} />
                    Mock Payload (JSON)
                  </label>
                  <textarea
                    value={mockPayload}
                    onChange={(e) => handleMockPayloadChange(e.target.value)}
                    className="w-full text-[10px] p-2 bg-slate-900 text-slate-200 font-mono rounded-lg h-24 focus:outline-none focus:ring-1 focus:ring-sky-500"
                    placeholder="Enter JSON payload for testing..."
                  />
                  {parseError && (
                    <p className="text-[10px] text-red-500 bg-red-50 border border-red-100 rounded px-1.5 py-0.5 mt-0.5">
                      ⚠️ JSON Error: {parseError}
                    </p>
                  )}
                </div>

                <div className="space-y-2">
                  <div className="flex items-center justify-between">
                    <span className="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Output</span>
                    <div className="flex bg-slate-100/80 p-0.5 rounded-lg border border-slate-200/60">
                      <button
                        onClick={() => setActiveTab('visual')}
                        className={`text-[9px] font-bold px-2 py-0.5 rounded-md transition-colors ${
                          activeTab === 'visual'
                            ? 'bg-white text-sky-600 shadow-sm'
                            : 'text-slate-500 hover:text-slate-800'
                        }`}
                      >
                        Visual Preview
                      </button>
                      <button
                        onClick={() => setActiveTab('raw')}
                        className={`text-[9px] font-bold px-2 py-0.5 rounded-md transition-colors ${
                          activeTab === 'raw'
                            ? 'bg-white text-sky-600 shadow-sm'
                            : 'text-slate-500 hover:text-slate-800'
                        }`}
                      >
                        HTML Output
                      </button>
                    </div>
                  </div>

                  {activeTab === 'visual' ? (
                    <div 
                      className="bg-white rounded-lg border border-slate-200/80 shadow-inner p-3 min-h-[90px] text-xs overflow-auto max-h-48 prose prose-sm prose-slate"
                      dangerouslySetInnerHTML={{ __html: renderedResult || '<em class="text-slate-400">No data to display</em>' }}
                    />
                  ) : (
                    <textarea
                      readOnly
                      value={renderedResult}
                      className="w-full font-mono text-[10px] p-2 bg-slate-950 text-emerald-400 rounded-lg min-h-[90px] h-24 select-all cursor-text focus:outline-none"
                    />
                  )}
                </div>
              </div>
            </div>
          );
        })()}
        
        {selectedNode.type === 'IteratorNode' && (
          <div className="space-y-4">
            <div>
              <label className="block text-sm font-medium text-slate-700 mb-1">Array Source</label>
              <SkaFXInput 
                nodes={nodes}
                selectedNode={selectedNode}
                mockPayload={mockPayload}
                type="text" 
                value={selectedNode.data.array_source || ''} 
                onChange={(val) => handleChange('array_source', val)}
                className="w-full text-sm p-2 border border-slate-300 rounded focus:ring-2 focus:ring-fuchsia-500 focus:border-fuchsia-500 outline-none font-mono"
                placeholder="[payload.users_list]"
              />
              <p className="text-xs text-slate-500 mt-1">Array variable. Inside Iterator, <code>[item]</code> and <code>[index]</code> will be available.</p>
            </div>
            
            <div className="p-3 bg-blue-50 border border-blue-200 rounded text-sm text-blue-800">
                <strong>Note:</strong> To configure loop steps, select another node and change its Parent Node to this Iterator ID.
            </div>
          </div>
        )}

        {!['TriggerNode', 'SetDataNode', 'DBActionNode', 'DBQueryNode', 'ConditionNode', 'SwitchNode', 'IteratorNode', 'ApiNode', 'ClientResponseNode', 'RenderTemplateNode'].includes(selectedNode.type) && (
          <DynamicNodeSettings 
            selectedNode={selectedNode}
            nodes={nodes}
            onUpdateNode={onUpdateNode}
            mockPayload={mockPayload}
          />
        )}
      </div>
      
      <div className="p-4 border-t border-slate-100 mt-auto">
        <button 
          onClick={() => onDeleteNode(selectedNode.id)}
          className="w-full flex items-center justify-center gap-2 py-2 text-sm font-medium text-red-600 bg-red-50 hover:bg-red-100 rounded transition-colors"
        >
          <Trash2 size={16} />
          {__( 'Delete this Node', 'ska-logic-engine' )}
        </button>
      </div>
    </aside>
  );
}
