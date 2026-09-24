<?php
/**
 * Admin list rendering helpers.
 *
 * @package Vetra_Dashboard
 */

defined( 'ABSPATH' ) || exit;

class VTD_List_Tables {

	protected static function value_label( $value ) {
		$labels = array(
			'pending' => 'در انتظار بررسی', 'approved' => 'تأییدشده', 'rejected' => 'ردشده', 'paid' => 'پرداخت‌شده',
			'success' => 'موفق', 'error' => 'خطا', 'failed' => 'ناموفق', 'all' => 'همه کاربران', 'user' => 'کاربر مشخص',
			'role' => 'نقش کاربری', 'login' => 'ورود', 'register' => 'ثبت‌نام', 'verify_phone' => 'تأیید موبایل',
			'password_reset' => 'بازیابی گذرواژه', 'signup' => 'ثبت‌نام',
		);
		return $labels[ (string) $value ] ?? $value;
	}

	protected static function action_url( $args ) {
		$args['action'] = 'vtd_action';
		return wp_nonce_url( add_query_arg( $args, admin_url( 'admin-post.php' ) ), 'vtd_action', '_vtdnonce' );
	}

	protected static function msg() {
		if ( isset( $_GET['vtd_msg'] ) ) {
			$map = array(
				'success' => __( 'The operation was successful.', 'vetra-dashboard' ),
				'error'   => __( 'The operation failed.', 'vetra-dashboard' ),
			);
			$key = sanitize_key( wp_unslash( $_GET['vtd_msg'] ) );
			if ( isset( $map[ $key ] ) ) {
				$class = 'success' === $key ? 'notice-success' : 'notice-error';
				echo '<div class="notice ' . esc_attr( $class ) . ' is-dismissible"><p>' . esc_html( $map[ $key ] ) . '</p></div>';
			}
		}
	}

	protected static function items( $rows ) {
		return is_array( $rows ) ? $rows : array();
	}

	protected static function empty_state( $columns ) {
		echo '<tr><td colspan="' . (int) $columns . '">' . esc_html__( 'No records found.', 'vetra-dashboard' ) . '</td></tr>';
	}

	public static function stats_grid( $stats ) {
		$labels = array(
			'tickets'       => __( 'Total tickets', 'vetra-dashboard' ),
			'open_tickets'  => __( 'Open tickets', 'vetra-dashboard' ),
			'cards_pending' => __( 'Pending cards', 'vetra-dashboard' ),
			'users'         => __( 'Users', 'vetra-dashboard' ),
			'withdrawals'   => __( 'Pending withdrawals', 'vetra-dashboard' ),
			'sms'           => __( 'SMS sent', 'vetra-dashboard' ),
		);
		echo '<div class="wrap vtd-admin-wrap"><h1>' . esc_html__( 'Vetra Overview', 'vetra-dashboard' ) . '</h1>';
		echo '<div class="vtd-admin-stats">';
		foreach ( $labels as $key => $label ) {
			echo '<div class="vtd-admin-stat"><span>' . esc_html( number_format_i18n( $stats[ $key ] ?? 0 ) ) . '</span><small>' . esc_html( $label ) . '</small></div>';
		}
		echo '</div></div>';
	}

	public static function tickets() {
		global $wpdb;
		$table  = VTD_DB::tickets();
		$status = isset( $_GET['status'] ) ? sanitize_key( wp_unslash( $_GET['status'] ) ) : '';
		$paged  = max( 1, (int) ( $_GET['paged'] ?? 1 ) );
		$per    = 20;
		$where  = $status ? $wpdb->prepare( 'WHERE status = %s', $status ) : '';

		$total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table} {$where}" );
		$items = self::items( $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$table} {$where} ORDER BY ticket_id DESC LIMIT %d OFFSET %d", $per, ( $paged - 1 ) * $per ) ) );

		echo '<div class="wrap vtd-admin-wrap"><h1>' . esc_html__( 'Tickets', 'vetra-dashboard' ) . '</h1>';
		self::msg();
		echo '<form method="get" class="vtd-admin-filters"><input type="hidden" name="page" value="vetra-tickets"><select name="status"><option value="">' . esc_html__( 'All statuses', 'vetra-dashboard' ) . '</option>';
		foreach ( VTD_Tickets::statuses() as $key => $label ) {
			echo '<option value="' . esc_attr( $key ) . '" ' . selected( $status, $key, false ) . '>' . esc_html( $label ) . '</option>';
		}
		echo '</select><button class="button">' . esc_html__( 'Filter', 'vetra-dashboard' ) . '</button></form>';
		echo '<table class="widefat striped"><thead><tr><th>#</th><th>' . esc_html__( 'Title', 'vetra-dashboard' ) . '</th><th>' . esc_html__( 'User', 'vetra-dashboard' ) . '</th><th>' . esc_html__( 'Status', 'vetra-dashboard' ) . '</th><th>' . esc_html__( 'Priority', 'vetra-dashboard' ) . '</th><th>' . esc_html__( 'Updated', 'vetra-dashboard' ) . '</th><th></th></tr></thead><tbody>';
		if ( empty( $items ) ) {
			self::empty_state( 7 );
		}
		foreach ( $items as $ticket ) {
			echo '<tr>';
			echo '<td>' . (int) $ticket->ticket_id . '</td>';
			echo '<td>' . esc_html( $ticket->ticket_title ) . '</td>';
			echo '<td>' . esc_html( vtd_current_user_name( $ticket->user_id ) ) . '</td>';
			echo '<td>' . esc_html( VTD_Tickets::status_label( $ticket->status ) ) . '</td>';
			echo '<td>' . esc_html( VTD_Tickets::priority_label( $ticket->priority ) ) . '</td>';
			echo '<td>' . esc_html( vtd_date_i18n( $ticket->updated_at ) ) . '</td>';
			echo '<td><a class="button button-small" href="' . esc_url( self::action_url( array( 'do' => 'delete_ticket', 'id' => $ticket->ticket_id ) ) ) . '" onclick="return confirm(\'' . esc_js( __( 'Delete this ticket?', 'vetra-dashboard' ) ) . '\')">' . esc_html__( 'Delete', 'vetra-dashboard' ) . '</a></td>';
			echo '</tr>';
		}
		echo '</tbody></table>';
		self::pagination( $total, $per, $paged );
		echo '</div>';
	}

	public static function departments() {
		global $wpdb;
		$table = VTD_DB::departments();

		if ( ! empty( $_POST['vtd_department'] ) && check_admin_referer( 'vtd_department' ) ) {
			$wpdb->insert(
				$table,
				array(
					'department_name' => sanitize_text_field( wp_unslash( $_POST['vtd_department'] ) ),
					'parent_id'       => (int) ( $_POST['vtd_parent'] ?? 0 ),
					'staff_ids'       => sanitize_text_field( wp_unslash( $_POST['vtd_staff'] ?? '' ) ),
					'description'     => sanitize_text_field( wp_unslash( $_POST['vtd_description'] ?? '' ) ),
				),
				array( '%s', '%d', '%s', '%s' )
			);
		}

		$items = self::items( $wpdb->get_results( "SELECT * FROM {$table} ORDER BY department_id DESC" ) );
		echo '<div class="wrap vtd-admin-wrap"><h1>' . esc_html__( 'Support Departments', 'vetra-dashboard' ) . '</h1>';
		echo '<form method="post" class="vtd-admin-form">';
		wp_nonce_field( 'vtd_department' );
		echo '<input type="text" name="vtd_department" placeholder="' . esc_attr__( 'Department name', 'vetra-dashboard' ) . '" required>';
		echo '<input type="text" name="vtd_staff" placeholder="' . esc_attr__( 'Staff user IDs (comma separated)', 'vetra-dashboard' ) . '">';
		echo '<input type="text" name="vtd_description" placeholder="' . esc_attr__( 'Description', 'vetra-dashboard' ) . '">';
		echo '<button class="button button-primary">' . esc_html__( 'Add', 'vetra-dashboard' ) . '</button></form>';
		echo '<table class="widefat striped"><thead><tr><th>#</th><th>' . esc_html__( 'Name', 'vetra-dashboard' ) . '</th><th>' . esc_html__( 'Staff', 'vetra-dashboard' ) . '</th><th></th></tr></thead><tbody>';
		if ( empty( $items ) ) {
			self::empty_state( 4 );
		}
		foreach ( $items as $item ) {
			echo '<tr><td>' . (int) $item->department_id . '</td><td>' . esc_html( $item->department_name ) . '</td><td>' . esc_html( $item->staff_ids ) . '</td>';
			echo '<td><a class="button button-small" href="' . esc_url( self::action_url( array( 'do' => 'delete_department', 'id' => $item->department_id ) ) ) . '">' . esc_html__( 'Delete', 'vetra-dashboard' ) . '</a></td></tr>';
		}
		echo '</tbody></table></div>';
	}

	public static function notifications() {
		global $wpdb;
		$table = VTD_DB::notifications();

		if ( ! empty( $_POST['vtd_notify_title'] ) && check_admin_referer( 'vtd_notification' ) ) {
			VTD_Notifications::create(
				wp_unslash( $_POST['vtd_notify_title'] ),
				wp_unslash( $_POST['vtd_notify_content'] ?? '' ),
				array(
					'audience'       => sanitize_key( $_POST['vtd_notify_audience'] ?? 'all' ),
					'audience_value' => sanitize_text_field( wp_unslash( $_POST['vtd_notify_value'] ?? '' ) ),
					'link'           => esc_url_raw( wp_unslash( $_POST['vtd_notify_link'] ?? '' ) ),
				)
			);
		}

		$paged = max( 1, (int) ( $_GET['paged'] ?? 1 ) );
		$per   = 20;
		$total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" );
		$items = self::items( $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$table} ORDER BY notification_id DESC LIMIT %d OFFSET %d", $per, ( $paged - 1 ) * $per ) ) );

		echo '<div class="wrap vtd-admin-wrap"><h1>' . esc_html__( 'Notifications', 'vetra-dashboard' ) . '</h1>';
		echo '<form method="post" class="vtd-admin-form">';
		wp_nonce_field( 'vtd_notification' );
		echo '<input type="text" name="vtd_notify_title" placeholder="' . esc_attr__( 'Title', 'vetra-dashboard' ) . '" required>';
		echo '<select name="vtd_notify_audience"><option value="all">' . esc_html__( 'All users', 'vetra-dashboard' ) . '</option><option value="user">' . esc_html__( 'Specific user', 'vetra-dashboard' ) . '</option><option value="role">' . esc_html__( 'Role', 'vetra-dashboard' ) . '</option></select>';
		echo '<input type="text" name="vtd_notify_value" placeholder="' . esc_attr__( 'User ID or role', 'vetra-dashboard' ) . '">';
		echo '<input type="text" name="vtd_notify_link" placeholder="' . esc_attr__( 'Link', 'vetra-dashboard' ) . '">';
		echo '<textarea name="vtd_notify_content" rows="2" placeholder="' . esc_attr__( 'Content', 'vetra-dashboard' ) . '"></textarea>';
		echo '<button class="button button-primary">' . esc_html__( 'Send', 'vetra-dashboard' ) . '</button></form>';
		echo '<table class="widefat striped"><thead><tr><th>#</th><th>' . esc_html__( 'Title', 'vetra-dashboard' ) . '</th><th>' . esc_html__( 'Audience', 'vetra-dashboard' ) . '</th><th>' . esc_html__( 'Date', 'vetra-dashboard' ) . '</th><th></th></tr></thead><tbody>';
		if ( empty( $items ) ) {
			self::empty_state( 5 );
		}
		foreach ( $items as $item ) {
			echo '<tr><td>' . (int) $item->notification_id . '</td><td>' . esc_html( $item->title ) . '</td><td>' . esc_html( self::value_label( $item->audience ) . ' ' . $item->audience_value ) . '</td><td>' . esc_html( vtd_date_i18n( $item->created_at ) ) . '</td>';
			echo '<td><a class="button button-small" href="' . esc_url( self::action_url( array( 'do' => 'delete_notification', 'id' => $item->notification_id ) ) ) . '">' . esc_html__( 'Delete', 'vetra-dashboard' ) . '</a></td></tr>';
		}
		echo '</tbody></table>';
		self::pagination( $total, $per, $paged );
		echo '</div>';
	}

	public static function polls() {
		global $wpdb;
		$table = VTD_DB::polls();

		if ( ! empty( $_POST['vtd_poll_title'] ) && check_admin_referer( 'vtd_poll_admin' ) ) {
			$choices = array_filter( array_map( 'sanitize_text_field', array_map( 'trim', explode( "\n", wp_unslash( $_POST['vtd_poll_choices'] ?? '' ) ) ) ) );
			$keyed   = array();
			foreach ( $choices as $i => $choice ) {
				$keyed[ $i + 1 ] = $choice;
			}
			$wpdb->insert(
				$table,
				array(
					'poll_title'   => sanitize_text_field( wp_unslash( $_POST['vtd_poll_title'] ) ),
					'poll_type'    => (int) ( $_POST['vtd_poll_type'] ?? 0 ),
					'poll_choices' => maybe_serialize( $keyed ),
					'poll_status'  => 1,
					'created_at'   => current_time( 'mysql' ),
				),
				array( '%s', '%d', '%s', '%d', '%s' )
			);
		}

		$items = self::items( $wpdb->get_results( "SELECT * FROM {$table} ORDER BY poll_id DESC" ) );
		echo '<div class="wrap vtd-admin-wrap"><h1>' . esc_html__( 'Polls', 'vetra-dashboard' ) . '</h1>';
		echo '<form method="post" class="vtd-admin-form">';
		wp_nonce_field( 'vtd_poll_admin' );
		echo '<input type="text" name="vtd_poll_title" placeholder="' . esc_attr__( 'Question', 'vetra-dashboard' ) . '" required>';
		echo '<select name="vtd_poll_type"><option value="0">' . esc_html__( 'Single choice', 'vetra-dashboard' ) . '</option><option value="1">' . esc_html__( 'Multiple choice', 'vetra-dashboard' ) . '</option></select>';
		echo '<textarea name="vtd_poll_choices" rows="4" placeholder="' . esc_attr__( 'One option per line', 'vetra-dashboard' ) . '" required></textarea>';
		echo '<button class="button button-primary">' . esc_html__( 'Create poll', 'vetra-dashboard' ) . '</button></form>';
		echo '<table class="widefat striped"><thead><tr><th>#</th><th>' . esc_html__( 'Question', 'vetra-dashboard' ) . '</th><th>' . esc_html__( 'Type', 'vetra-dashboard' ) . '</th><th>' . esc_html__( 'Participants', 'vetra-dashboard' ) . '</th><th></th></tr></thead><tbody>';
		if ( empty( $items ) ) {
			self::empty_state( 5 );
		}
		foreach ( $items as $item ) {
			echo '<tr><td>' . (int) $item->poll_id . '</td><td>' . esc_html( $item->poll_title ) . '</td><td>' . ( $item->poll_type ? esc_html__( 'Multiple', 'vetra-dashboard' ) : esc_html__( 'Single', 'vetra-dashboard' ) ) . '</td><td>' . (int) VTD_Polls::participants( $item->poll_id ) . '</td>';
			echo '<td><a class="button button-small" href="' . esc_url( self::action_url( array( 'do' => 'delete_poll', 'id' => $item->poll_id ) ) ) . '">' . esc_html__( 'Delete', 'vetra-dashboard' ) . '</a></td></tr>';
		}
		echo '</tbody></table></div>';
	}

	public static function attachments() {
		global $wpdb;
		$table = VTD_DB::attachments();

		if ( ! empty( $_POST['vtd_file_title'] ) && check_admin_referer( 'vtd_attachment_admin' ) ) {
			$wpdb->insert(
				$table,
				array(
					'file_title'     => sanitize_text_field( wp_unslash( $_POST['vtd_file_title'] ) ),
					'file_url'       => esc_url_raw( wp_unslash( $_POST['vtd_file_url'] ?? '' ) ),
					'file_password'  => sanitize_text_field( wp_unslash( $_POST['vtd_file_password'] ?? '' ) ),
					'attachment_ids' => maybe_serialize( array_filter( array_map( 'intval', explode( ',', wp_unslash( $_POST['vtd_file_ids'] ?? '' ) ) ) ) ),
					'target'         => sanitize_key( $_POST['vtd_file_target'] ?? 'all' ),
					'target_value'   => sanitize_text_field( wp_unslash( $_POST['vtd_file_target_value'] ?? '' ) ),
					'created_by'     => get_current_user_id(),
					'created_at'     => current_time( 'mysql' ),
				),
				array( '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s' )
			);
		}

		$items = self::items( $wpdb->get_results( "SELECT * FROM {$table} ORDER BY attachment_id DESC" ) );
		echo '<div class="wrap vtd-admin-wrap"><h1>' . esc_html__( 'Attachments', 'vetra-dashboard' ) . '</h1>';
		echo '<form method="post" class="vtd-admin-form">';
		wp_nonce_field( 'vtd_attachment_admin' );
		echo '<input type="text" name="vtd_file_title" placeholder="' . esc_attr__( 'Title', 'vetra-dashboard' ) . '" required>';
		echo '<input type="text" name="vtd_file_url" placeholder="' . esc_attr__( 'External URL (optional)', 'vetra-dashboard' ) . '">';
		echo '<input type="text" name="vtd_file_ids" placeholder="' . esc_attr__( 'Media attachment IDs (comma separated)', 'vetra-dashboard' ) . '">';
		echo '<input type="text" name="vtd_file_password" placeholder="' . esc_attr__( 'File password', 'vetra-dashboard' ) . '">';
		echo '<select name="vtd_file_target"><option value="all">' . esc_html__( 'All users', 'vetra-dashboard' ) . '</option><option value="role">' . esc_html__( 'Role', 'vetra-dashboard' ) . '</option><option value="user">' . esc_html__( 'User ID', 'vetra-dashboard' ) . '</option></select>';
		echo '<input type="text" name="vtd_file_target_value" placeholder="' . esc_attr__( 'Role or user ID', 'vetra-dashboard' ) . '">';
		echo '<button class="button button-primary">' . esc_html__( 'Add', 'vetra-dashboard' ) . '</button></form>';
		echo '<table class="widefat striped"><thead><tr><th>#</th><th>' . esc_html__( 'Title', 'vetra-dashboard' ) . '</th><th>' . esc_html__( 'Target', 'vetra-dashboard' ) . '</th><th>' . esc_html__( 'Date', 'vetra-dashboard' ) . '</th><th></th></tr></thead><tbody>';
		if ( empty( $items ) ) {
			self::empty_state( 5 );
		}
		foreach ( $items as $item ) {
			echo '<tr><td>' . (int) $item->attachment_id . '</td><td>' . esc_html( $item->file_title ) . '</td><td>' . esc_html( $item->target . ' ' . $item->target_value ) . '</td><td>' . esc_html( vtd_date_i18n( $item->created_at ) ) . '</td>';
			echo '<td><a class="button button-small" href="' . esc_url( self::action_url( array( 'do' => 'delete_attachment', 'id' => $item->attachment_id ) ) ) . '">' . esc_html__( 'Delete', 'vetra-dashboard' ) . '</a></td></tr>';
		}
		echo '</tbody></table></div>';
	}

	public static function cards() {
		global $wpdb;
		$table  = VTD_DB::cards();
		$status = isset( $_GET['card_status'] ) ? sanitize_key( wp_unslash( $_GET['card_status'] ) ) : '';
		$where  = $status ? $wpdb->prepare( 'WHERE card_status = %s', $status ) : '';
		$items  = self::items( $wpdb->get_results( "SELECT * FROM {$table} {$where} ORDER BY card_id DESC" ) );

		echo '<div class="wrap vtd-admin-wrap"><h1>' . esc_html__( 'Bank Cards', 'vetra-dashboard' ) . '</h1>';
		self::msg();
		echo '<table class="widefat striped"><thead><tr><th>#</th><th>' . esc_html__( 'User', 'vetra-dashboard' ) . '</th><th>' . esc_html__( 'Bank', 'vetra-dashboard' ) . '</th><th>' . esc_html__( 'Owner', 'vetra-dashboard' ) . '</th><th>' . esc_html__( 'Card', 'vetra-dashboard' ) . '</th><th>' . esc_html__( 'Status', 'vetra-dashboard' ) . '</th><th></th></tr></thead><tbody>';
		if ( empty( $items ) ) {
			self::empty_state( 7 );
		}
		foreach ( $items as $card ) {
			echo '<tr><td>' . (int) $card->card_id . '</td><td>' . esc_html( vtd_current_user_name( $card->user_id ) ) . '</td><td>' . esc_html( VTD_Banking::bank_label( $card->card_name ) ) . '</td><td>' . esc_html( $card->card_owner ) . '</td><td>' . esc_html( $card->card_number ) . '</td><td>' . esc_html( self::value_label( $card->card_status ) ) . '</td><td>';
			echo '<a class="button button-small" href="' . esc_url( self::action_url( array( 'do' => 'card_status', 'id' => $card->card_id, 'status' => 'approved' ) ) ) . '">' . esc_html__( 'Approve', 'vetra-dashboard' ) . '</a> ';
			echo '<a class="button button-small" href="' . esc_url( self::action_url( array( 'do' => 'card_status', 'id' => $card->card_id, 'status' => 'rejected' ) ) ) . '">' . esc_html__( 'Reject', 'vetra-dashboard' ) . '</a>';
			echo '</td></tr>';
		}
		echo '</tbody></table></div>';
	}

	public static function withdrawals() {
		global $wpdb;
		$table = VTD_DB::withdrawals();
		$items = self::items( $wpdb->get_results( "SELECT * FROM {$table} ORDER BY id DESC LIMIT 100" ) );

		echo '<div class="wrap vtd-admin-wrap"><h1>' . esc_html__( 'Withdrawal Requests', 'vetra-dashboard' ) . '</h1>';
		self::msg();
		echo '<table class="widefat striped"><thead><tr><th>#</th><th>' . esc_html__( 'User', 'vetra-dashboard' ) . '</th><th>' . esc_html__( 'Amount', 'vetra-dashboard' ) . '</th><th>' . esc_html__( 'Status', 'vetra-dashboard' ) . '</th><th>' . esc_html__( 'Date', 'vetra-dashboard' ) . '</th><th></th></tr></thead><tbody>';
		if ( empty( $items ) ) {
			self::empty_state( 6 );
		}
		foreach ( $items as $item ) {
			echo '<tr><td>' . (int) $item->id . '</td><td>' . esc_html( vtd_current_user_name( $item->user_id ) ) . '</td><td>' . esc_html( VTD_Wallet::format( $item->amount ) ) . '</td><td>' . esc_html( self::value_label( $item->status ) ) . '</td><td>' . esc_html( vtd_date_i18n( $item->created_at ) ) . '</td><td>';
			if ( 'pending' === $item->status ) {
				echo '<a class="button button-small" href="' . esc_url( self::action_url( array( 'do' => 'withdrawal', 'id' => $item->id, 'status' => 'paid' ) ) ) . '">' . esc_html__( 'Mark paid', 'vetra-dashboard' ) . '</a> ';
				echo '<a class="button button-small" href="' . esc_url( self::action_url( array( 'do' => 'withdrawal', 'id' => $item->id, 'status' => 'rejected' ) ) ) . '">' . esc_html__( 'Reject', 'vetra-dashboard' ) . '</a>';
			}
			echo '</td></tr>';
		}
		echo '</tbody></table></div>';
	}

	public static function wallet() {
		global $wpdb;

		if ( ! empty( $_POST['vtd_wallet_user'] ) && check_admin_referer( 'vtd_wallet_adjust' ) ) {
			$user_id = (int) $_POST['vtd_wallet_user'];
			$amount  = (float) $_POST['vtd_wallet_amount'];
			$type    = 'debit' === ( $_POST['vtd_wallet_type'] ?? 'credit' ) ? 'debit' : 'credit';
			if ( $user_id && $amount > 0 ) {
				VTD_Wallet::add_transaction( $user_id, $amount, $type, sanitize_text_field( wp_unslash( $_POST['vtd_wallet_details'] ?? '' ) ) );
			}
		}

		$wallets = self::items( $wpdb->get_results( 'SELECT w.*, u.display_name FROM ' . VTD_DB::wallets() . ' w LEFT JOIN ' . $wpdb->users . ' u ON u.ID = w.user_id ORDER BY w.balance DESC LIMIT 100' ) );

		echo '<div class="wrap vtd-admin-wrap"><h1>' . esc_html__( 'Wallets', 'vetra-dashboard' ) . '</h1>';
		echo '<form method="post" class="vtd-admin-form">';
		wp_nonce_field( 'vtd_wallet_adjust' );
		echo '<input type="number" name="vtd_wallet_user" placeholder="' . esc_attr__( 'User ID', 'vetra-dashboard' ) . '" required>';
		echo '<input type="number" name="vtd_wallet_amount" step="0.01" placeholder="' . esc_attr__( 'Amount', 'vetra-dashboard' ) . '" required>';
		echo '<select name="vtd_wallet_type"><option value="credit">' . esc_html__( 'Credit', 'vetra-dashboard' ) . '</option><option value="debit">' . esc_html__( 'Debit', 'vetra-dashboard' ) . '</option></select>';
		echo '<input type="text" name="vtd_wallet_details" placeholder="' . esc_attr__( 'Details', 'vetra-dashboard' ) . '">';
		echo '<button class="button button-primary">' . esc_html__( 'Apply', 'vetra-dashboard' ) . '</button></form>';
		echo '<table class="widefat striped"><thead><tr><th>' . esc_html__( 'User', 'vetra-dashboard' ) . '</th><th>' . esc_html__( 'Balance', 'vetra-dashboard' ) . '</th></tr></thead><tbody>';
		if ( empty( $wallets ) ) {
			self::empty_state( 2 );
		}
		foreach ( $wallets as $wallet ) {
			echo '<tr><td>' . esc_html( $wallet->display_name ? $wallet->display_name : '#' . $wallet->user_id ) . '</td><td>' . esc_html( VTD_Wallet::format( $wallet->balance ) ) . '</td></tr>';
		}
		echo '</tbody></table></div>';
	}

	public static function sms_log() {
		global $wpdb;
		$paged = max( 1, (int) ( $_GET['paged'] ?? 1 ) );
		$per   = 30;
		$table = VTD_DB::sms_log();
		$total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" );
		$items = self::items( $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$table} ORDER BY id DESC LIMIT %d OFFSET %d", $per, ( $paged - 1 ) * $per ) ) );

		echo '<div class="wrap vtd-admin-wrap"><h1>' . esc_html__( 'SMS Log', 'vetra-dashboard' ) . '</h1>';
		echo '<table class="widefat striped"><thead><tr><th>#</th><th>' . esc_html__( 'Phone', 'vetra-dashboard' ) . '</th><th>' . esc_html__( 'Message', 'vetra-dashboard' ) . '</th><th>' . esc_html__( 'Context', 'vetra-dashboard' ) . '</th><th>' . esc_html__( 'Status', 'vetra-dashboard' ) . '</th><th>' . esc_html__( 'Date', 'vetra-dashboard' ) . '</th></tr></thead><tbody>';
		if ( empty( $items ) ) {
			self::empty_state( 6 );
		}
		foreach ( $items as $item ) {
			echo '<tr><td>' . (int) $item->id . '</td><td dir="ltr">' . esc_html( $item->phone ) . '</td><td>' . esc_html( vtd_excerpt( $item->message, 60 ) ) . '</td><td>' . esc_html( self::value_label( $item->context ) ) . '</td><td>' . esc_html( self::value_label( $item->status ) ) . '</td><td>' . esc_html( vtd_date_i18n( $item->created_at ) ) . '</td></tr>';
		}
		echo '</tbody></table>';
		self::pagination( $total, $per, $paged );
		echo '</div>';
	}

	public static function users() {
		$search = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';
		$paged  = max( 1, (int) ( $_GET['paged'] ?? 1 ) );
		$per    = 25;

		$query = new WP_User_Query(
			array(
				'search'         => $search ? '*' . $search . '*' : '',
				'search_columns' => array( 'user_login', 'user_email', 'display_name' ),
				'number'         => $per,
				'offset'         => ( $paged - 1 ) * $per,
				'orderby'        => 'registered',
				'order'          => 'DESC',
			)
		);

		echo '<div class="wrap vtd-admin-wrap"><h1>' . esc_html__( 'Users', 'vetra-dashboard' ) . '</h1>';
		echo '<form method="get" class="vtd-admin-filters"><input type="hidden" name="page" value="vetra-users"><input type="search" name="s" value="' . esc_attr( $search ) . '"><button class="button">' . esc_html__( 'Search', 'vetra-dashboard' ) . '</button></form>';
		echo '<table class="widefat striped"><thead><tr><th>' . esc_html__( 'Name', 'vetra-dashboard' ) . '</th><th>' . esc_html__( 'Username', 'vetra-dashboard' ) . '</th><th>' . esc_html__( 'Phone', 'vetra-dashboard' ) . '</th><th>' . esc_html__( 'Verified', 'vetra-dashboard' ) . '</th><th>' . esc_html__( 'Balance', 'vetra-dashboard' ) . '</th><th></th></tr></thead><tbody>';
		$users = $query->get_results();
		if ( empty( $users ) ) {
			self::empty_state( 6 );
		}
		foreach ( $users as $user ) {
			$phone_ok = VTD_Auth::is_phone_verified( $user->ID );
			$email_ok = VTD_Auth::is_email_verified( $user->ID );
			echo '<tr>';
			echo '<td>' . esc_html( vtd_current_user_name( $user->ID ) ) . '</td>';
			echo '<td>' . esc_html( $user->user_login ) . '</td>';
			echo '<td dir="ltr">' . esc_html( VTD_Auth::get_phone( $user->ID ) ) . '</td>';
			echo '<td>' . ( $phone_ok ? esc_html__( 'Phone', 'vetra-dashboard' ) : '' ) . ' ' . ( $email_ok ? esc_html__( 'Email', 'vetra-dashboard' ) : '' ) . '</td>';
			echo '<td>' . esc_html( VTD_Options::get( 'wallet_enabled', 1 ) ? VTD_Wallet::format( VTD_Wallet::balance( $user->ID ) ) : '-' ) . '</td>';
			echo '<td>';
			if ( ! $phone_ok ) {
				echo '<a class="button button-small" href="' . esc_url( self::action_url( array( 'do' => 'verify_user', 'field' => 'phone', 'id' => $user->ID ) ) ) . '">' . esc_html__( 'Verify phone', 'vetra-dashboard' ) . '</a> ';
			}
			if ( ! $email_ok ) {
				echo '<a class="button button-small" href="' . esc_url( self::action_url( array( 'do' => 'verify_user', 'field' => 'email', 'id' => $user->ID ) ) ) . '">' . esc_html__( 'Verify email', 'vetra-dashboard' ) . '</a>';
			}
			echo '</td></tr>';
		}
		echo '</tbody></table>';
		self::pagination( (int) $query->get_total(), $per, $paged );
		echo '</div>';
	}

	protected static function pagination( $total, $per, $paged ) {
		$pages = (int) ceil( $total / max( 1, $per ) );
		if ( $pages < 2 ) {
			return;
		}
		echo '<div class="tablenav"><div class="tablenav-pages">';
		echo paginate_links(
			array(
				'base'    => add_query_arg( 'paged', '%#%' ),
				'format'  => '',
				'current' => $paged,
				'total'   => $pages,
			)
		);
		echo '</div></div>';
	}
}
