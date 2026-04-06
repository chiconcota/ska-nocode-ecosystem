<?php
defined( 'ABSPATH' ) || exit;

// Lấy danh sách bảng từ Ska Data Pro
$data_dictionary = get_option('ska_data_dictionary', []);
$available_tables = [];
foreach($data_dictionary as $table_name => $meta) {
    if (isset($meta['__table_info'])) {
        $available_tables[] = [
            'id' => $table_name,
            'name' => $meta['__table_info']['name']
        ];
    }
}

// Danh sách các Cục Node khả dụng (Nhất quán với các Class đã tạo)
$available_nodes = [
    [
        'class' => 'Ska_Slug_Processor',
        'name' => '🐌 Tạo Slug (Rửa Data)',
        'type' => 'processor',
        'color' => '#f59e0b',
        'fields' => [
            ['key' => 'source_field', 'label' => 'Trường Nguồn', 'placeholder' => 'vd: full_name'],
            ['key' => 'target_field', 'label' => 'Lưu vào Trường này', 'placeholder' => 'vd: user_slug']
        ]
    ],
    [
        'class' => 'Ska_Date_Processor',
        'name' => '📅 Chuẩn hóa Định dạng Ngày',
        'type' => 'processor',
        'color' => '#f59e0b',
        'fields' => [
            ['key' => 'source_field', 'label' => 'Trường Nguồn (Date)', 'placeholder' => 'vd: birthday'],
            ['key' => 'format', 'label' => 'Đóng Format PHP', 'placeholder' => 'vd: Y-m-d H:i:s']
        ]
    ],
    [
        'class' => 'Ska_Insert_Data_Action',
        'name' => '🗄️ Lưu Xuống Data (Bất kỳ Bảng nào)',
        'type' => 'action',
        'color' => '#10b981',
        'fields' => [
            ['key' => 'table_name', 'label' => 'Điền Tên Bảng Đích (Hoặc Chọn)', 'type' => 'datalist', 'options' => $available_tables, 'placeholder' => 'vd: wp_ska_data_leads, wp_posts...']
        ]
    ],
    [
        'class' => 'Ska_Email_Action',
        'name' => '✉️ Gửi Email (Có {{biến_động}})',
        'type' => 'action',
        'color' => '#3b82f6',
        'fields' => [
            ['key' => 'to', 'label' => 'Gửi Tới', 'placeholder' => 'vd: admin@ska.net hoặc {{email}}'],
            ['key' => 'subject', 'label' => 'Chủ Đề', 'placeholder' => 'Có đơn hàng từ {{name}}']
        ]
    ]
];

// Lấy danh sách Workflow hiện có
$all_workflows = get_option('ska_logic_simple_workflows', []);
$all_workflow_ids = array_keys($all_workflows);

// Nếu người dùng chọn từ dropdown, load đúng ID đó, nếu không load cái đầu tiên (hoặc default)
$current_wf_id = isset($_GET['workflow_id']) ? sanitize_text_field($_GET['workflow_id']) : (empty($all_workflow_ids) ? 'default_form_submit' : $all_workflow_ids[0]);

$current_wf = isset($all_workflows[$current_wf_id]) ? $all_workflows[$current_wf_id] : [];
$saved_graph = empty($current_wf['graph']) ? '[]' : wp_json_encode($current_wf['graph']);

?>

<style>
.ska-linear-card {
    background: #fff; border: 1px solid #e5e7eb; border-radius: 8px; margin-bottom: 24px; box-shadow: 0 2px 4px rgba(0,0,0,0.02);
    position: relative;
}
.ska-linear-card::after {
    content: ''; position: absolute; bottom: -25px; left: 50%; width: 2px; height: 24px; background: #cbd5e1;
    margin-left: -1px; z-index: 1;
}
.ska-linear-card:last-child::after { display: none; }
.ska-linear-card .card-header {
    background: #f8fafc; padding: 12px 16px; border-bottom: 1px solid #e5e7eb; border-radius: 8px 8px 0 0;
    display: flex; justify-content: space-between; align-items: center; font-weight: 600; font-size: 14px;
}
.ska-linear-card .card-body { padding: 16px; display: flex; flex-direction: column; gap: 12px; }
.ska-field-group { display: flex; flex-direction: column; gap: 6px; }
.ska-field-group label { font-size: 12px; font-weight: 600; color: #4b5563; }
.ska-field-group input, .ska-field-group select { width: 100%; border-radius: 4px; border: 1px solid #d1d5db; padding: 6px 12px; }
.ska-remove-node { color: #ef4444; cursor: pointer; display: flex; align-items: center; border:none; background:none; font-size:12px; }
.ska-remove-node:hover { text-decoration: underline; }

.ska-add-btn { background: #fff; border: 2px dashed #94a3b8; color: #475569; padding: 12px; border-radius: 8px; cursor: pointer; text-align: center; display: block; width: 100%; font-weight: bold; transition: all 0.2s; }
.ska-add-btn:hover { background: #f1f5f9; border-color: #64748b; }
.ska-node-menu { background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px; margin-top: 10px; display: none; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
.ska-node-menu.active { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
.ska-node-item { border: 1px solid #e2e8f0; padding: 10px 12px; border-radius: 6px; cursor: pointer; font-size: 13px; font-weight: 500; transition: 0.1s; display: flex; align-items: center; gap: 8px;}
.ska-node-item:hover { border-color: #3b82f6; background: #eff6ff; }
</style>

<div style="background: white; border: 1px solid #e5e7eb; border-radius: 12px; padding: 24px; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); max-width: 600px;">
    <div style="display:flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #f3f4f6; padding-bottom: 12px; margin-bottom: 16px;">
        <h3 style="margin:0; font-size: 16px;"><span class="dashicons dashicons-networking text-gray-500"></span> Linear Workflow Builder</h3>
        <?php if(!empty($all_workflows)): ?>
        <select onchange="if(this.value !== '') window.location.href='?page=ska-logic-engine&workflow_id=' + this.value" style="font-size:12px; border-radius:4px; max-width:200px;">
            <option value="">-- Chuyển Băng Chuyền Khác --</option>
            <?php foreach($all_workflows as $wid => $wdata): ?>
                <option value="<?php echo esc_attr($wid); ?>" <?php selected($wid, $current_wf_id); ?>><?php echo esc_html($wid); ?></option>
            <?php endforeach; ?>
        </select>
        <?php endif; ?>
    </div>
    
    <form method="POST" id="skaWorkflowForm">
        <?php wp_nonce_field('ska_logic_nonce'); ?>
        <input type="hidden" name="ska_logic_save" value="1">
        <input type="hidden" name="ska_linear_graph" id="skaLinearGraphInput" value="">
        
        <!-- MODULE ĐẦU VÀO LUÔN CỐ ĐỊNH Ở ĐỈNH -->
        <div class="ska-linear-card" style="border-color: #fca5a5;">
            <div class="card-header" style="background: #fef2f2; color: #b91c1c;">
                <span>⚡ Trigger: Nhận Form Frontend</span>
            </div>
            <div class="card-body">
                <div class="ska-field-group">
                    <label>Form Action ID (Mã định danh duy nhất của Khối Form)</label>
                    <input type="text" name="ska_form_id" value="<?php echo esc_attr($current_wf_id); ?>" required style="background:#fff; border-color:#fca5a5;">
                    <p style="margin:4px 0 0 0; font-size:11px; color:#ef4444;">* Đổi tên ID này thành text khác (vd: <b>form_contact_01</b>) rồi bấm Lưu. Hành động này sẽ TẠO MỚI một băng chuyền chạy độc lập!</p>
                </div>
            </div>
        </div>

        <!-- ZONE CHỨA CÁC NODE ĐỘNG -->
        <div id="skaNodeList"></div>

        <!-- NÚT ADD NODE -->
        <div style="margin-top:25px;">
            <button type="button" class="ska-add-btn" onclick="document.getElementById('skaNodeMenu').classList.toggle('active')">+ Thêm Bước Tiếp Theo</button>
            <div id="skaNodeMenu" class="ska-node-menu">
                <?php foreach($available_nodes as $n): ?>
                    <div class="ska-node-item" onclick="addNode('<?php echo esc_js($n['class']); ?>')" style="border-left: 4px solid <?php echo $n['color']; ?>">
                        <?php echo esc_html($n['name']); ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <button type="submit" class="button button-primary" style="background:#10b981; border-color:#10b981; width: 100%; padding: 6px 0; font-size: 15px; margin-top: 30px; border-radius: 6px;">💾 Kích Hoạt Luồng Máy</button>
    </form>
</div>

<script>
const AVAILABLE_NODES = <?php echo json_encode($available_nodes); ?>;
let CURRENT_GRAPH = <?php echo $saved_graph; ?>;

const nodeListEl = document.getElementById('skaNodeList');
const graphInput = document.getElementById('skaLinearGraphInput');

function renderNodes() {
    nodeListEl.innerHTML = '';
    
    CURRENT_GRAPH.forEach((node, index) => {
        // Tìm gốc Template của Node
        const template = AVAILABLE_NODES.find(n => n.class === node.class);
        if(!template) return;

        let fieldsHtml = '';
        if(template.fields) {
            template.fields.forEach(f => {
                const val = node.config[f.key] || '';
                
                if (f.type === 'select') {
                    let opts = `<option value="">-- Chọn --</option>`;
                    f.options.forEach(o => {
                        const sel = (o.id === val) ? 'selected' : '';
                        opts += `<option value="${o.id}" ${sel}>${o.name} (${o.id})</option>`;
                    });
                    
                    fieldsHtml += `
                    <div class="ska-field-group">
                        <label>${f.label}</label>
                        <select onchange="updateConfig(${index}, '${f.key}', this.value)">${opts}</select>
                    </div>`;
                } else if (f.type === 'datalist') {
                    // Datalist cho phép vừa gõ vừa chọn
                    let listId = 'datalist_' + index + '_' + f.key;
                    let opts = `<option value="wp_posts">Core: Bài Viết (wp_posts)</option>`;
                    opts += `<option value="wp_users">Core: Thành Viên (wp_users)</option>`;
                    if(f.options){
                        f.options.forEach(o => { opts += `<option value="${o.id}">${o.name} (${o.id})</option>`; });
                    }
                    fieldsHtml += `
                    <div class="ska-field-group">
                        <label>${f.label}</label>
                        <input type="text" list="${listId}" placeholder="${f.placeholder || ''}" value="${val}" onkeyup="updateConfig(${index}, '${f.key}', this.value)" onchange="updateConfig(${index}, '${f.key}', this.value)">
                        <datalist id="${listId}">${opts}</datalist>
                    </div>`;
                } else {
                    fieldsHtml += `
                    <div class="ska-field-group">
                        <label>${f.label}</label>
                        <input type="text" placeholder="${f.placeholder || ''}" value="${val}" onkeyup="updateConfig(${index}, '${f.key}', this.value)" onchange="updateConfig(${index}, '${f.key}', this.value)">
                    </div>`;
                }
            });
        }

        const card = document.createElement('div');
        card.className = 'ska-linear-card';
        card.innerHTML = `
            <div class="card-header">
                <div>Bước ${index + 1}: <span style="color:${template.color}">${template.name}</span></div>
                <button type="button" class="ska-remove-node" onclick="removeNode(${index})"><span class="dashicons dashicons-trash" style="font-size:14px;width:14px;height:14px;margin-right:2px;"></span> Xóa</button>
            </div>
            <div class="card-body">
                ${fieldsHtml || '<em style="color:#94a3b8;font-size:12px;">Hành động này không cần cấu hình.</em>'}
            </div>
        `;
        nodeListEl.appendChild(card);
    });

    syncInput();
}

function addNode(classId) {
    const template = AVAILABLE_NODES.find(n => n.class === classId);
    if(!template) return;

    CURRENT_GRAPH.push({
        type: template.type,
        class: template.class,
        config: {}
    });
    
    document.getElementById('skaNodeMenu').classList.remove('active');
    renderNodes();
}

function removeNode(idx) {
    if(confirm('Ông có chắc muốn chặt đứt nối dây này?')){
        CURRENT_GRAPH.splice(idx, 1);
        renderNodes();
    }
}

function updateConfig(idx, key, val) {
    if(!CURRENT_GRAPH[idx].config) CURRENT_GRAPH[idx].config = {};
    CURRENT_GRAPH[idx].config[key] = val;
    syncInput();
}

function syncInput() {
    graphInput.value = JSON.stringify(CURRENT_GRAPH);
}

// Khởi chạy
renderNodes();
</script>
