<?php 
global $wpdb;

$food = array();
?>
<div class="row">
    <div class="col">
        <div class="text-right mb-3">
            <a href="<?php echo vv_admin_url('foods') ?>" class="btn btn-sm btn-secondary"><?php vv_e( 'Back to Foods' ); ?></a>
        </div>
    </div>
</div>
<div class="card">
    <div class="card-header"><h3><?php vv_e( 'Add Food' ); ?></h3></div>
    <div class="card-body">
        <?php include('form.php'); ?>
    </div>
</div>
