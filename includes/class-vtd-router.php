<?php
/**
 * Panel routing, endpoints and redirects.
 *
 * @package Vetra_Dashboard
 */

defined( 'ABSPATH' ) || exit;

class VTD_Router {

	protected static $registered_sections = array();

	public static function init() {
		add_action( 'init', array( __CLASS__, 'add_rewrite' ) );
		add_filter( 'query_vars', array( __CLASS__, 'query_vars' ) );
		add_action( 'template_redirect', array( __CLASS__, 'handle_actions' ) );
		add_filter( 'login_redirect', array( __CLASS__, 'login_redirect' ), 10, 3 );
		add_filter( 'template_include', array( __CLASS__, 'template_include' ), 99 );
		add_filter( 'body_class', array( __CLASS__, 'body_class' ) );
	}

	public static function template_include( $template ) {
		if ( ! VTD_Options::get( 'panel_fullwidth', 1 ) ) {
			return $template;
		}
		if ( VTD_Assets::is_panel_page() && ! is_embed() ) {
			$custom = VTD_TEMPLATES . 'full-page.php';
			if ( file_exists( $custom ) ) {
				return $custom;
			}
		}
		return $template;
	}

	public static function body_class( $classes ) {
		if ( VTD_Assets::is_panel_page() ) {
			$classes[] = 'vtd-fullwidth';
			$classes[] = 'vtd-body';
		}
		if ( VTD_Assets::is_auth_page() ) {
			$classes[] = 'vtd-auth-page';
		}
		return $classes;
	}

	public static function add_rewrite() {
		add_rewrite_rule( '^vtd-panel/([^/]+)/?$', 'index.php?pagename=vtd-panel&vtd=$matches[1]', 'top' );
		add_rewrite_rule( '^vtd-panel/([^/]+)/([0-9]+)/?$', 'index.php?pagename=vtd-panel&vtd=$matches[1]&ticket=$matches[2]', 'top' );
	}

	public static function query_vars( $vars ) {
		$vars[] = 'vtd';
		$vars[] = 'vtd_action';
		$vars[] = 'ticket';
		$vars[] = 'ticket_status';
		$vars[] = 'ticket_search';
		$vars[] = 'tpage';
		$vars[] = 'department';
		$vars[] = 'vtd_msg';
		return $vars;
	}

	/** Direct URL of a single support ticket. */
	public static function ticket_url( $ticket_id ) {
		return apply_filters( 'vtd_ticket_url', vtd_panel_url( array( 'vtd' => 'ticket', 'ticket' => (int) $ticket_id ) ), (int) $ticket_id );
	}

	public static function page_url( $key ) {
		$settings = VTD_Options::all();
		$id       = isset( $settings[ $key ] ) ? (int) $settings[ $key ] : 0;
		if ( $id && 'publish' === get_post_status( $id ) ) {
			return get_permalink( $id );
		}
		$slugs = array(
			'panel_page'    => 'vtd-panel',
			'login_page'    => 'vtd-login',
			'register_page' => 'vtd-register',
			'reset_page'    => 'vtd-reset',
		);
		$slug  = $slugs[ $key ] ?? 'vtd-panel';
		$page  = get_page_by_path( $slug );
		return $page ? get_permalink( $page ) : home_url( '/' );
	}

	public static function panel_url() {
		return self::page_url( 'panel_page' );
	}

	public static function login_url() {
		return self::page_url( 'login_page' );
	}

	public static function register_url() {
		return self::page_url( 'register_page' );
	}

	public static function reset_url() {
		return self::page_url( 'reset_page' );
	}

	public static function current_section() {
		$section  = isset( $_GET['vtd'] ) ? sanitize_key( wp_unslash( $_GET['vtd'] ) ) : 'dashboard';
		$sections = self::sections();
		if ( ! isset( $sections[ $section ] ) ) {
			$section = 'dashboard';
		}
		return $section;
	}

	public static function sections() {
		$sections = array(
			'dashboard'      => array( 'label' => 'داشبورد', 'icon' => 'dashboard', 'callback' => array( 'VTD_Dashboard', 'render' ) ),
			'profile'        => array( 'label' => 'پروفایل', 'icon' => 'profile', 'callback' => array( 'VTD_Profile', 'render' ) ),
			'tickets'        => array( 'label' => 'درخواست‌های پشتیبانی', 'icon' => 'ticket', 'callback' => array( 'VTD_Tickets', 'render' ) ),
			'new-ticket'     => array( 'label' => 'تیکت جدید', 'icon' => 'plus', 'callback' => array( 'VTD_Tickets', 'render_new' ), 'hidden' => true ),
			'ticket'         => array( 'label' => 'تیکت', 'icon' => 'ticket', 'callback' => array( 'VTD_Tickets', 'render_single' ), 'hidden' => true ),
			'support-center' => array( 'label' => 'مرکز پشتیبانی', 'icon' => 'ticket', 'callback' => array( 'VTD_Tickets', 'render_staff' ), 'staff' => true ),
			'notifications'  => array( 'label' => 'اعلان‌ها', 'icon' => 'bell', 'callback' => array( 'VTD_Notifications', 'render' ) ),
			'polls'          => array( 'label' => 'نظرسنجی‌ها', 'icon' => 'poll', 'callback' => array( 'VTD_Polls', 'render' ) ),
			'attachments'    => array( 'label' => 'پیوست‌ها', 'icon' => 'download', 'callback' => array( 'VTD_Attachments', 'render' ) ),
			'wallet'         => array( 'label' => 'کیف پول', 'icon' => 'wallet', 'callback' => array( 'VTD_Wallet', 'render' ) ),
			'banking'        => array( 'label' => 'اطلاعات بانکی', 'icon' => 'bank', 'callback' => array( 'VTD_Banking', 'render' ) ),
			'comments'       => array( 'label' => 'دیدگاه‌ها', 'icon' => 'comment', 'callback' => array( 'VTD_Comments', 'render' ) ),
		);

		$settings = VTD_Options::all();
		$enabled  = array();
		foreach ( (array) ( $settings['menu_items'] ?? array() ) as $item ) {
			if ( ! empty( $item['enabled'] ) && ! empty( $item['slug'] ) ) {
				$enabled[] = $item['slug'];
			}
		}
		if ( empty( $enabled ) ) {
			$enabled = array( 'dashboard', 'profile', 'tickets', 'notifications', 'polls', 'attachments', 'wallet', 'banking', 'comments' );
		}

		foreach ( $sections as $slug => $data ) {
			if ( empty( $data['hidden'] ) && empty( $data['staff'] ) && ! in_array( $slug, $enabled, true ) ) {
				unset( $sections[ $slug ] );
			}
		}

		foreach ( self::$registered_sections as $slug => $section ) {
			if ( ! isset( $sections[ $slug ] ) ) {
				$sections[ $slug ] = $section;
			}
		}

		foreach ( (array) VTD_Options::get( 'dashboard_menu_custom', array() ) as $item ) {
			if ( empty( $item['enabled'] ) || empty( $item['slug'] ) || empty( $item['label'] ) ) {
				continue;
			}
			$slug = sanitize_key( $item['slug'] );
			if ( isset( $sections[ $slug ] ) ) {
				continue;
			}
			$entry = array(
				'label'  => sanitize_text_field( $item['label'] ),
				'icon'   => sanitize_key( $item['icon'] ?? 'default' ),
				'custom' => true,
			);
			if ( 'link' === ( $item['type'] ?? 'link' ) ) {
				if ( empty( $item['url'] ) ) {
					continue;
				}
				$entry['url']      = esc_url( $item['url'] );
				$host              = wp_parse_url( $item['url'], PHP_URL_HOST );
				$home_host         = wp_parse_url( home_url(), PHP_URL_HOST );
				$entry['external'] = $host && $home_host && strtolower( $host ) !== strtolower( $home_host );
			} else {
				$entry['callback'] = array( __CLASS__, 'render_custom_section' );
			}
			$sections[ $slug ] = $entry;
		}

		return apply_filters( 'vtd_panel_sections', $sections );
	}

	public static function register_section( $slug, $args ) {
		$slug = sanitize_key( $slug );
		$args = wp_parse_args( (array) $args, array( 'label' => '', 'icon' => 'default', 'callback' => null, 'staff' => false ) );
		if ( '' === $slug || '' === trim( (string) $args['label'] ) || ! is_callable( $args['callback'] ) ) {
			return false;
		}
		self::$registered_sections[ $slug ] = array(
			'label'    => sanitize_text_field( $args['label'] ),
			'icon'     => sanitize_key( $args['icon'] ),
			'callback' => $args['callback'],
			'staff'    => (bool) $args['staff'],
		);
		return true;
	}

	public static function render_custom_section( $slug = '' ) {
		$slug  = $slug ? sanitize_key( $slug ) : self::current_section();
		$items = (array) VTD_Options::get( 'dashboard_menu_custom', array() );
		foreach ( $items as $item ) {
			if ( empty( $item['enabled'] ) || sanitize_key( $item['slug'] ?? '' ) !== $slug ) {
				continue;
			}
			$type    = $item['type'] ?? 'link';
			$content = '';
			if ( 'page' === $type ) {
				$page = get_post( absint( $item['page_id'] ?? 0 ) );
				if ( $page && 'publish' === $page->post_status ) {
					$content = preg_replace( '/\[vetra_dashboard(?:\s[^\]]*)?\]/', '', $page->post_content );
					$content = do_blocks( $content );
					$content = do_shortcode( wpautop( $content ) );
				}
			} elseif ( 'shortcode' === $type ) {
				$content = do_shortcode( (string) ( $item['shortcode'] ?? '' ) );
			} elseif ( 'content' === $type ) {
				$content = do_blocks( (string) ( $item['content'] ?? '' ) );
				$content = do_shortcode( wpautop( $content ) );
			}
			$content = apply_filters( 'vtd_custom_panel_section_content', $content, $item, get_current_user_id() );
			return '<div class="vtd-custom-panel-content">' . $content . '</div>';
		}
		return VTD_Templates::module( 'alert', array( 'message' => __( 'This dashboard page is unavailable.', 'vetra-dashboard' ), 'type' => 'error' ) );
	}

	public static function render_section( $slug ) {
		$slug     = sanitize_key( $slug );
		$sections = self::sections();
		if ( empty( $sections[ $slug ] ) ) {
			return '';
		}
		if ( ! empty( $sections[ $slug ]['staff'] ) && ! vtd_is_staff() ) {
			return VTD_Templates::module( 'alert', array( 'message' => __( 'You do not have access to this section.', 'vetra-dashboard' ), 'type' => 'error' ) );
		}
		if ( ! empty( $sections[ $slug ]['custom'] ) ) {
			return self::render_custom_section( $slug );
		}
		return is_callable( $sections[ $slug ]['callback'] ?? null ) ? call_user_func( $sections[ $slug ]['callback'] ) : '';
	}

	public static function menu_sections() {
		$sections = self::sections();
		return array_filter(
			$sections,
			function ( $section ) {
				return empty( $section['hidden'] );
			}
		);
	}

	public static function handle_actions() {
		// Handle profile change request
		if ( isset( $_POST['vtd_action'] ) && 'profile_change_request' === $_POST['vtd_action'] && is_user_logged_in() ) {
			$nonce = isset( $_POST['vtd_change_request_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['vtd_change_request_nonce'] ) ) : '';
			if ( wp_verify_nonce( $nonce, 'vtd_change_request' ) ) {
				$user_id = get_current_user_id();
				$field = sanitize_key( wp_unslash( $_POST['change_field'] ?? '' ) );
				$value = sanitize_text_field( wp_unslash( $_POST['change_value'] ?? '' ) );
				$reason = sanitize_textarea_field( wp_unslash( $_POST['change_reason'] ?? '' ) );
				$doc_id = 0;

				if ( ! empty( $_FILES['change_document']['name'] ) ) {
					require_once ABSPATH . 'wp-admin/includes/file.php';
					require_once ABSPATH . 'wp-admin/includes/media.php';
					$doc_id = media_handle_sideload( $_FILES['change_document'], 0, __( 'Change request document', 'vetra-dashboard' ) );
					if ( is_wp_error( $doc_id ) ) {
						$doc_id = 0;
					}
				}

				if ( $field && $value ) {
					global $wpdb;
					$wpdb->insert(
						$wpdb->prefix . 'vtd_change_requests',
						array(
							'user_id' => $user_id,
							'field_name' => $field,
							'requested_value' => $value,
							'reason' => $reason,
							'document_id' => (int) $doc_id,
							'status' => 'pending',
							'created_at' => current_time( 'mysql' ),
						),
						array( '%d', '%s', '%s', '%s', '%d', '%s', '%s' )
					);
					wp_safe_redirect( vtd_panel_url( array( 'vtd' => 'profile', 'change_request' => 'submitted' ) ) );
					exit;
				}
			}
		}

		if ( isset( $_GET['vtd_action'] ) && 'logout' === $_GET['vtd_action'] ) {
			$nonce = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : '';
			if ( wp_verify_nonce( $nonce, 'vtd_logout' ) ) {
				wp_logout();
				wp_safe_redirect( add_query_arg( 'loggedout', '1', self::login_url() ) );
				exit;
			}
		}
	}

	public static function logout_url() {
		return wp_nonce_url( add_query_arg( 'vtd_action', 'logout', self::panel_url() ), 'vtd_logout' );
	}

	public static function login_redirect( $redirect_to, $requested, $user ) {
		if ( is_wp_error( $user ) || ! $user instanceof WP_User ) {
			return $redirect_to;
		}
		$target = VTD_Options::get( 'after_login_redirect', 'panel' );
		if ( 'panel' === $target ) {
			return self::panel_url();
		}
		if ( 'dashboard' === $target ) {
			return admin_url();
		}
		return $redirect_to;
	}

}
