<?php
$user_id = get_current_user_id();
$user    = get_user_by( 'ID', $user_id );

if ( ! $user ) {
	echo '<div class="alert alert-danger">' . esc_html( vv__( 'You must be logged in to view your account.' ) ) . '</div>';
	return;
}

$_GET['id']     = $user_id;
$_REQUEST['id'] = $user_id;

$vv_is_account_page = true;

$edit_view = vv_admin_account_edit_view( $user_id );
include $this->views_path . $edit_view . '.php';
