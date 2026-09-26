<?php
/**
 * Shared helper functions.
 *
 * @package Vetra_Dashboard
 */

defined( 'ABSPATH' ) || exit;

function vtd_get_option( $key, $default = null ) {
	return VTD_Options::get( $key, $default );
}

function vtd_update_option( $key, $value ) {
	return VTD_Options::update( $key, $value );
}

function vtd_get_settings() {
	return VTD_Options::all();
}

function vtd_rest_url() {
	return rest_url( 'vetra/v1' );
}

function vtd_panel_url( $args = array() ) {
	$url = VTD_Router::panel_url();
	if ( ! empty( $args ) ) {
		$url = add_query_arg( $args, $url );
	}
	return $url;
}

function vtd_login_url( $args = array() ) {
	$url = VTD_Router::login_url();
	if ( ! empty( $args ) ) {
		$url = add_query_arg( $args, $url );
	}
	return $url;
}

function vtd_register_url( $args = array() ) {
	$url = VTD_Router::register_url();
	if ( ! empty( $args ) ) {
		$url = add_query_arg( $args, $url );
	}
	return $url;
}

function vtd_reset_url() {
	return VTD_Router::reset_url();
}

function vtd_ticket_url( $ticket_id ) {
	return VTD_Router::ticket_url( $ticket_id );
}

function vtd_module_enabled( $module ) {
	return VTD_Modules::enabled( $module );
}

function vtd_module_section_enabled( $section ) {
	return VTD_Modules::section_enabled( $section );
}

/** Register a dashboard area from another plugin or theme. */
function vtd_register_panel_section( $slug, $args ) {
	return VTD_Router::register_section( $slug, $args );
}

function vtd_current_user_name( $user_id = 0 ) {
	$user_id = $user_id ? (int) $user_id : get_current_user_id();
	$user    = get_userdata( $user_id );
	if ( ! $user ) {
		return '';
	}
	$name = trim( $user->first_name . ' ' . $user->last_name );
	if ( '' === $name ) {
		$name = $user->display_name;
	}
	return $name;
}

function vtd_get_meta( $user_id, $key, $default = '' ) {
	$value = get_user_meta( $user_id, $key, true );
	return '' === $value || null === $value ? $default : $value;
}

function vtd_is_staff( $user_id = 0 ) {
	$user_id = $user_id ? (int) $user_id : get_current_user_id();
	if ( user_can( $user_id, 'manage_options' ) ) {
		return true;
	}
	return (bool) apply_filters( 'vtd_is_support_staff', user_can( $user_id, 'vtd_support_staff' ), $user_id );
}

function vtd_user_roles() {
	$roles = array();
	foreach ( wp_roles()->roles as $key => $role ) {
		$roles[ $key ] = $role['name'];
	}
	return $roles;
}

function vtd_sanitize_phone( $phone ) {
	$phone = preg_replace( '/[^0-9+]/', '', (string) $phone );
	$phone = ltrim( $phone, '+' );
	if ( 0 === strpos( $phone, '0098' ) ) {
		$phone = '98' . substr( $phone, 4 );
	}
	if ( 0 === strpos( $phone, '98' ) ) {
		return $phone;
	}
	if ( 0 === strpos( $phone, '9' ) && 10 === strlen( $phone ) ) {
		return '98' . $phone;
	}
	if ( 0 === strpos( $phone, '0' ) && 11 === strlen( $phone ) ) {
		return '98' . substr( $phone, 1 );
	}
	return $phone;
}

function vtd_local_phone( $phone ) {
	$phone = vtd_sanitize_phone( $phone );
	return preg_replace( '/^98/', '0', $phone );
}

function vtd_e164( $phone ) {
	return '+' . vtd_sanitize_phone( $phone );
}

function vtd_render( $template, $args = array() ) {
	$file = VTD_TEMPLATES . ltrim( $template, '/\\' );
	if ( ! file_exists( $file ) ) {
		$file .= '.php';
	}
	if ( ! file_exists( $file ) ) {
		return '';
	}
	$args = is_array( $args ) ? $args : array();
	extract( $args, EXTR_SKIP );
	ob_start();
	include $file;
	return ob_get_clean();
}

function vtd_get_template( $template, $args = array() ) {
	echo vtd_render( $template, $args );
}

function vtd_icon( $name, $class = '' ) {
	$icons = VTD_Assets::icons();
	$svg   = isset( $icons[ $name ] ) ? $icons[ $name ] : $icons['default'];
	return '<span class="vtd-icon ' . esc_attr( $class ) . '" aria-hidden="true">' . $svg . '</span>';
}

function vtd_date_i18n( $timestamp ) {
	$timestamp = is_numeric( $timestamp ) ? (int) $timestamp : strtotime( (string) $timestamp );
	if ( ! $timestamp ) {
		return '';
	}
	$gregorian = date_i18n( 'Y-n-j H:i', $timestamp );
	if ( ! preg_match( '/^(\d+)-(\d+)-(\d+) (\d{2}:\d{2})$/', $gregorian, $parts ) ) {
		return date_i18n( 'Y/m/d H:i', $timestamp );
	}
	$jalali = vtd_gregorian_to_jalali( (int) $parts[1], (int) $parts[2], (int) $parts[3] );
	return vtd_fa_digits( sprintf( '%04d/%02d/%02d %s', $jalali['year'], $jalali['month'], $jalali['day'], $parts[4] ) );
}

/** Convert a Gregorian date to its Jalali equivalent. */
function vtd_gregorian_to_jalali( $year, $month = null, $day = null ) {
	if ( null === $month && is_string( $year ) && preg_match( '/^(\d{4})[-\/]?(\d{1,2})[-\/]?(\d{1,2})/', $year, $parts ) ) {
		$year  = (int) $parts[1];
		$month = (int) $parts[2];
		$day   = (int) $parts[3];
	}
	$year  = (int) $year;
	$month = (int) $month;
	$day   = (int) $day;
	if ( $year < 1 || $month < 1 || $month > 12 || $day < 1 || $day > 31 ) {
		return array( 'year' => 0, 'month' => 0, 'day' => 0 );
	}
	$day_number = _vtd_gregorian_day_number( $year, $month, $day );
	$gy         = _vtd_day_number_to_gregorian( $day_number );
	$jy         = $gy['year'] - 621;
	$cal        = _vtd_jalali_calculate( $jy );
	$start      = _vtd_gregorian_day_number( $cal['gy'], 3, $cal['march'] );
	$offset     = $day_number - $start;
	if ( $offset < 0 ) {
		--$jy;
		$cal    = _vtd_jalali_calculate( $jy );
		$start  = _vtd_gregorian_day_number( $cal['gy'], 3, $cal['march'] );
		$offset = $day_number - $start;
	}
	if ( $offset <= 185 ) {
		$jm = 1 + (int) floor( $offset / 31 );
		$jd = ( $offset % 31 ) + 1;
	} else {
		$offset -= 186;
		$jm = 7 + (int) floor( $offset / 30 );
		$jd = ( $offset % 30 ) + 1;
	}
	return array( 'year' => $jy, 'month' => $jm, 'day' => $jd );
}

/** Convert a Jalali date string to the ISO Gregorian format used for storage. */
function vtd_jalali_to_gregorian( $date ) {
	$date = _vtd_normalize_digits( trim( (string) $date ) );
	if ( preg_match( '/^(\d{4})-(\d{1,2})-(\d{1,2})$/', $date, $parts ) ) {
		return checkdate( (int) $parts[2], (int) $parts[3], (int) $parts[1] ) ? sprintf( '%04d-%02d-%02d', $parts[1], $parts[2], $parts[3] ) : '';
	}
	if ( ! preg_match( '/^(\d{4})[\/\-.](\d{1,2})[\/\-.](\d{1,2})$/', $date, $parts ) ) {
		return '';
	}
	$jy = (int) $parts[1];
	$jm = (int) $parts[2];
	$jd = (int) $parts[3];
	if ( $jy < 1200 || $jy > 1600 || $jm < 1 || $jm > 12 || $jd < 1 || $jd > ( $jm <= 6 ? 31 : ( $jm <= 11 ? 30 : 30 ) ) ) {
		return '';
	}
	$cal = _vtd_jalali_calculate( $jy );
	if ( 12 === $jm && 30 === $jd && 0 !== $cal['leap'] ) {
		return '';
	}
	$day_number = _vtd_gregorian_day_number( $cal['gy'], 3, $cal['march'] ) + ( $jm - 1 ) * 31 - (int) floor( $jm / 7 ) * ( $jm - 7 ) + $jd - 1;
	$gregorian  = _vtd_day_number_to_gregorian( $day_number );
	return sprintf( '%04d-%02d-%02d', $gregorian['year'], $gregorian['month'], $gregorian['day'] );
}

function vtd_jalali_input_value( $date ) {
	$iso = vtd_jalali_to_gregorian( $date );
	if ( '' === $iso ) {
		return trim( (string) $date );
	}
	$parts = explode( '-', $iso );
	$jalali = vtd_gregorian_to_jalali( (int) $parts[0], (int) $parts[1], (int) $parts[2] );
	return vtd_fa_digits( sprintf( '%04d/%02d/%02d', $jalali['year'], $jalali['month'], $jalali['day'] ) );
}

function vtd_jalali_date_input( $name, $value = '', $required = false, $id = '' ) {
	$required_attr = $required ? ' required' : '';
	$id_attr       = $id ? ' id="' . esc_attr( $id ) . '"' : '';
	return '<input type="text" inputmode="numeric" autocomplete="off" class="vtd-jalali-date" data-vtd-jalali-date name="' . esc_attr( $name ) . '" value="' . esc_attr( vtd_jalali_input_value( $value ) ) . '" placeholder="۱۴۰۵/۰۱/۰۱" aria-label="' . esc_attr__( 'تاریخ شمسی', 'vetra-dashboard' ) . '"' . $id_attr . $required_attr . '>';
}

function vtd_fa_digits( $value ) {
	return strtr( (string) $value, array( '0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴', '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹' ) );
}

function _vtd_normalize_digits( $value ) {
	return strtr( (string) $value, array( '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4', '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9', '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4', '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9' ) );
}

function _vtd_jalali_div( $a, $b ) {
	return intdiv( (int) $a, (int) $b );
}

function _vtd_jalali_mod( $a, $b ) {
	return $a - _vtd_jalali_div( $a, $b ) * $b;
}

function _vtd_jalali_calculate( $jy ) {
	$breaks = array( -61, 9, 38, 199, 426, 686, 756, 818, 1111, 1181, 1210, 1635, 2060, 2097, 2192, 2262, 2324, 2394, 2456, 3178 );
	$gy     = $jy + 621;
	$leap_j = -14;
	$jp     = $breaks[0];
	$jump   = 0;
	$jm     = 0;
	for ( $i = 1; $i < count( $breaks ); $i++ ) {
		$jm   = $breaks[ $i ];
		$jump = $jm - $jp;
		if ( $jy < $jm ) {
			break;
		}
		$leap_j += _vtd_jalali_div( $jump, 33 ) * 8 + _vtd_jalali_div( _vtd_jalali_mod( $jump, 33 ), 4 );
		$jp      = $jm;
	}
	$n      = $jy - $jp;
	$leap_j += _vtd_jalali_div( $n, 33 ) * 8 + _vtd_jalali_div( _vtd_jalali_mod( $n, 33 ) + 3, 4 );
	if ( 4 === _vtd_jalali_mod( $jump, 33 ) && 4 === $jump - $n ) {
		++$leap_j;
	}
	$leap_g = _vtd_jalali_div( $gy, 4 ) - _vtd_jalali_div( ( _vtd_jalali_div( $gy, 100 ) + 1 ) * 3, 4 ) - 150;
	$march  = 20 + $leap_j - $leap_g;
	if ( $jump - $n < 6 ) {
		$n = $n - $jump + _vtd_jalali_div( $jump + 4, 33 ) * 33;
	}
	$leap = _vtd_jalali_mod( _vtd_jalali_mod( $n + 1, 33 ) - 1, 4 );
	if ( -1 === $leap ) {
		$leap = 4;
	}
	return array( 'gy' => $gy, 'march' => $march, 'leap' => $leap );
}

function _vtd_gregorian_day_number( $gy, $gm, $gd ) {
	$d = _vtd_jalali_div( ( $gy + _vtd_jalali_div( $gm - 8, 6 ) + 100100 ) * 1461, 4 ) + _vtd_jalali_div( 153 * _vtd_jalali_mod( $gm + 9, 12 ) + 2, 5 ) + $gd - 34840408;
	return $d - _vtd_jalali_div( _vtd_jalali_div( $gy + 100100 + _vtd_jalali_div( $gm - 8, 6 ), 100 ) * 3, 4 ) + 752;
}

function _vtd_day_number_to_gregorian( $jdn ) {
	$j  = 4 * $jdn + 139361631;
	$j += _vtd_jalali_div( _vtd_jalali_div( 4 * $jdn + 183187720, 146097 ) * 3, 4 ) * 4 - 3908;
	$i  = _vtd_jalali_div( _vtd_jalali_mod( $j, 1461 ), 4 ) * 5 + 308;
	$gd = _vtd_jalali_div( _vtd_jalali_mod( $i, 153 ), 5 ) + 1;
	$gm = _vtd_jalali_mod( _vtd_jalali_div( $i, 153 ), 12 ) + 1;
	$gy = _vtd_jalali_div( $j, 1461 ) - 100100 + _vtd_jalali_div( 8 - $gm, 6 );
	return array( 'year' => $gy, 'month' => $gm, 'day' => $gd );
}

function vtd_time_ago( $timestamp ) {
	$timestamp = is_numeric( $timestamp ) ? (int) $timestamp : strtotime( (string) $timestamp );
	return sprintf( _x( '%s ago', 'time ago', 'vetra-dashboard' ), human_time_diff( $timestamp, current_time( 'timestamp' ) ) );
}

function vtd_verify_nonce( $nonce, $action ) {
	if ( ! wp_verify_nonce( $nonce, $action ) ) {
		return false;
	}
	return true;
}

function vtd_json_error( $message, $code = 'vtd_error', $status = 400 ) {
	return new WP_Error( $code, $message, array( 'status' => $status ) );
}

function vtd_first_char( $text ) {
	$text = (string) $text;
	if ( function_exists( 'mb_substr' ) ) {
		return mb_substr( $text, 0, 1 );
	}
	return substr( $text, 0, 1 );
}

function vtd_excerpt( $text, $length = 60 ) {
	$text = wp_strip_all_tags( (string) $text );
	if ( function_exists( 'mb_substr' ) ) {
		return mb_strlen( $text ) > $length ? mb_substr( $text, 0, $length ) . '…' : $text;
	}
	return strlen( $text ) > $length ? substr( $text, 0, $length ) . '…' : $text;
}

function vtd_random_code( $length = 5 ) {
	$length = max( 3, (int) $length );
	$code   = '';
	for ( $i = 0; $i < $length; $i++ ) {
		$code .= wp_rand( 0, 9 );
	}
	return $code;
}
