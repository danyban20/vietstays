<?php
global $wpdb;
$districts = vv_get_districts();

$action = POST_Request('action');

if($action == 'booking-search' || $action == 'booking-save'){

    if(gArrayItem($booking,'ID') > 0) $_POST['exclude_ids'] = [$booking['ID']];

    $apartments = $this->booking_class->search_apartment_bookings($_POST);

    //echo print_r_pre($apartments);


    //die();

    if(count($apartments) > 0){
        include('form-booking-step2.php');
    }else{

        echo '<div class="alert alert-danger">No Apartment found. </div>';

        include('form-booking-step1.php');
    }

}else{
    include('form-booking-step1.php');
}