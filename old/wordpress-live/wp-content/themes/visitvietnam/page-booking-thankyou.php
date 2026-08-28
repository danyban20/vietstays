<?php
/**
Template Name: Booking Thank You

*/
$booking_class      = new vvBookings;
$apartment_class    = new vvApartments;
$users_class        = new vvUsers;
$booking_num        = GET_Request('booking');
$booking_id         = vv_get_booking_id_from_num($booking_num);


$booking = $booking_class->get_booking($booking_id);
if(gArrayItem($booking,'ID') > 0){

    //echo print_r_pre($booking);

    $guests = $booking['adults'];
    $guests .= ($booking['adults'] > 1) ? ' Adults, ' : ' Adult, ';
    $guests .= $booking['children'];
    $guests .= ($booking['children'] > 1) ? ' Children' : ' Child';

    if($booking['status'] == 'paid'){
        $status_txt = '<i class="fa fa-check text-success"></i> '.vv_get_status_name_text($booking['status']);
    }else{
        $status_txt = vv_get_status_name_text($booking['status']);
    }


    $check_in_date      = gArrayItem($booking,'check_in_date');
    $check_out_date     = gArrayItem($booking,'check_out_date');

    $checkInDate    = new DateTime($check_in_date);
    $checkOutDate   = new DateTime($check_out_date);

    $interval           = $checkInDate->diff($checkOutDate);
    $nights             = $interval->days;

    $booked_dates       = vv_get_date_range_text($booking['check_in_date'], $booking['check_out_date']);
    $booked_dates       .= ' <span style="font-size:13px">('.$nights;
    $booked_dates       .= ($nights > 1) ? ' Nights' : ' Night';
    $booked_dates       .= ')</span>';


    $apartment_id   = $booking['apartment_id'];
    $apartment      = $apartment_class->get_apartment($apartment_id);

    $host_id        = gArrayItem($apartment,'user_id');
    $host           = $users_class->get_user($host_id);

    $host_img       = get_user_meta($host_id,'user_avatar',true);
    if($host_img > 0){
        $host_img = wp_get_attachment_url($host_img);
    }
    if($host_img == ''){
        $host_img = get_avatar_url($host_id, 64 );
    }

    $host_verification_status = get_user_meta($host_id,'host_verification_status',true);
    if($host_verification_status == '') $host_verification_status = 'pending';

    //$host_verification_status = 'superhost-verified';


    $facilities = vv_get_facilities();
}


ob_start();
global $head_codes;
?>
<style type="text/css">

    #booking_ty .head{
        min-height:80vh;
        background-size:cover;
        background-position: top center;
        color:#004041;
    }

    #booking_ty .head > div{
        background-color: #ffffff7a;
    }

    #booking_ty .head .container .content{
        position:absolute;
        text-align:center;
        width:100%;
        top:100px;
        left:0;
    }

    #booking_ty .info{
        background-color: #013735;
        color:#fff;
        padding-top:50px;
        padding-bottom: 50px;
    }

    #booking_ty .info h2{
        font-family: 'TrajanProRegular';
        font-size: 26px;
        font-weight: 400;
        color:#fff;
    }
    #booking_ty .info .box{
        background-color: #F0E8D5;
        border-radius: 20px;
        padding:20px;
    }

    #booking_ty .booking_info{
        border:0px none;
    }

    #booking_ty .booking_info th, #booking_ty .booking_info td{
        border:0px none;
        background-color: transparent;
        font-size: 18px;
        font-weight: 500;
        padding: 10px;
    }

    #booking_ty .booking_info th{
        color: #013735;
    }

    #booking_ty .booking_info td{
        color:#BEA473;
        text-align: right;
    }

    #booking_ty .apt_info{
        padding:10px 25px;
    }
    #booking_ty .apt_info h3 a{
        font-size: 22px;
        font-family: 'TrajanProRegular';
        font-weight: 400;
        color:#FC780E;
        text-decoration: underline;
    }

    #booking_ty .apt_info p{
        font-size:18px;
        margin-bottom: 10px;
        color:#BEA473;
    }
    #booking_ty .apt_info p span{
        color: #013735;
    }

    #booking_ty .apt-host .row .col-md-8{
        border-right:1px solid #BEA473;
    }

    #booking_ty .host_info{
        padding:10px 20px;
    }
    #booking_ty .host_info .avatar{
        margin:0px auto;
        width:87px;
        height:87px;
        position: relative;
    }
    #booking_ty .host_info .avatar img{
        border-radius: 100%;
    }
    #booking_ty .host_info .avatar img.verified{
        position: absolute;
        bottom:-10px;
        left:60px;
    }
    #booking_ty .host_info h3{
        font-size: 20px;
        font-family: 'TrajanProRegular';
        color:#013735;
        font-weight: 400;
        text-align: center;
        margin-top:20px;
        margin-bottom: 10px;
        color:#013735;
    }

    #booking_ty .host_info .contact_host{
        background-color: transparent;
        border:1px solid #013735;
        color:#013735;
        display: block;
        width:100%;
        padding-top:5px;
        padding-bottom: 5px;
    }

    #booking_ty .host_info .host_status{
        text-align: center;
        margin-bottom: 20px;
        color:#BEA473;
    }
    #booking_ty .host_info .host_status > div{
        display: inline-block; 
        font-size: 16px;
        font-weight: 400;
    }

    #booking_ty .host_info .host_status.superhost-verified > div{
        background: url(<?php bloginfo('template_url') ?>/images/booking/verified-1.png) no-repeat top left;
        padding-left:30px;
    }

    #booking_ty .facilities{
        color:#F0E8D5;
    }
    #booking_ty .facilities ul li{
        display: inline-block;
        width: calc(50% - 24px);
        padding-top: 15px;
        padding-bottom: 15px;
        border-bottom: 1px solid #BEA473;
        padding-left: 10px;
        padding-right: 10px;
    }
    #booking_ty .facilities ul li .icon{
        display: inline-block;
        border:0px none;
        height: 30px;
        width: 60px;
        text-align: left;
    }
    #booking_ty .facilities ul li .icon img{
        margin-top:0;
        width:30px;
        height: 30px;
    }

    #booking_ty .practical_info{
        background-color: #004041;
        color:#F0E8D5;
        padding-top: 50px;
        padding-bottom: 50px;
    }

    #booking_ty .practical_info h2{
        color:#fff;
        font-size:45px;
        font-family: 'TrajanProRegular';
        font-weight: 400;
        text-align: center;
        margin-bottom: 40px;
    }

    #booking_ty .practical_info .blocks{
        max-width:860px;
        margin: 0px auto;
    }

    #booking_ty .practical_info .blocks .block{
        color:#F0E8D5;
        border: 1px solid #F0E8D5;
        border-radius: 30px;
        padding:30px;
        margin-bottom: 20px;
        position: relative;
    }
    #booking_ty .practical_info .blocks .block i{
        font-size: 20px;
        color:#F0E8D5;
        position: absolute;
        top:35px;
        right:30px;
    }
    #booking_ty .practical_info .blocks .block .collapse{
        padding-top:10px;
    }
    #booking_ty .practical_info .blocks .block .collapse p:last-child{
        margin-bottom: 0;
    }
    #booking_ty .practical_info .blocks .block h4{
        color:#F0E8D5;
        margin-bottom: 0;
    }

    #booking_ty .bottom_boxes{
        background-color: #F0E8D5;
        padding:50px 20px;
    }

    #booking_ty .bottom_boxes .box{
        border-radius: 20px;
        margin:30px 0;
    }

    #booking_ty .bottom_boxes .box .image{
        background-size: cover;
        background-position: center center;
        border-top-left-radius: 20px;
        border-bottom-left-radius: 20px;
    }
    #booking_ty .bottom_boxes .box .image img{
        visibility: hidden;
        max-height:350px;
        width:auto;
    }
    #booking_ty .bottom_boxes .box .content{
        background-color: #013735;
        border-top-right-radius: 20px;
        border-bottom-right-radius: 20px;
    }
    #booking_ty .bottom_boxes .box .content > div{
        padding:30px;
    }
    #booking_ty .bottom_boxes .box .content h3{
        text-align: center;
        font-size: 26px;
        font-family: 'TrajanProRegular';
        color:#fff;
        font-weight: 400;
        margin-top:50px;
    }
    #booking_ty .bottom_boxes .box .content .btn{
        width:100%;
        display: block;
        max-width:80%;
        margin: 0px auto;
    }

</style>
<?php
include('inc/search-apartments-styles.php');
$head_codes .= ob_get_clean();

get_header(); ?>  

<?php if ( have_posts() ) while ( have_posts() ) : the_post(); 


    $header = get_field('header');

    //echo print_r_pre($header);

    $header_img = gArrayItem($header,'background_image');
    if($header_img == '') $header_img = get_bloginfo('template_url').'/images/booking/ty-bg.png';

    if(gArrayItem($booking,'ID') == 0){
        $header['title'] = 'Booking # not found!';
        $header['subtitle'] = '<p>Sorry, the booking # you provided is not found</p>';
    }

    ?>        

    <div id="booking_ty" >

        <div class="head" style="background-image: url(<?php echo $header_img ?>);" >
            <div>
                <div class="container text-center position-relative">
                    <img src="<?php echo $header_img ?>" >
                    <div class="content">
                        <h1><?php echo gArrayItem($header,'title') ?></h1>
                        <?php echo gArrayItem($header,'subtitle') ?>
                    </div>
                </div>
            </div>
        </div>

        <?php 
        if(gArrayItem($booking,'ID') > 0){
            ?>
            <div class="info">
                <div class="container">
                    <div class="row pb-4">
                        <div class="col-md-4">
                            <h2>Your Booking Details</h2>
                            <div class="box">
                                <table class="booking_info">
                                    <tr>
                                        <th>Reservation ID:</th>
                                        <td>#<?php echo $booking_num ?></td>
                                    </tr>
                                    <tr>
                                        <th>Your Trip:</th>
                                        <td><?php echo $booked_dates ?></td>
                                    </tr>
                                    <tr>
                                        <th>Guests:</th>
                                        <td><?php echo $guests ?></td>
                                    </tr>
                                    <tr>
                                        <th>Total Price:</th>
                                        <td><?php echo vv_number_format($booking['total'],true) ?></td>
                                    </tr>
                                    <tr>
                                        <th>Status:</th>
                                        <td><?php echo $status_txt ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        <div class="col-md-8 apt-host">
                            <h2>Apartment & Host</h2>
                            <div class="box">
                                <div class="row">
                                    <div class="col-md-8 apt_info">
                                        <h3><a href="<?php echo vv_get_apartment_url($apartment) ?>" target="_blank"><?php echo gArrayItem($apartment,'display_name') ?></a></h3>
                                        <p><span>Address:</span> <?php echo gArrayItem($apartment,'address') ?></p>
                                        <p><span>Apartment Number:</span> <?php echo gArrayItem($apartment,'room_number') ?></p>
                                        <p><span>Apartment Floor:</span> <?php echo gArrayItem($apartment,'floor_number') ?></p>
                                        <p>
                                            <span>Check-in:</span>
                                            <?php
                                            if(gArrayItem($apartment,'check_in_time2') != '' && gArrayItem($apartment,'check_in_time1') != '' ){
                                                echo date("H:i", strtotime($apartment['check_in_time1'])).' - '.date("H:i", strtotime($apartment['check_in_time2']));
                                            }else{
                                                echo date("H:i", strtotime($apartment['check_in_time1']));
                                            }
                                            ?>
                                        </p>
                                        <p>
                                            <span>Check-out:</span>
                                            <?php echo date("H:i", strtotime($apartment['check_out_time'])); ?>
                                        </p>

                                    </div>
                                    <div class="col-md-4 ">
                                        <div class="host_info">
                                            <div class="avatar">
                                                <img src="<?php echo $host_img ?>" >
                                                <?php if(strpos($host_verification_status,'verified') !== false){ ?>
                                                    <img src="<?php bloginfo('template_url') ?>/images/booking/verified.png" class="verified">
                                                <?php }  ?>
                                            </div>
                                            <h3><?php echo gArrayItem($host,'firstname').' '.gArrayItem($host,'lastname') ?></h3>
                                            <div class="host_status <?php echo $host_verification_status ?>">
                                                <div><?php echo vv_get_status_name_text($host_verification_status) ?></div>
                                            </div>
                                            <button class="contact_host"><img src="<?php bloginfo('template_url') ?>/images/booking/contact-host.png" > Contact Host</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4 pt-4">
                        <div class="col-md-6">
                            <h2>What’s included</h2>
                            <div class="facilities">
                                <ul>
                                    <?php 
                                    $a_facilities = json_decode(gArrayItem($apartment,'facilities'),true);
                                    if(!is_array($a_facilities)) $a_facilities = [];
                                    //echo print_r_pre($a_facilities);
                                    foreach($facilities as $facility){
                                        foreach($a_facilities as $a_facility){
                                            if($a_facility == gArrayItem($facility,'facility_id')){
                                                $name = gArrayItem($facility,'name');
                                                ?>
                                                <li><span class="icon"><img src="<?php echo get_bloginfo('template_url') ?>/images/facilities/icon-<?php echo str_replace(" ","-",strtolower(trim($name))) ?>-2.svg" alt=""></span><?php echo $name ?></li>
                                                <?php
                                            }
                                        }
                                    }
                                    ?>
                                </ul>           
                            </div>             
                        </div>
                        <div class="col-md-6">
                            <h2>Location of your stay</h2>
                            <div id="apartments_map" style="height: 500px; width: 100%;border-radius: 30px;border:1px solid #BEA473"></div>
                        </div>
                    </div>
                </div>
            </div>
            <?php 
            ob_start();

            $booking_items = $booking_class->get_booking_items($booking_id);

            $has_airport_pickup = false;
            $has_scooter_rental = false;

            foreach($booking_items as $item){
                if(gArrayItem($item,'code') == 'airport_pickup') $has_airport_pickup = true;
                if(gArrayItem($item,'code') == 'scooter_rental') $has_scooter_rental = true;

            }
            //echo print_r_pre($booking_items);
            ?>

            <?php if(gArrayItem($apartment,'checkin_without_host') == 1){ ?>
                <div class="block">
                    <h4>Check-in without host</h4>
                    <a data-toggle="collapse" href="#checkinwithouthost" role="button" aria-expanded="false" ><i class="fas fa-angle-down"></i></a>
                    <div class="collapse" id="checkinwithouthost">
                        <p>Free of charge cancellation 2 days before arrival</p>
                    </div>
                </div>
            <?php } ?>
            <?php if(gArrayItem($apartment,'flexible_reservation') == 1){ ?>
                <div class="block">
                    <h4>Flexible reservation</h4>
                    <a data-toggle="collapse" href="#flexiblereservation" role="button" aria-expanded="false" ><i class="fas fa-angle-down"></i></a>
                    <div class="collapse" id="flexiblereservation">
                        <p>Free of charge cancellation 2 days before arrival</p>
                    </div>
                </div>
            <?php } ?>
            <?php if(gArrayItem($apartment,'airport_pickup') == 1 && $has_airport_pickup == true){ ?>
                <div class="block">
                    <h4>Air Port Pick Up</h4>
                    <a data-toggle="collapse" href="#airportpickup" role="button" aria-expanded="false" ><i class="fas fa-angle-down"></i></a>
                    <div class="collapse" id="airportpickup">
                        <p>Pick up at airport has been requested.</p>
                    </div>
                </div>
            <?php } ?>
            <?php if(gArrayItem($apartment,'scooter_rental') == 1 && $has_scooter_rental == true){ ?>
                <div class="block">
                    <h4>Scooters Rental</h4>
                    <a data-toggle="collapse" href="#airportpickup" role="button" aria-expanded="false" ><i class="fas fa-angle-down"></i></a>
                    <div class="collapse" id="airportpickup">
                        <p>Scooter rental is available.</p>
                    </div>
                </div>
            <?php } ?>


            <div class="block">
                <h4>House Rules</h4>
                <a data-toggle="collapse" href="#houserules" role="button" aria-expanded="false" ><i class="fas fa-angle-down"></i></a>
                <div class="collapse" id="houserules">
                    <?php
                    $check_in_time1 = gArrayItem($apartment,'check_in_time1');
                    $check_in_time2 = gArrayItem($apartment,'check_in_time2');
                    if($check_in_time1 != $check_in_time2){
                        echo '<p>– Innsjekking er '.$check_in_time1.' til '.$check_in_time2.'</p>';
                    }else{
                        echo '<p>– Innsjekking er '.$check_in_time1.'</p>';
                    }

                    $check_out_time = gArrayItem($apartment,'check_out_time');
                    echo '<p>– Utsjekking før kl. '.$check_out_time.'</p>';

                    echo '<p>– Maksimalt 8. '.gArrayItem($apartment,'max_guests').'</p>';
                    ?>
                </div>
            </div>
            <div class="block">
                <h4>Security and property</h4>
                <a data-toggle="collapse" href="#security" role="button" aria-expanded="false" ><i class="fas fa-angle-down"></i></a>
                <div class="collapse" id="security">
                    <?php
                    $security_features = vv_get_security_features();
                    if(is_array($security_features)){
                        $apartment_security_features = json_decode(gArrayItem($apartment,'security_features'),true);
                        if(is_array($apartment_security_features)){
                            foreach($security_features as $security_feature){
                                if(in_array($security_feature['security_feature_id'],$apartment_security_features,true)){
                                    echo '<p>- '.$security_feature['name'].'</p>';
                                }
                            }
                        }
                    }
                    ?>
                </div>
            </div>

            <?php 

            $practical_info_html = ob_get_clean();
            if(trim(strip_tags($practical_info_html)) != ''){
                ?>
                <div class="practical_info">
                    <div class="container">
                        <h2>Practical Information</h2>
                        <div class="blocks">
                            <?php echo $practical_info_html ?>
                        </div>
                    </div>
                </div>
                <?php 
            }
            ?>



            <?php
        



        }

        $bottom_boxes = get_field('bottom_boxes');
        //echo print_r_pre($bottom_boxes);
        if(count($bottom_boxes) > 0){
            ?>
            <div class="bottom_boxes">
                <div class="container">
                    <?php 
                    foreach($bottom_boxes as $box){
                        $image = gArrayItem($box,'image');
                        $sizes = gArrayItem($image,'sizes');
                        $large = gArrayItem($sizes,'large');

                        ?>
                        <div class="box">
                            <div class="row">
                                <div class="col-md-6 image" style="background-image: url(<?php echo $large ?>)">
                                    <img src="<?php echo $large ?>" >
                                </div>
                                <div class="col-md-6 content">
                                    <div>
                                        <h3><?php echo gArrayItem($box,'text') ?></h3>
                                        <a href="<?php echo gArrayItem($box,'button_link') ?>" class="btn btn-primary"><?php echo gArrayItem($box,'button_text') ?></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php
                    }
                    ?>
                </div>
            </div>
            <?php
        }
        ?>
    </div>
    

<?php endwhile; ?>

<?php

global $footer_codes;
ob_start();

?>
<script src="https://maps.googleapis.com/maps/api/js?key=<?php echo vv_get_google_map_api_key() ?>" ></script>

<script>
    $(document).ready(function (){

        <?php 
        if(gArrayItem($booking,'ID') > 0){
            ?>
            apartments_map = new google.maps.Map(document.getElementById('apartments_map'), {
                zoom: 12,
                center: { lat: 0, lng: 0 }
            });


            var lat = parseFloat(<?php echo gArrayItem($apartment,'address_latitude') ?>);
            var lng = parseFloat(<?php echo gArrayItem($apartment,'address_longitude') ?>);

            var marker = new google.maps.Marker({
                position: { lat: lat, lng: lng },
                map: apartments_map,
            });

            var infoWindow = new google.maps.InfoWindow({
                content: `
                    <div style="width:250px;color:#000;">
                        <strong><?php echo stripslashes(gArrayItem($apartment,'display_name')) ?></strong><br><?php echo stripslashes(gArrayItem($apartment,'address')) ?><br>
                        <a href="<?php echo vv_get_apartment_url($apartment) ?>" target="_blank">View Details</a><br>
                    </div>
                `
            });

            marker.addListener("click", function () {
                infoWindow.open(apartments_map, marker);
            });

            apartments_map.setCenter({ lat: lat, lng: lng });


            $('.practical_info').find('.collapse').on('shown.bs.collapse', function () {
                $(this).prev('a').find('i').removeClass('fa-angle-down').addClass('fa-angle-up');
            });
            $('.practical_info').find('.collapse').on('hidden.bs.collapse', function () {
                $(this).prev('a').find('i').removeClass('fa-angle-up').addClass('fa-angle-down');
            });

            <?php 
        }
        ?>

    });
</script>
<?php

$footer_codes .= ob_get_clean();

get_footer(); 

?>