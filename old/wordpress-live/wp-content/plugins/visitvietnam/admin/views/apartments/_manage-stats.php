<?php
/** Statistics tab — expects $dashboard_stats, $apartment_id, $image_count, $bookings, $currency. */
$ds = isset( $dashboard_stats ) && is_array( $dashboard_stats ) ? $dashboard_stats : [];

$month_bookings = is_array( $bookings ) ? count( $bookings ) : 0;
$days_in_month  = intval( date( 't' ) );
$booked_nights  = 0;
if ( is_array( $bookings ) ) {
	foreach ( $bookings as $booking ) {
		$ci = gArrayItem( $booking, 'check_in_date' );
		$co = gArrayItem( $booking, 'check_out_date' );
		if ( $ci && $co ) {
			$booked_nights += max( 0, ( strtotime( $co ) - strtotime( $ci ) ) / DAY_IN_SECONDS );
		}
	}
}
$occupancy_pct = $days_in_month > 0 ? min( 100, round( ( $booked_nights / $days_in_month ) * 100 ) ) : 0;

$bookings_30   = intval( gArrayItem( $ds, 'bookings_30' ) );
$external_30   = intval( gArrayItem( $ds, 'external_30' ) );
$platform_30   = max( 0, $bookings_30 );
$recent        = gArrayItem( $ds, 'recent_bookings', [] );
if ( ! is_array( $recent ) ) {
	$recent = [];
}
?>
<div class="vv-manage2026-dashboard mb-4">
    <p class="text-muted small mb-3"><?php vv_e( 'Last 30 days' ); ?></p>
    <div class="row">
        <div class="col-md-3 col-6 mb-3">
            <div class="vv-manage2026-stat-card">
                <span class="vv-manage2026-stat-label"><?php vv_e( 'Bookings' ); ?></span>
                <span class="vv-manage2026-stat-value"><?php echo $bookings_30; ?></span>
                <span class="vv-manage2026-stat-sub"><?php echo intval( gArrayItem( $ds, 'booked_nights_30' ) ); ?> <?php vv_e( 'nights booked' ); ?></span>
            </div>
        </div>
        <div class="col-md-3 col-6 mb-3">
            <div class="vv-manage2026-stat-card">
                <span class="vv-manage2026-stat-label"><?php vv_e( 'Occupancy' ); ?></span>
                <span class="vv-manage2026-stat-value"><?php echo intval( gArrayItem( $ds, 'occupancy_30' ) ); ?>%</span>
                <span class="vv-manage2026-stat-sub"><?php vv_e( 'rolling 30 days' ); ?></span>
            </div>
        </div>
        <div class="col-md-3 col-6 mb-3">
            <div class="vv-manage2026-stat-card">
                <span class="vv-manage2026-stat-label"><?php vv_e( 'Revenue' ); ?></span>
                <span class="vv-manage2026-stat-value vv-manage2026-stat-value--sm"><?php echo esc_html( vv_number_format( gArrayItem( $ds, 'revenue_30' ), true ) ); ?></span>
                <span class="vv-manage2026-stat-sub"><?php echo esc_html( $currency ); ?></span>
            </div>
        </div>
        <div class="col-md-3 col-6 mb-3">
            <div class="vv-manage2026-stat-card">
                <span class="vv-manage2026-stat-label"><?php vv_e( 'Booking mix' ); ?></span>
                <span class="vv-manage2026-stat-value vv-manage2026-stat-value--sm"><?php echo $platform_30; ?> / <?php echo $external_30; ?></span>
                <span class="vv-manage2026-stat-sub"><?php vv_e( 'Vietstays / external' ); ?></span>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-4 mb-3">
        <div class="card h-100">
            <div class="card-body">
                <h6 class="text-muted"><?php vv_e( 'All-time bookings' ); ?></h6>
                <p class="h3 mb-0"><?php echo intval( gArrayItem( $ds, 'total_bookings' ) ); ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card h-100">
            <div class="card-body">
                <h6 class="text-muted"><?php vv_e( 'All-time revenue' ); ?></h6>
                <p class="h3 mb-0"><?php echo esc_html( vv_number_format( gArrayItem( $ds, 'total_revenue' ), true ) ); ?></p>
                <p class="small text-muted mb-0"><?php echo esc_html( $currency ); ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card h-100">
            <div class="card-body">
                <h6 class="text-muted"><?php printf( esc_html( vv__( '%s occupancy' ) ), esc_html( date( 'F Y' ) ) ); ?></h6>
                <p class="h3 mb-0"><?php echo intval( $occupancy_pct ); ?>%</p>
                <p class="small text-muted mb-0"><?php printf( esc_html( vv__( '%d bookings this month' ) ), intval( $month_bookings ) ); ?></p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8 mb-3">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong><?php vv_e( 'Recent bookings' ); ?></strong>
                <a href="<?php echo esc_url( vv_admin_url( 'apartments/manage?id=' . $apartment_id . '&tab=availability' ) ); ?>" class="btn btn-sm btn-outline-secondary"><?php vv_e( 'Calendar' ); ?></a>
            </div>
            <?php if ( empty( $recent ) ) { ?>
                <div class="card-body">
                    <p class="text-muted small mb-0"><?php vv_e( 'No completed or past bookings yet.' ); ?></p>
                </div>
            <?php } else { ?>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th><?php vv_e( 'Guest' ); ?></th>
                                <th><?php vv_e( 'Check-in' ); ?></th>
                                <th><?php vv_e( 'Check-out' ); ?></th>
                                <th><?php vv_e( 'Total' ); ?></th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ( $recent as $booking ) { ?>
                                <tr>
                                    <td><?php echo esc_html( trim( gArrayItem( $booking, 'guest_name' ) . ' ' . gArrayItem( $booking, 'guest_surname' ) ) ); ?></td>
                                    <td><?php echo esc_html( gArrayItem( $booking, 'check_in_date' ) ); ?></td>
                                    <td><?php echo esc_html( gArrayItem( $booking, 'check_out_date' ) ); ?></td>
                                    <td><?php echo esc_html( vv_number_format( gArrayItem( $booking, 'total' ), true ) ); ?></td>
                                    <td class="text-right">
                                        <a href="<?php echo esc_url( vv_admin_url( 'booking/edit?id=' . intval( gArrayItem( $booking, 'ID' ) ) ) ); ?>" class="btn btn-link btn-sm p-0"><?php vv_e( 'Open' ); ?></a>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            <?php } ?>
        </div>
    </div>
    <div class="col-lg-4 mb-3">
        <div class="card h-100">
            <div class="card-body">
                <h6 class="text-muted"><?php vv_e( 'Listing photos' ); ?></h6>
                <p class="h3 mb-2"><?php echo intval( $image_count ); ?></p>
                <a href="<?php echo esc_url( vv_admin_url( 'apartments/manage?id=' . $apartment_id . '&tab=images' ) ); ?>" class="btn btn-sm btn-outline-secondary"><?php vv_e( 'Manage photos' ); ?></a>
            </div>
        </div>
    </div>
</div>
