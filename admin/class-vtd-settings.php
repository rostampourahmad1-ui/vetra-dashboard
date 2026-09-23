<?php
/**
 * Admin settings screen.
 *
 * @package Vetra_Dashboard
 */

defined( 'ABSPATH' ) || exit;

class VTD_Settings {

	public static function init() {
		add_action( 'admin_init', array( __CLASS__, 'register' ) );
	}

	public static function register() {
		register_setting(
			'vetra_settings_group',
			VTD_OPTION_KEY,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( __CLASS__, 'sanitize' ),
				'default'           => VTD_Options::defaults(),
			)
		);
	}

	public static function tabs() {
		return array(
			'general'   => __( 'General', 'vetra-dashboard' ),
			'design'    => __( 'Design', 'vetra-dashboard' ),
			'auth'      => __( 'Login & Register', 'vetra-dashboard' ),
			'profile'   => __( 'Profile fields', 'vetra-dashboard' ),
			'tickets'   => __( 'Tickets', 'vetra-dashboard' ),
			'modules'   => __( 'Modules', 'vetra-dashboard' ),
			'sms'       => __( 'SMS', 'vetra-dashboard' ),
			'email'     => __( 'Email', 'vetra-dashboard' ),
			'advanced'  => __( 'Advanced', 'vetra-dashboard' ),
		);
	}

	public static function fields( $tab ) {
		$pages   = self::page_options();
		$roles   = vtd_user_roles();
		$menus   = array(
			'dashboard'     => __( 'Dashboard', 'vetra-dashboard' ),
			'profile'       => __( 'Profile', 'vetra-dashboard' ),
			'tickets'       => __( 'Support Requests', 'vetra-dashboard' ),
			'notifications' => __( 'Notifications', 'vetra-dashboard' ),
			'polls'         => __( 'Polls', 'vetra-dashboard' ),
			'attachments'   => __( 'Attachments', 'vetra-dashboard' ),
			'wallet'        => __( 'Wallet', 'vetra-dashboard' ),
			'banking'       => __( 'Bank Information', 'vetra-dashboard' ),
			'comments'      => __( 'Comments', 'vetra-dashboard' ),
		);

		$fields = array(
			'general'  => array(
				'panel_page'    => array( 'label' => __( 'Panel page', 'vetra-dashboard' ), 'type' => 'page' ),
				'login_page'    => array( 'label' => __( 'Login page', 'vetra-dashboard' ), 'type' => 'page' ),
				'register_page' => array( 'label' => __( 'Register page', 'vetra-dashboard' ), 'type' => 'page' ),
				'reset_page'    => array( 'label' => __( 'Reset password page', 'vetra-dashboard' ), 'type' => 'page' ),
				'brand_name'    => array( 'label' => __( 'Brand name', 'vetra-dashboard' ), 'type' => 'text' ),
				'brand_tagline' => array( 'label' => __( 'Brand tagline', 'vetra-dashboard' ), 'type' => 'text' ),
				'panel_logo'    => array( 'label' => __( 'Panel logo', 'vetra-dashboard' ), 'type' => 'image' ),
				'menu_items'    => array( 'label' => __( 'Menu items', 'vetra-dashboard' ), 'type' => 'menu_toggle', 'options' => $menus ),
				'after_login_redirect' => array(
					'label'   => __( 'Redirect after login', 'vetra-dashboard' ),
					'type'    => 'select',
					'options' => array( 'panel' => __( 'Panel', 'vetra-dashboard' ), 'admin' => __( 'WP admin', 'vetra-dashboard' ) ),
				),
				'after_register_page' => array( 'label' => __( 'Redirect after registration (page)', 'vetra-dashboard' ), 'type' => 'page' ),
				'dashboard_shortcuts' => array( 'label' => __( 'Dashboard shortcuts', 'vetra-dashboard' ), 'type' => 'shortcuts' ),
			),
			'design'   => array(
				'primary_color' => array( 'label' => __( 'Primary color', 'vetra-dashboard' ), 'type' => 'color' ),
				'accent_color'  => array( 'label' => __( 'Accent color', 'vetra-dashboard' ), 'type' => 'color' ),
				'radius'        => array( 'label' => __( 'Border radius (px)', 'vetra-dashboard' ), 'type' => 'number' ),
				'dark_mode'     => array(
					'label'   => __( 'Dark mode', 'vetra-dashboard' ),
					'type'    => 'select',
					'options' => array( 'auto' => __( 'Automatic', 'vetra-dashboard' ), 'light' => __( 'Light', 'vetra-dashboard' ), 'dark' => __( 'Dark', 'vetra-dashboard' ) ),
				),
				'panel_fullwidth' => array( 'label' => __( 'Full-width panel (no theme header/footer)', 'vetra-dashboard' ), 'type' => 'switch' ),
			),
			'auth'     => array(
				'register_enabled'    => array( 'label' => __( 'Enable registration', 'vetra-dashboard' ), 'type' => 'switch' ),
				'email_login'         => array( 'label' => __( 'Email login', 'vetra-dashboard' ), 'type' => 'switch' ),
				'phone_login'         => array( 'label' => __( 'Phone login', 'vetra-dashboard' ), 'type' => 'switch' ),
				'otp_login'           => array( 'label' => __( 'OTP login', 'vetra-dashboard' ), 'type' => 'switch' ),
				'password_login'      => array( 'label' => __( 'Password login', 'vetra-dashboard' ), 'type' => 'switch' ),
				'login_modal'         => array( 'label' => __( 'Login modal in theme', 'vetra-dashboard' ), 'type' => 'switch' ),
				'email_verify'        => array( 'label' => __( 'Verify email on signup', 'vetra-dashboard' ), 'type' => 'switch' ),
				'phone_verify'        => array( 'label' => __( 'Verify phone on signup', 'vetra-dashboard' ), 'type' => 'switch' ),
				'register_first_last' => array( 'label' => __( 'Ask first/last name', 'vetra-dashboard' ), 'type' => 'switch' ),
				'register_birthday'   => array( 'label' => __( 'Ask birthday', 'vetra-dashboard' ), 'type' => 'switch' ),
				'register_terms'      => array( 'label' => __( 'Require terms', 'vetra-dashboard' ), 'type' => 'switch' ),
				'terms_text'          => array( 'label' => __( 'Terms text', 'vetra-dashboard' ), 'type' => 'textarea' ),
				'captcha_provider'    => array(
					'label'   => __( 'Captcha', 'vetra-dashboard' ),
					'type'    => 'select',
					'options' => array( 'none' => __( 'None', 'vetra-dashboard' ), 'recaptcha' => __( 'Google reCAPTCHA v2', 'vetra-dashboard' ) ),
				),
				'captcha_site_key'    => array( 'label' => __( 'Captcha site key', 'vetra-dashboard' ), 'type' => 'text' ),
				'captcha_secret'      => array( 'label' => __( 'Captcha secret', 'vetra-dashboard' ), 'type' => 'text' ),
			),
			'profile'  => array(
				'profile_avatar'        => array( 'label' => __( 'Avatar upload', 'vetra-dashboard' ), 'type' => 'switch' ),
				'profile_edit'          => array( 'label' => __( 'Allow profile editing', 'vetra-dashboard' ), 'type' => 'switch' ),
				'profile_change_pass'   => array( 'label' => __( 'Allow password change', 'vetra-dashboard' ), 'type' => 'switch' ),
				'profile_confirm_email' => array( 'label' => __( 'Email confirmation', 'vetra-dashboard' ), 'type' => 'switch' ),
				'profile_confirm_phone' => array( 'label' => __( 'Phone confirmation', 'vetra-dashboard' ), 'type' => 'switch' ),
				'profile_attachments'   => array( 'label' => __( 'Profile attachments', 'vetra-dashboard' ), 'type' => 'switch' ),
				'profile_custom_fields' => array( 'label' => __( 'Custom fields', 'vetra-dashboard' ), 'type' => 'repeater_fields' ),
			),
			'tickets'  => array(
				'ticket_enabled'     => array( 'label' => __( 'Enable tickets', 'vetra-dashboard' ), 'type' => 'switch' ),
				'ticket_attachments' => array( 'label' => __( 'Allow attachments', 'vetra-dashboard' ), 'type' => 'switch' ),
				'ticket_rating'      => array( 'label' => __( 'Allow rating', 'vetra-dashboard' ), 'type' => 'switch' ),
				'ticket_max_open'    => array( 'label' => __( 'Max open tickets', 'vetra-dashboard' ), 'type' => 'number' ),
				'ticket_auto_reply'  => array( 'label' => __( 'Automatic reply', 'vetra-dashboard' ), 'type' => 'textarea' ),
				'ticket_staff_roles' => array( 'label' => __( 'Staff roles', 'vetra-dashboard' ), 'type' => 'multiselect', 'options' => $roles ),
			),
			'modules'  => array(
				'notifications_enabled' => array( 'label' => __( 'Notifications', 'vetra-dashboard' ), 'type' => 'switch' ),
				'polls_enabled'         => array( 'label' => __( 'Polls', 'vetra-dashboard' ), 'type' => 'switch' ),
				'attachments_enabled'   => array( 'label' => __( 'Attachments', 'vetra-dashboard' ), 'type' => 'switch' ),
				'banking_enabled'       => array( 'label' => __( 'Banking', 'vetra-dashboard' ), 'type' => 'switch' ),
				'wallet_enabled'        => array( 'label' => __( 'Wallet', 'vetra-dashboard' ), 'type' => 'switch' ),
				'comments_enabled'      => array( 'label' => __( 'Comments', 'vetra-dashboard' ), 'type' => 'switch' ),
				'wallet_currency'       => array( 'label' => __( 'Wallet currency', 'vetra-dashboard' ), 'type' => 'text' ),
				'wallet_min_withdraw'   => array( 'label' => __( 'Minimum withdrawal', 'vetra-dashboard' ), 'type' => 'number' ),
			),
			'sms'      => array(
				'sms_enabled'      => array( 'label' => __( 'Enable SMS', 'vetra-dashboard' ), 'type' => 'switch' ),
				'sms_provider'     => array( 'label' => __( 'Provider', 'vetra-dashboard' ), 'type' => 'select', 'options' => VTD_SMS::providers() ),
				'sms_api_version'  => array(
					'label'   => __( 'IPPanel API', 'vetra-dashboard' ),
					'type'    => 'select',
					'options' => array( 'edge' => __( 'Edge API (edge.ippanel.com)', 'vetra-dashboard' ), 'legacy' => __( 'Legacy API (api2.ippanel.com)', 'vetra-dashboard' ) ),
				),
				'sms_api_key'      => array( 'label' => __( 'Access key / API key', 'vetra-dashboard' ), 'type' => 'text' ),
				'sms_sender'       => array( 'label' => __( 'Sender number', 'vetra-dashboard' ), 'type' => 'text' ),
				'sms_base_url'     => array( 'label' => __( 'Base URL', 'vetra-dashboard' ), 'type' => 'text' ),
				'sms_pattern_otp'  => array( 'label' => __( 'OTP pattern code', 'vetra-dashboard' ), 'type' => 'text' ),
				'sms_otp_variable' => array( 'label' => __( 'OTP variable name', 'vetra-dashboard' ), 'type' => 'text' ),
				'sms_otp_length'   => array( 'label' => __( 'OTP length', 'vetra-dashboard' ), 'type' => 'number' ),
				'sms_otp_expiry'   => array( 'label' => __( 'OTP expiry (seconds)', 'vetra-dashboard' ), 'type' => 'number' ),
				'sms_otp_resend'   => array( 'label' => __( 'Resend delay (seconds)', 'vetra-dashboard' ), 'type' => 'number' ),
				'sms_log'          => array( 'label' => __( 'Log SMS', 'vetra-dashboard' ), 'type' => 'switch' ),
			),
			'email'    => array(
				'email_from_name'  => array( 'label' => __( 'From name', 'vetra-dashboard' ), 'type' => 'text' ),
				'email_from_email' => array( 'label' => __( 'From email', 'vetra-dashboard' ), 'type' => 'text' ),
				'email_on_ticket'  => array( 'label' => __( 'Email on new ticket', 'vetra-dashboard' ), 'type' => 'switch' ),
				'email_on_signup'  => array( 'label' => __( 'Email on signup', 'vetra-dashboard' ), 'type' => 'switch' ),
				'email_header'     => array( 'label' => __( 'Email header', 'vetra-dashboard' ), 'type' => 'textarea' ),
				'email_footer'     => array( 'label' => __( 'Email footer', 'vetra-dashboard' ), 'type' => 'textarea' ),
			),
			'advanced' => array(
				'delete_data_on_uninstall' => array( 'label' => __( 'Delete all data on uninstall', 'vetra-dashboard' ), 'type' => 'switch' ),
			),
		);

		return apply_filters( 'vtd_settings_fields', $fields[ $tab ] ?? array(), $tab );
	}

	protected static function page_options() {
		$pages = get_posts( array( 'post_type' => 'page', 'numberposts' => 300, 'post_status' => 'publish' ) );
		$list  = array( 0 => __( '- Select page -', 'vetra-dashboard' ) );
		foreach ( $pages as $page ) {
			$list[ $page->ID ] = $page->post_title;
		}
		return $list;
	}

	public static function render() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$tabs    = self::tabs();
		$current = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'general';
		if ( ! isset( $tabs[ $current ] ) ) {
			$current = 'general';
		}
		$settings = VTD_Options::all();
		?>
		<div class="wrap vtd-admin-wrap">
			<h1><?php esc_html_e( 'Vetra Dashboard Settings', 'vetra-dashboard' ); ?></h1>
			<nav class="nav-tab-wrapper">
				<?php foreach ( $tabs as $key => $label ) : ?>
					<a class="nav-tab <?php echo $current === $key ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url( admin_url( 'admin.php?page=vetra-settings&tab=' . $key ) ); ?>"><?php echo esc_html( $label ); ?></a>
				<?php endforeach; ?>
			</nav>
			<?php settings_errors(); ?>
			<form method="post" action="options.php" class="vtd-settings-form">
				<?php settings_fields( 'vetra_settings_group' ); ?>
				<table class="form-table" role="presentation">
					<tbody>
					<?php foreach ( self::fields( $current ) as $key => $field ) : ?>
						<tr>
							<th scope="row"><label for="vtd-<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $field['label'] ); ?></label></th>
							<td><?php self::field( $key, $field, $settings[ $key ] ?? null ); ?></td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
				<?php submit_button(); ?>
			</form>
			<?php if ( 'sms' === $current ) : ?>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="vtd-sms-test">
					<input type="hidden" name="action" value="vtd_sms_test">
					<?php wp_nonce_field( 'vtd_sms_test' ); ?>
					<input type="text" name="phone" placeholder="<?php esc_attr_e( 'Test mobile number', 'vetra-dashboard' ); ?>">
					<button type="submit" class="button"><?php esc_html_e( 'Send test SMS', 'vetra-dashboard' ); ?></button>
				</form>
			<?php endif; ?>
		</div>
		<?php
	}

	protected static function field( $key, $field, $value ) {
		$name = VTD_OPTION_KEY . '[' . $key . ']';
		$id   = 'vtd-' . $key;
		switch ( $field['type'] ) {
			case 'switch':
				printf(
					'<label class="vtd-switch"><input type="checkbox" id="%1$s" name="%2$s" value="1" %3$s><span></span></label>',
					esc_attr( $id ),
					esc_attr( $name ),
					checked( (bool) $value, true, false )
				);
				break;
			case 'color':
				printf( '<input type="text" id="%1$s" name="%2$s" value="%3$s" class="vtd-color" data-default-color="">', esc_attr( $id ), esc_attr( $name ), esc_attr( $value ) );
				break;
			case 'number':
				printf( '<input type="number" id="%1$s" name="%2$s" value="%3$s" class="small-text">', esc_attr( $id ), esc_attr( $name ), esc_attr( $value ) );
				break;
			case 'textarea':
				printf( '<textarea id="%1$s" name="%2$s" rows="4" class="large-text">%3$s</textarea>', esc_attr( $id ), esc_attr( $name ), esc_textarea( (string) $value ) );
				break;
			case 'select':
				echo '<select id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '">';
				foreach ( $field['options'] as $opt_key => $opt_label ) {
					echo '<option value="' . esc_attr( $opt_key ) . '" ' . selected( (string) $value, (string) $opt_key, false ) . '>' . esc_html( $opt_label ) . '</option>';
				}
				echo '</select>';
				break;
			case 'multiselect':
				$values = (array) $value;
				echo '<select id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '[]" multiple style="min-width:260px;height:auto">';
				foreach ( $field['options'] as $opt_key => $opt_label ) {
					echo '<option value="' . esc_attr( $opt_key ) . '" ' . ( in_array( (string) $opt_key, array_map( 'strval', $values ), true ) ? 'selected' : '' ) . '>' . esc_html( $opt_label ) . '</option>';
				}
				echo '</select>';
				break;
			case 'page':
				echo '<select id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '">';
				foreach ( self::page_options() as $opt_key => $opt_label ) {
					echo '<option value="' . esc_attr( $opt_key ) . '" ' . selected( (int) $value, (int) $opt_key, false ) . '>' . esc_html( $opt_label ) . '</option>';
				}
				echo '</select>';
				break;
			case 'image':
				echo '<div class="vtd-image-field">';
				echo '<input type="text" id="' . esc_attr( $id ) . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( $value ) . '" class="regular-text">';
				echo '<button type="button" class="button vtd-media-select">' . esc_html__( 'Select', 'vetra-dashboard' ) . '</button>';
				echo '</div>';
				break;
			case 'menu_toggle':
				$current = is_array( $value ) ? wp_list_pluck( $value, 'enabled', 'slug' ) : array();
				echo '<fieldset class="vtd-menu-toggle">';
				foreach ( $field['options'] as $slug => $label ) {
					$enabled = isset( $current[ $slug ] ) ? (bool) $current[ $slug ] : true;
					echo '<label><input type="checkbox" name="' . esc_attr( $name . '[' . $slug . ']' ) . '" value="1" ' . checked( $enabled, true, false ) . '> ' . esc_html( $label ) . '</label>';
				}
				echo '</fieldset>';
				break;
			case 'repeater_fields':
				self::repeater_fields( $name, (array) $value );
				break;
			case 'shortcuts':
				$rows = array_values( (array) $value );
				echo '<div class="vtd-repeater" data-repeater="shortcuts">';
				echo '<div class="vtd-repeater-rows">';
				foreach ( $rows as $i => $row ) {
					$row = wp_parse_args( (array) $row, array( 'label' => '', 'icon' => '', 'url' => '' ) );
					echo '<div class="vtd-repeater-row">';
					echo '<input type="text" name="' . esc_attr( $name . '[' . $i . '][label]' ) . '" value="' . esc_attr( $row['label'] ) . '" placeholder="' . esc_attr__( 'Label', 'vetra-dashboard' ) . '">';
					echo '<input type="text" name="' . esc_attr( $name . '[' . $i . '][icon]' ) . '" value="' . esc_attr( $row['icon'] ) . '" placeholder="' . esc_attr__( 'Icon key', 'vetra-dashboard' ) . '">';
					echo '<input type="text" name="' . esc_attr( $name . '[' . $i . '][url]' ) . '" value="' . esc_attr( $row['url'] ) . '" placeholder="URL">';
					echo '<button type="button" class="button vtd-repeater-remove">&times;</button>';
					echo '</div>';
				}
				echo '</div>';
				echo '<button type="button" class="button vtd-repeater-add">' . esc_html__( 'Add shortcut', 'vetra-dashboard' ) . '</button>';
				echo '</div>';
				break;
			default:
				printf( '<input type="text" id="%1$s" name="%2$s" value="%3$s" class="regular-text">', esc_attr( $id ), esc_attr( $name ), esc_attr( $value ) );
		}
	}

	protected static function repeater_fields( $name, $rows ) {
		echo '<div class="vtd-repeater" data-repeater="profile_fields">';
		echo '<div class="vtd-repeater-rows">';
		$rows = array_values( $rows );
		foreach ( $rows as $i => $row ) {
			self::repeater_row( $name, $i, $row );
		}
		echo '</div>';
		echo '<button type="button" class="button vtd-repeater-add">' . esc_html__( 'Add field', 'vetra-dashboard' ) . '</button>';
		echo '</div>';
	}

	protected static function repeater_row( $name, $index, $row ) {
		$row = wp_parse_args( (array) $row, array( 'slug' => '', 'label' => '', 'type' => 'text', 'required' => 0, 'options' => '' ) );
		?>
		<div class="vtd-repeater-row">
			<input type="text" name="<?php echo esc_attr( $name . '[' . $index . '][slug]' ); ?>" value="<?php echo esc_attr( $row['slug'] ); ?>" placeholder="<?php esc_attr_e( 'slug', 'vetra-dashboard' ); ?>">
			<input type="text" name="<?php echo esc_attr( $name . '[' . $index . '][label]' ); ?>" value="<?php echo esc_attr( $row['label'] ); ?>" placeholder="<?php esc_attr_e( 'label', 'vetra-dashboard' ); ?>">
			<select name="<?php echo esc_attr( $name . '[' . $index . '][type]' ); ?>">
				<?php foreach ( array( 'text', 'email', 'tel', 'number', 'date', 'url', 'textarea', 'select' ) as $type ) : ?>
					<option value="<?php echo esc_attr( $type ); ?>" <?php selected( $row['type'], $type ); ?>><?php echo esc_html( $type ); ?></option>
				<?php endforeach; ?>
			</select>
			<input type="text" name="<?php echo esc_attr( $name . '[' . $index . '][options]' ); ?>" value="<?php echo esc_attr( $row['options'] ); ?>" placeholder="<?php esc_attr_e( 'option1,option2', 'vetra-dashboard' ); ?>">
			<label><input type="checkbox" name="<?php echo esc_attr( $name . '[' . $index . '][required]' ); ?>" value="1" <?php checked( ! empty( $row['required'] ) ); ?>> <?php esc_html_e( 'Required', 'vetra-dashboard' ); ?></label>
			<button type="button" class="button vtd-repeater-remove">&times;</button>
		</div>
		<?php
	}

	public static function sanitize( $input ) {
		$input    = is_array( $input ) ? $input : array();
		$defaults = VTD_Options::defaults();
		$clean    = array();

		$switches = array(
			'register_enabled', 'email_login', 'phone_login', 'otp_login', 'password_login', 'login_modal',
			'email_verify', 'phone_verify', 'register_first_last', 'register_birthday', 'register_terms',
			'profile_avatar', 'profile_edit', 'profile_change_pass', 'profile_confirm_email', 'profile_confirm_phone', 'profile_attachments',
			'ticket_enabled', 'ticket_attachments', 'ticket_rating',
			'notifications_enabled', 'polls_enabled', 'attachments_enabled', 'banking_enabled', 'wallet_enabled', 'comments_enabled',
			'sms_enabled', 'sms_log', 'email_on_ticket', 'email_on_signup', 'delete_data_on_uninstall',
			'panel_fullwidth',
		);

		foreach ( $defaults as $key => $default ) {
			if ( in_array( $key, $switches, true ) ) {
				$clean[ $key ] = ! empty( $input[ $key ] ) ? 1 : 0;
				continue;
			}
			if ( ! array_key_exists( $key, $input ) ) {
				$clean[ $key ] = $default;
				continue;
			}
			$value = $input[ $key ];
			switch ( $key ) {
				case 'panel_page':
				case 'login_page':
				case 'register_page':
				case 'reset_page':
				case 'after_register_page':
					$clean[ $key ] = (int) $value;
					break;
				case 'radius':
				case 'wallet_min_withdraw':
				case 'ticket_max_open':
				case 'sms_otp_length':
				case 'sms_otp_expiry':
				case 'sms_otp_resend':
					$clean[ $key ] = (int) $value;
					break;
				case 'primary_color':
				case 'accent_color':
					$clean[ $key ] = sanitize_hex_color( $value ) ? sanitize_hex_color( $value ) : $default;
					break;
				case 'panel_logo':
				case 'email_from_email':
				case 'captcha_site_key':
				case 'captcha_secret':
					$clean[ $key ] = sanitize_text_field( $value );
					break;
				case 'email_header':
				case 'email_footer':
				case 'terms_text':
				case 'ticket_auto_reply':
					$clean[ $key ] = wp_kses_post( $value );
					break;
				case 'ticket_staff_roles':
					$clean[ $key ] = array_values( array_map( 'sanitize_key', (array) $value ) );
					break;
				case 'menu_items':
					$clean[ $key ] = array();
					foreach ( $defaults['menu_items'] as $item ) {
						$clean[ $key ][] = array(
							'slug'    => $item['slug'],
							'enabled' => ! empty( $value[ $item['slug'] ] ) ? 1 : 0,
						);
					}
					break;
				case 'profile_custom_fields':
					$clean[ $key ] = array();
					foreach ( (array) $value as $row ) {
						if ( empty( $row['slug'] ) || empty( $row['label'] ) ) {
							continue;
						}
						$clean[ $key ][] = array(
							'slug'     => sanitize_key( $row['slug'] ),
							'label'    => sanitize_text_field( $row['label'] ),
							'type'     => sanitize_key( $row['type'] ?? 'text' ),
							'options'  => sanitize_text_field( $row['options'] ?? '' ),
							'required' => ! empty( $row['required'] ) ? 1 : 0,
						);
					}
					break;
				case 'dashboard_shortcuts':
					$clean[ $key ] = array();
					foreach ( (array) $value as $row ) {
						if ( empty( $row['label'] ) ) {
							continue;
						}
						$clean[ $key ][] = array(
							'label' => sanitize_text_field( $row['label'] ),
							'icon'  => sanitize_key( $row['icon'] ?? 'default' ),
							'url'   => esc_url_raw( $row['url'] ?? '' ),
						);
					}
					break;
				default:
					$clean[ $key ] = is_scalar( $value ) ? sanitize_text_field( $value ) : $value;
			}
		}

		VTD_Roles::sync_staff_caps();
		return $clean;
	}
}
