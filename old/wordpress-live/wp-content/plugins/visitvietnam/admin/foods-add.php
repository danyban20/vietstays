<?php 
global $wpdb;

$food = array();
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
    <title>Visit Vietnam Admin | Add Food</title>

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
                                    <a href="<?php echo vv_admin_url('foods') ?>" class="btn btn-sm btn-secondary">Back to Foods</a>
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-header"><h3>Add Food</h3></div>
                            <div class="card-body">
                                <?php include('inc/form-foods.php'); ?>
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