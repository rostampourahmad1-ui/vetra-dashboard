<?php
/**
 * Template loader.
 *
 * @package Vetra_Dashboard
 */

defined( 'ABSPATH' ) || exit;

class VTD_Templates {

	public static function locate( $relative ) {
		$relative = ltrim( str_replace( '\\', '/', $relative ), '/' );
		if ( substr( $relative, -4 ) !== '.php' ) {
			$relative .= '.php';
		}

		$theme = locate_template( array( 'vetra/' . $relative ) );
		if ( $theme ) {
			return $theme;
		}

		$file = VTD_TEMPLATES . $relative;
		return file_exists( $file ) ? $file : '';
	}

	public static function get( $relative, $args = array() ) {
		$file = self::locate( $relative );
		if ( ! $file ) {
			return '';
		}
		$args = is_array( $args ) ? $args : array();
		extract( $args, EXTR_SKIP );
		ob_start();
		include $file;
		return ob_get_clean();
	}

	public static function render( $relative, $args = array() ) {
		echo self::get( $relative, $args );
	}

	public static function panel() {
		return self::get( 'panel' );
	}

	public static function auth( $type, $args = array() ) {
		return self::get( 'auth/' . $type, $args );
	}

	public static function part( $name, $args = array() ) {
		return self::get( 'parts/' . $name, $args );
	}

	public static function module( $name, $args = array() ) {
		return self::get( 'modules/' . $name, $args );
	}
}
