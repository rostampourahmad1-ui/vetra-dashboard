<?php
/**
 * Profile change requests: users request edits (with documents) and admins approve.
 *
 * @package Vetra_Dashboard
 */

defined( 'ABSPATH' ) || exit;

class VTD_Changes {

	const STATUS_PENDING  = 'pending';
	const STATUS_APPROVED = 'approved';
	const STATUS_REJECTED = 'rejected';

	public static function init() {
		add_action( 'vtd_change_requested', array( __CLASS__, 'notify_admins' ), 10, 3 );
	}

	public static function statuses() {
		return array(
			self::STATUS_PENDING  => 'در انتظار بررسی',
			self::STATUS_APPROVED => 'تأیید شده',
			self::STATUS_REJECTED => 'رد شده',
		);
	}

	public static function status_label( $status ) {
		$statuses = self::statuses();
		return $statuses[ $status ] ?? $status;
	}

	/** All profile fields a change request may target. */
	public static function field_labels() {
		$labels = array(
			'first_name'    => 'نام',
			'last_name'     => 'نام خانوادگی',
			'national_code' => 'کد ملی',
			'phone'         => 'شماره موبایل',
			'email'         => 'ایمیل',
			'birthday'      => 'تاریخ تولد',
			'gender'        => 'جنسیت',
			'about'         => 'درباره من',
			'website'       => 'وب‌سایت',
		);
		foreach ( (array) VTD_Options::get( 'profile_custom_fields', array() ) as $field ) {
			$slug = sanitize_key( $field['slug'] ?? '' );
			if ( '' !== $slug ) {
				$labels[ $slug ] = $field['label'] ?? $slug;
			}
		}
		return apply_filters( 'vtd_change_field_labels', $labels );
	}

	public static function field_label( $key ) {
		$labels = self::field_labels();
		return $labels[ $key ] ?? $key;
	}

	/** Fields the panel exposes as change requests. */
	public static function editable_fields() {
		$labels  = self::field_labels();
		$allowed = (array) VTD_Options::get( 'profile_change_fields', array() );
		$list    = array();
		foreach ( $allowed as $slug ) {
			$slug = sanitize_key( $slug );
			if ( '' !== $slug ) {
				$list[ $slug ] = $labels[ $slug ] ?? $slug;
			}
		}
		return apply_filters( 'vtd_change_editable_fields', $list );
	}

	/** Current stored value of a profile field. */
	public static function current_value( $user_id, $field ) {
		$user_id = (int) $user_id;
		$user    = get_userdata( $user_id );
		if ( ! $user ) {
			return '';
		}
		switch ( $field ) {
			case 'first_name':
				return $user->first_name;
			case 'last_name':
				return $user->last_name;
			case 'email':
				return $user->user_email;
			case 'phone':
				return VTD_Auth::get_phone( $user_id );
			case 'about':
				return get_user_meta( $user_id, 'description', true );
			case 'website':
				return $user->user_url;
			case 'gender':
				return get_user_meta( $user_id, 'vtd_gender', true );
			case 'birthday':
				return get_user_meta( $user_id, 'vtd_birthday', true );
		}
		return get_user_meta( $user_id, 'vtd_' . sanitize_key( $field ), true );
	}

	/** Normalize + validate a submitted value. */
	public static function validate( $field, $value ) {
		$field = sanitize_key( $field );
		$value = is_string( $value ) ? trim( wp_unslash( $value ) ) : $value;

		switch ( $field ) {
			case 'first_name':
			case 'last_name':
				$value = sanitize_text_field( $value );
				if ( '' === $value ) {
					return new WP_Error( 'vtd_change_value', sprintf( 'مقدار جدید برای «%s» الزامی است.', self::field_label( $field ) ) );
				}
				return $value;
			case 'national_code':
				$digits = preg_replace( '/\D/', '', _vtd_normalize_digits( (string) $value ) );
				if ( ! preg_match( '/^\d{10}$/', $digits ) ) {
					return new WP_Error( 'vtd_change_national_code', 'کد ملی باید ۱۰ رقم باشد.' );
				}
				return $digits;
			case 'phone':
				$digits = preg_replace( '/\D/', '', _vtd_normalize_digits( (string) $value ) );
				$phone  = vtd_sanitize_phone( $digits );
				if ( ! preg_match( '/^9\d{9}$/', $phone ) ) {
					return new WP_Error( 'vtd_change_phone', 'شماره موبایل معتبر نیست.' );
				}
				return $phone;
			case 'email':
				$email = sanitize_email( $value );
				if ( ! is_email( $email ) ) {
					return new WP_Error( 'vtd_change_email', 'ایمیل معتبر نیست.' );
				}
				return $email;
			case 'birthday':
				$iso = vtd_jalali_to_gregorian( _vtd_normalize_digits( (string) $value ) );
				if ( '' === $iso ) {
					return new WP_Error( 'vtd_change_birthday', 'تاریخ تولد شمسی معتبر وارد کنید.' );
				}
				return $iso;
			case 'gender':
				$value = sanitize_key( $value );
				if ( ! in_array( $value, array( 'male', 'female', 'other' ), true ) ) {
					return new WP_Error( 'vtd_change_gender', 'جنسیت انتخابی معتبر نیست.' );
				}
				return $value;
			case 'about':
				return wp_kses_post( $value );
			case 'website':
				return esc_url_raw( $value );
		}

		return sanitize_text_field( (string) $value );
	}

	/** Current pending request of a user (optionally for a single field). */
	public static function pending_for_user( $user_id, $field = '' ) {
		global $wpdb;
		$table = VTD_DB::change_requests();
		if ( '' !== $field ) {
			return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE user_id = %d AND field_key = %s AND status = %s ORDER BY request_id DESC LIMIT 1", $user_id, sanitize_key( $field ), self::STATUS_PENDING ) );
		}
		return $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$table} WHERE user_id = %d AND status = %s ORDER BY request_id DESC", $user_id, self::STATUS_PENDING ) );
	}

	public static function get( $request_id ) {
		global $wpdb;
		$table = VTD_DB::change_requests();
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE request_id = %d", $request_id ) );
	}

	public static function docs( $row ) {
		$stored = $row->docs ?? '';
		$ids    = is_string( $stored ) ? json_decode( $stored, true ) : $stored;
		if ( ! is_array( $ids ) ) {
			return array();
		}
		return array_values( array_filter( array_map( 'intval', $ids ) ) );
	}

	public static function docs_html( $row ) {
		$ids = self::docs( $row );
		if ( ! $ids ) {
			return '';
		}
		$html = '<div class="vtd-change-docs">';
		foreach ( $ids as $id ) {
			$url = wp_get_attachment_url( $id );
			if ( ! $url ) {
				continue;
			}
			$html .= '<a class="vtd-chip" href="' . esc_url( $url ) . '" target="_blank" rel="noopener">' . vtd_icon( 'download' ) . esc_html( get_the_title( $id ) ) . '</a>';
		}
		$html .= '</div>';
		return $html;
	}

	/** Upload identity documents attached to a request. */
	public static function upload_docs( $field = 'change_docs', $max = 5 ) {
		if ( empty( $_FILES[ $field ] ) ) {
			return array();
		}
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';

		$file  = $_FILES[ $field ]; // phpcs:ignore WordPress.Security.NonceVerification
		$names = is_array( $file['name'] ) ? $file['name'] : array( $file['name'] );
		$ids   = array();

		add_filter( 'upload_mimes', array( 'VTD_Tickets', 'allow_upload_mimes' ) );
		foreach ( $names as $index => $name ) {
			if ( count( $ids ) >= $max ) {
				break;
			}
			$error = is_array( $file['error'] ) ? (int) $file['error'][ $index ] : (int) $file['error'];
			if ( '' === $name || UPLOAD_ERR_OK !== $error ) {
				continue;
			}
			$size = is_array( $file['size'] ) ? (int) $file['size'][ $index ] : (int) $file['size'];
			if ( $size > 5 * MB_IN_BYTES ) {
				continue;
			}
			$single = array(
				'name'     => $name,
				'type'     => is_array( $file['type'] ) ? $file['type'][ $index ] : $file['type'],
				'tmp_name' => is_array( $file['tmp_name'] ) ? $file['tmp_name'][ $index ] : $file['tmp_name'],
				'error'    => $error,
				'size'     => $size,
			);
			$id = media_handle_sideload( $single, 0, 'مدارک تغییر اطلاعات کاربری' );
			if ( ! is_wp_error( $id ) ) {
				$ids[] = (int) $id;
			}
		}
		remove_filter( 'upload_mimes', array( 'VTD_Tickets', 'allow_upload_mimes' ) );

		return $ids;
	}

	/**
	 * Create (or refresh) a pending change request for one field.
	 *
	 * @return int|WP_Error
	 */
	public static function submit( $user_id, $field, $new_value, $docs = array(), $note = '' ) {
		$user_id = (int) $user_id;
		$field   = sanitize_key( $field );
		if ( ! $user_id || '' === $field ) {
			return new WP_Error( 'vtd_change_field', 'فیلد انتخابی معتبر نیست.' );
		}
		if ( ! isset( self::editable_fields()[ $field ] ) ) {
			return new WP_Error( 'vtd_change_not_allowed', 'این فیلد قابل درخواست تغییر نیست.' );
		}

		$value = self::validate( $field, $new_value );
		if ( is_wp_error( $value ) ) {
			return $value;
		}

		$current = self::current_value( $user_id, $field );
		if ( (string) $current === (string) $value ) {
			return new WP_Error( 'vtd_change_same', 'مقدار جدید با مقدار فعلی یکسان است.' );
		}

		$docs = array_values( array_filter( array_map( 'intval', (array) $docs ) ) );
		if ( VTD_Options::get( 'profile_change_require_docs', 1 ) && ! $docs ) {
			return new WP_Error( 'vtd_change_docs', 'بارگذاری مدارک برای ثبت درخواست تغییر الزامی است.' );
		}

		global $wpdb;
		$table   = VTD_DB::change_requests();
		$now     = current_time( 'mysql' );
		$existing = self::pending_for_user( $user_id, $field );

		$data = array(
			'field_label'   => self::field_label( $field ),
			'current_value' => is_scalar( $current ) ? (string) $current : '',
			'new_value'     => is_scalar( $value ) ? (string) $value : '',
			'docs'          => wp_json_encode( $docs ),
			'status'        => self::STATUS_PENDING,
			'stage'         => $docs ? 'docs_received' : 'submitted',
			'admin_note'    => sanitize_textarea_field( $note ),
			'updated_at'    => $now,
		);

		if ( $existing ) {
			$wpdb->update( $table, $data, array( 'request_id' => (int) $existing->request_id ), array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' ), array( '%d' ) );
			$request_id = (int) $existing->request_id;
		} else {
			$data['user_id']    = $user_id;
			$data['field_key']  = $field;
			$data['created_at'] = $now;
			$wpdb->insert( $table, $data, array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s' ) );
			$request_id = (int) $wpdb->insert_id;
		}

		if ( ! $request_id ) {
			return new WP_Error( 'vtd_change_failed', 'ثبت درخواست انجام نشد. لطفاً دوباره تلاش کنید.' );
		}

		do_action( 'vtd_change_requested', $request_id, $user_id, $field );

		return $request_id;
	}

	public static function counts() {
		global $wpdb;
		$table  = VTD_DB::change_requests();
		$counts = array( 'all' => 0, self::STATUS_PENDING => 0, self::STATUS_APPROVED => 0, self::STATUS_REJECTED => 0 );
		$rows   = $wpdb->get_results( "SELECT status, COUNT(*) AS total FROM {$table} GROUP BY status" );
		if ( is_array( $rows ) ) {
			foreach ( $rows as $row ) {
				$counts[ $row->status ] = (int) $row->total;
				$counts['all']         += (int) $row->total;
			}
		}
		return $counts;
	}

	public static function query( $args = array() ) {
		global $wpdb;
		$table = VTD_DB::change_requests();
		$args  = wp_parse_args( $args, array( 'user_id' => 0, 'status' => '', 'search' => '', 'per_page' => 20, 'paged' => 1 ) );

		$where  = array( '1=1' );
		$params = array();
		if ( $args['user_id'] ) {
			$where[]  = 'user_id = %d';
			$params[] = (int) $args['user_id'];
		}
		if ( $args['status'] && 'all' !== $args['status'] ) {
			$where[]  = 'status = %s';
			$params[] = sanitize_key( $args['status'] );
		}
		if ( '' !== trim( (string) $args['search'] ) ) {
			$like     = '%' . $wpdb->esc_like( sanitize_text_field( $args['search'] ) ) . '%';
			$where[]  = '(field_key LIKE %s OR field_label LIKE %s OR new_value LIKE %s)';
			$params[] = $like;
			$params[] = $like;
			$params[] = $like;
		}

		$where_sql = implode( ' AND ', $where );
		$total     = (int) $wpdb->get_var( $params ? $wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE {$where_sql}", $params ) : "SELECT COUNT(*) FROM {$table}" );
		$per_page  = max( 1, (int) $args['per_page'] );
		$offset    = ( max( 1, (int) $args['paged'] ) - 1 ) * $per_page;
		$sql       = "SELECT * FROM {$table} WHERE {$where_sql} ORDER BY created_at DESC LIMIT %d OFFSET %d";
		$items     = $wpdb->get_results( $wpdb->prepare( $sql, array_merge( $params, array( $per_page, $offset ) ) ) );

		return array( 'items' => is_array( $items ) ? $items : array(), 'total' => $total );
	}

	public static function list_for_user( $user_id, $limit = 20 ) {
		global $wpdb;
		$table = VTD_DB::change_requests();
		$rows  = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$table} WHERE user_id = %d ORDER BY request_id DESC LIMIT %d", $user_id, max( 1, (int) $limit ) ) );
		return is_array( $rows ) ? $rows : array();
	}

	/** Admin: ask the user for more documents before deciding. */
	public static function request_docs( $request_id, $note = '' ) {
		global $wpdb;
		$row = self::get( $request_id );
		if ( ! $row ) {
			return new WP_Error( 'vtd_change_missing', 'درخواست یافت نشد.' );
		}
		$wpdb->update(
			VTD_DB::change_requests(),
			array( 'stage' => 'docs_requested', 'docs_request' => sanitize_textarea_field( $note ), 'updated_at' => current_time( 'mysql' ) ),
			array( 'request_id' => (int) $request_id ),
			array( '%s', '%s', '%s' ),
			array( '%d' )
		);
		do_action( 'vtd_change_docs_requested', (int) $request_id, (int) $row->user_id, $note );
		return true;
	}

	/** User: upload the documents the admin asked for. */
	public static function submit_docs( $request_id, $user_id, $docs = array(), $note = '' ) {
		global $wpdb;
		$row = self::get( $request_id );
		if ( ! $row || (int) $row->user_id !== (int) $user_id ) {
			return new WP_Error( 'vtd_change_denied', 'دسترسی به این درخواست مجاز نیست.' );
		}
		$docs = array_values( array_filter( array_map( 'intval', (array) $docs ) ) );
		if ( ! $docs ) {
			return new WP_Error( 'vtd_change_docs', 'حداقل یک فایل باید بارگذاری شود.' );
		}
		$existing = self::docs( $row );
		$wpdb->update(
			VTD_DB::change_requests(),
			array(
				'docs'       => wp_json_encode( array_values( array_unique( array_merge( $existing, $docs ) ) ) ),
				'stage'      => 'docs_received',
				'admin_note' => sanitize_textarea_field( $note ),
				'updated_at' => current_time( 'mysql' ),
			),
			array( 'request_id' => (int) $request_id ),
			array( '%s', '%s', '%s', '%s' ),
			array( '%d' )
		);
		return true;
	}

	/** Admin: approve the request and apply the value to the user account. */
	public static function approve( $request_id, $note = '' ) {
		global $wpdb;
		$row = self::get( $request_id );
		if ( ! $row ) {
			return new WP_Error( 'vtd_change_missing', 'درخواست یافت نشد.' );
		}
		$applied = self::apply_value( $row );
		if ( is_wp_error( $applied ) ) {
			return $applied;
		}
		$wpdb->update(
			VTD_DB::change_requests(),
			array(
				'status'      => self::STATUS_APPROVED,
				'stage'       => 'approved',
				'admin_note'  => sanitize_textarea_field( $note ),
				'reviewed_by' => get_current_user_id(),
				'reviewed_at' => current_time( 'mysql' ),
				'updated_at'  => current_time( 'mysql' ),
			),
			array( 'request_id' => (int) $request_id ),
			array( '%s', '%s', '%s', '%d', '%s', '%s' ),
			array( '%d' )
		);
		do_action( 'vtd_change_approved', (int) $request_id, (int) $row->user_id, $row->field_key );
		return true;
	}

	public static function reject( $request_id, $note = '' ) {
		global $wpdb;
		$row = self::get( $request_id );
		if ( ! $row ) {
			return new WP_Error( 'vtd_change_missing', 'درخواست یافت نشد.' );
		}
		$wpdb->update(
			VTD_DB::change_requests(),
			array(
				'status'      => self::STATUS_REJECTED,
				'stage'       => 'rejected',
				'admin_note'  => sanitize_textarea_field( $note ),
				'reviewed_by' => get_current_user_id(),
				'reviewed_at' => current_time( 'mysql' ),
				'updated_at'  => current_time( 'mysql' ),
			),
			array( 'request_id' => (int) $request_id ),
			array( '%s', '%s', '%s', '%d', '%s', '%s' ),
			array( '%d' )
		);
		do_action( 'vtd_change_rejected', (int) $request_id, (int) $row->user_id, $row->field_key, $note );
		return true;
	}

	/** Write an approved value onto the user profile. */
	public static function apply_value( $row ) {
		$user_id = (int) $row->user_id;
		$field   = sanitize_key( $row->field_key );
		$value   = (string) $row->new_value;
		if ( ! get_userdata( $user_id ) ) {
			return new WP_Error( 'vtd_change_user', 'کاربر یافت نشد.' );
		}

		switch ( $field ) {
			case 'first_name':
			case 'last_name':
				wp_update_user( array( 'ID' => $user_id, $field => $value ) );
				self::sync_display_name( $user_id );
				return true;
			case 'email':
				if ( email_exists( $value ) && (int) email_exists( $value ) !== $user_id ) {
					return new WP_Error( 'vtd_change_email_exists', 'این ایمیل برای کاربر دیگری ثبت شده است.' );
				}
				wp_update_user( array( 'ID' => $user_id, 'user_email' => $value ) );
				return true;
			case 'phone':
				VTD_Auth::update_phone( $user_id, $value, 0 );
				return true;
			case 'about':
				wp_update_user( array( 'ID' => $user_id, 'description' => $value ) );
				return true;
			case 'website':
				wp_update_user( array( 'ID' => $user_id, 'user_url' => $value ) );
				return true;
			case 'birthday':
				update_user_meta( $user_id, 'vtd_birthday', $value );
				return true;
			case 'gender':
				update_user_meta( $user_id, 'vtd_gender', $value );
				return true;
		}

		$meta_key = 'national_code' === $field ? (string) VTD_Options::get( 'national_code_meta', 'national_code' ) : 'vtd_' . $field;
		update_user_meta( $user_id, $meta_key, $value );
		return true;
	}

	/** Keep the WordPress display name in sync with first + last name. */
	public static function sync_display_name( $user_id ) {
		if ( 'full_name' !== VTD_Options::get( 'display_name_format', 'full_name' ) ) {
			return;
		}
		$user = get_userdata( $user_id );
		if ( ! $user ) {
			return;
		}
		$name = trim( $user->first_name . ' ' . $user->last_name );
		if ( '' !== $name && $name !== $user->display_name ) {
			wp_update_user( array( 'ID' => $user_id, 'display_name' => $name ) );
		}
	}

	/** Tell the administrators that a new request is waiting. */
	public static function notify_admins( $request_id, $user_id, $field ) {
		$row = self::get( $request_id );
		if ( ! $row ) {
			return;
		}
		$user = get_userdata( $user_id );
		$name = $user ? trim( $user->first_name . ' ' . $user->last_name ) : '';
		$name = '' !== $name ? $name : ( $user ? $user->display_name : '' );

		$lines = array(
			sprintf( 'درخواست تغییر «%s» از سوی %s ثبت شد.', self::field_label( $field ), $name ),
			sprintf( 'مقدار فعلی: %s', $row->current_value ),
			sprintf( 'مقدار درخواستی: %s', $row->new_value ),
			sprintf( 'مشاهده درخواست: %s', admin_url( 'admin.php?page=vetra-changes' ) ),
		);

		VTD_Email::send(
			array( get_option( 'admin_email' ) ),
			'درخواست تغییر اطلاعات کاربری',
			'درخواست تغییر اطلاعات',
			wpautop( esc_html( implode( "\n", $lines ) ) ),
			array( 'context' => 'change' )
		);

		$admin_phone = VTD_Options::get( 'sms_admin_notify', '' );
		if ( VTD_Options::get( 'profile_sms_on_change', 0 ) && $admin_phone ) {
			VTD_SMS::send( $admin_phone, sprintf( 'درخواست تغییر %s برای %s', self::field_label( $field ), $name ), 'change' );
		}
	}
}
