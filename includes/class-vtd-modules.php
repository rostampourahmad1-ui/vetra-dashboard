<?php
/**
 * Module registry: enable/disable every dashboard module from one place.
 *
 * @package Vetra_Dashboard
 */

defined( 'ABSPATH' ) || exit;

class VTD_Modules {

	/**
	 * Registered modules. Every entry maps a dashboard area to its option key
	 * and to the panel sections that must disappear when it is disabled.
	 */
	public static function all() {
		return apply_filters(
			'vtd_modules',
			array(
				'dashboard'     => array(
					'label'    => 'داشبورد',
					'option'   => 'dashboard_enabled',
					'sections' => array( 'dashboard' ),
				),
				'profile'       => array(
					'label'    => 'پروفایل',
					'option'   => 'profile_enabled',
					'sections' => array( 'profile' ),
				),
				'tickets'       => array(
					'label'    => 'سیستم تیکتینگ',
					'option'   => 'ticket_enabled',
					'sections' => array( 'tickets', 'new-ticket', 'ticket', 'staff-tickets' ),
				),
				'notifications' => array(
					'label'    => 'اعلان‌ها',
					'option'   => 'notifications_enabled',
					'sections' => array( 'notifications' ),
				),
				'polls'         => array(
					'label'    => 'نظرسنجی‌ها',
					'option'   => 'polls_enabled',
					'sections' => array( 'polls' ),
				),
				'attachments'   => array(
					'label'    => 'پیوست‌ها و فایل‌ها',
					'option'   => 'attachments_enabled',
					'sections' => array( 'attachments' ),
				),
				'banking'       => array(
					'label'    => 'اطلاعات بانکی',
					'option'   => 'banking_enabled',
					'sections' => array( 'banking' ),
				),
				'wallet'        => array(
					'label'    => 'کیف پول',
					'option'   => 'wallet_enabled',
					'sections' => array( 'wallet' ),
				),
				'comments'      => array(
					'label'    => 'دیدگاه‌ها',
					'option'   => 'comments_enabled',
					'sections' => array( 'comments' ),
				),
			)
		);
	}

	public static function module( $key ) {
		$modules = self::all();
		return isset( $modules[ $key ] ) ? $modules[ $key ] : array();
	}

	/** Is a module enabled? Unknown modules are always considered enabled. */
	public static function enabled( $key ) {
		$module = self::module( $key );
		if ( empty( $module['option'] ) ) {
			return true;
		}
		return (bool) VTD_Options::get( $module['option'], 1 );
	}

	/** Which module owns a dashboard section? */
	public static function section_module( $section ) {
		$section = sanitize_key( $section );
		foreach ( self::all() as $key => $module ) {
			$sections = (array) ( $module['sections'] ?? array() );
			if ( in_array( $section, $sections, true ) ) {
				return $key;
			}
		}
		return '';
	}

	public static function section_enabled( $section ) {
		$module = self::section_module( $section );
		return '' === $module ? true : self::enabled( $module );
	}

	/** Control which panel sections are visible in the navigation. */
	public static function visible_sections( $sections ) {
		$visible = array();
		foreach ( (array) $sections as $slug => $section ) {
			if ( self::section_enabled( $slug ) ) {
				$visible[ $slug ] = $section;
			}
		}
		return $visible;
	}

	/** Admin screens that can be hidden from the Vetra menu. */
	public static function admin_pages() {
		return apply_filters(
			'vtd_admin_pages',
			array(
				'vetra-dashboard'     => 'نمای کلی',
				'vetra-settings'      => 'تنظیمات',
				'vetra-tickets'       => 'تیکت‌ها',
				'vetra-changes'       => 'درخواست تغییر اطلاعات',
				'vetra-departments'   => 'دپارتمان‌ها',
				'vetra-notifications' => 'اعلان‌ها',
				'vetra-polls'         => 'نظرسنجی‌ها',
				'vetra-attachments'   => 'پیوست‌ها',
				'vetra-cards'         => 'کارت‌های بانکی',
				'vetra-wallet'        => 'کیف پول',
				'vetra-withdrawals'   => 'برداشت‌ها',
				'vetra-sms-log'       => 'گزارش پیامک‌ها',
				'vetra-users'         => 'کاربران',
			)
		);
	}

	/** Pages that are locked on (cannot be hidden). */
	public static function always_visible_pages() {
		return array( 'vetra-dashboard', 'vetra-settings' );
	}

	public static function admin_page_visible( $slug ) {
		if ( in_array( $slug, self::always_visible_pages(), true ) ) {
			return true;
		}
		$stored = (array) VTD_Options::get( 'admin_modules', array() );
		if ( empty( $stored ) ) {
			return true;
		}
		foreach ( $stored as $item ) {
			if ( sanitize_key( $item['slug'] ?? '' ) === sanitize_key( $slug ) ) {
				return ! empty( $item['enabled'] );
			}
		}
		return true;
	}
}
