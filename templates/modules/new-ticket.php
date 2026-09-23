<?php
/**
 * New ticket form.
 *
 * @package Vetra_Dashboard
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="vtd-card">
	<h3><?php esc_html_e( 'Submit a new ticket', 'vetra-dashboard' ); ?></h3>
	<form class="vtd-form" method="post" enctype="multipart/form-data" data-vtd-ticket-form>
		<?php // File field rendered below. ?>
		<input type="hidden" name="vtd_ticket_action" value="create">
		<?php wp_nonce_field( 'vtd_ticket', 'vtd_ticket_nonce' ); ?>

		<label class="vtd-field">
			<span><?php esc_html_e( 'Subject', 'vetra-dashboard' ); ?></span>
			<input type="text" name="title" required>
		</label>

		<div class="vtd-form-grid">
			<label class="vtd-field">
				<span><?php esc_html_e( 'Department', 'vetra-dashboard' ); ?></span>
				<select name="department_id" required>
					<option value=""><?php esc_html_e( 'Select department', 'vetra-dashboard' ); ?></option>
					<?php foreach ( $departments as $department ) : ?>
						<option value="<?php echo (int) $department->department_id; ?>"><?php echo esc_html( $department->department_name ); ?></option>
					<?php endforeach; ?>
				</select>
			</label>
			<label class="vtd-field">
				<span><?php esc_html_e( 'Priority', 'vetra-dashboard' ); ?></span>
				<select name="priority">
					<?php foreach ( $priorities as $key => $label ) : ?>
						<option value="<?php echo esc_attr( $key ); ?>" <?php selected( 'medium', $key ); ?>><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select>
			</label>
		</div>

		<label class="vtd-field">
			<span><?php esc_html_e( 'Message', 'vetra-dashboard' ); ?></span>
			<textarea name="content" rows="7" required></textarea>
		</label>

		<?php if ( VTD_Options::get( 'ticket_attachments', 1 ) ) : ?>
			<label class="vtd-field">
				<span><?php esc_html_e( 'Attachments', 'vetra-dashboard' ); ?></span>
				<input type="file" name="ticket_files[]" multiple accept=".jpg,.jpeg,.png,.pdf,.zip,.doc,.docx">
			</label>
		<?php endif; ?>

		<div class="vtd-form-actions">
			<button type="submit" class="vtd-btn vtd-btn-primary"><?php esc_html_e( 'Submit ticket', 'vetra-dashboard' ); ?></button>
			<a class="vtd-btn vtd-btn-link" href="<?php echo esc_url( vtd_panel_url( array( 'vtd' => 'tickets' ) ) ); ?>"><?php esc_html_e( 'Cancel', 'vetra-dashboard' ); ?></a>
		</div>
	</form>
</div>
