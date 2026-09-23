<?php
/**
 * Polls list.
 *
 * @package Vetra_Dashboard
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="vtd-polls">
	<?php if ( empty( $polls ) ) : ?>
		<?php echo VTD_Templates::module( 'alert', array( 'message' => __( 'No polls available.', 'vetra-dashboard' ), 'type' => 'info' ) ); // phpcs:ignore ?>
	<?php else : ?>
		<div class="vtd-grid-2">
			<?php foreach ( $polls as $vtd_poll ) : ?>
				<?php
				$poll      = $vtd_poll['poll'];
				$choices   = $vtd_poll['choices'];
				$answered  = $vtd_poll['answered'];
				$answers   = wp_list_pluck( $vtd_poll['answers'], 'user_choice' );
				$results   = $vtd_poll['results'];
				$total_v   = array_sum( $results );
				?>
				<section class="vtd-card vtd-poll">
					<h3><?php echo esc_html( $poll->poll_title ); ?></h3>
					<p class="vtd-muted">
						<?php echo 0 === (int) $poll->poll_type ? esc_html__( 'Single choice', 'vetra-dashboard' ) : esc_html__( 'Multiple choice', 'vetra-dashboard' ); ?>
						&middot; <?php printf( esc_html__( '%d participants', 'vetra-dashboard' ), (int) $vtd_poll['participants'] ); ?>
					</p>
					<form data-vtd-poll="<?php echo (int) $poll->poll_id; ?>">
						<?php foreach ( $choices as $key => $label ) : ?>
							<?php $count = (int) ( $results[ (string) $key ] ?? 0 ); ?>
							<label class="vtd-poll-option <?php echo $answered ? 'is-answered' : ''; ?>">
								<input type="<?php echo 0 === (int) $poll->poll_type ? 'radio' : 'checkbox'; ?>"
									name="choices[]" value="<?php echo esc_attr( $key ); ?>"
									<?php checked( in_array( (string) $key, $answers, true ) ); ?> <?php disabled( (bool) $answered ); ?>>
								<span class="vtd-poll-label"><?php echo esc_html( $label ); ?></span>
								<?php if ( $answered ) : ?>
									<span class="vtd-poll-bar"><i style="width:<?php echo $total_v ? esc_attr( round( $count / $total_v * 100 ) ) : 0; ?>%"></i></span>
									<span class="vtd-poll-count"><?php echo (int) $count; ?></span>
								<?php endif; ?>
							</label>
						<?php endforeach; ?>
						<?php if ( ! $answered ) : ?>
							<button type="submit" class="vtd-btn vtd-btn-primary"><?php esc_html_e( 'Submit vote', 'vetra-dashboard' ); ?></button>
						<?php else : ?>
							<span class="vtd-chip is-ok"><?php esc_html_e( 'You have voted', 'vetra-dashboard' ); ?></span>
						<?php endif; ?>
						<span class="vtd-form-msg" data-vtd-form-msg></span>
					</form>
				</section>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>
</div>
