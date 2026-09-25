<?php
/**
 * Bank cards.
 *
 * @package Vetra_Dashboard
 */

defined( 'ABSPATH' ) || exit;

$vtd_statuses = array(
	'pending'  => __( 'Pending', 'vetra-dashboard' ),
	'approved' => __( 'Approved', 'vetra-dashboard' ),
	'rejected' => __( 'Rejected', 'vetra-dashboard' ),
);
?>
<div class="vtd-banking">
	<div class="vtd-grid-2">
		<section class="vtd-card">
			<h3><?php esc_html_e( 'Add bank card', 'vetra-dashboard' ); ?></h3>
			<form class="vtd-form" data-vtd-card-form>
				<label class="vtd-field">
					<span><?php esc_html_e( 'Bank', 'vetra-dashboard' ); ?></span>
					<select name="bank_name" required>
						<option value=""><?php esc_html_e( 'Select bank', 'vetra-dashboard' ); ?></option>
						<?php foreach ( $banks as $key => $bank ) : ?>
							<option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $bank['name'] ); ?></option>
						<?php endforeach; ?>
					</select>
				</label>
				<label class="vtd-field">
					<span><?php esc_html_e( 'Card owner', 'vetra-dashboard' ); ?></span>
					<input type="text" name="card_owner" required>
				</label>
				<label class="vtd-field">
					<span><?php esc_html_e( 'Card number', 'vetra-dashboard' ); ?></span>
					<input type="text" name="card_number" inputmode="numeric" maxlength="16" placeholder="6037xxxxxxxxxxxx" required>
				</label>
				<label class="vtd-field">
					<span><?php esc_html_e( 'Sheba number', 'vetra-dashboard' ); ?></span>
					<input type="text" name="card_sheba" inputmode="numeric" maxlength="24" placeholder="IRxxxxxxxxxxxxxxxxxxxxxxxx" required>
				</label>
				<div class="vtd-form-actions">
					<button type="submit" class="vtd-btn vtd-btn-primary"><?php esc_html_e( 'Submit', 'vetra-dashboard' ); ?></button>
					<span class="vtd-form-msg" data-vtd-form-msg></span>
				</div>
			</form>
		</section>

		<section class="vtd-cards">
			<?php if ( empty( $cards ) ) : ?>
				<?php echo VTD_Templates::module( 'alert', array( 'message' => __( 'No bank cards yet.', 'vetra-dashboard' ), 'type' => 'info' ) ); // phpcs:ignore ?>
			<?php else : ?>
				<?php foreach ( $cards as $card ) : ?>
					<article class="vtd-bank-card" style="--vtd-bank-color:<?php echo esc_attr( VTD_Banking::bank_color( $card->card_name ) ); ?>">
						<header>
							<strong><?php echo esc_html( VTD_Banking::bank_label( $card->card_name ) ); ?></strong>
							<span class="vtd-pill vtd-status-<?php echo 'approved' === $card->card_status ? 'closed' : ( 'rejected' === $card->card_status ? 'open' : 'pending' ); ?>">
								<?php echo esc_html( $vtd_statuses[ $card->card_status ] ?? $card->card_status ); ?>
							</span>
						</header>
						<div class="vtd-bank-number"><?php echo esc_html( implode( '-', str_split( $card->card_number, 4 ) ) ); ?></div>
						<footer>
							<span><?php echo esc_html( $card->card_owner ); ?></span>
							<span dir="ltr">IR<?php echo esc_html( implode( '-', str_split( str_pad( $card->card_sheba, 24, '0', STR_PAD_LEFT ), 2 ) ) ); ?></span>
						</footer>
						<button type="button" class="vtd-card-delete" data-vtd-card-delete="<?php echo (int) $card->card_id; ?>"><?php echo vtd_icon( 'close' ); // phpcs:ignore ?></button>
					</article>
				<?php endforeach; ?>
			<?php endif; ?>
		</section>
	</div>
</div>
