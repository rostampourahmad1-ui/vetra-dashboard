<?php
/**
 * Theme profile links widget.
 *
 * @package Vetra_Dashboard
 */

defined( 'ABSPATH' ) || exit;

if ( is_user_logged_in() ) :
	$vtd_user_id = get_current_user_id();
	?>
	<div class="vtd-ui-links">
		<a class="vtd-ui-user" href="<?php echo esc_url( vtd_panel_url( array( 'vtd' => 'profile' ) ) ); ?>">
			<img src="<?php echo esc_url( VTD_Profile::avatar_url( $vtd_user_id ) ); ?>" alt="">
			<span><?php echo esc_html( vtd_current_user_name( $vtd_user_id ) ); ?></span>
		</a>
		<a href="<?php echo esc_url( VTD_Router::panel_url() ); ?>"><?php esc_html_e( 'Dashboard', 'vetra-dashboard' ); ?></a>
		<a href="<?php echo esc_url( VTD_Auth::logout_url() ); ?>"><?php esc_html_e( 'Logout', 'vetra-dashboard' ); ?></a>
	</div>
<?php else : ?>
	<div class="vtd-ui-links">
		<?php if ( VTD_Options::get( 'login_modal', 1 ) ) : ?>
			<a href="#" class="vtd-login-modal"><?php esc_html_e( 'Login', 'vetra-dashboard' ); ?></a>
		<?php else : ?>
			<a href="<?php echo esc_url( VTD_Router::login_url() ); ?>"><?php esc_html_e( 'Login', 'vetra-dashboard' ); ?></a>
		<?php endif; ?>
		<a href="<?php echo esc_url( VTD_Router::register_url() ); ?>"><?php esc_html_e( 'Signup', 'vetra-dashboard' ); ?></a>
	</div>
<?php endif; ?>
