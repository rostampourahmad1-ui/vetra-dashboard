<?php
/**
 * Wallet view.
 *
 * @package Vetra_Dashboard
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="vtd-wallet">
	<section class="vtd-wallet-hero">
		<div>
			<span><?php esc_html_e( 'Available balance', 'vetra-dashboard' ); ?></span>
			<h2><?php echo esc_html( VTD_Wallet::format( $balance ) ); ?></h2>
		</div>
		<span class="vtd-wallet-dots"><?php echo vtd_icon( 'wallet' ); // phpcs:ignore ?></span>
	</section>

	<div class="vtd-grid-2">
		<section class="vtd-card">
			<h3><?php esc_html_e( 'Request withdrawal', 'vetra-dashboard' ); ?></h3>
			<?php if ( empty( $cards ) ) : ?>
				<?php echo VTD_Templates::module( 'alert', array( 'message' => __( 'Add an approved bank card first.', 'vetra-dashboard' ), 'type' => 'info' ) ); // phpcs:ignore ?>
			<?php else : ?>
				<form class="vtd-form" data-vtd-withdraw-form>
					<label class="vtd-field">
						<span><?php esc_html_e( 'Amount', 'vetra-dashboard' ); ?></span>
						<input type="number" name="amount" min="1" step="1000" required>
					</label>
					<label class="vtd-field">
						<span><?php esc_html_e( 'Bank card', 'vetra-dashboard' ); ?></span>
						<select name="card_id" required>
							<?php foreach ( $cards as $card ) : ?>
								<option value="<?php echo (int) $card->card_id; ?>" <?php disabled( 'approved' !== $card->card_status ); ?>>
									<?php echo esc_html( VTD_Banking::bank_label( $card->card_name ) . ' - ' . implode( ' ', str_split( $card->card_number, 4 ) ) ); ?>
								</option>
							<?php endforeach; ?>
						</select>
					</label>
					<label class="vtd-field">
						<span><?php esc_html_e( 'Note', 'vetra-dashboard' ); ?></span>
						<textarea name="note" rows="2"></textarea>
					</label>
					<div class="vtd-form-actions">
						<button type="submit" class="vtd-btn vtd-btn-primary"><?php esc_html_e( 'Submit request', 'vetra-dashboard' ); ?></button>
						<span class="vtd-form-msg" data-vtd-form-msg></span>
					</div>
				</form>
			<?php endif; ?>
		</section>

		<section class="vtd-card">
			<h3><?php esc_html_e( 'Withdrawal requests', 'vetra-dashboard' ); ?></h3>
			<?php if ( empty( $withdrawals ) ) : ?>
				<p class="vtd-muted"><?php esc_html_e( 'No requests yet.', 'vetra-dashboard' ); ?></p>
			<?php else : ?>
				<ul class="vtd-list">
					<?php foreach ( $withdrawals as $withdrawal ) : ?>
						<li>
							<span><?php echo esc_html( VTD_Wallet::format( $withdrawal->amount ) ); ?></span>
							<span class="vtd-pill vtd-status-<?php echo 'approved' === $withdrawal->status || 'paid' === $withdrawal->status ? 'closed' : ( 'rejected' === $withdrawal->status ? 'open' : 'pending' ); ?>"><?php echo esc_html( VTD_Wallet::withdrawal_status_label( $withdrawal->status ) ); ?></span>
							<time><?php echo esc_html( vtd_date_i18n( $withdrawal->created_at ) ); ?></time>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</section>
	</div>

	<section class="vtd-card">
		<h3><?php esc_html_e( 'Recent transactions', 'vetra-dashboard' ); ?></h3>
		<?php if ( empty( $transactions ) ) : ?>
			<p class="vtd-muted"><?php esc_html_e( 'No transactions found.', 'vetra-dashboard' ); ?></p>
		<?php else : ?>
			<div class="vtd-table">
				<table>
					<thead>
						<tr>
							<th><?php esc_html_e( 'Details', 'vetra-dashboard' ); ?></th>
							<th><?php esc_html_e( 'Type', 'vetra-dashboard' ); ?></th>
							<th><?php esc_html_e( 'Amount', 'vetra-dashboard' ); ?></th>
							<th><?php esc_html_e( 'Balance', 'vetra-dashboard' ); ?></th>
							<th><?php esc_html_e( 'Date', 'vetra-dashboard' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $transactions as $tx ) : ?>
							<tr>
								<td><?php echo esc_html( $tx->details ); ?></td>
								<td><span class="vtd-pill <?php echo 'credit' === $tx->type ? 'vtd-status-closed' : 'vtd-status-open'; ?>"><?php echo esc_html( 'credit' === $tx->type ? __( 'Credit', 'vetra-dashboard' ) : __( 'Debit', 'vetra-dashboard' ) ); ?></span></td>
								<td><?php echo esc_html( ( 'credit' === $tx->type ? '+' : '-' ) . VTD_Wallet::format( $tx->amount ) ); ?></td>
								<td><?php echo esc_html( VTD_Wallet::format( $tx->balance_after ) ); ?></td>
								<td><?php echo esc_html( vtd_date_i18n( $tx->created_at ) ); ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		<?php endif; ?>
	</section>
</div>
