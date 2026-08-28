<?php


$colors = ['#93D050','#03A9F4','#F44336','#9C27B0','#cddc39','#03a9f4','#E91E63'];



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
        <a href="<?php echo vv_admin_url()?>booking?month=<?php echo $prev_month.'-'.$prev_year ?>" class="btn btn-sm btn-info" >
            <i class="fa fa-caret-left"></i> <?php echo date("M Y",mktime(0,0,0,$prev_month,1,$prev_year)) ?>
        </a>
    </div>
    <div class="col-4 text-center">
        <a href="#" class="" data-value="<?php echo $month ?>" ><h3><?php echo date("F",mktime(0,0,0,$month,1,$year)) ?></h3></a>
    </div>
    <div class="col-4 text-right">
        <a href="<?php echo vv_admin_url()?>booking?month=<?php echo $next_month.'-'.$next_year ?>" class="btn btn-sm btn-info" >
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
                if($apartment['district'] == $district['ID']){
                    $color_index = -1;
                    $prev_book_id = 0;
                    ?>
                    <tr>
                        <td class="apartment_name"><?php echo $apartment['name'].', '.$district['post_title'] ?></td>
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
                                    $cdate          = date("Y-m-d",mktime(0,0,0,$month,$day,$year));
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
<?php   
