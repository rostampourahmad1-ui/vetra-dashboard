<?php
/**
 * Dashboard module.
 *
 * @package Vetra_Dashboard
 */

defined( 'ABSPATH' ) || exit;

class VTD_Dashboard {

	public static function init() {
		add_action( 'admin_bar_menu', array( __CLASS__, 'admin_bar' ), 80 );
	}

	public static function admin_bar( $bar ) {
		if ( ! is_user_logged_in() ) {
			return;
		}
		$bar->add_node(
			array(
				'id'    => 'vtd-panel',
				'title' => __( 'Vetra Dashboard', 'vetra-dashboard' ),
				'href'  => VTD_Router::panel_url(),
				'meta'  => array( 'title' => __( 'Open your dashboard', 'vetra-dashboard' ) ),
			)
		);
	}

	public static function stats( $user_id ) {
		global $wpdb;
		$tickets = VTD_DB::tickets();
		$stats   = array(
			'open_tickets'     => (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$tickets} WHERE user_id = %d AND status NOT IN ('closed')", $user_id ) ),
			'total_tickets'    => (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$tickets} WHERE user_id = %d", $user_id ) ),
			'unread_notifications' => VTD_Notifications::unread_count( $user_id ),
			'wallet_balance'   => VTD_Options::get( 'wallet_enabled', 1 ) ? VTD_Wallet::format( VTD_Wallet::balance( $user_id ) ) : '',
			'comments'         => (int) get_comments( array( 'user_id' => $user_id, 'count' => true, 'status' => 'any' ) ),
			'polls'            => count( VTD_Polls::get_polls() ),
		);
		return apply_filters( 'vtd_dashboard_stats', $stats, $user_id );
	}

	public static function shortcuts() {
		$default = array(
			array( 'label' => __( 'New Ticket', 'vetra-dashboard' ), 'icon' => 'plus', 'url' => vtd_panel_url( array( 'vtd' => 'new-ticket' ) ) ),
			array( 'label' => __( 'Profile', 'vetra-dashboard' ), 'icon' => 'profile', 'url' => vtd_panel_url( array( 'vtd' => 'profile' ) ) ),
			array( 'label' => __( 'Wallet', 'vetra-dashboard' ), 'icon' => 'wallet', 'url' => vtd_panel_url( array( 'vtd' => 'wallet' ) ) ),
			array( 'label' => __( 'Attachments', 'vetra-dashboard' ), 'icon' => 'download', 'url' => vtd_panel_url( array( 'vtd' => 'attachments' ) ) ),
		);

		$configured = (array) VTD_Options::get( 'dashboard_shortcuts', array() );
		if ( ! empty( $configured ) ) {
			$items = array();
			foreach ( $configured as $item ) {
				if ( empty( $item['label'] ) ) {
					continue;
				}
				$items[] = array(
					'label' => $item['label'],
					'icon'  => $item['icon'] ?? 'default',
					'url'   => $item['url'] ?? '#',
				);
			}
			if ( $items ) {
				return $items;
			}
		}
		return $default;
	}

	public static function render() {
		$user_id = get_current_user_id();
		return VTD_Templates::module(
			'dashboard',
			array(
				'user_id'   => $user_id,
				'stats'     => self::stats( $user_id ),
				'shortcuts' => self::shortcuts(),
				'banner'    => VTD_Options::get( 'dashboard_banner', array() ),
			)
		);
	}
}
