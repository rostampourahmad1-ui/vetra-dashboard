<?php
/**
 * Capabilities and support staff roles.
 *
 * @package Vetra_Dashboard
 */

defined( 'ABSPATH' ) || exit;

class VTD_Roles {

	public static function init() {
		add_filter( 'map_meta_cap', array( __CLASS__, 'map_meta_cap' ), 10, 4 );
	}

	public static function maybe_sync() {
		$staff_roles = (array) VTD_Options::get( 'ticket_staff_roles', array( 'administrator', 'editor' ) );
		$hash        = md5( wp_json_encode( $staff_roles ) );
		if ( get_option( 'vtd_caps_hash' ) === $hash ) {
			return;
		}
		self::sync_staff_caps();
		update_option( 'vtd_caps_hash', $hash );
	}

	public static function caps() {
		return array(
			'vtd_access_panel',
			'vtd_manage_tickets',
			'vtd_support_staff',
			'vtd_manage_settings',
		);
	}

	public static function install() {
		$admin = get_role( 'administrator' );
		if ( $admin ) {
			foreach ( self::caps() as $cap ) {
				$admin->add_cap( $cap );
			}
		}
		self::sync_staff_caps();
		$staff_roles = (array) VTD_Options::get( 'ticket_staff_roles', array( 'administrator', 'editor' ) );
		update_option( 'vtd_caps_hash', md5( wp_json_encode( $staff_roles ) ) );
	}

	public static function sync_staff_caps() {
		$staff_roles = (array) VTD_Options::get( 'ticket_staff_roles', array( 'administrator', 'editor' ) );
		foreach ( wp_roles()->roles as $role_key => $role_data ) {
			$role = get_role( $role_key );
			if ( ! $role ) {
				continue;
			}
			if ( 'administrator' === $role_key || in_array( $role_key, $staff_roles, true ) ) {
				$role->add_cap( 'vtd_access_panel' );
				$role->add_cap( 'vtd_support_staff' );
				$role->add_cap( 'vtd_manage_tickets' );
			} else {
				$role->remove_cap( 'vtd_support_staff' );
				$role->remove_cap( 'vtd_manage_tickets' );
			}
		}
	}

	public static function map_meta_cap( $caps, $cap, $user_id, $args ) {
		if ( 'vtd_manage_settings' === $cap ) {
			$caps = user_can( $user_id, 'manage_options' ) ? array( 'manage_options' ) : array( 'do_not_allow' );
		}
		return $caps;
	}
}
