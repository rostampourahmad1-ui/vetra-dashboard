<?php
/**
 * New ticket form.
 *
 * @package Vetra_Dashboard
 */

defined( 'ABSPATH' ) || exit;

$faq_enabled = $faq_enabled ?? VTD_Options::get( 'ticket_faq_enabled', 1 );
$faq_content = $faq_content ?? VTD_Options::get( 'ticket_faq_content', '' );
?>
<?php if ( $faq_enabled && $faq_content ) : ?>
<div class="vtd-card vtd-ticket-faq" data-vtd-faq-step>
	<h3><?php esc_html_e( 'Before submitting a ticket', 'vetra-dashboard' ); ?></h3>
	<div class="vtd-faq-content">
		<?php echo wp_kses_post( $faq_content ); ?>
	</div>
	<div class="vtd-faq-actions">
		<label class="vtd-checkbox-label">
			<input type="checkbox" data-vtd-faq-confirm>
			<span><?php esc_html_e( 'I have read the FAQ and tutorial, and I understand.', 'vetra-dashboard' ); ?></span>
		</label>
		<button type="button" class="vtd-btn vtd-btn-primary" data-vtd-faq-continue disabled><?php esc_html_e( 'Continue', 'vetra-dashboard' ); ?></button>
	</div>
</div>
<script>
(function(){
	var faqStep = document.querySelector('[data-vtd-faq-step]');
	if (!faqStep) return;
	var confirm = faqStep.querySelector('[data-vtd-faq-confirm]');
	var continueBtn = faqStep.querySelector('[data-vtd-faq-continue]');
	var formCard = faqStep.nextElementSibling;
	if (formCard) formCard.style.display = 'none';
	confirm.addEventListener('change', function(){
		continueBtn.disabled = !confirm.checked;
	});
	continueBtn.addEventListener('click', function(){
		if (confirm.checked) {
			faqStep.style.display = 'none';
			if (formCard) formCard.style.display = '';
		}
	});
})();
</script>
<?php endif; ?>
<div class="vtd-card">
	<h3><?php esc_html_e( 'Submit a new ticket', 'vetra-dashboard' ); ?></h3>
	<form class="vtd-form" method="post" enctype="multipart/form-data" data-vtd-ticket-form>
		<input type="hidden" name="vtd_ticket_action" value="create">
		<?php wp_nonce_field( 'vtd_ticket', 'vtd_ticket_nonce' ); ?>

		<label class="vtd-field">
			<span><?php esc_html_e( 'Subject', 'vetra-dashboard' ); ?></span>
			<input type="text" name="title" required>
		</label>

		<div class="vtd-form-grid">
			<?php if ( ! empty( $departments ) ) : ?>
			<label class="vtd-field">
				<span><?php esc_html_e( 'Department', 'vetra-dashboard' ); ?></span>
				<select name="department_id" required>
					<option value=""><?php esc_html_e( 'Select department', 'vetra-dashboard' ); ?></option>
					<?php foreach ( $departments as $department ) : ?>
						<option value="<?php echo (int) $department->department_id; ?>"><?php echo esc_html( $department->department_name ); ?></option>
					<?php endforeach; ?>
				</select>
			</label>
			<?php endif; ?>
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
