<?php
/**
 * Polls module.
 *
 * @package Vetra_Dashboard
 */

defined( 'ABSPATH' ) || exit;

class VTD_Polls {

	public static function init() {
		add_action( 'init', array( __CLASS__, 'process_form' ), 30 );
	}

	public static function get_polls( $only_active = true ) {
		global $wpdb;
		$table = VTD_DB::polls();
		$where = $only_active ? 'WHERE poll_status = 1' : '';
		$rows  = $wpdb->get_results( "SELECT * FROM {$table} {$where} ORDER BY poll_id DESC" );
		return is_array( $rows ) ? $rows : array();
	}

	public static function get_poll( $poll_id ) {
		global $wpdb;
		$table = VTD_DB::polls();
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE poll_id = %d", $poll_id ) );
	}

	public static function choices( $poll ) {
		$choices = maybe_unserialize( $poll->poll_choices );
		return is_array( $choices ) ? $choices : array();
	}

	public static function user_answer( $poll_id, $user_id ) {
		global $wpdb;
		$table = VTD_DB::poll_answers();
		$rows  = $wpdb->get_results(
			$wpdb->prepare( "SELECT user_choice FROM {$table} WHERE poll_id = %d AND user_id = %d", $poll_id, $user_id )
		);
		return is_array( $rows ) ? $rows : array();
	}

	public static function has_voted( $poll_id, $user_id ) {
		return ! empty( self::user_answer( $poll_id, $user_id ) );
	}

	public static function vote( $user_id, $poll_id, $choices ) {
		global $wpdb;
		$poll = self::get_poll( $poll_id );
		if ( ! $poll || ! (int) $poll->poll_status ) {
			return new WP_Error( 'vtd_poll_invalid', __( 'Invalid poll.', 'vetra-dashboard' ), array( 'status' => 404 ) );
		}
		if ( self::has_voted( $poll_id, $user_id ) ) {
			return new WP_Error( 'vtd_poll_exists', __( 'You have already voted on this poll.', 'vetra-dashboard' ) );
		}

		$valid   = array_keys( self::choices( $poll ) );
		$choices = array_values( array_intersect( array_map( 'strval', (array) $choices ), array_map( 'strval', $valid ) ) );

		if ( empty( $choices ) ) {
			return new WP_Error( 'vtd_poll_choice', __( 'Please choose an option.', 'vetra-dashboard' ) );
		}
		if ( 0 === (int) $poll->poll_type && count( $choices ) > 1 ) {
			$choices = array( $choices[0] );
		}

		$table = VTD_DB::poll_answers();
		foreach ( $choices as $choice ) {
			$wpdb->insert(
				$table,
				array(
					'poll_id'     => $poll_id,
					'user_id'     => $user_id,
					'user_choice' => $choice,
					'created_at'  => current_time( 'mysql' ),
				),
				array( '%d', '%d', '%s', '%s' )
			);
		}

		do_action( 'vtd_poll_voted', $poll_id, $user_id, $choices );
		return true;
	}

	public static function results( $poll_id ) {
		global $wpdb;
		$table = VTD_DB::poll_answers();
		$rows  = $wpdb->get_results(
			$wpdb->prepare( "SELECT user_choice, COUNT(*) AS total FROM {$table} WHERE poll_id = %d GROUP BY user_choice", $poll_id )
		);
		$out = array();
		if ( is_array( $rows ) ) {
			foreach ( $rows as $row ) {
				$out[ (string) $row->user_choice ] = (int) $row->total;
			}
		}
		return $out;
	}

	public static function participants( $poll_id ) {
		global $wpdb;
		$table = VTD_DB::poll_answers();
		return (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(DISTINCT user_id) FROM {$table} WHERE poll_id = %d", $poll_id ) );
	}

	public static function process_form() {
		if ( empty( $_POST['vtd_poll_submit'] ) || ! is_user_logged_in() ) {
			return;
		}
		$nonce = isset( $_POST['vtd_poll_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['vtd_poll_nonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'vtd_poll' ) ) {
			return;
		}
		$poll_id = isset( $_POST['poll_id'] ) ? (int) $_POST['poll_id'] : 0;
		$choices = isset( $_POST['poll_choice'] ) ? (array) wp_unslash( $_POST['poll_choice'] ) : array();
		$choices = array_map( 'sanitize_text_field', $choices );
		self::vote( get_current_user_id(), $poll_id, $choices );
	}

	public static function render() {
		$user_id = get_current_user_id();
		$polls   = self::get_polls();
		$data    = array();
		foreach ( $polls as $poll ) {
			$data[] = array(
				'poll'         => $poll,
				'choices'      => self::choices( $poll ),
				'answered'     => self::has_voted( $poll->poll_id, $user_id ),
				'answers'      => self::user_answer( $poll->poll_id, $user_id ),
				'results'      => self::results( $poll->poll_id ),
				'participants' => self::participants( $poll->poll_id ),
			);
		}

		return VTD_Templates::module( 'polls', array( 'polls' => $data ) );
	}
}
