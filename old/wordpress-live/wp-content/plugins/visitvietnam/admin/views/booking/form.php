<?php
$districts      = vv_get_districts();
$action         = POST_Request('action');
$step           = intval(GET_Request('step'));
$booking_id     = GET_Request('id');
$booking_items  = [];

if($step == 0) $step = 1;
if($booking_id == '') $booking_id = 'new';


if(intval($booking_id) > 0){

    $booking_items = $this->booking_class->get_booking_items($booking_id);

    //echo print_r_pre($booking_items);

    if($step == 1){

        $apartment = $this->apartment_class->get_apartment(gArrayItem($booking,'apartment_id'));

        if(gArrayItem($apartment,'ID') > 0){

            $booking['district_id'] = gArrayItem($apartment,'district'); // GET THE LOCATION FROM APARTMENT

            $city_id = get_post_meta($booking['district_id'],'city',true);

            $district = $this->district_class->get_district($booking['district_id']);

            if(gArrayItem($district,'ID') > 0){
                $booking['parent_district'] = gArrayItem($district,'post_parent');
            }

            $booking['city_id'] = $city_id;

        }

        foreach($booking_items as $booking_item){
            if($booking_item['item_type'] == 'service' && $booking_item['code'] == 'airport_pickup'){
                $booking['airport_pickup']      = 1;
                $booking['airport_pickup_cost'] = $booking_item['price'];
            }

            if($booking_item['item_type'] == 'service' && $booking_item['code'] == 'cleaning_fee'){
                $booking['num_cleaning']        = $booking_item['qty'];
                $booking['cleaning_fee']        = $booking_item['price'];
            }

            if($booking_item['item_type'] == 'service' && $booking_item['code'] == 'scooter_rental'){
                $booking['num_scooter_rental']  = $booking_item['qty'];
                $booking['scooter_rental_fee']  = $booking_item['price'];
            }

        }


        $_SESSION['BOOKING_DATA_ADMIN'] = $booking;
        //$step = 2; // SKIP TO STEP 2 WHEN EDITING
    }else{
        $booking    = gArrayItem($_SESSION,'BOOKING_DATA_ADMIN');
    }


}else{
    $booking    = gArrayItem($_SESSION,'BOOKING_DATA_ADMIN');
    if($step == 1){
        $_SESSION['BOOKING_DATA_ADMIN'] = [];
        $booking = [];
    }
}



//echo print_r_pre($booking);

//echo 'step = '.$step;

include('form-step1.php');

if($step == 2){

    $filter = $booking;
    $filter['apartment_id'] = 0;

    if(gArrayItem($booking,'ID') > 0) $filter['exclude_ids'] = [$filter['ID']];

    //echo print_r_pre($filter);
    $apartments = $this->booking_class->search_apartment_bookings($filter);

    if(count($apartments)){
        include('form-step2.php');
    }else{
        echo '<div class="alert alert-danger">' . esc_html( vv__( 'No available apartment found' ) ) . '</div>';
    }
}else{
    if($step == 3 || $booking_id > 0){
        include('form-step3.php');
    }
}


ob_start();
?>
<style type="text/css">
.table thead th{
    border-bottom: 0;
} 
</style>
<?php
$header_codes .= ob_get_clean();

