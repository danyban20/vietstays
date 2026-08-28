<?php
$user_id = intval( GET_Request( 'id' ) );
$user    = get_user_by( 'ID', $user_id );

if ( ! $user ) {
	echo '<div class="alert alert-danger">' . esc_html( vv__( 'Host application not found.' ) ) . '</div>';
	return;
}

if ( vv_get_user_account_role( $user_id ) !== vvHostApplications::ROLE_HOST ) {
	echo '<div class="alert alert-danger">' . esc_html( vv__( 'This user is not a host applicant.' ) ) . '</div>';
	return;
}

$application = $this->host_applications_class->ensure_application_for_user( $user_id );

if ( ! is_array( $application ) || intval( gArrayItem( $application, 'ID' ) ) <= 0 ) {
	echo '<div class="alert alert-danger">' . esc_html( vv__( 'Host application not found.' ) ) . '</div>';
	return;
}

$pg_title   = sprintf( 'Host Application %s', gArrayItem( $application, 'application_ref' ) );
$meta_title = vv_get_app_admin_title() . ' | ' . sprintf( vv__( 'Host Application %s' ), gArrayItem( $application, 'application_ref' ) );

vv_show_page_title_bar( [
	'pg_title'  => $pg_title,
	'add_link'  => vv_admin_url( 'host-applications' ),
	'add_txt'   => 'Back to list',
	'add_attr'  => '',
] );

include __DIR__ . '/_detail.php';
