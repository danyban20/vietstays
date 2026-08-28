<?php 
global $wpdb;

$pg_title   = 'Edit Location';
vv_show_page_title_bar(['pg_title' => $pg_title, 'back_link' => vv_admin_url('locations/list'), 'back_txt' => 'Back to Locations']);
$meta_title = vv_admin_meta_title( $pg_title );

$location = $this->district_class->get_district(GET_Request('id'));
//echo print_r_pre($user_data);

if(gArrayItem($location,'ID') > 0){
    include('form.php');
}else{

    echo '<div class="alert alert-danger">' . esc_html( vv__( 'Invalid URL' ) ) . '</div>';

}



