<?php 
global $wpdb;

$back_link  = vv_admin_url('tasks/list');
$back_txt   = 'Back to Tasks';
$task = get_post(GET_Request('id'));
$task_data = [];
$pg_title   = 'Edit Task';
if($task){
    $task_data =$post_array = get_object_vars($task);

}

vv_show_page_title_bar(['pg_title' => $pg_title, 'back_link' => $back_link, 'back_txt' => $back_txt]);
$meta_title = vv_admin_meta_title( $pg_title );


//echo print_r_pre($task_data);

?>

<?php include('form.php'); ?>
