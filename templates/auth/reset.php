<?php
/**
 * Password reset form.
 *
 * @package Vetra_Dashboard
 */

defined( 'ABSPATH' ) || exit;

$vtd_stage = VTD_Auth::$stage;
$vtd_data  = VTD_Auth::$form_data;
?>
<div class="vtd-auth-card">
	<div class="vtd-auth-head">
		<h2><?php esc_html_e( 'Reset your password', 'vetra-dashboard' ); ?></h2>
		<p><?php esc_html_e( 'We will send a reset code to your mobile or email.', 'vetra-dashboard' ); ?></p>
	</div>

	<?php foreach ( VTD_Auth::$errors as $vtd_error ) : ?>
		<div class="vtd-alert vtd-alert-error"><?php echo esc_html( $vtd_error ); ?></div>
	<?php endforeach; ?>
	<?php foreach ( VTD_Auth::$success as $vtd_message ) : ?>
		<div class="vtd-alert vtd-alert-success"><?php echo esc_html( $vtd_message ); ?></div>
	<?php endforeach; ?>

	<?php if ( 'reset' === $vtd_stage ) : ?>
		<form class="vtd-form" method="post" data-vtd-form>
			<input type="hidden" name="vtd_auth_action" value="reset_set">
			<input type="hidden" name="phone" value="<?php echo esc_attr( $vtd_data['phone'] ?? '' ); ?>">
			<?php wp_nonce_field( 'vtd_auth', 'vtd_auth_nonce' ); ?>
			<label class="vtd-field">
				<span><?php esc_html_e( 'Verification code', 'vetra-dashboard' ); ?></span>
				<input type="text" name="code" inputmode="numeric" required>
			</label>
			<label class="vtd-field">
				<span><?php esc_html_e( 'New password', 'vetra-dashboard' ); ?></span>
				<input type="password" name="password" autocomplete="new-password" required>
			</label>
			<label class="vtd-field">
				<span><?php esc_html_e( 'Confirm new password', 'vetra-dashboard' ); ?></span>
				<input type="password" name="password_confirm" autocomplete="new-password" required>
			</label>
			<button type="submit" class="vtd-btn vtd-btn-primary vtd-btn-block"><?php esc_html_e( 'Change password', 'vetra-dashboard' ); ?></button>
		</form>
	<?php else : ?>
		<form class="vtd-form" method="post" data-vtd-form>
			<input type="hidden" name="vtd_auth_action" value="reset_request">
			<?php wp_nonce_field( 'vtd_auth', 'vtd_auth_nonce' ); ?>
			<label class="vtd-field">
				<span><?php esc_html_e( 'Email, username or mobile', 'vetra-dashboard' ); ?></span>
				<input type="text" name="identity" value="<?php echo esc_attr( $vtd_data['identity'] ?? '' ); ?>" required>
			</label>
			<button type="submit" class="vtd-btn vtd-btn-primary vtd-btn-block"><?php esc_html_e( 'Send reset code', 'vetra-dashboard' ); ?></button>
		</form>
	<?php endif; ?>

	<p class="vtd-auth-foot">
		<a class="vtd-link" href="<?php echo esc_url( VTD_Router::login_url() ); ?>"><?php esc_html_e( 'Back to sign in', 'vetra-dashboard' ); ?></a>
	</p>
</div>
