<?php
global $wpdb;


$where = ' 1 ';

if($logged_user_role != 'administrator'){
    $where = " user_id =  ".$logged_user_id;
}


if(GET_Request('curday') != ''){
    $time   = GET_Request('curday');
    $month  = date("n",$time);
    $year   = date("Y",$time);
    $day    = date("j",$time);
}elseif(GET_Request('month') != ''){
    $tmp    = explode("-",$_GET['month']);
    $month  = $tmp[0];
    $year   = $tmp[1];
}else{
    $month  = date("n");
    $year   = date("Y");
    $day    = date("j");

    //echo $day.' - '.date("t");
    if( $day == date("t") ) $month++; // SET TO NEXT MONTH IF THE NEXT DAY IS LAST DAY OF MONTH BECAUSE ITS BLOCK
}


$start_date = mktime(0,0,0,$month,1,$year);
$end_date   = mktime(23,59,59,$month,date('t',$start_date),$year);

$filter = ['per_page' => 30, 'pgnum' => 1, 'return_total' => 1];

if($logged_user_role != 'administrator'){
    $user_apartments    = vv_get_apartments(['user_id' => $logged_user_id]);
    $apartment_ids      = array();
    foreach($user_apartments as $apartment){
        array_push($apartment_ids,gArrayItem($apartment,'ID'));
    }
    $filter['apartment_ids'] = $apartment_ids;
}

$data       = $this->booking_class->get_bookings($filter);
$bookings   = gArrayItem($data,'bookings');
$total_rows = gArrayItem($data,'total_rows');

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
    <title>Visit Vietnam Admin | Booking</title>

    <?php include('inc/head_codes.php'); ?>

    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

    <style>
    .booking_calender{
        border-top:1px solid #024041;
        border-left:1px solid #024041;
        width:100%;
    }
    .booking_calender th{
        border-right:1px solid #024041;
        border-bottom: 1px solid #024041;
        text-align: center;
        padding:10px;
        background: #024041;
        color:#fff;
        font-size:10px;
    }
    .booking_calender .booking_name{
        text-align: left;
        font-weight: normal;
        width:300px;
        min-width: 300px;
        font-size: 12px;
        background: #fff;
        color:#000;
    }
    .booking_calender td{
        border-right:1px solid #024041;
        border-bottom: 1px solid #024041;;
        overflow: hidden;
        width:40px;
    }
    .booking_calender td > div{
        font-size:12px;
    }
    .booking_calender td.booked{
        background: #93D050;
    }

    </style>
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
                                <div class="text-right mb-3">
                                    <a href="<?php echo vv_admin_url('booking-add') ?>" class="btn btn-sm btn-primary">Add Booking</a>
                                </div>
                              <div class="au-card" style="overflow: auto;">
                                <div class="table-responsive table--no-card m-b-30">
                                    <table class="table table-borderless table-striped table-earning bookings_list">
                                        <thead>
                                            <tr>
                                                <th style="width:20px">
                                                    <input name="checkAll" type="checkbox" value="1" >
                                                </th>
                                                <th class="text-left">Booking #</th>
                                                <th class="text-left">Check In/Out</th>
                                                <th class="text-left">District</th>
                                                <th class="text-left">Apartment</th>
                                                <th class="text-left">Customer</th>
                                                <th>Amount</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            foreach($bookings as $booking){

                                                $check_in_date = POST_Request('check_in_date');
                                                if($check_in_date == '') $check_in_date = date("Y-m-d");

                                                $check_in_time =  strtotime($check_in_date);
                                                $dayofweek = date("w",$check_in_time);

                                                ?>
                                                <tr data-booking_id="<?php echo $booking['ID'] ?>">
                                                    <td>
                                                        <input type="checkbox" name="booking_id[]" id="booking_<?php echo $booking['ID'] ?>" value="<?php echo $booking['ID'] ?>" required>
                                                        <input type="hidden" name="price_<?php echo $booking['ID'] ?>" value="<?php echo $price ?>" >
                                                    </td>
                                                    <td><a href="#" data-toggle="modal" data-target="#bookingModal<?php echo $booking['ID'] ?>" ><?php echo vv_get_booking_num($booking) ?></a></td>
                                                    <td><?php echo date("m/d/Y",$booking['check_in_date']).' - '.date("m/d/Y",$booking['check_out_date']) ?></td>
                                                    <td><?php echo stripslashes($booking['district_name']) ?></td>
                                                    <td><?php echo stripslashes($booking['apartment_name']) ?></td>
                                                    <td>
                                                        <div><?php echo $booking['firstname'].'  '.$booking['lastname'] ?></div>
                                                    </td>
                                                    <td><?php echo '$'.number_format($booking['total'],2) ?></td>
                                                    <td><?php echo $booking['status'] ?></td>
                                                </tr>
                                                <?php 
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
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


    <script type="text/javascript">
        $(document).ready(function (){
            $('input[name="checkAll"]').click(function (){
                $('input[name="booking_id[]"]').prop('checked',$(this).is(':checked'));
            });
        });
    </script>

    <?php
    include('inc/booking-modal.php');
    ?>

</body>

</html>
<!-- end document-->