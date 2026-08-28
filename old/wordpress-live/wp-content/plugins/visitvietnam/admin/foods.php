<?php 
global $wpdb;

$filter = [];


$foods      = vv_get_foods($filter);
$categories = vv_get_food_categories();

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
    <title>Visit Vietnam Admin | Foods</title>

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
                            <div class="col-md-8">
                                <h1 class="mb-4">Foods</h1>
                            </div>
                            <div class="col-md-4 text-right mb-4">
                                <a href="<?php echo vv_admin_url('foods-add') ?>" class="btn btn-sm btn-primary">Add Food</a>
                            </div>
                        </div>
                        <?php showAlertMessages() ?>
                        <div class="row">
                            <div class="col">
                                <div class="table-responsive table--no-card m-b-30">
                                    <table class="table table-borderless table-striped table-earning">
                                        <thead>
                                            <tr>
                                                <th style="width:100px">Image</th>
                                                <th>Name</th>
                                                <th style="width:200px">Category</th>
                                                <th style="width:100px">Price</th>
                                                <th class="text-center" style="width:100px;">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            foreach($foods as $food){
                                                $category_names = array();
                                                $food_cats = explode("|,|",trim($food['category'],'|'));
                                                foreach($food_cats as $food_cat){
                                                    if(intval($food_cat) > 0){
                                                        foreach($categories as $category){
                                                            if(gArrayItem($category,'food_id') == $food_cat){
                                                                array_push($category_names,gArrayItem($category,'name'));
                                                            }
                                                        }
                                                    }
                                                }
                                                ?>
                                                <tr>
                                                    <td>
                                                        <?php 
                                                        if(gArrayItem($food,'image') != ''){
                                                            ?>
                                                            <img src="<?php echo $food['image'] ?>" >
                                                            <?php 
                                                        }else{
                                                            ?>
                                                            &nbsp;
                                                            <?php 
                                                        }
                                                        ?>
                                                    </td>
                                                    <td><a href="<?php echo vv_admin_url('foods-edit/?id='.$food['food_id']) ?>" ><?php echo $food['name'] ?></a></td>
                                                    <td><?php echo implode(", ",$category_names); ?></td>
                                                    <td>$<?php echo number_format($food['price'],2) ?></td>
                                                    <td class="text-right p-2">
                                                        <a href="<?php echo vv_admin_url('foods-edit/?id='.$food['food_id']) ?>" ><i class="fa fa-pencil-alt"></i></a>
                                                        <a href="<?php echo vv_admin_url('?action=delete-food&id='.$food['food_id']) ?>" onclick="return confirm('Delete this food?')" ><i class="fa fa-trash"></i></a>
                                                    </td>
                                                </tr>
                                                <?php 
                                            }
                                            ?>
                                        </tbody>
                                    </table>
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

    <!-- full calendar requires moment along jquery which is included above -->


</body>

</html>
<!-- end document-->