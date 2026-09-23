<?php
/**
 * Front-end login modal.
 *
 * @package Vetra_Dashboard
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="vtd-modal" data-vtd-login-modal hidden>
	<div class="vtd-modal-backdrop" data-vtd-modal-close></div>
	<div class="vtd-modal-dialog" role="dialog" aria-modal="true">
		<button type="button" class="vtd-modal-close" data-vtd-modal-close aria-label="<?php esc_attr_e( 'Close', 'vetra-dashboard' ); ?>">&times;</button>
		<?php echo VTD_Templates::auth( 'login', array( 'vtd_modal' => true ) ); // phpcs:ignore ?>
	</div>
</div>
