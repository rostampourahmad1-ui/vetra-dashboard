<?php
/**
 * Tickets list.
 *
 * @package Vetra_Dashboard
 */

defined( 'ABSPATH' ) || exit;

$vtd_statuses     = VTD_Tickets::statuses();
$vtd_priorities   = VTD_Tickets::priorities();
$vtd_cancel_enabled = VTD_Options::get( 'ticket_cancel_enabled', 1 );
$vtd_rating_enabled = VTD_Options::get( 'ticket_rating', 1 );
?>
<div class="vtd-tickets">
	<section class="vtd-toolbar">
		<div class="vtd-toolbar-left">
			<a class="vtd-btn vtd-btn-primary" href="<?php echo esc_url( vtd_panel_url( array( 'vtd' => 'new-ticket' ) ) ); ?>">
				<?php echo vtd_icon( 'plus' ); // phpcs:ignore ?>
				<?php esc_html_e( 'New ticket', 'vetra-dashboard' ); ?>
			</a>
		</div>
		<div class="vtd-toolbar-right">
			<?php foreach ( $vtd_statuses as $vtd_key => $vtd_label ) : ?>
				<a class="vtd-filter <?php echo $status === $vtd_key ? 'is-active' : ''; ?>"
					href="<?php echo esc_url( vtd_panel_url( array( 'vtd' => 'tickets', 'ticket_status' => $vtd_key ) ) ); ?>">
					<?php echo esc_html( $vtd_label ); ?>
					<span><?php echo (int) ( $counts[ $vtd_key ] ?? 0 ); ?></span>
				</a>
			<?php endforeach; ?>
			<a class="vtd-filter <?php echo '' === $status ? 'is-active' : ''; ?>" href="<?php echo esc_url( vtd_panel_url( array( 'vtd' => 'tickets' ) ) ); ?>">
				<?php esc_html_e( 'All', 'vetra-dashboard' ); ?>
			</a>
		</div>
	</section>

	<?php if ( empty( $items ) ) : ?>
		<?php echo VTD_Templates::module( 'alert', array( 'message' => __( 'No tickets found.', 'vetra-dashboard' ), 'type' => 'info' ) ); // phpcs:ignore ?>
	<?php else : ?>
		<section class="vtd-card vtd-table">
			<table>
				<thead>
					<tr>
						<th><?php esc_html_e( 'Ticket', 'vetra-dashboard' ); ?></th>
						<th><?php esc_html_e( 'Department', 'vetra-dashboard' ); ?></th>
						<th><?php esc_html_e( 'Priority', 'vetra-dashboard' ); ?></th>
						<th><?php esc_html_e( 'Status', 'vetra-dashboard' ); ?></th>
						<th><?php esc_html_e( 'Updated', 'vetra-dashboard' ); ?></th>
						<th><?php esc_html_e( 'Actions', 'vetra-dashboard' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $items as $ticket ) : ?>
						<tr>
							<td>
								<a class="vtd-ticket-title" href="<?php echo esc_url( vtd_panel_url( array( 'vtd' => 'ticket', 'ticket' => $ticket->ticket_id ) ) ); ?>">
									<?php echo esc_html( $ticket->ticket_title ); ?>
								</a>
								<span class="vtd-muted">#<?php echo (int) $ticket->ticket_id; ?></span>
							</td>
							<td><?php echo esc_html( VTD_Tickets::department_name( $ticket->department_id ) ); ?></td>
							<td><span class="vtd-pill vtd-priority-<?php echo esc_attr( $ticket->priority ); ?>"><?php echo esc_html( VTD_Tickets::priority_label( $ticket->priority ) ); ?></span></td>
							<td><span class="vtd-pill vtd-status-<?php echo esc_attr( $ticket->status ); ?>"><?php echo esc_html( VTD_Tickets::status_label( $ticket->status ) ); ?></span></td>
							<td><?php echo esc_html( vtd_time_ago( $ticket->updated_at ) ); ?></td>
							<td class="vtd-ticket-row-actions">
								<a class="vtd-btn vtd-btn-sm vtd-btn-outline" href="<?php echo esc_url( vtd_panel_url( array( 'vtd' => 'ticket', 'ticket' => $ticket->ticket_id ) ) ); ?>"><?php esc_html_e( 'View', 'vetra-dashboard' ); ?></a>
								<?php if ( $vtd_cancel_enabled && 'closed' !== $ticket->status ) : ?>
									<form method="post" style="display:inline" onsubmit="return confirm('<?php esc_attr_e( 'Cancel this ticket?', 'vetra-dashboard' ); ?>')">
										<?php wp_nonce_field( 'vtd_ticket', 'vtd_ticket_nonce' ); ?>
										<input type="hidden" name="vtd_ticket_action" value="cancel">
										<input type="hidden" name="ticket_id" value="<?php echo (int) $ticket->ticket_id; ?>">
										<button type="submit" class="vtd-btn vtd-btn-sm vtd-btn-danger"><?php esc_html_e( 'Cancel', 'vetra-dashboard' ); ?></button>
									</form>
								<?php endif; ?>
								<?php
								if ( $vtd_rating_enabled && 'closed' === $ticket->status ) :
									$vtd_rating = VTD_Tickets::rating( $ticket->ticket_id );
									if ( $vtd_rating ) :
								?>
									<span class="vtd-stars-mini" title="<?php echo esc_attr( $vtd_rating->score . '/5' ); ?>">
										<?php for ( $vtd_r = 1; $vtd_r <= 5; $vtd_r++ ) : ?>
											<span class="<?php echo $vtd_r <= (int) $vtd_rating->score ? 'is-active' : ''; ?>">★</span>
										<?php endfor; ?>
									</span>
								<?php
									endif;
								endif;
								?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</section>
		<?php
		$vtd_pages = (int) ceil( $total / max( 1, $per_page ) );
		if ( $vtd_pages > 1 ) :
			?>
			<div class="vtd-pagination">
				<?php
				echo paginate_links(
					array(
						'base'      => add_query_arg( 'tpage', '%#%' ),
						'format'    => '',
						'current'   => max( 1, $paged ),
						'total'     => $vtd_pages,
						'prev_text' => '&laquo;',
						'next_text' => '&raquo;',
					)
				);
				?>
			</div>
		<?php endif; ?>
	<?php endif; ?>
</div>
