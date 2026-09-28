<?php
/**
 * Dashboard home.
 *
 * @package Vetra_Dashboard
 */

defined( 'ABSPATH' ) || exit;

// Resolve dashboard widget visibility settings.
$vtd_widgets = VTD_Options::get( 'dashboard_widgets', array() );
$vtd_widget_enabled = array();
if ( is_array( $vtd_widgets ) ) {
	foreach ( $vtd_widgets as $w ) {
		if ( ! empty( $w['slug'] ) ) {
			$vtd_widget_enabled[ $w['slug'] ] = ! empty( $w['enabled'] );
		}
	}
}
// Default all to enabled if not set
$vtd_show_widget = function( $slug ) use ( $vtd_widget_enabled ) {
	return ! isset( $vtd_widget_enabled[ $slug ] ) || $vtd_widget_enabled[ $slug ];
};

	$metric_defaults = array(
	'open_tickets' => array( 'label' => __( 'Open tickets', 'vetra-dashboard' ), 'icon' => 'ticket', 'tone' => 'primary', 'url' => vtd_panel_url( array( 'vtd' => 'tickets', 'ticket_status' => 'open' ) ), 'widget' => 'open_tickets', 'module' => 'tickets' ),
	'unread_notifications' => array( 'label' => __( 'Unread notifications', 'vetra-dashboard' ), 'icon' => 'bell', 'tone' => 'accent', 'url' => vtd_panel_url( array( 'vtd' => 'notifications' ) ), 'widget' => '', 'module' => 'notifications' ),
	'wallet_balance' => array( 'label' => __( 'Wallet balance', 'vetra-dashboard' ), 'icon' => 'wallet', 'tone' => 'success', 'url' => vtd_panel_url( array( 'vtd' => 'wallet' ) ), 'widget' => 'wallet_balance', 'module' => 'wallet' ),
	'total_tickets' => array( 'label' => __( 'Total tickets', 'vetra-dashboard' ), 'icon' => 'ticket', 'tone' => 'muted', 'url' => vtd_panel_url( array( 'vtd' => 'tickets' ) ), 'widget' => 'total_tickets', 'module' => 'tickets' ),
	'comments' => array( 'label' => __( 'Comments', 'vetra-dashboard' ), 'icon' => 'comment', 'tone' => 'primary', 'url' => vtd_panel_url( array( 'vtd' => 'comments' ) ), 'widget' => '', 'module' => 'comments' ),
	'polls' => array( 'label' => __( 'Active polls', 'vetra-dashboard' ), 'icon' => 'poll', 'tone' => 'accent', 'url' => vtd_panel_url( array( 'vtd' => 'polls' ) ), 'widget' => '', 'module' => 'polls' ),
);
$cards = array();
foreach ( (array) VTD_Options::get( 'dashboard_cards', array() ) as $card_setting ) {
	$card_setting = wp_parse_args( (array) $card_setting, array( 'metric' => '', 'label' => '', 'value' => '', 'icon' => '', 'tone' => 'primary', 'url' => '', 'enabled' => 1 ) );
	$metric = sanitize_key( $card_setting['metric'] );
	if ( empty( $card_setting['enabled'] ) || ( isset( $metric_defaults[ $metric ]['widget'] ) && $metric_defaults[ $metric ]['widget'] && ! $vtd_show_widget( $metric_defaults[ $metric ]['widget'] ) ) || ( isset( $metric_defaults[ $metric ]['module'] ) && ! VTD_Modules::enabled( $metric_defaults[ $metric ]['module'] ) ) ) {
		continue;
	}
	$defaults = $metric_defaults[ $metric ] ?? array( 'label' => '', 'icon' => 'default', 'tone' => 'primary', 'url' => '', 'widget' => '' );
	$value = 'custom' === $metric ? $card_setting['value'] : ( $stats[ $metric ] ?? '' );
	if ( '' === (string) $value || ( 'wallet_balance' === $metric && empty( $value ) ) ) {
		continue;
	}
	$url = $card_setting['url'] ? $card_setting['url'] : $defaults['url'];
	$cards[] = array(
		'label' => $card_setting['label'] ? $card_setting['label'] : $defaults['label'],
		'value' => $value,
		'icon'  => $card_setting['icon'] ? $card_setting['icon'] : $defaults['icon'],
		'type'  => in_array( $card_setting['tone'], array( 'primary', 'accent', 'success', 'muted' ), true ) ? $card_setting['tone'] : $defaults['tone'],
		'url'   => $url,
	);
}
$vtd_layout = sanitize_key( VTD_Options::get( 'dashboard_layout', 'comfortable' ) );
$vtd_columns = max( 2, min( 6, (int) VTD_Options::get( 'dashboard_columns', 4 ) ) );
?>
<div class="vtd-dashboard vtd-dashboard-layout-<?php echo esc_attr( $vtd_layout ); ?>" style="--vtd-dashboard-columns:<?php echo (int) $vtd_columns; ?>">
	<?php if ( VTD_Options::get( 'dashboard_welcome', 1 ) ) : ?>
	<section class="vtd-hero">
		<div>
			<h2><?php printf( esc_html__( 'Hi %s, welcome back', 'vetra-dashboard' ), esc_html( vtd_current_user_name( $user_id ) ) ); ?></h2>
			<p><?php esc_html_e( 'Here is a quick overview of your account.', 'vetra-dashboard' ); ?></p>
		</div>
		<?php if ( VTD_Modules::enabled( 'tickets' ) ) : ?><a class="vtd-btn vtd-btn-primary" href="<?php echo esc_url( vtd_panel_url( array( 'vtd' => 'new-ticket' ) ) ); ?>">
			<?php echo vtd_icon( 'plus' ); // phpcs:ignore ?>
			<?php esc_html_e( 'New ticket', 'vetra-dashboard' ); ?>
		</a><?php endif; ?>
	</section>
	<?php endif; ?>

	<?php if ( VTD_Options::get( 'dashboard_show_stats', 1 ) && ! empty( $cards ) ) : ?>
	<div class="vtd-stats">
		<?php foreach ( $cards as $card ) : ?>
			<?php if ( $card['url'] ) : ?><a class="vtd-stat vtd-stat-<?php echo esc_attr( $card['type'] ); ?>" href="<?php echo esc_url( $card['url'] ); ?>"><?php else : ?><div class="vtd-stat vtd-stat-<?php echo esc_attr( $card['type'] ); ?>"><?php endif; ?>
				<span class="vtd-stat-icon"><?php echo vtd_icon( $card['icon'] ); // phpcs:ignore ?></span>
				<span class="vtd-stat-value"><?php echo esc_html( $card['value'] ); ?></span>
				<span class="vtd-stat-label"><?php echo esc_html( $card['label'] ); ?></span>
			<?php if ( $card['url'] ) : ?></a><?php else : ?></div><?php endif; ?>
		<?php endforeach; ?>
	</div>
	<?php endif; ?>

	<div class="vtd-grid-2 vtd-dashboard-blocks">
		<?php if ( $vtd_show_widget( 'quick_access' ) ) : ?>
		<section class="vtd-card">
			<h3><?php esc_html_e( 'Quick access', 'vetra-dashboard' ); ?></h3>
			<div class="vtd-shortcuts">
				<?php foreach ( $shortcuts as $shortcut ) : ?>
					<a class="vtd-shortcut" href="<?php echo esc_url( $shortcut['url'] ); ?>">
						<span><?php echo vtd_icon( $shortcut['icon'] ); // phpcs:ignore ?></span>
						<?php echo esc_html( $shortcut['label'] ); ?>
					</a>
				<?php endforeach; ?>
				<?php if ( empty( $shortcuts ) ) : ?><p class="vtd-muted">میانبری برای نمایش تنظیم نشده است.</p><?php endif; ?>
			</div>
		</section>
		<?php endif; ?>

		<?php if ( $vtd_show_widget( 'account_summary' ) ) : ?>
		<section class="vtd-card">
			<h3><?php esc_html_e( 'Account summary', 'vetra-dashboard' ); ?></h3>
			<ul class="vtd-summary">
				<?php if ( VTD_Modules::enabled( 'comments' ) ) : ?><li><span><?php esc_html_e( 'Comments', 'vetra-dashboard' ); ?></span><strong><?php echo (int) $stats['comments']; ?></strong></li><?php endif; ?>
				<?php if ( VTD_Modules::enabled( 'polls' ) ) : ?><li><span><?php esc_html_e( 'Active polls', 'vetra-dashboard' ); ?></span><strong><?php echo (int) $stats['polls']; ?></strong></li><?php endif; ?>
				<?php if ( VTD_Modules::enabled( 'tickets' ) ) : ?><li><span><?php esc_html_e( 'Open tickets', 'vetra-dashboard' ); ?></span><strong><?php echo (int) $stats['open_tickets']; ?></strong></li><?php endif; ?>
			</ul>
		</section>
		<?php endif; ?>
	</div>
</div>
