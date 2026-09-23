<?php
/**
 * Dashboard home.
 *
 * @package Vetra_Dashboard
 */

defined( 'ABSPATH' ) || exit;

$cards = array(
	array( 'label' => __( 'Open tickets', 'vetra-dashboard' ), 'value' => $stats['open_tickets'], 'icon' => 'ticket', 'type' => 'primary', 'url' => vtd_panel_url( array( 'vtd' => 'tickets', 'ticket_status' => 'open' ) ) ),
	array( 'label' => __( 'Unread notifications', 'vetra-dashboard' ), 'value' => $stats['unread_notifications'], 'icon' => 'bell', 'type' => 'accent', 'url' => vtd_panel_url( array( 'vtd' => 'notifications' ) ) ),
);
if ( ! empty( $stats['wallet_balance'] ) ) {
	$cards[] = array( 'label' => __( 'Wallet balance', 'vetra-dashboard' ), 'value' => $stats['wallet_balance'], 'icon' => 'wallet', 'type' => 'success', 'url' => vtd_panel_url( array( 'vtd' => 'wallet' ) ) );
}
$cards[] = array( 'label' => __( 'Total tickets', 'vetra-dashboard' ), 'value' => $stats['total_tickets'], 'icon' => 'ticket', 'type' => 'muted', 'url' => vtd_panel_url( array( 'vtd' => 'tickets' ) ) );
?>
<div class="vtd-dashboard">
	<section class="vtd-hero">
		<div>
			<h2><?php printf( esc_html__( 'Hi %s, welcome back', 'vetra-dashboard' ), esc_html( vtd_current_user_name( $user_id ) ) ); ?></h2>
			<p><?php esc_html_e( 'Here is a quick overview of your account.', 'vetra-dashboard' ); ?></p>
		</div>
		<a class="vtd-btn vtd-btn-primary" href="<?php echo esc_url( vtd_panel_url( array( 'vtd' => 'new-ticket' ) ) ); ?>">
			<?php echo vtd_icon( 'plus' ); // phpcs:ignore ?>
			<?php esc_html_e( 'New ticket', 'vetra-dashboard' ); ?>
		</a>
	</section>

	<div class="vtd-stats">
		<?php foreach ( $cards as $card ) : ?>
			<a class="vtd-stat vtd-stat-<?php echo esc_attr( $card['type'] ); ?>" href="<?php echo esc_url( $card['url'] ); ?>">
				<span class="vtd-stat-icon"><?php echo vtd_icon( $card['icon'] ); // phpcs:ignore ?></span>
				<span class="vtd-stat-value"><?php echo esc_html( $card['value'] ); ?></span>
				<span class="vtd-stat-label"><?php echo esc_html( $card['label'] ); ?></span>
			</a>
		<?php endforeach; ?>
	</div>

	<div class="vtd-grid-2">
		<section class="vtd-card">
			<h3><?php esc_html_e( 'Quick access', 'vetra-dashboard' ); ?></h3>
			<div class="vtd-shortcuts">
				<?php foreach ( $shortcuts as $shortcut ) : ?>
					<a class="vtd-shortcut" href="<?php echo esc_url( $shortcut['url'] ); ?>">
						<span><?php echo vtd_icon( $shortcut['icon'] ); // phpcs:ignore ?></span>
						<?php echo esc_html( $shortcut['label'] ); ?>
					</a>
				<?php endforeach; ?>
			</div>
		</section>

		<section class="vtd-card">
			<h3><?php esc_html_e( 'Account summary', 'vetra-dashboard' ); ?></h3>
			<ul class="vtd-summary">
				<li><span><?php esc_html_e( 'Comments', 'vetra-dashboard' ); ?></span><strong><?php echo (int) $stats['comments']; ?></strong></li>
				<li><span><?php esc_html_e( 'Active polls', 'vetra-dashboard' ); ?></span><strong><?php echo (int) $stats['polls']; ?></strong></li>
				<li><span><?php esc_html_e( 'Open tickets', 'vetra-dashboard' ); ?></span><strong><?php echo (int) $stats['open_tickets']; ?></strong></li>
			</ul>
		</section>
	</div>
</div>
