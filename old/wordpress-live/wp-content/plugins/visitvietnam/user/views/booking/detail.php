<?php
$booking_class   = new vvBookings;
$apartment_class = new vvApartments;
$users_class     = new vvUsers;
$booking_num     = trim( GET_Request( 'booking' ) );
$booking         = vv_get_booking_by_num( $booking_num );
$user_id         = get_current_user_id();

if ( gArrayItem( $booking, 'ID' ) <= 0 || ! vv_guest_can_view_booking( $booking, $user_id ) ) {
	?>
	<div class="portal-header">
		<h1>Booking not found</h1>
	</div>
	<div class="card-booking">
		<p>This booking was not found or you do not have permission to view it.</p>
		<a href="<?php echo esc_url( vv_users_url() ); ?>">Back to my stays</a>
	</div>
	<?php
	return;
}

$show_practical = vv_booking_has_practical_access( $booking );
$apartment      = $apartment_class->get_apartment( gArrayItem( $booking, 'apartment_id' ) );
$host_id        = gArrayItem( $apartment, 'user_id' );
$host           = $users_class->get_user( $host_id );
$facilities     = vv_get_facilities();

$check_in_date  = gArrayItem( $booking, 'check_in_date' );
$check_out_date = gArrayItem( $booking, 'check_out_date' );
$checkInDate    = new DateTime( $check_in_date );
$checkOutDate   = new DateTime( $check_out_date );
$nights         = $checkInDate->diff( $checkOutDate )->days;

$host_img = get_user_meta( $host_id, 'user_avatar', true );
if ( $host_img > 0 ) {
	$host_img = wp_get_attachment_url( $host_img );
}
if ( $host_img === '' ) {
	$host_img = get_avatar_url( $host_id, 64 );
}

$host_verification_status = get_user_meta( $host_id, 'host_verification_status', true );
if ( $host_verification_status === '' ) {
	$host_verification_status = 'pending';
}

$passport_id = intval( get_user_meta( $user_id, 'vv_passport_document', true ) );
$checkin_status = get_user_meta( $user_id, 'vv_checkin_status', true );
if ( $checkin_status === '' ) {
	$checkin_status = $passport_id > 0 ? 'passport_submitted' : 'pending';
}

global $head_codes, $footer_codes;
ob_start();
?>
<style>
	#guest-booking-detail .hero-box { background: #013735; color: #fff; border-radius: 20px; padding: 30px; margin-bottom: 24px; }
	#guest-booking-detail .info-box { background: #fff; border: 1px solid #BEA473; border-radius: 20px; padding: 24px; margin-bottom: 24px; }
	#guest-booking-detail .practical_info { background: #013735; color: #fff; border-radius: 20px; padding: 30px; margin-top: 24px; }
	#guest-booking-detail .practical_info .block { border: 1px solid #F0E8D5; border-radius: 20px; padding: 20px; margin-bottom: 16px; position: relative; }
	#guest-booking-detail .practical_info .block h4 { color: #F0E8D5; margin-bottom: 0; }
	#guest-booking-detail .practical_info .block a { position: absolute; top: 22px; right: 20px; color: #F0E8D5; }
	#guest-booking-detail .host_info { text-align: center; }
	#guest-booking-detail .host_info .avatar img { width: 80px; height: 80px; border-radius: 50%; object-fit: cover; }
</style>
<?php
$head_codes .= ob_get_clean();
?>
<div id="guest-booking-detail">
	<div class="portal-header">
		<div>
			<p class="mb-1"><a href="<?php echo esc_url( vv_users_url() ); ?>">&larr; All bookings</a></p>
			<h1>Booking #<?php echo esc_html( vv_get_booking_num( $booking ) ); ?></h1>
		</div>
		<div>
			<a class="btn-outline" href="<?php echo esc_url( vv_users_url() . '?action=guest_logout' ); ?>">Sign out</a>
		</div>
	</div>

	<div class="hero-box">
		<div class="row">
			<div class="col-md-8">
				<h2 class="h4"><?php echo esc_html( gArrayItem( $apartment, 'display_name' ) ); ?></h2>
				<p class="mb-1"><?php echo esc_html( vv_get_date_range_text( $check_in_date, $check_out_date ) ); ?> (<?php echo intval( $nights ); ?> nights)</p>
				<p class="mb-0">Status: <?php echo esc_html( vv_get_status_name_text( gArrayItem( $booking, 'status' ) ) ); ?></p>
			</div>
			<div class="col-md-4 text-md-right">
				<p class="mb-0"><strong><?php echo esc_html( vv_number_format( gArrayItem( $booking, 'total' ), true ) ); ?></strong></p>
			</div>
		</div>
	</div>

	<?php if ( ! $show_practical ) { ?>
		<div class="alert alert-info alert-portal">
			Practical check-in information (WiFi, door codes, and full stay details) will be available once your booking is confirmed and paid.
		</div>
	<?php } ?>

	<div class="info-box">
		<div class="row">
			<div class="col-md-8">
				<h3 class="h5">Apartment</h3>
				<p class="mb-1"><strong>Address:</strong> <?php echo esc_html( gArrayItem( $apartment, 'address' ) ); ?></p>
				<?php if ( gArrayItem( $apartment, 'room_number' ) !== '' ) { ?>
					<p class="mb-1"><strong>Apartment number:</strong> <?php echo esc_html( gArrayItem( $apartment, 'room_number' ) ); ?></p>
				<?php } ?>
				<?php if ( gArrayItem( $apartment, 'floor_number' ) !== '' ) { ?>
					<p class="mb-1"><strong>Floor:</strong> <?php echo esc_html( gArrayItem( $apartment, 'floor_number' ) ); ?></p>
				<?php } ?>
				<p class="mb-0">
					<strong>Check-in / check-out:</strong>
					<?php
					if ( gArrayItem( $apartment, 'check_in_time2' ) !== '' && gArrayItem( $apartment, 'check_in_time1' ) !== '' ) {
						echo esc_html( date( 'H:i', strtotime( $apartment['check_in_time1'] ) ) . ' - ' . date( 'H:i', strtotime( $apartment['check_in_time2'] ) ) );
					} else {
						echo esc_html( date( 'H:i', strtotime( gArrayItem( $apartment, 'check_in_time1' ) ) ) );
					}
					echo ' / ' . esc_html( date( 'H:i', strtotime( gArrayItem( $apartment, 'check_out_time' ) ) ) );
					?>
				</p>
			</div>
			<div class="col-md-4">
				<div class="host_info">
					<div class="avatar mb-2"><img src="<?php echo esc_url( $host_img ); ?>" alt=""></div>
					<h4 class="h6"><?php echo esc_html( gArrayItem( $host, 'firstname' ) . ' ' . gArrayItem( $host, 'lastname' ) ); ?></h4>
					<p class="small mb-0"><?php echo esc_html( vv_get_status_name_text( $host_verification_status ) ); ?></p>
				</div>
			</div>
		</div>
	</div>

	<div class="info-box">
		<h3 class="h5">Check-in documents</h3>
		<p class="mb-2">Upload a passport photo to speed up check-in (required for some properties).</p>
		<p class="mb-3"><strong>Status:</strong> <?php echo esc_html( vv_get_status_name_text( $checkin_status ) ); ?></p>
		<?php if ( $passport_id > 0 ) { ?>
			<p class="text-success mb-3">Passport on file — uploaded <?php echo esc_html( get_user_meta( $user_id, 'vv_passport_uploaded_at', true ) ); ?></p>
		<?php } ?>
		<form action="" method="post" enctype="multipart/form-data" class="form-inline">
			<input type="hidden" name="action" value="guest_passport_upload">
			<input type="hidden" name="booking_num" value="<?php echo esc_attr( vv_get_booking_num( $booking ) ); ?>">
			<input type="file" name="passport_file" class="form-control-file mr-2 mb-2" accept="image/*,.pdf" required>
			<button type="submit" class="btn btn-primary mb-2">Upload passport</button>
		</form>
	</div>

	<?php if ( $show_practical ) { ?>
		<div class="info-box">
			<h3 class="h5">What's included</h3>
			<ul class="list-unstyled row">
				<?php
				$a_facilities = json_decode( gArrayItem( $apartment, 'facilities' ), true );
				if ( ! is_array( $a_facilities ) ) {
					$a_facilities = [];
				}
				foreach ( $facilities as $facility ) {
					foreach ( $a_facilities as $a_facility ) {
						if ( $a_facility == gArrayItem( $facility, 'facility_id' ) ) {
							echo '<li class="col-md-6 mb-2">' . esc_html( gArrayItem( $facility, 'name' ) ) . '</li>';
						}
					}
				}
				?>
			</ul>
		</div>

		<div class="info-box">
			<h3 class="h5">Location</h3>
			<div id="apartments_map" style="height: 400px; width: 100%; border-radius: 20px; border: 1px solid #BEA473;"></div>
		</div>

		<div class="practical_info">
			<h2 class="h4 mb-4">Practical Information</h2>
			<div class="blocks">
				<?php
				$show_sensitive = true;
				include dirname( dirname( dirname( __DIR__ ) ) ) . '/inc/partials/guest-booking-practical.php';
				?>
			</div>
		</div>
	<?php } ?>
</div>
<?php
ob_start();
?>
<script src="https://maps.googleapis.com/maps/api/js?key=<?php echo esc_attr( vv_get_google_map_api_key() ); ?>"></script>
<script>
jQuery(function($) {
	<?php if ( $show_practical && gArrayItem( $apartment, 'address_latitude' ) !== '' ) { ?>
	var lat = parseFloat(<?php echo floatval( gArrayItem( $apartment, 'address_latitude' ) ); ?>);
	var lng = parseFloat(<?php echo floatval( gArrayItem( $apartment, 'address_longitude' ) ); ?>);
	if ($('#apartments_map').length && !isNaN(lat) && !isNaN(lng)) {
		var map = new google.maps.Map(document.getElementById('apartments_map'), { zoom: 14, center: { lat: lat, lng: lng } });
		new google.maps.Marker({ position: { lat: lat, lng: lng }, map: map });
	}
	<?php } ?>
	$('.practical_info .collapse').on('shown.bs.collapse', function () {
		$(this).prev('a').find('i').removeClass('fa-angle-down').addClass('fa-angle-up');
	}).on('hidden.bs.collapse', function () {
		$(this).prev('a').find('i').removeClass('fa-angle-up').addClass('fa-angle-down');
	});
});
</script>
<?php
$footer_codes .= ob_get_clean();
