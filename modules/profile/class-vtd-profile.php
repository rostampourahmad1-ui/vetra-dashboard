<?php
/**
 * User profile module.
 *
 * @package Vetra_Dashboard
 */

defined( 'ABSPATH' ) || exit;

class VTD_Profile {

	public static function init() {}

	public static function fields() {
		$fields = array();
		foreach ( (array) VTD_Options::get( 'profile_custom_fields', array() ) as $field ) {
			if ( empty( $field['slug'] ) || 'birthday' === $field['slug'] ) {
				continue;
			}
			$fields[ $field['slug'] ] = array(
				'label'    => $field['label'] ?? $field['slug'],
				'type'     => $field['type'] ?? 'text',
				'required' => ! empty( $field['required'] ),
				'options'  => isset( $field['options'] ) ? array_filter( array_map( 'trim', explode( ',', $field['options'] ) ) ) : array(),
			);
		}
		return apply_filters( 'vtd_profile_fields', $fields );
	}

	public static function data( $user_id ) {
		$user = get_userdata( $user_id );
		$data = array(
			'username'   => $user->user_login,
			'email'      => $user->user_email,
			'first_name' => $user->first_name,
			'last_name'  => $user->last_name,
			'phone'      => VTD_Auth::get_phone( $user_id ),
			'about'      => get_user_meta( $user_id, 'description', true ),
			'gender'     => get_user_meta( $user_id, 'vtd_gender', true ),
			'birthday'   => get_user_meta( $user_id, 'vtd_birthday', true ),
			'country'    => get_user_meta( $user_id, 'vtd_country', true ),
			'city'       => get_user_meta( $user_id, 'vtd_city', true ),
			'website'    => $user->user_url,
		);
		foreach ( self::fields() as $slug => $field ) {
			$data[ $slug ] = get_user_meta( $user_id, 'vtd_' . $slug, true );
		}
		return $data;
	}

	public static function save( $user_id, $data ) {
		if ( ! VTD_Options::get( 'profile_edit', 1 ) && ! current_user_can( 'manage_options' ) ) {
			return new WP_Error( 'vtd_profile_locked', __( 'Editing is disabled.', 'vetra-dashboard' ), array( 'status' => 403 ) );
		}

		$user_update = array( 'ID' => $user_id );
		$errors      = array();

		if ( isset( $data['first_name'] ) ) {
			$user_update['first_name'] = sanitize_text_field( $data['first_name'] );
		}
		if ( isset( $data['last_name'] ) ) {
			$user_update['last_name'] = sanitize_text_field( $data['last_name'] );
		}
		if ( isset( $data['about'] ) ) {
			$user_update['description'] = wp_kses_post( $data['about'] );
		}
		if ( isset( $data['website'] ) ) {
			$user_update['user_url'] = esc_url_raw( $data['website'] );
		}
		if ( isset( $data['email'] ) && ! VTD_Options::get( 'profile_confirm_email', 1 ) ) {
			$email = sanitize_email( $data['email'] );
			if ( $email && $email !== get_userdata( $user_id )->user_email ) {
				if ( email_exists( $email ) && email_exists( $email ) !== $user_id ) {
					$errors[] = __( 'This email is used by another user.', 'vetra-dashboard' );
				} else {
					$user_update['user_email'] = $email;
				}
			}
		}

		if ( ! empty( $errors ) ) {
			return new WP_Error( 'vtd_profile_error', implode( ' ', $errors ) );
		}

		wp_update_user( $user_update );

		if ( isset( $data['gender'] ) ) {
			update_user_meta( $user_id, 'vtd_gender', sanitize_text_field( $data['gender'] ) );
		}
		if ( isset( $data['birthday'] ) ) {
			$birthday = vtd_jalali_to_gregorian( sanitize_text_field( $data['birthday'] ) );
			if ( '' !== $birthday || '' === trim( (string) $data['birthday'] ) ) {
				update_user_meta( $user_id, 'vtd_birthday', $birthday );
			} else {
				$errors[] = __( 'لطفاً تاریخ تولد شمسی معتبر وارد کنید.', 'vetra-dashboard' );
			}
		}
		if ( isset( $data['country'] ) ) {
			update_user_meta( $user_id, 'vtd_country', sanitize_text_field( $data['country'] ) );
		}
		if ( isset( $data['city'] ) ) {
			update_user_meta( $user_id, 'vtd_city', sanitize_text_field( $data['city'] ) );
		}

		foreach ( self::fields() as $slug => $field ) {
			if ( ! array_key_exists( $slug, $data ) ) {
				continue;
			}
			$value = $data[ $slug ];
			if ( $field['required'] && '' === trim( (string) $value ) ) {
				$errors[] = sprintf( __( '%s is required.', 'vetra-dashboard' ), $field['label'] );
				continue;
			}
			if ( 'url' === $field['type'] ) {
				$value = esc_url_raw( $value );
			} elseif ( 'date' === $field['type'] ) {
				$value = vtd_jalali_to_gregorian( sanitize_text_field( $value ) );
				if ( '' === $value && '' !== trim( (string) $data[ $slug ] ) ) {
					$errors[] = sprintf( __( 'لطفاً تاریخ شمسی معتبر برای «%s» وارد کنید.', 'vetra-dashboard' ), $field['label'] );
					continue;
				}
			} else {
				$value = sanitize_text_field( $value );
			}
			update_user_meta( $user_id, 'vtd_' . $slug, $value );
		}

		if ( ! empty( $errors ) ) {
			return new WP_Error( 'vtd_profile_error', implode( ' ', $errors ) );
		}

		do_action( 'vtd_profile_saved', $user_id, $data );
		return true;
	}

	public static function change_password( $user_id, $old, $new ) {
		$user = get_userdata( $user_id );
		if ( ! $user ) {
			return new WP_Error( 'vtd_user_missing', __( 'User not found.', 'vetra-dashboard' ) );
		}
		if ( ! wp_check_password( $old, $user->user_pass, $user_id ) ) {
			return new WP_Error( 'vtd_pass_old', __( 'Your old password is incorrect.', 'vetra-dashboard' ) );
		}
		if ( strlen( (string) $new ) < 8 ) {
			return new WP_Error( 'vtd_pass_short', __( 'Password must be at least 8 characters long.', 'vetra-dashboard' ) );
		}

		wp_set_password( $new, $user_id );
		wp_set_current_user( $user_id );
		wp_set_auth_cookie( $user_id, true, is_ssl() );

		do_action( 'vtd_password_changed', $user_id );
		return true;
	}

	public static function save_avatar( $user_id, $request ) {
		if ( ! VTD_Options::get( 'profile_avatar', 1 ) ) {
			return new WP_Error( 'vtd_avatar_disabled', __( 'Avatar upload is disabled.', 'vetra-dashboard' ) );
		}

		$files = $request->get_file_params();
		if ( empty( $files['avatar'] ) ) {
			return new WP_Error( 'vtd_avatar_missing', __( 'No file uploaded.', 'vetra-dashboard' ) );
		}

		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';

		$file = $files['avatar'];
		if ( $file['size'] > 2 * MB_IN_BYTES ) {
			return new WP_Error( 'vtd_avatar_size', __( 'The maximum upload size is 2MB.', 'vetra-dashboard' ) );
		}

		add_filter( 'upload_mimes', array( __CLASS__, 'allow_images' ) );
		$attachment_id = media_handle_sideload( $file, 0, __( 'User avatar', 'vetra-dashboard' ) );
		remove_filter( 'upload_mimes', array( __CLASS__, 'allow_images' ) );

		if ( is_wp_error( $attachment_id ) ) {
			return $attachment_id;
		}

		update_user_meta( $user_id, 'vtd_avatar', $attachment_id );
		return wp_get_attachment_image_url( $attachment_id, 'thumbnail' );
	}

	public static function allow_images( $mimes ) {
		$mimes['jpg|jpeg'] = 'image/jpeg';
		$mimes['png']      = 'image/png';
		$mimes['webp']     = 'image/webp';
		return $mimes;
	}

	public static function avatar_url( $user_id ) {
		$id = (int) get_user_meta( $user_id, 'vtd_avatar', true );
		if ( $id ) {
			$url = wp_get_attachment_image_url( $id, 'thumbnail' );
			if ( $url ) {
				return $url;
			}
		}
		return get_avatar_url( $user_id, array( 'size' => 128 ) );
	}

	public static function verify_field( $user_id, $field, $value, $code ) {
		if ( 'phone' === $field ) {
			$phone = vtd_sanitize_phone( $value );
			if ( '' === $code ) {
				$result = VTD_SMS::send_otp( $phone, 'verify_phone' );
				if ( is_wp_error( $result ) ) {
					return $result;
				}
				set_transient( 'vtd_phone_verify_' . $user_id, $phone, 10 * MINUTE_IN_SECONDS );
				return array( 'sent' => true, 'message' => __( 'The verification code was sent.', 'vetra-dashboard' ) );
			}
			$verify = VTD_SMS::verify_otp( $phone, $code, 'verify_phone' );
			if ( is_wp_error( $verify ) ) {
				return $verify;
			}
			VTD_Auth::update_phone( $user_id, $phone, 1 );
			delete_transient( 'vtd_phone_verify_' . $user_id );
			return array( 'sent' => false, 'message' => __( 'Your mobile number was verified.', 'vetra-dashboard' ) );
		}

		if ( 'email' === $field ) {
			if ( '' === $code ) {
				$key = wp_generate_password( 6, false );
				set_transient( 'vtd_email_verify_' . $user_id, array( 'email' => sanitize_email( $value ), 'key' => $key ), 30 * MINUTE_IN_SECONDS );
				wp_mail( sanitize_email( $value ), __( 'Email verification', 'vetra-dashboard' ), sprintf( __( 'Your verification code is: %s', 'vetra-dashboard' ), $key ) );
				return array( 'sent' => true, 'message' => __( 'The verification code was sent to your email.', 'vetra-dashboard' ) );
			}
			$stored = get_transient( 'vtd_email_verify_' . $user_id );
			if ( ! $stored || (string) $stored['key'] !== (string) $code ) {
				return new WP_Error( 'vtd_email_code', __( 'The verification code is incorrect.', 'vetra-dashboard' ) );
			}
			if ( email_exists( $stored['email'] ) && email_exists( $stored['email'] ) !== $user_id ) {
				return new WP_Error( 'vtd_email_exists', __( 'This email is used by another user.', 'vetra-dashboard' ) );
			}
			wp_update_user( array( 'ID' => $user_id, 'user_email' => $stored['email'] ) );
			update_user_meta( $user_id, VTD_Auth::EMAIL_VERIFIED_META, 1 );
			delete_transient( 'vtd_email_verify_' . $user_id );
			return array( 'sent' => false, 'message' => __( 'Your email was verified.', 'vetra-dashboard' ) );
		}

		return new WP_Error( 'vtd_verify_field', __( 'Invalid field.', 'vetra-dashboard' ) );
	}

	public static function render() {
		$user_id = get_current_user_id();
		return VTD_Templates::module(
			'profile',
			array(
				'user_id'       => $user_id,
				'data'          => self::data( $user_id ),
				'fields'        => self::fields(),
				'phone_verified' => VTD_Auth::is_phone_verified( $user_id ),
				'email_verified' => VTD_Auth::is_email_verified( $user_id ),
				'attachments'   => VTD_Options::get( 'profile_attachments', 1 ) ? VTD_Attachments::get_for_user( $user_id ) : array(),
			)
		);
	}
}
