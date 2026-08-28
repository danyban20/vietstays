<?php
$booking = $this->booking_class->get_booking(GET_Request('id'));
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Required meta tags-->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="Wiise.no">
    <meta name="keywords" content="">

    <!-- Title Page-->
    <title>Visit Vietnam Admin | Edit Booking</title>

    <?php include('inc/head_codes.php'); ?>

    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

</head>

<body class="">
    <div class="page-wrapper">

        <?php include('inc/header-mobile.php'); ?>
        <?php include('inc/sidebar.php'); ?>


        <!-- PAGE CONTAINER-->
        <div class="page-container">
            <?php include('inc/header.php'); ?>
            <!-- MAIN CONTENT-->
            <div class="main-content">
                <div class="section__content section__content--p30">
                    <div class="container-fluid">
                        <?php showSuccessMsg() ?>
                        <div class="row">
                            <div class="col">
                                <div class="row">
                                    <div class="col-md-6"><h1>Edit Booking</h1></div>
                                    <div class="col-md-6">
                                        <div class="text-right mb-3">
                                            <a href="<?php echo vv_admin_url('booking') ?>" class="btn btn-sm btn-primary">Back to Booking</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="au-card">
                                    <?php
                                    if(gArrayItem($booking,'ID') > 0){
                                        include('inc/form-booking.php');
                                    }else{
                                        echo '<div class="alert alert-danger">Invalid Booking #</div>';
                                    }
                                    ?>
                                </div>
                            </div><!-- .col -->
                        </div>
                        <?php include('inc/footer.php'); ?>
                    </div>
                </div>
            </div>
            <!-- END MAIN CONTENT-->
            <!-- END PAGE CONTAINER-->
        </div>

    </div>

    
    <?php 
    include('inc/footer_codes.php');
    ?>


</body>

</html>
<!-- end document-->