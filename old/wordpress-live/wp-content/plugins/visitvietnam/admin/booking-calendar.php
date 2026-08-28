<?php
global $wpdb;

$where = ' 1 ';

$filter = array();
if($logged_user_role != 'administrator'){
    $filter["user_id"] = $logged_user_id;
}

$districts      = vv_get_districts();
$apartments     = vv_get_apartments($filter);


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

//echo date("F j, Y",$start_date).' - '.date("F j, Y",$end_date);
//die();

$filter = ['start_date' => $start_date, 'end_date' => $end_date];

if($logged_user_role != 'administrator'){
    $user_apartments    = vv_get_apartments(['user_id' => $logged_user_id]);
    $apartment_ids      = array();
    foreach($user_apartments as $apartment){
        array_push($apartment_ids,gArrayItem($apartment,'ID'));
    }
    $filter['apartment_ids'] = $apartment_ids;
}

$bookings = $this->booking_class->get_bookings($filter);

$colors = ['#93D050','#03A9F4','#F44336','#9C27B0','#cddc39','#03a9f4','#E91E63'];

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
    .booking_calender .apartment_name{
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



                                    <?php

                                    //echo print_r_pre($bookings);

                                    $weekdays   = array("Sun","Mon","Tue","Wed","Thu","Fri","Sat");
                                    $day_start  = date("w",mktime(0,0,0,$month,1,$year));
                                    $num_days   = date("t",mktime(0,0,0,$month,1,$year));
                                    $cur_day    = date("j");
                                    $cur_month  = date("n");
                                    $cur_year   = date("Y");
                                    $day        = 1;



                                    $prev_month = $month - 1;
                                    $prev_year  = $year;
                                    if($month == 1){
                                        $prev_month = 12;
                                        $prev_year  = $year-1;
                                    }

                                    $next_month = $month + 1;
                                    $next_year  = $year;
                                    if($month == 12){
                                        $next_month = 1;
                                        $next_year = $year+1;
                                    }

                                    ?>
                                    <div class="booking_calendar_actions row mb-4">
                                        <div class="col-4 text-left">
                                            <a href="<?php echo vv_admin_url()?>booking-calendar?month=<?php echo $prev_month.'-'.$prev_year ?>" class="btn btn-sm btn-info" >
                                                <i class="fa fa-caret-left"></i> <?php echo date("M Y",mktime(0,0,0,$prev_month,1,$prev_year)) ?>
                                            </a>
                                        </div>
                                        <div class="col-4 text-center">
                                            <a href="#" class="" data-value="<?php echo $month ?>" ><h3><?php echo date("F",mktime(0,0,0,$month,1,$year)) ?></h3></a>
                                        </div>
                                        <div class="col-4 text-right">
                                            <a href="<?php echo vv_admin_url()?>booking-calendar?month=<?php echo $next_month.'-'.$next_year ?>" class="btn btn-sm btn-info" >
                                                <?php echo date("M Y",mktime(0,0,0,$next_month,1,$next_year)) ?> <i class="fa fa-caret-right"></i>
                                            </a>
                                        </div>
                                    </div>
                                    <div style="width:100%;overflow-x:auto">
                                        <table class="booking_calender" >
                                            <tr>
                                                <th>&nbsp;</th>
                                                <?php 
                                                for($day = 1; $day <= $num_days; $day++){
                                                    echo '<th>'.$day.'</th>';
                                                }
                                                ?>
                                            </tr>

                                            <?php 
                                            foreach($districts as $district){
                                                foreach($apartments as $apartment){
                                                    if($apartment['district'] == $district['district_id']){
                                                        $color_index = -1;
                                                        $prev_book_id = 0;
                                                        ?>
                                                        <tr>
                                                            <td class="apartment_name"><?php echo stripslashes($apartment['name'].', '.$district['name']) ?></td>
                                                            <?php 
                                                            $dow = 1;
                                                            for($day = 1; $day <= $num_days; $day++){

                                                            
                                                                
                                                                if(GET_Request('curday') > 0 )  $curday = GET_Request('curday');
                                                                else                        $curday = mktime(0,0,0);
                                                                
                                                                if($curday == mktime(0,0,0,$month,$day,$year)) $today = 'curday';
                                                                else $today = '';
                                                                
                                                                
                                                                echo '<td valign="top" ';

                                                                $html = '&nbsp;';

                                                                $class = ' text-center text-white ';
                                                                $atts = '';

                                                                foreach($bookings as $booking){
                                                                    if($booking['apartment_id'] == $apartment['ID']){
                                                                        $check_in_date  = date("m-d-Y",$booking['check_in_date']);
                                                                        $check_out_date = date("m-d-Y",$booking['check_out_date']);
                                                                        $cdate          = date("m-d-Y",mktime(0,0,0,$month,$day,$year));
                                                                        if($cdate >= $check_in_date && $cdate < $check_out_date){

                                                                            if($prev_book_id != $booking['ID']){    
                                                                                $color_index++;
                                                                                if($color_index == count($colors)) $color_index = 0;
                                                                            }

                                                                            $class  .= 'booked';
                                                                            $atts   .= '  style="background-color:'.gArrayItem($colors,$color_index).'" ';
                                                                            $atts   .= ' data-toggle="tooltip" data-title="'.$booking['firstname'].' '.$booking['lastname'].'" ';
                                                                            
                                                                            $prev_book_id = $booking['ID'];

                                                                            $html = '<a href="#" class="text-white" data-toggle="modal" data-target="#bookingModal'.$booking['ID'].'">'.substr($booking['firstname'],0,1).substr($booking['lastname'],0,1).'</a>';

                                                                        }

                                                                    }
                                                                }

                                                                echo ' class="'.$class.'" '.$atts.' >'.$html.'</td>';

                                                            
                                                                echo '</td>';   
                                                            }
                                                            ?>
                                                        </tr>
                                                        <?php 
                                                    }
                                                }
                                            }
                                            ?>
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


    <?php
    include('inc/booking-modal.php');
    ?>


</body>

</html>
<!-- end document-->