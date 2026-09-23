<?php
/**
 * Generic SMS webhook provider.
 *
 * @package Vetra_Dashboard
 */

defined( 'ABSPATH' ) || exit;

class VTD_SMS_Webhook implements VTD_SMS_Provider {

	public function send( $phone, $message, $args = array() ) {
		$endpoint = (string) apply_filters( 'vtd_sms_webhook_url', VTD_Options::get( 'sms_base_url', '' ) );
		if ( '' === $endpoint ) {
			return new WP_Error( 'vtd_sms_webhook', __( 'Webhook URL is missing.', 'vetra-dashboard' ) );
		}

		$body = array(
			'to'      => $phone,
			'message' => (string) $message,
			'args'    => (array) $args,
			'api_key' => (string) VTD_Options::get( 'sms_api_key', '' ),
			'sender'  => (string) VTD_Options::get( 'sms_sender', '' ),
		);

		$response = wp_remote_post(
			$endpoint,
			array(
				'timeout' => 20,
				'headers' => array( 'Content-Type' => 'application/json' ),
				'body'    => wp_json_encode( $body ),
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$code = (int) wp_remote_retrieve_response_code( $response );
		if ( $code >= 200 && $code < 300 ) {
			return array( 'success' => true, 'response' => json_decode( wp_remote_retrieve_body( $response ), true ) );
		}

		return new WP_Error( 'vtd_sms_webhook_http', __( 'Webhook request failed.', 'vetra-dashboard' ), array( 'code' => $code ) );
	}
}
