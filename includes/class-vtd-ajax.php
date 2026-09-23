<?php
/**
 * REST API endpoints for the panel.
 *
 * @package Vetra_Dashboard
 */

defined( 'ABSPATH' ) || exit;

class VTD_Ajax {

	const NS = 'vetra/v1';

	public static function init() {
		add_action( 'rest_api_init', array( __CLASS__, 'register' ) );
	}

	public static function register() {
		$auth = array( __CLASS__, 'permission_logged_in' );
		$open = '__return_true';

		register_rest_route( self::NS, '/otp/send', array( 'methods' => 'POST', 'callback' => array( __CLASS__, 'otp_send' ), 'permission_callback' => $open ) );
		register_rest_route( self::NS, '/otp/verify', array( 'methods' => 'POST', 'callback' => array( __CLASS__, 'otp_verify' ), 'permission_callback' => $open ) );

		register_rest_route( self::NS, '/notifications', array( 'methods' => 'GET', 'callback' => array( __CLASS__, 'notifications' ), 'permission_callback' => $auth ) );
		register_rest_route( self::NS, '/notifications/read', array( 'methods' => 'POST', 'callback' => array( __CLASS__, 'notifications_read' ), 'permission_callback' => $auth ) );

		register_rest_route( self::NS, '/profile', array( 'methods' => 'POST', 'callback' => array( __CLASS__, 'profile_update' ), 'permission_callback' => $auth ) );
		register_rest_route( self::NS, '/profile/password', array( 'methods' => 'POST', 'callback' => array( __CLASS__, 'profile_password' ), 'permission_callback' => $auth ) );
		register_rest_route( self::NS, '/profile/avatar', array( 'methods' => 'POST', 'callback' => array( __CLASS__, 'profile_avatar' ), 'permission_callback' => $auth ) );
		register_rest_route( self::NS, '/profile/verify', array( 'methods' => 'POST', 'callback' => array( __CLASS__, 'profile_verify' ), 'permission_callback' => $auth ) );

		register_rest_route( self::NS, '/tickets', array( 'methods' => 'POST', 'callback' => array( __CLASS__, 'ticket_create' ), 'permission_callback' => $auth ) );
		register_rest_route( self::NS, '/tickets/reply', array( 'methods' => 'POST', 'callback' => array( __CLASS__, 'ticket_reply' ), 'permission_callback' => $auth ) );
		register_rest_route( self::NS, '/tickets/close', array( 'methods' => 'POST', 'callback' => array( __CLASS__, 'ticket_close' ), 'permission_callback' => $auth ) );
		register_rest_route( self::NS, '/tickets/rate', array( 'methods' => 'POST', 'callback' => array( __CLASS__, 'ticket_rate' ), 'permission_callback' => $auth ) );
		register_rest_route( self::NS, '/tickets/star', array( 'methods' => 'POST', 'callback' => array( __CLASS__, 'ticket_star' ), 'permission_callback' => $auth ) );

		register_rest_route( self::NS, '/polls/vote', array( 'methods' => 'POST', 'callback' => array( __CLASS__, 'poll_vote' ), 'permission_callback' => $auth ) );

		register_rest_route( self::NS, '/banking/cards', array( 'methods' => 'POST', 'callback' => array( __CLASS__, 'card_save' ), 'permission_callback' => $auth ) );
		register_rest_route( self::NS, '/banking/cards/delete', array( 'methods' => 'POST', 'callback' => array( __CLASS__, 'card_delete' ), 'permission_callback' => $auth ) );

		register_rest_route( self::NS, '/wallet/withdraw', array( 'methods' => 'POST', 'callback' => array( __CLASS__, 'wallet_withdraw' ), 'permission_callback' => $auth ) );

		register_rest_route( self::NS, '/attachment/download', array( 'methods' => 'POST', 'callback' => array( __CLASS__, 'attachment_download' ), 'permission_callback' => $auth ) );
	}

	public static function permission_logged_in() {
		return is_user_logged_in();
	}

	protected static function param( $request, $key, $default = '' ) {
		$value = $request->get_param( $key );
		return null === $value ? $default : $value;
	}

	protected static function error( $wp ) {
		$code = $wp->get_error_code();
		$data = $wp->get_error_data();
		$status = is_array( $data ) && isset( $data['status'] ) ? (int) $data['status'] : 400;
		return new WP_REST_Response( array( 'success' => false, 'code' => $code, 'message' => $wp->get_error_message() ), $status );
	}

	protected static function ok( $data = array(), $message = '' ) {
		return new WP_REST_Response( array_merge( array( 'success' => true, 'message' => $message ), $data ), 200 );
	}

	public static function otp_send( $request ) {
		$phone   = vtd_sanitize_phone( self::param( $request, 'phone' ) );
		$purpose = sanitize_key( self::param( $request, 'purpose', 'login' ) );

		if ( ! preg_match( '/^9\d{9}$/', $phone ) ) {
			return new WP_REST_Response( array( 'success' => false, 'message' => __( 'The mobile number is incorrect.', 'vetra-dashboard' ) ), 400 );
		}

		$limit_key = 'vtd_otp_' . md5( $phone . '|' . self::client_ip() );
		if ( get_transient( $limit_key ) ) {
			return new WP_REST_Response( array( 'success' => false, 'message' => __( 'Please wait before requesting a new code.', 'vetra-dashboard' ) ), 429 );
		}
		set_transient( $limit_key, 1, (int) VTD_Options::get( 'sms_otp_resend', 60 ) );

		if ( ! VTD_SMS::enabled() ) {
			return new WP_REST_Response( array( 'success' => false, 'message' => __( 'SMS service is disabled.', 'vetra-dashboard' ) ), 400 );
		}

		$result = VTD_SMS::send_otp( $phone, $purpose );
		if ( is_wp_error( $result ) ) {
			return self::error( $result );
		}
		return self::ok( array( 'resend' => (int) VTD_Options::get( 'sms_otp_resend', 60 ) ), __( 'The verification code was sent.', 'vetra-dashboard' ) );
	}

	public static function otp_verify( $request ) {
		$phone   = vtd_sanitize_phone( self::param( $request, 'phone' ) );
		$code    = self::param( $request, 'code' );
		$purpose = sanitize_key( self::param( $request, 'purpose', 'login' ) );

		$result = VTD_SMS::verify_otp( $phone, $code, $purpose );
		if ( is_wp_error( $result ) ) {
			return self::error( $result );
		}
		return self::ok( array(), __( 'Verified successfully.', 'vetra-dashboard' ) );
	}

	public static function notifications( $request ) {
		return self::ok( array( 'items' => VTD_Notifications::api_list( get_current_user_id() ) ) );
	}

	public static function notifications_read( $request ) {
		$id = (int) self::param( $request, 'id', 0 );
		VTD_Notifications::mark_read( get_current_user_id(), $id );
		return self::ok( array(), __( 'Done.', 'vetra-dashboard' ) );
	}

	public static function profile_update( $request ) {
		$result = VTD_Profile::save( get_current_user_id(), $request->get_params() );
		if ( is_wp_error( $result ) ) {
			return self::error( $result );
		}
		return self::ok( array(), __( 'Profile updated successfully.', 'vetra-dashboard' ) );
	}

	public static function profile_password( $request ) {
		$result = VTD_Profile::change_password(
			get_current_user_id(),
			self::param( $request, 'old_password' ),
			self::param( $request, 'new_password' )
		);
		if ( is_wp_error( $result ) ) {
			return self::error( $result );
		}
		return self::ok( array(), __( 'Your password has been changed.', 'vetra-dashboard' ) );
	}

	public static function profile_avatar( $request ) {
		$result = VTD_Profile::save_avatar( get_current_user_id(), $request );
		if ( is_wp_error( $result ) ) {
			return self::error( $result );
		}
		return self::ok( array( 'url' => $result ), __( 'Avatar updated.', 'vetra-dashboard' ) );
	}

	public static function profile_verify( $request ) {
		$result = VTD_Profile::verify_field(
			get_current_user_id(),
			sanitize_key( self::param( $request, 'field' ) ),
			self::param( $request, 'value' ),
			self::param( $request, 'code' )
		);
		if ( is_wp_error( $result ) ) {
			return self::error( $result );
		}
		return self::ok( array( 'sent' => ! empty( $result['sent'] ) ), $result['message'] );
	}

	public static function ticket_create( $request ) {
		$result = VTD_Tickets::create( get_current_user_id(), $request->get_params() );
		if ( is_wp_error( $result ) ) {
			return self::error( $result );
		}
		return self::ok( array( 'id' => $result, 'redirect' => vtd_panel_url( array( 'vtd' => 'ticket', 'ticket' => $result ) ) ), __( 'Ticket submitted successfully.', 'vetra-dashboard' ) );
	}

	public static function ticket_reply( $request ) {
		$result = VTD_Tickets::reply( get_current_user_id(), (int) self::param( $request, 'ticket_id', 0 ), self::param( $request, 'content' ), $request->get_params() );
		if ( is_wp_error( $result ) ) {
			return self::error( $result );
		}
		return self::ok( array(), __( 'Your reply has been sent.', 'vetra-dashboard' ) );
	}

	public static function ticket_close( $request ) {
		$result = VTD_Tickets::close( get_current_user_id(), (int) self::param( $request, 'ticket_id', 0 ) );
		if ( is_wp_error( $result ) ) {
			return self::error( $result );
		}
		return self::ok( array(), __( 'Ticket closed.', 'vetra-dashboard' ) );
	}

	public static function ticket_rate( $request ) {
		$result = VTD_Tickets::rate( get_current_user_id(), (int) self::param( $request, 'ticket_id', 0 ), (int) self::param( $request, 'score', 0 ), self::param( $request, 'feedback' ) );
		if ( is_wp_error( $result ) ) {
			return self::error( $result );
		}
		return self::ok( array(), __( 'Your feedback has been submitted.', 'vetra-dashboard' ) );
	}

	public static function ticket_star( $request ) {
		$result = VTD_Tickets::toggle_star( get_current_user_id(), (int) self::param( $request, 'ticket_id', 0 ) );
		if ( is_wp_error( $result ) ) {
			return self::error( $result );
		}
		return self::ok( array( 'starred' => $result ) );
	}

	public static function poll_vote( $request ) {
		$result = VTD_Polls::vote( get_current_user_id(), (int) self::param( $request, 'poll_id', 0 ), (array) self::param( $request, 'choices', array() ) );
		if ( is_wp_error( $result ) ) {
			return self::error( $result );
		}
		return self::ok( array(), __( 'Your vote has been recorded.', 'vetra-dashboard' ) );
	}

	public static function card_save( $request ) {
		$result = VTD_Banking::save( get_current_user_id(), $request->get_params() );
		if ( is_wp_error( $result ) ) {
			return self::error( $result );
		}
		return self::ok( array( 'id' => $result ), __( 'Bank card added.', 'vetra-dashboard' ) );
	}

	public static function card_delete( $request ) {
		$result = VTD_Banking::delete( get_current_user_id(), (int) self::param( $request, 'card_id', 0 ) );
		if ( is_wp_error( $result ) ) {
			return self::error( $result );
		}
		return self::ok( array(), __( 'The deletion was successful.', 'vetra-dashboard' ) );
	}

	public static function wallet_withdraw( $request ) {
		$result = VTD_Wallet::request_withdrawal( get_current_user_id(), (float) self::param( $request, 'amount', 0 ), (int) self::param( $request, 'card_id', 0 ), self::param( $request, 'note' ) );
		if ( is_wp_error( $result ) ) {
			return self::error( $result );
		}
		return self::ok( array(), __( 'Withdrawal request submitted.', 'vetra-dashboard' ) );
	}

	public static function attachment_download( $request ) {
		$result = VTD_Attachments::authorize( get_current_user_id(), (int) self::param( $request, 'id', 0 ) );
		if ( is_wp_error( $result ) ) {
			return self::error( $result );
		}
		return self::ok( array( 'url' => $result ) );
	}

	protected static function client_ip() {
		return isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	}
}
