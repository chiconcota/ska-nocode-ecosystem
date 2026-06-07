import React, { useState, useRef, useEffect } from 'react';
import { Search, ChevronDown, Database, Check } from 'lucide-react';

export default function TablePicker({ value, onChange }) {
  const [isOpen, setIsOpen] = useState(false);
  const [searchTerm, setSearchTerm] = useState('');
  const wrapperRef = useRef(null);

  const tables = window.SKA_DAG_CONTEXT?.AVAILABLE_TABLES || [];
  
  const filteredTables = tables.filter(t => 
    t.name.toLowerCase().includes(searchTerm.toLowerCase()) || 
    t.id.toLowerCase().includes(searchTerm.toLowerCase())
  );

  const selectedTable = tables.find(t => t.id === value);

  // Close when clicking outside or pressing ESC
  useEffect(() => {
    function handleClickOutside(event) {
      if (wrapperRef.current && !wrapperRef.current.contains(event.target)) {
        // Only close if it's not the modal backdrop click (handled separately)
        // Wait, if it's a fixed modal, the click outside logic might need adjustment.
        // Actually, the backdrop has onClick={() => setIsOpen(false)}, so we just need ESC key handler.
      }
    }

    function handleKeyDown(event) {
      if (event.key === 'Escape') {
        setIsOpen(false);
      }
    }

    if (isOpen) {
      document.addEventListener('keydown', handleKeyDown);
    }
    
    document.addEventListener("mousedown", handleClickOutside);
    return () => {
      document.removeEventListener("mousedown", handleClickOutside);
      document.removeEventListener('keydown', handleKeyDown);
    };
  }, [isOpen]);

  return (
    <div className="relative" ref={wrapperRef}>
      <div 
        className="w-full text-sm p-2 border border-slate-300 rounded focus-within:ring-2 focus-within:ring-blue-500 focus-within:border-blue-500 outline-none bg-white flex justify-between items-center cursor-pointer"
        onClick={() => {
          setIsOpen(!isOpen);
          setSearchTerm('');
        }}
      >
        <div className="flex items-center gap-2 truncate">
          <Database size={14} className="text-slate-400 flex-shrink-0" />
          <span className={selectedTable ? 'text-slate-800' : 'text-slate-400'}>
            {selectedTable ? `${selectedTable.name} (${selectedTable.id})` : '-- Search or select a table --'}
          </span>
        </div>
        <ChevronDown size={16} className={`text-slate-400 transition-transform ${isOpen ? 'rotate-180' : ''}`} />
      </div>

      {isOpen && (
        <>
          {/* Fixed Backdrop */}
          <div className="fixed inset-0 z-[9999] bg-slate-900/40 backdrop-blur-sm flex items-center justify-center p-4" onClick={() => setIsOpen(false)}>
            
            {/* Modal Content */}
            <div 
              className="bg-white rounded-xl shadow-2xl w-full max-w-lg flex flex-col overflow-hidden max-h-[80vh] transform transition-all"
              onClick={e => e.stopPropagation()}
              onKeyDown={e => { if (e.key === 'Escape') setIsOpen(false); }}
              tabIndex={-1}
            >
              {/* Header / Search */}
              <div className="p-4 border-b border-slate-100 flex items-center gap-3 bg-white">
                <Search size={18} className="text-blue-500" />
                <input 
                  type="text" 
                  className="w-full text-base outline-none bg-transparent placeholder-slate-400 text-slate-800"
                  placeholder="Search table by name or ID..."
                  value={searchTerm}
                  onChange={(e) => setSearchTerm(e.target.value)}
                  onKeyDown={(e) => { if (e.key === 'Escape') setIsOpen(false); }}
                  autoFocus
                />
                <div className="text-xs text-slate-400 bg-slate-100 px-2 py-1 rounded font-mono">ESC to close</div>
              </div>
              
              {/* Body / List */}
              <div className="overflow-y-auto flex-1 p-4 bg-slate-50">
                {filteredTables.length === 0 ? (
                  <div className="p-8 text-center flex flex-col items-center justify-center">
                    <Database size={32} className="text-slate-300 mb-2" />
                    <span className="text-sm text-slate-500 font-medium">No tables found</span>
                    <span className="text-xs text-slate-400 mt-1">Try searching with another keyword</span>
                  </div>
                ) : (
                  Object.entries(
                    filteredTables.reduce((acc, t) => {
                      const group = t.app_group || 'Other';
                      if (!acc[group]) acc[group] = [];
                      acc[group].push(t);
                      return acc;
                    }, {})
                  ).map(([groupName, groupTables]) => (
                    <div key={groupName} className="mb-5 last:mb-0">
                      <div className="flex items-center gap-2 mb-2 px-1">
                        <div className="text-xs font-bold text-slate-500 uppercase tracking-wider">
                          {groupName}
                        </div>
                        <div className="h-px bg-slate-200 flex-1"></div>
                      </div>
                      <div className="grid grid-cols-1 gap-2">
                        {groupTables.map(t => (
                          <div 
                            key={t.id}
                            className={`p-3 rounded-lg flex items-start justify-between cursor-pointer border transition-all ${value === t.id ? 'bg-blue-50 border-blue-200 text-blue-700 shadow-sm' : 'bg-white border-slate-200 hover:border-blue-300 hover:shadow-md text-slate-700'}`}
                            onClick={() => {
                              onChange(t.id);
                              setIsOpen(false);
                            }}
                          >
                            <div className="flex flex-col">
                              <span className="font-semibold text-sm">{t.name}</span>
                              <span className={`text-[11px] font-mono mt-1 ${value === t.id ? 'text-blue-500/70' : 'text-slate-400'}`}>
                                {t.id}
                              </span>
                            </div>
                            {value === t.id ? (
                              <div className="bg-blue-100 p-1 rounded-full text-blue-600">
                                <Check size={16} />
                              </div>
                            ) : (
                              <div className="p-1 rounded-full text-slate-300 opacity-0 group-hover:opacity-100">
                                <ChevronDown size={16} className="-rotate-90" />
                              </div>
                            )}
                          </div>
                        ))}
                      </div>
                    </div>
                  ))
                )}
              </div>
            </div>
          </div>
        </>
      )}
    </div>
  );
}
