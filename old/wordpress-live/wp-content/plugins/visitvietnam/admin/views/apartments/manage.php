<?php
if ( ! class_exists( 'vvApartmentsWizard' ) ) {
	echo '<div class="alert alert-danger">' . esc_html( vv__( 'Module not available.' ) ) . '</div>';
	return;
}

$apartment_id = intval( GET_Request( 'id' ) );
if ( $apartment_id <= 0 ) {
	echo '<div class="alert alert-danger">' . esc_html( vv__( 'Apartment not found.' ) ) . '</div>';
	return;
}

$apartment = vv_get_apartment( $apartment_id );
if ( ! vvApartmentsWizard::can_access_apartment( $apartment ) ) {
	echo '<div class="alert alert-danger">' . esc_html( vv__( 'Permission denied.' ) ) . '</div>';
	return;
}

$tab            = sanitize_key( GET_Request( 'tab' ) );
if ( $tab === '' ) {
	$tab = 'overview';
}

$status_key   = gArrayItem( $apartment, 'status', 'pending' );
$status_label = gArrayItem( vvApartments::status_labels(), $status_key, $status_key );
$badge_class  = 'vv-manage2026-badge--' . ( $status_key === 'active' ? 'active' : ( $status_key === 'pending' ? 'pending' : 'draft' ) );

$building_id = intval( gArrayItem( $apartment, 'building_id' ) );
$building    = $building_id > 0 ? vv_get_neighbourhood( $building_id ) : [];
$district_name = vv_get_district_display_name( intval( gArrayItem( $apartment, 'district' ) ) );

$images      = vv_get_apartment_images( $apartment );
$image_count = count( $images );
$listing_photo_count = function_exists( 'vv_count_listing_photos' ) ? vv_count_listing_photos( $apartment ) : $image_count;
$min_images  = 10;
if ( class_exists( 'vvApartmentPlatform' ) ) {
	$min_images = intval( vvApartmentPlatform::get_settings()['min_images_publish'] );
}

$quality_standards = vv_apartments_quality_standards();
$quality_standard  = gArrayItem( $apartment, 'quality_standard', 'above_average' );
$suggested_price   = floatval( gArrayItem( $apartment, 'price_daily' ) );
if ( $suggested_price <= 0 && class_exists( 'vvApartmentPlatform' ) ) {
	$suggested_price = vvApartmentPlatform::suggest_daily_price(
		gArrayItem( $apartment, 'apartment_type', '2BR' ),
		gArrayItem( $apartment, 'price_level', 'normal' ),
		$building_id
	);
}

$pricing         = json_decode( gArrayItem( $apartment, 'pricing' ), true );
if ( ! is_array( $pricing ) ) {
	$pricing = [];
}
$owner_id = intval( gArrayItem( $apartment, 'user_id' ) );
$currency = get_user_meta( $owner_id, 'vv_host_currency', true );
if ( $currency === '' ) {
	$currency = 'USD';
}

$blocked_dates = class_exists( 'vvApartmentPlatform' ) ? vvApartmentPlatform::get_blocked_dates( $apartment_id ) : [];
$month_num     = intval( date( 'n' ) );
$year_num      = intval( date( 'Y' ) );
$days_in_month = intval( date( 't' ) );

$bookings = [];
if ( isset( $this->booking_class ) ) {
	$bookings = $this->booking_class->get_bookings( [
		'apartment_ids'  => [ $apartment_id ],
		'overlap_start'  => sprintf( '%04d-%02d-01', $year_num, $month_num ),
		'overlap_end'    => sprintf( '%04d-%02d-%02d', $year_num, $month_num, $days_in_month ),
	] );
}

$i18n_locales = class_exists( 'vvI18n' ) ? vvI18n::supported_locales() : [ 'no' => 'NO', 'en' => 'EN', 'vi' => 'VI' ];
$desc_ok      = [ 'no' => trim( gArrayItem( $apartment, 'about_this_short' ) ) !== '', 'en' => false, 'vi' => false ];
if ( class_exists( 'vvI18n' ) ) {
	foreach ( array_keys( $i18n_locales ) as $loc ) {
		$row = vvI18n::get_apartment_i18n( $apartment_id, $loc );
		$desc_ok[ $loc ] = trim( gArrayItem( $row, 'about_this_short' ) ) !== '' || trim( gArrayItem( $row, 'description' ) ) !== '';
	}
}

$completion       = vvApartmentsWizard::get_listing_completion( $apartment );
$rejection_reason = trim( gArrayItem( $apartment, 'rejection_reason' ) );
$admin_review_note = trim( gArrayItem( $apartment, 'admin_review_note' ) );

$apartment_type   = gArrayItem( $apartment, 'apartment_type', '2BR' );
$price_limits     = class_exists( 'vvApartmentPlatform' ) ? vvApartmentPlatform::get_price_limits( $apartment_type ) : [ 'min' => 0, 'max' => 0 ];
$default_cleaning = class_exists( 'vvApartmentPlatform' ) ? vvApartmentPlatform::default_cleaning_fee( $apartment_type ) : 0;
$current_daily    = floatval( gArrayItem( $apartment, 'price_daily' ) );
$price_is_custom  = $current_daily > 0 && abs( $current_daily - $suggested_price ) > 0.01;
$building_has_matrix = false;
if ( $building_id > 0 && class_exists( 'vvApartmentPlatform' ) ) {
	$bm = vvApartmentPlatform::get_building_price_matrix( $building_id );
	$mk = vvApartmentPlatform::apartment_type_to_matrix_key( $apartment_type );
	$building_has_matrix = ! empty( $bm[ $mk ] );
}

$booking_class_ref = isset( $this->booking_class ) ? $this->booking_class : null;
$dashboard_stats   = vvApartmentsWizard::get_apartment_dashboard_stats( $apartment_id, $booking_class_ref );

ob_start();
?>
<link rel="stylesheet" href="<?php echo esc_url( vv_plugins_url() . 'admin/css/apartments-wizard.css?v=2026mock7' ); ?>" media="all">
<?php
$header_codes .= ob_get_clean();

$is_admin  = vv_is_admin_role();
$seasonal  = json_decode( gArrayItem( $apartment, 'seasonal_pricing' ), true );
if ( ! is_array( $seasonal ) ) {
	$seasonal = [];
}

$approve_url = vv_admin_url( '?action=vv_approve_apartment&id=' . $apartment_id );
$reject_url  = vv_admin_url( '?action=vv_reject_apartment&id=' . $apartment_id );
$approve_confirm = esc_js( vv__( 'Publish this apartment? It will become visible in search.' ) );
$reject_confirm  = esc_js( vv__( 'Reject this listing and return it to draft?' ) );
?>
<div class="vv-manage2026">
    <div class="vv-manage2026-header">
        <div>
            <h1><?php echo esc_html( stripslashes( gArrayItem( $apartment, 'name' ) ) ); ?></h1>
            <span class="vv-manage2026-badge <?php echo esc_attr( $badge_class ); ?>"><?php echo esc_html( vv__( $status_label ) ); ?></span>
        </div>
        <div>
            <a href="<?php echo esc_url( vv_admin_url( 'apartments/edit-old/?id=' . $apartment_id ) ); ?>" class="btn btn-sm btn-outline-secondary"><?php vv_e( 'Legacy editor' ); ?></a>
            <a href="<?php echo esc_url( vv_admin_url( 'apartments' ) ); ?>" class="btn btn-sm btn-secondary"><?php vv_e( 'All apartments' ); ?></a>
        </div>
    </div>

    <ul class="nav nav-tabs vv-manage2026-tabs mb-4">
        <li class="nav-item"><a class="nav-link<?php echo $tab === 'overview' ? ' active' : ''; ?>" href="<?php echo esc_url( vv_admin_url( 'apartments/manage?id=' . $apartment_id . '&tab=overview' ) ); ?>"><?php vv_e( 'Overview' ); ?></a></li>
        <li class="nav-item"><a class="nav-link<?php echo $tab === 'pricing' ? ' active' : ''; ?>" href="<?php echo esc_url( vv_admin_url( 'apartments/manage?id=' . $apartment_id . '&tab=pricing' ) ); ?>"><?php vv_e( 'Pricing' ); ?></a></li>
        <li class="nav-item"><a class="nav-link<?php echo $tab === 'availability' ? ' active' : ''; ?>" href="<?php echo esc_url( vv_admin_url( 'apartments/manage?id=' . $apartment_id . '&tab=availability' ) ); ?>"><?php vv_e( 'Availability' ); ?></a></li>
        <li class="nav-item"><a class="nav-link<?php echo $tab === 'images' ? ' active' : ''; ?>" href="<?php echo esc_url( vv_admin_url( 'apartments/manage?id=' . $apartment_id . '&tab=images' ) ); ?>"><?php vv_e( 'Photos' ); ?></a></li>
        <li class="nav-item"><a class="nav-link<?php echo $tab === 'facilities' ? ' active' : ''; ?>" href="<?php echo esc_url( vv_admin_url( 'apartments/manage?id=' . $apartment_id . '&tab=facilities' ) ); ?>"><?php vv_e( 'Facilities & rules' ); ?></a></li>
        <li class="nav-item"><a class="nav-link<?php echo $tab === 'stats' ? ' active' : ''; ?>" href="<?php echo esc_url( vv_admin_url( 'apartments/manage?id=' . $apartment_id . '&tab=stats' ) ); ?>"><?php vv_e( 'Statistics' ); ?></a></li>
    </ul>

    <?php if ( $is_admin && $status_key === 'pending' ) { ?>
    <div class="vv-manage2026-approval-bar">
        <div>
            <strong><?php vv_e( 'Awaiting publish approval' ); ?></strong>
            <span class="text-muted small d-block"><?php vv_e( 'Review pricing, photos, and details before publishing.' ); ?></span>
        </div>
        <div>
            <a href="<?php echo esc_url( $approve_url ); ?>" class="btn btn-sm btn-success mr-1" onclick="return confirm('<?php echo $approve_confirm; ?>')"><?php vv_e( 'Approve & publish' ); ?></a>
            <button type="button" class="btn btn-sm btn-outline-danger" data-toggle="modal" data-target="#manage2026RejectModal"><?php vv_e( 'Reject' ); ?></button>
        </div>
    </div>
    <?php } ?>

    <?php if ( $rejection_reason !== '' && $status_key === 'draft' && ! $is_admin ) { ?>
    <div class="alert alert-warning vv-manage2026-rejection-notice">
        <strong><?php vv_e( 'Changes requested' ); ?></strong>
        <p class="mb-0 small"><?php echo nl2br( esc_html( $rejection_reason ) ); ?></p>
    </div>
    <?php } elseif ( $is_admin && $admin_review_note !== '' ) { ?>
    <div class="alert alert-secondary vv-manage2026-rejection-notice small">
        <strong><?php vv_e( 'Internal review note' ); ?></strong>
        <p class="mb-0"><?php echo nl2br( esc_html( $admin_review_note ) ); ?></p>
    </div>
    <?php } ?>

    <?php if ( $tab === 'overview' ) { ?>
    <?php include __DIR__ . '/_manage-overview-dashboard.php'; ?>
    <form method="post">
        <input type="hidden" name="apartment_id" value="<?php echo intval( $apartment_id ); ?>">
        <input type="hidden" name="manage_tab" value="overview">

        <div class="row">
            <div class="col-lg-8">
                <?php
                $overview_map = vvApartmentsWizard::get_calendar_day_map( $apartment_id, $year_num, $month_num, $booking_class_ref );
                ?>
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <strong><?php vv_e( 'Availability' ); ?> — <?php echo esc_html( date( 'F Y' ) ); ?></strong>
                        <a href="<?php echo esc_url( vv_admin_url( 'apartments/manage?id=' . $apartment_id . '&tab=availability' ) ); ?>" class="btn btn-sm btn-outline-secondary"><?php vv_e( 'Manage calendar' ); ?></a>
                    </div>
                    <div class="card-body">
                        <table class="vv-manage2026-mini-cal table table-bordered table-sm">
                            <thead><tr><?php foreach ( [ 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa', 'Su' ] as $d ) { ?><th><?php echo esc_html( $d ); ?></th><?php } ?></tr></thead>
                            <tbody><tr>
                            <?php
                            $first_dow = intval( date( 'N', mktime( 0, 0, 0, $month_num, 1, $year_num ) ) );
                            for ( $p = 1; $p < $first_dow; $p++ ) {
                                echo '<td></td>';
                            }
                            for ( $d = 1; $d <= $days_in_month; $d++ ) {
                                $date_str = sprintf( '%04d-%02d-%02d', $year_num, $month_num, $d );
                                $cls      = gArrayItem( $overview_map, $date_str, '' );
                                echo '<td class="' . esc_attr( $cls ) . '">' . intval( $d ) . '</td>';
                                if ( ( $first_dow + $d - 1 ) % 7 === 0 && $d < $days_in_month ) {
                                    echo '</tr><tr>';
                                }
                            }
                            ?>
                            </tr></tbody>
                        </table>
                        <p class="small text-muted mb-0"><span class="booked px-1">■</span> <?php vv_e( 'Booked' ); ?> &nbsp; <span class="blocked px-1">■</span> <?php vv_e( 'Blocked' ); ?> &nbsp; <span class="external px-1">■</span> <?php vv_e( 'External' ); ?></p>
                    </div>
                </div>

                <?php if ( ! empty( $dashboard_stats['upcoming_bookings'] ) ) { ?>
                <div class="card mb-4">
                    <div class="card-header"><strong><?php vv_e( 'Upcoming bookings' ); ?></strong></div>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <thead><tr><th><?php vv_e( 'Guest' ); ?></th><th><?php vv_e( 'Check-in' ); ?></th><th><?php vv_e( 'Check-out' ); ?></th><th></th></tr></thead>
                            <tbody>
                                <?php foreach ( $dashboard_stats['upcoming_bookings'] as $ub ) { ?>
                                    <tr>
                                        <td><?php echo esc_html( trim( gArrayItem( $ub, 'guest_name' ) . ' ' . gArrayItem( $ub, 'guest_surname' ) ) ); ?></td>
                                        <td><?php echo esc_html( gArrayItem( $ub, 'check_in_date' ) ); ?></td>
                                        <td><?php echo esc_html( gArrayItem( $ub, 'check_out_date' ) ); ?></td>
                                        <td class="text-right"><a href="<?php echo esc_url( vv_admin_url( 'booking/edit?id=' . intval( gArrayItem( $ub, 'ID' ) ) ) ); ?>" class="btn btn-link btn-sm p-0"><?php vv_e( 'Open' ); ?></a></td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php } ?>
            </div>

            <div class="col-lg-4">
                <div class="vv-manage2026-sidebar">
                    <dl>
                        <dt><?php vv_e( 'Status' ); ?></dt>
                        <dd>
                            <select name="status" class="form-control form-control-sm">
                                <option value="pending" <?php selected( $status_key, 'pending' ); ?>><?php vv_e( 'Pending approval' ); ?></option>
                                <option value="draft" <?php selected( $status_key, 'draft' ); ?>><?php vv_e( 'Deactivated / hidden' ); ?></option>
                                <?php if ( $is_admin ) { ?>
                                <option value="active" <?php selected( $status_key, 'active' ); ?>><?php vv_e( 'Published' ); ?></option>
                                <?php } ?>
                            </select>
                        </dd>
                        <dt><?php vv_e( 'Visibility' ); ?></dt>
                        <dd><?php echo $status_key === 'active' ? esc_html( vv__( 'Visible in search' ) ) : esc_html( vv__( 'Hidden (waiting)' ) ); ?></dd>
                        <dt><?php vv_e( 'Created' ); ?></dt>
                        <dd><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( gArrayItem( $apartment, 'dateadded' ) ) ) ); ?></dd>
                    </dl>
                    <?php if ( $status_key !== 'active' ) { ?>
                    <div class="alert alert-warning small mt-3 mb-0"><?php vv_e( 'This apartment is not published yet.' ); ?></div>
                    <?php } ?>
                    <?php if ( ! $is_admin && $status_key !== 'active' && $status_key !== 'pending' ) { ?>
                    <button type="submit" name="vv_action" value="vv_submit_apartment_wizard" class="btn btn-success btn-sm btn-block mt-3"><?php vv_e( 'Submit for approval' ); ?></button>
                    <?php } elseif ( ! $is_admin && $status_key === 'pending' ) { ?>
                    <p class="small text-muted mt-3 mb-0"><?php vv_e( 'Waiting for Vietstays to review and publish.' ); ?></p>
                    <?php } else { ?>
                    <button type="submit" name="vv_action" value="vv_save_apartment_wizard_manage" class="btn btn-primary btn-sm btn-block mt-3"><?php vv_e( 'Save status' ); ?></button>
                    <?php } ?>
                </div>
            </div>
        </div>
    </form>
    <?php } elseif ( $tab === 'pricing' ) { ?>
    <form method="post">
        <input type="hidden" name="apartment_id" value="<?php echo intval( $apartment_id ); ?>">
        <input type="hidden" name="manage_tab" value="pricing">

        <div class="row">
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header"><strong><?php vv_e( 'Price administration' ); ?></strong></div>
                    <div class="card-body">
                        <div class="vv-manage2026-pricing-suggest mb-3">
                            <div class="d-flex flex-wrap align-items-center justify-content-between">
                                <div>
                                    <p class="text-muted small mb-1"><?php vv_e( 'Suggested daily rate' ); ?><?php echo $building_has_matrix ? ' · ' . esc_html( vv__( 'from building matrix' ) ) : ''; ?></p>
                                    <div class="vv-manage2026-ai-price" id="manage2026SuggestedPrice"><?php echo esc_html( vv_number_format( $suggested_price, true ) ); ?> <small class="text-muted">/ <?php vv_e( 'night' ); ?></small></div>
                                </div>
                                <div class="mt-2 mt-md-0">
                                    <?php if ( $price_is_custom ) { ?>
                                        <span class="badge badge-info mr-1"><?php vv_e( 'Custom price' ); ?></span>
                                    <?php } else { ?>
                                        <span class="badge badge-secondary mr-1"><?php vv_e( 'Using suggested' ); ?></span>
                                    <?php } ?>
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="manage2026UseSuggested"><?php vv_e( 'Use suggested' ); ?></button>
                                </div>
                            </div>
                            <?php if ( floatval( $price_limits['min'] ) > 0 || floatval( $price_limits['max'] ) > 0 ) { ?>
                                <p class="small text-muted mb-0 mt-2"><?php printf( esc_html( vv__( 'Platform range for %s: %s – %s %s' ) ), esc_html( $apartment_type ), esc_html( vv_number_format( $price_limits['min'], true ) ), esc_html( vv_number_format( $price_limits['max'], true ) ), esc_html( $currency ) ); ?></p>
                            <?php } ?>
                        </div>

                        <label class="d-block small font-weight-bold mb-2"><?php vv_e( 'Apartment standard' ); ?></label>
                        <div class="vv-manage2026-level-pills mb-3">
                            <?php foreach ( $quality_standards as $key => $row ) { ?>
                                <label>
                                    <input type="radio" name="quality_standard" value="<?php echo esc_attr( $key ); ?>" <?php checked( $quality_standard, $key ); ?>>
                                    <span><?php echo esc_html( vv__( $row['label'] ) ); ?></span>
                                </label>
                            <?php } ?>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label><?php vv_e( 'Daily price' ); ?> (<?php echo esc_html( $currency ); ?>)</label>
                                <input type="text" name="price_daily" id="manage2026PriceDaily" class="form-control form-control-sm" value="<?php echo esc_attr( vv_number_format( gArrayItem( $apartment, 'price_daily' ) ) ); ?>" data-suggested="<?php echo esc_attr( $suggested_price ); ?>">
                            </div>
                            <div class="col-md-6 form-group">
                                <label><?php vv_e( 'Pricing model' ); ?></label>
                                <select name="pricing_model" id="manage2026PricingModel" class="form-control form-control-sm">
                                    <option value="fixed" <?php selected( gArrayItem( $apartment, 'pricing_model', 'fixed' ), 'fixed' ); ?>><?php vv_e( 'Fixed price' ); ?></option>
                                    <option value="seasonal" <?php selected( gArrayItem( $apartment, 'pricing_model' ), 'seasonal' ); ?>><?php vv_e( 'Dynamic seasonal' ); ?></option>
                                </select>
                            </div>
                        </div>

                        <div id="manage2026SeasonalWrap" class="form-group" style="<?php echo gArrayItem( $apartment, 'pricing_model' ) === 'seasonal' ? '' : 'display:none'; ?>">
                            <label><?php vv_e( 'Monthly seasonal prices' ); ?></label>
                            <div class="row">
                                <?php
                                $months = [ 1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'May', 6 => 'Jun', 7 => 'Jul', 8 => 'Aug', 9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dec' ];
                                foreach ( $months as $m => $mlabel ) {
                                    ?>
                                    <div class="col-md-3 col-6 form-group mb-2">
                                        <label class="small"><?php echo esc_html( $mlabel ); ?></label>
                                        <input type="text" name="seasonal_price[<?php echo $m; ?>]" class="form-control form-control-sm" value="<?php echo esc_attr( vv_number_format( gArrayItem( $seasonal, $m ) ) ); ?>">
                                    </div>
                                <?php } ?>
                            </div>
                        </div>

                        <div class="form-group">
                            <label><input type="checkbox" name="cleaning_fee_enabled" value="1" <?php checked( intval( gArrayItem( $apartment, 'cleaning_fee_enabled', 1 ) ), 1 ); ?>> <?php vv_e( 'Cleaning fee enabled' ); ?></label>
                            <div class="d-flex align-items-center flex-wrap">
                                <input type="text" name="cleaning_fee" id="manage2026CleaningFee" class="form-control form-control-sm mt-1 mr-2" style="max-width:160px" value="<?php echo esc_attr( vv_number_format( gArrayItem( $apartment, 'cleaning_fee' ) ) ); ?>" data-default="<?php echo esc_attr( $default_cleaning ); ?>">
                                <?php if ( $default_cleaning > 0 ) { ?>
                                    <button type="button" class="btn btn-link btn-sm p-0 mt-1" id="manage2026UseStdCleaning"><?php printf( esc_html( vv__( 'Use standard (%s)' ) ), esc_html( vv_number_format( $default_cleaning, true ) ) ); ?></button>
                                <?php } ?>
                            </div>
                        </div>

                        <hr>
                        <h6 class="small font-weight-bold"><?php vv_e( 'Long-stay discounts' ); ?></h6>
                        <p class="text-muted small"><?php vv_e( 'Percentage off the nightly rate for longer bookings.' ); ?></p>
                        <div class="row">
                            <div class="col-md-4 form-group">
                                <label class="small"><?php vv_e( '3+ nights %' ); ?></label>
                                <input type="text" name="discount_3days" class="form-control form-control-sm" value="<?php echo esc_attr( gArrayItem( $pricing, 'discount_3days' ) ); ?>">
                            </div>
                            <div class="col-md-4 form-group">
                                <label class="small"><?php vv_e( '7+ nights %' ); ?></label>
                                <input type="text" name="discount_7days" class="form-control form-control-sm" value="<?php echo esc_attr( gArrayItem( $pricing, 'discount_7days' ) ); ?>">
                            </div>
                            <div class="col-md-4 form-group">
                                <label class="small"><?php vv_e( '30+ nights %' ); ?></label>
                                <input type="text" name="discount_30days" class="form-control form-control-sm" value="<?php echo esc_attr( gArrayItem( $pricing, 'discount_30days' ) ); ?>">
                            </div>
                        </div>
                    </div>
                </div>

                <button type="submit" name="vv_action" value="vv_save_apartment_wizard_manage" class="btn btn-primary btn-sm"><?php vv_e( 'Save changes' ); ?></button>
            </div>

            <div class="col-lg-4">
                <div class="vv-manage2026-sidebar">
                    <dl>
                        <dt><?php vv_e( 'Status' ); ?></dt>
                        <dd>
                            <select name="status" class="form-control form-control-sm">
                                <option value="pending" <?php selected( $status_key, 'pending' ); ?>><?php vv_e( 'Pending approval' ); ?></option>
                                <option value="draft" <?php selected( $status_key, 'draft' ); ?>><?php vv_e( 'Deactivated / hidden' ); ?></option>
                                <?php if ( $is_admin ) { ?>
                                <option value="active" <?php selected( $status_key, 'active' ); ?>><?php vv_e( 'Published' ); ?></option>
                                <?php } ?>
                            </select>
                        </dd>
                    </dl>
                </div>
            </div>
        </div>
    </form>
    <?php } elseif ( $tab === 'images' ) {
        include __DIR__ . '/_manage-images.php';
    } elseif ( $tab === 'availability' ) {
        include __DIR__ . '/_manage-availability.php';
    } elseif ( $tab === 'facilities' ) {
        include __DIR__ . '/_manage-facilities.php';
    } else {
        include __DIR__ . '/_manage-stats.php';
    } ?>

    <?php if ( $is_admin ) { ?>
    <div class="modal fade" id="manage2026RejectModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form method="post" class="modal-content">
                <input type="hidden" name="vv_action" value="vv_reject_apartment_post">
                <input type="hidden" name="apartment_id" value="<?php echo intval( $apartment_id ); ?>">
                <div class="modal-header">
                    <h5 class="modal-title"><?php vv_e( 'Reject listing' ); ?></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="<?php echo esc_attr( vv__( 'Close' ) ); ?>"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label><?php vv_e( 'Reason for host' ); ?> <span class="text-danger">*</span></label>
                        <textarea name="rejection_reason" class="form-control form-control-sm" rows="4" required placeholder="<?php echo esc_attr( vv__( 'Explain what the host needs to fix before resubmitting…' ) ); ?>"></textarea>
                    </div>
                    <div class="form-group mb-0">
                        <label><?php vv_e( 'Internal note' ); ?> <span class="text-muted"><?php vv_e( 'optional, admin only' ); ?></span></label>
                        <textarea name="admin_review_note" class="form-control form-control-sm" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal"><?php vv_e( 'Cancel' ); ?></button>
                    <button type="submit" class="btn btn-danger btn-sm"><?php vv_e( 'Reject & return to draft' ); ?></button>
                </div>
            </form>
        </div>
    </div>
    <?php } ?>
</div>
<?php
if ( $tab === 'images' ) {
    ob_start();
    $images_wrap_id      = 'manage2026ImagesWrap';
    $images_form_id      = 'vvManage2026Images';
    $require_min_on_save = false;
    $disable_next_selector = '#noop';
    include __DIR__ . '/_apt-images-script.php';
    $footer_codes .= ob_get_clean();
} elseif ( $tab === 'pricing' ) {
    ob_start();
    ?>
<script>
jQuery(function($){
    $('#manage2026PricingModel').on('change', function(){
        if ($(this).val() === 'seasonal') {
            $('#manage2026SeasonalWrap').show();
        } else {
            $('#manage2026SeasonalWrap').hide();
        }
    });
    $('#manage2026UseSuggested').on('click', function(){
        var val = $('#manage2026PriceDaily').attr('data-suggested') || '';
        if (val !== '') {
            $('#manage2026PriceDaily').val(val);
        }
    });
    $('#manage2026UseStdCleaning').on('click', function(){
        var val = $('#manage2026CleaningFee').attr('data-default') || '';
        if (val !== '') {
            $('#manage2026CleaningFee').val(val);
        }
    });
});
</script>
    <?php
    $footer_codes .= ob_get_clean();
} elseif ( $tab === 'availability' ) {
    ob_start();
    $avail_apartment_id = intval( $apartment_id );
    $avail_redirect     = esc_js( vv_admin_url( 'apartments/manage?id=' . $apartment_id . '&tab=availability' ) );
    $avail_can_conv     = class_exists( 'vvConversions' ) && vvConversions::can_manage() ? '1' : '0';
    $avail_detail_open  = esc_js( GET_Request( 'detail' ) );
    ?>
<script>
jQuery(function($){
    var apartmentId = <?php echo $avail_apartment_id; ?>;
    var redirectUrl = '<?php echo $avail_redirect; ?>';
    var canConversions = <?php echo $avail_can_conv; ?> === 1;
    var timeline = [];
    try {
        timeline = JSON.parse($('#manage2026TimelineData').text() || '[]');
    } catch (e) { timeline = []; }

    function toggleExternalFields() {
        var isExternal = $('#manage2026PeriodType').val() === 'external_airbnb';
        $('.manage2026ExternalFields').toggle(isExternal);
        var needEmail = isExternal && $('#manage2026CreateConversion').is(':checked');
        $('.manage2026ConvRequired').toggle(needEmail);
        $('#manage2026GuestEmail').prop('required', needEmail);
    }

    $('#manage2026PeriodType').on('change', toggleExternalFields);
    $('#manage2026CreateConversion').on('change', toggleExternalFields);
    toggleExternalFields();

    function escHtml(s) {
        return $('<div>').text(s || '').html();
    }

    function formatMoney(n) {
        if (n === '' || n === null || isNaN(parseFloat(n))) return '—';
        return parseFloat(n).toLocaleString(undefined, {minimumFractionDigits: 0, maximumFractionDigits: 2});
    }

    function renderDetail(row) {
        var html = '<dl class="row mb-0">';
        html += '<dt class="col-sm-3"><?php echo esc_js( vv__( 'Type' ) ); ?></dt><dd class="col-sm-9">' + escHtml(row.type_label) + '</dd>';
        html += '<dt class="col-sm-3"><?php echo esc_js( vv__( 'Dates' ) ); ?></dt><dd class="col-sm-9">' + escHtml(row.start_date) + ' → ' + escHtml(row.end_date) + '</dd>';

        if (row.type === 'vietstays') {
            html += '<dt class="col-sm-3"><?php echo esc_js( vv__( 'Channel' ) ); ?></dt><dd class="col-sm-9">Vietstays</dd>';
            if (row.booking_num) {
                html += '<dt class="col-sm-3"><?php echo esc_js( vv__( 'Booking #' ) ); ?></dt><dd class="col-sm-9"><code>' + escHtml(row.booking_num) + '</code></dd>';
            }
            html += '<dt class="col-sm-3"><?php echo esc_js( vv__( 'Guest' ) ); ?></dt><dd class="col-sm-9">' + escHtml(row.guest_name || '—');
            if (row.guest_email) html += '<br><small class="text-muted">' + escHtml(row.guest_email) + '</small>';
            html += '</dd>';
            if (row.nights) {
                html += '<dt class="col-sm-3"><?php echo esc_js( vv__( 'Nights' ) ); ?></dt><dd class="col-sm-9">' + escHtml(String(row.nights)) + '</dd>';
            }
            html += '<dt class="col-sm-3"><?php echo esc_js( vv__( 'Total' ) ); ?></dt><dd class="col-sm-9">' + formatMoney(row.total) + '</dd>';
            if (parseFloat(row.cleaning_fee) > 0) {
                html += '<dt class="col-sm-3"><?php echo esc_js( vv__( 'Cleaning fee' ) ); ?></dt><dd class="col-sm-9">' + formatMoney(row.cleaning_fee) + '</dd>';
            }
            if (row.promo_code) {
                html += '<dt class="col-sm-3"><?php echo esc_js( vv__( 'Promo code' ) ); ?></dt><dd class="col-sm-9"><code>' + escHtml(row.promo_code) + '</code></dd>';
            }
            if (row.payment_method) {
                html += '<dt class="col-sm-3"><?php echo esc_js( vv__( 'Payment' ) ); ?></dt><dd class="col-sm-9">' + escHtml(row.payment_method) + '</dd>';
            }
            if (row.booking_status) {
                html += '<dt class="col-sm-3"><?php echo esc_js( vv__( 'Status' ) ); ?></dt><dd class="col-sm-9">' + escHtml(row.booking_status) + '</dd>';
            }
        } else if (row.type === 'external_airbnb') {
            html += '<dt class="col-sm-3"><?php echo esc_js( vv__( 'Platform' ) ); ?></dt><dd class="col-sm-9">' + escHtml(row.external_platform || row.source || '—') + '</dd>';
            if (row.external_booking_ref) {
                html += '<dt class="col-sm-3"><?php echo esc_js( vv__( 'External ref' ) ); ?></dt><dd class="col-sm-9"><code>' + escHtml(row.external_booking_ref) + '</code></dd>';
            }
            html += '<dt class="col-sm-3"><?php echo esc_js( vv__( 'Guest' ) ); ?></dt><dd class="col-sm-9">' + escHtml(row.guest_name || '—');
            if (row.guest_email) html += '<br><small class="text-muted">' + escHtml(row.guest_email) + '</small>';
            html += '</dd>';
            if (row.conversion_id) {
                html += '<dt class="col-sm-3"><?php echo esc_js( vv__( 'Conversion' ) ); ?></dt><dd class="col-sm-9"><code>' + escHtml(row.conversion_ref) + '</code><br><span class="text-muted">' + escHtml(row.conversion_status) + '</span>';
                if (row.conversion_promo) html += '<br><small><?php echo esc_js( vv__( 'Promo:' ) ); ?> <code>' + escHtml(row.conversion_promo) + '</code></small>';
                html += '</dd>';
            }
        } else {
            if (row.guest_name) {
                html += '<dt class="col-sm-3"><?php echo esc_js( vv__( 'Note' ) ); ?></dt><dd class="col-sm-9">' + escHtml(row.guest_name) + '</dd>';
            }
        }

        if (row.note) {
            html += '<dt class="col-sm-3"><?php echo esc_js( vv__( 'Notes' ) ); ?></dt><dd class="col-sm-9">' + escHtml(row.note) + '</dd>';
        }
        html += '</dl>';

        $('#manage2026DetailTitle').text(row.type_label || '<?php echo esc_js( vv__( 'Details' ) ); ?>');
        $('#manage2026DetailBody').html(html);

        var footer = '<button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal"><?php echo esc_js( vv__( 'Close' ) ); ?></button>';
        if (row.type === 'vietstays' && row.detail_url) {
            footer = '<a href="' + escHtml(row.detail_url) + '" class="btn btn-primary btn-sm mr-auto"><?php echo esc_js( vv__( 'Open booking' ) ); ?></a>' + footer;
        } else if (row.type === 'external_airbnb' && canConversions) {
            if (row.conversion_id && row.conversion_status === 'draft') {
                footer = '<form method="post" class="mr-auto"><input type="hidden" name="vv_action" value="vv_send_conversion"><input type="hidden" name="conversion_id" value="' + row.conversion_id + '"><input type="hidden" name="redirect_to" value="' + redirectUrl + '"><button type="submit" class="btn btn-success btn-sm"><?php echo esc_js( vv__( 'Send invite' ) ); ?></button></form>' + footer;
            } else if (row.conversion_id && ['sent','opened','registered'].indexOf(row.conversion_status) >= 0) {
                footer = '<a href="<?php echo esc_js( vv_admin_url( 'conversions' ) ); ?>?search=' + encodeURIComponent(row.conversion_ref || '') + '" class="btn btn-outline-primary btn-sm mr-auto"><?php echo esc_js( vv__( 'View conversion' ) ); ?></a>' + footer;
            } else if (row.period_id) {
                footer = '<form method="post" class="mr-auto text-left"><input type="hidden" name="vv_action" value="vv_create_conversion_from_period"><input type="hidden" name="apartment_id" value="' + apartmentId + '"><input type="hidden" name="period_id" value="' + row.period_id + '"><div class="form-group mb-2"><label class="small mb-0"><?php echo esc_js( vv__( 'Guest email' ) ); ?></label><input type="email" name="guest_email" class="form-control form-control-sm" value="' + escHtml(row.guest_email || '') + '" required></div><label class="small"><input type="checkbox" name="send_conversion_invite" value="1" checked> <?php echo esc_js( vv__( 'Send invite' ) ); ?></label><br><button type="submit" class="btn btn-success btn-sm mt-2"><?php echo esc_js( vv__( 'Create conversion' ) ); ?></button></form>' + footer;
            }
        }
        $('#manage2026DetailFooter').html(footer);
        $('#manage2026PeriodDetail').modal('show');
    }

    $(document).on('click', '.manage2026PeriodDetail', function(){
        var rowId = $(this).data('row-id');
        var row = timeline.find(function(r){ return r.id === rowId; });
        if (row) renderDetail(row);
    });

    var openDetail = '<?php echo $avail_detail_open; ?>';
    if (openDetail) {
        var row = timeline.find(function(r){ return r.id === openDetail; });
        if (row) renderDetail(row);
    }
});
</script>
    <?php
    $footer_codes .= ob_get_clean();
}
?>
