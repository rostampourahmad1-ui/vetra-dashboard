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
	return date_i18n( 'Y/m/d H:i', $timestamp );
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

function vtd_random_code( $length = 5 ) {
	$length = max( 3, (int) $length );
	$code   = '';
	for ( $i = 0; $i < $length; $i++ ) {
		$code .= wp_rand( 0, 9 );
	}
	return $code;
}
