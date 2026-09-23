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
		return $wpdb->get_results( "SELECT * FROM " . VTD_DB::departments() . " ORDER BY parent_id ASC, department_id ASC" );
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
		foreach ( $rows as $row ) {
			$counts[ $row->status ] = (int) $row->total;
			$total                 += (int) $row->total;
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
		return $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$table} WHERE ticket_id = %d{$where} ORDER BY reply_id ASC", $ticket_id ) );
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
			return new WP_Error( 'vtd_ticket_department', __( 'The ticket department is required.', 'vetra-dashboard' ) );
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
			return;
		}
		$action = sanitize_key( wp_unslash( $_POST['vtd_ticket_action'] ) );
		$user_id = get_current_user_id();

		if ( 'create' === $action ) {
			$data                 = wp_unslash( $_POST );
			$data['attachments']  = self::upload_files( 'ticket_files' );
			$result               = self::create( $user_id, $data );
			if ( ! is_wp_error( $result ) ) {
				wp_safe_redirect( vtd_panel_url( array( 'vtd' => 'ticket', 'ticket' => $result ) ) );
				exit;
			}
		}

		if ( 'reply' === $action ) {
			$ticket_id = (int) ( $_POST['ticket_id'] ?? 0 );
			$data      = array( 'attachments' => self::upload_files( 'ticket_files' ) );
			$result    = self::reply( $user_id, $ticket_id, wp_unslash( $_POST['content'] ?? '' ), $data );
			if ( ! is_wp_error( $result ) ) {
				wp_safe_redirect( vtd_panel_url( array( 'vtd' => 'ticket', 'ticket' => $ticket_id ) ) );
				exit;
			}
		}
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
		return VTD_Templates::module(
			'new-ticket',
			array(
				'departments' => self::departments(),
				'priorities'  => self::priorities(),
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
			)
		);
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
		foreach ( $wpdb->get_results( "SELECT status, COUNT(*) AS total FROM {$table} GROUP BY status" ) as $row ) {
			$counts[ $row->status ] = (int) $row->total;
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
