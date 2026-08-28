<?php
$booking_class   = new vvBookings;
$apartment_class = new vvApartments;
$user_id         = get_current_user_id();
$filter_status   = sanitize_key( GET_Request( 'status' ) );
$all_bookings    = $booking_class->get_bookings( [ 'user_id' => $user_id ] );
$logged_user     = wp_get_current_user();
$today           = date( 'Y-m-d' );

$active_conversion = class_exists( 'vvConversions' ) ? vvConversions::get_active_conversion_for_user( $user_id ) : [];

$bookings = $all_bookings;
if ( $filter_status === 'upcoming' ) {
	$bookings = array_filter(
		$all_bookings,
		function ( $booking ) use ( $today ) {
			return gArrayItem( $booking, 'check_out_date' ) >= $today && ! in_array( gArrayItem( $booking, 'status' ), [ 'cancelled' ], true );
		}
	);
} elseif ( $filter_status === 'past' ) {
	$bookings = array_filter(
		$all_bookings,
		function ( $booking ) use ( $today ) {
			return gArrayItem( $booking, 'check_out_date' ) < $today || in_array( gArrayItem( $booking, 'status' ), [ 'cancelled' ], true );
		}
	);
}

$total_spent = 0.0;
foreach ( $all_bookings as $booking ) {
	if ( in_array( gArrayItem( $booking, 'status' ), [ 'paid', 'confirmed', 'completed' ], true ) ) {
		$total_spent += floatval( gArrayItem( $booking, 'total' ) );
	}
}

$list_url = vv_users_url();
?>
<div class="portal-header">
	<div>
		<h1>My Stays</h1>
		<p class="mb-0 text-muted">Welcome, <?php echo esc_html( $logged_user->display_name ); ?></p>
	</div>
	<div>
		<a class="btn-outline" href="<?php echo esc_url( vv_users_url() . '?action=guest_logout' ); ?>">Sign out</a>
	</div>
</div>

<?php if ( ! empty( $active_conversion['ID'] ) ) { ?>
	<div class="card-booking" style="border-color:#013735;">
		<p class="mb-2"><strong>Your <?php echo intval( gArrayItem( $active_conversion, 'discount_pct', vvConversions::DISCOUNT_PCT ) ); ?>% rebooking discount is active</strong></p>
		<p class="mb-2 small">Code: <code><?php echo esc_html( gArrayItem( $active_conversion, 'promo_code' ) ); ?></code></p>
		<a href="<?php echo esc_url( vv_users_url( 'conversion/stay' ) ); ?>" class="btn btn-primary btn-sm">Open stay portal</a>
	</div>
<?php } ?>

<div class="row mb-4">
	<div class="col-md-4 mb-2">
		<div class="card-booking mb-0">
			<p class="text-muted small mb-1">Total bookings</p>
			<p class="h4 mb-0"><?php echo intval( count( $all_bookings ) ); ?></p>
		</div>
	</div>
	<div class="col-md-4 mb-2">
		<div class="card-booking mb-0">
			<p class="text-muted small mb-1">Total spent</p>
			<p class="h4 mb-0"><?php echo esc_html( vv_number_format( $total_spent, true ) ); ?></p>
		</div>
	</div>
	<div class="col-md-4 mb-2">
		<div class="card-booking mb-0">
			<p class="text-muted small mb-1">Filter</p>
			<div class="btn-group btn-group-sm">
				<a href="<?php echo esc_url( $list_url ); ?>" class="btn btn-outline-secondary<?php echo $filter_status === '' ? ' active' : ''; ?>">All</a>
				<a href="<?php echo esc_url( $list_url . '?status=upcoming' ); ?>" class="btn btn-outline-secondary<?php echo $filter_status === 'upcoming' ? ' active' : ''; ?>">Upcoming</a>
				<a href="<?php echo esc_url( $list_url . '?status=past' ); ?>" class="btn btn-outline-secondary<?php echo $filter_status === 'past' ? ' active' : ''; ?>">Past</a>
			</div>
		</div>
	</div>
</div>

<?php if ( count( $bookings ) === 0 ) { ?>
	<div class="card-booking">
		<p class="mb-0">No bookings in this view yet. When you complete a reservation on Vietstays, it will appear here.</p>
	</div>
<?php } else { ?>
	<?php foreach ( $bookings as $booking ) {
		$apartment   = $apartment_class->get_apartment( gArrayItem( $booking, 'apartment_id' ) );
		$status      = gArrayItem( $booking, 'status' );
		$status_cls  = in_array( $status, [ 'paid', 'confirmed', 'completed' ], true ) ? 'status-paid' : '';
		$detail_url  = vv_get_booking_link( $booking );
		?>
		<div class="card-booking">
			<div class="row align-items-center">
				<div class="col-md-8">
					<h3 class="h5 mb-2">
						<a href="<?php echo esc_url( $detail_url ); ?>"><?php echo esc_html( gArrayItem( $apartment, 'display_name', 'Apartment' ) ); ?></a>
					</h3>
					<p class="mb-1"><strong>Booking #:</strong> <?php echo esc_html( vv_get_booking_num( $booking ) ); ?></p>
					<p class="mb-1">
						<strong>Dates:</strong>
						<?php echo esc_html( vv_get_date_range_text( gArrayItem( $booking, 'check_in_date' ), gArrayItem( $booking, 'check_out_date' ) ) ); ?>
					</p>
					<p class="mb-1"><strong>Total:</strong> <?php echo esc_html( vv_number_format( gArrayItem( $booking, 'total' ), true ) ); ?></p>
					<p class="mb-0 <?php echo esc_attr( $status_cls ); ?>">
						<strong>Status:</strong> <?php echo esc_html( vv_get_status_name_text( $status ) ); ?>
					</p>
				</div>
				<div class="col-md-4 text-md-right mt-3 mt-md-0">
					<a class="btn btn-primary" href="<?php echo esc_url( $detail_url ); ?>">View stay</a>
				</div>
			</div>
		</div>
	<?php } ?>
<?php } ?>
