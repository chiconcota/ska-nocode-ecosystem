<?php
/**
 * View: Admin Dashboard
 * 
 * @package Ska_Builder_Core\Admin_Dashboard
 */

defined( 'ABSPATH' ) || exit;

// Note: Outputting inline styles/scripts for MVP.
// We should use an enqueued CSS file later for production.
?>
<style>
	.ska-switch { position: relative; display: inline-block; width: 40px; height: 20px; }
	.ska-switch input { opacity: 0; width: 0; height: 0; }
	.ska-slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; transition: .4s; border-radius: 20px; }
	.ska-slider:before { position: absolute; content: ""; height: 14px; width: 14px; left: 3px; bottom: 3px; background-color: white; transition: .4s; border-radius: 50%; }
	input:checked + .ska-slider { background-color: #10b981; }
	input:checked + .ska-slider:before { transform: translateX(20px); }
</style>
<div class="wrap ska-builder">
	<h1 class="wp-heading-inline"><?php esc_html_e( 'Ska Builder Dashboard', 'ska-builder-core' ); ?></h1>
	<hr class="wp-header-end">

	<div class="ska-dashboard-container" style="margin-top: 20px; display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">
		
		<!-- Main Content -->
		<div class="ska-main-panel">
			<div class="postbox">
				<h2 class="hndle"><span><?php esc_html_e( 'System Status', 'ska-builder-core' ); ?></span></h2>
				<div class="inside">
					<p><?php esc_html_e( 'Welcome to the Ska Core Builder administrative dashboard. The system is operating normally.', 'ska-builder-core' ); ?></p>
					
					<table class="widefat striped">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Module', 'ska-builder-core' ); ?></th>
								<th><?php esc_html_e( 'Status', 'ska-builder-core' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td><?php esc_html_e( 'Design Engine', 'ska-builder-core' ); ?></td>
								<td><span style="color: green; font-weight: bold;"><?php esc_html_e( 'Active', 'ska-builder-core' ); ?></span></td>
							</tr>
							<tr>
								<td><?php esc_html_e( 'Data Engine', 'ska-builder-core' ); ?></td>
								<td><span style="color: green; font-weight: bold;"><?php esc_html_e( 'Active', 'ska-builder-core' ); ?></span></td>
							</tr>
							<tr>
								<td><?php esc_html_e( 'Logic Engine', 'ska-builder-core' ); ?></td>
								<td><span style="color: green; font-weight: bold;"><?php esc_html_e( 'Active', 'ska-builder-core' ); ?></span></td>
							</tr>
							<tr>
								<td><?php esc_html_e( 'Conversion Bridge (Beta)', 'ska-builder-core' ); ?></td>
								<td>
									<?php $bridge_enabled = get_option( 'ska_bridge_enabled', 'yes' ); ?>
									<form method="post" action="">
										<?php wp_nonce_field( 'ska_save_dashboard_settings' ); ?>
										<label class="ska-switch">
											<input type="checkbox" name="ska_bridge_enabled" value="yes" <?php checked( $bridge_enabled, 'yes' ); ?> onchange="this.form.submit()">
											<span class="ska-slider round"></span>
										</label>
										<input type="hidden" name="ska_save_settings" value="1">
										<span style="margin-left: 10px; color: <?php echo $bridge_enabled === 'yes' ? 'green' : 'gray'; ?>; font-weight: bold;">
											<?php echo $bridge_enabled === 'yes' ? esc_html__( 'Active', 'ska-builder-core' ) : esc_html__( 'Disabled', 'ska-builder-core' ); ?>
										</span>
									</form>
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>

			<!-- Brand Colors -->
			<div class="postbox" style="margin-top: 20px;">
				<h2 class="hndle"><span><?php esc_html_e( 'Brand Colors (JIT Custom Registry)', 'ska-builder-core' ); ?></span></h2>
				<div class="inside">
					<p><?php esc_html_e( 'Định nghĩa brand colors để JIT Compiler tự động resolve các class như bg-{name}, text-{name}, border-{name}.', 'ska-builder-core' ); ?></p>
					<?php $custom_colors = get_option( 'ska_custom_colors', array() ); ?>
					<form method="post" action="">
						<?php wp_nonce_field( 'ska_save_custom_colors' ); ?>
						<table class="widefat striped" id="ska-colors-table">
							<thead>
								<tr>
									<th style="width: 30%;"><?php esc_html_e( 'Token Name', 'ska-builder-core' ); ?></th>
									<th style="width: 30%;"><?php esc_html_e( 'Hex Value', 'ska-builder-core' ); ?></th>
									<th style="width: 25%;"><?php esc_html_e( 'Preview', 'ska-builder-core' ); ?></th>
									<th style="width: 15%;"><?php esc_html_e( 'Action', 'ska-builder-core' ); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php if ( ! empty( $custom_colors ) ) : ?>
									<?php foreach ( $custom_colors as $name => $hex ) : ?>
									<tr>
										<td><input type="text" name="ska_color_names[]" value="<?php echo esc_attr( $name ); ?>" style="width:100%;" /></td>
										<td><input type="text" name="ska_color_values[]" value="<?php echo esc_attr( $hex ); ?>" style="width:100%;" /></td>
										<td><span style="display:inline-block; width:40px; height:24px; border-radius:4px; background:<?php echo esc_attr( $hex ); ?>; border:1px solid #ddd; vertical-align:middle;"></span> <code><?php echo esc_html( "bg-{$name}" ); ?></code></td>
										<td><button type="button" class="button button-link-delete ska-remove-row"><?php esc_html_e( 'Remove', 'ska-builder-core' ); ?></button></td>
									</tr>
									<?php endforeach; ?>
								<?php endif; ?>
								<tr id="ska-new-color-row">
									<td><input type="text" name="ska_color_names[]" placeholder="e.g. primary" style="width:100%;" /></td>
									<td><input type="text" name="ska_color_values[]" placeholder="e.g. #0d46f2" style="width:100%;" /></td>
									<td></td>
									<td></td>
								</tr>
							</tbody>
						</table>
						<p style="margin-top: 12px;">
							<button type="button" class="button" id="ska-add-color-row">+ <?php esc_html_e( 'Add Color', 'ska-builder-core' ); ?></button>
							<input type="submit" name="ska_save_colors" class="button button-primary" value="<?php esc_attr_e( 'Save Colors', 'ska-builder-core' ); ?>" />
						</p>
					</form>
				</div>
			</div>
		</div>

		<!-- Sidebar Content -->
		<div class="ska-sidebar-panel">
			<div class="postbox">
				<h2 class="hndle"><span><?php esc_html_e( 'Quick Links', 'ska-builder-core' ); ?></span></h2>
				<div class="inside">
					<ul>
						<li><a href="#" class="button button-secondary"><?php esc_html_e( 'Documentation', 'ska-builder-core' ); ?></a></li>
						<li style="margin-top: 10px;"><a href="#" class="button button-primary"><?php esc_html_e( 'Start Building', 'ska-builder-core' ); ?></a></li>
					</ul>
				</div>
			</div>
			
			<div class="postbox">
				<h2 class="hndle"><span><?php esc_html_e( 'License Info', 'ska-builder-core' ); ?></span></h2>
				<div class="inside">
					<p><?php esc_html_e( 'Status:', 'ska-builder-core' ); ?> <strong><?php esc_html_e( 'Development', 'ska-builder-core' ); ?></strong></p>
					<p><?php esc_html_e( 'Version:', 'ska-builder-core' ); ?> <code>1.0.0</code></p>
				</div>
			</div>
		</div>
	</div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
	document.getElementById('ska-add-color-row')?.addEventListener('click', function() {
		var tbody = document.querySelector('#ska-colors-table tbody');
		var row = document.createElement('tr');
		row.innerHTML = '<td><input type="text" name="ska_color_names[]" placeholder="e.g. secondary" style="width:100%;" /></td>' +
			'<td><input type="text" name="ska_color_values[]" placeholder="e.g. #10b981" style="width:100%;" /></td>' +
			'<td></td>' +
			'<td><button type="button" class="button button-link-delete ska-remove-row">Remove</button></td>';
		tbody.appendChild(row);
	});
	document.addEventListener('click', function(e) {
		if (e.target.classList.contains('ska-remove-row')) {
			e.target.closest('tr').remove();
		}
	});
});
</script>
