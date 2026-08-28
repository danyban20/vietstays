<?php
if ( ! vv_is_admin_role() ) {
	echo '<div class="alert alert-danger">' . esc_html( vv__( 'Permission denied.' ) ) . '</div>';
	return;
}

$request_id = intval( GET_Request( 'id' ) );
$request    = vvApartmentsWizard::get_building_request( $request_id );

if ( empty( $request['ID'] ) ) {
	echo '<div class="alert alert-danger">' . esc_html( vv__( 'Request not found.' ) ) . '</div>';
	return;
}

$pg_title   = 'Building request #' . $request_id;
$meta_title = vv_admin_meta_title( $pg_title );
vv_show_page_title_bar( [
	'pg_title'  => $pg_title,
	'back_link' => vv_admin_url( 'building-requests' ),
	'back_txt'  => 'All requests',
] );

$status_labels = vvApartmentsWizard::building_request_status_labels();
$status        = gArrayItem( $request, 'status', 'pending' );
$user_id       = intval( gArrayItem( $request, 'user_id' ) );
$user          = $user_id > 0 ? get_user_by( 'ID', $user_id ) : false;
$city_id       = intval( gArrayItem( $request, 'city_id' ) );
$district_id   = intval( gArrayItem( $request, 'district_id' ) );
$apt_id        = intval( gArrayItem( $request, 'apartment_id' ) );
$resolved_id   = intval( gArrayItem( $request, 'resolved_building_id' ) );

$city_name = '';
if ( $city_id > 0 ) {
	global $wpdb;
	$city_name = (string) $wpdb->get_var( $wpdb->prepare( "SELECT post_title FROM {$wpdb->posts} WHERE ID = %d", $city_id ) );
}
$district_name = $district_id > 0 ? gArrayItem( vv_get_district( $district_id ), 'post_title', '' ) : '';

$create_building_url = vv_admin_url( 'locations/neighbourhoods/add' ) . '?' . http_build_query( [
	'from_request'    => $request_id,
	'prefill_name'    => gArrayItem( $request, 'building_name' ),
	'prefill_district'=> $district_id,
] );
?>

<div class="row">
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong><?php echo esc_html( gArrayItem( $request, 'building_name' ) ); ?></strong>
                <span class="badge badge-<?php echo $status === 'pending' ? 'warning' : ( $status === 'approved' ? 'success' : ( $status === 'rejected' ? 'danger' : 'secondary' ) ); ?>"><?php echo esc_html( vv__( gArrayItem( $status_labels, $status, $status ) ) ); ?></span>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-3"><?php vv_e( 'Address' ); ?></dt>
                    <dd class="col-sm-9"><?php echo esc_html( gArrayItem( $request, 'address' ) ?: '—' ); ?></dd>

                    <dt class="col-sm-3"><?php vv_e( 'City / district' ); ?></dt>
                    <dd class="col-sm-9"><?php echo esc_html( trim( $district_name . ( $city_name !== '' ? ', ' . $city_name : '' ) ) ?: '—' ); ?></dd>

                    <dt class="col-sm-3"><?php vv_e( 'Host notes' ); ?></dt>
                    <dd class="col-sm-9"><?php echo nl2br( esc_html( gArrayItem( $request, 'notes' ) ?: '—' ) ); ?></dd>

                    <dt class="col-sm-3"><?php vv_e( 'Submitted by' ); ?></dt>
                    <dd class="col-sm-9">
                        <?php if ( $user ) {
                            echo esc_html( $user->display_name . ' (' . $user->user_email . ')' );
                        } else {
                            echo '—';
                        } ?>
                    </dd>

                    <dt class="col-sm-3"><?php vv_e( 'Linked apartment' ); ?></dt>
                    <dd class="col-sm-9">
                        <?php if ( $apt_id > 0 ) { ?>
                            <a href="<?php echo esc_url( vv_admin_url( 'apartments/manage?id=' . $apt_id ) ); ?>"><?php vv_e( 'Open apartment wizard' ); ?> #<?php echo $apt_id; ?></a>
                        <?php } else {
                            vv_e( 'None' );
                        } ?>
                    </dd>

                    <?php if ( $resolved_id > 0 ) { ?>
                    <dt class="col-sm-3"><?php vv_e( 'Created building' ); ?></dt>
                    <dd class="col-sm-9">
                        <a href="<?php echo esc_url( vv_admin_url( 'locations/neighbourhoods/edit?id=' . $resolved_id ) ); ?>"><?php vv_e( 'Edit building' ); ?> #<?php echo $resolved_id; ?></a>
                    </dd>
                    <?php } ?>

                    <?php if ( trim( gArrayItem( $request, 'admin_note' ) ) !== '' ) { ?>
                    <dt class="col-sm-3"><?php vv_e( 'Admin note' ); ?></dt>
                    <dd class="col-sm-9"><?php echo nl2br( esc_html( gArrayItem( $request, 'admin_note' ) ) ); ?></dd>
                    <?php } ?>

                    <dt class="col-sm-3"><?php vv_e( 'Submitted' ); ?></dt>
                    <dd class="col-sm-9"><?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' H:i', strtotime( gArrayItem( $request, 'dateadded' ) ) ) ); ?></dd>
                </dl>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <?php if ( in_array( $status, [ 'pending', 'reviewed' ], true ) ) { ?>
        <div class="card mb-3">
            <div class="card-header"><strong><?php vv_e( 'Actions' ); ?></strong></div>
            <div class="card-body">
                <a href="<?php echo esc_url( $create_building_url ); ?>" class="btn btn-success btn-sm btn-block mb-2"><?php vv_e( 'Create building from request' ); ?></a>
                <form method="post" class="mb-3">
                    <input type="hidden" name="vv_action" value="vv_update_building_request">
                    <input type="hidden" name="request_id" value="<?php echo $request_id; ?>">
                    <input type="hidden" name="request_action" value="reviewed">
                    <button type="submit" class="btn btn-outline-secondary btn-sm btn-block"><?php vv_e( 'Mark under review' ); ?></button>
                </form>
                <form method="post" onsubmit="return confirm('<?php echo esc_js( vv__( 'Reject this building request?' ) ); ?>')">
                    <input type="hidden" name="vv_action" value="vv_update_building_request">
                    <input type="hidden" name="request_id" value="<?php echo $request_id; ?>">
                    <input type="hidden" name="request_action" value="reject">
                    <div class="form-group">
                        <label class="small"><?php vv_e( 'Rejection note' ); ?></label>
                        <textarea name="admin_note" class="form-control form-control-sm" rows="3" required placeholder="<?php echo esc_attr( vv__( 'Reason for host…' ) ); ?>"></textarea>
                    </div>
                    <button type="submit" class="btn btn-outline-danger btn-sm btn-block"><?php vv_e( 'Reject request' ); ?></button>
                </form>
            </div>
        </div>
        <?php } elseif ( $status === 'rejected' ) { ?>
        <div class="card mb-3">
            <div class="card-body">
                <form method="post">
                    <input type="hidden" name="vv_action" value="vv_update_building_request">
                    <input type="hidden" name="request_id" value="<?php echo $request_id; ?>">
                    <input type="hidden" name="request_action" value="reopen">
                    <button type="submit" class="btn btn-outline-secondary btn-sm btn-block"><?php vv_e( 'Reopen request' ); ?></button>
                </form>
            </div>
        </div>
        <?php } ?>
    </div>
</div>
