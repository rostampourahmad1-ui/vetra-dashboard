<?php
/**
 * Registration form.
 *
 * @package Vetra_Dashboard
 */

defined( 'ABSPATH' ) || exit;

$vtd_settings = VTD_Options::all();
$vtd_data     = VTD_Auth::$form_data;
?>
<div class="vtd-auth-card">
	<div class="vtd-auth-head">
		<h2><?php esc_html_e( 'Create your account', 'vetra-dashboard' ); ?></h2>
		<p><?php esc_html_e( 'Join us in a few seconds.', 'vetra-dashboard' ); ?></p>
	</div>

	<?php foreach ( VTD_Auth::$errors as $vtd_error ) : ?>
		<div class="vtd-alert vtd-alert-error"><?php echo esc_html( $vtd_error ); ?></div>
	<?php endforeach; ?>
	<?php foreach ( VTD_Auth::$success as $vtd_message ) : ?>
		<div class="vtd-alert vtd-alert-success"><?php echo esc_html( $vtd_message ); ?></div>
	<?php endforeach; ?>

	<form class="vtd-form vtd-form-grid" method="post" data-vtd-form>
		<input type="hidden" name="vtd_auth_action" value="register">
		<?php wp_nonce_field( 'vtd_auth', 'vtd_auth_nonce' ); ?>

		<?php if ( ! empty( $vtd_settings['register_first_last'] ) ) : ?>
			<label class="vtd-field">
				<span><?php esc_html_e( 'First name', 'vetra-dashboard' ); ?></span>
				<input type="text" name="first_name" value="<?php echo esc_attr( $vtd_data['first_name'] ?? '' ); ?>">
			</label>
			<label class="vtd-field">
				<span><?php esc_html_e( 'Last name', 'vetra-dashboard' ); ?></span>
				<input type="text" name="last_name" value="<?php echo esc_attr( $vtd_data['last_name'] ?? '' ); ?>">
			</label>
		<?php endif; ?>

		<label class="vtd-field">
			<span><?php esc_html_e( 'Username', 'vetra-dashboard' ); ?></span>
			<input type="text" name="username" value="<?php echo esc_attr( $vtd_data['username'] ?? '' ); ?>" autocomplete="username" required>
		</label>

		<?php if ( ! empty( $vtd_settings['email_login'] ) ) : ?>
			<label class="vtd-field">
				<span><?php esc_html_e( 'Email', 'vetra-dashboard' ); ?></span>
				<input type="email" name="email" value="<?php echo esc_attr( $vtd_data['email'] ?? '' ); ?>" autocomplete="email" required>
			</label>
		<?php endif; ?>

		<?php if ( ! empty( $vtd_settings['phone_login'] ) ) : ?>
			<label class="vtd-field">
				<span><?php esc_html_e( 'Mobile number', 'vetra-dashboard' ); ?></span>
				<input type="tel" name="phone" placeholder="09xxxxxxxxx" value="<?php echo esc_attr( $vtd_data['phone'] ?? '' ); ?>" autocomplete="tel">
			</label>
		<?php endif; ?>

		<?php if ( ! empty( $vtd_settings['register_birthday'] ) ) : ?>
			<div class="vtd-field">
				<label for="vtd-register-birthday"><span>تاریخ تولد</span></label>
				<?php echo vtd_jalali_date_input( 'birthday', $vtd_data['birthday'] ?? '', false, 'vtd-register-birthday' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>
		<?php endif; ?>

		<label class="vtd-field">
			<span><?php esc_html_e( 'Password', 'vetra-dashboard' ); ?></span>
			<input type="password" name="password" autocomplete="new-password" required>
		</label>

		<?php if ( ! empty( $vtd_settings['register_terms'] ) ) : ?>
			<label class="vtd-check vtd-field-full">
				<input type="checkbox" name="terms" value="1"> <?php echo wp_kses_post( $vtd_settings['terms_text'] ? $vtd_settings['terms_text'] : __( 'I accept the rules.', 'vetra-dashboard' ) ); ?>
			</label>
		<?php endif; ?>

		<?php do_action( 'vtd_register_form_fields' ); ?>

		<button type="submit" class="vtd-btn vtd-btn-primary vtd-btn-block vtd-field-full"><?php esc_html_e( 'Create account', 'vetra-dashboard' ); ?></button>
	</form>

	<p class="vtd-auth-foot">
		<?php esc_html_e( 'Already registered?', 'vetra-dashboard' ); ?>
		<a class="vtd-link" href="<?php echo esc_url( VTD_Router::login_url() ); ?>"><?php esc_html_e( 'Sign in', 'vetra-dashboard' ); ?></a>
	</p>
</div>
