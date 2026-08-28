<?php 
global $wpdb;

$pg_title   = 'Add Location';
vv_show_page_title_bar(['pg_title' => $pg_title, 'back_link' => vv_admin_url('locations/list'), 'back_txt' => 'Back to Locations']);
$meta_title = vv_admin_meta_title( $pg_title );

$location = [];

//echo print_r_pre($user_data);

?>

<?php include('form.php'); ?>

