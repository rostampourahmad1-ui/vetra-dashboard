<?php
/**
 * Database schema and table accessors.
 *
 * @package Vetra_Dashboard
 */

defined( 'ABSPATH' ) || exit;

class VTD_DB {

	public static function table( $name ) {
		global $wpdb;
		return $wpdb->prefix . 'vtd_' . $name;
	}

	public static function tickets() {
		return self::table( 'tickets' );
	}

	public static function ticket_replies() {
		return self::table( 'ticket_replies' );
	}

	public static function ticket_meta() {
		return self::table( 'ticket_meta' );
	}

	public static function departments() {
		return self::table( 'departments' );
	}

	public static function ticket_rating() {
		return self::table( 'ticket_rating' );
	}

	public static function polls() {
		return self::table( 'polls' );
	}

	public static function poll_answers() {
		return self::table( 'poll_answers' );
	}

	public static function notifications() {
		return self::table( 'notifications' );
	}

	public static function notification_read() {
		return self::table( 'notification_read' );
	}

	public static function attachments() {
		return self::table( 'attachments' );
	}

	public static function attachment_map() {
		return self::table( 'attachment_map' );
	}

	public static function cards() {
		return self::table( 'cards' );
	}

	public static function wallets() {
		return self::table( 'wallets' );
	}

	public static function transactions() {
		return self::table( 'transactions' );
	}

	public static function withdrawals() {
		return self::table( 'withdrawals' );
	}

	public static function sms_log() {
		return self::table( 'sms_log' );
	}

	public static function otp() {
		return self::table( 'otp' );
	}

	public static function change_requests() {
		return self::table( 'change_requests' );
	}

	public static function schema() {
		global $wpdb;
		$charset = $wpdb->get_charset_collate();

		$sql = array();

		$sql[] = "CREATE TABLE " . self::departments() . " (
			department_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			department_name VARCHAR(191) NOT NULL,
			parent_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
			staff_ids LONGTEXT NULL,
			description TEXT NULL,
			PRIMARY KEY  (department_id)
		) $charset;";

		$sql[] = "CREATE TABLE " . self::tickets() . " (
			ticket_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			ticket_title VARCHAR(255) NOT NULL,
			ticket_content LONGTEXT NULL,
			user_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
			department_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
			priority VARCHAR(20) NOT NULL DEFAULT 'medium',
			status VARCHAR(20) NOT NULL DEFAULT 'open',
			starred TINYINT(1) NOT NULL DEFAULT 0,
			staff_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
			attachments LONGTEXT NULL,
			created_at DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
			updated_at DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
			PRIMARY KEY  (ticket_id),
			KEY user_id (user_id),
			KEY status (status),
			KEY department_id (department_id)
		) $charset;";

		$sql[] = "CREATE TABLE " . self::ticket_replies() . " (
			reply_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			ticket_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
			user_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
			content LONGTEXT NULL,
			attachments LONGTEXT NULL,
			is_staff TINYINT(1) NOT NULL DEFAULT 0,
			is_internal TINYINT(1) NOT NULL DEFAULT 0,
			read_by_user TINYINT(1) NOT NULL DEFAULT 0,
			created_at DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
			PRIMARY KEY  (reply_id),
			KEY ticket_id (ticket_id)
		) $charset;";

		$sql[] = "CREATE TABLE " . self::ticket_meta() . " (
			meta_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			ticket_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
			meta_key VARCHAR(191) NOT NULL,
			meta_value LONGTEXT NULL,
			PRIMARY KEY  (meta_id),
			KEY ticket_id (ticket_id)
		) $charset;";

		$sql[] = "CREATE TABLE " . self::ticket_rating() . " (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			ticket_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
			user_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
			score TINYINT(3) NOT NULL DEFAULT 0,
			feedback TEXT NULL,
			created_at DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
			PRIMARY KEY  (id),
			UNIQUE KEY ticket_user (ticket_id,user_id)
		) $charset;";

		$sql[] = "CREATE TABLE " . self::polls() . " (
			poll_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			poll_title VARCHAR(255) NOT NULL,
			poll_type TINYINT(1) NOT NULL DEFAULT 0,
			poll_choices LONGTEXT NULL,
			poll_status TINYINT(1) NOT NULL DEFAULT 1,
			poll_start DATETIME NULL,
			poll_end DATETIME NULL,
			created_at DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
			PRIMARY KEY  (poll_id)
		) $charset;";

		$sql[] = "CREATE TABLE " . self::poll_answers() . " (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			poll_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
			user_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
			user_choice VARCHAR(191) NOT NULL,
			created_at DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
			PRIMARY KEY  (id),
			KEY poll_id (poll_id)
		) $charset;";

		$sql[] = "CREATE TABLE " . self::notifications() . " (
			notification_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			title VARCHAR(255) NOT NULL,
			content LONGTEXT NULL,
			audience VARCHAR(20) NOT NULL DEFAULT 'all',
			audience_value VARCHAR(191) NULL,
			priority VARCHAR(20) NOT NULL DEFAULT 'normal',
			link VARCHAR(255) NULL,
			created_by BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
			created_at DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
			PRIMARY KEY  (notification_id)
		) $charset;";

		$sql[] = "CREATE TABLE " . self::notification_read() . " (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			notification_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
			user_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
			read_at DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
			PRIMARY KEY  (id),
			UNIQUE KEY notification_user (notification_id,user_id)
		) $charset;";

		$sql[] = "CREATE TABLE " . self::attachments() . " (
			attachment_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			file_title VARCHAR(255) NOT NULL,
			file_url VARCHAR(255) NULL,
			file_password VARCHAR(191) NULL,
			attachment_ids LONGTEXT NULL,
			target VARCHAR(20) NOT NULL DEFAULT 'all',
			target_value VARCHAR(191) NULL,
			created_by BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
			created_at DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
			PRIMARY KEY  (attachment_id)
		) $charset;";

		$sql[] = "CREATE TABLE " . self::attachment_map() . " (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			attachment_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
			user_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
			created_at DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
			PRIMARY KEY  (id),
			KEY user_id (user_id),
			KEY attachment_id (attachment_id)
		) $charset;";

		$sql[] = "CREATE TABLE " . self::cards() . " (
			card_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			user_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
			card_name VARCHAR(50) NOT NULL,
			card_owner VARCHAR(191) NOT NULL,
			card_number VARCHAR(32) NOT NULL,
			card_sheba VARCHAR(32) NOT NULL,
			card_photo BIGINT(20) UNSIGNED NULL,
			card_status VARCHAR(20) NOT NULL DEFAULT 'pending',
			created_at DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
			PRIMARY KEY  (card_id),
			KEY user_id (user_id)
		) $charset;";

		$sql[] = "CREATE TABLE " . self::wallets() . " (
			wallet_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			user_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
			balance DECIMAL(20,2) NOT NULL DEFAULT 0,
			PRIMARY KEY  (wallet_id),
			UNIQUE KEY user_id (user_id)
		) $charset;";

		$sql[] = "CREATE TABLE " . self::transactions() . " (
			tx_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			user_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
			amount DECIMAL(20,2) NOT NULL DEFAULT 0,
			type VARCHAR(10) NOT NULL DEFAULT 'credit',
			balance_after DECIMAL(20,2) NOT NULL DEFAULT 0,
			details TEXT NULL,
			created_by BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
			created_at DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
			PRIMARY KEY  (tx_id),
			KEY user_id (user_id)
		) $charset;";

		$sql[] = "CREATE TABLE " . self::withdrawals() . " (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			user_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
			amount DECIMAL(20,2) NOT NULL DEFAULT 0,
			card_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
			status VARCHAR(20) NOT NULL DEFAULT 'pending',
			note TEXT NULL,
			admin_note TEXT NULL,
			created_at DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
			updated_at DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
			PRIMARY KEY  (id),
			KEY user_id (user_id)
		) $charset;";

		$sql[] = "CREATE TABLE " . self::sms_log() . " (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			phone VARCHAR(32) NOT NULL,
			message TEXT NULL,
			provider VARCHAR(50) NULL,
			status VARCHAR(20) NULL,
			response TEXT NULL,
			context VARCHAR(50) NULL,
			created_at DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
			PRIMARY KEY  (id),
			KEY phone (phone)
		) $charset;";

		$sql[] = "CREATE TABLE " . self::otp() . " (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			phone VARCHAR(32) NOT NULL,
			code VARCHAR(20) NOT NULL,
			purpose VARCHAR(50) NOT NULL DEFAULT 'login',
			tries SMALLINT(5) NOT NULL DEFAULT 0,
			expires_at DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
			created_at DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
			PRIMARY KEY  (id),
			KEY phone (phone)
		) $charset;";

		$sql[] = "CREATE TABLE " . self::change_requests() . " (
			request_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			user_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
			field_name VARCHAR(100) NOT NULL,
			requested_value VARCHAR(255) NOT NULL,
			reason TEXT NULL,
			document_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
			status VARCHAR(20) NOT NULL DEFAULT 'pending',
			admin_note TEXT NULL,
			approved_by BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
			created_at DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
			updated_at DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
			PRIMARY KEY  (request_id),
			KEY user_id (user_id),
			KEY status (status)
		) $charset;";

		return $sql;
	}
}
