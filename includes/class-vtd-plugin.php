<?php
/**
 * Main plugin bootstrap.
 *
 * @package Vetra_Dashboard
 */

defined( 'ABSPATH' ) || exit;

final class VTD_Plugin {

	protected static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	protected function __construct() {
		VTD_I18n::init();
		VTD_Install::maybe_upgrade();

		VTD_Roles::init();
		VTD_Assets::init();
		VTD_Router::init();
		VTD_Shortcodes::init();
		VTD_Ajax::init();

		VTD_Email::init();
		VTD_SMS::init();
		VTD_Auth::init();
		VTD_Profile::init();
		VTD_Dashboard::init();
		VTD_Tickets::init();
		VTD_Notifications::init();
		VTD_Polls::init();
		VTD_Attachments::init();
		VTD_Banking::init();
		VTD_Wallet::init();
		VTD_Comments::init();

		if ( is_admin() ) {
			VTD_Admin::init();
		}

		add_action( 'init', array( $this, 'load_textdomain' ) );
		add_action( 'admin_notices', array( $this, 'setup_notice' ) );
		add_filter( 'wp_mail_from', array( $this, 'mail_from' ) );
		add_filter( 'wp_mail_from_name', array( $this, 'mail_from_name' ) );
	}

	public function mail_from( $email ) {
		$custom = VTD_Options::get( 'email_from_email', '' );
		return is_email( $custom ) ? $custom : $email;
	}

	public function mail_from_name( $name ) {
		$custom = VTD_Options::get( 'email_from_name', '' );
		return '' !== $custom ? $custom : $name;
	}

	public function load_textdomain() {
		load_plugin_textdomain( 'vetra-dashboard', false, dirname( VTD_BASENAME ) . '/languages' );
	}

	public function setup_notice() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$settings = VTD_Options::all();
		if ( empty( $settings['panel_page'] ) || ! get_post( $settings['panel_page'] ) ) {
			echo '<div class="notice notice-warning is-dismissible"><p>';
			printf(
				/* translators: %s settings url */
				esc_html__( 'Vetra Dashboard: the panel page has not been created yet. %s', 'vetra-dashboard' ),
				'<a href="' . esc_url( admin_url( 'admin.php?page=vetra-settings&tab=general' ) ) . '">' . esc_html__( 'Open settings', 'vetra-dashboard' ) . '</a>'
			);
			echo '</p></div>';
		}
	}
}
