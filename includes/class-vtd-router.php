<?php
/**
 * Panel routing, endpoints and redirects.
 *
 * @package Vetra_Dashboard
 */

defined( 'ABSPATH' ) || exit;

class VTD_Router {

	public static function init() {
		add_action( 'init', array( __CLASS__, 'add_rewrite' ) );
		add_filter( 'query_vars', array( __CLASS__, 'query_vars' ) );
		add_action( 'template_redirect', array( __CLASS__, 'handle_actions' ) );
		add_filter( 'login_redirect', array( __CLASS__, 'login_redirect' ), 10, 3 );
		add_action( 'wp_logout', array( __CLASS__, 'after_logout' ) );
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
	}

	public static function query_vars( $vars ) {
		$vars[] = 'vtd';
		$vars[] = 'vtd_action';
		return $vars;
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
			'dashboard'     => array( 'label' => __( 'Dashboard', 'vetra-dashboard' ), 'icon' => 'dashboard', 'callback' => array( 'VTD_Dashboard', 'render' ) ),
			'profile'       => array( 'label' => __( 'Profile', 'vetra-dashboard' ), 'icon' => 'profile', 'callback' => array( 'VTD_Profile', 'render' ) ),
			'tickets'       => array( 'label' => __( 'Support Requests', 'vetra-dashboard' ), 'icon' => 'ticket', 'callback' => array( 'VTD_Tickets', 'render' ) ),
			'new-ticket'    => array( 'label' => __( 'New Ticket', 'vetra-dashboard' ), 'icon' => 'plus', 'callback' => array( 'VTD_Tickets', 'render_new' ), 'hidden' => true ),
			'ticket'        => array( 'label' => __( 'Ticket', 'vetra-dashboard' ), 'icon' => 'ticket', 'callback' => array( 'VTD_Tickets', 'render_single' ), 'hidden' => true ),
			'support-center' => array( 'label' => __( 'Support Center', 'vetra-dashboard' ), 'icon' => 'ticket', 'callback' => array( 'VTD_Tickets', 'render_staff' ), 'staff' => true ),
			'notifications' => array( 'label' => __( 'Notifications', 'vetra-dashboard' ), 'icon' => 'bell', 'callback' => array( 'VTD_Notifications', 'render' ) ),
			'polls'         => array( 'label' => __( 'Polls', 'vetra-dashboard' ), 'icon' => 'poll', 'callback' => array( 'VTD_Polls', 'render' ) ),
			'attachments'   => array( 'label' => __( 'Attachments', 'vetra-dashboard' ), 'icon' => 'download', 'callback' => array( 'VTD_Attachments', 'render' ) ),
			'wallet'        => array( 'label' => __( 'My Wallet', 'vetra-dashboard' ), 'icon' => 'wallet', 'callback' => array( 'VTD_Wallet', 'render' ) ),
			'banking'       => array( 'label' => __( 'Bank Information', 'vetra-dashboard' ), 'icon' => 'bank', 'callback' => array( 'VTD_Banking', 'render' ) ),
			'comments'      => array( 'label' => __( 'Comments', 'vetra-dashboard' ), 'icon' => 'comment', 'callback' => array( 'VTD_Comments', 'render' ) ),
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

		return apply_filters( 'vtd_panel_sections', $sections );
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

	public static function after_logout() {
		$target = VTD_Options::get( 'after_login_redirect', 'panel' );
		if ( 'panel' === $target && ! isset( $_GET['loggedout'] ) ) {
			wp_safe_redirect( self::login_url() );
			exit;
		}
	}
}
