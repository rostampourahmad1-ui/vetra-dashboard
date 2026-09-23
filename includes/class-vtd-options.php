<?php
/**
 * Settings storage and defaults.
 *
 * @package Vetra_Dashboard
 */

defined( 'ABSPATH' ) || exit;

class VTD_Options {

	protected static $cache = null;

	public static function defaults() {
		$defaults = array(
			'panel_page'          => 0,
			'login_page'          => 0,
			'register_page'       => 0,
			'reset_page'          => 0,
			'panel_logo'          => '',
			'brand_name'          => 'Vetra',
			'brand_tagline'       => '',
			'primary_color'       => '#6d28d9',
			'accent_color'        => '#06b6d4',
			'radius'              => 18,
			'layout'              => 'rtl',
			'dark_mode'           => 'auto',
			'panel_fullwidth'     => 1,
			'font_family'         => 'system',
			'login_modal'         => 1,
			'login_redirect'      => '',
			'after_register_page' => '',
			'after_login_redirect' => 'panel',
			'register_enabled'    => 1,
			'email_login'         => 1,
			'phone_login'         => 1,
			'otp_login'           => 1,
			'password_login'      => 1,
			'login_username_type' => 'both',
			'email_verify'        => 1,
			'phone_verify'        => 1,
			'register_birthday'   => 1,
			'register_first_last' => 1,
			'register_terms'      => 1,
			'terms_text'          => '',
			'captcha_provider'    => 'none',
			'captcha_site_key'    => '',
			'captcha_secret'      => '',
			'menu_items'          => array(
				array( 'slug' => 'dashboard', 'enabled' => 1 ),
				array( 'slug' => 'profile', 'enabled' => 1 ),
				array( 'slug' => 'tickets', 'enabled' => 1 ),
				array( 'slug' => 'notifications', 'enabled' => 1 ),
				array( 'slug' => 'polls', 'enabled' => 1 ),
				array( 'slug' => 'attachments', 'enabled' => 1 ),
				array( 'slug' => 'wallet', 'enabled' => 1 ),
				array( 'slug' => 'banking', 'enabled' => 1 ),
				array( 'slug' => 'comments', 'enabled' => 1 ),
			),
			'dashboard_banner'    => array(),
			'dashboard_shortcuts' => array(),
			'dashboard_show_stats' => 1,
			'dashboard_welcome'   => 1,
			'profile_avatar'      => 1,
			'profile_custom_fields' => array(
				array( 'slug' => 'national_code', 'label' => 'کد ملی', 'type' => 'text', 'required' => 0 ),
				array( 'slug' => 'birthday', 'label' => 'تاریخ تولد', 'type' => 'date', 'required' => 0 ),
			),
			'profile_edit'        => 1,
			'profile_change_pass' => 1,
			'profile_confirm_email' => 1,
			'profile_confirm_phone' => 1,
			'profile_attachments' => 1,
			'ticket_enabled'      => 1,
			'ticket_allow_guest_departments' => 1,
			'ticket_max_open'     => 0,
			'ticket_auto_reply'   => '',
			'ticket_attachments'  => 1,
			'ticket_rating'       => 1,
			'ticket_staff_roles'  => array( 'administrator', 'editor' ),
			'ticket_departments'  => array(
				array( 'name' => 'پشتیبانی فنی' ),
				array( 'name' => 'مالی و پرداخت' ),
				array( 'name' => 'فروش' ),
			),
			'notifications_enabled' => 1,
			'polls_enabled'       => 1,
			'attachments_enabled' => 1,
			'banking_enabled'     => 1,
			'wallet_enabled'      => 1,
			'wallet_currency'     => 'تومان',
			'wallet_min_withdraw' => 50000,
			'wallet_topup_gateway' => '',
			'banking_require_photo' => 0,
			'comments_enabled'    => 1,
			'sms_enabled'         => 0,
			'sms_provider'        => 'ippanel',
			'sms_api_key'         => '',
			'sms_sender'          => '',
			'sms_base_url'        => 'https://edge.ippanel.com/v1',
			'sms_api_version'     => 'edge',
			'sms_pattern_otp'     => '',
			'sms_otp_variable'    => 'code',
			'sms_otp_length'      => 5,
			'sms_otp_expiry'      => 120,
			'sms_otp_resend'      => 60,
			'sms_log'             => 1,
			'sms_admin_notify'    => '',
			'email_from_name'     => '',
			'email_from_email'    => '',
			'email_header'        => '',
			'email_footer'        => '',
			'email_on_ticket'     => 1,
			'email_on_signup'     => 1,
			'delete_data_on_uninstall' => 0,
		);

		return apply_filters( 'vtd_default_settings', $defaults );
	}

	public static function all() {
		if ( null === self::$cache ) {
			$stored = get_option( VTD_OPTION_KEY, array() );
			$stored = is_array( $stored ) ? $stored : array();
			self::$cache = wp_parse_args( $stored, self::defaults() );
		}
		return self::$cache;
	}

	public static function get( $key, $default = null ) {
		$all = self::all();
		if ( array_key_exists( $key, $all ) ) {
			return $all[ $key ];
		}
		return $default;
	}

	public static function update( $key, $value ) {
		$all         = self::all();
		$all[ $key ] = $value;
		self::$cache = $all;
		return update_option( VTD_OPTION_KEY, $all );
	}

	public static function save( $settings ) {
		$settings    = wp_parse_args( (array) $settings, self::all() );
		self::$cache = $settings;
		return update_option( VTD_OPTION_KEY, $settings );
	}

	public static function reset() {
		self::$cache = null;
		delete_option( VTD_OPTION_KEY );
	}
}
