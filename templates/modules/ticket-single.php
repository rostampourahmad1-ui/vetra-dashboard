<?php
/**
 * Single ticket conversation.
 *
 * @package Vetra_Dashboard
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="vtd-ticket-single" data-vtd-ticket="<?php echo (int) $ticket->ticket_id; ?>">
	<section class="vtd-card vtd-ticket-header">
		<div>
			<h3><?php echo esc_html( $ticket->ticket_title ); ?> <span class="vtd-muted">#<?php echo (int) $ticket->ticket_id; ?></span></h3>
			<div class="vtd-chips">
				<span class="vtd-pill vtd-status-<?php echo esc_attr( $ticket->status ); ?>"><?php echo esc_html( VTD_Tickets::status_label( $ticket->status ) ); ?></span>
				<span class="vtd-pill vtd-priority-<?php echo esc_attr( $ticket->priority ); ?>"><?php echo esc_html( VTD_Tickets::priority_label( $ticket->priority ) ); ?></span>
				<span class="vtd-chip"><?php echo esc_html( VTD_Tickets::department_name( $ticket->department_id ) ); ?></span>
			</div>
		</div>
		<div class="vtd-ticket-actions">
			<?php if ( $is_staff ) : ?>
				<button type="button" class="vtd-btn vtd-btn-outline" data-vtd-ticket-star><?php echo vtd_icon( 'star' ); // phpcs:ignore ?> <?php esc_html_e( 'Star', 'vetra-dashboard' ); ?></button>
			<?php endif; ?>
			<?php if ( 'closed' !== $ticket->status ) : ?>
				<button type="button" class="vtd-btn vtd-btn-danger" data-vtd-ticket-close><?php esc_html_e( 'Close ticket', 'vetra-dashboard' ); ?></button>
			<?php endif; ?>
		</div>
	</section>

	<section class="vtd-card vtd-conversation">
		<article class="vtd-message vtd-message-user">
			<header>
				<strong><?php echo esc_html( vtd_current_user_name( $ticket->user_id ) ); ?></strong>
				<time><?php echo esc_html( vtd_date_i18n( $ticket->created_at ) ); ?></time>
			</header>
			<div class="vtd-message-body"><?php echo wp_kses_post( wpautop( $ticket->ticket_content ) ); ?></div>
			<?php echo VTD_Tickets::attachments_html( $ticket->attachments ); // phpcs:ignore ?>
		</article>

		<?php foreach ( $replies as $reply ) : ?>
			<article class="vtd-message <?php echo $reply->is_staff ? 'vtd-message-staff' : 'vtd-message-user'; ?>">
				<header>
					<strong>
						<?php echo esc_html( $reply->is_staff ? __( 'Support', 'vetra-dashboard' ) : vtd_current_user_name( $reply->user_id ) ); ?>
						<?php if ( $reply->is_internal ) : ?>
							<span class="vtd-pill"><?php esc_html_e( 'Internal note', 'vetra-dashboard' ); ?></span>
						<?php endif; ?>
					</strong>
					<time><?php echo esc_html( vtd_date_i18n( $reply->created_at ) ); ?></time>
				</header>
				<div class="vtd-message-body"><?php echo wp_kses_post( wpautop( $reply->content ) ); ?></div>
				<?php echo VTD_Tickets::attachments_html( $reply->attachments ); // phpcs:ignore ?>
			</article>
		<?php endforeach; ?>
	</section>

	<?php if ( 'closed' !== $ticket->status ) : ?>
		<section class="vtd-card">
			<h3><?php esc_html_e( 'Reply', 'vetra-dashboard' ); ?></h3>
			<form class="vtd-form" method="post" enctype="multipart/form-data">
				<input type="hidden" name="vtd_ticket_action" value="reply">
				<input type="hidden" name="ticket_id" value="<?php echo (int) $ticket->ticket_id; ?>">
				<?php wp_nonce_field( 'vtd_ticket', 'vtd_ticket_nonce' ); ?>
				<label class="vtd-field">
					<textarea name="content" rows="5" required placeholder="<?php esc_attr_e( 'Write your reply...', 'vetra-dashboard' ); ?>"></textarea>
				</label>
				<?php if ( VTD_Options::get( 'ticket_attachments', 1 ) ) : ?>
					<label class="vtd-field">
						<span><?php esc_html_e( 'Attachments', 'vetra-dashboard' ); ?></span>
						<input type="file" name="ticket_files[]" multiple accept=".jpg,.jpeg,.png,.pdf,.zip,.doc,.docx">
					</label>
				<?php endif; ?>
				<div class="vtd-form-actions">
					<button type="submit" class="vtd-btn vtd-btn-primary"><?php esc_html_e( 'Send reply', 'vetra-dashboard' ); ?></button>
				</div>
			</form>
		</section>
	<?php endif; ?>

	<?php if ( VTD_Options::get( 'ticket_rating', 1 ) && ! $is_staff && 'closed' === $ticket->status && ! $rating ) : ?>
		<section class="vtd-card">
			<h3><?php esc_html_e( 'How was the support?', 'vetra-dashboard' ); ?></h3>
			<form class="vtd-form" data-vtd-ticket-rate>
				<div class="vtd-stars" data-vtd-stars>
					<?php for ( $i = 1; $i <= 5; $i++ ) : ?>
						<button type="button" data-value="<?php echo (int) $i; ?>"><?php echo vtd_icon( 'star' ); // phpcs:ignore ?></button>
					<?php endfor; ?>
					<input type="hidden" name="score" value="5">
				</div>
				<label class="vtd-field">
					<textarea name="feedback" rows="3" placeholder="<?php esc_attr_e( 'Your feedback...', 'vetra-dashboard' ); ?>"></textarea>
				</label>
				<button type="submit" class="vtd-btn vtd-btn-primary"><?php esc_html_e( 'Submit rating', 'vetra-dashboard' ); ?></button>
			</form>
		</section>
	<?php endif; ?>
</div>
