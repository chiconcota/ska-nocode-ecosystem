import React from 'react';
import { Handle, Position } from '@xyflow/react';
import BaseNode from './BaseNode';

const { __ } = window.wp?.i18n || { __: (text) => text };

export default function ClientResponseNode(props) {
  const data = props.data || {};
  
  return (
    <BaseNode
      {...props}
      data={data}
      icon={<span className="material-symbols-outlined text-[16px]">reply</span>}
      title={data.label || __( 'Client Response', 'ska-logic-engine' )}
      colorClass="bg-teal-50"
      borderClass="border-teal-200"
      headerClass="bg-teal-100 text-teal-800 border-teal-200"
      hideOutput={true}
    >
      <div className="p-3 text-xs text-slate-600">
        <div className="flex items-center gap-2 mb-2 font-medium text-slate-800">
          <span className="material-symbols-outlined text-[14px]">desktop_windows</span>
          <span>{__( 'UI Action', 'ska-logic-engine' )}: {data.response_type || 'toast'}</span>
        </div>
        
        {data.response_type === 'redirect' && (
          <div className="bg-white p-2 rounded border border-slate-100 mb-1">
            <span className="font-semibold block mb-1">{__( 'URL', 'ska-logic-engine' )}:</span>
            <code className="text-[10px] bg-slate-50 px-1 rounded block truncate">{data.url || __( 'Not set', 'ska-logic-engine' )}</code>
          </div>
        )}

        {data.response_type === 'open_modal' && (
          <div className="bg-white p-2 rounded border border-slate-100 mb-1 space-y-1">
            {data.modal_content ? (
              <>
                <span className="font-semibold block text-[10px]">{__( 'Content', 'ska-logic-engine' )}:</span>
                <span className="text-[10px] text-slate-500 truncate block font-mono bg-slate-50 px-1 rounded" title={data.modal_content}>
                  {data.modal_content.length > 25 ? data.modal_content.substring(0, 25) + '...' : data.modal_content}
                </span>
              </>
            ) : (
              <>
                <span className="font-semibold block text-[10px]">{__( 'Modal ID', 'ska-logic-engine' )}:</span>
                <code className="text-[10px] bg-slate-50 px-1 rounded block truncate">{data.modal_id || __( 'Not set', 'ska-logic-engine' )}</code>
              </>
            )}
          </div>
        )}

        {(!data.response_type || data.response_type === 'toast') && (
          <div className="bg-white p-2 rounded border border-slate-100 mb-1">
            <span className="font-semibold block mb-1">{__( 'Message', 'ska-logic-engine' )}:</span>
            <span className="text-[11px] truncate block">{data.message || __( 'Operation successful!', 'ska-logic-engine' )}</span>
            <div className="mt-1 flex items-center gap-1">
              <span className={`inline-block w-2 h-2 rounded-full ${data.toast_type === 'error' ? 'bg-rose-500' : 'bg-emerald-500'}`}></span>
              <span className="text-[10px] capitalize">{data.toast_type || 'success'}</span>
            </div>
          </div>
        )}
      </div>
    </BaseNode>
  );
}
