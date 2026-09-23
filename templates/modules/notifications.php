<?php
/**
 * Notifications list.
 *
 * @package Vetra_Dashboard
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="vtd-notifications-page">
	<?php if ( empty( $items ) ) : ?>
		<?php echo VTD_Templates::module( 'alert', array( 'message' => __( 'No notifications yet.', 'vetra-dashboard' ), 'type' => 'info' ) ); // phpcs:ignore ?>
	<?php else : ?>
		<div class="vtd-notify-feed">
			<?php foreach ( $items as $item ) : ?>
				<article class="vtd-notify-item <?php echo empty( $item->read_at ) ? 'is-unread' : ''; ?>">
					<span class="vtd-notify-icon"><?php echo vtd_icon( 'bell' ); // phpcs:ignore ?></span>
					<div>
						<h4><?php echo esc_html( $item->title ); ?></h4>
						<div class="vtd-notify-body"><?php echo wp_kses_post( $item->content ); ?></div>
						<div class="vtd-notify-meta">
							<time><?php echo esc_html( vtd_time_ago( $item->created_at ) ); ?></time>
							<?php if ( ! empty( $item->link ) ) : ?>
								<a class="vtd-link" href="<?php echo esc_url( $item->link ); ?>"><?php esc_html_e( 'View', 'vetra-dashboard' ); ?></a>
							<?php endif; ?>
						</div>
					</div>
				</article>
			<?php endforeach; ?>
		</div>
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
