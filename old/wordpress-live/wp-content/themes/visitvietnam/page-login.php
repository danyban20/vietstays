<?php
/**
 * Template Name: Sign In
 *
 * Unified guest / host login.
 */
$portal = sanitize_key( GET_Request( 'portal' ) );
if ( $portal !== 'host' ) {
	$portal = 'guest';
}

if ( is_user_logged_in() ) {
	if ( $portal === 'host' ) {
		if ( function_exists( 'vv_host_can_access_admin' ) && vv_host_can_access_admin( get_current_user_id() ) ) {
			wp_redirect( vv_admin_url( 'dashboard' ) );
			exit;
		}
	} else {
		wp_redirect( vv_sanitize_guest_redirect( GET_Request( 'redirect_to' ) ) );
		exit;
	}
}

$vv_login_portal   = $portal;
$vv_login_redirect = vv_sanitize_guest_redirect( GET_Request( 'redirect_to' ) );
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title><?php echo esc_html( function_exists( 'vv__' ) ? vv__( 'Sign in' ) : 'Sign in' ); ?> | Vietstays</title>
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" crossorigin="anonymous">
	<style>
		body { background: #F0E8D5; color: #013735; font-family: Georgia, serif; margin: 0; }
		.vv-signin-wrap { min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 40px 15px; }
		.vv-signin-card { background: #fff; border-radius: 20px; padding: 40px; max-width: 460px; width: 100%; box-shadow: 0 10px 40px rgba(1,55,53,.12); }
		.vv-signin-brand { text-align: center; margin-bottom: 24px; font-size: 22px; font-weight: 700; color: #013735; }
	</style>
</head>
<body>
	<div class="vv-signin-wrap">
		<div class="vv-signin-card">
			<div class="vv-signin-brand">Vietstays</div>
			<?php showSuccessMsg( false ); ?>
			<?php showErrorMsg( false ); ?>
			<?php
			include WP_PLUGIN_DIR . '/visitvietnam/inc/partials/vv-login-portal.php';
			?>
		</div>
	</div>
</body>
</html>
