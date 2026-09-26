<?php
/**
 * Panel layout.
 *
 * @package Vetra_Dashboard
 */

defined( 'ABSPATH' ) || exit;

$vtd_is_logged_in = is_user_logged_in();
if ( ! $vtd_is_logged_in ) {
	return;
}
$vtd_user_id      = get_current_user_id();
$vtd_current      = VTD_Router::current_section();
$vtd_sections     = VTD_Router::menu_sections();
$vtd_all_sections = VTD_Router::sections();
$vtd_settings     = VTD_Options::all();
$vtd_avatar_menu   = VTD_Dashboard::avatar_menu_items( $vtd_user_id );
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
					<span class="vtd-brand-mark"><?php echo esc_html( vtd_first_char( $vtd_brand ) ); ?></span>
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
				<span><?php echo esc_html__( 'حساب کاربری', 'vetra-dashboard' ); ?></span>
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
					$vtd_href   = ! empty( $vtd_section['url'] ) ? $vtd_section['url'] : vtd_panel_url( array( 'vtd' => $vtd_slug ) );
					$vtd_target = ! empty( $vtd_section['external'] ) ? ' target="_blank" rel="noopener noreferrer"' : '';
					?>
					<li>
						<a href="<?php echo esc_url( $vtd_href ); ?>" class="<?php echo $vtd_active ? 'is-active' : ''; ?>"<?php echo $vtd_target; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
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
				<details class="vtd-user-menu" data-vtd-user-menu>
					<summary class="vtd-user-menu-trigger" aria-label="منوی حساب کاربری">
						<img src="<?php echo esc_url( VTD_Profile::avatar_url( $vtd_user_id ) ); ?>" alt="">
						<span><strong><?php echo esc_html( vtd_current_user_name( $vtd_user_id ) ); ?></strong><small>حساب کاربری</small></span>
						<span class="vtd-user-menu-chevron" aria-hidden="true">⌄</span>
					</summary>
					<div class="vtd-user-menu-dropdown">
						<div class="vtd-user-menu-heading">
							<img src="<?php echo esc_url( VTD_Profile::avatar_url( $vtd_user_id ) ); ?>" alt="">
							<div><strong><?php echo esc_html( vtd_current_user_name( $vtd_user_id ) ); ?></strong><small dir="ltr"><?php echo esc_html( wp_get_current_user()->user_email ); ?></small></div>
						</div>
						<nav aria-label="منوی حساب کاربری">
							<?php foreach ( $vtd_avatar_menu as $vtd_user_menu_item ) : ?>
								<a class="<?php echo esc_attr( $vtd_user_menu_item['class'] ); ?>" href="<?php echo esc_url( $vtd_user_menu_item['url'] ); ?>" target="<?php echo esc_attr( $vtd_user_menu_item['target'] ); ?>" <?php echo '_blank' === $vtd_user_menu_item['target'] ? 'rel="noopener noreferrer"' : ''; ?>>
									<?php echo vtd_icon( $vtd_user_menu_item['icon'] ); // phpcs:ignore ?>
									<span><?php echo esc_html( $vtd_user_menu_item['label'] ); ?></span>
								</a>
							<?php endforeach; ?>
						</nav>
					</div>
				</details>
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
		<?php echo VTD_Social::render(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	</main>
</div>
