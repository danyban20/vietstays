<?php
/** Conversion stay portal — external booking guest without Vietstays booking yet. */
$conversion = [];
if ( class_exists( 'vvConversions' ) ) {
	$conversion = vvConversions::get_active_conversion_for_user( get_current_user_id() );
	if ( empty( $conversion['ID'] ) && ! empty( $_SESSION['VV_CONVERSION_ID'] ) ) {
		global $visitVietnam;
		if ( isset( $visitVietnam->conversions_class ) ) {
			$conversion = $visitVietnam->conversions_class->get_conversion( intval( $_SESSION['VV_CONVERSION_ID'] ) );
		}
	}
}

if ( empty( $conversion['ID'] ) ) {
	?>
	<div class="portal-header"><h1>Stay portal</h1></div>
	<div class="card-booking">
		<p class="mb-0">No active conversion stay found. Open the link from your host invite email, or view your bookings below.</p>
		<p class="mt-3 mb-0"><a href="<?php echo esc_url( vv_users_url() ); ?>">My stays</a></p>
	</div>
	<?php
	return;
}

$apartment     = vv_get_apartment( intval( gArrayItem( $conversion, 'apartment_id' ) ) );
$discount_pct  = intval( gArrayItem( $conversion, 'discount_pct', vvConversions::DISCOUNT_PCT ) );
$promo_code    = gArrayItem( $conversion, 'promo_code' );
$check_in      = gArrayItem( $conversion, 'check_in_date' );
$check_out     = gArrayItem( $conversion, 'check_out_date' );
$expires_at    = gArrayItem( $conversion, 'expires_at' );
$platform      = gArrayItem( vvConversions::platforms(), gArrayItem( $conversion, 'external_platform' ), gArrayItem( $conversion, 'external_platform' ) );
$apt_url       = function_exists( 'vv_get_apartment_url' ) ? vv_get_apartment_url( $apartment ) : home_url( '/' );
$passport_id   = intval( get_user_meta( get_current_user_id(), 'vv_passport_document', true ) );
$checkin_status = get_user_meta( get_current_user_id(), 'vv_checkin_status', true );
if ( $checkin_status === '' ) {
	$checkin_status = $passport_id > 0 ? 'passport_submitted' : 'pending';
}
?>
<div class="portal-header">
	<div>
		<p class="mb-1"><a href="<?php echo esc_url( vv_users_url() ); ?>">&larr; My stays</a></p>
		<h1>Your stay at <?php echo esc_html( gArrayItem( $apartment, 'display_name', gArrayItem( $apartment, 'name' ) ) ); ?></h1>
	</div>
	<div>
		<a class="btn-outline" href="<?php echo esc_url( vv_users_url() . '?action=guest_logout' ); ?>">Sign out</a>
	</div>
</div>

<div class="card-booking" style="border-color:#013735;">
	<p class="mb-2"><span class="badge badge-success"><?php echo intval( $discount_pct ); ?>% direct booking discount active</span></p>
	<p class="mb-1"><strong>Promo code:</strong> <code><?php echo esc_html( $promo_code ); ?></code></p>
	<?php if ( $expires_at ) { ?>
		<p class="mb-0 small text-muted">Valid until <?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $expires_at ) ) ); ?> · single use</p>
	<?php } ?>
</div>

<div class="card-booking">
	<h3 class="h5">Stay details</h3>
	<p class="mb-1"><strong>Booked via:</strong> <?php echo esc_html( $platform ); ?></p>
	<?php if ( $check_in && $check_out ) { ?>
		<p class="mb-1"><strong>Dates:</strong> <?php echo esc_html( vv_get_date_range_text( $check_in, $check_out ) ); ?></p>
	<?php } ?>
	<p class="mb-1"><strong>Address:</strong> <?php echo esc_html( gArrayItem( $apartment, 'address' ) ); ?></p>
	<?php if ( gArrayItem( $apartment, 'room_number' ) !== '' ) { ?>
		<p class="mb-0"><strong>Apartment:</strong> <?php echo esc_html( gArrayItem( $apartment, 'room_number' ) ); ?></p>
	<?php } ?>
</div>

<div class="card-booking">
	<h3 class="h5">Check-in documents</h3>
	<p class="mb-2">Upload your passport before arrival to speed up check-in (required in Vietnam).</p>
	<p class="mb-2"><strong>Status:</strong> <?php echo esc_html( vv_get_status_name_text( $checkin_status ) ); ?></p>
	<?php if ( $passport_id <= 0 ) { ?>
		<form action="<?php echo esc_url( vv_users_url( 'conversion/stay' ) ); ?>" method="post" enctype="multipart/form-data">
			<input type="hidden" name="action" value="guest_passport_upload">
			<input type="hidden" name="conversion_stay" value="1">
			<input type="file" name="passport_file" class="form-control-file mb-2" accept="image/*,.pdf" required>
			<button type="submit" class="btn btn-primary btn-sm">Upload passport</button>
		</form>
	<?php } else { ?>
		<p class="text-success mb-0">Passport on file — uploaded <?php echo esc_html( get_user_meta( get_current_user_id(), 'vv_passport_uploaded_at', true ) ); ?></p>
	<?php } ?>
</div>

<div class="card-booking">
	<h3 class="h5">Book directly on Vietstays</h3>
	<p class="mb-3">Skip platform fees and save <?php echo intval( $discount_pct ); ?>% when you book this apartment again through Vietstays.</p>
	<form method="post" class="d-inline">
		<input type="hidden" name="vv_action" value="conversion_start_booking">
		<input type="hidden" name="conversion_token" value="<?php echo esc_attr( gArrayItem( $conversion, 'conversion_token' ) ); ?>">
		<button type="submit" class="btn btn-primary">Book with discount</button>
	</form>
	<a href="<?php echo esc_url( $apt_url ); ?>" class="btn btn-outline-dark ml-2">View apartment</a>
</div>

<div class="card-booking">
	<h3 class="h5">House rules &amp; amenities</h3>
	<p class="mb-2 small text-muted">Full access codes and WiFi are shared by your host closer to check-in. Below is a preview of what is included.</p>
	<?php
	$facilities = vv_get_facilities();
	$a_facilities = json_decode( gArrayItem( $apartment, 'facilities' ), true );
	if ( ! is_array( $a_facilities ) ) {
		$a_facilities = [];
	}
	if ( ! empty( $a_facilities ) ) {
		echo '<ul class="mb-0 pl-3">';
		foreach ( $facilities as $facility ) {
			if ( in_array( gArrayItem( $facility, 'facility_id' ), $a_facilities, true ) ) {
				echo '<li>' . esc_html( gArrayItem( $facility, 'name' ) ) . '</li>';
			}
		}
		echo '</ul>';
	} else {
		echo '<p class="mb-0 text-muted">Amenity list will appear here once the host completes the listing.</p>';
	}
	?>
</div>
