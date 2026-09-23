<?php
/**
 * Wallet module.
 *
 * @package Vetra_Dashboard
 */

defined( 'ABSPATH' ) || exit;

class VTD_Wallet {

	public static function init() {
		add_action( 'vtd_user_registered', array( __CLASS__, 'create_wallet' ) );
	}

	public static function create_wallet( $user_id ) {
		global $wpdb;
		$table = VTD_DB::wallets();
		$exists = $wpdb->get_var( $wpdb->prepare( "SELECT wallet_id FROM {$table} WHERE user_id = %d", $user_id ) );
		if ( ! $exists ) {
			$wpdb->insert( $table, array( 'user_id' => $user_id, 'balance' => 0 ), array( '%d', '%f' ) );
		}
	}

	public static function balance( $user_id ) {
		global $wpdb;
		$table = VTD_DB::wallets();
		$balance = $wpdb->get_var( $wpdb->prepare( "SELECT balance FROM {$table} WHERE user_id = %d", $user_id ) );
		if ( null === $balance ) {
			self::create_wallet( $user_id );
			$balance = 0;
		}
		return (float) $balance;
	}

	public static function format( $amount ) {
		$currency = VTD_Options::get( 'wallet_currency', 'تومان' );
		return number_format_i18n( (float) $amount ) . ' ' . $currency;
	}

	public static function add_transaction( $user_id, $amount, $type, $details = '', $created_by = 0 ) {
		global $wpdb;
		$amount  = (float) $amount;
		$balance = self::balance( $user_id );

		if ( 'credit' === $type ) {
			$balance += $amount;
		} else {
			if ( $amount > $balance ) {
				return new WP_Error( 'vtd_wallet_insufficient', __( 'Insufficient balance.', 'vetra-dashboard' ) );
			}
			$balance -= $amount;
		}

		$wpdb->update( VTD_DB::wallets(), array( 'balance' => $balance ), array( 'user_id' => $user_id ), array( '%f' ), array( '%d' ) );

		$wpdb->insert(
			VTD_DB::transactions(),
			array(
				'user_id'       => $user_id,
				'amount'        => $amount,
				'type'          => $type,
				'balance_after' => $balance,
				'details'       => sanitize_text_field( $details ),
				'created_by'    => $created_by ? $created_by : get_current_user_id(),
				'created_at'    => current_time( 'mysql' ),
			),
			array( '%d', '%f', '%s', '%f', '%s', '%d', '%s' )
		);

		$tx_id = (int) $wpdb->insert_id;
		do_action( 'vtd_wallet_transaction', $tx_id, $user_id, $amount, $type, $balance );

		VTD_Notifications::create(
			__( 'Wallet balance updated', 'vetra-dashboard' ),
			sprintf(
				/* translators: 1: amount 2: balance */
				__( 'A %1$s transaction was recorded. New balance: %2$s', 'vetra-dashboard' ),
				$type,
				self::format( $balance )
			),
			array( 'audience' => 'user', 'audience_value' => $user_id, 'link' => vtd_panel_url( array( 'vtd' => 'wallet' ) ) )
		);

		return $tx_id;
	}

	public static function transactions( $user_id, $limit = 20 ) {
		global $wpdb;
		$table = VTD_DB::transactions();
		return $wpdb->get_results(
			$wpdb->prepare( "SELECT * FROM {$table} WHERE user_id = %d ORDER BY tx_id DESC LIMIT %d", $user_id, $limit )
		);
	}

	public static function request_withdrawal( $user_id, $amount, $card_id, $note = '' ) {
		if ( ! VTD_Options::get( 'wallet_enabled', 1 ) ) {
			return new WP_Error( 'vtd_wallet_disabled', __( 'Wallet is disabled.', 'vetra-dashboard' ) );
		}
		$min = (float) VTD_Options::get( 'wallet_min_withdraw', 0 );
		$amount = (float) $amount;

		if ( $amount < $min || $amount <= 0 ) {
			return new WP_Error( 'vtd_wallet_min', sprintf( __( 'Minimum withdrawal amount is %s.', 'vetra-dashboard' ), self::format( $min ) ) );
		}
		if ( $amount > self::balance( $user_id ) ) {
			return new WP_Error( 'vtd_wallet_insufficient', __( 'Insufficient balance.', 'vetra-dashboard' ) );
		}

		$card = VTD_Banking::get_card( $card_id );
		if ( ! $card || (int) $card->user_id !== (int) $user_id ) {
			return new WP_Error( 'vtd_wallet_card', __( 'Please select a valid bank card.', 'vetra-dashboard' ) );
		}
		if ( 'approved' !== $card->card_status ) {
			return new WP_Error( 'vtd_wallet_card_status', __( 'The selected bank card is not approved yet.', 'vetra-dashboard' ) );
		}

		global $wpdb;
		$pending = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM " . VTD_DB::withdrawals() . " WHERE user_id = %d AND status = 'pending'", $user_id ) );
		if ( $pending ) {
			return new WP_Error( 'vtd_wallet_pending', __( 'You already have a pending withdrawal request.', 'vetra-dashboard' ) );
		}

		$wpdb->insert(
			VTD_DB::withdrawals(),
			array(
				'user_id'    => $user_id,
				'amount'     => $amount,
				'card_id'    => $card_id,
				'status'     => 'pending',
				'note'       => sanitize_text_field( $note ),
				'created_at' => current_time( 'mysql' ),
				'updated_at' => current_time( 'mysql' ),
			),
			array( '%d', '%f', '%d', '%s', '%s', '%s', '%s' )
		);

		$id = (int) $wpdb->insert_id;
		do_action( 'vtd_withdrawal_requested', $id, $user_id, $amount );
		return $id;
	}

	public static function approve_withdrawal( $id, $status, $admin_note = '' ) {
		global $wpdb;
		$table = VTD_DB::withdrawals();
		$row   = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $id ) );
		if ( ! $row ) {
			return new WP_Error( 'vtd_withdrawal_missing', __( 'Request not found.', 'vetra-dashboard' ) );
		}
		if ( ! in_array( $status, array( 'approved', 'rejected', 'paid' ), true ) ) {
			return new WP_Error( 'vtd_withdrawal_status', __( 'Invalid status.', 'vetra-dashboard' ) );
		}

		$wpdb->update(
			$table,
			array( 'status' => $status, 'admin_note' => sanitize_text_field( $admin_note ), 'updated_at' => current_time( 'mysql' ) ),
			array( 'id' => $id ),
			array( '%s', '%s', '%s' ),
			array( '%d' )
		);

		if ( 'approved' === $status && 'pending' === $row->status ) {
			self::add_transaction( $row->user_id, (float) $row->amount, 'debit', __( 'Withdrawal', 'vetra-dashboard' ) );
		}

		do_action( 'vtd_withdrawal_status_changed', $id, $status, $row );
		return true;
	}

	public static function withdrawals( $user_id, $limit = 20 ) {
		global $wpdb;
		$table = VTD_DB::withdrawals();
		return $wpdb->get_results(
			$wpdb->prepare( "SELECT * FROM {$table} WHERE user_id = %d ORDER BY id DESC LIMIT %d", $user_id, $limit )
		);
	}

	public static function render() {
		$user_id = get_current_user_id();
		return VTD_Templates::module(
			'wallet',
			array(
				'balance'      => self::balance( $user_id ),
				'transactions' => self::transactions( $user_id ),
				'withdrawals'  => self::withdrawals( $user_id ),
				'cards'        => VTD_Banking::get_cards( $user_id ),
			)
		);
	}
}
