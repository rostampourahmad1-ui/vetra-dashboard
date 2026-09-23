<?php
/**
 * Transactional email service.
 *
 * @package Vetra_Dashboard
 */

defined( 'ABSPATH' ) || exit;

class VTD_Email {

	public static function init() {
		add_action( 'vtd_user_registered', array( __CLASS__, 'welcome' ) );
		add_action( 'vtd_ticket_created', array( __CLASS__, 'ticket_created' ), 10, 2 );
		add_action( 'vtd_ticket_replied', array( __CLASS__, 'ticket_replied' ), 10, 4 );
		add_action( 'vtd_card_status_changed', array( __CLASS__, 'card_status' ), 10, 2 );
		add_action( 'vtd_withdrawal_status_changed', array( __CLASS__, 'withdrawal_status' ), 10, 3 );
	}

	public static function enabled( $context ) {
		$map = array(
			'ticket' => 'email_on_ticket',
			'signup' => 'email_on_signup',
		);
		$key = $map[ $context ] ?? '';
		return '' === $key ? true : (bool) VTD_Options::get( $key, 1 );
	}

	public static function send( $to, $subject, $heading, $body, $args = array() ) {
		$args = wp_parse_args( $args, array( 'context' => 'general', 'unsubscribe' => false ) );

		if ( ! self::enabled( $args['context'] ) ) {
			return false;
		}

		$recipients = is_array( $to ) ? array_filter( array_map( 'sanitize_email', $to ) ) : array_filter( array( sanitize_email( $to ) ) );
		if ( empty( $recipients ) ) {
			return false;
		}

		$subject = wp_strip_all_tags( $subject );
		$html    = apply_filters( 'vtd_email_html', self::wrap( $heading, $body ), $subject, $heading, $body, $args );

		$headers = array(
			'Content-Type: text/html; charset=UTF-8',
		);

		$sent = wp_mail( $recipients, $subject, $html, $headers );

		do_action( 'vtd_email_sent', $recipients, $subject, $sent, $args );
		return $sent;
	}

	public static function wrap( $heading, $body ) {
		$brand  = VTD_Options::get( 'brand_name', 'Vetra' );
		$color  = VTD_Options::get( 'primary_color', '#6d28d9' );
		$accent = VTD_Options::get( 'accent_color', '#06b6d4' );
		$header = VTD_Options::get( 'email_header', '' );
		$footer = VTD_Options::get( 'email_footer', '' );
		$logo   = VTD_Options::get( 'panel_logo', '' );

		ob_start();
		?>
		<!DOCTYPE html>
		<html dir="rtl" lang="fa">
		<head>
			<meta charset="UTF-8">
			<meta name="viewport" content="width=device-width, initial-scale=1.0">
			<title><?php echo esc_html( $heading ); ?></title>
		</head>
		<body style="margin:0;padding:0;background:#f4f5fb;font-family:Tahoma,'Segoe UI',sans-serif;">
		<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f4f5fb;padding:32px 12px;">
			<tr>
				<td align="center">
					<table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#ffffff;border-radius:18px;overflow:hidden;box-shadow:0 20px 50px -30px rgba(28,27,46,.5);">
						<tr>
							<td style="background:linear-gradient(135deg,<?php echo esc_attr( $color ); ?>,<?php echo esc_attr( $accent ); ?>);padding:26px 30px;color:#ffffff;">
								<?php if ( $logo ) : ?>
									<img src="<?php echo esc_url( $logo ); ?>" alt="<?php echo esc_attr( $brand ); ?>" style="height:36px;margin-bottom:12px;">
								<?php endif; ?>
								<div style="font-size:20px;font-weight:800;"><?php echo esc_html( $brand ); ?></div>
							</td>
						</tr>
						<?php if ( $header ) : ?>
							<tr><td style="padding:18px 30px 0;color:#7b7a92;font-size:13px;"><?php echo wp_kses_post( $header ); ?></td></tr>
						<?php endif; ?>
						<tr>
							<td style="padding:26px 30px;">
								<h2 style="margin:0 0 14px;font-size:19px;color:#1c1b2e;"><?php echo esc_html( $heading ); ?></h2>
								<div style="font-size:15px;line-height:1.9;color:#3b3a4d;"><?php echo wp_kses_post( $body ); ?></div>
							</td>
						</tr>
						<tr>
							<td style="padding:18px 30px;background:#f8f9fc;color:#7b7a92;font-size:12px;text-align:center;">
								<?php echo $footer ? wp_kses_post( $footer ) : esc_html( sprintf( __( 'This email was sent by %s.', 'vetra-dashboard' ), $brand ) ); ?>
							</td>
						</tr>
					</table>
					<div style="color:#9b97b5;font-size:11px;margin-top:14px;">&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php echo esc_html( $brand ); ?></div>
				</td>
			</tr>
		</table>
		</body>
		</html>
		<?php
		return ob_get_clean();
	}

	public static function button( $url, $label ) {
		return '<p style="text-align:center;margin:24px 0;"><a href="' . esc_url( $url ) . '" style="display:inline-block;padding:12px 26px;border-radius:12px;background:#6d28d9;color:#ffffff;text-decoration:none;font-weight:700;">' . esc_html( $label ) . '</a></p>';
	}

	public static function welcome( $user_id ) {
		$user = get_userdata( $user_id );
		if ( ! $user || ! is_email( $user->user_email ) ) {
			return;
		}
		$body = sprintf(
			/* translators: %s: user name */
			__( 'Hi %s, welcome to our community. Your account has been created successfully.', 'vetra-dashboard' ),
			esc_html( vtd_current_user_name( $user_id ) )
		);
		$body .= self::button( VTD_Router::panel_url(), __( 'Go to dashboard', 'vetra-dashboard' ) );

		self::send( $user->user_email, __( 'Welcome', 'vetra-dashboard' ), __( 'Your account is ready', 'vetra-dashboard' ), $body, array( 'context' => 'signup' ) );
	}

	public static function ticket_created( $ticket_id, $user_id ) {
		$ticket = VTD_Tickets::get( $ticket_id );
		if ( ! $ticket ) {
			return;
		}

		global $wpdb;
		$staff_raw = $wpdb->get_var( $wpdb->prepare( 'SELECT staff_ids FROM ' . VTD_DB::departments() . ' WHERE department_id = %d', $ticket->department_id ) );
		$staff     = $staff_raw ? array_filter( array_map( 'intval', explode( ',', $staff_raw ) ) ) : array();
		$emails    = array();
		foreach ( $staff as $staff_id ) {
			$data = get_userdata( $staff_id );
			if ( $data && is_email( $data->user_email ) ) {
				$emails[] = $data->user_email;
			}
		}
		if ( empty( $emails ) ) {
			$emails[] = get_option( 'admin_email' );
		}

		$body  = '<p>' . esc_html( sprintf( __( 'A new ticket was registered by %s.', 'vetra-dashboard' ), vtd_current_user_name( $user_id ) ) ) . '</p>';
		$body .= '<p><strong>' . esc_html( $ticket->ticket_title ) . '</strong></p>';
		$body .= self::button( vtd_panel_url( array( 'vtd' => 'ticket', 'ticket' => $ticket_id ) ), __( 'View ticket', 'vetra-dashboard' ) );

		self::send( $emails, sprintf( __( 'New ticket #%d', 'vetra-dashboard' ), $ticket_id ), __( 'New support ticket', 'vetra-dashboard' ), $body, array( 'context' => 'ticket' ) );
	}

	public static function ticket_replied( $ticket_id, $reply_id, $owner_id, $is_staff ) {
		$ticket = VTD_Tickets::get( $ticket_id );
		if ( ! $ticket ) {
			return;
		}

		if ( $is_staff ) {
			$user = get_userdata( $owner_id );
			if ( ! $user || ! is_email( $user->user_email ) ) {
				return;
			}
			$recipients = array( $user->user_email );
			$heading    = __( 'New reply from support', 'vetra-dashboard' );
		} else {
			global $wpdb;
			$staff_raw = $wpdb->get_var( $wpdb->prepare( 'SELECT staff_ids FROM ' . VTD_DB::departments() . ' WHERE department_id = %d', $ticket->department_id ) );
			$staff     = $staff_raw ? array_filter( array_map( 'intval', explode( ',', $staff_raw ) ) ) : array();
			$recipients = array();
			foreach ( $staff as $staff_id ) {
				$data = get_userdata( $staff_id );
				if ( $data && is_email( $data->user_email ) ) {
					$recipients[] = $data->user_email;
				}
			}
			if ( empty( $recipients ) ) {
				$recipients[] = get_option( 'admin_email' );
			}
			$heading = __( 'New reply from user', 'vetra-dashboard' );
		}

		$body  = '<p>' . esc_html( sprintf( __( 'Ticket #%d received a new reply.', 'vetra-dashboard' ), $ticket_id ) ) . '</p>';
		$body .= '<p><strong>' . esc_html( $ticket->ticket_title ) . '</strong></p>';
		$body .= self::button( vtd_panel_url( array( 'vtd' => 'ticket', 'ticket' => $ticket_id ) ), __( 'View conversation', 'vetra-dashboard' ) );

		self::send( $recipients, $heading, $heading, $body, array( 'context' => 'ticket' ) );
	}

	public static function card_status( $card_id, $status ) {
		$card = VTD_Banking::get_card( $card_id );
		if ( ! $card ) {
			return;
		}
		$user = get_userdata( $card->user_id );
		if ( ! $user || ! is_email( $user->user_email ) ) {
			return;
		}
		$labels = array(
			'approved' => __( 'Your bank card was approved.', 'vetra-dashboard' ),
			'rejected' => __( 'Your bank card was rejected.', 'vetra-dashboard' ),
			'pending'  => __( 'Your bank card is under review.', 'vetra-dashboard' ),
		);
		$body = '<p>' . esc_html( $labels[ $status ] ?? __( 'Your bank card status changed.', 'vetra-dashboard' ) ) . '</p>';
		self::send( $user->user_email, __( 'Bank card status', 'vetra-dashboard' ), __( 'Bank card status', 'vetra-dashboard' ), $body );
	}

	public static function withdrawal_status( $id, $status, $row ) {
		$user = get_userdata( $row->user_id );
		if ( ! $user || ! is_email( $user->user_email ) ) {
			return;
		}
		$labels = array(
			'approved' => __( 'Your withdrawal request was approved.', 'vetra-dashboard' ),
			'rejected' => __( 'Your withdrawal request was rejected.', 'vetra-dashboard' ),
			'paid'     => __( 'Your withdrawal request has been paid.', 'vetra-dashboard' ),
		);
		$body = '<p>' . esc_html( $labels[ $status ] ?? __( 'Your withdrawal status changed.', 'vetra-dashboard' ) ) . '</p>';
		$body .= '<p>' . esc_html( VTD_Wallet::format( $row->amount ) ) . '</p>';
		self::send( $user->user_email, __( 'Withdrawal status', 'vetra-dashboard' ), __( 'Withdrawal status', 'vetra-dashboard' ), $body );
	}
}
