<?php
/**
 * Support tickets module.
 *
 * @package Vetra_Dashboard
 */

defined( 'ABSPATH' ) || exit;

class VTD_Tickets {

	public static function init() {
		add_action( 'init', array( __CLASS__, 'process_form' ), 30 );
		add_action( 'vtd_ticket_created', array( __CLASS__, 'notify_created' ), 20, 2 );
		add_action( 'vtd_ticket_replied', array( __CLASS__, 'notify_replied' ), 20, 4 );
	}

	/* Departments ---------------------------------------------------------- */

	/** Departments hidden from the panel through the settings screen. */
	public static function hidden_departments() {
		return array_map( 'intval', (array) VTD_Options::get( 'ticket_hidden_departments', array() ) );
	}

	public static function is_department_visible( $department_id ) {
		if ( ! VTD_Options::get( 'ticket_departments_visible', 1 ) ) {
			return false;
		}
		return ! in_array( (int) $department_id, self::hidden_departments(), true );
	}

	/** Departments offered to customers when creating a ticket. */
	public static function visible_departments() {
		if ( ! VTD_Options::get( 'ticket_departments_visible', 1 ) ) {
			return array();
		}
		$hidden = self::hidden_departments();
		return array_values(
			array_filter(
				self::departments(),
				function ( $department ) use ( $hidden ) {
					return ! in_array( (int) $department->department_id, $hidden, true );
				}
			)
		);
	}

	/** Default department used when the selector is hidden. */
	public static function default_department_id() {
		$departments = self::visible_departments();
		if ( ! $departments ) {
			$departments = self::departments();
		}
		return $departments ? (int) $departments[0]->department_id : 0;
	}

	/* Support staff -------------------------------------------------------- */

	/** Users with the configured support roles. */
	public static function role_staff_users() {
		$roles = array_values( array_filter( array_map( 'sanitize_key', (array) VTD_Options::get( 'ticket_staff_roles', array( 'administrator', 'editor' ) ) ) ) );
		if ( ! $roles ) {
			return array();
		}
		return get_users(
			array(
				'role__in' => $roles,
				'fields'   => array( 'ID', 'display_name', 'first_name', 'last_name' ),
				'number'   => 200,
			)
		);
	}

	/** Explicit department assignments from the settings screen. */
	public static function department_assignments( $department_id = 0 ) {
		$rows  = (array) VTD_Options::get( 'ticket_department_assign', array() );
		$users = array();
		foreach ( $rows as $row ) {
			$row = wp_parse_args( (array) $row, array( 'user_id' => 0, 'department_id' => 0, 'enabled' => 1 ) );
			if ( empty( $row['enabled'] ) || ! (int) $row['user_id'] ) {
				continue;
			}
			if ( $department_id && (int) $row['department_id'] !== (int) $department_id ) {
				continue;
			}
			$users[] = (int) $row['user_id'];
		}
		return array_values( array_unique( $users ) );
	}

	/** Staff members responsible for a department. */
	public static function department_staff( $department_id ) {
		global $wpdb;
		$department_id = (int) $department_id;
		$ids           = array();

		$raw = $wpdb->get_var( $wpdb->prepare( "SELECT staff_ids FROM " . VTD_DB::departments() . " WHERE department_id = %d", $department_id ) );
		if ( ! empty( $raw ) ) {
			$decoded = json_decode( (string) $raw, true );
			if ( is_array( $decoded ) ) {
				$ids = array_merge( $ids, array_map( 'intval', $decoded ) );
			} else {
				$ids = array_merge( $ids, array_map( 'intval', array_filter( explode( ',', (string) $raw ) ) ) );
			}
		}

		$ids = array_merge( $ids, self::department_assignments( $department_id ) );

		if ( ! array_filter( $ids ) && VTD_Options::get( 'ticket_staff_by_role', 1 ) ) {
			foreach ( self::role_staff_users() as $user ) {
				$ids[] = (int) $user->ID;
			}
		}

		$ids = array_values( array_unique( array_filter( array_map( 'intval', $ids ) ) ) );
		return apply_filters( 'vtd_department_staff', $ids, $department_id );
	}

	/** May this user handle tickets of the given department? */
	public static function is_department_staff( $user_id, $department_id ) {
		$user_id = (int) $user_id;
		if ( ! $user_id ) {
			return false;
		}
		if ( user_can( $user_id, 'manage_options' ) ) {
			return true;
		}
		$staff = self::department_staff( $department_id );
		if ( in_array( $user_id, $staff, true ) ) {
			return true;
		}
		return ! $staff && vtd_is_staff( $user_id );
	}

	/** Staff phone numbers that must be notified about a ticket. */
	public static function staff_phones( $department_id = 0 ) {
		$phones = array();
		$admin  = trim( (string) VTD_Options::get( 'ticket_sms_admin_phone', '' ) );
		if ( '' === $admin ) {
			$admin = trim( (string) VTD_Options::get( 'sms_admin_notify', '' ) );
		}
		if ( '' !== $admin ) {
			$phones[] = $admin;
		}
		foreach ( self::department_staff( $department_id ) as $user_id ) {
			$phone = VTD_Auth::get_phone( $user_id );
			if ( '' !== $phone ) {
				$phones[] = $phone;
			}
		}
		return array_values( array_unique( array_filter( $phones ) ) );
	}

	/* Notices -------------------------------------------------------------- */

	/** Store a one-time notice for the current user. */
	public static function flash( $type, $message ) {
		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			return;
		}
		$notices   = (array) get_transient( 'vtd_ticket_notices_' . $user_id );
		$notices[] = array( 'type' => 'success' === $type ? 'success' : 'error', 'message' => (string) $message );
		set_transient( 'vtd_ticket_notices_' . $user_id, $notices, 5 * MINUTE_IN_SECONDS );
	}

	public static function notices() {
		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			return array();
		}
		$notices = (array) get_transient( 'vtd_ticket_notices_' . $user_id );
		delete_transient( 'vtd_ticket_notices_' . $user_id );
		return $notices;
	}

	public static function notices_html() {
		$html = '';
		foreach ( self::notices() as $notice ) {
			$html .= '<div class="vtd-alert vtd-alert-' . esc_attr( $notice['type'] ) . '">' . esc_html( $notice['message'] ) . '</div>';
		}
		return $html;
	}

	/* SMS notifications ---------------------------------------------------- */

	protected static function send_sms( $phone, $message, $pattern_key, $tokens ) {
		if ( '' === (string) $phone || ! VTD_SMS::enabled() ) {
			return false;
		}
		$pattern = (string) VTD_Options::get( $pattern_key, '' );
		if ( '' !== $pattern ) {
			$result = VTD_SMS::send_pattern( $phone, $pattern, $tokens, 'ticket' );
			if ( false !== $result ) {
				return $result;
			}
		}
		return VTD_SMS::send( $phone, $message, 'ticket' );
	}

	public static function notify_created( $ticket_id, $user_id ) {
		$ticket = self::get( $ticket_id );
		if ( ! $ticket ) {
			return;
		}
		$title = sprintf( '#%d %s', (int) $ticket_id, $ticket->ticket_title );

		if ( VTD_Options::get( 'ticket_sms_notify_user', 0 ) ) {
			$phone = VTD_Auth::get_phone( $user_id );
			self::send_sms(
				$phone,
				sprintf( 'تیکت %s ثبت شد. پیگیری: %s', $title, self::ticket_link( $ticket_id ) ),
				'ticket_sms_pattern_created',
				array( 'ticket_id' => (string) $ticket_id, 'title' => $ticket->ticket_title, 'link' => self::ticket_link( $ticket_id ) )
			);
		}

		if ( VTD_Options::get( 'ticket_sms_notify_staff', 0 ) ) {
			$message = sprintf( 'تیکت جدید %s در انتظار بررسی است.', $title );
			foreach ( self::staff_phones( (int) $ticket->department_id ) as $phone ) {
				self::send_sms(
					$phone,
					$message,
					'ticket_sms_pattern_created',
					array( 'ticket_id' => (string) $ticket_id, 'title' => $ticket->ticket_title, 'link' => self::ticket_link( $ticket_id ) )
				);
			}
		}
	}

	public static function notify_replied( $ticket_id, $reply_id, $user_id, $is_staff ) {
		$ticket = self::get( $ticket_id );
		if ( ! $ticket ) {
			return;
		}
		$title = sprintf( '#%d %s', (int) $ticket_id, $ticket->ticket_title );

		if ( $is_staff && VTD_Options::get( 'ticket_sms_notify_user', 0 ) ) {
			$phone = VTD_Auth::get_phone( $user_id );
			self::send_sms(
				$phone,
				sprintf( 'پاسخ تازه‌ای برای تیکت %s ثبت شد.', $title ),
				'ticket_sms_pattern_reply',
				array( 'ticket_id' => (string) $ticket_id, 'title' => $ticket->ticket_title, 'link' => self::ticket_link( $ticket_id ) )
			);
		}

		if ( ! $is_staff && VTD_Options::get( 'ticket_sms_notify_staff', 0 ) ) {
			$message = sprintf( 'پاسخ کاربر برای تیکت %s ثبت شد.', $title );
			foreach ( self::staff_phones( (int) $ticket->department_id ) as $phone ) {
				self::send_sms(
					$phone,
					$message,
					'ticket_sms_pattern_reply',
					array( 'ticket_id' => (string) $ticket_id, 'title' => $ticket->ticket_title, 'link' => self::ticket_link( $ticket_id ) )
				);
			}
		}
	}

	public static function ticket_link( $ticket_id ) {
		return VTD_Router::ticket_url( $ticket_id );
	}

	public static function statuses() {
		return apply_filters(
			'vtd_ticket_statuses',
			array(
				'open'         => __( 'Open', 'vetra-dashboard' ),
				'investigating' => __( 'Investigating', 'vetra-dashboard' ),
				'answered'     => __( 'Answered', 'vetra-dashboard' ),
				'pending'      => __( 'Awaiting Reply', 'vetra-dashboard' ),
				'closed'       => __( 'Closed', 'vetra-dashboard' ),
			)
		);
	}

	public static function priorities() {
		return apply_filters(
			'vtd_ticket_priorities',
			array(
				'low'    => __( 'Low', 'vetra-dashboard' ),
				'medium' => __( 'Medium', 'vetra-dashboard' ),
				'high'   => __( 'High', 'vetra-dashboard' ),
			)
		);
	}

	public static function status_label( $status ) {
		$statuses = self::statuses();
		return $statuses[ $status ] ?? $status;
	}

	public static function priority_label( $priority ) {
		$priorities = self::priorities();
		return $priorities[ $priority ] ?? $priority;
	}

	public static function departments() {
		global $wpdb;
		$table = VTD_DB::departments();
		$rows  = $wpdb->get_results( "SELECT * FROM " . $table . " ORDER BY parent_id ASC, department_id ASC" );
		return is_array( $rows ) ? $rows : array();
	}

	public static function department_name( $id ) {
		global $wpdb;
		$name = $wpdb->get_var( $wpdb->prepare( "SELECT department_name FROM " . VTD_DB::departments() . " WHERE department_id = %d", $id ) );
		return $name ? $name : __( 'General', 'vetra-dashboard' );
	}

	public static function counts( $user_id, $staff = false ) {
		global $wpdb;
		$table  = VTD_DB::tickets();
		$column = $staff ? 'staff_id' : 'user_id';
		$rows   = $wpdb->get_results(
			$wpdb->prepare( "SELECT status, COUNT(*) AS total FROM {$table} WHERE {$column} = %d GROUP BY status", $user_id )
		);
		$counts = array_fill_keys( array_keys( self::statuses() ), 0 );
		$total  = 0;
		if ( is_array( $rows ) ) {
			foreach ( $rows as $row ) {
				$counts[ $row->status ] = (int) $row->total;
				$total                 += (int) $row->total;
			}
		}
		$counts['all'] = $total;
		return $counts;
	}

	public static function query( $args = array() ) {
		global $wpdb;
		$table = VTD_DB::tickets();
		$args  = wp_parse_args(
			$args,
			array(
				'user_id'       => 0,
				'department_id' => 0,
				'status'        => '',
				'priority'      => '',
				'search'        => '',
				'per_page'      => 10,
				'paged'         => 1,
				'orderby'       => 'updated_at',
				'order'         => 'DESC',
			)
		);

		$where  = array( '1=1' );
		$params = array();

		if ( $args['user_id'] ) {
			$where[]  = 'user_id = %d';
			$params[] = $args['user_id'];
		}
		if ( $args['department_id'] ) {
			$where[]  = 'department_id = %d';
			$params[] = $args['department_id'];
		}
		if ( $args['status'] && 'all' !== $args['status'] ) {
			$where[]  = 'status = %s';
			$params[] = $args['status'];
		}
		if ( $args['priority'] && 'all' !== $args['priority'] ) {
			$where[]  = 'priority = %s';
			$params[] = $args['priority'];
		}
		if ( '' !== $args['search'] ) {
			$where[]  = '(ticket_title LIKE %s OR ticket_id = %d)';
			$params[] = '%' . $wpdb->esc_like( $args['search'] ) . '%';
			$params[] = (int) $args['search'];
		}

		$where_sql = implode( ' AND ', $where );
		$orderby   = in_array( $args['orderby'], array( 'created_at', 'updated_at', 'ticket_id', 'priority' ), true ) ? $args['orderby'] : 'updated_at';
		$order     = 'ASC' === strtoupper( $args['order'] ) ? 'ASC' : 'DESC';

		$count_sql = "SELECT COUNT(*) FROM {$table} WHERE {$where_sql}";
		$total     = (int) $wpdb->get_var( $params ? $wpdb->prepare( $count_sql, $params ) : $count_sql );

		$offset   = ( max( 1, (int) $args['paged'] ) - 1 ) * (int) $args['per_page'];
		$list_sql = "SELECT * FROM {$table} WHERE {$where_sql} ORDER BY {$orderby} {$order} LIMIT %d OFFSET %d";
		$list_params = array_merge( $params, array( (int) $args['per_page'], $offset ) );
		$items    = $wpdb->get_results( $wpdb->prepare( $list_sql, $list_params ) );
		$items    = is_array( $items ) ? $items : array();

		return array( 'items' => $items, 'total' => $total );
	}

	public static function get( $ticket_id ) {
		global $wpdb;
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM " . VTD_DB::tickets() . " WHERE ticket_id = %d", $ticket_id ) );
	}

	public static function replies( $ticket_id, $include_internal = false ) {
		global $wpdb;
		$table = VTD_DB::ticket_replies();
		$where = $include_internal ? '' : ' AND is_internal = 0';
		$rows  = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$table} WHERE ticket_id = %d{$where} ORDER BY reply_id ASC", $ticket_id ) );
		return is_array( $rows ) ? $rows : array();
	}

	public static function can_view( $user_id, $ticket ) {
		if ( ! $ticket ) {
			return false;
		}
		if ( (int) $ticket->user_id === (int) $user_id ) {
			return true;
		}
		return vtd_is_staff( $user_id );
	}

	public static function create( $user_id, $data ) {
		if ( ! VTD_Options::get( 'ticket_enabled', 1 ) ) {
			return new WP_Error( 'vtd_ticket_disabled', __( 'The ticket system is disabled.', 'vetra-dashboard' ) );
		}

		$title    = sanitize_text_field( $data['title'] ?? '' );
		$content  = wp_kses_post( $data['content'] ?? '' );
		$priority = sanitize_key( $data['priority'] ?? 'medium' );
		$department = (int) ( $data['department_id'] ?? 0 );

		if ( strlen( $title ) < 5 ) {
			return new WP_Error( 'vtd_ticket_title', __( 'The ticket title is required.', 'vetra-dashboard' ) );
		}
		if ( strlen( wp_strip_all_tags( $content ) ) < 10 ) {
			return new WP_Error( 'vtd_ticket_content', __( 'The ticket content is required.', 'vetra-dashboard' ) );
		}
		if ( ! array_key_exists( $priority, self::priorities() ) ) {
			$priority = 'medium';
		}
		if ( ! $department ) {
			$department = self::default_department_id();
		}
		if ( ! $department ) {
			return new WP_Error( 'vtd_ticket_department', __( 'The ticket department is required.', 'vetra-dashboard' ) );
		}
		if ( VTD_Options::get( 'ticket_departments_visible', 1 ) && ! self::is_department_visible( $department ) ) {
			return new WP_Error( 'vtd_ticket_department_hidden', 'دپارتمان انتخابی در حال حاضر فعال نیست.' );
		}

		$max_open = (int) VTD_Options::get( 'ticket_max_open', 0 );
		if ( $max_open > 0 ) {
			$open = self::counts( $user_id );
			unset( $open['closed'], $open['all'] );
			if ( array_sum( $open ) >= $max_open ) {
				return new WP_Error( 'vtd_ticket_limit', __( 'You have reached the maximum number of open tickets.', 'vetra-dashboard' ) );
			}
		}

		global $wpdb;
		$now = current_time( 'mysql' );
		$wpdb->insert(
			VTD_DB::tickets(),
			array(
				'ticket_title'   => $title,
				'ticket_content' => $content,
				'user_id'        => $user_id,
				'department_id'  => $department,
				'priority'       => $priority,
				'status'         => 'open',
				'attachments'    => wp_json_encode( self::sanitize_attachments( $data['attachments'] ?? array() ) ),
				'created_at'     => $now,
				'updated_at'     => $now,
			),
			array( '%s', '%s', '%d', '%d', '%s', '%s', '%s', '%s', '%s' )
		);
		$ticket_id = (int) $wpdb->insert_id;

		$auto = VTD_Options::get( 'ticket_auto_reply', '' );
		if ( $auto ) {
			$wpdb->insert(
				VTD_DB::ticket_replies(),
				array(
					'ticket_id'  => $ticket_id,
					'user_id'    => 0,
					'content'    => wp_kses_post( $auto ),
					'is_staff'   => 1,
					'created_at' => $now,
				),
				array( '%d', '%d', '%s', '%d', '%s' )
			);
		}

		do_action( 'vtd_ticket_created', $ticket_id, $user_id );
		self::maybe_send_sms( $ticket_id, 'created' );
		return $ticket_id;
	}

	public static function reply( $user_id, $ticket_id, $content, $data = array() ) {
		$ticket = self::get( $ticket_id );
		if ( ! self::can_view( $user_id, $ticket ) ) {
			return new WP_Error( 'vtd_ticket_denied', __( 'You do not have access to this ticket.', 'vetra-dashboard' ), array( 'status' => 403 ) );
		}
		if ( 'closed' === $ticket->status && ! vtd_is_staff( $user_id ) ) {
			return new WP_Error( 'vtd_ticket_closed', __( 'This ticket is closed.', 'vetra-dashboard' ) );
		}
		if ( strlen( wp_strip_all_tags( $content ) ) < 3 ) {
			return new WP_Error( 'vtd_ticket_reply', __( 'The reply content is required.', 'vetra-dashboard' ) );
		}

		$is_staff    = vtd_is_staff( $user_id ) && (int) $ticket->user_id !== (int) $user_id;
		$is_internal = $is_staff && ! empty( $data['internal'] );

		global $wpdb;
		$wpdb->insert(
			VTD_DB::ticket_replies(),
			array(
				'ticket_id'   => $ticket_id,
				'user_id'     => $user_id,
				'content'     => wp_kses_post( $content ),
				'attachments' => wp_json_encode( self::sanitize_attachments( $data['attachments'] ?? array() ) ),
				'is_staff'    => $is_staff ? 1 : 0,
				'is_internal' => $is_internal ? 1 : 0,
				'created_at'  => current_time( 'mysql' ),
			),
			array( '%d', '%d', '%s', '%s', '%d', '%d', '%s' )
		);
		$reply_id = (int) $wpdb->insert_id;

		$status = $is_staff ? 'answered' : 'pending';
		$wpdb->update( VTD_DB::tickets(), array( 'status' => $status, 'updated_at' => current_time( 'mysql' ) ), array( 'ticket_id' => $ticket_id ), array( '%s', '%s' ), array( '%d' ) );

		do_action( 'vtd_ticket_replied', $ticket_id, $reply_id, (int) $ticket->user_id, $is_staff );
		self::maybe_send_sms( $ticket_id, 'replied' );
		return $reply_id;
	}

	public static function close( $user_id, $ticket_id ) {
		$ticket = self::get( $ticket_id );
		if ( ! self::can_view( $user_id, $ticket ) ) {
			return new WP_Error( 'vtd_ticket_denied', __( 'You do not have access.', 'vetra-dashboard' ), array( 'status' => 403 ) );
		}
		global $wpdb;
		$wpdb->update( VTD_DB::tickets(), array( 'status' => 'closed', 'updated_at' => current_time( 'mysql' ) ), array( 'ticket_id' => $ticket_id ), array( '%s', '%s' ), array( '%d' ) );
		do_action( 'vtd_ticket_closed', $ticket_id, $user_id );
		self::maybe_send_sms( $ticket_id, 'closed' );
		return true;
	}

	public static function set_status( $ticket_id, $status ) {
		if ( ! array_key_exists( $status, self::statuses() ) ) {
			return new WP_Error( 'vtd_ticket_status', __( 'Invalid status.', 'vetra-dashboard' ) );
		}
		global $wpdb;
		$wpdb->update( VTD_DB::tickets(), array( 'status' => $status, 'updated_at' => current_time( 'mysql' ) ), array( 'ticket_id' => $ticket_id ), array( '%s', '%s' ), array( '%d' ) );
		return true;
	}

	public static function rate( $user_id, $ticket_id, $score, $feedback = '' ) {
		$ticket = self::get( $ticket_id );
		if ( ! $ticket || (int) $ticket->user_id !== (int) $user_id ) {
			return new WP_Error( 'vtd_ticket_denied', __( 'You do not have access.', 'vetra-dashboard' ), array( 'status' => 403 ) );
		}
		$score = max( 1, min( 5, (int) $score ) );
		global $wpdb;
		$table = VTD_DB::ticket_rating();
		$wpdb->replace(
			$table,
			array(
				'ticket_id'  => $ticket_id,
				'user_id'    => $user_id,
				'score'      => $score,
				'feedback'   => sanitize_textarea_field( $feedback ),
				'created_at' => current_time( 'mysql' ),
			),
			array( '%d', '%d', '%d', '%s', '%s' )
		);
		do_action( 'vtd_ticket_rated', $ticket_id, $user_id, $score );
		return true;
	}

	public static function toggle_star( $user_id, $ticket_id ) {
		if ( ! vtd_is_staff( $user_id ) ) {
			return new WP_Error( 'vtd_ticket_denied', __( 'You do not have access.', 'vetra-dashboard' ), array( 'status' => 403 ) );
		}
		$ticket = self::get( $ticket_id );
		if ( ! $ticket ) {
			return new WP_Error( 'vtd_ticket_missing', __( 'Ticket not found.', 'vetra-dashboard' ), array( 'status' => 404 ) );
		}
		global $wpdb;
		$starred = $ticket->starred ? 0 : 1;
		$wpdb->update( VTD_DB::tickets(), array( 'starred' => $starred ), array( 'ticket_id' => $ticket_id ), array( '%d' ), array( '%d' ) );
		return $starred;
	}

	public static function rating( $ticket_id ) {
		global $wpdb;
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM " . VTD_DB::ticket_rating() . " WHERE ticket_id = %d", $ticket_id ) );
	}

	protected static function sanitize_attachments( $attachments ) {
		if ( is_string( $attachments ) ) {
			$attachments = array_filter( array_map( 'trim', explode( ',', $attachments ) ) );
		}
		return array_values( array_filter( array_map( 'intval', (array) $attachments ) ) );
	}

	public static function process_form() {
		if ( empty( $_POST['vtd_ticket_action'] ) || ! is_user_logged_in() ) {
			return;
		}
		$nonce = isset( $_POST['vtd_ticket_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['vtd_ticket_nonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'vtd_ticket' ) ) {
			self::flash( 'error', __( 'Security check failed. Please try again.', 'vetra-dashboard' ) );
			self::redirect_back();
		}
		if ( ! VTD_Options::get( 'ticket_enabled', 1 ) ) {
			self::flash( 'error', __( 'The ticket system is disabled.', 'vetra-dashboard' ) );
			self::redirect_back();
		}

		$action  = sanitize_key( wp_unslash( $_POST['vtd_ticket_action'] ) );
		$user_id = get_current_user_id();
		$target  = '';

		try {
			switch ( $action ) {
				case 'create':
					$data                = wp_unslash( $_POST );
					$data['attachments'] = self::upload_files( 'ticket_files' );
					$result              = self::create( $user_id, $data );
					if ( is_wp_error( $result ) ) {
						self::flash( 'error', $result->get_error_message() );
						$target = vtd_panel_url( array( 'vtd' => 'new-ticket' ) );
					} else {
						self::flash( 'success', __( 'Ticket submitted successfully.', 'vetra-dashboard' ) );
						$target = self::ticket_link( $result );
					}
					break;

				case 'reply':
					$ticket_id = (int) ( $_POST['ticket_id'] ?? 0 );
					$data      = array( 'attachments' => self::upload_files( 'ticket_files' ) );
					$result    = self::reply( $user_id, $ticket_id, wp_unslash( $_POST['content'] ?? '' ), $data );
					$target    = self::ticket_link( $ticket_id );
					self::flash( is_wp_error( $result ) ? 'error' : 'success', is_wp_error( $result ) ? $result->get_error_message() : __( 'Your reply has been sent.', 'vetra-dashboard' ) );
					break;

				case 'close':
				case 'cancel':
					$ticket_id = (int) ( $_POST['ticket_id'] ?? 0 );
					$note      = isset( $_POST['note'] ) ? wp_unslash( $_POST['note'] ) : '';
					$result    = self::close( $user_id, $ticket_id, $note, $action );
					$target    = self::ticket_link( $ticket_id );
					if ( is_wp_error( $result ) ) {
						self::flash( 'error', $result->get_error_message() );
					} else {
						self::flash( 'success', 'cancel' === $action ? __( 'Ticket cancelled.', 'vetra-dashboard' ) : __( 'Ticket closed.', 'vetra-dashboard' ) );
					}
					break;

				default:
					return;
			}
		} catch ( Exception $e ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement
			self::flash( 'error', __( 'Something went wrong while processing your request. Please try again.', 'vetra-dashboard' ) );
			$target = wp_get_referer() ? wp_get_referer() : vtd_panel_url( array( 'vtd' => 'tickets' ) );
		} catch ( Throwable $e ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement
			self::flash( 'error', __( 'Something went wrong while processing your request. Please try again.', 'vetra-dashboard' ) );
			$target = wp_get_referer() ? wp_get_referer() : vtd_panel_url( array( 'vtd' => 'tickets' ) );
		}

		if ( '' === $target ) {
			$target = vtd_panel_url( array( 'vtd' => 'tickets' ) );
		}
		wp_safe_redirect( $target );
		exit;
	}

	/** Redirect to the previous panel page when a form cannot be processed. */
	public static function redirect_back() {
		$target = wp_get_referer() ? wp_get_referer() : vtd_panel_url( array( 'vtd' => 'tickets' ) );
		wp_safe_redirect( $target );
		exit;
	}

	public static function upload_files( $field, $max = 5 ) {
		if ( ! VTD_Options::get( 'ticket_attachments', 1 ) || empty( $_FILES[ $field ] ) ) {
			return array();
		}

		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';

		$ids   = array();
		$file  = $_FILES[ $field ]; // phpcs:ignore
		$count = is_array( $file['name'] ) ? count( $file['name'] ) : 0;

		add_filter( 'upload_mimes', array( __CLASS__, 'allow_upload_mimes' ) );
		for ( $i = 0; $i < min( $count, $max ); $i++ ) {
			if ( empty( $file['name'][ $i ] ) || UPLOAD_ERR_OK !== (int) $file['error'][ $i ] ) {
				continue;
			}
			if ( (int) $file['size'][ $i ] > 5 * MB_IN_BYTES ) {
				continue;
			}
			$single = array(
				'name'     => $file['name'][ $i ],
				'type'     => $file['type'][ $i ],
				'tmp_name' => $file['tmp_name'][ $i ],
				'error'    => $file['error'][ $i ],
				'size'     => $file['size'][ $i ],
			);
			$id = media_handle_sideload( $single, 0, __( 'Ticket attachment', 'vetra-dashboard' ) );
			if ( ! is_wp_error( $id ) ) {
				$ids[] = (int) $id;
			}
		}
		remove_filter( 'upload_mimes', array( __CLASS__, 'allow_upload_mimes' ) );

		return $ids;
	}

	public static function attachments_html( $json ) {
		$ids = maybe_unserialize( $json );
		if ( is_string( $ids ) ) {
			$ids = json_decode( $ids, true );
		}
		if ( ! is_array( $ids ) || empty( $ids ) ) {
			return '';
		}
		$html = '<div class="vtd-message-files">';
		foreach ( $ids as $id ) {
			$url = wp_get_attachment_url( (int) $id );
			if ( ! $url ) {
				continue;
			}
			$html .= '<a class="vtd-chip" href="' . esc_url( $url ) . '" download>' . vtd_icon( 'download' ) . esc_html( get_the_title( (int) $id ) ) . '</a>';
		}
		$html .= '</div>';
		return $html;
	}

	public static function allow_upload_mimes( $mimes ) {
		$extra = array(
			'jpg|jpeg' => 'image/jpeg',
			'png'      => 'image/png',
			'pdf'      => 'application/pdf',
			'zip'      => 'application/zip',
			'doc'      => 'application/msword',
			'docx'     => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
		);
		return array_merge( $mimes, $extra );
	}

	public static function render() {
		$user_id = get_current_user_id();
		$status  = isset( $_GET['ticket_status'] ) ? sanitize_key( wp_unslash( $_GET['ticket_status'] ) ) : '';
		$paged   = max( 1, (int) ( $_GET['tpage'] ?? 1 ) );
		$result  = self::query( array( 'user_id' => $user_id, 'status' => $status, 'paged' => $paged, 'per_page' => 10 ) );

		return VTD_Templates::module(
			'tickets',
			array(
				'items'    => $result['items'],
				'total'    => $result['total'],
				'counts'   => self::counts( $user_id ),
				'status'   => $status,
				'paged'    => $paged,
				'per_page' => 10,
			)
		);
	}

	public static function render_new() {
		$departments = self::departments();
		$dept_toggle = VTD_Options::get( 'ticket_departments_toggle', 1 );
		if ( ! $dept_toggle ) {
			$departments = array();
		}
		return VTD_Templates::module(
			'new-ticket',
			array(
				'departments' => $departments,
				'priorities'  => self::priorities(),
				'faq_enabled' => VTD_Options::get( 'ticket_faq_enabled', 1 ),
				'faq_content' => VTD_Options::get( 'ticket_faq_content', '' ),
				'cancel_enabled' => VTD_Options::get( 'ticket_cancel_enabled', 1 ),
				'rating_enabled' => VTD_Options::get( 'ticket_rating', 1 ),
			)
		);
	}

	public static function render_single() {
		$ticket_id = isset( $_GET['ticket'] ) ? (int) $_GET['ticket'] : 0;
		$ticket    = self::get( $ticket_id );
		$user_id   = get_current_user_id();

		if ( ! self::can_view( $user_id, $ticket ) ) {
			return VTD_Templates::module( 'alert', array( 'message' => __( 'Ticket not found.', 'vetra-dashboard' ), 'type' => 'error' ) );
		}

		if ( ! vtd_is_staff( $user_id ) ) {
			global $wpdb;
			$wpdb->update( VTD_DB::ticket_replies(), array( 'read_by_user' => 1 ), array( 'ticket_id' => $ticket_id ), array( '%d' ), array( '%d' ) );
		}

		return VTD_Templates::module(
			'ticket-single',
			array(
				'ticket'    => $ticket,
				'replies'   => self::replies( $ticket_id, vtd_is_staff( $user_id ) ),
				'rating'    => self::rating( $ticket_id ),
				'is_staff'  => vtd_is_staff( $user_id ),
				'statuses'  => self::statuses(),
				'priorities' => self::priorities(),
				'cancel_enabled' => VTD_Options::get( 'ticket_cancel_enabled', 1 ),
				'rating_enabled' => VTD_Options::get( 'ticket_rating', 1 ),
			)
		);
	}

	public static function maybe_send_sms( $ticket_id, $event ) {
		if ( ! VTD_Options::get( 'ticket_sms_notify', 0 ) ) {
			return;
		}
		$events = VTD_Options::get( 'ticket_sms_events', array() );
		if ( ! in_array( $event, $events, true ) ) {
			return;
		}
		$ticket = self::get( $ticket_id );
		if ( ! $ticket ) {
			return;
		}
		$user = get_userdata( (int) $ticket->user_id );
		if ( ! $user ) {
			return;
		}
		$phone = get_user_meta( (int) $ticket->user_id, 'billing_phone', true );
		if ( ! $phone ) {
			return;
		}
		$messages = array(
			'created' => 'تیکت جدید شما با شماره ' . $ticket_id . ' ثبت شد.',
			'replied' => 'پاسخ جدیدی برای تیکت شماره ' . $ticket_id . ' ثبت شد.',
			'closed'  => 'تیکت شماره ' . $ticket_id . ' بسته شد.',
		);
		$msg = $messages[ $event ] ?? '';
		if ( $msg ) {
			VTD_SMS::send( $phone, $msg );
		}
	}

	public static function render_staff() {
		if ( ! vtd_is_staff( get_current_user_id() ) ) {
			return VTD_Templates::module( 'alert', array( 'message' => __( 'You do not have access to this page.', 'vetra-dashboard' ), 'type' => 'error' ) );
		}
		$paged   = max( 1, (int) ( $_GET['tpage'] ?? 1 ) );
		$status  = isset( $_GET['ticket_status'] ) ? sanitize_key( wp_unslash( $_GET['ticket_status'] ) ) : '';
		$dep     = isset( $_GET['department'] ) ? (int) $_GET['department'] : 0;
		$search  = isset( $_GET['ticket_search'] ) ? sanitize_text_field( wp_unslash( $_GET['ticket_search'] ) ) : '';
		$result  = self::query(
			array(
				'department_id' => $dep,
				'status'        => $status,
				'search'        => $search,
				'paged'         => $paged,
				'per_page'      => 15,
			)
		);

		$counts = array( 'all' => $result['total'] );
		global $wpdb;
		$table = VTD_DB::tickets();
		$rows  = $wpdb->get_results( "SELECT status, COUNT(*) AS total FROM {$table} GROUP BY status" );
		if ( is_array( $rows ) ) {
			foreach ( $rows as $row ) {
				$counts[ $row->status ] = (int) $row->total;
			}
		}

		return VTD_Templates::module(
			'staff-tickets',
			array(
				'items'       => $result['items'],
				'total'       => $result['total'],
				'counts'      => $counts,
				'departments' => self::departments(),
				'status'      => $status,
				'department'  => $dep,
				'search'      => $search,
				'paged'       => $paged,
				'per_page'    => 15,
			)
		);
	}
}
