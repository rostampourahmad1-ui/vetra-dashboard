<?php
/**
 * Authentication: login, register, OTP and password reset.
 *
 * @package Vetra_Dashboard
 */

defined( 'ABSPATH' ) || exit;

class VTD_Auth {

	public static $errors         = array();
	public static $success        = array();
	public static $form_data      = array();
	public static $stage          = '';
	public static $modal_requested = false;

	const PHONE_META = 'vtd_phone';
	const PHONE_VERIFIED_META = 'vtd_phone_verified';
	const EMAIL_VERIFIED_META = 'vtd_email_verified';

	public static function init() {
		add_action( 'init', array( __CLASS__, 'process' ), 30 );
		add_action( 'template_redirect', array( __CLASS__, 'block_wp_login' ) );
		add_filter( 'authenticate', array( __CLASS__, 'authenticate_by_phone' ), 20, 3 );
		add_filter( 'wp_authenticate_user', array( __CLASS__, 'check_account_status' ), 30, 2 );
		add_action( 'wp_footer', array( __CLASS__, 'render_login_modal' ) );
	}

	public static function render_login_modal() {
		if ( is_user_logged_in() || is_admin() ) {
			return;
		}
		if ( ! VTD_Options::get( 'login_modal', 1 ) ) {
			return;
		}
		if ( ! self::$modal_requested && ! apply_filters( 'vtd_render_login_modal', false ) ) {
			return;
		}
		VTD_Assets::panel_assets();
		echo VTD_Templates::part( 'login-modal' ); // phpcs:ignore WordPress.Security.EscapeOutput
	}

	public static function check_account_status( $user, $password = '' ) {
		if ( is_wp_error( $user ) || ! $user instanceof WP_User ) {
			return $user;
		}
		$status = get_user_meta( $user->ID, 'vtd_account_status', true );
		if ( in_array( $status, array( 'rejected', 'disabled', 'suspended' ), true ) ) {
			return new WP_Error( 'vtd_account_disabled', __( 'Your account is disabled.', 'vetra-dashboard' ) );
		}
		return $user;
	}

	public static function phone_meta_key() {
		return apply_filters( 'vtd_phone_meta_key', self::PHONE_META );
	}

	public static function get_phone( $user_id ) {
		$phone = get_user_meta( $user_id, self::phone_meta_key(), true );
		if ( '' === $phone ) {
			$phone = get_user_meta( $user_id, 'cell-phone', true );
		}
		return $phone;
	}

	public static function update_phone( $user_id, $phone, $verified = 0 ) {
		update_user_meta( $user_id, self::phone_meta_key(), $phone );
		update_user_meta( $user_id, 'cell-phone', $phone );
		if ( $verified ) {
			update_user_meta( $user_id, self::PHONE_VERIFIED_META, 1 );
		}
	}

	public static function is_phone_verified( $user_id ) {
		return (bool) get_user_meta( $user_id, self::PHONE_VERIFIED_META, true );
	}

	public static function is_email_verified( $user_id ) {
		return (bool) get_user_meta( $user_id, self::EMAIL_VERIFIED_META, true );
	}

	public static function find_user_by_phone( $phone ) {
		global $wpdb;
		$phone = vtd_sanitize_phone( $phone );
		if ( '' === $phone ) {
			return null;
		}
		$local = vtd_local_phone( $phone );
		$keys  = array( self::phone_meta_key(), 'cell-phone' );
		foreach ( $keys as $key ) {
			$user_id = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key = %s AND (meta_value = %s OR meta_value = %s) LIMIT 1",
					$key,
					$phone,
					$local
				)
			);
			if ( $user_id ) {
				return get_userdata( (int) $user_id );
			}
		}
		return null;
	}

	public static function guard( $callback ) {
		if ( is_user_logged_in() ) {
			return is_callable( $callback ) ? call_user_func( $callback ) : (string) $callback;
		}
		VTD_Assets::panel_assets();
		$html  = '<div class="vtd-guard">';
		$html .= '<div class="vtd-guard-card">';
		$html .= '<h2>' . esc_html__( 'Sign in to access your dashboard', 'vetra-dashboard' ) . '</h2>';
		$html .= '<p>' . esc_html__( 'Please log in or create an account to continue.', 'vetra-dashboard' ) . '</p>';
		$html .= VTD_Templates::auth( 'login' );
		$html .= '</div></div>';
		return $html;
	}

	public static function block_wp_login() {
		$login_page = VTD_Options::get( 'login_page', 0 );
		if ( ! $login_page || ! is_page( $login_page ) ) {
			return;
		}
		if ( is_user_logged_in() ) {
			wp_safe_redirect( VTD_Router::panel_url() );
			exit;
		}
	}

	public static function authenticate_by_phone( $user, $username, $password ) {
		if ( $user instanceof WP_User || '' === $username ) {
			return $user;
		}
		if ( ! VTD_Options::get( 'phone_login', 1 ) ) {
			return $user;
		}
		$found = self::find_user_by_phone( $username );
		if ( $found ) {
			$check = wp_authenticate_username_password( null, $found->user_login, $password );
			if ( ! is_wp_error( $check ) ) {
				return $check;
			}
		}
		return $user;
	}

	public static function process() {
		if ( empty( $_POST['vtd_auth_action'] ) ) {
			return;
		}

		$action = sanitize_key( wp_unslash( $_POST['vtd_auth_action'] ) );
		$nonce  = isset( $_POST['vtd_auth_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['vtd_auth_nonce'] ) ) : '';

		if ( ! wp_verify_nonce( $nonce, 'vtd_auth' ) ) {
			self::$errors[] = __( 'Security check failed. Please try again.', 'vetra-dashboard' );
			return;
		}

		switch ( $action ) {
			case 'login':
				self::handle_login();
				break;
			case 'register':
				self::handle_register();
				break;
			case 'otp_request':
				self::handle_otp_request();
				break;
			case 'otp_verify':
				self::handle_otp_verify();
				break;
			case 'reset_request':
				self::handle_reset_request();
				break;
			case 'reset_set':
				self::handle_reset_set();
				break;
		}
	}

	protected static function field( $key ) {
		return isset( $_POST[ $key ] ) ? sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) : '';
	}

	protected static function captcha_ok() {
		$provider = VTD_Options::get( 'captcha_provider', 'none' );
		if ( 'none' === $provider ) {
			return true;
		}
		if ( 'recaptcha' === $provider ) {
			$secret   = VTD_Options::get( 'captcha_secret', '' );
			$token    = self::field( 'g-recaptcha-response' );
			$response = wp_remote_post(
				'https://www.google.com/recaptcha/api/siteverify',
				array(
					'timeout' => 15,
					'body'    => array(
						'secret'   => $secret,
						'response' => $token,
						'remoteip' => self::client_ip(),
					),
				)
			);
			if ( is_wp_error( $response ) ) {
				return false;
			}
			$data = json_decode( wp_remote_retrieve_body( $response ), true );
			return ! empty( $data['success'] );
		}
		return apply_filters( 'vtd_captcha_verify', true, $provider );
	}

	public static function client_ip() {
		$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
		return $ip;
	}

	protected static function handle_login() {
		if ( ! VTD_Options::get( 'password_login', 1 ) ) {
			self::$errors[] = __( 'Password login is disabled.', 'vetra-dashboard' );
			return;
		}
		$identity = self::field( 'identity' );
		$password = isset( $_POST['password'] ) ? (string) wp_unslash( $_POST['password'] ) : '';
		$remember = ! empty( $_POST['remember'] );

		self::$form_data['identity'] = $identity;

		if ( '' === $identity || '' === $password ) {
			self::$errors[] = __( 'Please enter your credentials.', 'vetra-dashboard' );
			return;
		}
		if ( ! self::captcha_ok() ) {
			self::$errors[] = __( 'Captcha verification failed.', 'vetra-dashboard' );
			return;
		}

		$user = wp_signon(
			array(
				'user_login'    => $identity,
				'user_password' => $password,
				'remember'      => $remember,
			),
			is_ssl()
		);

		if ( is_wp_error( $user ) ) {
			self::$errors[] = __( 'The username or password is incorrect.', 'vetra-dashboard' );
			return;
		}

		do_action( 'vtd_after_login', $user->ID );
		wp_safe_redirect( self::redirect_after_login() );
		exit;
	}

	protected static function handle_otp_request() {
		if ( ! VTD_Options::get( 'otp_login', 1 ) || ! VTD_Options::get( 'phone_login', 1 ) ) {
			self::$errors[] = __( 'OTP login is disabled.', 'vetra-dashboard' );
			return;
		}
		$phone = self::field( 'phone' );
		self::$form_data['phone'] = $phone;

		if ( ! preg_match( '/^9\d{9}$/', vtd_sanitize_phone( $phone ) ) && ! preg_match( '/^09\d{9}$/', $phone ) ) {
			self::$errors[] = __( 'The mobile number is incorrect.', 'vetra-dashboard' );
			return;
		}

		$user = self::find_user_by_phone( $phone );
		if ( ! $user ) {
			self::$errors[] = __( 'There is no user with this mobile number.', 'vetra-dashboard' );
			return;
		}

		$result = VTD_SMS::send_otp( $phone, 'login' );
		if ( is_wp_error( $result ) ) {
			self::$errors[] = $result->get_error_message();
			return;
		}

		self::$stage      = 'otp';
		self::$form_data['phone'] = vtd_sanitize_phone( $phone );
		self::$success[]  = __( 'The verification code was sent.', 'vetra-dashboard' );
	}

	protected static function handle_otp_verify() {
		$phone = self::field( 'phone' );
		$code  = self::field( 'code' );

		$result = VTD_SMS::verify_otp( $phone, $code, 'login' );
		if ( is_wp_error( $result ) ) {
			self::$stage = 'otp';
			self::$form_data['phone'] = $phone;
			self::$errors[] = $result->get_error_message();
			return;
		}

		$user = self::find_user_by_phone( $phone );
		if ( ! $user ) {
			self::$errors[] = __( 'User not found.', 'vetra-dashboard' );
			return;
		}

		$status = get_user_meta( $user->ID, 'vtd_account_status', true );
		if ( in_array( $status, array( 'rejected', 'disabled', 'suspended' ), true ) ) {
			self::$errors[] = __( 'Your account is disabled.', 'vetra-dashboard' );
			return;
		}

		wp_set_current_user( $user->ID );
		wp_set_auth_cookie( $user->ID, true, is_ssl() );
		do_action( 'wp_login', $user->user_login, $user );
		do_action( 'vtd_after_login', $user->ID );
		wp_safe_redirect( self::redirect_after_login() );
		exit;
	}

	protected static function handle_register() {
		if ( ! VTD_Options::get( 'register_enabled', 1 ) ) {
			self::$errors[] = __( 'Registration is currently disabled.', 'vetra-dashboard' );
			return;
		}

		$username   = sanitize_user( self::field( 'username' ) );
		$email      = sanitize_email( self::field( 'email' ) );
		$phone      = self::field( 'phone' );
		$password   = isset( $_POST['password'] ) ? (string) wp_unslash( $_POST['password'] ) : '';
		$first_name = self::field( 'first_name' );
		$last_name  = self::field( 'last_name' );

		$birthday = self::field( 'birthday' );
		self::$form_data = compact( 'username', 'email', 'phone', 'first_name', 'last_name', 'birthday' );

		if ( ! VTD_Options::get( 'register_first_last', 1 ) ) {
			$first_name = '';
			$last_name  = '';
		}

		if ( '' === $username || ! validate_username( $username ) ) {
			self::$errors[] = __( 'Please enter a valid username.', 'vetra-dashboard' );
		}
		if ( VTD_Options::get( 'email_login', 1 ) ) {
			if ( '' === $email || ! is_email( $email ) ) {
				self::$errors[] = __( 'Please enter a valid email.', 'vetra-dashboard' );
			} elseif ( email_exists( $email ) ) {
				self::$errors[] = __( 'This email is already registered.', 'vetra-dashboard' );
			}
		}
		if ( VTD_Options::get( 'phone_login', 1 ) && '' !== $phone ) {
			$normalized = vtd_sanitize_phone( $phone );
			if ( ! preg_match( '/^9\d{9}$/', $normalized ) ) {
				self::$errors[] = __( 'The mobile number is incorrect.', 'vetra-dashboard' );
			} elseif ( self::find_user_by_phone( $phone ) ) {
				self::$errors[] = __( 'This number is already in use.', 'vetra-dashboard' );
			}
		}
		if ( strlen( $password ) < 8 ) {
			self::$errors[] = __( 'Password must be at least 8 characters long.', 'vetra-dashboard' );
		}
		if ( '' !== $birthday && '' === vtd_jalali_to_gregorian( $birthday ) ) {
			self::$errors[] = __( 'لطفاً تاریخ تولد شمسی معتبر وارد کنید.', 'vetra-dashboard' );
		}
		if ( VTD_Options::get( 'register_terms', 1 ) && empty( $_POST['terms'] ) ) {
			self::$errors[] = __( 'Please confirm the rules.', 'vetra-dashboard' );
		}
		if ( ! self::captcha_ok() ) {
			self::$errors[] = __( 'Captcha verification failed.', 'vetra-dashboard' );
		}

		if ( ! empty( self::$errors ) ) {
			return;
		}

		$user_id = wp_insert_user(
			array(
				'user_login' => $username,
				'user_email' => $email,
				'user_pass'  => $password,
				'first_name' => $first_name,
				'last_name'  => $last_name,
				'role'       => apply_filters( 'vtd_default_registered_role', get_option( 'default_role', 'subscriber' ) ),
			)
		);

		if ( is_wp_error( $user_id ) ) {
			self::$errors[] = $user_id->get_error_message();
			return;
		}

		$phone = vtd_sanitize_phone( $phone );
		if ( '' !== $phone ) {
			self::update_phone( $user_id, $phone, VTD_Options::get( 'phone_verify', 1 ) ? 0 : 1 );
		}
		if ( VTD_Options::get( 'email_verify', 1 ) ) {
			update_user_meta( $user_id, self::EMAIL_VERIFIED_META, 0 );
		} else {
			update_user_meta( $user_id, self::EMAIL_VERIFIED_META, 1 );
		}
		update_user_meta( $user_id, 'vtd_birthday', '' !== $birthday ? vtd_jalali_to_gregorian( $birthday ) : '' );

		do_action( 'vtd_user_registered', $user_id );

		if ( VTD_Options::get( 'email_on_signup', 1 ) ) {
			VTD_SMS::send( $phone, __( 'Your account was created successfully.', 'vetra-dashboard' ), 'signup' );
		}

		$auto_login = (bool) apply_filters( 'vtd_auto_login_after_register', true, $user_id );
		if ( $auto_login ) {
			wp_set_current_user( $user_id );
			wp_set_auth_cookie( $user_id, true, is_ssl() );
			do_action( 'wp_login', $username, get_userdata( $user_id ) );
			wp_safe_redirect( self::redirect_after_register() );
			exit;
		}

		self::$success[] = __( 'Registration completed successfully. Please log in.', 'vetra-dashboard' );
	}

	protected static function handle_reset_request() {
		$identity = self::field( 'identity' );
		$user     = null;

		if ( is_email( $identity ) ) {
			$user = get_user_by( 'email', $identity );
		} elseif ( preg_match( '/\d{10,}/', vtd_sanitize_phone( $identity ) ) ) {
			$user = self::find_user_by_phone( $identity );
		} else {
			$user = get_user_by( 'login', $identity );
		}

		if ( ! $user ) {
			self::$errors[] = __( 'No user found with the provided information.', 'vetra-dashboard' );
			return;
		}

		$phone = self::get_phone( $user->ID );
		$key   = get_password_reset_key( $user );

		if ( $phone && VTD_SMS::enabled() ) {
			$result = VTD_SMS::send_otp( $phone, 'reset' );
			if ( is_wp_error( $result ) ) {
				self::$errors[] = $result->get_error_message();
				return;
			}
			set_transient( 'vtd_reset_user_' . vtd_sanitize_phone( $phone ), $user->ID, HOUR_IN_SECONDS );
			self::$stage             = 'reset';
			self::$form_data['phone'] = vtd_sanitize_phone( $phone );
			self::$success[]         = __( 'The verification code was sent to your mobile number.', 'vetra-dashboard' );
			return;
		}

		$url = add_query_arg(
			array(
				'key'   => $key,
				'login' => rawurlencode( $user->user_login ),
			),
			VTD_Router::reset_url()
		);

		if ( is_email( $user->user_email ) ) {
			wp_mail(
				$user->user_email,
				__( 'Password reset', 'vetra-dashboard' ),
				sprintf( __( 'Reset your password using this link: %s', 'vetra-dashboard' ), $url )
			);
		}

		self::$success[] = __( 'The reset link was sent to your email.', 'vetra-dashboard' );
	}

	protected static function handle_reset_set() {
		$phone = self::field( 'phone' );
		$code  = self::field( 'code' );
		$pass  = isset( $_POST['password'] ) ? (string) wp_unslash( $_POST['password'] ) : '';
		$pass2 = isset( $_POST['password_confirm'] ) ? (string) wp_unslash( $_POST['password_confirm'] ) : '';

		if ( strlen( $pass ) < 8 ) {
			self::$errors[] = __( 'Password must be at least 8 characters long.', 'vetra-dashboard' );
			return;
		}
		if ( $pass !== $pass2 ) {
			self::$errors[] = __( 'Passwords do not match.', 'vetra-dashboard' );
			return;
		}

		$verify = VTD_SMS::verify_otp( $phone, $code, 'reset' );
		if ( is_wp_error( $verify ) ) {
			self::$stage = 'reset';
			self::$form_data['phone'] = $phone;
			self::$errors[] = $verify->get_error_message();
			return;
		}

		$user_id = (int) get_transient( 'vtd_reset_user_' . vtd_sanitize_phone( $phone ) );
		if ( ! $user_id ) {
			self::$errors[] = __( 'Your link has expired. Please try again.', 'vetra-dashboard' );
			return;
		}

		wp_set_password( $pass, $user_id );
		delete_transient( 'vtd_reset_user_' . vtd_sanitize_phone( $phone ) );
		self::$success[] = __( 'Your password has been changed successfully.', 'vetra-dashboard' );
	}

	public static function redirect_after_login() {
		$target = VTD_Options::get( 'after_login_redirect', 'panel' );
		if ( 'admin' === $target && current_user_can( 'manage_options' ) ) {
			return admin_url();
		}
		return apply_filters( 'vtd_login_redirect_url', VTD_Router::panel_url() );
	}

	public static function redirect_after_register() {
		$page = VTD_Options::get( 'after_register_page', '' );
		if ( ! empty( $page ) ) {
			$url = get_permalink( (int) $page );
			if ( $url ) {
				return $url;
			}
		}
		return apply_filters( 'vtd_register_redirect_url', VTD_Router::panel_url() );
	}

	public static function logout_url() {
		return VTD_Router::logout_url();
	}
}
