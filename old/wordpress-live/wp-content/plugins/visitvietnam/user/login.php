<?php
$redirect_to       = vv_sanitize_guest_redirect( GET_Request( 'redirect_to' ) );
$portal            = sanitize_key( GET_Request( 'portal' ) );
$vv_login_portal   = $portal === 'host' ? 'host' : 'guest';
$vv_login_redirect = $redirect_to;

if ( is_user_logged_in() ) {
	if ( $vv_login_portal === 'host' ) {
		if ( function_exists( 'vv_host_can_access_admin' ) && vv_host_can_access_admin( get_current_user_id() ) ) {
			wp_redirect( vv_admin_url( 'dashboard' ) );
			exit;
		}
	} else {
		wp_redirect( $redirect_to );
		exit;
	}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>My Stays | Sign in</title>
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" crossorigin="anonymous">
	<style>
		body { background: #F0E8D5; color: #013735; font-family: Georgia, serif; }
		.login-wrap { min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 40px 15px; }
		.login-card { background: #fff; border-radius: 20px; padding: 40px; max-width: 460px; width: 100%; box-shadow: 0 10px 40px rgba(1,55,53,.12); }
	</style>
</head>
<body>
	<div class="login-wrap">
		<div class="login-card">
			<?php showSuccessMsg( false ); ?>
			<?php showErrorMsg( false ); ?>
			<?php include dirname( __DIR__ ) . '/inc/partials/vv-login-portal.php'; ?>
		</div>
	</div>
</body>
</html>
