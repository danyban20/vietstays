<?php 
global $wpdb;

$pg_title   = 'Add User';
$back_link  = vv_admin_url('users/list');
$back_txt   = 'Back to Users';
$user_data 	= [];

if($user_level == 'admin'){
    $pg_title   = 'Add Admin';
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

vv_show_page_title_bar(['pg_title' => $pg_title, 'back_link' => $back_link, 'back_txt' => $back_txt]);
$meta_title = vv_admin_meta_title( $pg_title );


//echo print_r_pre($user_data);

?>

<?php include('form.php'); ?>
