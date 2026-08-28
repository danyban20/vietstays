<?php 
global $wpdb;

$pg_title   = 'Add District';
vv_show_page_title_bar(['pg_title' => $pg_title, 'back_link' => vv_admin_url('districts/list'), 'back_txt' => 'Back to Districts']);
$meta_title = vv_admin_meta_title( $pg_title );

$district = [];

//echo print_r_pre($user_data);

?>

<?php include('form.php'); ?>

