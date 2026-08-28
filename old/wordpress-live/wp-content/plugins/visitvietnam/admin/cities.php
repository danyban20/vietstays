<?php 
global $wpdb;

$filter = [];


$cities     = vv_get_cities($filter);


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
    <title>Visit Vietnam Admin | Cities</title>

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
                        <h1 class="mb-4">Cities</h1>
                        <?php showAlertMessages() ?>
                        <div class="form-wrap">
                                <form method="post">
                                    <input type="hidden" name="action" value="vv_save_city" >
                                    <div class="table-responsive table--no-card m-b-30">
                                        <table class="table table-borderless table-striped table-earning">
                                            <thead>
                                                <tr>
                                                    <th>Name</th>
                                                    <th class="text-center" style="width:50px;">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php 
                                                foreach($cities as $city){
                                                    ?>
                                                    <tr>
                                                        <td><input type="text" name="name[<?php echo $city['city_id'] ?>]" value="<?php echo $city['name'] ?>" class="form-control form-control-sm" ></td>
                                                        <td class="text-right p-2">
                                                            <a href="<?php echo vv_admin_url('?action=delete-city&id='.$city['city_id']) ?>" onclick="return confirm('Delete this city?')" ><i class="fa fa-trash"></i></a>
                                                        </td>
                                                    </tr>
                                                    <?php 
                                                }
                                                ?>
                                                <tr>
                                                    <td><input type="text" name="name['new']" value="" placeholder="Add New" class="form-control form-control-sm" ></td>
                                                    <td class="text-right p-2">&nbsp;</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="mt-4 mb-4">
                                        <button type="submit" class="btn btn-primary">Save Cities</button>
                                    </div>
                                </form>
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