<?php
/**
 * Attachments and downloads module.
 *
 * @package Vetra_Dashboard
 */

defined( 'ABSPATH' ) || exit;

class VTD_Attachments {

	public static function init() {}

	public static function get( $attachment_id ) {
		global $wpdb;
		$table = VTD_DB::attachments();
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE attachment_id = %d", $attachment_id ) );
	}

	public static function get_for_user( $user_id ) {
		global $wpdb;
		$table = VTD_DB::attachments();
		$map   = VTD_DB::attachment_map();
		$roles = (array) get_userdata( $user_id )->roles;
		$role  = ! empty( $roles ) ? $roles[0] : '';

		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT DISTINCT a.* FROM {$table} a
				LEFT JOIN {$map} m ON m.attachment_id = a.attachment_id
				WHERE (a.target = 'all')
				   OR (a.target = 'role' AND a.target_value = %s)
				   OR (a.target = 'user' AND a.target_value = %s)
				   OR (m.user_id = %d)
				ORDER BY a.created_at DESC",
				$role,
				(string) $user_id,
				$user_id
			)
		);
		return is_array( $rows ) ? $rows : array();
	}

	public static function files( $attachment ) {
		$output = array();
		if ( ! empty( $attachment->file_url ) ) {
			$output[] = array(
				'url'      => $attachment->file_url,
				'title'    => $attachment->file_title,
				'password' => $attachment->file_password,
			);
			return $output;
		}
		$ids = maybe_unserialize( $attachment->attachment_ids );
		if ( is_array( $ids ) ) {
			foreach ( $ids as $id ) {
				$url = wp_get_attachment_url( (int) $id );
				if ( $url ) {
					$output[] = array(
						'url'      => $url,
						'title'    => $attachment->file_title ? $attachment->file_title : get_the_title( (int) $id ),
						'password' => $attachment->file_password,
					);
				}
			}
		}
		return $output;
	}

	public static function authorize( $user_id, $attachment_id ) {
		$allowed = wp_list_pluck( self::get_for_user( $user_id ), 'attachment_id' );
		if ( ! in_array( (string) $attachment_id, array_map( 'strval', $allowed ), true ) ) {
			return new WP_Error( 'vtd_attachment_denied', __( 'You do not have access to this file.', 'vetra-dashboard' ), array( 'status' => 403 ) );
		}
		$attachment = self::get( $attachment_id );
		$files      = self::files( $attachment );
		return ! empty( $files ) ? $files[0]['url'] : new WP_Error( 'vtd_attachment_missing', __( 'File not found.', 'vetra-dashboard' ), array( 'status' => 404 ) );
	}

	public static function render() {
		$items = array();
		foreach ( self::get_for_user( get_current_user_id() ) as $attachment ) {
			$items[] = array(
				'attachment' => $attachment,
				'files'      => self::files( $attachment ),
			);
		}
		return VTD_Templates::module( 'attachments', array( 'items' => $items ) );
	}
}
