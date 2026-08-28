<?php 
global $wpdb;

$filter = [];

if($logged_user_role == 'partner'){
    $filter["user_id"] = $logged_user_id;
}


$apartments     = vv_get_apartments($filter);
$districts      = vv_get_districts();


$owner_ids = array();
foreach($apartments as $apartment){
    if(!in_array($apartment['user_id'],$owner_ids) && $apartment['user_id'] != 0) array_push($owner_ids,$apartment['user_id']);
}
$users      = array();
foreach($owner_ids as $owner_id){
    array_push($users, get_user_by('ID',$owner_id));
}
//echo print_r_pre($owner_ids);

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
    <title>Visit Vietnam Admin | Dashboard</title>

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
                        <?php showAlertMessages() ?>
                        <div class="row">
                            <div class="col">
                                <div class="text-right mb-3">
                                    <a href="<?php echo vv_admin_url('apartments-add') ?>" class="btn btn-sm btn-primary">Add Apartment</a>
                                </div>
                                <div class="table-responsive table--no-card m-b-30">
                                    <table class="table table-borderless table-striped table-earning">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th style="width:200px;">District</th>
                                                <th style="width:200px;">Owner</th>
                                                <th class="text-center" style="width:100px;"># of Rooms</th>
                                                <th class="text-center" style="width:100px;">Max Guests Allowed</th>
                                                <th class="text-right" style="width:100px;">Pricing</th>
                                                <th class="text-center" style="width:100px;">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            foreach($apartments as $apartment){
                                                $district_name = '';
                                                foreach($districts as $district){
                                                    if($district['ID'] == $apartment['district']) $district_name = $district['name'];
                                                }

                                                $owner = 'Visit Vietnam';
                                                foreach($users as $user){
                                                    if($user->ID == $apartment['user_id']) $owner = $user->display_name;
                                                }

                                                $pricing = json_decode($apartment['pricing'],true);
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
                                                    <td><a href="<?php echo vv_admin_url('apartments-edit/?id='.$apartment['ID']) ?>" ><?php echo $apartment['name'] ?></td></td>
                                                    <td><?php echo $district_name ?></td>
                                                    <td><?php echo $owner ?></td>
                                                    <td class="text-center"><?php echo gArrayItem($apartment,'rooms') ?></td>
                                                    <td class="text-center"><?php echo gArrayItem($apartment,'max_guests') ?></td>
                                                    <td><?php echo $price_str ?></td>
                                                    <td class="text-right pr-2">
                                                        <a href="<?php echo vv_get_apartment_url($apartment) ?>" class="mr-1" target="_blank" ><i class="fa fa-eye"></i>
                                                        <a href="<?php echo vv_admin_url('apartments-edit/?id='.$apartment['ID']) ?>" ><i class="fa fa-pencil-alt"></i></a>
                                                        <a href="<?php echo vv_admin_url('?action=delete-apartment&id='.$apartment['ID']) ?>" onclick="return confirm('Warning! Deleting apartment will delete the rooms under it. Delete this apartment?')" ><i class="fa fa-trash"></i></a>
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