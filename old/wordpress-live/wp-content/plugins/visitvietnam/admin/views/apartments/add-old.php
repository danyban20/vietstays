<?php 
global $wpdb;

$apartment = array();
?>

    <div class="row">
        <div class="col">
            <div class="text-right mb-3">
                <a href="<?php echo vv_admin_url('apartments') ?>" class="btn btn-sm btn-secondary"><?php vv_e( 'Back to Apartments' ); ?></a>
            </div>
        </div>
    </div>
    <h3 class="mb-4"><?php vv_e( 'Add Apartment' ); ?></h3>
    <?php 
    if($logged_user_role == 'administrator'){
        include('form-old.php'); 
    }else{
        echo '<div class="alert alert-danger">' . esc_html( vv__( 'You do not have access to this page.' ) ) . '</div>';
    }
    ?>
