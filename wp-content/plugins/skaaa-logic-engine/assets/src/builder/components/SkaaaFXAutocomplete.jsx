import React, { useState, useEffect, useRef } from 'react';

// Built-in functions with signatures and descriptions in English (Rule 10)
const BUILT_IN_FUNCTIONS = [
  { name: 'IF(', insertText: 'IF(', label: 'IF(cond, true, false)', desc: 'Logical conditional function', type: 'function' },
  { name: 'CONCAT(', insertText: 'CONCAT(', label: 'CONCAT(str1, str2, ...)', desc: 'Concatenate multiple text values', type: 'function' },
  { name: 'LIST_COL(', insertText: 'LIST_COL(', label: 'LIST_COL(array, col, sep)', desc: 'Extract column from array and join', type: 'function' },
  { name: 'ROUND(', insertText: 'ROUND(', label: 'ROUND(num, decimals)', desc: 'Round numeric value to decimal places', type: 'function' },
  { name: 'SUM(', insertText: 'SUM(', label: 'SUM(array)', desc: 'Calculate the sum of array elements', type: 'function' }
];

// Helper to flatten mock payload object keys into path strings (e.g., payload.user.name)
const getFlatPaths = (mockPayloadStr) => {
  if (!mockPayloadStr) return [];
  try {
    const obj = JSON.parse(mockPayloadStr);
    const paths = [];
    
    const traverse = (current, currentPath) => {
      if (current === null || current === undefined) return;
      if (typeof current !== 'object') {
        paths.push(currentPath);
        return;
      }
      if (Array.isArray(current)) {
        paths.push(currentPath);
        paths.push(`${currentPath}.length`);
        return;
      }
      for (const [key, value] of Object.entries(current)) {
        traverse(value, currentPath ? `${currentPath}.${key}` : key);
      }
    };
    
    if (obj && typeof obj === 'object') {
      if ('payload' in obj) {
        traverse(obj.payload, 'payload');
      } else {
        traverse(obj, 'payload');
      }
    }
    return paths;
  } catch (e) {
    return [];
  }
};

// Helper to get preceding result variables in the flow graph
const getPrecedingResultVars = (nodes, currentNodeId) => {
  if (!Array.isArray(nodes)) return [];
  const vars = [];
  nodes.forEach(n => {
    if (n.id === currentNodeId) return;
    
    if (n.type === 'RenderTemplateNode') {
      vars.push(n.data?.result_var || 'payload.rendered_template');
      vars.push('payload.render_template');
    } else if (n.type === 'DBQueryNode') {
      vars.push(n.data?.resultVar || 'payload.query_results');
    } else if (n.type === 'SetDataNode') {
      if (Array.isArray(n.data?.assignments)) {
        n.data.assignments.forEach(assign => {
          if (assign && assign.key) {
            let key = assign.key.trim();
            if (key) {
              if (!key.startsWith('payload.')) {
                key = 'payload.' + key;
              }
              vars.push(key);
            }
          }
        });
      }
    } else {
      const fallbackVar = n.data?.resultVar || n.data?.result_var;
      if (fallbackVar) {
        vars.push(fallbackVar);
      }
    }
  });
  return [...new Set(vars)].filter(Boolean);
};

// Helper to check recursively if the node is child of an IteratorNode
const isInsideIterator = (nodeId, nodes) => {
  if (!nodeId || !Array.isArray(nodes)) return false;
  const node = nodes.find(n => n.id === nodeId);
  if (!node || !node.parentId) return false;
  const parent = nodes.find(n => n.id === node.parentId);
  if (!parent) return false;
  if (parent.type === 'IteratorNode') return true;
  return isInsideIterator(parent.id, nodes);
};

// Helper to extract relative model prefix from physical table ID (e.g. wp_skaaa_data_app_clinic_doctors -> clinic.doctors)
const getTableRelativePrefix = (tableId) => {
  if (!tableId || typeof tableId !== 'string') return '';
  let clean = tableId;
  // Strip wp_prefix if it exists
  const wpPrefixMatch = tableId.match(/^(?:wp|wp_)[0-9]*_?/);
  if (wpPrefixMatch) {
    clean = tableId.substring(wpPrefixMatch[0].length);
  }
  
  if (clean.startsWith('skaaa_data_')) {
    clean = clean.substring('skaaa_data_'.length);
  }
  
  if (clean.startsWith('app_')) {
    const parts = clean.substring(4).split('_');
    if (parts.length >= 2) {
      const appId = parts[0];
      const modelName = parts.slice(1).join('_');
      return `${appId}.${modelName}`;
    }
    return clean.substring(4);
  }
  
  if (clean.startsWith('sys_')) {
    return `sys.${clean.substring(4)}`;
  }
  
  return clean;
};

// Generic autocomplete logic hook
function useAutocomplete({ value, onChange, nodes, selectedNode, mockPayload }) {
  const [showSuggestions, setShowSuggestions] = useState(false);
  const [suggestions, setSuggestions] = useState([]);
  const [activeIdx, setActiveIdx] = useState(0);
  const [triggerInfo, setTriggerInfo] = useState(null); // { type, index, query }
  const [positionAbove, setPositionAbove] = useState(false);
  const inputRef = useRef(null);

  // Parse dictionary and payload variables on mount/change
  const getSuggestionsSource = () => {
    const sources = [];
    
    // 1. Mock Payload variables
    const payloadPaths = getFlatPaths(mockPayload);
    payloadPaths.forEach(path => {
      sources.push({ name: `[${path}]`, insertText: `[${path}]`, label: `[${path}]`, desc: 'Payload Variable', type: 'payload' });
    });
    
    // 2. Preceding output variables
    if (selectedNode) {
      const outputVars = getPrecedingResultVars(nodes, selectedNode.id);
      outputVars.forEach(v => {
        const cleanVar = v.startsWith('[') ? v : `[${v}]`;
        sources.push({ name: cleanVar, insertText: cleanVar, label: cleanVar, desc: 'Output Variable', type: 'output' });
      });
      
      // 3. Loop context variables (item & index)
      if (isInsideIterator(selectedNode.id, nodes)) {
        sources.push({ name: '[$item]', insertText: '[$item]', label: '[$item]', desc: 'Current Loop Item', type: 'loop' });
        sources.push({ name: '[$index]', insertText: '[$index]', label: '[$index]', desc: 'Current Loop Index', type: 'loop' });
      }
    }
    
    // 4. Data dictionary fields
    const tables = window.SKAAA_DAG_CONTEXT?.AVAILABLE_TABLES || [];
    tables.forEach(table => {
      const relPrefix = getTableRelativePrefix(table.id);
      if (Array.isArray(table.columns)) {
        table.columns.forEach(col => {
          const varName = `[${relPrefix}.${col.id}]`;
          sources.push({
            name: varName,
            insertText: varName,
            label: varName,
            desc: `${table.name} > ${col.name}`,
            type: 'field'
          });
        });
      }
    });

    return sources;
  };

  const handleInputSelection = (suggestion) => {
    if (!triggerInfo || !inputRef.current) return;
    
    const text = value || '';
    const before = text.substring(0, triggerInfo.index);
    const after = text.substring(inputRef.current.selectionStart);
    
    let replacement = suggestion.insertText;
    let newCursorPos = before.length + replacement.length;
    
    if (triggerInfo.type === '{') {
      // For templates, wrap the suggestion in double curly braces, stripping any square brackets
      const cleanVar = suggestion.insertText.replace(/[\[\]]/g, '');
      if (triggerInfo.query === '') {
        replacement = `{{ ${cleanVar} }}`;
      } else {
        replacement = `${cleanVar} }}`;
      }
      newCursorPos = before.length + replacement.length;
    } else if (suggestion.type === 'function') {
      // For functions, position caret inside the parenthesis
      replacement = suggestion.insertText + ')';
      newCursorPos = before.length + suggestion.insertText.length;
    }
    
    const newValue = before + replacement + after;
    onChange(newValue);
    setShowSuggestions(false);
    setTriggerInfo(null);
    
    // Re-focus and set caret position asynchronously after React render
    setTimeout(() => {
      if (inputRef.current) {
        inputRef.current.focus();
        inputRef.current.setSelectionRange(newCursorPos, newCursorPos);
      }
    }, 10);
  };

  const checkTriggers = (text, cursorPos) => {
    if (cursorPos === 0) {
      setShowSuggestions(false);
      return;
    }

    const textBeforeCursor = text.substring(0, cursorPos);

    if (inputRef.current) {
      const rect = inputRef.current.getBoundingClientRect();
      const spaceBelow = window.innerHeight - rect.bottom;
      setPositionAbove(spaceBelow < 220);
    }

    // 1. Check double curly brace trigger '{{'
    const lastOpenBrace = textBeforeCursor.lastIndexOf('{{');
    const lastCloseBrace = textBeforeCursor.lastIndexOf('}}');
    if (lastOpenBrace !== -1 && lastOpenBrace > lastCloseBrace) {
      const query = textBeforeCursor.substring(lastOpenBrace + 2).trim();
      setTriggerInfo({ type: '{', index: lastOpenBrace, query });
      
      const allVars = getSuggestionsSource().filter(s => s.type !== 'function');
      const filtered = allVars.filter(s => {
        const cleanName = s.name.replace(/[\[\]]/g, '');
        return cleanName.toLowerCase().includes(query.toLowerCase());
      });
      
      setSuggestions(filtered);
      setShowSuggestions(filtered.length > 0);
      setActiveIdx(0);
      return;
    }

    // 2. Check variable trigger '['
    const lastOpenBracket = textBeforeCursor.lastIndexOf('[');
    const lastCloseBracket = textBeforeCursor.lastIndexOf(']');
    if (lastOpenBracket !== -1 && lastOpenBracket > lastCloseBracket) {
      const query = textBeforeCursor.substring(lastOpenBracket + 1).trim();
      setTriggerInfo({ type: '[', index: lastOpenBracket, query });
      
      const allVars = getSuggestionsSource().filter(s => s.type !== 'function');
      const filtered = allVars.filter(s => s.name.toLowerCase().includes(query.toLowerCase()));
      
      setSuggestions(filtered);
      setShowSuggestions(filtered.length > 0);
      setActiveIdx(0);
      return;
    }

    // 3. Check function trigger (capitalized prefix following operator/space/start)
    const fnMatch = textBeforeCursor.match(/(?:^|[\s+\-*/(,])([A-Z_]{1,10})$/);
    if (fnMatch) {
      const query = fnMatch[1];
      const triggerIndex = cursorPos - query.length;
      setTriggerInfo({ type: 'fn', index: triggerIndex, query });
      
      const filtered = BUILT_IN_FUNCTIONS.filter(f => f.name.toLowerCase().startsWith(query.toLowerCase()));
      setSuggestions(filtered);
      setShowSuggestions(filtered.length > 0);
      setActiveIdx(0);
      return;
    }

    setShowSuggestions(false);
  };

  const onKeyDown = (e) => {
    if (!showSuggestions || suggestions.length === 0) return;

    if (e.key === 'ArrowDown') {
      e.preventDefault();
      setActiveIdx(prev => (prev + 1) % suggestions.length);
    } else if (e.key === 'ArrowUp') {
      e.preventDefault();
      setActiveIdx(prev => (prev - 1 + suggestions.length) % suggestions.length);
    } else if (e.key === 'Enter' || e.key === 'Tab') {
      e.preventDefault();
      handleInputSelection(suggestions[activeIdx]);
    } else if (e.key === 'Escape') {
      e.preventDefault();
      setShowSuggestions(false);
    }
  };

  const onKeyUp = (e) => {
    // Skip arrow keys/navigation keyup to prevent recalculating trigger
    if (['ArrowUp', 'ArrowDown', 'Enter', 'Tab', 'Escape'].includes(e.key)) return;
    checkTriggers(e.target.value, e.target.selectionStart);
  };

  const handleBlur = () => {
    // Delay hiding dropdown so click handlers can run first
    setTimeout(() => {
      setShowSuggestions(false);
    }, 200);
  };

  return {
    showSuggestions,
    suggestions,
    activeIdx,
    inputRef,
    positionAbove,
    onKeyDown,
    onKeyUp,
    handleBlur,
    handleInputSelection,
    setActiveIdx
  };
}

// Render styling helper for suggestion items
const getSuggestionItemClass = (index, activeIndex) => {
  const base = "flex items-center justify-between px-3 py-1.5 hover:bg-slate-100/80 cursor-pointer transition-colors text-slate-700 text-xs";
  return index === activeIndex ? `${base} bg-indigo-50 text-indigo-700 font-medium` : base;
};

// Render icon/badge based on type of suggestion
const getSuggestionTypeBadge = (type) => {
  const badgeClasses = {
    function: "bg-purple-100 text-purple-700 border-purple-200",
    payload: "bg-blue-100 text-blue-700 border-blue-200",
    output: "bg-teal-100 text-teal-700 border-teal-200",
    loop: "bg-fuchsia-100 text-fuchsia-700 border-fuchsia-200",
    field: "bg-slate-100 text-slate-600 border-slate-200"
  };
  
  const label = {
    function: "FN",
    payload: "PAYLOAD",
    output: "OUT",
    loop: "LOOP",
    field: "FIELD"
  }[type] || "VAR";

  return (
    <span className={`text-[8px] px-1 py-0.5 rounded font-bold uppercase tracking-wider border ${badgeClasses[type] || ''}`}>
      {label}
    </span>
  );
};

// SkaaaFXInput wrapper component
export function SkaaaFXInput({ value, onChange, nodes, selectedNode, mockPayload, className = '', ...props }) {
  const {
    showSuggestions,
    suggestions,
    activeIdx,
    inputRef,
    positionAbove,
    onKeyDown,
    onKeyUp,
    handleBlur,
    handleInputSelection,
    setActiveIdx
  } = useAutocomplete({ value, onChange, nodes, selectedNode, mockPayload });

  return (
    <div className="relative w-full">
      <input
        ref={inputRef}
        type="text"
        value={value}
        onChange={(e) => onChange(e.target.value)}
        onKeyDown={onKeyDown}
        onKeyUp={onKeyUp}
        onBlur={handleBlur}
        className={`${className} outline-none focus:ring-2 focus:ring-blue-500`}
        {...props}
      />
      {showSuggestions && suggestions.length > 0 && (
        <div className={`absolute left-0 right-0 ${positionAbove ? 'bottom-full mb-1' : 'top-full mt-1'} max-h-48 overflow-y-auto bg-white/95 backdrop-blur-md border border-slate-200 rounded-lg shadow-lg z-50 py-1 shadow-inner scrollbar-thin`}>
          {suggestions.map((sug, idx) => (
            <div
              key={idx}
              className={getSuggestionItemClass(idx, activeIdx)}
              onMouseDown={() => handleInputSelection(sug)}
              onMouseEnter={() => setActiveIdx(idx)}
            >
              <div className="flex flex-col min-w-0">
                <span className="font-mono font-medium truncate">{sug.label}</span>
                <span className="text-[10px] text-slate-400 truncate mt-0.5">{sug.desc}</span>
              </div>
              <div className="flex-shrink-0 ml-2">
                {getSuggestionTypeBadge(sug.type)}
              </div>
            </div>
          ))}
        </div>
      )}
    </div>
  );
}

// SkaaaFXTextarea wrapper component
export function SkaaaFXTextarea({ value, onChange, nodes, selectedNode, mockPayload, className = '', ...props }) {
  const {
    showSuggestions,
    suggestions,
    activeIdx,
    inputRef,
    positionAbove,
    onKeyDown,
    onKeyUp,
    handleBlur,
    handleInputSelection,
    setActiveIdx
  } = useAutocomplete({ value, onChange, nodes, selectedNode, mockPayload });

  return (
    <div className="relative w-full">
      <textarea
        ref={inputRef}
        value={value}
        onChange={(e) => onChange(e.target.value)}
        onKeyDown={onKeyDown}
        onKeyUp={onKeyUp}
        onBlur={handleBlur}
        className={`${className} outline-none focus:ring-2 focus:ring-blue-500`}
        {...props}
      />
      {showSuggestions && suggestions.length > 0 && (
        <div className={`absolute left-0 right-0 ${positionAbove ? 'bottom-full mb-1' : 'top-full mt-1'} max-h-48 overflow-y-auto bg-white/95 backdrop-blur-md border border-slate-200 rounded-lg shadow-lg z-50 py-1 shadow-inner scrollbar-thin`}>
          {suggestions.map((sug, idx) => (
            <div
              key={idx}
              className={getSuggestionItemClass(idx, activeIdx)}
              onMouseDown={() => handleInputSelection(sug)}
              onMouseEnter={() => setActiveIdx(idx)}
            >
              <div className="flex flex-col min-w-0">
                <span className="font-mono font-medium truncate">{sug.label}</span>
                <span className="text-[10px] text-slate-400 truncate mt-0.5">{sug.desc}</span>
              </div>
              <div className="flex-shrink-0 ml-2">
                {getSuggestionTypeBadge(sug.type)}
              </div>
            </div>
          ))}
        </div>
      )}
    </div>
  );
}
