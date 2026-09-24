<?php
/**
 * Shortcodes.
 *
 * @package Vetra_Dashboard
 */

defined( 'ABSPATH' ) || exit;

class VTD_Shortcodes {

	public static function init() {
		add_shortcode( 'vetra_dashboard', array( __CLASS__, 'dashboard' ) );
		add_shortcode( 'vetra_login', array( __CLASS__, 'login' ) );
		add_shortcode( 'vetra_register', array( __CLASS__, 'register' ) );
		add_shortcode( 'vetra_reset_password', array( __CLASS__, 'reset' ) );
		add_shortcode( 'vetra_profile_links', array( __CLASS__, 'profile_links' ) );
	}

	protected static function needs_assets() {
		VTD_Assets::panel_assets();
	}

	public static function dashboard( $atts = array() ) {
		self::needs_assets();
		return VTD_Auth::guard( array( 'VTD_Templates', 'panel' ) );
	}

	public static function login( $atts = array() ) {
		self::needs_assets();
		if ( is_user_logged_in() ) {
			return '<div class="vtd-auth-page"><div class="vtd-auth-wrap"><div class="vtd-auth-card vtd-auth-redirect">' . esc_html__( 'You are already logged in.', 'vetra-dashboard' ) . ' <a class="vtd-link" href="' . esc_url( VTD_Router::panel_url() ) . '">' . esc_html__( 'Go to dashboard', 'vetra-dashboard' ) . '</a></div></div></div>';
		}
		return '<div class="vtd-auth-page"><div class="vtd-auth-wrap">' . VTD_Templates::auth( 'login' ) . '</div></div>';
	}

	public static function register( $atts = array() ) {
		self::needs_assets();
		if ( is_user_logged_in() ) {
			return '<div class="vtd-auth-page"><div class="vtd-auth-wrap"><div class="vtd-auth-card vtd-auth-redirect"><a class="vtd-link" href="' . esc_url( VTD_Router::panel_url() ) . '">' . esc_html__( 'Go to dashboard', 'vetra-dashboard' ) . '</a></div></div></div>';
		}
		if ( ! VTD_Options::get( 'register_enabled', 1 ) ) {
			return '<div class="vtd-auth-page"><div class="vtd-auth-wrap"><div class="vtd-auth-card">' . esc_html__( 'Registration is currently disabled.', 'vetra-dashboard' ) . '</div></div></div>';
		}
		return '<div class="vtd-auth-page"><div class="vtd-auth-wrap">' . VTD_Templates::auth( 'register' ) . '</div></div>';
	}

	public static function reset( $atts = array() ) {
		self::needs_assets();
		return '<div class="vtd-auth-page"><div class="vtd-auth-wrap">' . VTD_Templates::auth( 'reset' ) . '</div></div>';
	}

	public static function profile_links( $atts = array() ) {
		self::needs_assets();
		VTD_Auth::$modal_requested = true;
		return VTD_Templates::part( 'profile-links' );
	}
}
