<?php
/**
 * Template Name: Stay Welcome
 *
 * Conversion invite landing — guest welcome portal (Spec §1.9 / §2.1).
 */
get_header();

$token      = sanitize_text_field( GET_Request( 'token' ) );
$error      = '';
$conversion = [];
$apartment  = [];
$host       = null;

if ( $token === '' ) {
	$error = 'This welcome link is missing or invalid.';
} elseif ( ! class_exists( 'vvConversions' ) ) {
	$error = 'This feature is not available right now.';
} else {
	global $visitVietnam;
	$conv_class = isset( $visitVietnam->conversions_class ) ? $visitVietnam->conversions_class : new vvConversions();
	$conversion = $conv_class->get_conversion_by_token( $token );

	if ( empty( $conversion['ID'] ) ) {
		$error = 'This welcome link is invalid or has expired.';
	} else {
		$valid = vvConversions::validate_conversion_active( $conversion );
		if ( is_wp_error( $valid ) ) {
			$error = $valid->get_error_message();
		} else {
			vvConversions::apply_conversion_session( $conversion );
			vvConversions::mark_opened( intval( $conversion['ID'] ) );

			if ( is_user_logged_in() ) {
				$user = wp_get_current_user();
				if ( strtolower( $user->user_email ) === strtolower( gArrayItem( $conversion, 'guest_email' ) ) ) {
					vvConversions::link_conversion_to_user( intval( $conversion['ID'] ), get_current_user_id() );
					wp_redirect( vv_users_url( 'conversion/stay' ) );
					exit;
				}
			}

			$apartment = vv_get_apartment( intval( gArrayItem( $conversion, 'apartment_id' ) ) );
			$host      = get_user_by( 'ID', intval( gArrayItem( $conversion, 'host_id' ) ) );
		}
	}
}

$discount_pct = intval( gArrayItem( $conversion, 'discount_pct', vvConversions::DISCOUNT_PCT ) );
$guest_name   = gArrayItem( $conversion, 'guest_name' ) ?: 'Guest';
$check_in     = gArrayItem( $conversion, 'check_in_date' );
$check_out    = gArrayItem( $conversion, 'check_out_date' );
$expires_at   = gArrayItem( $conversion, 'expires_at' );
$apt_image    = '';
if ( ! empty( $apartment['ID'] ) ) {
	$images = vv_get_apartment_images( $apartment );
	if ( ! empty( $images[0]['thumb'] ) ) {
		$apt_image = $images[0]['thumb'];
	}
}
?>

<style>
#stay_welcome { background: #F0E8D5; padding: 48px 0 80px; min-height: 70vh; color: #013735; }
#stay_welcome .wrap { max-width: 720px; margin: 0 auto; padding: 0 16px; }
#stay_welcome .card { background: #fff; border-radius: 20px; border: 1px solid #BEA473; padding: 32px; margin-bottom: 24px; box-shadow: 0 8px 30px rgba(1,55,53,.08); }
#stay_welcome h1 { font-size: 28px; margin-bottom: 8px; }
#stay_welcome .lead { color: #BEA473; margin-bottom: 24px; }
#stay_welcome .discount-badge { display: inline-block; background: #013735; color: #fff; border-radius: 999px; padding: 6px 14px; font-size: 13px; margin-bottom: 16px; }
#stay_welcome .apt-preview { display: flex; gap: 16px; align-items: flex-start; margin-bottom: 20px; }
#stay_welcome .apt-preview img { width: 120px; height: 90px; object-fit: cover; border-radius: 12px; }
#stay_welcome .form-control { border-radius: 10px; border-color: #BEA473; margin-bottom: 12px; }
#stay_welcome .btn-primary { background: #FC780E; border-color: #FC780E; border-radius: 999px; padding: 10px 24px; }
#stay_welcome .btn-primary:hover { background: #e06a0c; border-color: #e06a0c; }
#stay_welcome .btn-outline-dark { border-radius: 999px; }
#stay_welcome .tabs { display: flex; gap: 8px; margin-bottom: 16px; }
#stay_welcome .tabs button { border: 1px solid #BEA473; background: #fff; border-radius: 999px; padding: 6px 16px; cursor: pointer; }
#stay_welcome .tabs button.is-active { background: #013735; color: #fff; border-color: #013735; }
</style>

<div id="stay_welcome">
	<div class="wrap">
		<?php showSuccessMsg( false ); ?>
		<?php showErrorMsg( false ); ?>

		<?php if ( $error !== '' ) { ?>
			<div class="card">
				<h1>Welcome link unavailable</h1>
				<p class="mb-0"><?php echo esc_html( $error ); ?></p>
				<p class="mt-3 mb-0"><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Return to homepage</a></p>
			</div>
		<?php } else { ?>
			<div class="card">
				<span class="discount-badge"><?php echo intval( $discount_pct ); ?>% off your next direct booking</span>
				<h1>Welcome to <?php echo esc_html( gArrayItem( $apartment, 'display_name', gArrayItem( $apartment, 'name', 'your stay' ) ) ); ?></h1>
				<p class="lead">Hi <?php echo esc_html( $guest_name ); ?> — your host invited you to Vietstays for practical stay info and a private rebooking discount.</p>

				<div class="apt-preview">
					<?php if ( $apt_image !== '' ) { ?>
						<img src="<?php echo esc_url( $apt_image ); ?>" alt="">
					<?php } ?>
					<div>
						<p class="mb-1"><strong>Your stay</strong></p>
						<?php if ( $check_in && $check_out ) { ?>
							<p class="mb-1"><?php echo esc_html( vv_get_date_range_text( $check_in, $check_out ) ); ?></p>
						<?php } ?>
						<?php if ( $host ) { ?>
							<p class="mb-0 small text-muted">Host: <?php echo esc_html( $host->display_name ); ?></p>
						<?php } ?>
						<?php if ( $expires_at ) { ?>
							<p class="mb-0 small text-muted">Discount valid until <?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $expires_at ) ) ); ?></p>
						<?php } ?>
					</div>
				</div>

				<ul class="mb-4 pl-3">
					<li>View apartment details and check-in guidance in your guest portal</li>
					<li>Upload passport info for faster check-in</li>
					<li>Book the same apartment directly on Vietstays with <?php echo intval( $discount_pct ); ?>% off — no booking fee</li>
				</ul>

				<form method="post" class="mb-3">
					<input type="hidden" name="vv_action" value="conversion_start_booking">
					<input type="hidden" name="conversion_token" value="<?php echo esc_attr( $token ); ?>">
					<button type="submit" class="btn btn-primary">Book now with <?php echo intval( $discount_pct ); ?>% discount</button>
				</form>
			</div>

			<div class="card">
				<h2 class="h5 mb-3">Access your guest portal</h2>
				<p class="small text-muted mb-3">Create an account or sign in with <strong><?php echo esc_html( gArrayItem( $conversion, 'guest_email' ) ); ?></strong> to save your discount and view stay details.</p>

				<div class="tabs" role="tablist">
					<button type="button" class="is-active" data-tab="register">Create account</button>
					<button type="button" data-tab="login">Sign in</button>
				</div>

				<form method="post" id="stayWelcomeRegister">
					<input type="hidden" name="vv_action" value="conversion_guest_account">
					<input type="hidden" name="conversion_token" value="<?php echo esc_attr( $token ); ?>">
					<input type="hidden" name="account_mode" value="register">
					<input type="hidden" name="email" value="<?php echo esc_attr( gArrayItem( $conversion, 'guest_email' ) ); ?>">
					<div class="row">
						<div class="col-md-6">
							<input type="text" name="firstname" class="form-control" placeholder="First name" required>
						</div>
						<div class="col-md-6">
							<input type="text" name="lastname" class="form-control" placeholder="Last name" required>
						</div>
					</div>
					<input type="email" class="form-control" value="<?php echo esc_attr( gArrayItem( $conversion, 'guest_email' ) ); ?>" disabled>
					<input type="password" name="password" class="form-control" placeholder="Choose a password (min. 8 characters)" minlength="8" required>
					<button type="submit" class="btn btn-primary">Create account &amp; open portal</button>
				</form>

				<form method="post" id="stayWelcomeLogin" style="display:none;">
					<input type="hidden" name="vv_action" value="conversion_guest_account">
					<input type="hidden" name="conversion_token" value="<?php echo esc_attr( $token ); ?>">
					<input type="hidden" name="account_mode" value="login">
					<input type="email" name="login" class="form-control" value="<?php echo esc_attr( gArrayItem( $conversion, 'guest_email' ) ); ?>" required>
					<input type="password" name="password" class="form-control" placeholder="Password" required>
					<button type="submit" class="btn btn-primary">Sign in &amp; open portal</button>
				</form>
			</div>
		<?php } ?>
	</div>
</div>

<script>
jQuery(function($){
	$('#stay_welcome .tabs button').on('click', function(){
		var tab = $(this).data('tab');
		$('#stay_welcome .tabs button').removeClass('is-active');
		$(this).addClass('is-active');
		if (tab === 'login') {
			$('#stayWelcomeRegister').hide();
			$('#stayWelcomeLogin').show();
		} else {
			$('#stayWelcomeRegister').show();
			$('#stayWelcomeLogin').hide();
		}
	});
});
</script>

<?php get_footer(); ?>
