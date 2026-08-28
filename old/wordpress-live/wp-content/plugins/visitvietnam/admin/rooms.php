<?php 
global $wpdb;

$filter = ['apartment_id' => GET_Request('apartment')];

$apartment = $this->apartment_class->get_apartment(GET_Request('apartment'));

if($logged_user_role == 'partner'){

    if($apartment['user_id'] != $logged_user_id){        
        die('Invalid URL');
    }

}

$rooms = $this->room_class->get_rooms($filter);


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
    <title>Visit Vietnam Admin | Rooms</title>

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
                        <div class="row mb-4">
                            <div class="col-md-8">
                                <h1>Rooms of Apartment: <span class="text-info"><?php echo gArrayItem($apartment,'name') ?></span></h1>
                            </div>
                            <div class="col-md-4">
                                <div class="text-right mb-3">
                                    <a href="<?php echo vv_admin_url('apartments') ?>" class="btn btn-sm btn-secondary">Back to Apartments List</a>
                                    <a href="<?php echo vv_admin_url('rooms-add/?apartment='.$apartment['ID']) ?>" class="btn btn-sm btn-primary">Add Room</a>
                                </div>
                            </div>
                        </div>
                        <?php showAlertMessages() ?>
                        <div class="row">
                            <div class="col">
                                <div class="table-responsive table--no-card m-b-30">
                                    <table class="table table-borderless table-striped table-earning">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Adult</th>
                                                <th>Child</th>
                                                <th>Extra</th>
                                                <th>Price</th>
                                                <th class="text-right">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            foreach($rooms as $room){
                                                $pricing = json_decode($room['pricing'],true);
                                                if(!is_array($pricing)) $pricing = array();

                                                if(count($pricing) > 1){
                                                    for($i = 0; $i < count($pricing); $i++){
                                                        for($x = 0; $x < count($pricing)-1; $x++){
                                                            if($pricing[$x] > $pricing[$x+1]){
                                                                $tmp = $pricing[$x];
                                                                $pricing[$x] = $pricing[$x+1];
                                                                $pricing[$x+1] = $tmp;
                                                            }
                                                        }
                                                    }
                                                    if(gArrayItem($pricing,0) != gArrayItem($pricing,count($pricing)-1)){
                                                        $price_str = '$'.gArrayItem($pricing,0).' - $'.gArrayItem($pricing,count($pricing)-1);
                                                    }else{
                                                        $price_str = '$'.gArrayItem($pricing,0);
                                                    }
                                                }else{
                                                    $price_str = '$'.gArrayItem($pricing,0);
                                                }



                                                ?>
                                                <tr>
                                                    <td><a href="<?php echo vv_admin_url('rooms-edit/?id='.$room['ID']) ?>" ><?php echo $room['name'] ?></td></td>
                                                    <td><?php echo $room['adults'] ?></td>
                                                    <td><?php echo $room['children'] ?></td>
                                                    <td><?php echo $room['extra'] ?></td>
                                                    <td><?php echo $price_str ?></td>
                                                    <td class="text-right">
                                                        <a href="<?php echo vv_admin_url('rooms-edit/?id='.$room['ID']) ?>" ><i class="fa fa-pencil-alt"></i></a>
                                                        <a href="<?php echo vv_admin_url('?action=delete-room&id='.$room['ID']) ?>" onclick="return confirm('Delete this room?')" ><i class="fa fa-trash"></i></a>
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