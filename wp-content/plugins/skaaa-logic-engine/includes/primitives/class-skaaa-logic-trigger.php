<?php
defined( 'ABSPATH' ) || exit;

class Skaaa_Logic_Trigger_Node implements Skaaa_Logic_Node {
    
    /**
     * Entrypoint cho DAG.
     * Trigger Node không biến đổi Data quá nhiều, 
     * chủ yếu inject thêm metadata (vd: timestamp, trigger_type, workflow_id) vào Payload
     * để các node phía sau có context thực thi.
     * 
     * @param array $payload Dữ liệu từ Trigger
     * @param array $config Cấu hình của Node từ UI
     * @return array
     */
    public function execute( $payload, $config ) {
        
        $trigger_type = $config['triggerType'] ?? 'form_submit';
        
        // Bơm Metadata khởi tạo luồng vào Payload
        $payload['_skaaa_workflow_meta'] = [
            'trigger_type' => $trigger_type,
            'started_at'   => current_time('mysql'),
            'workflow_id'  => $config['workflowId'] ?? 'default',
            'cron_expr'    => $config['cronExpression'] ?? null
        ];

        return [
            'payload' => $payload,
            'port'    => 'main'
        ];
    }
}
