<?php
/**
 * Shared Guest / Host login portal UI.
 *
 * Expects: $vv_login_portal (guest|host), optional $vv_login_redirect, $vv_login_compact (bool).
 */
$vv_login_portal    = isset( $vv_login_portal ) && $vv_login_portal === 'host' ? 'host' : 'guest';
$vv_login_redirect  = isset( $vv_login_redirect ) ? $vv_login_redirect : vv_sanitize_guest_redirect( GET_Request( 'redirect_to' ) );
$vv_login_compact   = ! empty( $vv_login_compact );
$guest_login_url    = vv_users_url( 'login' );
$host_login_url     = vv_admin_url( 'login' );
$guest_tab_url      = vv_login_url( 'guest', $vv_login_redirect );
$host_tab_url       = vv_login_url( 'host', $vv_login_redirect );

if ( $vv_login_redirect !== '' && $vv_login_redirect !== vv_users_url() ) {
	$guest_login_url = add_query_arg( 'redirect_to', rawurlencode( $vv_login_redirect ), $guest_login_url );
}
?>

<div class="vv-login-portal<?php echo $vv_login_compact ? ' vv-login-portal--compact' : ''; ?>">
	<div class="vv-login-portal__tabs" role="tablist">
		<a href="<?php echo esc_url( $guest_tab_url ); ?>" class="vv-login-portal__tab<?php echo $vv_login_portal === 'guest' ? ' is-active' : ''; ?>" role="tab" aria-selected="<?php echo $vv_login_portal === 'guest' ? 'true' : 'false'; ?>">
			<?php echo esc_html( function_exists( 'vv__' ) ? vv__( 'Guest' ) : 'Guest' ); ?>
		</a>
		<a href="<?php echo esc_url( $host_tab_url ); ?>" class="vv-login-portal__tab<?php echo $vv_login_portal === 'host' ? ' is-active' : ''; ?>" role="tab" aria-selected="<?php echo $vv_login_portal === 'host' ? 'true' : 'false'; ?>">
			<?php echo esc_html( function_exists( 'vv__' ) ? vv__( 'Host / Manager' ) : 'Host / Manager' ); ?>
		</a>
	</div>

	<?php if ( $vv_login_portal === 'guest' ) { ?>
		<div class="vv-login-portal__panel">
			<?php if ( ! $vv_login_compact ) { ?>
				<h2 class="vv-login-portal__title"><?php echo esc_html( function_exists( 'vv__' ) ? vv__( 'My Stays' ) : 'My Stays' ); ?></h2>
				<p class="vv-login-portal__lead"><?php echo esc_html( function_exists( 'vv__' ) ? vv__( 'Sign in to view bookings, check-in details, and your conversion discount.' ) : 'Sign in to view bookings, check-in details, and your conversion discount.' ); ?></p>
			<?php } ?>
			<form action="<?php echo esc_url( vv_users_url( 'login' ) ); ?>" method="post" class="vv-login-portal__form">
				<input type="hidden" name="action" value="guest_login">
				<input type="hidden" name="redirect_to" value="<?php echo esc_attr( $vv_login_redirect ); ?>">
				<div class="form-group">
					<label><?php echo esc_html( function_exists( 'vv__' ) ? vv__( 'Email address' ) : 'Email address' ); ?></label>
					<input class="form-control vv-login-portal__input" type="email" name="login" required autocomplete="username">
				</div>
				<div class="form-group">
					<label><?php echo esc_html( function_exists( 'vv__' ) ? vv__( 'Password' ) : 'Password' ); ?></label>
					<input class="form-control vv-login-portal__input" type="password" name="password" required autocomplete="current-password">
				</div>
				<div class="form-group form-check vv-login-portal__remember">
					<input type="checkbox" class="form-check-input" name="remember" value="1" id="vvGuestRemember">
					<label class="form-check-label" for="vvGuestRemember"><?php echo esc_html( function_exists( 'vv__' ) ? vv__( 'Remember me' ) : 'Remember me' ); ?></label>
				</div>
				<button class="btn vv-login-portal__submit" type="submit"><?php echo esc_html( function_exists( 'vv__' ) ? vv__( 'Sign in as guest' ) : 'Sign in as guest' ); ?></button>
			</form>
			<p class="vv-login-portal__footer">
				<a href="<?php echo esc_url( wp_lostpassword_url( vv_users_url() ) ); ?>"><?php echo esc_html( function_exists( 'vv__' ) ? vv__( 'Forgot password?' ) : 'Forgot password?' ); ?></a>
			</p>
		</div>
	<?php } else { ?>
		<div class="vv-login-portal__panel">
			<?php if ( ! $vv_login_compact ) { ?>
				<h2 class="vv-login-portal__title"><?php echo esc_html( function_exists( 'vv__' ) ? vv__( 'Host portal' ) : 'Host portal' ); ?></h2>
				<p class="vv-login-portal__lead"><?php echo esc_html( function_exists( 'vv__' ) ? vv__( 'Sign in to manage apartments, bookings, and your team.' ) : 'Sign in to manage apartments, bookings, and your team.' ); ?></p>
			<?php } ?>
			<form action="<?php echo esc_url( vv_admin_url( 'login' ) ); ?>" method="post" class="vv-login-portal__form">
				<input type="hidden" name="action" value="login">
				<div class="form-group">
					<label><?php echo esc_html( function_exists( 'vv__' ) ? vv__( 'Email address' ) : 'Email address' ); ?></label>
					<input class="form-control vv-login-portal__input au-input au-input--full" type="text" name="login" required autocomplete="username">
				</div>
				<div class="form-group">
					<label><?php echo esc_html( function_exists( 'vv__' ) ? vv__( 'Password' ) : 'Password' ); ?></label>
					<input class="form-control vv-login-portal__input au-input au-input--full" type="password" name="password" required autocomplete="current-password">
				</div>
				<div class="vv-login-portal__remember vv-login-portal__remember--host">
					<label>
						<input type="checkbox" name="remember" value="1">
						<?php echo esc_html( function_exists( 'vv__' ) ? vv__( 'Remember me' ) : 'Remember me' ); ?>
					</label>
				</div>
				<button class="btn vv-login-portal__submit au-btn au-btn--block au-btn--green" type="submit"><?php echo esc_html( function_exists( 'vv__' ) ? vv__( 'Sign in as host' ) : 'Sign in as host' ); ?></button>
			</form>
			<p class="vv-login-portal__footer">
				<?php echo esc_html( function_exists( 'vv__' ) ? vv__( 'New host?' ) : 'New host?' ); ?>
				<a href="<?php echo esc_url( home_url( '/host-application/' ) ); ?>"><?php echo esc_html( function_exists( 'vv__' ) ? vv__( 'Apply to become a host' ) : 'Apply to become a host' ); ?></a>
			</p>
		</div>
	<?php } ?>
</div>

<style>
.vv-login-portal__tabs {
	display: flex;
	gap: 8px;
	margin-bottom: 24px;
	background: #f3efe6;
	border-radius: 999px;
	padding: 4px;
}
.vv-login-portal__tab {
	flex: 1;
	text-align: center;
	padding: 10px 12px;
	border-radius: 999px;
	font-size: 14px;
	font-weight: 600;
	color: #013735;
	text-decoration: none !important;
	transition: background .15s, color .15s;
}
.vv-login-portal__tab.is-active {
	background: #013735;
	color: #fff;
}
.vv-login-portal__title {
	font-size: 26px;
	margin: 0 0 8px;
	color: #013735;
}
.vv-login-portal__lead {
	color: #BEA473;
	margin-bottom: 24px;
	font-size: 15px;
}
.vv-login-portal__input {
	border-radius: 10px;
	border-color: #BEA473;
}
.vv-login-portal__remember {
	margin-bottom: 16px;
	font-size: 14px;
}
.vv-login-portal__remember--host label {
	display: flex;
	align-items: center;
	gap: 8px;
	margin: 0 0 16px;
	font-size: 14px;
}
.vv-login-portal__submit {
	background: #FC780E;
	border-color: #FC780E;
	color: #fff;
	border-radius: 999px;
	padding: 10px 24px;
	width: 100%;
	font-weight: 600;
}
.vv-login-portal__submit:hover {
	background: #e06a0c;
	border-color: #e06a0c;
	color: #fff;
}
.vv-login-portal--compact .vv-login-portal__tabs {
	margin-bottom: 16px;
}
.vv-login-portal__footer {
	margin: 16px 0 0;
	text-align: center;
	font-size: 14px;
}
.vv-login-portal__footer a {
	color: #013735;
	font-weight: 600;
}
</style>
