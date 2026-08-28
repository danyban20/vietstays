<?php 
global $wpdb;

$apartment_id = GET_Request('id');

?>

    <div class="row">
        <div class="col">
            <div class="text-right mb-3">
                <a href="<?php echo vv_admin_url('apartments') ?>" class="btn btn-sm btn-secondary"><?php vv_e( 'Back to Apartments' ); ?></a>
            </div>
        </div>
    </div>
    <?php 
    if($apartment_id > 0){

        $apartment = $this->apartment_class->get_apartment($apartment_id);
        ?>
                <h3 class="mb-4"><?php vv_e( 'Edit Apartment' ); ?> <a href="<?php echo vv_get_apartment_url($apartment) ?>" class="ml-2" target="_blank" title="<?php echo esc_attr( vv__( 'View' ) ); ?>" ><i class="fa fa-eye"></i></a>
                </h3>
                <?php include('form-old.php'); ?>
        <?php 
    }else{
        echo '<div class="alert alert-danger">' . esc_html( vv__( 'Apartment Not Found' ) ) . '</div>';  
    }
    ?>
