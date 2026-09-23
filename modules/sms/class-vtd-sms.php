<?php
/**
 * SMS service facade with pluggable providers.
 *
 * @package Vetra_Dashboard
 */

defined( 'ABSPATH' ) || exit;

interface VTD_SMS_Provider {
	public function send( $phone, $message, $args = array() );
}

require_once VTD_MODULES . 'sms/class-vtd-sms-ippanel.php';
require_once VTD_MODULES . 'sms/class-vtd-sms-webhook.php';

class VTD_SMS {

	public static function init() {
		add_action( 'vtd_sms_send', array( __CLASS__, 'cron_send' ), 10, 3 );
		add_action( 'vtd_cleanup_otp', array( __CLASS__, 'cleanup_otp' ) );
		add_action( 'init', array( __CLASS__, 'schedule_cleanup' ) );
	}

	public static function schedule_cleanup() {
		if ( ! wp_next_scheduled( 'vtd_cleanup_otp' ) ) {
			wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', 'vtd_cleanup_otp' );
		}
	}

	public static function cleanup_otp() {
		global $wpdb;
		$table = VTD_DB::otp();
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$table} WHERE expires_at < %s", gmdate( 'Y-m-d H:i:s', current_time( 'timestamp', true ) - HOUR_IN_SECONDS ) ) );
	}

	public static function enabled() {
		return (bool) VTD_Options::get( 'sms_enabled', 0 ) && '' !== (string) VTD_Options::get( 'sms_api_key', '' );
	}

	public static function providers() {
		return apply_filters(
			'vtd_sms_providers',
			array(
				'ippanel' => __( 'IPPanel (edge.ippanel.com)', 'vetra-dashboard' ),
				'webhook' => __( 'Custom webhook', 'vetra-dashboard' ),
			)
		);
	}

	public static function provider() {
		$name = VTD_Options::get( 'sms_provider', 'ippanel' );
		switch ( $name ) {
			case 'webhook':
				return new VTD_SMS_Webhook();
			case 'ippanel':
			default:
				return new VTD_SMS_IPPanel();
		}
	}

	public static function send( $phone, $message, $context = 'general', $args = array() ) {
		if ( ! self::enabled() ) {
			return new WP_Error( 'vtd_sms_disabled', __( 'SMS service is disabled.', 'vetra-dashboard' ) );
		}

		$phone = vtd_sanitize_phone( $phone );
		if ( '' === $phone ) {
			return new WP_Error( 'vtd_sms_phone', __( 'Invalid phone number.', 'vetra-dashboard' ) );
		}

		$provider = self::provider();
		$result   = $provider->send( $phone, $message, $args );

		self::log( $phone, $message, $context, $result );

		return $result;
	}

	public static function send_otp( $phone, $purpose = 'login' ) {
		$phone = vtd_sanitize_phone( $phone );
		if ( '' === $phone ) {
			return new WP_Error( 'vtd_sms_phone', __( 'Invalid phone number.', 'vetra-dashboard' ) );
		}

		$length = (int) VTD_Options::get( 'sms_otp_length', 5 );
		$code   = vtd_random_code( $length );
		$expiry = (int) VTD_Options::get( 'sms_otp_expiry', 120 );
		$now    = current_time( 'timestamp' );

		self::save_otp( $phone, $code, $purpose, $now + $expiry );

		$pattern = (string) VTD_Options::get( 'sms_pattern_otp', '' );
		$variable = (string) VTD_Options::get( 'sms_otp_variable', 'code' );

		if ( self::enabled() ) {
			if ( '' !== $pattern && 'ippanel' === VTD_Options::get( 'sms_provider', 'ippanel' ) ) {
				$result = self::provider()->send(
					$phone,
					'',
					array(
						'pattern' => $pattern,
						'params'  => array( $variable => $code ),
					)
				);
				self::log( $phone, 'OTP pattern ' . $pattern, $purpose, $result );
			} else {
				$message = sprintf(
					/* translators: %s code */
					__( 'Your verification code is: %s', 'vetra-dashboard' ),
					$code
				);
				self::send( $phone, $message, $purpose );
			}
		}

		do_action( 'vtd_otp_generated', $phone, $code, $purpose );

		return array(
			'code'   => $code,
			'expiry' => $expiry,
		);
	}

	protected static function save_otp( $phone, $code, $purpose, $expires ) {
		global $wpdb;
		$table = VTD_DB::otp();
		$now   = current_time( 'mysql' );
		$wpdb->delete( $table, array( 'phone' => $phone, 'purpose' => $purpose ) );
		$wpdb->insert(
			$table,
			array(
				'phone'      => $phone,
				'code'       => $code,
				'purpose'    => $purpose,
				'tries'      => 0,
				'expires_at' => gmdate( 'Y-m-d H:i:s', $expires ),
				'created_at' => $now,
			),
			array( '%s', '%s', '%s', '%d', '%s', '%s' )
		);
	}

	public static function verify_otp( $phone, $code, $purpose = 'login' ) {
		global $wpdb;
		$phone = vtd_sanitize_phone( $phone );
		$table = VTD_DB::otp();
		$row   = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE phone = %s AND purpose = %s ORDER BY id DESC LIMIT 1",
				$phone,
				$purpose
			)
		);

		if ( ! $row ) {
			return new WP_Error( 'vtd_otp_missing', __( 'No verification code found.', 'vetra-dashboard' ) );
		}

		if ( strtotime( $row->expires_at . ' UTC' ) < current_time( 'timestamp', true ) ) {
			return new WP_Error( 'vtd_otp_expired', __( 'The verification code has expired.', 'vetra-dashboard' ) );
		}

		if ( (int) $row->tries >= 5 ) {
			return new WP_Error( 'vtd_otp_tries', __( 'Too many attempts. Request a new code.', 'vetra-dashboard' ) );
		}

		$wpdb->update( $table, array( 'tries' => (int) $row->tries + 1 ), array( 'id' => $row->id ), array( '%d' ), array( '%d' ) );

		if ( (string) $row->code !== (string) $code ) {
			return new WP_Error( 'vtd_otp_invalid', __( 'The verification code is incorrect.', 'vetra-dashboard' ) );
		}

		$wpdb->delete( $table, array( 'id' => $row->id ) );
		return true;
	}

	public static function log( $phone, $message, $context, $result ) {
		if ( ! VTD_Options::get( 'sms_log', 1 ) ) {
			return;
		}
		global $wpdb;
		$status = is_wp_error( $result ) ? 'failed' : 'sent';
		$wpdb->insert(
			VTD_DB::sms_log(),
			array(
				'phone'      => $phone,
				'message'    => is_scalar( $message ) ? (string) $message : wp_json_encode( $message ),
				'provider'   => VTD_Options::get( 'sms_provider', 'ippanel' ),
				'status'     => $status,
				'response'   => is_wp_error( $result ) ? $result->get_error_message() : wp_json_encode( $result ),
				'context'    => $context,
				'created_at' => current_time( 'mysql' ),
			),
			array( '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
		);
	}

	public static function cron_send( $phone, $message, $context ) {
		self::send( $phone, $message, $context );
	}

	public static function test( $phone ) {
		return self::send( $phone, __( 'Vetra Dashboard test message.', 'vetra-dashboard' ), 'test' );
	}
}
