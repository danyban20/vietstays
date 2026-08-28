<?php
/** Overview dashboard cards. Expects $dashboard_stats, $completion, $apartment_id, $currency, $suggested_price, $current_daily, $listing_photo_count, $min_images. */
$next = gArrayItem( $dashboard_stats, 'next_check_in' );
?>
<div class="vv-manage2026-dashboard mb-4">
    <div class="row">
        <div class="col-md-3 col-6 mb-3">
            <div class="vv-manage2026-stat-card">
                <span class="vv-manage2026-stat-label"><?php vv_e( 'Listing completion' ); ?></span>
                <span class="vv-manage2026-stat-value"><?php echo intval( $completion['percent'] ); ?>%</span>
                <div class="progress mt-2" style="height:6px">
                    <div class="progress-bar bg-success" style="width:<?php echo intval( $completion['percent'] ); ?>%"></div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6 mb-3">
            <div class="vv-manage2026-stat-card">
                <span class="vv-manage2026-stat-label"><?php vv_e( 'Last 30 days' ); ?></span>
                <span class="vv-manage2026-stat-value"><?php echo intval( gArrayItem( $dashboard_stats, 'bookings_30' ) ); ?></span>
                <span class="vv-manage2026-stat-sub"><?php vv_e( 'bookings' ); ?> · <?php echo intval( gArrayItem( $dashboard_stats, 'occupancy_30' ) ); ?>% <?php vv_e( 'occupancy' ); ?></span>
            </div>
        </div>
        <div class="col-md-3 col-6 mb-3">
            <div class="vv-manage2026-stat-card">
                <span class="vv-manage2026-stat-label"><?php vv_e( '30-day revenue' ); ?></span>
                <span class="vv-manage2026-stat-value"><?php echo esc_html( vv_number_format( gArrayItem( $dashboard_stats, 'revenue_30' ), true ) ); ?></span>
                <span class="vv-manage2026-stat-sub"><?php echo esc_html( $currency ); ?></span>
            </div>
        </div>
        <div class="col-md-3 col-6 mb-3">
            <div class="vv-manage2026-stat-card">
                <span class="vv-manage2026-stat-label"><?php vv_e( 'Next check-in' ); ?></span>
                <?php if ( is_array( $next ) && gArrayItem( $next, 'check_in_date' ) ) { ?>
                    <span class="vv-manage2026-stat-value vv-manage2026-stat-value--sm"><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( gArrayItem( $next, 'check_in_date' ) ) ) ); ?></span>
                    <span class="vv-manage2026-stat-sub"><?php echo esc_html( trim( gArrayItem( $next, 'guest_name' ) . ' ' . gArrayItem( $next, 'guest_surname' ) ) ); ?></span>
                <?php } else { ?>
                    <span class="vv-manage2026-stat-value vv-manage2026-stat-value--sm text-muted">—</span>
                    <span class="vv-manage2026-stat-sub"><?php vv_e( 'No upcoming bookings' ); ?></span>
                <?php } ?>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-lg-6 mb-3">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong><?php vv_e( 'Content status' ); ?></strong>
                <span class="small text-muted"><?php echo intval( $completion['done'] ); ?>/<?php echo intval( $completion['total'] ); ?></span>
            </div>
            <div class="card-body py-2">
                <?php
                $checks = [
                    [ 'key' => 'photos', 'label' => vv__( 'Photos' ), 'tab' => 'images', 'detail' => $listing_photo_count . '/' . $min_images ],
                    [ 'key' => 'description', 'label' => vv__( 'Description' ), 'tab' => 'overview' ],
                    [ 'key' => 'pricing', 'label' => vv__( 'Pricing' ), 'tab' => 'pricing' ],
                    [ 'key' => 'house_rules', 'label' => vv__( 'House rules' ), 'tab' => 'facilities' ],
                    [ 'key' => 'facilities', 'label' => vv__( 'Facilities' ), 'tab' => 'facilities' ],
                    [ 'key' => 'check_in', 'label' => vv__( 'Check-in info' ), 'tab' => 'facilities' ],
                    [ 'key' => 'practical_info', 'label' => vv__( 'Practical info' ), 'tab' => 'facilities' ],
                ];
                foreach ( $checks as $check ) {
                    $ok = ! empty( $completion['items'][ $check['key'] ] );
                    $url = vv_admin_url( 'apartments/manage?id=' . $apartment_id . '&tab=' . $check['tab'] );
                    ?>
                    <div class="vv-manage2026-check-row">
                        <span class="<?php echo $ok ? 'text-success' : 'text-muted'; ?>"><?php echo $ok ? '✓' : '○'; ?></span>
                        <a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $check['label'] ); ?></a>
                        <?php if ( ! empty( $check['detail'] ) ) { ?>
                            <span class="text-muted small ml-auto"><?php echo esc_html( $check['detail'] ); ?></span>
                        <?php } ?>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
    <div class="col-lg-6 mb-3">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong><?php vv_e( 'Pricing snapshot' ); ?></strong>
                <a href="<?php echo esc_url( vv_admin_url( 'apartments/manage?id=' . $apartment_id . '&tab=pricing' ) ); ?>" class="btn btn-sm btn-outline-secondary"><?php vv_e( 'Edit pricing' ); ?></a>
            </div>
            <div class="card-body">
                <dl class="row mb-0 small">
                    <dt class="col-5"><?php vv_e( 'Daily rate' ); ?></dt>
                    <dd class="col-7"><?php echo esc_html( vv_number_format( $current_daily, true ) ); ?> <?php echo esc_html( $currency ); ?></dd>
                    <dt class="col-5"><?php vv_e( 'Suggested' ); ?></dt>
                    <dd class="col-7"><?php echo esc_html( vv_number_format( $suggested_price, true ) ); ?> <?php echo esc_html( $currency ); ?></dd>
                    <dt class="col-5"><?php vv_e( 'Standard' ); ?></dt>
                    <dd class="col-7"><?php
                    $std_row = gArrayItem( vv_apartments_quality_standards(), $quality_standard, [] );
                    echo esc_html( vv__( gArrayItem( $std_row, 'label', $quality_standard ) ) );
                    ?></dd>
                    <dt class="col-5"><?php vv_e( 'Upcoming (30d)' ); ?></dt>
                    <dd class="col-7"><?php echo intval( gArrayItem( $dashboard_stats, 'upcoming_count' ) ); ?> <?php vv_e( 'bookings' ); ?>
                        <?php if ( intval( gArrayItem( $dashboard_stats, 'external_30' ) ) > 0 ) { ?>
                            · <?php echo intval( gArrayItem( $dashboard_stats, 'external_30' ) ); ?> <?php vv_e( 'external' ); ?>
                        <?php } ?>
                    </dd>
                </dl>
            </div>
        </div>
    </div>
</div>
