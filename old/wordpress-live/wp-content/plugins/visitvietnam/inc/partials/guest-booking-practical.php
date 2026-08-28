<?php
/**
 * Practical information blocks for guest booking detail.
 *
 * Expects: $booking, $apartment, $booking_class, $show_sensitive (bool)
 */
if ( ! isset( $booking ) || ! isset( $apartment ) || ! isset( $booking_class ) ) {
	return;
}

$show_sensitive = ! empty( $show_sensitive );
$booking_items  = $booking_class->get_booking_items( gArrayItem( $booking, 'ID' ) );

$has_airport_pickup = false;
$has_scooter_rental = false;

foreach ( $booking_items as $item ) {
	if ( gArrayItem( $item, 'code' ) === 'airport_pickup' ) {
		$has_airport_pickup = true;
	}
	if ( gArrayItem( $item, 'code' ) === 'scooter_rental' ) {
		$has_scooter_rental = true;
	}
}
?>

<?php if ( $show_sensitive ) { ?>
	<?php
	$wifi_name     = gArrayItem( $apartment, 'wifi_name' );
	$wifi_password = gArrayItem( $apartment, 'wifi_password' );
	$door_code     = gArrayItem( $apartment, 'door_code' );
	if ( $wifi_name !== '' || $wifi_password !== '' || $door_code !== '' ) {
		?>
		<div class="block">
			<h4>Access details</h4>
			<div class="collapse show" id="accessdetails">
				<?php if ( $wifi_name !== '' ) { ?>
					<p><strong>WiFi network:</strong> <?php echo esc_html( $wifi_name ); ?></p>
				<?php } ?>
				<?php if ( $wifi_password !== '' ) { ?>
					<p><strong>WiFi password:</strong> <?php echo esc_html( $wifi_password ); ?></p>
				<?php } ?>
				<?php if ( $door_code !== '' ) { ?>
					<p><strong>Door / entry code:</strong> <?php echo esc_html( $door_code ); ?></p>
				<?php } ?>
			</div>
		</div>
		<?php
	}
	?>
<?php } ?>

<?php if ( gArrayItem( $apartment, 'checkin_without_host' ) == 1 ) { ?>
	<div class="block">
		<h4>Check-in without host</h4>
		<a data-toggle="collapse" href="#checkinwithouthost" role="button" aria-expanded="false"><i class="fas fa-angle-down"></i></a>
		<div class="collapse" id="checkinwithouthost">
			<p>Free of charge cancellation 2 days before arrival</p>
		</div>
	</div>
<?php } ?>

<?php if ( gArrayItem( $apartment, 'flexible_reservation' ) == 1 ) { ?>
	<div class="block">
		<h4>Flexible reservation</h4>
		<a data-toggle="collapse" href="#flexiblereservation" role="button" aria-expanded="false"><i class="fas fa-angle-down"></i></a>
		<div class="collapse" id="flexiblereservation">
			<p>Free of charge cancellation 2 days before arrival</p>
		</div>
	</div>
<?php } ?>

<?php if ( gArrayItem( $apartment, 'airport_pickup' ) == 1 && $has_airport_pickup ) { ?>
	<div class="block">
		<h4>Air Port Pick Up</h4>
		<a data-toggle="collapse" href="#airportpickup" role="button" aria-expanded="false"><i class="fas fa-angle-down"></i></a>
		<div class="collapse" id="airportpickup">
			<p>Pick up at airport has been requested.</p>
		</div>
	</div>
<?php } ?>

<?php if ( gArrayItem( $apartment, 'scooter_rental' ) == 1 && $has_scooter_rental ) { ?>
	<div class="block">
		<h4>Scooters Rental</h4>
		<a data-toggle="collapse" href="#scooterrental" role="button" aria-expanded="false"><i class="fas fa-angle-down"></i></a>
		<div class="collapse" id="scooterrental">
			<p>Scooter rental is available.</p>
		</div>
	</div>
<?php } ?>

<div class="block">
	<h4>House Rules</h4>
	<a data-toggle="collapse" href="#houserules" role="button" aria-expanded="false"><i class="fas fa-angle-down"></i></a>
	<div class="collapse" id="houserules">
		<?php
		$check_in_time1 = gArrayItem( $apartment, 'check_in_time1' );
		$check_in_time2 = gArrayItem( $apartment, 'check_in_time2' );
		if ( $check_in_time1 != $check_in_time2 ) {
			echo '<p>– Check-in is ' . esc_html( $check_in_time1 ) . ' to ' . esc_html( $check_in_time2 ) . '</p>';
		} else {
			echo '<p>– Check-in is ' . esc_html( $check_in_time1 ) . '</p>';
		}

		$check_out_time = gArrayItem( $apartment, 'check_out_time' );
		echo '<p>– Check-out before ' . esc_html( $check_out_time ) . '</p>';
		echo '<p>– Maximum ' . esc_html( gArrayItem( $apartment, 'max_guests' ) ) . ' guests</p>';
		?>
	</div>
</div>

<div class="block">
	<h4>Security and property</h4>
	<a data-toggle="collapse" href="#security" role="button" aria-expanded="false"><i class="fas fa-angle-down"></i></a>
	<div class="collapse" id="security">
		<?php
		$security_features = vv_get_security_features();
		if ( is_array( $security_features ) ) {
			$apartment_security_features = json_decode( gArrayItem( $apartment, 'security_features' ), true );
			if ( is_array( $apartment_security_features ) ) {
				foreach ( $security_features as $security_feature ) {
					if ( in_array( $security_feature['security_feature_id'], $apartment_security_features, true ) ) {
						echo '<p>- ' . esc_html( $security_feature['name'] ) . '</p>';
					}
				}
			}
		}
		?>
	</div>
</div>
