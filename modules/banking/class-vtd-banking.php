<?php
/**
 * Bank cards module.
 *
 * @package Vetra_Dashboard
 */

defined( 'ABSPATH' ) || exit;

class VTD_Banking {

	const STATUSES = array( 'pending', 'approved', 'rejected' );

	public static function init() {}

	public static function banks() {
		return apply_filters(
			'vtd_banks',
			array(
				'melli'   => array( 'name' => 'ملی', 'color' => '#d81b60' ),
				'sepah'   => array( 'name' => 'سپه', 'color' => '#1a3f8c' ),
				'keshavarzi' => array( 'name' => 'کشاورزی', 'color' => '#1b5e20' ),
				'maskan'  => array( 'name' => 'مسکن', 'color' => '#f9a825' ),
				'eghtesadnovin' => array( 'name' => 'اقتصاد نوین', 'color' => '#6a1b9a' ),
				'parsian' => array( 'name' => 'پارسیان', 'color' => '#ad1457' ),
				'pasargad' => array( 'name' => 'پاسارگاد', 'color' => '#283593' ),
				'saman'   => array( 'name' => 'سامان', 'color' => '#00838f' ),
				'sina'    => array( 'name' => 'سینا', 'color' => '#0277bd' ),
				'shahr'   => array( 'name' => 'شهر', 'color' => '#00897b' ),
				'saderat' => array( 'name' => 'صادرات', 'color' => '#3949ab' ),
				'mellat'  => array( 'name' => 'ملت', 'color' => '#c62828' ),
				'tejarat' => array( 'name' => 'تجارت', 'color' => '#f57f17' ),
				'refah'   => array( 'name' => 'رفاه', 'color' => '#2e7d32' ),
				'mehriran' => array( 'name' => 'مهر ایران', 'color' => '#5e35b1' ),
				'resalat' => array( 'name' => 'رسالت', 'color' => '#455a64' ),
			)
		);
	}

	public static function bank_label( $key ) {
		$banks = self::banks();
		$key   = strtolower( $key );
		return isset( $banks[ $key ] ) ? $banks[ $key ]['name'] : $key;
	}

	public static function bank_color( $key ) {
		$banks = self::banks();
		$key   = strtolower( $key );
		return isset( $banks[ $key ] ) ? $banks[ $key ]['color'] : '#6d28d9';
	}

	public static function get_cards( $user_id ) {
		global $wpdb;
		$table = VTD_DB::cards();
		$rows  = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$table} WHERE user_id = %d ORDER BY card_id DESC", $user_id ) );
		return is_array( $rows ) ? $rows : array();
	}

	public static function get_card( $card_id ) {
		global $wpdb;
		$table = VTD_DB::cards();
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE card_id = %d", $card_id ) );
	}

	public static function save( $user_id, $data ) {
		if ( ! VTD_Options::get( 'banking_enabled', 1 ) ) {
			return new WP_Error( 'vtd_banking_disabled', __( 'Banking is disabled.', 'vetra-dashboard' ) );
		}

		$bank   = sanitize_key( $data['bank_name'] ?? '' );
		$owner  = sanitize_text_field( $data['card_owner'] ?? '' );
		$number = preg_replace( '/\D/', '', $data['card_number'] ?? '' );
		$sheba  = preg_replace( '/\D/', '', $data['card_sheba'] ?? '' );

		if ( ! array_key_exists( $bank, self::banks() ) ) {
			return new WP_Error( 'vtd_banking_bank', __( 'Please select a bank.', 'vetra-dashboard' ) );
		}
		if ( '' === $owner ) {
			return new WP_Error( 'vtd_banking_owner', __( 'Please enter the card owner.', 'vetra-dashboard' ) );
		}
		if ( 16 !== strlen( $number ) ) {
			return new WP_Error( 'vtd_banking_number', __( 'Please enter a valid card number.', 'vetra-dashboard' ) );
		}
		if ( 24 !== strlen( $sheba ) ) {
			return new WP_Error( 'vtd_banking_sheba', __( 'Please enter a valid Sheba number.', 'vetra-dashboard' ) );
		}

		global $wpdb;
		$table     = VTD_DB::cards();
		$photo_id  = isset( $data['card_photo'] ) ? (int) $data['card_photo'] : 0;
		$existing  = $wpdb->get_var( $wpdb->prepare( "SELECT card_id FROM {$table} WHERE user_id = %d AND card_number = %s", $user_id, $number ) );

		if ( $existing ) {
			$wpdb->update(
				$table,
				array(
					'card_name'   => $bank,
					'card_owner'  => $owner,
					'card_number' => $number,
					'card_sheba'  => $sheba,
					'card_photo'  => $photo_id ? $photo_id : null,
					'card_status' => 'pending',
				),
				array( 'card_id' => $existing ),
				array( '%s', '%s', '%s', '%s', '%d', '%s' ),
				array( '%d' )
			);
			$card_id = (int) $existing;
		} else {
			$wpdb->insert(
				$table,
				array(
					'user_id'     => $user_id,
					'card_name'   => $bank,
					'card_owner'  => $owner,
					'card_number' => $number,
					'card_sheba'  => $sheba,
					'card_photo'  => $photo_id ? $photo_id : null,
					'card_status' => 'pending',
					'created_at'  => current_time( 'mysql' ),
				),
				array( '%d', '%s', '%s', '%s', '%s', '%d', '%s', '%s' )
			);
			$card_id = (int) $wpdb->insert_id;
		}

		do_action( 'vtd_card_saved', $card_id, $user_id );
		return $card_id;
	}

	public static function delete( $user_id, $card_id ) {
		global $wpdb;
		$card = self::get_card( $card_id );
		if ( ! $card || (int) $card->user_id !== (int) $user_id ) {
			return new WP_Error( 'vtd_card_denied', __( 'You do not have access.', 'vetra-dashboard' ), array( 'status' => 403 ) );
		}
		$wpdb->delete( VTD_DB::cards(), array( 'card_id' => $card_id ), array( '%d' ) );
		return true;
	}

	public static function set_status( $card_id, $status ) {
		if ( ! in_array( $status, self::STATUSES, true ) ) {
			return false;
		}
		global $wpdb;
		$wpdb->update( VTD_DB::cards(), array( 'card_status' => $status ), array( 'card_id' => $card_id ), array( '%s' ), array( '%d' ) );
		do_action( 'vtd_card_status_changed', $card_id, $status );
		return true;
	}

	public static function render() {
		$user_id = get_current_user_id();
		return VTD_Templates::module(
			'banking',
			array(
				'cards' => self::get_cards( $user_id ),
				'banks' => self::banks(),
			)
		);
	}
}
