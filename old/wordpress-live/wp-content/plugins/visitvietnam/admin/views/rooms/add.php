<?php 
global $wpdb;

$apartment = $this->apartment_class->get_apartment(GET_Request('apartment'));
if(intval(gArrayItem($apartment,'ID')) == 0){
    die( esc_html( vv__( 'Invalid Apartment ID' ) ) );
}
$room = array();

?>
<div class="row">
    <div class="col-md-7">
        <h1><?php vv_e( 'Add Room' ); ?></h1>
    </div>
    <div class="col-md-5">
        <div class="text-right mb-3">
            <a href="<?php echo vv_admin_url('rooms/?apartment='.$apartment['ID']) ?>" class="btn btn-sm btn-secondary"><?php vv_e( 'Back to Rooms List' ); ?></a>
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
