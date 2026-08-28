<?php
$user_id = intval( GET_Request( 'id' ) );
$user    = get_user_by( 'ID', $user_id );

if ( ! $user ) {
	echo '<div class="alert alert-danger">' . esc_html( vv__( 'Partner not found.' ) ) . '</div>';
	return;
}

global $logged_user_role;
if ( $logged_user_role === 'partner' && get_current_user_id() !== $user_id ) {
	echo '<div class="alert alert-danger">' . esc_html( vv__( 'You can only edit your own profile.' ) ) . '</div>';
	return;
}

$user_data  = (array) $user->data;
$user_level = get_user_meta( $user_id, 'vv_user_level', true );
$application = $this->host_applications_class->get_application_by_user_id( $user_id );

if ( ! empty( $vv_is_account_page ) ) {
	$pg_title = 'Your Account';
	vv_show_page_title_bar( [
		'pg_title'  => $pg_title,
		'back_link' => vv_admin_url( 'dashboard' ),
		'back_txt'  => 'Dashboard',
	] );
} else {
	$pg_title = 'Edit Partner';
	if ( is_array( $application ) && gArrayItem( $application, 'application_ref' ) !== '' ) {
		$pg_title = 'Partner — ' . gArrayItem( $application, 'application_ref' );
	}

	vv_show_page_title_bar( [
		'pg_title'  => $pg_title,
		'back_link' => vv_admin_url( 'partners' ),
		'back_txt'  => 'Back to Partners',
	] );
}
$meta_title = vv_admin_meta_title( $pg_title );

if ( is_array( $application ) && intval( gArrayItem( $application, 'ID' ) ) > 0 ) {
	include dirname( __DIR__ ) . '/host-applications/_detail.php';
} else {
	?>
	<div class="alert alert-warning mb-4"><?php vv_e( 'No host application record linked to this partner. Showing account form only.' ); ?></div>
	<?php include $this->views_path . '/users/form.php'; ?>
	<?php
}
