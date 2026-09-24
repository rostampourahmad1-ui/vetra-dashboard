<?php
/**
 * Front-end and admin asset registration.
 *
 * @package Vetra_Dashboard
 */

defined( 'ABSPATH' ) || exit;

class VTD_Assets {

	protected static $panel_loaded = false;

	public static function init() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'front' ), 20 );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin' ) );
		add_action( 'wp_head', array( __CLASS__, 'dynamic_css' ), 99 );
		add_action( 'admin_head', array( __CLASS__, 'dynamic_css' ), 99 );
	}

	public static function icons() {
		return apply_filters(
			'vtd_icons',
			array(
				'dashboard' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="3" y="3" width="7" height="9" rx="2"/><rect x="14" y="3" width="7" height="5" rx="2"/><rect x="14" y="12" width="7" height="9" rx="2"/><rect x="3" y="16" width="7" height="5" rx="2"/></svg>',
				'profile'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-3.5 3.6-6 8-6s8 2.5 8 6"/></svg>',
				'ticket'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M4 7a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v3a2 2 0 0 0 0 4v3a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-3a2 2 0 0 0 0-4z"/><path d="M14 5v14" stroke-dasharray="2 2"/></svg>',
				'bell'      => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M6 9a6 6 0 1 1 12 0c0 5 2 6 2 6H4s2-1 2-6"/><path d="M10 19a2 2 0 0 0 4 0"/></svg>',
				'poll'      => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M6 20V10"/><path d="M12 20V4"/><path d="M18 20v-6"/></svg>',
				'download'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 3v12"/><path d="m7 12 5 5 5-5"/><path d="M5 21h14"/></svg>',
				'wallet'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="3" y="6" width="18" height="13" rx="3"/><path d="M3 10h18"/><circle cx="17" cy="14" r="1.3" fill="currentColor" stroke="none"/></svg>',
				'bank'      => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="m3 9 9-5 9 5"/><path d="M5 9v9M19 9v9M9 9v9M15 9v9M3 20h18"/></svg>',
				'comment'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M4 5h16a1 1 0 0 1 1 1v10a1 1 0 0 1-1 1H8l-4 4V6a1 1 0 0 1 1-1z"/></svg>',
				'logout'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M15 4h3a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-3"/><path d="M10 17l-5-5 5-5"/><path d="M5 12h11"/></svg>',
				'settings'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.6 1.6 0 0 0 .3 1.8l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.6 1.6 0 0 0-2.7 1.1V21a2 2 0 1 1-4 0v-.1A1.6 1.6 0 0 0 7 19.4a1.6 1.6 0 0 0-1.8.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.6 1.6 0 0 0-1.1-2.7H1a2 2 0 1 1 0-4h.1A1.6 1.6 0 0 0 2.6 7a1.6 1.6 0 0 0-.3-1.8l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.6 1.6 0 0 0 1.8.3H7a1.6 1.6 0 0 0 1-1.5V1a2 2 0 1 1 4 0v.1A1.6 1.6 0 0 0 15 2.6a1.6 1.6 0 0 0 1.8-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.6 1.6 0 0 0-.3 1.8V7a1.6 1.6 0 0 0 1.5 1H21a2 2 0 1 1 0 4h-.1a1.6 1.6 0 0 0-1.5 1z"/></svg>',
				'user'      => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-3.5 3.6-6 8-6s8 2.5 8 6"/></svg>',
				'lock'      => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="5" y="10" width="14" height="10" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></svg>',
				'mail'      => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m4 7 8 6 8-6"/></svg>',
				'phone'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="7" y="2" width="10" height="20" rx="2"/><path d="M11 18h2"/></svg>',
				'plus'      => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 5v14M5 12h14"/></svg>',
				'search'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>',
				'check'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><path d="m5 13 4 4L19 7"/></svg>',
				'close'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><path d="M6 6l12 12M18 6 6 18"/></svg>',
				'star'      => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="m12 3 2.7 5.6 6.1.9-4.4 4.3 1 6.1L12 17l-5.4 2.9 1-6.1L3.2 9.5l6.1-.9z"/></svg>',
				'arrow'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="m14 6-6 6 6 6"/></svg>',
				'default'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="12" r="8"/></svg>',
			)
		);
	}

	public static function front() {
		if ( ! self::$panel_loaded && ! self::is_auth_page() && ! self::has_vtd_content() ) {
			return;
		}

		wp_enqueue_style( 'vtd-front', VTD_ASSETS . 'css/vetra.css', array(), VTD_VERSION );
		wp_enqueue_script( 'vtd-jalali', VTD_ASSETS . 'js/jalali.js', array(), VTD_VERSION, true );
		wp_enqueue_script( 'vtd-front', VTD_ASSETS . 'js/vetra.js', array( 'vtd-jalali' ), VTD_VERSION, true );

		if ( self::is_auth_page() ) {
			wp_enqueue_style( 'vtd-auth', VTD_ASSETS . 'css/auth.css', array( 'vtd-front' ), VTD_VERSION );
		}

		wp_localize_script(
			'vtd-front',
			'VTD',
			array(
				'rest'      => esc_url_raw( rest_url( 'vetra/v1/' ) ),
				'nonce'     => wp_create_nonce( 'wp_rest' ),
				'panelUrl'  => VTD_Router::panel_url(),
				'loginUrl'  => VTD_Router::login_url(),
				'isLoggedIn' => is_user_logged_in(),
				'locale'    => get_locale(),
				'i18n'      => array(
					'save'       => __( 'Save', 'vetra-dashboard' ),
					'saved'      => __( 'Saved successfully', 'vetra-dashboard' ),
					'error'      => __( 'Something went wrong', 'vetra-dashboard' ),
					'loading'    => __( 'Loading...', 'vetra-dashboard' ),
					'confirm'    => __( 'Are you sure?', 'vetra-dashboard' ),
					'copied'     => __( 'Copied', 'vetra-dashboard' ),
					'empty'      => __( 'No notifications yet.', 'vetra-dashboard' ),
					'view'       => __( 'View', 'vetra-dashboard' ),
				),
			)
		);
	}

	public static function panel_assets() {
		self::$panel_loaded = true;
		if ( ! wp_style_is( 'vtd-front', 'enqueued' ) ) {
			wp_enqueue_style( 'vtd-front', VTD_ASSETS . 'css/vetra.css', array(), VTD_VERSION );
		}
		if ( ! wp_script_is( 'vtd-jalali', 'enqueued' ) ) {
			wp_enqueue_script( 'vtd-jalali', VTD_ASSETS . 'js/jalali.js', array(), VTD_VERSION, true );
		}
		if ( ! wp_script_is( 'vtd-front', 'enqueued' ) ) {
			wp_enqueue_script( 'vtd-front', VTD_ASSETS . 'js/vetra.js', array( 'vtd-jalali' ), VTD_VERSION, true );
		}
	}

	public static function is_auth_page() {
		$settings = VTD_Options::all();
		$ids      = array_filter(
			array(
				$settings['login_page'] ?? 0,
				$settings['register_page'] ?? 0,
				$settings['reset_page'] ?? 0,
			)
		);
		return is_page( $ids ) || is_page( array( 'vtd-login', 'vtd-register', 'vtd-reset' ) );
	}

	public static function is_panel_page() {
		$settings = VTD_Options::all();
		$panel    = (int) ( $settings['panel_page'] ?? 0 );
		if ( $panel && is_page( $panel ) ) {
			return true;
		}
		return is_page( array( 'vtd-panel' ) );
	}

	public static function has_vtd_content() {
		if ( self::is_panel_page() ) {
			return true;
		}
		$post = get_post();
		if ( ! $post || empty( $post->post_content ) ) {
			return false;
		}
		foreach ( array( 'vetra_dashboard', 'vetra_login', 'vetra_register', 'vetra_reset_password', 'vetra_profile_links', 'vetra_panel_section', 'vetra_panel_menu' ) as $shortcode ) {
			if ( has_shortcode( $post->post_content, $shortcode ) ) {
				return true;
			}
		}
		return false;
	}

	public static function admin( $hook ) {
		$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';
		if ( 0 !== strpos( $page, 'vetra-' ) && 'vetra-dashboard' !== $page ) {
			return;
		}
		wp_enqueue_style( 'vtd-admin', VTD_ASSETS . 'css/admin.css', array(), VTD_VERSION );
		wp_enqueue_media();
		wp_enqueue_script( 'vtd-admin', VTD_ASSETS . 'js/admin.js', array( 'jquery', 'wp-color-picker' ), VTD_VERSION, true );
		wp_enqueue_style( 'wp-color-picker' );
		wp_localize_script(
			'vtd-admin',
			'VTD_ADMIN',
			array(
				'rest'  => esc_url_raw( rest_url( 'vetra/v1/' ) ),
				'nonce' => wp_create_nonce( 'wp_rest' ),
				'i18n'  => array(
					'selectImage' => __( 'Select', 'vetra-dashboard' ),
					'useImage'    => __( 'Use this file', 'vetra-dashboard' ),
					'remove'      => __( 'Remove', 'vetra-dashboard' ),
				),
			)
		);
	}

	public static function dynamic_css() {
		$settings = VTD_Options::all();
		$primary  = $settings['primary_color'] ?? '#6d28d9';
		$accent   = $settings['accent_color'] ?? '#06b6d4';
		$radius   = (int) ( $settings['radius'] ?? 18 );
		?>
		<style id="vtd-dynamic-css">
			:root {
				--vtd-primary: <?php echo esc_html( $primary ); ?>;
				--vtd-accent: <?php echo esc_html( $accent ); ?>;
				--vtd-radius: <?php echo (int) $radius; ?>px;
			}
		</style>
		<?php
	}
}
