<?php 
global $wpdb;


$room = $this->room_class->get_room(GET_Request('id'));
if(intval(gArrayItem($room,'ID')) == 0){
    die('Invalid Room ID');
}


$apartment = $this->apartment_class->get_apartment(gArrayItem($room,'apartment_id'));
if(intval(gArrayItem($apartment,'ID')) == 0){
    die('Invalid Apartment ID');
}
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
    <title>Visit Vietnam Admin | Edit Room</title>

    <?php include('inc/head_codes.php'); ?>

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
                        <div class="row">
                            <div class="col-md-7">
                                <h1>Edit Room: <span class="text-info"><?php echo gArrayItem($room,'name') ?></span></h1>
                            </div>
                            <div class="col-md-5">
                                <div class="text-right mb-3">
                                    <a href="<?php echo vv_admin_url('rooms?apartment='.$room['apartment_id']) ?>" class="btn btn-sm btn-secondary">Back to Rooms List</a>
                                </div>
                            </div>
                        </div>
                        <?php showAlertMessages() ?>
                        <div class="mt-4">
                                <div class="card">
                                    <div class="card-body">
                                        <?php include('inc/form-rooms.php'); ?>
                                    </div>
                                </div>

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

    <!-- full calendar requires moment along jquery which is included above -->


</body>

</html>
<!-- end document-->