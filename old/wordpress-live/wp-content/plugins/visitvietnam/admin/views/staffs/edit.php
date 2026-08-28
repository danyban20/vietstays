<?php 
global $wpdb;

$staff = get_user_by('ID',GET_Request('id'));
$user_data = [];

if ( ! empty( $vv_is_account_page ) ) {
	$pg_title   = 'Your Account';
	$back_link  = vv_admin_url( 'dashboard' );
	$back_txt   = 'Dashboard';
} else {
	$back_link  = vv_admin_url('staffs/list');
	$back_txt   = 'Back to Staffs';
	$pg_title   = 'Edit Staff';
}

if($staff){
    $user_data = (array) $staff->data;

    if ( empty( $vv_is_account_page ) ) {
	    $user_level = get_user_meta($staff->ID,'vv_user_level',true);

	    if($user_level == 'admin'){
	        $pg_title   = 'Edit Admin';
	        $back_link  = vv_admin_url('admins');
	        $back_txt   = 'Back to Admins List';    
	    }elseif($user_level == 'ambassador'){
	        $pg_title   = 'Edit Ambassador';
	        $back_link  = vv_admin_url('ambassadors');
	        $back_txt   = 'Back to Ambassadors List';    
	    }elseif($user_level == 'partner'){
	        $pg_title   = 'Edit Partner';
	        $back_link  = vv_admin_url('partners');
	        $back_txt   = 'Back to Partners List';    
	    }
    }
}

vv_show_page_title_bar(['pg_title' => $pg_title, 'back_link' => $back_link, 'back_txt' => $back_txt]);
$meta_title = vv_admin_meta_title( $pg_title );


//echo print_r_pre($user_data);

?>

<?php include('form.php'); ?>
