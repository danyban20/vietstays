<?php 
global $wpdb;

$pg_title   = 'Edit District';
vv_show_page_title_bar(['pg_title' => $pg_title, 'back_link' => vv_admin_url('districts/list'), 'back_txt' => 'Back to Districts']);
$meta_title = vv_admin_meta_title( $pg_title );

$district = $this->district_class->get_district(GET_Request('id'));
//echo print_r_pre($user_data);

if(gArrayItem($district,'ID') > 0){
    include('form.php');
}else{

    echo '<div class="alert alert-danger">' . esc_html( vv__( 'Invalid URL' ) ) . '</div>';

}



