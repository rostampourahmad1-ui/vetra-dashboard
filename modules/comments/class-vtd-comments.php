<?php
/**
 * User comments module.
 *
 * @package Vetra_Dashboard
 */

defined( 'ABSPATH' ) || exit;

class VTD_Comments {

	public static function init() {}

	public static function get_comments( $user_id, $args = array() ) {
		$per   = isset( $args['per_page'] ) ? (int) $args['per_page'] : 10;
		$paged = isset( $args['paged'] ) ? max( 1, (int) $args['paged'] ) : 1;

		$comments = get_comments(
			array(
				'user_id' => $user_id,
				'number'  => $per,
				'offset'  => ( $paged - 1 ) * $per,
				'orderby' => 'comment_date_gmt',
				'order'   => 'DESC',
			)
		);

		return array(
			'comments' => $comments,
			'total'    => (int) get_comments( array( 'user_id' => $user_id, 'count' => true ) ),
			'paged'    => $paged,
			'per_page' => $per,
		);
	}

	public static function render() {
		$paged = max( 1, (int) ( $_GET['tpage'] ?? 1 ) );
		$data  = self::get_comments( get_current_user_id(), array( 'paged' => $paged ) );
		return VTD_Templates::module( 'comments', $data );
	}
}
