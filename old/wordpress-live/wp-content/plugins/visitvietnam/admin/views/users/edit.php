<?php 
global $wpdb;

$user = get_user_by('ID',GET_Request('id'));
$user_data = [];

if ( ! empty( $vv_is_account_page ) ) {
	$pg_title   = 'Your Account';
	$back_link  = vv_admin_url( 'dashboard' );
	$back_txt   = 'Dashboard';
} else {
	$back_link  = vv_admin_url('users/list');
	$back_txt   = 'Back to Users';
}

if($user){
    $user_data = (array) $user->data;

    if ( empty( $vv_is_account_page ) ) {
	    $user_level = get_user_meta($user->ID,'vv_user_level',true);
	    if ( $user_level === '' && class_exists( 'vvRoles' ) && vvRoles::get_primary_backend_role( $user->ID ) === vvRoles::ROLE_ADMIN ) {
		    $user_level = 'admin';
	    }

	    if ( in_array( $user_level, [ 'admin', 'administrator' ], true ) ) {
	        $back_link  = vv_admin_url('admins');
	        $back_txt   = 'Back to Admins List';
	    }elseif($user_level == 'ambassador'){
	        $back_link  = vv_admin_url('ambassadors');
	        $back_txt   = 'Back to Ambassadors List';    
	    }elseif($user_level == 'partner'){
	        $back_link  = vv_admin_url('partners');
	        $back_txt   = 'Back to Partners List';    
	    }
    }
}

if ( empty( $vv_is_account_page ) ) {
	$pg_title = 'Edit User';
}
vv_show_page_title_bar(['pg_title' => $pg_title, 'back_link' => $back_link, 'back_txt' => $back_txt]);
$meta_title = vv_admin_meta_title( $pg_title );


//echo print_r_pre($user_data);

?>

<?php include('form.php'); ?>
