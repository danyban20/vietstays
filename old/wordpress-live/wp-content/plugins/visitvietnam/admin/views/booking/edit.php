<?php
$booking = $this->booking_class->get_booking(GET_Request('id'));
?>
    <div class="row">
        <div class="col-md-6"><h1><?php vv_e( 'Edit Booking' ); ?> #<?php echo vv_get_booking_num($booking) ?></h1></div>
        <div class="col-md-6">
            <div class="text-right mb-3">
                <a href="<?php echo vv_admin_url('booking') ?>" class="btn btn-sm btn-primary"><?php vv_e( 'Back to Booking' ); ?></a>
            </div>
        </div>
    </div>
    <div class="au-card">
        <?php
        if(gArrayItem($booking,'ID') > 0){
            include('form.php');
        }else{
            echo '<div class="alert alert-danger">' . esc_html( vv__( 'Invalid Booking #' ) ) . '</div>';
        }
        ?>
    </div>
