<?php
/**
 * Notifications module.
 *
 * @package Vetra_Dashboard
 */

defined( 'ABSPATH' ) || exit;

class VTD_Notifications {

	public static function init() {
		add_action( 'vtd_user_registered', array( __CLASS__, 'welcome' ) );
		add_action( 'vtd_ticket_created', array( __CLASS__, 'on_ticket' ), 10, 2 );
		add_action( 'vtd_ticket_replied', array( __CLASS__, 'on_reply' ), 10, 4 );
	}

	public static function user_role( $user_id ) {
		$user  = get_userdata( $user_id );
		$roles = $user ? (array) $user->roles : array();
		return ! empty( $roles ) ? (string) $roles[0] : '';
	}

	public static function audience_query( $user_id ) {
		return array(
			'all'  => true,
			'user' => (int) $user_id,
			'role' => self::user_role( $user_id ),
		);
	}

	public static function query( $user_id, $args = array() ) {
		global $wpdb;
		$table = VTD_DB::notifications();
		$read  = VTD_DB::notification_read();

		$role   = self::user_role( $user_id );
		$limit  = isset( $args['limit'] ) ? (int) $args['limit'] : 10;
		$offset = isset( $args['offset'] ) ? (int) $args['offset'] : 0;

		$sql = $wpdb->prepare(
			"SELECT n.*, r.read_at FROM {$table} n
			LEFT JOIN {$read} r ON r.notification_id = n.notification_id AND r.user_id = %d
			WHERE (n.audience = 'all')
			   OR (n.audience = 'user' AND n.audience_value = %s)
			   OR (n.audience = 'role' AND n.audience_value = %s)
			ORDER BY n.created_at DESC LIMIT %d OFFSET %d",
			$user_id,
			(string) $user_id,
			$role,
			$limit,
			$offset
		);

		return $wpdb->get_results( $sql );
	}

	public static function count( $user_id ) {
		global $wpdb;
		$table = VTD_DB::notifications();
		$role  = self::user_role( $user_id );
		return (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$table}
				WHERE (audience = 'all')
				   OR (audience = 'user' AND audience_value = %s)
				   OR (audience = 'role' AND audience_value = %s)",
				(string) $user_id,
				$role
			)
		);
	}

	public static function unread_count( $user_id ) {
		global $wpdb;
		$table = VTD_DB::notifications();
		$read  = VTD_DB::notification_read();
		$role  = self::user_role( $user_id );
		return (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$table} n
				LEFT JOIN {$read} r ON r.notification_id = n.notification_id AND r.user_id = %d
				WHERE r.id IS NULL AND ((n.audience = 'all')
				   OR (n.audience = 'user' AND n.audience_value = %s)
				   OR (n.audience = 'role' AND n.audience_value = %s))",
				$user_id,
				(string) $user_id,
				$role
			)
		);
	}

	public static function mark_read( $user_id, $notification_id = 0 ) {
		global $wpdb;
		$table = VTD_DB::notification_read();
		$now   = current_time( 'mysql' );

		if ( $notification_id ) {
			$wpdb->query(
				$wpdb->prepare(
					"INSERT INTO {$table} (notification_id, user_id, read_at) VALUES (%d, %d, %s)
					ON DUPLICATE KEY UPDATE read_at = %s",
					$notification_id,
					$user_id,
					$now,
					$now
				)
			);
			return;
		}

		foreach ( self::query( $user_id, array( 'limit' => 200 ) ) as $item ) {
			self::mark_read( $user_id, (int) $item->notification_id );
		}
	}

	public static function create( $title, $content, $args = array() ) {
		global $wpdb;
		$args = wp_parse_args(
			$args,
			array(
				'audience'       => 'all',
				'audience_value' => '',
				'priority'       => 'normal',
				'link'           => '',
				'created_by'     => get_current_user_id(),
			)
		);

		$wpdb->insert(
			VTD_DB::notifications(),
			array(
				'title'          => sanitize_text_field( $title ),
				'content'        => wp_kses_post( $content ),
				'audience'       => sanitize_key( $args['audience'] ),
				'audience_value' => sanitize_text_field( $args['audience_value'] ),
				'priority'       => sanitize_key( $args['priority'] ),
				'link'           => esc_url_raw( $args['link'] ),
				'created_by'     => (int) $args['created_by'],
				'created_at'     => current_time( 'mysql' ),
			),
			array( '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s' )
		);

		$id = (int) $wpdb->insert_id;
		do_action( 'vtd_notification_created', $id, $args );
		return $id;
	}

	public static function api_list( $user_id, $limit = 15 ) {
		$items = array();
		foreach ( self::query( $user_id, array( 'limit' => $limit ) ) as $row ) {
			$items[] = array(
				'id'       => (int) $row->notification_id,
				'title'    => $row->title,
				'content'  => wp_strip_all_tags( $row->content ),
				'link'     => $row->link,
				'priority' => $row->priority,
				'read'     => ! empty( $row->read_at ),
				'date'     => vtd_date_i18n( $row->created_at ),
				'ago'      => vtd_time_ago( $row->created_at ),
			);
		}
		return $items;
	}

	public static function welcome( $user_id ) {
		self::create(
			__( 'Welcome to Vetra', 'vetra-dashboard' ),
			__( 'Your account is ready. Complete your profile and explore the dashboard.', 'vetra-dashboard' ),
			array( 'audience' => 'user', 'audience_value' => $user_id )
		);
	}

	public static function on_ticket( $ticket_id, $user_id ) {
		self::create(
			__( 'Ticket registered', 'vetra-dashboard' ),
			sprintf( __( 'Your ticket #%d has been registered.', 'vetra-dashboard' ), $ticket_id ),
			array( 'audience' => 'user', 'audience_value' => $user_id, 'link' => vtd_panel_url( array( 'vtd' => 'ticket', 'ticket' => $ticket_id ) ) )
		);
	}

	public static function on_reply( $ticket_id, $reply_id, $owner_id, $is_staff = false ) {
		if ( ! $is_staff ) {
			return;
		}
		self::create(
			__( 'New reply from support', 'vetra-dashboard' ),
			sprintf( __( 'A new reply was posted on ticket #%d.', 'vetra-dashboard' ), $ticket_id ),
			array( 'audience' => 'user', 'audience_value' => $owner_id, 'link' => vtd_panel_url( array( 'vtd' => 'ticket', 'ticket' => $ticket_id ) ) )
		);
	}

	public static function render() {
		$user_id = get_current_user_id();
		$paged   = max( 1, (int) ( $_GET['tpage'] ?? 1 ) );
		$per     = 10;
		$items   = self::query( $user_id, array( 'limit' => $per, 'offset' => ( $paged - 1 ) * $per ) );
		$total   = self::count( $user_id );

		self::mark_read( $user_id );

		return VTD_Templates::module(
			'notifications',
			array(
				'items'    => $items,
				'total'    => $total,
				'paged'    => $paged,
				'per_page' => $per,
			)
		);
	}
}
