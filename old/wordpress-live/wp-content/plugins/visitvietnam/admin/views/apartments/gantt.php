<?php
global $wpdb;

$filter = [];
if ( $logged_user_role !== 'administrator' ) {
	$filter['user_id'] = $logged_user_id;
}

$apartments = vv_get_apartments( array_merge( $filter, [ 'status' => 'all', 'per_page' => 'all' ] ) );
$highlight  = intval( GET_Request( 'apt' ) );

$month = GET_Request( 'month' );
if ( $month !== '' && strpos( $month, '-' ) !== false ) {
	list( $m, $y ) = explode( '-', $month );
	$month_num = intval( $m );
	$year_num  = intval( $y );
} else {
	$month_num = intval( date( 'n' ) );
	$year_num  = intval( date( 'Y' ) );
}

$days_in_month = intval( date( 't', mktime( 0, 0, 0, $month_num, 1, $year_num ) ) );
$start_date    = sprintf( '%04d-%02d-01', $year_num, $month_num );
$end_date      = sprintf( '%04d-%02d-%02d', $year_num, $month_num, $days_in_month );

$booking_filter = [ 'overlap_start' => $start_date, 'overlap_end' => $end_date, 'per_page' => 'all' ];
if ( $logged_user_role !== 'administrator' ) {
	$booking_filter['apartment_owner'] = $logged_user_id;
}
$bookings = $this->booking_class->get_bookings( $booking_filter );

$bookings_by_apt = [];
foreach ( $bookings as $booking ) {
	$aid = intval( gArrayItem( $booking, 'apartment_id' ) );
	if ( ! isset( $bookings_by_apt[ $aid ] ) ) {
		$bookings_by_apt[ $aid ] = [];
	}
	$bookings_by_apt[ $aid ][] = $booking;
}

$prev_m = $month_num - 1;
$prev_y = $year_num;
if ( $prev_m < 1 ) {
	$prev_m = 12;
	$prev_y--;
}
$next_m = $month_num + 1;
$next_y = $year_num;
if ( $next_m > 12 ) {
	$next_m = 1;
	$next_y++;
}

$month_names_en = [
	1  => 'January',
	2  => 'February',
	3  => 'March',
	4  => 'April',
	5  => 'May',
	6  => 'June',
	7  => 'July',
	8  => 'August',
	9  => 'September',
	10 => 'October',
	11 => 'November',
	12 => 'December',
];
$month_label = gArrayItem( $month_names_en, $month_num, '' ) . ' ' . $year_num;

$pg_title   = 'Portfolio calendar';
$meta_title = vv_admin_meta_title( $pg_title );
vv_show_page_title_bar( [
	'pg_title'  => $pg_title,
	'back_link' => vv_admin_url( 'apartments' ),
	'back_txt'  => 'Back to apartments',
] );
?>
<p class="text-muted mb-3"><?php echo esc_html( vv__( 'Gantt-style view of bookings and blocked dates across all apartments.' ) ); ?></p>

<div class="mb-3">
	<a href="<?php echo esc_url( vv_admin_url( 'apartments/gantt?month=' . $prev_m . '-' . $prev_y ) ); ?>" class="btn btn-sm btn-outline-secondary">&laquo; <?php echo esc_html( vv__( 'Previous' ) ); ?></a>
	<strong class="mx-2"><?php echo esc_html( $month_label ); ?></strong>
	<a href="<?php echo esc_url( vv_admin_url( 'apartments/gantt?month=' . $next_m . '-' . $next_y ) ); ?>" class="btn btn-sm btn-outline-secondary"><?php echo esc_html( vv__( 'Next' ) ); ?> &raquo;</a>
</div>

<div class="table-responsive">
	<table class="table table-bordered table-sm" style="font-size:11px;">
		<thead>
			<tr>
				<th style="min-width:180px;position:sticky;left:0;background:#fff;z-index:2;"><?php echo esc_html( vv__( 'Apartment' ) ); ?></th>
				<?php for ( $d = 1; $d <= $days_in_month; $d++ ) { ?>
					<th class="text-center" style="min-width:28px;"><?php echo intval( $d ); ?></th>
				<?php } ?>
			</tr>
		</thead>
		<tbody>
			<?php if ( count( $apartments ) === 0 ) { ?>
				<tr>
					<td colspan="<?php echo intval( $days_in_month + 1 ); ?>" class="text-center text-muted py-4"><?php echo esc_html( vv__( 'No apartments found.' ) ); ?></td>
				</tr>
			<?php } ?>
			<?php foreach ( $apartments as $apartment ) {
				$aid = intval( $apartment['ID'] );
				$blocked = class_exists( 'vvApartmentPlatform' ) ? vvApartmentPlatform::get_blocked_dates( $aid ) : [];
				$row_class = ( $aid === $highlight ) ? 'table-info' : '';
				if ( $logged_user_role === 'partner' || $logged_user_role === 'administrator' ) {
					$manage_url = vv_admin_url( 'apartments/manage?id=' . $aid . '&tab=pricing' );
				} else {
					$manage_url = vv_admin_url( 'apartments/edit/?id=' . $aid );
				}
				?>
				<tr class="<?php echo esc_attr( $row_class ); ?>">
					<td style="position:sticky;left:0;background:#fff;z-index:1;">
						<a href="<?php echo esc_url( $manage_url ); ?>" title="<?php echo esc_attr( vv__( 'Manage pricing & blocked dates' ) ); ?>"><?php echo esc_html( stripslashes( $apartment['name'] ) ); ?></a>
					</td>
					<?php for ( $d = 1; $d <= $days_in_month; $d++ ) {
						$date_str = sprintf( '%04d-%02d-%02d', $year_num, $month_num, $d );
						$cell     = '';
						$bg       = '';
						if ( in_array( $date_str, $blocked, true ) ) {
							$bg   = '#ffc107';
							$cell = 'B';
						}
						if ( isset( $bookings_by_apt[ $aid ] ) ) {
							foreach ( $bookings_by_apt[ $aid ] as $booking ) {
								$ci = gArrayItem( $booking, 'check_in_date' );
								$co = gArrayItem( $booking, 'check_out_date' );
								if ( $date_str >= $ci && $date_str < $co ) {
									$bg   = '#28a745';
									$cell = '•';
									break;
								}
							}
						}
						?>
						<td class="text-center" style="background:<?php echo esc_attr( $bg ); ?>;padding:2px;"><?php echo esc_html( $cell ); ?></td>
					<?php } ?>
				</tr>
			<?php } ?>
		</tbody>
	</table>
</div>
<p class="small text-muted mt-2">
	<span style="background:#28a745;color:#fff;padding:0 6px;">•</span> <?php echo esc_html( vv__( 'Booked' ) ); ?>
	<span style="background:#ffc107;padding:0 6px;" class="ml-2">B</span> <?php echo esc_html( vv__( 'Blocked' ) ); ?>
	<span class="ml-2"><?php echo esc_html( vv__( 'Click an apartment name to manage blocked dates.' ) ); ?></span>
</p>
