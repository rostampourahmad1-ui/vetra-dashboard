<?php
/**
 * Profile view.
 *
 * @package Vetra_Dashboard
 */

defined( 'ABSPATH' ) || exit;

$vtd_readonly = VTD_Options::get( 'profile_readonly_mode', 0 );
$vtd_change_request = VTD_Options::get( 'profile_change_request', 0 );
?>
<div class="vtd-profile">
	<section class="vtd-card vtd-profile-head">
		<div class="vtd-profile-avatar">
			<img src="<?php echo esc_url( VTD_Profile::avatar_url( $user_id ) ); ?>" alt="" data-vtd-avatar>
			<label class="vtd-avatar-upload">
				<?php echo vtd_icon( 'plus' ); // phpcs:ignore ?>
				<input type="file" accept="image/*" data-vtd-avatar-input hidden>
			</label>
		</div>
		<div class="vtd-profile-meta">
			<h2><?php echo esc_html( vtd_current_user_name( $user_id ) ); ?></h2>
			<div class="vtd-chips">
				<span class="vtd-chip <?php echo $phone_verified ? 'is-ok' : 'is-warn'; ?>">
					<?php echo vtd_icon( 'phone' ); // phpcs:ignore ?>
					<?php echo $phone_verified ? esc_html__( 'Phone verified', 'vetra-dashboard' ) : esc_html__( 'Phone not verified', 'vetra-dashboard' ); ?>
				</span>
				<span class="vtd-chip <?php echo $email_verified ? 'is-ok' : 'is-warn'; ?>">
					<?php echo vtd_icon( 'mail' ); // phpcs:ignore ?>
					<?php echo $email_verified ? esc_html__( 'Email verified', 'vetra-dashboard' ) : esc_html__( 'Email not verified', 'vetra-dashboard' ); ?>
				</span>
			</div>
		</div>
	</section>

	<section class="vtd-card">
		<h3><?php esc_html_e( 'Personal details', 'vetra-dashboard' ); ?></h3>
		<?php if ( $vtd_readonly ) : ?>
			<div class="vtd-alert vtd-alert-info">
				<?php esc_html_e( 'Your profile information is read-only. To change any details, please submit a change request with supporting documents below.', 'vetra-dashboard' ); ?>
			</div>
		<?php endif; ?>
		<form class="vtd-form vtd-form-grid" data-vtd-profile-form>
			<label class="vtd-field">
				<span><?php esc_html_e( 'First name', 'vetra-dashboard' ); ?></span>
				<input type="text" name="first_name" value="<?php echo esc_attr( $data['first_name'] ); ?>" <?php echo $vtd_readonly ? 'readonly' : ''; ?>>
			</label>
			<label class="vtd-field">
				<span><?php esc_html_e( 'Last name', 'vetra-dashboard' ); ?></span>
				<input type="text" name="last_name" value="<?php echo esc_attr( $data['last_name'] ); ?>" <?php echo $vtd_readonly ? 'readonly' : ''; ?>>
			</label>
			<label class="vtd-field">
				<span><?php esc_html_e( 'Email', 'vetra-dashboard' ); ?></span>
				<input type="email" name="email" value="<?php echo esc_attr( $data['email'] ); ?>" <?php echo ( $vtd_readonly || VTD_Options::get( 'profile_confirm_email', 1 ) ) ? 'readonly' : ''; ?>>
			</label>
			<label class="vtd-field">
				<span><?php esc_html_e( 'Mobile', 'vetra-dashboard' ); ?></span>
				<input type="tel" inputmode="numeric" pattern="[0-9]*" name="phone" value="<?php echo esc_attr( $data['phone'] ); ?>" <?php echo ( $vtd_readonly || VTD_Options::get( 'profile_confirm_phone', 1 ) ) ? 'readonly' : ''; ?>>
			</label>
			<label class="vtd-field">
				<span><?php esc_html_e( 'Gender', 'vetra-dashboard' ); ?></span>
				<select name="gender" <?php echo $vtd_readonly ? 'disabled' : ''; ?>>
					<option value=""><?php esc_html_e( 'Select', 'vetra-dashboard' ); ?></option>
					<option value="male" <?php selected( $data['gender'], 'male' ); ?>><?php esc_html_e( 'Male', 'vetra-dashboard' ); ?></option>
					<option value="female" <?php selected( $data['gender'], 'female' ); ?>><?php esc_html_e( 'Female', 'vetra-dashboard' ); ?></option>
				</select>
			</label>
			<div class="vtd-field">
				<label for="vtd-profile-birthday"><span>تاریخ تولد</span></label>
				<?php echo vtd_jalali_date_input( 'birthday', $data['birthday'], false, 'vtd-profile-birthday' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</div>
			<label class="vtd-field vtd-field-full">
				<span><?php esc_html_e( 'About', 'vetra-dashboard' ); ?></span>
				<textarea name="about" rows="4" <?php echo $vtd_readonly ? 'readonly' : ''; ?>><?php echo esc_textarea( $data['about'] ); ?></textarea>
			</label>

			<?php foreach ( $fields as $slug => $field ) : ?>
				<?php if ( 'date' === $field['type'] ) : ?>
					<div class="vtd-field">
						<label for="vtd-profile-field-<?php echo esc_attr( $slug ); ?>"><span><?php echo esc_html( $field['label'] ); ?></span></label>
						<?php echo vtd_jalali_date_input( $slug, $data[ $slug ] ?? '', $field['required'], 'vtd-profile-field-' . $slug ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</div>
				<?php else : ?>
					<label class="vtd-field">
						<span><?php echo esc_html( $field['label'] ); ?></span>
						<?php if ( 'select' === $field['type'] ) : ?>
							<select name="<?php echo esc_attr( $slug ); ?>" <?php echo $vtd_readonly ? 'disabled' : ''; ?>>
								<option value=""><?php esc_html_e( 'Select', 'vetra-dashboard' ); ?></option>
								<?php foreach ( $field['options'] as $option ) : ?>
									<option value="<?php echo esc_attr( $option ); ?>" <?php selected( $data[ $slug ] ?? '', $option ); ?>><?php echo esc_html( $option ); ?></option>
								<?php endforeach; ?>
							</select>
						<?php else : ?>
							<input type="<?php echo esc_attr( $field['type'] ); ?>" inputmode="<?php echo 'tel' === $field['type'] ? 'numeric' : ''; ?>" name="<?php echo esc_attr( $slug ); ?>" value="<?php echo esc_attr( $data[ $slug ] ?? '' ); ?>" <?php echo $field['required'] ? 'required' : ''; ?> <?php echo $vtd_readonly ? 'readonly' : ''; ?>>
						<?php endif; ?>
					</label>
				<?php endif; ?>
			<?php endforeach; ?>

			<?php if ( ! $vtd_readonly ) : ?>
			<div class="vtd-field-full vtd-form-actions">
				<button type="submit" class="vtd-btn vtd-btn-primary"><?php esc_html_e( 'Save changes', 'vetra-dashboard' ); ?></button>
				<span class="vtd-form-msg" data-vtd-form-msg></span>
			</div>
			<?php endif; ?>
		</form>
	</section>

	<?php if ( $vtd_readonly && $vtd_change_request ) : ?>
		<section class="vtd-card vtd-change-request">
			<h3><?php esc_html_e( 'Request information change', 'vetra-dashboard' ); ?></h3>
			<p class="vtd-muted"><?php esc_html_e( 'Submit a request to change your personal information. An administrator will review your request and may ask for supporting documents.', 'vetra-dashboard' ); ?></p>
			<form class="vtd-form" method="post" enctype="multipart/form-data" data-vtd-change-request-form>
				<input type="hidden" name="vtd_action" value="profile_change_request">
				<?php wp_nonce_field( 'vtd_change_request', 'vtd_change_request_nonce' ); ?>
				<div class="vtd-form-grid">
					<label class="vtd-field">
						<span><?php esc_html_e( 'Field to change', 'vetra-dashboard' ); ?></span>
						<select name="change_field" required>
							<option value=""><?php esc_html_e( 'Select field', 'vetra-dashboard' ); ?></option>
							<option value="first_name"><?php esc_html_e( 'First name', 'vetra-dashboard' ); ?></option>
							<option value="last_name"><?php esc_html_e( 'Last name', 'vetra-dashboard' ); ?></option>
							<option value="email"><?php esc_html_e( 'Email', 'vetra-dashboard' ); ?></option>
							<option value="phone"><?php esc_html_e( 'Mobile', 'vetra-dashboard' ); ?></option>
							<option value="gender"><?php esc_html_e( 'Gender', 'vetra-dashboard' ); ?></option>
							<option value="birthday"><?php esc_html_e( 'Birthday', 'vetra-dashboard' ); ?></option>
						</select>
					</label>
					<label class="vtd-field">
						<span><?php esc_html_e( 'New value', 'vetra-dashboard' ); ?></span>
						<input type="text" name="change_value" required>
					</label>
				</div>
				<label class="vtd-field">
					<span><?php esc_html_e( 'Reason for change', 'vetra-dashboard' ); ?></span>
					<textarea name="change_reason" rows="3" required></textarea>
				</label>
				<label class="vtd-field">
					<span><?php esc_html_e( 'Supporting document (optional)', 'vetra-dashboard' ); ?></span>
					<input type="file" name="change_document" accept=".jpg,.jpeg,.png,.pdf">
				</label>
				<div class="vtd-form-actions">
					<button type="submit" class="vtd-btn vtd-btn-primary"><?php esc_html_e( 'Submit request', 'vetra-dashboard' ); ?></button>
				</div>
			</form>
		</section>
	<?php endif; ?>

	<?php if ( VTD_Options::get( 'profile_change_pass', 1 ) ) : ?>
		<section class="vtd-card">
			<h3><?php esc_html_e( 'Change password', 'vetra-dashboard' ); ?></h3>
			<form class="vtd-form vtd-form-grid" data-vtd-password-form>
				<label class="vtd-field">
					<span><?php esc_html_e( 'Current password', 'vetra-dashboard' ); ?></span>
					<input type="password" name="old_password" autocomplete="current-password">
				</label>
				<label class="vtd-field">
					<span><?php esc_html_e( 'New password', 'vetra-dashboard' ); ?></span>
					<input type="password" name="new_password" autocomplete="new-password">
				</label>
				<div class="vtd-field-full vtd-form-actions">
					<button type="submit" class="vtd-btn vtd-btn-outline"><?php esc_html_e( 'Update password', 'vetra-dashboard' ); ?></button>
					<span class="vtd-form-msg" data-vtd-form-msg></span>
				</div>
			</form>
		</section>
	<?php endif; ?>

	<?php if ( ! empty( $attachments ) ) : ?>
		<section class="vtd-card">
			<h3><?php esc_html_e( 'My attachments', 'vetra-dashboard' ); ?></h3>
			<div class="vtd-grid-3">
				<?php foreach ( $attachments as $row ) : ?>
					<?php $files = VTD_Attachments::files( $row ); ?>
					<div class="vtd-file">
						<span class="vtd-file-icon"><?php echo vtd_icon( 'download' ); // phpcs:ignore ?></span>
						<h4><?php echo esc_html( $row->file_title ); ?></h4>
						<?php if ( ! empty( $row->file_password ) ) : ?>
							<p class="vtd-muted"><?php echo vtd_icon( 'lock' ); // phpcs:ignore ?> <?php echo esc_html( $row->file_password ); ?></p>
						<?php endif; ?>
						<div class="vtd-file-actions">
							<?php foreach ( $files as $file ) : ?>
								<a class="vtd-btn vtd-btn-outline" href="<?php echo esc_url( $file['url'] ); ?>" download><?php esc_html_e( 'Download', 'vetra-dashboard' ); ?></a>
							<?php endforeach; ?>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</section>
	<?php endif; ?>
</div>
