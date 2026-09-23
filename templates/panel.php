<?php
/**
 * Panel layout.
 *
 * @package Vetra_Dashboard
 */

defined( 'ABSPATH' ) || exit;

$vtd_is_logged_in = is_user_logged_in();
$vtd_user_id      = get_current_user_id();
$vtd_current      = VTD_Router::current_section();
$vtd_sections     = VTD_Router::menu_sections();
$vtd_all_sections = VTD_Router::sections();
$vtd_settings     = VTD_Options::all();
$vtd_logo         = $vtd_settings['panel_logo'] ?? '';
$vtd_brand        = $vtd_settings['brand_name'] ?? 'Vetra';
$vtd_unread       = VTD_Notifications::unread_count( $vtd_user_id );
$vtd_ticket_count = VTD_Tickets::counts( $vtd_user_id );
$vtd_open_tickets = (int) ( $vtd_ticket_count['all'] ?? 0 ) - (int) ( $vtd_ticket_count['closed'] ?? 0 );
?>
<div class="vtd-app" data-theme="<?php echo esc_attr( $vtd_settings['dark_mode'] ?? 'auto' ); ?>">
	<div class="vtd-backdrop" data-vtd-drawer-close></div>

	<aside class="vtd-sidebar" id="vtd-sidebar">
		<div class="vtd-sidebar-head">
			<a class="vtd-brand" href="<?php echo esc_url( VTD_Router::panel_url() ); ?>">
				<?php if ( $vtd_logo ) : ?>
					<img src="<?php echo esc_url( $vtd_logo ); ?>" alt="<?php echo esc_attr( $vtd_brand ); ?>">
				<?php else : ?>
					<span class="vtd-brand-mark"><?php echo esc_html( mb_substr( $vtd_brand, 0, 1 ) ); ?></span>
				<?php endif; ?>
				<span class="vtd-brand-text">
					<strong><?php echo esc_html( $vtd_brand ); ?></strong>
					<?php if ( ! empty( $vtd_settings['brand_tagline'] ) ) : ?>
						<small><?php echo esc_html( $vtd_settings['brand_tagline'] ); ?></small>
					<?php endif; ?>
				</span>
			</a>
		</div>

		<div class="vtd-sidebar-user">
			<img class="vtd-avatar" src="<?php echo esc_url( VTD_Profile::avatar_url( $vtd_user_id ) ); ?>" alt="">
			<div class="vtd-sidebar-user-info">
				<strong><?php echo esc_html( vtd_current_user_name( $vtd_user_id ) ); ?></strong>
				<span><?php echo esc_html( get_userdata( $vtd_user_id )->user_login ); ?></span>
			</div>
		</div>

		<nav class="vtd-nav">
			<ul>
				<?php foreach ( $vtd_sections as $vtd_slug => $vtd_section ) : ?>
					<?php
					if ( ! empty( $vtd_section['staff'] ) && ! vtd_is_staff( $vtd_user_id ) ) {
						continue;
					}
					$vtd_active = ( $vtd_current === $vtd_slug ) || ( 'dashboard' === $vtd_slug && 'dashboard' === $vtd_current );
					?>
					<li>
						<a href="<?php echo esc_url( vtd_panel_url( array( 'vtd' => $vtd_slug ) ) ); ?>" class="<?php echo $vtd_active ? 'is-active' : ''; ?>">
							<?php echo vtd_icon( $vtd_section['icon'] ?? 'default' ); // phpcs:ignore ?>
							<span><?php echo esc_html( $vtd_section['label'] ); ?></span>
							<?php if ( 'notifications' === $vtd_slug && $vtd_unread ) : ?>
								<span class="vtd-badge"><?php echo (int) $vtd_unread; ?></span>
							<?php endif; ?>
							<?php if ( 'tickets' === $vtd_slug && $vtd_open_tickets > 0 ) : ?>
								<span class="vtd-badge"><?php echo (int) $vtd_open_tickets; ?></span>
							<?php endif; ?>
						</a>
					</li>
				<?php endforeach; ?>
				<li class="vtd-nav-logout">
					<a href="<?php echo esc_url( VTD_Auth::logout_url() ); ?>">
						<?php echo vtd_icon( 'logout' ); // phpcs:ignore ?>
						<span><?php esc_html_e( 'Logout', 'vetra-dashboard' ); ?></span>
					</a>
				</li>
			</ul>
		</nav>
	</aside>

	<main class="vtd-main">
		<header class="vtd-topbar">
			<button type="button" class="vtd-icon-btn vtd-drawer-toggle" data-vtd-drawer-open aria-label="<?php esc_attr_e( 'Menu', 'vetra-dashboard' ); ?>">
				<?php echo vtd_icon( 'dashboard' ); // phpcs:ignore ?>
			</button>
			<div class="vtd-topbar-title">
				<h1><?php echo esc_html( $vtd_all_sections[ $vtd_current ]['label'] ?? __( 'Dashboard', 'vetra-dashboard' ) ); ?></h1>
			</div>
			<div class="vtd-topbar-actions">
				<button type="button" class="vtd-icon-btn" data-vtd-theme-toggle aria-label="<?php esc_attr_e( 'Theme', 'vetra-dashboard' ); ?>">
					<?php echo vtd_icon( 'star' ); // phpcs:ignore ?>
				</button>
				<div class="vtd-notify" data-vtd-notify>
					<button type="button" class="vtd-icon-btn" data-vtd-notify-toggle aria-label="<?php esc_attr_e( 'Notifications', 'vetra-dashboard' ); ?>">
						<?php echo vtd_icon( 'bell' ); // phpcs:ignore ?>
						<span class="vtd-dot" data-vtd-notify-count <?php echo $vtd_unread ? '' : 'hidden'; ?>><?php echo (int) $vtd_unread; ?></span>
					</button>
					<div class="vtd-notify-panel" data-vtd-notify-panel>
						<div class="vtd-notify-head">
							<strong><?php esc_html_e( 'Notifications', 'vetra-dashboard' ); ?></strong>
							<button type="button" class="vtd-text-btn" data-vtd-notify-readall><?php esc_html_e( 'Mark all read', 'vetra-dashboard' ); ?></button>
						</div>
						<div class="vtd-notify-list" data-vtd-notify-list>
							<div class="vtd-notify-loading"><?php esc_html_e( 'Loading...', 'vetra-dashboard' ); ?></div>
						</div>
					</div>
				</div>
				<a class="vtd-user-chip" href="<?php echo esc_url( vtd_panel_url( array( 'vtd' => 'profile' ) ) ); ?>">
					<img src="<?php echo esc_url( VTD_Profile::avatar_url( $vtd_user_id ) ); ?>" alt="">
					<span><?php echo esc_html( vtd_current_user_name( $vtd_user_id ) ); ?></span>
				</a>
			</div>
		</header>

		<div class="vtd-content" id="vtd-content">
			<?php
			$vtd_callback = $vtd_all_sections[ $vtd_current ]['callback'] ?? null;
			if ( is_callable( $vtd_callback ) ) {
				echo call_user_func( $vtd_callback ); // phpcs:ignore WordPress.Security.EscapeOutput
			} else {
				echo VTD_Templates::module( 'dashboard', array( 'stats' => VTD_Dashboard::stats( $vtd_user_id ), 'shortcuts' => VTD_Dashboard::shortcuts(), 'banner' => array(), 'user_id' => $vtd_user_id ) ); // phpcs:ignore
			}
			?>
		</div>
	</main>
</div>
