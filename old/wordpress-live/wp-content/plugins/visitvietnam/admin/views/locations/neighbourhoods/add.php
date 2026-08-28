<?php 
global $wpdb;

$pg_title   = 'Add building';
vv_show_page_title_bar( [
	'pg_title'  => $pg_title,
	'back_link' => vv_admin_url( 'locations/neighbourhoods' ),
	'back_txt'  => 'Back to buildings',
] );
$meta_title = vv_admin_meta_title( $pg_title );

$neighbourhood = [];
$from_request_id = intval( GET_Request( 'from_request' ) );
$prefill_name    = sanitize_text_field( GET_Request( 'prefill_name' ) );
$prefill_district = intval( GET_Request( 'prefill_district' ) );

if ( $from_request_id > 0 ) {
	echo '<div class="alert alert-info">' . esc_html( vv__( 'Creating building from host request #' ) . $from_request_id ) . '</div>';
}
if ( $from_request_id > 0 && $prefill_name !== '' ) {
	$neighbourhood['post_title']  = $prefill_name;
	$neighbourhood['post_parent'] = $prefill_district;
}

//echo print_r_pre($user_data);

?>

<?php include('form.php'); ?>

