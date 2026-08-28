<?php
global $wpdb;

$pg_title   = 'Edit building';
vv_show_page_title_bar( [
	'pg_title'  => $pg_title,
	'back_link' => vv_admin_url( 'locations/neighbourhoods' ),
	'back_txt'  => 'Back to buildings',
] );
$meta_title = vv_admin_meta_title( $pg_title );

$neighbourhood = vv_get_neighbourhood( GET_Request( 'id' ) );

if ( gArrayItem( $neighbourhood, 'ID' ) > 0 ) {
	include 'form.php';
} else {
	echo '<div class="alert alert-danger">' . esc_html( vv__( 'Invalid URL' ) ) . '</div>';
}
