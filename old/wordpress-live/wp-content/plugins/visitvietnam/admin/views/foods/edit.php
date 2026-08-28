<?php 
global $wpdb;

$food_id = GET_Request('id');


?>
<div class="row">
    <div class="col-md-8"><h1><?php vv_e( 'Edit Food' ); ?></h1>
    <div class="col-md-4">
        <div class="text-right mb-3">
            <a href="<?php echo vv_admin_url('foods') ?>" class="btn btn-sm btn-secondary"><?php vv_e( 'Back to Foods' ); ?></a>
        </div>
    </div>
</div>
<?php 
if($food_id > 0){

    $food = $this->foods_class->get_food($food_id);
    ?>
    <div class="card">
        <div class="card-header"><h3><?php vv_e( 'Edit Food' ); ?></h3></div>
        <div class="card-body">
            <?php include('form.php'); ?>
        </div>
    </div>
    <?php 
}else{
    echo '<div class="alert alert-danger">' . esc_html( vv__( 'Food Not Found' ) ) . '</div>';  
}
?>
