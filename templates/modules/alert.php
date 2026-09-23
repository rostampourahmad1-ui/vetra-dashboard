<?php
/**
 * Generic alert.
 *
 * @package Vetra_Dashboard
 */

defined( 'ABSPATH' ) || exit;

$type    = isset( $type ) ? $type : 'info';
$message = isset( $message ) ? $message : '';
?>
<div class="vtd-alert vtd-alert-<?php echo esc_attr( $type ); ?>"><?php echo esc_html( $message ); ?></div>
