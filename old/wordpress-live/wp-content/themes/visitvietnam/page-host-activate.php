<?php
/**
 * Template Name: Host Activate
 */
get_header();

$key   = sanitize_text_field( GET_Request( 'key' ) );
$error = '';
$done  = false;

if ( $key === '' ) {
	$error = 'Invalid or missing activation link.';
} else {
	global $visitvietnam;
	if ( ! isset( $visitvietnam ) || ! isset( $visitvietnam->host_applications_class ) ) {
		$host_app_class = new vvHostApplications();
	} else {
		$host_app_class = $visitvietnam->host_applications_class;
	}
	$activation = $host_app_class->validate_activation_key( $key );
	if ( is_wp_error( $activation ) ) {
		$error = $activation->get_error_message();
	}
}
?>

<style>
#host_activate {
	background: #f7f3ea;
	padding: 60px 0 80px;
	min-height: 70vh;
	color: #444;
}
#host_activate .host-activate-wrap {
	max-width: 520px;
	margin: 0 auto;
	padding: 0 16px;
}
#host_activate .host-activate-card {
	background: #fff;
	border: 1px solid #e5e5e5;
	border-radius: 16px;
	padding: 32px;
	box-shadow: 0 8px 30px rgba(0, 64, 65, 0.08);
	color: #444;
}
#host_activate .host-activate-card h1 {
	font-size: 24px;
	color: #004041;
	margin-bottom: 8px;
}
#host_activate .host-activate-card p {
	color: #555;
}
#host_activate .host-activate-card label {
	color: #004041;
	font-weight: 600;
	margin-bottom: 4px;
}
#host_activate .host-activate-card input[type="text"],
#host_activate .host-activate-card input[type="email"] {
	display: block;
	width: 100%;
	margin: 0 0 16px;
	padding: 14px 20px;
	background: #fff !important;
	color: #004041 !important;
	-webkit-text-fill-color: #004041 !important;
	caret-color: #004041 !important;
	border: 1px solid #e4e4e4;
	border-radius: 8px;
	font-size: 16px;
	line-height: normal;
	color-scheme: light;
}
#host_activate .host-activate-card input[type="password"] {
	display: block;
	width: 100%;
	margin: 0 0 16px;
	padding: 14px 20px;
	background: #fff !important;
	color: #004041 !important;
	-webkit-text-fill-color: #004041 !important;
	caret-color: #004041 !important;
	border: 1px solid #e4e4e4;
	border-radius: 8px;
	font-size: 18px !important;
	line-height: normal;
	color-scheme: light;
	/* Custom theme fonts break password bullet glyphs — use system font */
	font-family: Arial, Helvetica, sans-serif !important;
	letter-spacing: 0.12em;
}
#host_activate .host-activate-card input[type="password"]::selection {
	background-color: #b3d4fc;
	color: #004041 !important;
	-webkit-text-fill-color: #004041 !important;
}
#host_activate .host-activate-card input[type="password"]::-moz-selection {
	background-color: #b3d4fc;
	color: #004041 !important;
}
#host_activate .host-activate-card input[type="password"]:focus,
#host_activate .host-activate-card input[type="email"]:focus,
#host_activate .host-activate-card input[type="text"]:focus {
	border-color: #FD780E;
	outline: none;
	color: #004041 !important;
	-webkit-text-fill-color: #004041 !important;
}
#host_activate .host-activate-card input[type="password"]:-webkit-autofill,
#host_activate .host-activate-card input[type="password"]:-webkit-autofill:hover,
#host_activate .host-activate-card input[type="password"]:-webkit-autofill:focus,
#host_activate .host-activate-card input:-internal-autofill-selected {
	color: #004041 !important;
	-webkit-text-fill-color: #004041 !important;
	box-shadow: 0 0 0 1000px #fff inset !important;
}
#host_activate .host-activate-card input:disabled {
	color: #666 !important;
	-webkit-text-fill-color: #666 !important;
	background-color: #f5f5f5 !important;
}
#host_activate .host-activate-card .btn-primary {
	background: #FD780E;
	border-color: #FD780E;
	color: #fff;
}
#host_activate .host-activate-card .btn-primary:hover {
	background: #004041;
	border-color: #004041;
	color: #fff;
}
</style>

<div id="host_activate">
<div class="host-activate-wrap">
	<div class="host-activate-card">
		<?php if ( $error !== '' ) { ?>
			<h1>Activation link invalid</h1>
			<div class="alert alert-danger"><?php echo esc_html( $error ); ?></div>
			<p>If you need help, email <a href="mailto:<?php echo esc_attr( vv_admin_contact_email() ); ?>"><?php echo esc_html( vv_admin_contact_email() ); ?></a> with your application ID.</p>
		<?php } else { ?>
			<h1>Activate your Vietstays host account</h1>
			<p>Hi <?php echo esc_html( gArrayItem( $activation, 'full_name' ) ); ?>, set your password and verify your email to access the host portal.</p>
			<p class="small text-muted">Application: <?php echo esc_html( gArrayItem( $activation, 'application_ref' ) ); ?></p>

			<?php
			$form_errors = gArrayItem( $_SESSION, 'HOST_ACTIVATE_ERRORS', [] );
			if ( ! is_array( $form_errors ) ) {
				$form_errors = [];
			}
			if ( count( $form_errors ) > 0 ) {
				echo '<div class="alert alert-danger"><ul class="mb-0">';
				foreach ( $form_errors as $msg ) {
					echo '<li>' . esc_html( $msg ) . '</li>';
				}
				echo '</ul></div>';
				unset( $_SESSION['HOST_ACTIVATE_ERRORS'] );
			}
			?>

			<form method="post" action="<?php echo esc_url( get_permalink() ); ?>">
				<input type="hidden" name="vv_action" value="vv_host_activate_account">
				<input type="hidden" name="activation_key" value="<?php echo esc_attr( $key ); ?>">

				<div class="form-group">
					<label>Email</label>
					<input type="email" value="<?php echo esc_attr( gArrayItem( $activation, 'email' ) ); ?>" disabled>
				</div>
				<div class="form-group">
					<label>New password</label>
					<input type="password" name="password" required minlength="8" autocomplete="new-password" style="font-family: Arial, Helvetica, sans-serif; color: #004041; -webkit-text-fill-color: #004041;">
				</div>
				<div class="form-group">
					<label>Confirm password</label>
					<input type="password" name="password_confirm" required minlength="8" autocomplete="new-password" style="font-family: Arial, Helvetica, sans-serif; color: #004041; -webkit-text-fill-color: #004041;">
				</div>
				<button type="submit" class="btn btn-primary">Verify email &amp; activate account</button>
			</form>
		<?php } ?>
	</div>
</div>
</div>

<?php get_footer(); ?>
