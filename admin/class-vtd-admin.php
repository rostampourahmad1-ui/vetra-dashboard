<?php
/**
 * Admin menu, pages and actions.
 *
 * @package Vetra_Dashboard
 */

defined( 'ABSPATH' ) || exit;

class VTD_Admin {

	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
		add_action( 'admin_post_vtd_sms_test', array( __CLASS__, 'sms_test' ) );
		add_action( 'admin_post_vtd_action', array( __CLASS__, 'handle_action' ) );
		add_filter( 'plugin_action_links_' . VTD_BASENAME, array( __CLASS__, 'plugin_links' ) );
		VTD_Settings::init();
	}

	public static function plugin_links( $links ) {
		$links[] = '<a href="' . esc_url( admin_url( 'admin.php?page=vetra-settings' ) ) . '">' . esc_html__( 'Settings', 'vetra-dashboard' ) . '</a>';
		$links[] = '<a href="' . esc_url( admin_url( 'admin.php?page=vetra-dashboard' ) ) . '">' . esc_html__( 'Dashboard', 'vetra-dashboard' ) . '</a>';
		return $links;
	}

	public static function menu() {
		add_menu_page(
			__( 'Vetra Dashboard', 'vetra-dashboard' ),
			__( 'Vetra', 'vetra-dashboard' ),
			'manage_options',
			'vetra-dashboard',
			array( __CLASS__, 'page_dashboard' ),
			'dashicons-layout',
			3
		);

		$pages = array(
			'vetra-dashboard'    => array( __( 'Overview', 'vetra-dashboard' ), 'page_dashboard' ),
			'vetra-settings'     => array( __( 'Settings', 'vetra-dashboard' ), array( 'VTD_Settings', 'render' ) ),
			'vetra-tickets'      => array( __( 'Tickets', 'vetra-dashboard' ), 'page_tickets' ),
			'vetra-departments'  => array( __( 'Departments', 'vetra-dashboard' ), 'page_departments' ),
			'vetra-notifications' => array( __( 'Notifications', 'vetra-dashboard' ), 'page_notifications' ),
			'vetra-polls'        => array( __( 'Polls', 'vetra-dashboard' ), 'page_polls' ),
			'vetra-attachments'  => array( __( 'Attachments', 'vetra-dashboard' ), 'page_attachments' ),
			'vetra-cards'        => array( __( 'Bank Cards', 'vetra-dashboard' ), 'page_cards' ),
			'vetra-wallet'       => array( __( 'Wallet', 'vetra-dashboard' ), 'page_wallet' ),
			'vetra-withdrawals'  => array( __( 'Withdrawals', 'vetra-dashboard' ), 'page_withdrawals' ),
			'vetra-sms-log'      => array( __( 'SMS Log', 'vetra-dashboard' ), 'page_sms_log' ),
			'vetra-users'        => array( __( 'Users', 'vetra-dashboard' ), 'page_users' ),
		);

		foreach ( $pages as $slug => $data ) {
			add_submenu_page( 'vetra-dashboard', $data[0], $data[0], 'manage_options', $slug, $data[1] );
		}
	}

	protected static function guard() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'vetra-dashboard' ) );
		}
	}

	protected static function confirm_url( $args ) {
		return wp_nonce_url( add_query_arg( array_merge( array( 'page' => $_GET['page'] ?? 'vetra-dashboard' ), $args ), admin_url( 'admin-post.php' ) ), 'vtd_action', '_vtdnonce' );
	}

	public static function page_dashboard() {
		self::guard();
		global $wpdb;
		$tickets = VTD_DB::tickets();
		$users   = count_users();
		$cards   = VTD_DB::cards();

		$stats = array(
			'tickets'        => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$tickets}" ),
			'open_tickets'   => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$tickets} WHERE status != 'closed'" ),
			'cards_pending'  => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$cards} WHERE card_status = 'pending'" ),
			'users'          => (int) ( $users['total_users'] ?? 0 ),
			'withdrawals'    => (int) $wpdb->get_var( "SELECT COUNT(*) FROM " . VTD_DB::withdrawals() . " WHERE status = 'pending'" ),
			'sms'            => (int) $wpdb->get_var( "SELECT COUNT(*) FROM " . VTD_DB::sms_log() ),
		);
		VTD_List_Tables::stats_grid( $stats );
	}

	public static function page_tickets() {
		self::guard();
		VTD_List_Tables::tickets();
	}

	public static function page_departments() {
		self::guard();
		VTD_List_Tables::departments();
	}

	public static function page_notifications() {
		self::guard();
		VTD_List_Tables::notifications();
	}

	public static function page_polls() {
		self::guard();
		VTD_List_Tables::polls();
	}

	public static function page_attachments() {
		self::guard();
		VTD_List_Tables::attachments();
	}

	public static function page_cards() {
		self::guard();
		VTD_List_Tables::cards();
	}

	public static function page_wallet() {
		self::guard();
		VTD_List_Tables::wallet();
	}

	public static function page_withdrawals() {
		self::guard();
		VTD_List_Tables::withdrawals();
	}

	public static function page_sms_log() {
		self::guard();
		VTD_List_Tables::sms_log();
	}

	public static function page_users() {
		self::guard();
		VTD_List_Tables::users();
	}

	public static function sms_test() {
		if ( ! current_user_can( 'manage_options' ) || ! check_admin_referer( 'vtd_sms_test' ) ) {
			wp_die( esc_html__( 'Permission denied.', 'vetra-dashboard' ) );
		}
		$phone  = isset( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : '';
		$result = VTD_SMS::test( $phone );
		$state  = is_wp_error( $result ) ? 'error' : 'success';
		wp_safe_redirect( add_query_arg( array( 'page' => 'vetra-settings', 'tab' => 'sms', 'vtd_msg' => $state ), admin_url( 'admin.php' ) ) );
		exit;
	}

	public static function handle_action() {
		if ( ! current_user_can( 'manage_options' ) || ! isset( $_GET['_vtdnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_vtdnonce'] ) ), 'vtd_action' ) ) {
			wp_die( esc_html__( 'Permission denied.', 'vetra-dashboard' ) );
		}

		$do = isset( $_GET['do'] ) ? sanitize_key( wp_unslash( $_GET['do'] ) ) : '';
		$id = isset( $_GET['id'] ) ? (int) $_GET['id'] : 0;
		global $wpdb;

		switch ( $do ) {
			case 'card_status':
				$status = isset( $_GET['status'] ) ? sanitize_key( wp_unslash( $_GET['status'] ) ) : '';
				VTD_Banking::set_status( $id, $status );
				break;
			case 'withdrawal':
				$status = isset( $_GET['status'] ) ? sanitize_key( wp_unslash( $_GET['status'] ) ) : '';
				VTD_Wallet::approve_withdrawal( $id, $status, isset( $_GET['note'] ) ? sanitize_text_field( wp_unslash( $_GET['note'] ) ) : '' );
				break;
			case 'delete_ticket':
				$wpdb->delete( VTD_DB::tickets(), array( 'ticket_id' => $id ), array( '%d' ) );
				$wpdb->delete( VTD_DB::ticket_replies(), array( 'ticket_id' => $id ), array( '%d' ) );
				break;
			case 'delete_notification':
				$wpdb->delete( VTD_DB::notifications(), array( 'notification_id' => $id ), array( '%d' ) );
				break;
			case 'delete_poll':
				$wpdb->delete( VTD_DB::polls(), array( 'poll_id' => $id ), array( '%d' ) );
				$wpdb->delete( VTD_DB::poll_answers(), array( 'poll_id' => $id ), array( '%d' ) );
				break;
			case 'delete_attachment':
				$wpdb->delete( VTD_DB::attachments(), array( 'attachment_id' => $id ), array( '%d' ) );
				$wpdb->delete( VTD_DB::attachment_map(), array( 'attachment_id' => $id ), array( '%d' ) );
				break;
			case 'delete_department':
				$wpdb->delete( VTD_DB::departments(), array( 'department_id' => $id ), array( '%d' ) );
				break;
			case 'verify_user':
				$field = isset( $_GET['field'] ) ? sanitize_key( wp_unslash( $_GET['field'] ) ) : 'phone';
				if ( 'phone' === $field ) {
					update_user_meta( $id, VTD_Auth::PHONE_VERIFIED_META, 1 );
				} else {
					update_user_meta( $id, VTD_Auth::EMAIL_VERIFIED_META, 1 );
				}
				break;
			case 'reject_user':
				update_user_meta( $id, 'vtd_account_status', 'rejected' );
				break;
		}

		wp_safe_redirect( wp_get_referer() ? wp_get_referer() : admin_url( 'admin.php?page=vetra-dashboard' ) );
		exit;
	}
}
