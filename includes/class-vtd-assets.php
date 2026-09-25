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
					'telegram'  => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.173-.18 3.074-2.82 3.132-3.06.007-.03.014-.14-.052-.198-.066-.058-.162-.038-.232-.022-.099.022-1.673 1.063-4.76 3.117-.45.31-.858.461-1.224.451-.36-.01-1.054-.204-1.57-.372-.632-.197-1.134-.301-1.089-.637.022-.165.345-.334.969-.507 3.078-.856 5.137-1.423 6.175-1.7 2.934-.784 3.544-1.019 3.941-1.026z"/></svg>',
					'instagram' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.332.014 7.052.072 2.695.272.273 2.69.073 7.052.014 8.332 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.332 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.668-.072-4.948-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 1 0 0 12.324 6.162 6.162 0 0 0 0-12.324zM12 16a4 4 0 1 1 0-8 4 4 0 0 1 0 8zm6.406-11.845a1.44 1.44 0 1 0 0 2.881 1.44 1.44 0 0 0 0-2.881z"/></svg>',
					'whatsapp'  => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.149-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51l-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0 0 20.464 3.488"/></svg>',
					'twitter'   => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>',
					'facebook'  => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>',
					'linkedin'  => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 0 1-2.063-2.065 2.063 2.063 0 1 1 2.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>',
					'youtube'   => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>',
					'link'      => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>',
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
