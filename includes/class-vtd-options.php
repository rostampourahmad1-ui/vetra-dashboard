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
			'otp_login_provider'  => 'native',
			'digits_shortcode'    => 'digits',
			'digits_login_page'   => 0,
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
			'dashboard_menu_custom' => array(),
			'avatar_menu_items'   => array(
				array( 'label' => 'پروفایل من', 'slug' => 'profile', 'url' => '', 'icon' => 'profile', 'enabled' => 1 ),
				array( 'label' => 'تیکت‌های من', 'slug' => 'tickets', 'url' => '', 'icon' => 'ticket', 'enabled' => 1 ),
				array( 'label' => 'کیف پول', 'slug' => 'wallet', 'url' => '', 'icon' => 'wallet', 'enabled' => 1 ),
				array( 'label' => 'خروج از حساب', 'slug' => 'logout', 'url' => '', 'icon' => 'logout', 'enabled' => 1 ),
			),
			'dashboard_show_stats' => 1,
			'dashboard_welcome'   => 1,
			'dashboard_widgets'   => array(
				array( 'slug' => 'open_tickets', 'enabled' => 1 ),
				array( 'slug' => 'unread_notifications', 'enabled' => 1 ),
				array( 'slug' => 'wallet_balance', 'enabled' => 1 ),
				array( 'slug' => 'total_tickets', 'enabled' => 1 ),
				array( 'slug' => 'quick_access', 'enabled' => 1 ),
				array( 'slug' => 'account_summary', 'enabled' => 1 ),
			),
			'dashboard_layout'    => 'default',
			'social_links'        => array(),
			'copyright_text'      => '',
			'copyright_enabled'   => 1,
			'profile_avatar'      => 1,
			'profile_custom_fields' => array(
				array( 'slug' => 'national_code', 'label' => 'کد ملی', 'type' => 'text', 'required' => 0 ),
			),
			'profile_edit'        => 1,
			'profile_change_pass' => 1,
			'profile_confirm_email' => 1,
			'profile_confirm_phone' => 1,
			'profile_attachments' => 1,
			'profile_readonly_mode' => 0,
			'profile_change_request' => 0,
			'reset_password_visible' => 1,
			'ticket_enabled'      => 1,
			'ticket_allow_guest_departments' => 1,
			'ticket_max_open'     => 0,
			'ticket_auto_reply'   => '',
			'ticket_attachments'  => 1,
			'ticket_rating'       => 1,
			'ticket_faq_enabled'  => 1,
			'ticket_faq_content'  => '<h3>سوالات متداول</h3><p>قبل از ثبت تیکت لطفاً سوالات متداول را مطالعه فرمایید.</p>',
			'ticket_sms_notify'   => 0,
			'ticket_sms_events'   => array( 'created', 'replied', 'closed' ),
			'ticket_cancel_enabled' => 1,
			'ticket_departments_toggle' => 1,
			'ticket_staff_assignment' => array(),
			'ticket_staff_roles'  => array( 'administrator', 'editor' ),
			'ticket_departments'  => array(
				array( 'name' => 'پشتیبانی فنی', 'enabled' => 1 ),
				array( 'name' => 'مالی و پرداخت', 'enabled' => 1 ),
				array( 'name' => 'فروش', 'enabled' => 1 ),
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
			'email_template_welcome' => '<p>سلام {{user_name}} عزیز،</p><p>حساب کاربری شما در {{site_name}} با موفقیت ساخته شد.</p><p><a href="{{action_url}}">ورود به پیشخوان</a></p>',
			'email_template_ticket_created' => '<p>درخواست پشتیبانی شما با شماره {{ticket_id}} ثبت شد.</p><p><strong>{{ticket_title}}</strong></p><p><a href="{{action_url}}">مشاهده تیکت</a></p>',
			'email_template_ticket_reply' => '<p>پاسخ تازه‌ای برای تیکت شماره {{ticket_id}} ثبت شده است.</p><p><strong>{{ticket_title}}</strong></p><p><a href="{{action_url}}">مشاهده گفتگو</a></p>',
			'email_template_card_status' => '<p>وضعیت کارت بانکی شما: {{status}}</p>',
			'email_template_withdrawal_status' => '<p>وضعیت درخواست برداشت شما: {{status}}</p><p>مبلغ: {{amount}}</p>',
			'email_on_ticket'     => 1,
			'email_on_signup'     => 1,
			'delete_data_on_uninstall' => 0,

			/* Modules visibility ------------------------------------------------ */
			'dashboard_enabled'   => 1,
			'profile_enabled'     => 1,
			'admin_modules'       => array(
				array( 'slug' => 'vetra-users', 'enabled' => 1 ),
				array( 'slug' => 'vetra-sms-log', 'enabled' => 1 ),
				array( 'slug' => 'vetra-withdrawals', 'enabled' => 1 ),
				array( 'slug' => 'vetra-wallet', 'enabled' => 1 ),
				array( 'slug' => 'vetra-cards', 'enabled' => 1 ),
				array( 'slug' => 'vetra-attachments', 'enabled' => 1 ),
				array( 'slug' => 'vetra-polls', 'enabled' => 1 ),
				array( 'slug' => 'vetra-notifications', 'enabled' => 1 ),
				array( 'slug' => 'vetra-departments', 'enabled' => 1 ),
				array( 'slug' => 'vetra-tickets', 'enabled' => 1 ),
				array( 'slug' => 'vetra-changes', 'enabled' => 1 ),
			),

			/* Dashboard layout -------------------------------------------------- */
			'dashboard_layout'    => 'comfortable',
			'dashboard_columns'   => 4,
			'dashboard_gradient'  => 1,
			'dashboard_hero'      => 1,
			'dashboard_cards'     => array(
				array( 'metric' => 'open_tickets', 'label' => 'تیکت‌های باز', 'icon' => 'ticket', 'tone' => 'primary', 'enabled' => 1 ),
				array( 'metric' => 'unread_notifications', 'label' => 'اعلان‌های خوانده‌نشده', 'icon' => 'bell', 'tone' => 'accent', 'enabled' => 1 ),
				array( 'metric' => 'wallet_balance', 'label' => 'موجودی کیف پول', 'icon' => 'wallet', 'tone' => 'success', 'enabled' => 1 ),
				array( 'metric' => 'total_tickets', 'label' => 'کل تیکت‌ها', 'icon' => 'ticket', 'tone' => 'muted', 'enabled' => 1 ),
			),
			'dashboard_custom_cards' => array(),
			'dashboard_blocks'    => array(
				array( 'slug' => 'shortcuts', 'enabled' => 1 ),
				array( 'slug' => 'summary', 'enabled' => 1 ),
			),

			/* Footer: social links + copyright ---------------------------------- */
			'social_enabled'      => 1,
			'social_color_mode'   => 'brand',
			'social_icons'        => array(
				array( 'network' => 'instagram', 'label' => 'اینستاگرام', 'url' => '', 'color' => '#e1306c', 'enabled' => 1 ),
				array( 'network' => 'telegram', 'label' => 'تلگرام', 'url' => '', 'color' => '#229ed9', 'enabled' => 1 ),
				array( 'network' => 'whatsapp', 'label' => 'واتس‌اپ', 'url' => '', 'color' => '#25d366', 'enabled' => 1 ),
				array( 'network' => 'linkedin', 'label' => 'لینکدین', 'url' => '', 'color' => '#0a66c2', 'enabled' => 0 ),
				array( 'network' => 'youtube', 'label' => 'یوتیوب', 'url' => '', 'color' => '#ff0000', 'enabled' => 0 ),
				array( 'network' => 'x', 'label' => 'ایکس', 'url' => '', 'color' => '#111827', 'enabled' => 0 ),
			),
			'footer_copyright_enabled' => 1,
			'footer_copyright'    => '© {year} Vetra — تمامی حقوق برای Vetra محفوظ است.',
			'footer_extra'        => '',

			/* Ticket extras ------------------------------------------------------ */
			'ticket_departments_visible' => 1,
			'ticket_hidden_departments'  => array(),
			'ticket_allow_close'  => 1,
			'ticket_allow_cancel' => 1,
			'ticket_faq_enabled'  => 1,
			'ticket_faq_gate'     => 1,
			'ticket_faq_intro'    => 'قبل از ثبت تیکت، لطفاً پرسش‌های متداول و آموزش‌های زیر را مطالعه کنید.',
			'ticket_faq_items'    => array(
				array( 'question' => 'چگونه رمز عبور خود را تغییر دهم؟', 'answer' => 'از بخش پروفایل و کارت «تغییر گذرواژه» می‌توانید رمز خود را تغییر دهید.', 'url' => '' ),
				array( 'question' => 'چطور مدارک هویتی را ارسال کنم؟', 'answer' => 'در بخش پروفایل، درخواست تغییر اطلاعات را ثبت کنید و مدارک را پیوست نمایید.', 'url' => '' ),
			),
			'ticket_faq_links'    => array(
				array( 'label' => 'راهنمای شروع به کار', 'url' => '' ),
			),
			'ticket_sms_notify_user'  => 0,
			'ticket_sms_notify_staff' => 0,
			'ticket_sms_admin_phone'  => '',
			'ticket_sms_pattern_created' => '',
			'ticket_sms_pattern_reply'   => '',
			'ticket_department_staff' => array(),
			'ticket_staff_by_role'    => 1,
			'ticket_department_assign' => array(),

			/* Profile change-request workflow ------------------------------------ */
			'profile_readonly'         => 0,
			'profile_change_requests'  => 1,
			'profile_change_require_docs' => 1,
			'profile_change_fields'    => array( 'first_name', 'last_name', 'national_code', 'phone', 'email', 'birthday', 'gender', 'about' ),
			'profile_national_code_field' => 'national_code',
			'profile_sms_on_change'    => 0,

			/* Auth extras -------------------------------------------------------- */
			'forgot_password'     => 1,
			'username_is_national_code' => 0,
			'register_national_code'    => 1,
			'display_name_format' => 'full_name',
			'national_code_meta'  => 'national_code',
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
