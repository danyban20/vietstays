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

$start_date = date("Y-m-d",mktime(0,0,0,$month,1,$year));
$end_date   = date("Y-m-d",mktime(23,59,59,$month,date('t',$start_date),$year));

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

//echo print_r_pre($bookings);

$colors = ['#93D050','#03A9F4','#F44336','#9C27B0','#cddc39','#03a9f4','#E91E63'];


ob_start();
include('calendar-styles.php');
$header_codes .= ob_get_clean();
?>

<div class="row">
    <div class="col-md-8">
        <h1 class="mb-4"><?php vv_e( 'Booking Calendar' ); ?></h1>
    </div>
    <div class="col-md-4">
        <?php if($logged_user_role == 'administrator'){ ?>
            <div class="text-right mb-3">
                <a href="<?php echo vv_admin_url('booking/add') ?>" class="btn btn-sm btn-primary"><?php vv_e( 'Add Booking' ); ?></a>
            </div>
        <?php } ?>
    </div>
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
            <a href="<?php echo vv_admin_url()?>booking/calendar?month=<?php echo $prev_month.'-'.$prev_year ?>" class="btn btn-sm btn-info" >
                <i class="fa fa-caret-left"></i> <?php echo date("M Y",mktime(0,0,0,$prev_month,1,$prev_year)) ?>
            </a>
        </div>
        <div class="col-4 text-center">
            <a href="#" class="" data-value="<?php echo $month ?>" ><h3><?php echo date("F",mktime(0,0,0,$month,1,$year)) ?></h3></a>
        </div>
        <div class="col-4 text-right">
            <a href="<?php echo vv_admin_url()?>booking/calendar?month=<?php echo $next_month.'-'.$next_year ?>" class="btn btn-sm btn-info" >
                <?php echo date("M Y",mktime(0,0,0,$next_month,1,$next_year)) ?> <i class="fa fa-caret-right"></i>
            </a>
        </div>
    </div>
    <div style="width:100%;overflow-x:auto">
        <table class="booking_calender mb-2" >
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
                    if($apartment['district'] == $district['ID']){
                        $color_index = -1;
                        $prev_book_id = 0;
                        ?>
                        <tr>
                            <td class="apartment_name"><?php echo stripslashes($apartment['name'].', '.$district['post_title']) ?></td>
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
                                        $check_in_date  = $booking['check_in_date'];
                                        $check_out_date = $booking['check_out_date'];
                                        $cdate          = date("Y-m-d 00:00:00",mktime(0,0,0,$month,$day,$year));
                                        if($cdate >= $check_in_date && $cdate <= $check_out_date){

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
<?php
ob_start();
include('modal.php');
$footer_codes .= ob_get_clean();
