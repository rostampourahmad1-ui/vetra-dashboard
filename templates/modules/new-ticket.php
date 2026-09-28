<?php
/**
 * New ticket form.
 *
 * @package Vetra_Dashboard
 */

defined( 'ABSPATH' ) || exit;

$faq_enabled = $faq_enabled ?? VTD_Options::get( 'ticket_faq_enabled', 1 );
$faq_content = $faq_content ?? VTD_Options::get( 'ticket_faq_content', '' );
$faq_gate = $faq_gate ?? VTD_Options::get( 'ticket_faq_gate', 1 );
$faq_intro = $faq_intro ?? VTD_Options::get( 'ticket_faq_intro', '' );
$faq_items = $faq_items ?? (array) VTD_Options::get( 'ticket_faq_items', array() );
$faq_links = $faq_links ?? (array) VTD_Options::get( 'ticket_faq_links', array() );
$faq_required = $faq_enabled && $faq_gate && ( $faq_content || $faq_items || $faq_links );
?>
<?php echo VTD_Tickets::notices_html(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
<?php if ( $faq_required ) : ?>
<div class="vtd-card vtd-ticket-faq" data-vtd-faq-step>
	<h3><?php esc_html_e( 'Before submitting a ticket', 'vetra-dashboard' ); ?></h3>
	<?php if ( $faq_intro ) : ?><p class="vtd-muted"><?php echo esc_html( $faq_intro ); ?></p><?php endif; ?>
	<?php if ( $faq_content ) : ?><div class="vtd-faq-content"><?php echo wp_kses_post( $faq_content ); ?></div><?php endif; ?>
	<?php if ( $faq_items ) : ?>
		<div class="vtd-faq-list">
			<?php foreach ( $faq_items as $faq_item ) : ?>
				<?php $faq_item = wp_parse_args( (array) $faq_item, array( 'question' => '', 'answer' => '', 'url' => '' ) ); ?>
				<?php if ( $faq_item['question'] || $faq_item['answer'] ) : ?>
					<details><summary><?php echo esc_html( $faq_item['question'] ); ?></summary><p><?php echo esc_html( $faq_item['answer'] ); ?></p>
					<?php if ( $faq_item['url'] ) : ?><a class="vtd-link" href="<?php echo esc_url( $faq_item['url'] ); ?>" target="_blank" rel="noopener">مطالعه راهنما</a><?php endif; ?></details>
				<?php endif; ?>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
	<?php if ( $faq_links ) : ?><nav class="vtd-faq-links" aria-label="لینک‌های آموزشی">
		<?php foreach ( $faq_links as $faq_link ) : ?>
			<?php $faq_link = wp_parse_args( (array) $faq_link, array( 'label' => '', 'url' => '' ) ); ?>
			<?php if ( $faq_link['label'] && $faq_link['url'] ) : ?><a class="vtd-btn vtd-btn-outline" href="<?php echo esc_url( $faq_link['url'] ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $faq_link['label'] ); ?></a><?php endif; ?>
		<?php endforeach; ?>
	</nav><?php endif; ?>
	<div class="vtd-faq-actions">
		<label class="vtd-checkbox-label">
			<input type="checkbox" data-vtd-faq-confirm>
			<span>مطالعه کردم و مشکل من در موارد بالا ذکر نشده است.</span>
		</label>
		<button type="button" class="vtd-btn vtd-btn-primary" data-vtd-faq-continue disabled>متوجه شدم؛ ادامه ثبت تیکت</button>
	</div>
</div>
<?php endif; ?>
<div class="vtd-card">
	<h3><?php esc_html_e( 'Submit a new ticket', 'vetra-dashboard' ); ?></h3>
	<form class="vtd-form" method="post" enctype="multipart/form-data" data-vtd-ticket-form>
		<input type="hidden" name="vtd_ticket_action" value="create">
		<?php if ( $faq_required ) : ?>
			<label class="vtd-checkbox-label vtd-faq-fallback-confirm" data-vtd-faq-fallback>
				<input type="checkbox" name="faq_confirmed" value="1" required>
				<span>مطالعه کردم و مشکل من در موارد بالا ذکر نشده است.</span>
			</label>
		<?php else : ?>
			<input type="hidden" name="faq_confirmed" value="1">
		<?php endif; ?>
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
