<?php
/**
 * IPPanel SMS provider (edge + legacy APIs).
 *
 * @package Vetra_Dashboard
 */

defined( 'ABSPATH' ) || exit;

class VTD_SMS_IPPanel implements VTD_SMS_Provider {

	public function send( $phone, $message, $args = array() ) {
		$api_key = (string) VTD_Options::get( 'sms_api_key', '' );
		$sender  = (string) VTD_Options::get( 'sms_sender', '' );
		$version = (string) VTD_Options::get( 'sms_api_version', 'edge' );

		if ( '' === $api_key ) {
			return new WP_Error( 'vtd_sms_key', __( 'IPPanel API key is missing.', 'vetra-dashboard' ) );
		}
		if ( '' === $sender ) {
			return new WP_Error( 'vtd_sms_sender', __( 'Sender number is missing.', 'vetra-dashboard' ) );
		}

		if ( 'legacy' === $version ) {
			return $this->send_legacy( $phone, $message, $args, $api_key, $sender );
		}

		return $this->send_edge( $phone, $message, $args, $api_key, $sender );
	}

	protected function send_edge( $phone, $message, $args, $api_key, $sender ) {
		$base = untrailingslashit( (string) VTD_Options::get( 'sms_base_url', 'https://edge.ippanel.com/v1' ) );
		$url  = $base . '/api/send';
		$args = is_array( $args ) ? $args : array();

		if ( ! empty( $args['pattern'] ) ) {
			$body = array(
				'sending_type' => 'pattern',
				'from_number'  => $this->e164_sender( $sender ),
				'code'         => (string) $args['pattern'],
				'recipients'   => array( vtd_e164( $phone ) ),
				'params'       => isset( $args['params'] ) ? (array) $args['params'] : array(),
			);
		} else {
			$body = array(
				'sending_type' => 'webservice',
				'from_number'  => $this->e164_sender( $sender ),
				'message'      => (string) $message,
				'params'       => array(
					'recipients' => array( vtd_e164( $phone ) ),
				),
			);
			if ( ! empty( $args['send_time'] ) ) {
				$body['send_time'] = (string) $args['send_time'];
			}
		}

		$response = wp_remote_post(
			$url,
			array(
				'timeout' => 20,
				'headers' => array(
					'Authorization' => $api_key,
					'Content-Type'  => 'application/json',
					'Accept'        => 'application/json',
				),
				'body'    => wp_json_encode( $body ),
			)
		);

		return $this->parse( $response, $body );
	}

	protected function send_legacy( $phone, $message, $args, $api_key, $sender ) {
		$args = is_array( $args ) ? $args : array();
		if ( ! empty( $args['pattern'] ) ) {
			$url  = 'https://api2.ippanel.com/api/v1/sms/pattern/normal/send';
			$body = array(
				'code'       => (string) $args['pattern'],
				'sender'     => ltrim( $sender, '+' ),
				'recipient'  => vtd_local_phone( $phone ),
				'variable'   => isset( $args['params'] ) ? (array) $args['params'] : array(),
			);
		} else {
			$url  = 'https://api2.ippanel.com/api/v1/sms/send/webservice/single';
			$body = array(
				'sender'    => ltrim( $sender, '+' ),
				'recipient' => vtd_local_phone( $phone ),
				'message'   => (string) $message,
			);
		}

		$response = wp_remote_post(
			$url,
			array(
				'timeout' => 20,
				'headers' => array(
					'apikey'       => $api_key,
					'Content-Type' => 'application/json',
					'Accept'       => 'application/json',
				),
				'body'    => wp_json_encode( $body ),
			)
		);

		return $this->parse( $response, $body );
	}

	protected function e164_sender( $sender ) {
		$sender = trim( $sender );
		if ( '' === $sender ) {
			return '';
		}
		if ( 0 === strpos( $sender, '+' ) ) {
			return $sender;
		}
		if ( 0 === strpos( $sender, '98' ) ) {
			return '+' . $sender;
		}
		if ( 0 === strpos( $sender, '0' ) ) {
			return '+98' . substr( $sender, 1 );
		}
		return '+' . $sender;
	}

	protected function parse( $response, $body ) {
		if ( is_wp_error( $response ) ) {
			return $response;
		}
		$code = (int) wp_remote_retrieve_response_code( $response );
		$raw  = wp_remote_retrieve_body( $response );
		$data = json_decode( $raw, true );

		if ( $code >= 200 && $code < 300 ) {
			if ( is_array( $data ) && isset( $data['meta']['status'] ) && false === $data['meta']['status'] ) {
				$message = $data['meta']['message'] ?? __( 'IPPanel rejected the message.', 'vetra-dashboard' );
				return new WP_Error( 'vtd_sms_provider', $message, $data );
			}
			return array( 'success' => true, 'response' => $data );
		}

		$message = is_array( $data ) && isset( $data['meta']['message'] ) ? $data['meta']['message'] : __( 'IPPanel request failed.', 'vetra-dashboard' );
		return new WP_Error( 'vtd_sms_http', $message, array( 'code' => $code, 'body' => $data ) );
	}
}
