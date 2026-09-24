<?php
/**
 * Activation, pages and schema upgrades.
 *
 * @package Vetra_Dashboard
 */

defined( 'ABSPATH' ) || exit;

class VTD_Install {

	public static function activate() {
		self::create_tables();
		VTD_Options::reset();
		self::create_pages();
		VTD_Roles::install();
		self::seed_departments();
		VTD_Options::all();
		update_option( 'vtd_db_version', VTD_DB_VERSION );
		if ( ! get_option( 'vtd_installed_at' ) ) {
			update_option( 'vtd_installed_at', current_time( 'mysql' ) );
		}
		flush_rewrite_rules();
	}

	public static function deactivate() {
		wp_clear_scheduled_hook( 'vtd_cleanup_otp' );
		flush_rewrite_rules();
	}

	public static function maybe_upgrade() {
		if ( get_option( 'vtd_db_version' ) !== VTD_DB_VERSION ) {
			self::create_tables();
			update_option( 'vtd_db_version', VTD_DB_VERSION );
		}
		VTD_Roles::maybe_sync();
	}

	public static function create_tables() {
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		foreach ( VTD_DB::schema() as $query ) {
			dbDelta( $query );
		}
	}

	public static function create_pages() {
		$pages = array(
			'panel_page'    => array( 'title' => 'پیشخوان کاربری', 'slug' => 'vtd-panel', 'shortcode' => '[vetra_dashboard]' ),
			'login_page'    => array( 'title' => 'ورود', 'slug' => 'vtd-login', 'shortcode' => '[vetra_login]' ),
			'register_page' => array( 'title' => 'ثبت‌نام', 'slug' => 'vtd-register', 'shortcode' => '[vetra_register]' ),
			'reset_page'    => array( 'title' => 'بازیابی گذرواژه', 'slug' => 'vtd-reset', 'shortcode' => '[vetra_reset_password]' ),
		);

		$settings = VTD_Options::all();
		$changed  = false;

		foreach ( $pages as $key => $page ) {
			if ( ! empty( $settings[ $key ] ) && get_post( $settings[ $key ] ) ) {
				continue;
			}
			$existing = get_page_by_path( $page['slug'] );
			if ( $existing ) {
				$settings[ $key ] = $existing->ID;
				$changed          = true;
				continue;
			}
			$id = wp_insert_post(
				array(
					'post_title'   => $page['title'],
					'post_name'    => $page['slug'],
					'post_content' => $page['shortcode'],
					'post_status'  => 'publish',
					'post_type'    => 'page',
				)
			);
			if ( $id && ! is_wp_error( $id ) ) {
				$settings[ $key ] = $id;
				$changed          = true;
			}
		}

		if ( $changed ) {
			VTD_Options::save( $settings );
		}
	}

	protected static function seed_departments() {
		global $wpdb;
		$table = VTD_DB::departments();
		$count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" );
		if ( $count > 0 ) {
			return;
		}
		$departments = (array) VTD_Options::get( 'ticket_departments', array() );
		foreach ( $departments as $department ) {
			$name = is_array( $department ) ? ( $department['name'] ?? '' ) : $department;
			if ( '' === $name ) {
				continue;
			}
			$wpdb->insert(
				$table,
				array(
					'department_name' => sanitize_text_field( $name ),
					'parent_id'       => 0,
					'description'     => '',
				),
				array( '%s', '%d', '%s' )
			);
		}
	}
}
