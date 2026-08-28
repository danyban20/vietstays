<?php 
global $wpdb;


$room = $this->room_class->get_room(GET_Request('id'));
if(intval(gArrayItem($room,'ID')) == 0){
    die( esc_html( vv__( 'Invalid Room ID' ) ) );
}


$apartment = $this->apartment_class->get_apartment(gArrayItem($room,'apartment_id'));
if(intval(gArrayItem($apartment,'ID')) == 0){
    die( esc_html( vv__( 'Invalid Apartment ID' ) ) );
}
?>

<div class="row">
    <div class="col-md-7">
        <h1><?php vv_e( 'Edit Room:' ); ?> <span class="text-info"><?php echo esc_html( gArrayItem($room,'name') ) ?></span></h1>
    </div>
    <div class="col-md-5">
        <div class="text-right mb-3">
            <a href="<?php echo vv_admin_url('rooms?apartment='.$room['apartment_id']) ?>" class="btn btn-sm btn-secondary"><?php vv_e( 'Back to Rooms List' ); ?></a>
        </div>
    </div>
</div>
<div class="mt-4">
    <div class="card">
        <div class="card-body">
            <?php include('form.php'); ?>
        </div>
    </div>
</div>
