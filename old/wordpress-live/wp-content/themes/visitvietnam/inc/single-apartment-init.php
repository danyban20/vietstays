<?php 
global $visitVietnam;

if(POST_Request('apartment_id') > 0){
	$apartment = $visitVietnam->apartment_class->get_apartment(POST_Request('apartment_id'));
}else{
	$apartment = $visitVietnam->apartment_class->get_apartment_by_post(get_the_ID());
}


if(intval(gArrayItem($apartment,'ID')) == 0 || ! vvApartments::is_publicly_visible( $apartment )){
    global $wp_query;
    $wp_query->set_404();
    status_header( 404 );
    get_template_part( 404 ); exit();    
}

$images = vv_get_apartment_images($apartment);
//echo print_r_pre($images);
$facilities = vv_get_facilities();

$booking = json_decode(gArrayItem($_SESSION,'BOOKING_DATA'),true);
if(!is_array($booking)) $booking = [];

if($_POST){
	foreach($_POST as $i => $v){
		$booking[$i] = $v;
	}

	$_SESSION['BOOKING_DATA'] = json_encode($booking);
	
}


if(gArrayItem($booking,'check_in_date') == '') $booking['check_in_date'] = time();
if(gArrayItem($booking,'check_out_date') == '') $booking['check_out_date'] = strtotime("+1 Day");



$check_in_date = gArrayItem($booking,'check_in_date');
$check_in_time =  strtotime($check_in_date);
$dayofweek = date("w",$check_in_time);


$pricing = json_decode($apartment['pricing'],true);
if(!is_array($pricing)) $pricing = array();

$price = 0;

if(count($pricing) > 1){
    $price = gArrayItem($pricing,$dayofweek);
}

$discount = $visitVietnam->apartment_class->get_discount_val($apartment['ID'],$check_in_date);
//echo 'discount = '.$discount;

$subtotal = $price;
$save = 0;
