<?php
/**
 * Social networks, editable panel footer and copyright line.
 *
 * @package Vetra_Dashboard
 */

defined( 'ABSPATH' ) || exit;

class VTD_Social {

	/** All supported networks (Western + Iranian). */
	public static function networks() {
		return apply_filters( 'vtd_social_networks', array_merge( self::networks_global(), self::networks_iran() ) );
	}

	public static function networks_global() {
		return array(
			'instagram' => array( 'label' => 'اینستاگرام', 'color' => '#e1306c', 'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="3.5" y="3.5" width="17" height="17" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17" cy="7" r="1.2" fill="currentColor" stroke="none"/></svg>' ),
			'telegram'  => array( 'label' => 'تلگرام', 'color' => '#229ed9', 'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M21 4 3 11l5.2 1.8L10 19l3-4 5.5 4z"/><path d="m8.2 12.8 10-7"/></svg>' ),
			'whatsapp'  => array( 'label' => 'واتس‌اپ', 'color' => '#25d366', 'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M4 20l1.6-4.2A7.8 7.8 0 1 1 12 20a7.9 7.9 0 0 1-4-1.1z"/><path d="M9 9c0 3 3 6 6 6 .9 0 1.5-.9 1.5-1.5L15 12.4l-1.6 1L11 11l1-1.5L10.4 8C10 8 9 8.2 9 9z"/></svg>' ),
			'linkedin'  => array( 'label' => 'لینکدین', 'color' => '#0a66c2', 'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="3.5" y="3.5" width="17" height="17" rx="4"/><path d="M8 10.5V17"/><circle cx="8" cy="7.6" r="1.1"/><path d="M12 17v-3.6a2 2 0 0 1 4 0V17"/></svg>' ),
			'youtube'   => array( 'label' => 'یوتیوب', 'color' => '#ff0000', 'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="2.5" y="5.5" width="19" height="13" rx="4"/><path d="m10.5 9.5 5 2.5-5 2.5z"/></svg>' ),
			'x'         => array( 'label' => 'اکس (توییتر)', 'color' => '#111827', 'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M5 5l14 14M19 5 5 19"/></svg>' ),
			'facebook'  => array( 'label' => 'فیسبوک', 'color' => '#1877f2', 'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M14.5 4h-1.4A3.6 3.6 0 0 0 9.5 7.6V10h-2v3h2v7h3v-7h2.4l.6-3h-3V8a1 1 0 0 1 1-1h2z"/></svg>' ),
			'aparat'    => array( 'label' => 'آپارات', 'color' => '#ff4d4d', 'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="12" r="9"/><path d="m10 8.5 6 3.5-6 3.5z"/></svg>' ),
			'github'    => array( 'label' => 'گیت‌هاب', 'color' => '#24292f', 'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 3.5a8.5 8.5 0 0 0-2.7 16.6c0-1.4 0-2 .1-2.5-2.3.4-2.8-1.1-2.8-1.1-.4-.9-.9-1.2-.9-1.2-.7-.5.1-.5.1-.5.8.1 1.2.9 1.2.9.8 1.3 2 1 2.5.8.1-.6.3-.9.6-1.2-2-.2-3.7-1-3.7-4.1 0-.9.3-1.7.9-2.3-.1-.2-.4-1.1.1-2.2 0 0 .7-.2 2.2.8a8 8 0 0 1 4 0c1.5-1 2.2-.8 2.2-.8.5 1.1.2 2 .1 2.2.6.6.9 1.4.9 2.3 0 3.1-1.7 3.9-3.7 4.1.4.4.7 1 .7 2v3.1A8.5 8.5 0 0 0 12 3.5z"/></svg>' ),
			'pinterest' => array( 'label' => 'پینترست', 'color' => '#e60023', 'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="12" r="9"/><path d="M11.5 20.5 13 13"/><path d="M9.5 12a2.7 2.7 0 1 1 4.6 1.9c-.9.8-2.7.6-2.7.6"/></svg>' ),
			'tiktok'    => array( 'label' => 'تیک‌تاک', 'color' => '#111827', 'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M14 4.5v9.2a3 3 0 1 1-3-3"/><path d="M14 6.2a4 4 0 0 0 4 4"/></svg>' ),
			'website'   => array( 'label' => 'وب‌سایت', 'color' => '#6d28d9', 'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="12" r="9"/><path d="M3.5 12h17"/><path d="M12 3c2.6 2.5 2.6 15.5 0 18-2.6-2.5-2.6-15.5 0-18z"/></svg>' ),
			'phone'     => array( 'label' => 'تلفن', 'color' => '#0ea5e9', 'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M5 4h4l2 5-2 1c1 2 2.9 3.9 5 5l1-2 5 2v4c0 1-.7 1.6-2 1.6C10 20.6 3.4 14 3.4 6c0-1.3.6-2 1.6-2z"/></svg>' ),
			'mail'      => array( 'label' => 'ایمیل', 'color' => '#ef4444', 'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="3" y="5.5" width="18" height="13" rx="3"/><path d="m4.2 7.4 7.8 6 7.8-6"/></svg>' ),
		);
	}

	/** Iranian messengers. */
	public static function networks_iran() {
		return array(
			'rubika'  => array( 'label' => 'روبیکا', 'color' => '#6d28d9', 'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="m12 3.5 8.5 8.5-8.5 8.5L3.5 12z"/><path d="M8.5 12h7"/></svg>' ),
			'bale'    => array( 'label' => 'بله', 'color' => '#0f766e', 'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M4 5.5h16v11H9.5L4 20.5z"/><path d="M8.5 10h7"/></svg>' ),
			'eitaa'   => array( 'label' => 'ایتا', 'color' => '#f59e0b', 'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="4" y="5.5" width="16" height="10" rx="3"/><path d="M8 19c3 1 5 1 7-1"/></svg>' ),
			'soroush' => array( 'label' => 'سروش پلاس', 'color' => '#0891b2', 'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="11" r="8"/><path d="M8.5 12.5c1.8 3 4.9 3 7 0"/></svg>' ),
			'whatsapp_api' => array( 'label' => 'شماره واتس‌اپ', 'color' => '#128c7e', 'icon' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="12" r="8.5"/><path d="M9 9c0 3 3 6 6 6"/><path d="M14.5 13.5 12 12"/></svg>' ),
		);
	}


	public static function network( $key ) {
		$networks = self::networks();
		$key      = sanitize_key( $key );
		return $networks[ $key ] ?? $networks['website'];
	}

	public static function color_mode() {
		$mode = VTD_Options::get( 'social_color_mode', 'brand' );
		return in_array( $mode, array( 'brand', 'custom', 'mono' ), true ) ? $mode : 'brand';
	}

	/** Editable copyright line with token replacement. */
	public static function copyright() {
		$brand  = VTD_Options::get( 'brand_name', 'Vetra' );
		$text   = (string) VTD_Options::get( 'footer_copyright', '' );
		$jalali = vtd_gregorian_to_jalali( (int) current_time( 'Y' ), (int) current_time( 'n' ), (int) current_time( 'j' ) );
		$tokens = array(
			'{year}'  => vtd_fa_digits( $jalali['year'] ),
			'{gyear}' => vtd_fa_digits( (string) current_time( 'Y' ) ),
			'{site}'  => get_bloginfo( 'name' ),
			'{brand}' => $brand,
		);
		return strtr( $text, $tokens );
	}

	/** Enabled social links with a resolved href. */
	public static function items() {
		if ( ! VTD_Options::get( 'social_enabled', 1 ) ) {
			return array();
		}
		$items = array();
		foreach ( (array) VTD_Options::get( 'social_icons', array() ) as $row ) {
			$row = wp_parse_args(
				(array) $row,
				array( 'network' => '', 'label' => '', 'url' => '', 'color' => '', 'enabled' => 1, 'icon_url' => '' )
			);
			if ( empty( $row['enabled'] ) || '' === trim( (string) $row['url'] ) ) {
				continue;
			}
			$network = self::network( $row['network'] );
			$color   = sanitize_hex_color( $row['color'] );
			$items[] = array(
				'network'  => sanitize_key( $row['network'] ),
				'label'    => '' !== trim( (string) $row['label'] ) ? sanitize_text_field( $row['label'] ) : $network['label'],
				'url'      => self::resolve_url( $row['network'], $row['url'] ),
				'color'    => $color ? $color : $network['color'],
				'icon'     => $network['icon'],
				'icon_url' => esc_url_raw( $row['icon_url'] ),
			);
		}
		return apply_filters( 'vtd_social_items', $items );
	}

	/** Turn a handle, phone number or full URL into a working link. */
	public static function resolve_url( $network, $value ) {
		$value = trim( (string) $value );
		if ( '' === $value ) {
			return '';
		}
		$network = sanitize_key( $network );
		$digits  = preg_replace( '/\D/', '', $value );

		if ( 'phone' === $network ) {
			return 'tel:' . ( '' !== $digits ? '+' . $digits : $value );
		}
		if ( 'mail' === $network ) {
			return 'mailto:' . $value;
		}
		foreach ( array( 'http://', 'https://', 'tel:', 'mailto:' ) as $prefix ) {
			if ( 0 === strpos( $value, $prefix ) ) {
				return esc_url_raw( $value );
			}
		}

		$handle = ltrim( $value, '@/' );
		switch ( $network ) {
			case 'telegram':
				return ( '' !== $digits && $digits === $value ) ? 'https://t.me/+' . $digits : 'https://t.me/' . $handle;
			case 'whatsapp':
			case 'whatsapp_api':
				return '' !== $digits ? 'https://wa.me/' . $digits : 'https://wa.me/' . $handle;
			case 'instagram':
				return 'https://instagram.com/' . $handle;
			case 'linkedin':
				return 'https://www.linkedin.com/' . ( 0 === strpos( $value, 'in/' ) || 0 === strpos( $value, 'company/' ) ? $value : 'in/' . $handle );
			case 'youtube':
				return ( 0 === strpos( $handle, 'channel/' ) || 0 === strpos( $handle, 'c/' ) ) ? 'https://www.youtube.com/' . $handle : 'https://www.youtube.com/@' . $handle;
			case 'x':
				return 'https://x.com/' . $handle;
			case 'facebook':
				return 'https://facebook.com/' . $handle;
			case 'github':
				return 'https://github.com/' . $handle;
			case 'pinterest':
				return 'https://pinterest.com/' . $handle;
			case 'tiktok':
				return 'https://tiktok.com/@' . $handle;
			case 'aparat':
				return 'https://aparat.com/' . $handle;
			case 'rubika':
				return 'https://rubika.ir/' . $handle;
			case 'bale':
				return 'https://ble.ir/' . $handle;
			case 'eitaa':
				return 'https://eitaa.com/' . $handle;
			case 'soroush':
				return 'https://splus.ir/' . $handle;
		}

		return esc_url_raw( $value );
	}

	/** Footer markup: social icons, optional extra text and the copyright line. */
	public static function render() {
		$brand = VTD_Options::get( 'brand_name', 'Vetra' );
		$items = self::items();
		$extra = (string) VTD_Options::get( 'footer_extra', '' );
		$note  = self::copyright();
		$mode  = self::color_mode();

		if ( ! $items && '' === trim( $note ) && '' === trim( $extra ) ) {
			return '';
		}

		ob_start();
		?>
		<footer class="vtd-site-footer" dir="rtl">
			<?php if ( $items ) : ?>
				<nav class="vtd-social vtd-social-mode-<?php echo esc_attr( $mode ); ?>" aria-label="<?php esc_attr_e( 'شبکه‌های اجتماعی', 'vetra-dashboard' ); ?>">
					<?php foreach ( $items as $item ) : ?>
						<a class="vtd-social-link vtd-social-<?php echo esc_attr( $item['network'] ); ?>"
							href="<?php echo esc_url( $item['url'] ); ?>"
							target="_blank" rel="noopener noreferrer nofollow"
							title="<?php echo esc_attr( $item['label'] ); ?>"
							aria-label="<?php echo esc_attr( $item['label'] ); ?>"
							<?php echo 'brand' === $mode ? 'style="--vtd-social-color:' . esc_attr( $item['color'] ) . '"' : ''; ?>>
							<?php if ( $item['icon_url'] ) : ?>
								<img src="<?php echo esc_url( $item['icon_url'] ); ?>" alt="<?php echo esc_attr( $item['label'] ); ?>">
							<?php else : ?>
								<span class="vtd-icon" aria-hidden="true"><?php echo $item['icon']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
							<?php endif; ?>
							<span class="vtd-social-label"><?php echo esc_html( $item['label'] ); ?></span>
						</a>
					<?php endforeach; ?>
				</nav>
			<?php endif; ?>

			<?php if ( '' !== trim( $extra ) ) : ?>
				<div class="vtd-footer-extra"><?php echo wp_kses_post( wpautop( $extra ) ); ?></div>
			<?php endif; ?>

			<?php if ( VTD_Options::get( 'footer_copyright_enabled', 1 ) && '' !== trim( $note ) ) : ?>
				<p class="vtd-copyright"><?php echo wp_kses_post( $note ); ?></p>
			<?php endif; ?>
		</footer>
		<?php
		return apply_filters( 'vtd_footer_html', ob_get_clean(), $brand );
	}
}
