<?php 
global $wpdb;

$apartment_id = GET_Request('id');


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
    <title>Visit Vietnam Admin | Edit Apartment</title>

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
                        <?php showAlertMessages() ?>
                        <div class="row">
                            <div class="col">
                                <div class="text-right mb-3">
                                    <a href="<?php echo vv_admin_url('apartments') ?>" class="btn btn-sm btn-secondary">Back to Apartments</a>
                                </div>
                            </div>
                        </div>
                        <?php 
                        if($apartment_id > 0){

                            $apartment = $this->apartment_class->get_apartment($apartment_id);
                            ?>
                                    <h3 class="mb-4">Edit Apartment <a href="<?php echo vv_get_apartment_url($apartment) ?>" class="ml-2" target="_blank" ><i class="fa fa-eye"></i></a>
                                    </h3>
                                    <?php include('inc/form-apartments.php'); ?>
                            <?php 
                        }else{
                            echo '<div class="alert alert-danger">Apartment Not Found</div>';  
                        }
                        ?>

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