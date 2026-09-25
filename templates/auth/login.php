<?php
/**
 * Login form.
 *
 * @package Vetra_Dashboard
 */

defined( 'ABSPATH' ) || exit;

$vtd_settings = VTD_Options::all();
$vtd_modal    = ! empty( $vtd_modal );
$vtd_stage    = VTD_Auth::$stage;
$vtd_otp      = (bool) ( $vtd_settings['otp_login'] ?? 1 );
$vtd_pass     = (bool) ( $vtd_settings['password_login'] ?? 1 );
$vtd_digits   = 'digits' === ( $vtd_settings['otp_login_provider'] ?? 'native' );
$vtd_digits_tag = sanitize_key( $vtd_settings['digits_shortcode'] ?? 'digits' );
$vtd_digits_ready = $vtd_digits && $vtd_digits_tag && shortcode_exists( $vtd_digits_tag );
?>
<div class="vtd-auth-card<?php echo $vtd_modal ? ' is-modal' : ''; ?>">
	<?php if ( ! $vtd_modal ) : ?>
		<div class="vtd-auth-head">
			<h2><?php esc_html_e( 'Sign in to your account', 'vetra-dashboard' ); ?></h2>
			<p><?php esc_html_e( 'Welcome back. Please enter your details.', 'vetra-dashboard' ); ?></p>
		</div>
	<?php endif; ?>

	<?php foreach ( VTD_Auth::$errors as $vtd_error ) : ?>
		<div class="vtd-alert vtd-alert-error"><?php echo esc_html( $vtd_error ); ?></div>
	<?php endforeach; ?>
	<?php foreach ( VTD_Auth::$success as $vtd_message ) : ?>
		<div class="vtd-alert vtd-alert-success"><?php echo esc_html( $vtd_message ); ?></div>
	<?php endforeach; ?>

	<?php if ( $vtd_digits ) : ?>
		<?php if ( $vtd_digits_ready ) : ?>
			<div class="vtd-digits-login" dir="rtl"><?php echo do_shortcode( '[' . $vtd_digits_tag . ']' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
		<?php else : ?>
			<div class="vtd-alert vtd-alert-error">افزونهٔ Digits یا شورت‌کد انتخاب‌شده فعال نیست؛ فرم ورود داخلی وترا به‌عنوان جایگزین نمایش داده شده است.</div>
			<?php if ( ! empty( $vtd_settings['digits_login_page'] ) && get_post_status( (int) $vtd_settings['digits_login_page'] ) ) : ?>
				<p><a class="vtd-link" href="<?php echo esc_url( get_permalink( (int) $vtd_settings['digits_login_page'] ) ); ?>">رفتن به صفحهٔ ورود Digits</a></p>
			<?php endif; ?>
		<?php endif; ?>
	<?php endif; ?>

	<?php if ( ! $vtd_digits_ready ) : ?>
	<?php if ( 'otp' === $vtd_stage ) : ?>
		<form class="vtd-form" method="post" data-vtd-form>
			<input type="hidden" name="vtd_auth_action" value="otp_verify">
			<input type="hidden" name="phone" value="<?php echo esc_attr( VTD_Auth::$form_data['phone'] ?? '' ); ?>">
			<?php wp_nonce_field( 'vtd_auth', 'vtd_auth_nonce' ); ?>
			<label class="vtd-field">
				<span><?php esc_html_e( 'Verification code', 'vetra-dashboard' ); ?></span>
				<input type="text" name="code" inputmode="numeric" autocomplete="one-time-code" required>
			</label>
			<button type="submit" class="vtd-btn vtd-btn-primary vtd-btn-block"><?php esc_html_e( 'Confirm', 'vetra-dashboard' ); ?></button>
			<button type="button" class="vtd-btn vtd-btn-link" data-vtd-resend data-phone="<?php echo esc_attr( VTD_Auth::$form_data['phone'] ?? '' ); ?>">
				<?php esc_html_e( 'Resend code', 'vetra-dashboard' ); ?>
			</button>
		</form>
	<?php else : ?>
		<?php if ( $vtd_pass && $vtd_otp ) : ?>
			<div class="vtd-tabs" data-vtd-tabs>
				<button type="button" class="is-active" data-vtd-tab="password"><?php esc_html_e( 'Password', 'vetra-dashboard' ); ?></button>
				<button type="button" data-vtd-tab="otp"><?php esc_html_e( 'SMS code', 'vetra-dashboard' ); ?></button>
			</div>
		<?php endif; ?>

		<div class="vtd-tab-panel" data-vtd-panel="password" <?php echo ( $vtd_pass && ! $vtd_otp ) || $vtd_pass ? '' : 'hidden'; ?>>
			<form class="vtd-form" method="post" data-vtd-form>
				<input type="hidden" name="vtd_auth_action" value="login">
				<?php wp_nonce_field( 'vtd_auth', 'vtd_auth_nonce' ); ?>
				<label class="vtd-field">
					<span><?php esc_html_e( 'Email, username or phone', 'vetra-dashboard' ); ?></span>
					<input type="text" name="identity" value="<?php echo esc_attr( VTD_Auth::$form_data['identity'] ?? '' ); ?>" autocomplete="username" required>
				</label>
				<label class="vtd-field">
					<span><?php esc_html_e( 'Password', 'vetra-dashboard' ); ?></span>
					<input type="password" name="password" autocomplete="current-password" required>
				</label>
				<div class="vtd-form-row">
					<label class="vtd-check"><input type="checkbox" name="remember" value="1"> <?php esc_html_e( 'Remember me', 'vetra-dashboard' ); ?></label>
					<?php if ( ! empty( $vtd_settings['reset_password_visible'] ) ) : ?>
				<a class="vtd-link" href="<?php echo esc_url( VTD_Router::reset_url() ); ?>"><?php esc_html_e( 'Forgot password?', 'vetra-dashboard' ); ?></a>
				<?php endif; ?>
				</div>
				<button type="submit" class="vtd-btn vtd-btn-primary vtd-btn-block"><?php esc_html_e( 'Sign in', 'vetra-dashboard' ); ?></button>
			</form>
		</div>

		<?php if ( $vtd_otp ) : ?>
			<div class="vtd-tab-panel" data-vtd-panel="otp" <?php echo $vtd_pass ? 'hidden' : ''; ?>>
				<form class="vtd-form" method="post" data-vtd-form>
					<input type="hidden" name="vtd_auth_action" value="otp_request">
					<?php wp_nonce_field( 'vtd_auth', 'vtd_auth_nonce' ); ?>
					<label class="vtd-field">
						<span><?php esc_html_e( 'Mobile number', 'vetra-dashboard' ); ?></span>
						<input type="tel" name="phone" placeholder="09xxxxxxxxx" value="<?php echo esc_attr( VTD_Auth::$form_data['phone'] ?? '' ); ?>" required>
					</label>
					<button type="submit" class="vtd-btn vtd-btn-primary vtd-btn-block"><?php esc_html_e( 'Send code', 'vetra-dashboard' ); ?></button>
				</form>
			</div>
		<?php endif; ?>
	<?php endif; ?>
	<?php endif; ?>

	<?php if ( ! $vtd_modal && ! $vtd_digits_ready && ! empty( $vtd_settings['register_enabled'] ) ) : ?>
		<p class="vtd-auth-foot">
			<?php esc_html_e( 'Do not have an account?', 'vetra-dashboard' ); ?>
			<a class="vtd-link" href="<?php echo esc_url( VTD_Router::register_url() ); ?>"><?php esc_html_e( 'Create one', 'vetra-dashboard' ); ?></a>
		</p>
	<?php endif; ?>
</div>
