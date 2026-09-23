<?php
/**
 * Staff ticket center.
 *
 * @package Vetra_Dashboard
 */

defined( 'ABSPATH' ) || exit;

$vtd_statuses = VTD_Tickets::statuses();
?>
<div class="vtd-staff-tickets">
	<section class="vtd-card">
		<form class="vtd-filter-form" method="get">
			<input type="hidden" name="vtd" value="support-center">
			<select name="department">
				<option value="0"><?php esc_html_e( 'All departments', 'vetra-dashboard' ); ?></option>
				<?php foreach ( $departments as $vtd_department ) : ?>
					<option value="<?php echo (int) $vtd_department->department_id; ?>" <?php selected( (int) $department, (int) $vtd_department->department_id ); ?>>
						<?php echo esc_html( $vtd_department->department_name ); ?>
					</option>
				<?php endforeach; ?>
			</select>
			<select name="ticket_status">
				<option value=""><?php esc_html_e( 'All statuses', 'vetra-dashboard' ); ?></option>
				<?php foreach ( $vtd_statuses as $vtd_key => $vtd_label ) : ?>
					<option value="<?php echo esc_attr( $vtd_key ); ?>" <?php selected( $status, $vtd_key ); ?>><?php echo esc_html( $vtd_label ); ?></option>
				<?php endforeach; ?>
			</select>
			<input type="search" name="ticket_search" value="<?php echo esc_attr( $search ); ?>" placeholder="<?php esc_attr_e( 'Ticket ID or title', 'vetra-dashboard' ); ?>">
			<button type="submit" class="vtd-btn vtd-btn-primary"><?php esc_html_e( 'Filter', 'vetra-dashboard' ); ?></button>
		</form>
		<div class="vtd-summary-chips">
			<span class="vtd-chip"><?php esc_html_e( 'All', 'vetra-dashboard' ); ?>: <strong><?php echo (int) ( $counts['all'] ?? 0 ); ?></strong></span>
			<?php foreach ( $vtd_statuses as $vtd_key => $vtd_label ) : ?>
				<span class="vtd-chip"><?php echo esc_html( $vtd_label ); ?>: <strong><?php echo (int) ( $counts[ $vtd_key ] ?? 0 ); ?></strong></span>
			<?php endforeach; ?>
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
						<th><?php esc_html_e( 'User', 'vetra-dashboard' ); ?></th>
						<th><?php esc_html_e( 'Department', 'vetra-dashboard' ); ?></th>
						<th><?php esc_html_e( 'Priority', 'vetra-dashboard' ); ?></th>
						<th><?php esc_html_e( 'Status', 'vetra-dashboard' ); ?></th>
						<th><?php esc_html_e( 'Updated', 'vetra-dashboard' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $items as $ticket ) : ?>
						<tr>
							<td>
								<a class="vtd-ticket-title" href="<?php echo esc_url( vtd_panel_url( array( 'vtd' => 'ticket', 'ticket' => $ticket->ticket_id ) ) ); ?>">
									<?php if ( $ticket->starred ) : ?><span class="vtd-star">★</span><?php endif; ?>
									<?php echo esc_html( $ticket->ticket_title ); ?>
								</a>
								<span class="vtd-muted">#<?php echo (int) $ticket->ticket_id; ?></span>
							</td>
							<td><?php echo esc_html( vtd_current_user_name( $ticket->user_id ) ); ?></td>
							<td><?php echo esc_html( VTD_Tickets::department_name( $ticket->department_id ) ); ?></td>
							<td><span class="vtd-pill vtd-priority-<?php echo esc_attr( $ticket->priority ); ?>"><?php echo esc_html( VTD_Tickets::priority_label( $ticket->priority ) ); ?></span></td>
							<td><span class="vtd-pill vtd-status-<?php echo esc_attr( $ticket->status ); ?>"><?php echo esc_html( VTD_Tickets::status_label( $ticket->status ) ); ?></span></td>
							<td><?php echo esc_html( vtd_time_ago( $ticket->updated_at ) ); ?></td>
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
						'base'    => add_query_arg( 'tpage', '%#%' ),
						'format'  => '',
						'current' => max( 1, $paged ),
						'total'   => $vtd_pages,
					)
				);
				?>
			</div>
		<?php endif; ?>
	<?php endif; ?>
</div>
