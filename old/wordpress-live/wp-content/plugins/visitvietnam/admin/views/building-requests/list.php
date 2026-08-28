<?php
if ( ! vv_is_admin_role() ) {
	echo '<div class="alert alert-danger">' . esc_html( vv__( 'Permission denied.' ) ) . '</div>';
	return;
}

$pg_title   = 'Building requests';
$meta_title = vv_admin_meta_title( $pg_title );
vv_show_page_title_bar( [ 'pg_title' => $pg_title ] );

$status_filter = sanitize_key( GET_Request( 'status' ) );
$search        = sanitize_text_field( GET_Request( 'search' ) );

$filter = [
	'search'   => $search,
	'per_page' => 50,
];
if ( $status_filter === 'all' ) {
	$filter['status'] = 'all';
} elseif ( $status_filter !== '' ) {
	$filter['status'] = $status_filter;
}

$requests      = vvApartmentsWizard::get_building_requests( $filter );
$status_labels = vvApartmentsWizard::building_request_status_labels();
$pending_count = count( vvApartmentsWizard::get_building_requests( [ 'status' => 'pending', 'per_page' => 'all' ] ) );
?>

<div class="row mb-3">
    <div class="col-md-9">
        <form method="get" class="form-inline">
            <input type="text" name="search" class="form-control form-control-sm mr-2 mb-2" placeholder="<?php echo esc_attr( vv__( 'Search building name, address…' ) ); ?>" value="<?php echo esc_attr( $search ); ?>">
            <select name="status" class="form-control form-control-sm mr-2 mb-2">
                <option value=""><?php printf( '%s (%d)', esc_html( vv__( 'Pending' ) ), intval( $pending_count ) ); ?></option>
                <option value="all" <?php selected( $status_filter, 'all' ); ?>><?php vv_e( 'All statuses' ); ?></option>
                <?php foreach ( $status_labels as $key => $label ) { ?>
                    <option value="<?php echo esc_attr( $key ); ?>" <?php selected( $status_filter, $key ); ?>><?php echo esc_html( vv__( $label ) ); ?></option>
                <?php } ?>
            </select>
            <button type="submit" class="btn btn-sm btn-primary mb-2"><?php vv_e( 'Filter' ); ?></button>
            <?php if ( $search !== '' || $status_filter !== '' ) { ?>
                <a href="<?php echo esc_url( vv_admin_url( 'building-requests' ) ); ?>" class="btn btn-sm btn-light ml-2 mb-2"><?php vv_e( 'Clear' ); ?></a>
            <?php } ?>
        </form>
    </div>
</div>

<div class="row">
    <div class="col">
        <div class="au-card" style="overflow:auto">
            <div class="table-responsive table--no-card m-b-30">
                <table class="table table-borderless table-striped table-earning">
                    <thead>
                        <tr>
                            <th><?php vv_e( 'ID' ); ?></th>
                            <th><?php vv_e( 'Building' ); ?></th>
                            <th><?php vv_e( 'Location' ); ?></th>
                            <th><?php vv_e( 'Host' ); ?></th>
                            <th><?php vv_e( 'Apartment' ); ?></th>
                            <th><?php vv_e( 'Submitted' ); ?></th>
                            <th><?php vv_e( 'Status' ); ?></th>
                            <th style="width:90px">&nbsp;</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ( count( $requests ) === 0 ) { ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4"><?php vv_e( 'No building requests found.' ); ?></td>
                            </tr>
                        <?php } ?>
                        <?php foreach ( $requests as $row ) {
                            $request_id  = intval( gArrayItem( $row, 'ID' ) );
                            $user_id     = intval( gArrayItem( $row, 'user_id' ) );
                            $user        = $user_id > 0 ? get_user_by( 'ID', $user_id ) : false;
                            $host_label  = $user ? trim( $user->display_name . ' · ' . $user->user_email ) : '—';
                            $city_id     = intval( gArrayItem( $row, 'city_id' ) );
                            $district_id = intval( gArrayItem( $row, 'district_id' ) );
                            $location    = [];
                            if ( $district_id > 0 ) {
                                $district = vv_get_district( $district_id );
                                if ( ! empty( $district['post_title'] ) ) {
                                    $location[] = $district['post_title'];
                                }
                            }
                            if ( $city_id > 0 ) {
                                global $wpdb;
                                $city_name = $wpdb->get_var( $wpdb->prepare( "SELECT post_title FROM {$wpdb->posts} WHERE ID = %d", $city_id ) );
                                if ( $city_name ) {
                                    $location[] = $city_name;
                                }
                            }
                            $status      = gArrayItem( $row, 'status', 'pending' );
                            $apt_id      = intval( gArrayItem( $row, 'apartment_id' ) );
                            $view_url    = vv_admin_url( 'building-requests/view?id=' . $request_id );
                            ?>
                            <tr>
                                <td>#<?php echo $request_id; ?></td>
                                <td>
                                    <strong><?php echo esc_html( gArrayItem( $row, 'building_name' ) ); ?></strong>
                                    <?php if ( trim( gArrayItem( $row, 'address' ) ) !== '' ) { ?>
                                        <br><span class="small text-muted"><?php echo esc_html( gArrayItem( $row, 'address' ) ); ?></span>
                                    <?php } ?>
                                </td>
                                <td class="small"><?php echo esc_html( implode( ', ', $location ) ?: '—' ); ?></td>
                                <td class="small"><?php echo esc_html( $host_label ); ?></td>
                                <td class="small">
                                    <?php if ( $apt_id > 0 ) { ?>
                                        <a href="<?php echo esc_url( vv_admin_url( 'apartments/manage?id=' . $apt_id ) ); ?>">#<?php echo $apt_id; ?></a>
                                    <?php } else {
                                        echo '—';
                                    } ?>
                                </td>
                                <td class="small"><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( gArrayItem( $row, 'dateadded' ) ) ) ); ?></td>
                                <td><span class="badge badge-<?php echo $status === 'pending' ? 'warning' : ( $status === 'approved' ? 'success' : ( $status === 'rejected' ? 'danger' : 'secondary' ) ); ?>"><?php echo esc_html( vv__( gArrayItem( $status_labels, $status, $status ) ) ); ?></span></td>
                                <td><a href="<?php echo esc_url( $view_url ); ?>" class="btn btn-sm btn-outline-primary"><?php vv_e( 'View' ); ?></a></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
