<?php
/**
 * Attachments and downloads.
 *
 * @package Vetra_Dashboard
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="vtd-attachments">
	<?php if ( empty( $items ) ) : ?>
		<?php echo VTD_Templates::module( 'alert', array( 'message' => __( 'No attachments found.', 'vetra-dashboard' ), 'type' => 'info' ) ); // phpcs:ignore ?>
	<?php else : ?>
		<div class="vtd-grid-3">
			<?php foreach ( $items as $item ) : ?>
				<section class="vtd-card vtd-file">
					<span class="vtd-file-icon"><?php echo vtd_icon( 'download' ); // phpcs:ignore ?></span>
					<h4><?php echo esc_html( $item['attachment']->file_title ); ?></h4>
					<?php if ( ! empty( $item['attachment']->file_password ) ) : ?>
						<p class="vtd-muted"><?php echo vtd_icon( 'lock' ); // phpcs:ignore ?> <?php echo esc_html( $item['attachment']->file_password ); ?></p>
					<?php endif; ?>
					<div class="vtd-file-actions">
						<?php foreach ( $item['files'] as $file ) : ?>
							<a class="vtd-btn vtd-btn-outline" href="<?php echo esc_url( $file['url'] ); ?>" download>
								<?php esc_html_e( 'Download', 'vetra-dashboard' ); ?>
							</a>
						<?php endforeach; ?>
					</div>
				</section>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</div>
