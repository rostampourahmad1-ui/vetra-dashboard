<?php
/**
 * Full-width panel page (no theme header/footer).
 *
 * @package Vetra_Dashboard
 */

defined( 'ABSPATH' ) || exit;
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'vtd-fullwidth vtd-body' ); ?>>
<?php echo do_shortcode( '[vetra_dashboard]' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
<?php wp_footer(); ?>
</body>
</html>
