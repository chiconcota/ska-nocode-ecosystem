import React from 'react';
import { Settings, X, Trash2, Code } from 'lucide-react';
import TablePicker from './TablePicker';

export default function SettingsPanel({ selectedNode, onUpdateNode, onDeleteNode, onClose }) {
  if (!selectedNode) return null;

  const handleChange = (key, value) => {
    onUpdateNode(selectedNode.id, {
      ...selectedNode.data,
      [key]: value
    });
  };

  return (
    <aside className="w-80 flex-shrink-0 bg-white border-l border-slate-200 flex flex-col h-full shadow-sm z-10">
      <div className="p-4 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
        <h3 className="font-semibold text-slate-800 flex items-center gap-2">
          <Settings size={18} className="text-blue-500" />
          Settings
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
          <label className="block text-sm font-medium text-slate-700 mb-1">Node ID</label>
          <input 
            type="text" 
            value={selectedNode.id} 
            disabled
            className="w-full text-sm p-2 border border-slate-200 rounded bg-slate-50 text-slate-500 font-mono cursor-not-allowed"
          />
        </div>
        
        {selectedNode.type !== 'TriggerNode' && (
          <div>
            <label className="block text-sm font-medium text-slate-700 mb-1">Parent Node (Iterator)</label>
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
              placeholder="Tự động gán khi kéo vào Iterator"
            />
            <p className="text-[10px] text-slate-500 mt-1">Tự động gán khi kéo node vào Iterator. Xóa trắng để tháo ra.</p>
          </div>
        )}
        
        <div>
          <label className="block text-sm font-medium text-slate-700 mb-1">Label</label>
          <input 
            type="text" 
            value={selectedNode.data.label || ''} 
            onChange={(e) => handleChange('label', e.target.value)}
            className="w-full text-sm p-2 border border-slate-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
            placeholder="Enter node label"
          />
        </div>

        <div>
          <label className="block text-sm font-medium text-slate-700 mb-1">Description</label>
          <textarea 
            value={selectedNode.data.description || ''} 
            onChange={(e) => handleChange('description', e.target.value)}
            className="w-full text-sm p-2 border border-slate-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none resize-none h-24"
            placeholder="Enter description"
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
                  <input 
                    type="text" 
                    value={assignment.value || ''} 
                    onChange={(e) => {
                      const newAssignments = [...(selectedNode.data.assignments || [])];
                      newAssignments[index] = { ...newAssignments[index], value: e.target.value };
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
            <p className="text-[10px] text-slate-400 italic">Hint: Sử dụng [...] để tính toán hoặc {'{{ ... }}'} để nối chuỗi.</p>
            <button 
              onClick={() => {
                const newAssignments = [...(selectedNode.data.assignments || []), { key: '', value: '' }];
                handleChange('assignments', newAssignments);
              }}
              className="w-full py-1.5 text-xs font-medium text-indigo-600 border border-indigo-200 rounded bg-indigo-50 hover:bg-indigo-100 transition-colors"
            >
              + Add Variable
            </button>
          </div>
        )}
        
        {selectedNode.type === 'ConditionNode' && (
          <div>
            <label className="block text-sm font-medium text-slate-700 mb-1">If condition</label>
            <textarea 
              value={selectedNode.data.expression || ''} 
              onChange={(e) => handleChange('expression', e.target.value)}
              className="w-full text-sm p-2 border border-slate-300 rounded focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none font-mono text-amber-700 bg-amber-50/30 resize-none h-20"
              placeholder="[trigger.age] > 18 AND [status] == 'active'"
            />
            <p className="text-xs text-slate-500 mt-1">Sử dụng cú pháp SkaFX. Biến phải nằm trong dấu ngoặc vuông `[biến]`.</p>
          </div>
        )}

        {selectedNode.type === 'SwitchNode' && (
          <div className="space-y-4">
            <div className="flex items-center justify-between border-b pb-2">
              <label className="block text-sm font-medium text-slate-700">Routes (Nhánh rẽ)</label>
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
                  <input 
                    type="text" 
                    value={route.expression || ''} 
                    onChange={(e) => {
                      const newRoutes = [...(selectedNode.data.routes || [])];
                      newRoutes[index] = { ...newRoutes[index], expression: e.target.value };
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
            <p className="text-xs text-slate-500 mt-2">Lưu ý: Luôn có cổng 'Default' được tự sinh trên Canvas.</p>
          </div>
        )}

        {selectedNode.type === 'DBActionNode' && (
          <div className="space-y-4">
            <div>
              <label className="block text-sm font-medium text-slate-700 mb-1">Hành động (Action)</label>
              <select 
                value={selectedNode.data.actionType || 'insert'} 
                onChange={(e) => handleChange('actionType', e.target.value)}
                className="w-full text-sm p-2 border border-slate-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none bg-white"
              >
                <option value="insert">Thêm mới (Insert)</option>
                <option value="update">Cập nhật (Update)</option>
                <option value="delete">Xóa (Delete)</option>
              </select>
            </div>
            
            <div>
              <label className="block text-sm font-medium text-slate-700 mb-1">Bảng dữ liệu (Table)</label>
              <TablePicker 
                value={selectedNode.data.table || ''}
                onChange={(val) => handleChange('table', val)}
              />
            </div>

            {(selectedNode.data.actionType === 'update' || selectedNode.data.actionType === 'delete') && (
              <div>
                <label className="block text-sm font-medium text-slate-700 mb-1">Record ID (SkaFX var)</label>
                <input 
                  type="text" 
                  value={selectedNode.data.recordId || ''} 
                  onChange={(e) => handleChange('recordId', e.target.value)}
                  className="w-full text-sm p-2 border border-rose-300 rounded focus:ring-2 focus:ring-rose-500 focus:border-rose-500 outline-none font-mono"
                  placeholder="[payload.id]"
                />
                <p className="text-xs text-slate-500 mt-1">Truyền biến ID của bản ghi để tìm kiếm.</p>
              </div>
            )}

            {selectedNode.data.table && selectedNode.data.actionType !== 'delete' && (
              <div className="mt-4 border-t border-slate-200 pt-4">
                <label className="block text-sm font-medium text-slate-700 mb-2">Ánh xạ dữ liệu (Auto-map)</label>
                <div className="space-y-2">
                  {(() => {
                    const tableObj = (window.SKA_DAG_CONTEXT?.AVAILABLE_TABLES || []).find(t => t.id === selectedNode.data.table);
                    if (!tableObj || !tableObj.columns) return <p className="text-xs text-slate-500">Không tìm thấy schema cột.</p>;
                    
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
                               <option value="">-- Chọn một giá trị --</option>
                               {options.map((opt, idx) => (
                                  <option key={idx} value={opt.value || opt}>{opt.label || opt.value || opt}</option>
                               ))}
                            </select>
                          ) : (
                            <input
                              type="text"
                              value={mappings[col.id] || ''}
                              onChange={(e) => {
                                handleChange('mappings', {
                                  ...mappings,
                                  [col.id]: e.target.value
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
              <label className="block text-sm font-medium text-slate-700 mb-1">Bảng dữ liệu (Table)</label>
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
                <option value="multiple">Nhiều dòng (Array)</option>
                <option value="single">Một dòng (Object)</option>
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
                    <input 
                      type="text" 
                      value={cond.value || ''} 
                      onChange={(e) => {
                        const newConds = [...(selectedNode.data.conditions || [])];
                        newConds[index] = { ...newConds[index], value: e.target.value };
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
              <label className="block text-sm font-medium text-slate-700 mb-1">Lưu kết quả vào (Result Variable)</label>
              <input 
                type="text" 
                value={selectedNode.data.resultVar || ''} 
                onChange={(e) => handleChange('resultVar', e.target.value)}
                className="w-full text-sm p-2 border border-slate-300 rounded focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 outline-none font-mono"
                placeholder="payload.query_results"
              />
              <p className="text-xs text-slate-500 mt-1">VD: <code>payload.users</code>. Dữ liệu sẽ được lưu vào biến này để các node sau sử dụng.</p>
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
              <input 
                type="text" 
                value={selectedNode.data.url || ''} 
                onChange={(e) => handleChange('url', e.target.value)}
                className="w-full text-sm p-2 border border-slate-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none font-mono text-xs"
                placeholder="https://api.example.com/v1/users/{{payload.id}}"
              />
              <p className="text-[10px] text-slate-500 mt-1">Hỗ trợ SkaFX bằng cú pháp &#123;&#123; payload.abc &#125;&#125;</p>
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
                    <input 
                      type="text" 
                      value={header.value || ''} 
                      onChange={(e) => {
                        const newHeaders = [...(selectedNode.data.headers || [])];
                        newHeaders[index] = { ...newHeaders[index], value: e.target.value };
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
                <textarea 
                  value={selectedNode.data.body || ''} 
                  onChange={(e) => handleChange('body', e.target.value)}
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
                <option value="remove_row">Remove Row (Xóa dòng)</option>
                <option value="redirect">Redirect / Navigate</option>
                <option value="open_modal">Open Modal Popup</option>
                <option value="fire_event">Fire Custom Event</option>
              </select>
            </div>

            {selectedNode.data.response_type === 'redirect' && (
              <div>
                <label className="block text-sm font-medium text-slate-700 mb-1">Redirect URL</label>
                <input 
                  type="text" 
                  value={selectedNode.data.url || ''} 
                  onChange={(e) => handleChange('url', e.target.value)}
                  className="w-full text-sm p-2 border border-slate-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none font-mono"
                  placeholder="https://example.com/success"
                />
                <p className="text-[10px] text-slate-500 mt-1">Hỗ trợ SkaFX (vd: [payload.next_url])</p>
              </div>
            )}

            {selectedNode.data.response_type === 'open_modal' && (
              <div>
                <label className="block text-sm font-medium text-slate-700 mb-1">Modal ID</label>
                <input 
                  type="text" 
                  value={selectedNode.data.modal_id || ''} 
                  onChange={(e) => handleChange('modal_id', e.target.value)}
                  className="w-full text-sm p-2 border border-slate-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none font-mono"
                  placeholder="login_modal"
                />
                <p className="text-[10px] text-slate-500 mt-1">ID của Modal Element (vd: my_modal)</p>
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
                <p className="text-[10px] text-slate-500 mt-1">Sẽ kích hoạt: window.dispatchEvent(...) với detail chứa toàn bộ payload.</p>
              </div>
            )}

            {(!selectedNode.data.response_type || selectedNode.data.response_type === 'toast' || selectedNode.data.response_type === 'remove_row') && (
              <div className="space-y-4">
                <div>
                  <label className="block text-sm font-medium text-slate-700 mb-1">Toast Message</label>
                  <textarea 
                    value={selectedNode.data.message || ''} 
                    onChange={(e) => handleChange('message', e.target.value)}
                    className="w-full text-sm p-2 border border-slate-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                    placeholder="Thao tác thành công!"
                  />
                  <p className="text-[10px] text-slate-500 mt-1">Hỗ trợ SkaFX (vd: Cảm ơn [payload.name])</p>
                </div>
                <div>
                  <label className="block text-sm font-medium text-slate-700 mb-1">Toast Type</label>
                  <select 
                    value={selectedNode.data.toast_type || 'success'} 
                    onChange={(e) => handleChange('toast_type', e.target.value)}
                    className="w-full text-sm p-2 border border-slate-300 rounded focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none bg-white"
                  >
                    <option value="success">Thành công (Success)</option>
                    <option value="error">Lỗi (Error)</option>
                  </select>
                </div>
              </div>
            )}
          </div>
        )}
        
        {selectedNode.type === 'RenderTemplateNode' && (
          <div className="space-y-4">
            <div>
              <label className="block text-sm font-medium text-slate-700 mb-1">Nguồn Template (Source)</label>
              <select 
                value={selectedNode.data.source_type || 'system'} 
                onChange={(e) => handleChange('source_type', e.target.value)}
                className="w-full text-sm p-2 border border-slate-300 rounded focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none bg-white"
              >
                <option value="system">Từ System Organisms (Builder)</option>
                <option value="raw">Từ Biến / Custom Text (Ska Data Pro)</option>
              </select>
            </div>

            {(!selectedNode.data.source_type || selectedNode.data.source_type === 'system') ? (
                <div>
                  <label className="block text-sm font-medium text-slate-700 mb-1">Organism ID</label>
                  <input 
                    type="text" 
                    value={selectedNode.data.organism_id || ''} 
                    onChange={(e) => handleChange('organism_id', e.target.value)}
                    className="w-full text-sm p-2 border border-slate-300 rounded focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none font-mono text-xs"
                    placeholder="VD: org_12345 hoặc [payload.org_id]"
                  />
                  <p className="text-[10px] text-slate-500 mt-1">ID hoặc tên của template trong bảng ska_data_sys_organisms.</p>
                </div>
            ) : (
                <div>
                  <label className="block text-sm font-medium text-slate-700 mb-1">Dữ liệu HTML / Biến</label>
                  <textarea 
                    value={selectedNode.data.raw_template || ''} 
                    onChange={(e) => handleChange('raw_template', e.target.value)}
                    className="w-full text-sm p-2 border border-slate-300 rounded focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none font-mono text-xs min-h-[100px]"
                    placeholder="Vd: {{ payload.db_result.html_content }}"
                  />
                  <p className="text-[10px] text-slate-500 mt-1">Sử dụng SkaFX &#123;&#123; biến &#125;&#125; để truyền nguyên cục HTML lấy từ DB Query Node vào đây.</p>
                </div>
            )}
            
            <div className="border-t border-slate-200 pt-4 mt-4">
              <label className="block text-sm font-medium text-slate-700 mb-1">Lưu HTML vào biến (Result Var)</label>
              <input 
                type="text" 
                value={selectedNode.data.result_var || 'payload.rendered_template'} 
                onChange={(e) => handleChange('result_var', e.target.value)}
                className="w-full text-sm p-2 border border-slate-300 rounded focus:ring-2 focus:ring-sky-500 focus:border-sky-500 outline-none font-mono"
                placeholder="payload.rendered_template"
              />
              <p className="text-xs text-slate-500 mt-1">Dữ liệu HTML sau khi nội suy sẽ được lưu vào biến này.</p>
            </div>
          </div>
        )}
        
        {selectedNode.type === 'IteratorNode' && (
          <div className="space-y-4">
            <div>
              <label className="block text-sm font-medium text-slate-700 mb-1">Array Source (Nguồn dữ liệu lặp)</label>
              <input 
                type="text" 
                value={selectedNode.data.array_source || ''} 
                onChange={(e) => handleChange('array_source', e.target.value)}
                className="w-full text-sm p-2 border border-slate-300 rounded focus:ring-2 focus:ring-fuchsia-500 focus:border-fuchsia-500 outline-none font-mono"
                placeholder="[payload.users_list]"
              />
              <p className="text-xs text-slate-500 mt-1">Biến chứa danh sách (Array). Bên trong Iterator sẽ có sẵn biến <code>[item]</code> và <code>[index]</code>.</p>
            </div>
            
            <div className="p-3 bg-blue-50 border border-blue-200 rounded text-sm text-blue-800">
                <strong>Lưu ý:</strong> Để cấu hình các bước trong vòng lặp, hãy chọn một node khác và thay đổi `Parent Node` của nó thành ID của Iterator này.
            </div>
          </div>
        )}
      </div>
      
      <div className="p-4 border-t border-slate-100 mt-auto">
        <button 
          onClick={() => onDeleteNode(selectedNode.id)}
          className="w-full flex items-center justify-center gap-2 py-2 text-sm font-medium text-red-600 bg-red-50 hover:bg-red-100 rounded transition-colors"
        >
          <Trash2 size={16} />
          Xóa Node này
        </button>
      </div>
    </aside>
  );
}
