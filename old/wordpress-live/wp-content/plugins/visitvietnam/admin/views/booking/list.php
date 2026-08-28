<?php
global $wpdb;

if ( $logged_user_role === 'partner' ) {
	include __DIR__ . '/list-host.php';
	return;
}

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

$filter = $_GET;

$filter['per_page']     = 30;
$filter['pgnum']        = 1;
$filter['return_total'] = 1;

if($logged_user_role != 'administrator'){
    $filter['apartment_owner'] = $logged_user_id;
}

$data       = $this->booking_class->get_bookings($filter);
$bookings   = gArrayItem($data,'bookings');
$total_rows = gArrayItem($data,'total_rows');

$delete_booking_confirm = esc_js( vv__( 'Delete this booking?' ) );

?>
<div class="row">
    <div class="col-md-8">
        <h1 class="mb-4"><?php vv_e( 'Booking' ); ?></h1>
    </div>
    <div class="col-md-4">
        <?php if($logged_user_role == 'administrator' || $logged_user_role == 'partner'){ ?>
            <div class="text-right mb-3">
                <a href="<?php echo vv_admin_url('booking/add') ?>" class="btn btn-sm btn-primary"><?php vv_e( 'Add Booking' ); ?></a>
            </div>
        <?php } ?>
    </div>
</div>
<div class="au-card" style="overflow: auto;">
    <?php include('search.php') ?>
    <hr>
    <div class="table-responsive table--no-card m-b-30">
        <table class="table table-borderless table-striped table-earning bookings_list">
            <thead>
                <tr>
                    <th style="width:20px">
                        <input name="checkAll" type="checkbox" value="1" >
                    </th>
                    <th class="text-left"><?php vv_e( 'Booking #' ); ?></th>
                    <th class="text-left"><?php vv_e( 'Check In/Out' ); ?></th>
                    <th class="text-left"><?php vv_e( 'District' ); ?></th>
                    <th class="text-left"><?php vv_e( 'Apartment' ); ?></th>
                    <th class="text-left"><?php vv_e( 'Customer' ); ?></th>
                    <th><?php vv_e( 'Amount' ); ?></th>
                    <th><?php vv_e( 'Status' ); ?></th>
                    <th style="width:100px"><?php vv_e( 'Action' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php 
                foreach($bookings as $booking){

                    $check_in_date = POST_Request('check_in_date');
                    if($check_in_date == '') $check_in_date = date("Y-m-d");

                    $check_in_time =  strtotime($check_in_date);
                    $dayofweek = date("w",$check_in_time);

                    $booked_dates = vv_get_date_range_text($booking['check_in_date'], $booking['check_out_date']);

                    $district_name = vv_get_district_display_name($booking['district_id']);

                    ?>
                    <tr data-booking_id="<?php echo $booking['ID'] ?>">
                        <td>
                            <input type="checkbox" name="booking_id[]" id="booking_<?php echo $booking['ID'] ?>" value="<?php echo $booking['ID'] ?>" required>
                            <input type="hidden" name="price_<?php echo $booking['ID'] ?>" value="<?php echo $price ?>" >
                        </td>
                        <td><a href="#" data-toggle="modal" data-target="#bookingModal<?php echo $booking['ID'] ?>" ><?php echo vv_get_booking_num($booking) ?></a></td>
                        <td><?php echo $booked_dates ?></td>
                        <td><?php echo stripslashes($district_name) ?></td>
                        <td><?php echo stripslashes($booking['apartment_name']) ?></td>
                        <td>
                            <div><?php echo $booking['firstname'].'  '.$booking['lastname'] ?></div>
                        </td>
                        <td><?php echo vv_number_format($booking['total'],true) ?></td>
                        <td><?php echo $booking['status'] ?></td>
                        <td class="text-right pr-2">
                            <a href="#" data-toggle="modal" data-target="#bookingModal<?php echo $booking['ID'] ?>" class="mr-1" target="_blank" ><i class="fa fa-eye"></i>
                            <a href="<?php echo vv_admin_url('booking/edit/?id='.$booking['ID']) ?>" ><i class="fa fa-pencil-alt"></i></a>
                            <a href="<?php echo vv_admin_url('?vv_action=delete-booking&id='.$booking['ID']) ?>" onclick="return confirm('<?php echo $delete_booking_confirm; ?>')" ><i class="fa fa-trash"></i></a>
                        </td>
                    </tr>
                    <?php 
                }
                ?>
            </tbody>
        </table>
    </div>
  </div>
</div>

<?php
ob_start();
    ?>
    <script type="text/javascript">
        $(document).ready(function (){
            $('input[name="checkAll"]').click(function (){
                $('input[name="booking_id[]"]').prop('checked',$(this).is(':checked'));
            });
        });
    </script>

    <?php
    include('modal.php');
$footer_codes .= ob_get_clean();
