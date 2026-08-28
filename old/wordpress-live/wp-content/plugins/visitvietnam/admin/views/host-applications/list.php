<?php
$pg_title   = 'Host Applications';
$meta_title = vv_admin_meta_title( $pg_title );
vv_show_page_title_bar( [ 'pg_title' => $pg_title ] );

$status_filter = sanitize_text_field( GET_Request( 'status' ) );
$search        = sanitize_text_field( GET_Request( 'search' ) );

$filter = [
	'search'  => $search,
	'per_page' => 50,
];
if ( $status_filter === 'all' ) {
	$filter['include_all_statuses'] = true;
} elseif ( $status_filter !== '' ) {
	$filter['status'] = $status_filter;
}

$result         = $this->host_applications_class->get_host_applicant_users( $filter );
$applicants     = gArrayItem( $result, 'users', [] );
$status_labels  = vvHostApplications::user_status_labels();
?>

<div class="row mb-3">
    <div class="col-md-8">
        <form method="get" class="form-inline">
            <input type="text" name="search" class="form-control form-control-sm mr-2 mb-2" placeholder="<?php echo esc_attr( vv__( 'Search name, email, ref...' ) ); ?>" value="<?php echo esc_attr( $search ); ?>">
            <select name="status" class="form-control form-control-sm mr-2 mb-2">
                <option value=""><?php vv_e( 'Pending approval' ); ?></option>
                <option value="all" <?php selected( $status_filter, 'all' ); ?>><?php vv_e( 'All statuses' ); ?></option>
                <?php foreach ( $status_labels as $key => $label ) { ?>
                    <option value="<?php echo esc_attr( $key ); ?>" <?php selected( $status_filter, $key ); ?>><?php echo esc_html( $label ); ?></option>
                <?php } ?>
            </select>
            <button type="submit" class="btn btn-sm btn-primary mb-2"><?php vv_e( 'Filter' ); ?></button>
            <?php if ( $search !== '' || $status_filter !== '' ) { ?>
                <a href="<?php echo esc_url( vv_admin_url( 'host-applications' ) ); ?>" class="btn btn-sm btn-light ml-2 mb-2"><?php vv_e( 'Clear' ); ?></a>
            <?php } ?>
        </form>
    </div>
</div>


<div class="row">
    <div class="col">
        <div class="au-card" style="overflow: auto;">
            <div class="table-responsive table--no-card m-b-30">
                <table class="table table-borderless table-striped table-earning">
                    <thead>
                        <tr>
                            <th><?php vv_e( 'Application ID' ); ?></th>
                            <th><?php vv_e( 'Name' ); ?></th>
                            <th><?php vv_e( 'Email' ); ?></th>
                            <th><?php vv_e( 'Type' ); ?></th>
                            <th><?php vv_e( 'City' ); ?></th>
                            <th><?php vv_e( 'Properties' ); ?></th>
                            <th><?php vv_e( 'Submitted' ); ?></th>
                            <th><?php vv_e( 'Status' ); ?></th>
                            <th style="width:80px">&nbsp;</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ( count( $applicants ) === 0 ) { ?>
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4"><?php vv_e( 'No pending host applications found.' ); ?></td>
                            </tr>
                        <?php } ?>
                        <?php foreach ( $applicants as $row ) { ?>
                            <?php
                            $user_id    = intval( gArrayItem( $row, 'user_id' ) );
                            $type_label = ( gArrayItem( $row, 'applicant_type' ) === 'single_property' ) ? vv__( 'One' ) : vv__( 'Multiple' );
                            $props      = gArrayItem( $row, 'num_properties' );
                            if ( $props === '' || $props === null ) {
                                $props = '—';
                            }
                            $status     = gArrayItem( $row, 'status' );
                            $view_url   = vv_admin_url( 'host-applications/view' ) . '?id=' . $user_id;
                            ?>
                            <tr>
                                <td>
                                    <a href="<?php echo esc_url( $view_url ); ?>">
                                        <?php echo esc_html( gArrayItem( $row, 'application_ref' ) ?: ( 'User #' . $user_id ) ); ?>
                                    </a>
                                </td>
                                <td><?php echo esc_html( gArrayItem( $row, 'full_name' ) ); ?></td>
                                <td><a href="<?php echo esc_url( vv_admin_url( 'partners/edit' ) . '?id=' . $user_id ); ?>"><?php echo esc_html( gArrayItem( $row, 'email' ) ); ?></a></td>
                                <td><?php echo esc_html( $type_label ); ?></td>
                                <td><?php echo esc_html( gArrayItem( $row, 'home_city' ) ?: '—' ); ?></td>
                                <td><?php echo esc_html( $props ); ?></td>
                                <td><?php echo esc_html( date( 'j M Y H:i', strtotime( gArrayItem( $row, 'dateadded' ) ) ) ); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo ( $status === 'pending-approval' ) ? 'warning' : 'secondary'; ?>">
                                        <?php echo esc_html( vvHostApplications::user_status_label( $status ) ); ?>
                                    </span>
                                </td>
                                <td class="text-right">
                                    <a href="<?php echo esc_url( $view_url ); ?>" class="btn btn-sm btn-primary"><?php vv_e( 'View' ); ?></a>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
