<?php
/**
 * Uninstall routine.
 *
 * @package Vetra_Dashboard
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

$settings = get_option( 'vetra_settings', array() );
if ( empty( $settings['delete_data_on_uninstall'] ) ) {
	return;
}

global $wpdb;

$tables = array(
	'tickets',
	'ticket_replies',
	'ticket_meta',
	'departments',
	'ticket_rating',
	'polls',
	'poll_answers',
	'notifications',
	'notification_read',
	'attachments',
	'attachment_map',
	'cards',
	'wallets',
	'transactions',
	'withdrawals',
	'sms_log',
	'otp',
);

foreach ( $tables as $table ) {
	$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}vtd_{$table}" ); // phpcs:ignore
}

delete_option( 'vetra_settings' );
delete_option( 'vtd_db_version' );
delete_option( 'vtd_installed_at' );
