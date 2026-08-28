<?php
/*
    * Template Name: Apartment
*/

$apartment_id = get_query_var('id');


get_header(); 

if($apartment_id == ''){

    ?>
    <div class="container pt-4 pb-4">
        <div class="alert alert-danger mt-4 mb-4">Invalid URL</div>
    </div>
    <?php

}else{
    global $visitVietnam,$wpdb;

    if(is_int($apartment_id)){
        $apartment = $visitVietnam->apartment_class->get_apartment($apartment_id);    
    }else{
        $apartment = $visitVietnam->apartment_class->get_apartment_by_slug($apartment_id);    
    }

    if ( intval( gArrayItem( $apartment, 'ID' ) ) <= 0 || ! vvApartments::is_publicly_visible( $apartment ) ) {
        ?>
        <div class="container pt-4 pb-4">
            <div class="alert alert-danger mt-4 mb-4">This apartment is not available.</div>
        </div>
        <?php
        get_footer();
        exit;
    }

    //echo $apartment_id;
    //echo print_r_pre($apartment);
    //die();
    

    $images = vv_get_apartment_images($apartment);

    //echo print_r_pre($apartment);
    //echo print_r_pre($images);

    $main_img_url = '';
    if(count($images) > 0){
        $main_img = gArrayItem($images,0);

        $main_img_url = wp_get_attachment_url(gArrayItem($main_img,'image_id'));

    }
    $conversion_rates = [];
    $host_id = gArrayItem($apartment,'user_id');
    $host_currency = get_user_meta($host_id,'vv_host_currency',true);
    if($host_currency == '') $host_currency = 'USD';
    $conversion_rates= json_decode(get_option('vv_conversion_rates_'.$host_currency),true);
    if(!is_array($conversion_rates))$conversion_rates = [];


    ?>
    <script>
        var conversionRates = <?php echo json_encode($conversion_rates ); ?>;
    </script>

    <?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>        
    	<div id="topbar">
        	<div class="container">
            	<div class="topbar">
                	<div class="topbar_left">
                    	<a href="/"><img src="<?php bloginfo('template_url'); ?>/images/logo.png" alt=""></a>
                    </div>
                    <div class="topbar_right">
                        <ul>
                            <li><a href="#menucontent_1">Pictures</a></li>
                            <li><a href="#menucontent_2">Facilities</a></li>
                            <li><a href="#menucontent_3">Practical information</a></li>
                            <li><a href="#menucontent_4">Host</a></li>
                            <li><a href="#menucontent_4">Price 3544-</a></li>
                        </ul>
                        <a href="#" class="btn">Book now</a>
                    </div>
                </div>
            </div>
        </div>
    	<div class="single_app_top" id="menucontent_1">
        	<div class="container">
            	<div class="app_title">
                	<div>
                    	<h1 class="heading-2"><?php echo gArrayItem($apartment,'name') ?></h1>
                        <h5><img src="<?php bloginfo('template_url'); ?>/images/np_pin1.svg" alt=""><?php echo gArrayItem($apartment,'address') ?></h5>
                    </div>
                   
                    <a href="#" class="share_link"> <img src="<?php bloginfo('template_url'); ?>/images/share.svg" alt=""> Share link</a> 
                </div>
                <div class="app_gall">
                	<div class="app_gall_left">
                    	<div class="gall_img">
                        	<a href="#images_data" data-fancybox class="gall_img_inn">
    	                        <img src="<?php echo $main_img_url; ?>" alt="">
    						</a>
    					</div>
                    </div>
                    <div class="app_gall_right">
                    	<div class="app_gall_right_inn">
                            <div class="gall_col_wrap">
                                <?php
                                $index = 1;
                                for($col = 1; $col < 4; $col++){

                                    echo '<div class="gall_col" >';

                                    for($row = 1; $row < 3; $row ++){
                                        if($index < count($images)){
                                            ?>
                                            <div class="gall_img"><a href="#images_data" data-fancybox class="gall_img_inn"><img src="<?php echo $images[$index]['thumb'] ?>" alt=""></a></div>
                                            <?php
                                        }
                                        $index++;
                                    }

                                    echo '</div>';
                                }
                                ?>
                            </div>
                            
                            <a href="#images_data" class="show_all" data-fancybox>Show all images (<?php echo count($images); ?>)</a>
                            <div id="images_data" class="content_box" style="display:none;">
                            	<h2>Show all images (<?php echo count($images); ?>)</h2>
                                <?php 
                                foreach( $images as $image_data ){ 
                                    $main_img_url = wp_get_attachment_url(gArrayItem($image_data,'image_id'));
                                    ?>
                                	<a href="<?php echo $main_img_url ?>" data-fancybox="gallery"><img src="<?php echo $image_data['thumb']; ?>" alt="" /></a>
                                    <h4><?php echo $image_data['caption']; ?></h4>
                                    <?php 
                                } 
                                ?>
                            </div>                                                
                            
                    	</div>
                    </div>
                </div>
                <div class="app_feature">
                	<div class="row">
                    	<div class="col-sm-12">
                            <h3 class="mb-4"><?php echo gArrayItem($apartment,'description') ?></h3>

                            <ul>
                                <li>
                                    <div class="icon"><img src="<?php echo get_bloginfo('template_url') ?>/images/icon_rooms.svg" alt=""></div>
                                    <h4><?php echo gArrayItem($apartment,'rooms') ?> rooms</h4>
                                </li>
                                <li>
                                    <div class="icon"><img src="<?php echo get_bloginfo('template_url') ?>/images/icon_beds.svg" alt=""></div>
                                    <h4><?php echo gArrayItem($apartment,'num_beds') ?> beds</h4>
                                </li>
                                <li>
                                    <div class="icon"><img src="<?php echo get_bloginfo('template_url') ?>/images/icon_guests.svg" alt=""></div>
                                    <h4><?php echo gArrayItem($apartment,'max_guests') ?> guests</h4>
                                </li>
                                <li>
                                    <div class="icon"><img src="<?php echo get_bloginfo('template_url') ?>/images/icon_baths.svg" alt=""></div>
                                    <h4><?php echo gArrayItem($apartment,'num_bathrooms') ?> baths</h4>
                                </li>
                                <li>
                                    <div class="icon"><img src="<?php echo get_bloginfo('template_url') ?>/images/icon_size.svg" alt=""></div>
                                    <h4><?php echo gArrayItem($apartment,'area_sqm') ?> m<sup>2</sup></h4>
                                </li>
                            </ul>
                    	</div>
                    </div>
                </div>
            </div>
        </div>
        <?php
        $facilities     = vv_get_facilities();
        if(!is_array($facilities)) $facilities = [];
        //echo print_r_pre($facilities);
        $a_facilities = json_decode(gArrayItem($apartment,'facilities'),true);
        if(!is_array($a_facilities)) $a_facilities = [];
        ?>
        <div class="app_content" id="app_content">
        	<div class="container">
            	<div class="app_content_inn">
                    <div class="app_leftbar" id="app_leftbar">
                        <?php if(count($a_facilities) > 0){
                            ?>
                            <div class="app_facility" id="menucontent_2">
                                <h3>Facilities</h3>
                                <ul>
                                    <?php 
                                    $ctr = 0;
                                    foreach($facilities as $facility){
                                        foreach($a_facilities as $a_facility){
                                            if($a_facility == gArrayItem($facility,'facility_id')){
                                                $name = gArrayItem($facility,'name');
                                                if($ctr < 8){
                                                    ?>
                                                    <li><span class="icon"><img src="<?php echo get_bloginfo('template_url') ?>/images/facilities/icon-<?php echo str_replace(" ","-",strtolower(trim($name))) ?>.svg" alt=""></span><?php echo $name ?></li>
                                                    <?php
                                                }
                                                $ctr++;
                                            }
                                        }
                                    }
                                    ?>
                                </ul>
                                <?php
                                if(count($a_facilities) > 8){
                                    ?>
                                    <a href="#facilities_data" data-touch="false" data-fancybox class="btn border_btn">Show all facilities (<?php echo count($a_facilities); ?>)</a>
                                    <div id="facilities_data" class="content_box facilities_pop_up" style="display:none;">
                                        <div class="popup_scroll">
                                            <h2>All facilities (<?php echo count($a_facilities); ?>)</h2>
                                            <ul>
                                                <?php
                                                foreach($facilities as $facility){
                                                    foreach($a_facilities as $a_facility){
                                                        if($a_facility == gArrayItem($facility,'facility_id')){
                                                            $name = gArrayItem($facility,'name');
                                                            ?>
                                                            <li><span class="icon"><img src="<?php echo get_bloginfo('template_url') ?>/images/facilities/icon-<?php echo str_replace(" ","-",strtolower(trim($name))) ?>.svg" alt=""></span><?php echo $name ?></li>
                                                            <?php 
                                                        } 
                                                    }
                                                }
                                                ?>
                                            </ul>
                                        </div>
                                    </div>
                                    <?php
                                }
                                ?>
                                <hr/>
                            </div>
                            <?php 
                        }
                        ?>
                        <?php 
                        if(trim(strip_tags(gArrayItem($apartment,'about_this_short'))) != ''){
                            ?>
                            <div class="app_about">
                                <?php echo nl2br(gArrayItem($apartment,'about_this_short')) ?>
                            </div>
                            <hr/>
                            <?php 
                        }
                        ?>
    					<?php 
                        $practical_information = get_field('practical_information'); 
                        ob_start();
                        ?>
                        <?php if(gArrayItem($apartment,'checkin_without_host') == 1){ ?>
                            <div class="block">
                                <span class="icon"><img src="<?php bloginfo('template_url') ?>/images/icon-checkinwithouthost.svg" alt=""></span>
                                <h4>Check-in without host</h4>
                                <p>Personal check-in can be arranged</p>
                            </div>
                        <?php } ?>
                        <?php if(gArrayItem($apartment,'flexible_reservation') == 1){ ?>
                            <div class="block">
                                <span class="icon"><img src="<?php bloginfo('template_url') ?>/images/icon-airportpickup.svg" alt=""></span>
                                <h4>Flexible reservation</h4>
                                <p>Free of charge cancellation 2 days before arrival</p>
                            </div>
                        <?php } ?>
                        <?php if(gArrayItem($apartment,'airport_pickup') == 1){ ?>
                        <div class="block">
                            <span class="icon"><img src="<?php bloginfo('template_url') ?>/images/icon-flexiblereservation.svg" alt=""></span>
                            <h4>Air Port Pick Up</h4>
                            <p>Pick up at airport can be arranged</p>
                        </div>
                        <?php } ?>
                        <?php if(gArrayItem($apartment,'scooter_rental') == 1){ ?>
                        <div class="block">
                            <span class="icon"><img src="<?php bloginfo('template_url') ?>/images/icon-scootersavailable.svg" alt=""></span>
                            <h4>Scooters availability</h4>
                            <p>We can arrange scooters for your stay</p>
                        </div>
                        <?php } ?>

                        <?php 

                        $practical_info_html = ob_get_clean();
                        if(trim(strip_tags($practical_info_html)) != ''){
                            ?>
                            <div class="app_per_info" id="menucontent_3">
                                <h3>Practical information</h3>
                                <div class="app_per_info_inn">
                                    <?php echo $practical_info_html ?>
                                </div>
                            </div>
                            <?php 
                        }
                        ?>


                        <div class="app_per_info_bot">
                            <div class="block">
                                <h4>House Rules</h4>
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
                            <div class="block">
                                <h4>Security and property</h4>
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
                        if(trim(strip_tags(gArrayItem($apartment,'about_this'))) != ''){
                            ?>
                            <a href="#practical_information" data-touch="false" data-fancybox class="btn border_btn">Read more</a>
                            <div id="practical_information" class="content_box" style="display:none;">
                            	<div class="popup_scroll">
        							<?php echo gArrayItem($apartment,'about_this') ?>
                                </div>
                            </div>
                            <hr/>
                            <?php 
                        }
                        ?>
                        <?php /*
                        <div class="app_avability">
                            <h3>Apartment availability</h3>
                            <div class="input_wrap availability_sel">      
                              <input type="text" name="datefilter" placeholder="Select dates" class="availability_sel_picker" value="" />
                          </div>
                        </div>
                        */ ?>
                        <div class="app_here_app pt-4">
                            <h3>Here is the apartment<a href="#" class="map_open">Open in Google Maps</a></h3>
                            <div class="map_img">
                                <img src="<?php bloginfo('template_url'); ?>/images/map.png" alt="">
                            </div>
                            <div class="app_here_app_inn" id="menucontent_4">
                                <h3>This is your host</h3>
                                <div class="host_info">
                                    <div class="user_img"><img src="<?php bloginfo('template_url'); ?>/images/user_img.png" alt=""></div>
                                    <div class="user_desc">
                                        <h4>Ngân</h4>
                                        <h5>Your host</h5>
                                    </div>
                                    <div class="btns">
                                        <a href="#" class="btn cont_btn">Host</a>
                                        <a href="#" class="btn">View profile</a>
                                    </div>
                                </div>
                                <h3>Reviews</h3>
                                <div class="nano">
    								<div class="content">
                                        <div class="app_rev_block">
                                            <h4>Very clean apartment!</h4>
                                            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut</p>
                                            <p class="name">– Thao, July 2023</p>
                                        </div>
                                        <div class="app_rev_block">
                                            <h4>Beautiful view</h4>
                                            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua!</p>
                                            <p class="name">– Charles, June 2023</p>
                                        </div>
                                        <div class="app_rev_block">
                                            <h4>Very clean apartment!</h4>
                                            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut</p>
                                            <p class="name">– Thao, July 2023</p>
                                        </div>
                                        <div class="app_rev_block">
                                            <h4>Beautiful view</h4>
                                            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua!</p>
                                            <p class="name">– Charles, June 2023</p>
                                        </div>
                                        <div class="app_rev_block">
                                            <h4>Very clean apartment!</h4>
                                            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut</p>
                                            <p class="name">– Thao, July 2023</p>
                                        </div>
                                        <div class="app_rev_block">
                                            <h4>Beautiful view</h4>
                                            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua!</p>
                                            <p class="name">– Charles, June 2023</p>
                                        </div>
                                	</div>
                                </div>
                            </div>
                            
                        </div>
                    </div>
                    <div class="app_rightbar">
                    	<div id="app_rightbar_inn">
                            <a name="book-now"></a>
                            <div class="app_book_block" id="app_book_block">
                                
                                <?php

                                $check_in_date = GET_Request('check_in_date');
                                $check_out_date = GET_Request('check_out_date');

                                //if($check_in_date == '')    $check_in_date = gArrayItem($_SESSION,'check_in_date');
                                //if($check_out_date == '')   $check_out_date = gArrayItem($_SESSION,'check_out_date');

                                $booking = gArrayItem($_SESSION,'BOOKING_DATA_FRONT');
                                if($check_in_date == '')    $check_in_date = gArrayItem($booking,'check_in_date');
                                if($check_out_date == '')   $check_out_date = gArrayItem($booking,'check_out_date');

                                //echo print_r_pre($booking);

                                if($check_in_date == '') $check_in_date = date('m/d/Y',strtotime("+1 Day"));
                                if($check_out_date == '') $check_out_date = date("m/d/Y",strtotime("+1 Day",strtotime($check_in_date)));

                                $discount_val = '';
                                $discount_txt = '';
                                ?>
                                <form method="post" id="formBooking" >
                                    <input type="hidden" name="action" value="vv_start_booking">
                                    <input type="hidden" name="booking_id" value="<?php echo gArrayItem($booking,'ID') ?>" >
                                    <input type="hidden" name="user_id" value="0" >
                                    <input type="hidden" name="apartment_id" value="<?php echo gArrayItem($apartment,'ID') ?>" >
                                    <input type="hidden" name="check_in_date" value="<?php echo $check_in_date ?>"  >
                                    <input type="hidden" name="check_out_date" value="<?php echo $check_out_date ?>" >
                                    <input type="hidden" name="discount_val" value="<?php echo $discount_val ?>" >
                                    <input type="hidden" name="discount_txt" value="<?php echo $discount_txt ?>" >
                                    <h2>Book now</h2>
                                    <h5>Add dates and check availability & prices</h5>
                                    <hr>
                                           
                                     <div class="check_in_out_date">       
                                          <div class="input_wrap date_sel">      
                                              <label>Check in</label>
                                              <input type="text" name="datefilter" placeholder="Select dates" class="start_date" value="<?php echo date("m/d/Y",strtotime($check_in_date)) ?>" />
                                          </div>
                                          <div class="input_wrap date_sel">      
                                              <label>Checkout</label>
                                              <input type="text" name="datefilter" placeholder="Select dates" class="end_date" value="<?php echo date("m/d/Y",strtotime($check_out_date)) ?>" />
                                          </div>
                                    </div>
                                    <div class="guest_opt_wrap">
                                        <div class="guest_opt d-block pr-3">
                                            <a href="#" class="room_btn pr-3">
                                                <div class="guest_opt_left float-left">
                                                    <span style="font-size:14px"> 2 adults & 1 child</span>
                                                </div>
                                                <div class="room_sel float-right w-auto pl-0">
                                                    <span>Guests</span>
                                                </div>
                                                <div style="clear:both;"></div>
                                            </a>
                                            <div class="rooom_dropdown pt-4 pb-4">
                                                <ul>
                                                    <li>
                                                        <span class="lbltxt">Adults<i>13 years old or older</i></span>
                                                        <div class="number">
                                                            <span class="minus"></span>
                                                            <input type="text" name="num_adults" value="2" data-min="1" />
                                                            <span class="plus" data-field="num_adults"></span>
                                                        </div>
                                                    </li>
                                                    <li>
                                                        <span class="lbltxt">Children<i> below years old</i></span>
                                                        <div class="number">
                                                            <span class="minus"></span>
                                                            <input type="text" name="num_children" value="1" data-min="0" />
                                                            <span class="plus"></span>
                                                        </div>
                                                    </li>
                                                </ul>
                                                <div class="text-center mt-2"><a class="btn" href="#" id="btnUpdateRoomGuest" ><i class="fa fa-check"></i></a></div>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="submit" class="book_now_btn">Book now</button>
                                    <div class="app_price">
                                        <h2>Your price:</h2>
                                        <ul>
                                            <?php /*
                                            <li><span class="lbltxt">0 kr x 5 nights</span><span class="valtxt">0 kr</span></li>
                                            <li><span class="lbltxt">25% discount</span><span class="valtxt">- 0 kr</span></li>
                                            <li><span class="lbltxt">Cleaning fee</span><span class="valtxt">0 kr</span></li>
                                            <li><span class="lbltxt">Booking fee</span><span class="valtxt">0</span></li>
                                            <li><span class="lbltxt">Total</span><span class="valtxt"><i>0 kr</i>3.530,5 kr</span></li>
                                            */ ?>
                                        </ul>
                                    </div>
                                </form>
                            </div>
                            <div class="checkout_late_block">
                                <h2>Check out late</h2>
                                <h5>This apartment is available for late checkout</h5>
                                <hr/>
                                <p>Did you know that you can check out late without charge when you become gold member on our site?</p>
                                <a href="#" class="btn">Read more about our gold membership</a>
                            </div>
                    	</div>
                    </div>
            	</div>
            </div>
        </div>

    <?php endwhile; // end of the loop. ?>

    <?php
    ob_start();
    ?>

    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

    <script>
        var $formBooking;
        function calculateBooking(){
            check_in_date   = $formBooking.find('input[name="check_in_date"]').val();
            check_out_date  = $formBooking.find('input[name="check_out_date"]').val();
            discount_val    = $formBooking.find('input[name="discount_val"]').val();
            discount_txt    = $formBooking.find('input[name="discount_txt"]').val();
            booking_fee     = <?php echo floatval(vv_fee()) ?>;

            $.post('<?php echo vv_base_url().'?isajax=1' ?>', $formBooking.serialize(), function (){});

            if(check_in_date != '' && check_out_date != ''){    
                date1 = new Date(check_in_date);
                date2 = new Date(check_out_date);

                if(date1 < date2){

                    //date1.setTime(check_in_date);
                    //date2.setTime(check_out_date);

                    $formBooking.find('.app_price').find('ul').html('<li>...</li>');
                    $formBooking.find('.book_now_btn').hide();

                    $.get('<?php echo get_bloginfo('url').'?action=vv_check_apartment_booking&apartment='.gArrayItem($apartment,'ID') ?>&check_in_date='+vv_formatDateToYMD(date1)+'&check_out_date='+vv_formatDateToYMD(date2), function (data){
                        //console.log(data);

                        html = '';
                        if(data.status == 'success'){
                            console.log(data)

                            total = data.total;


                            discount_t  = 0;
                            html = '<li><span class="lbltxt">'+data.label+'</span><span class="valtxt">'+vvFormatCurrency(total)+'</span></li>';



                            if(data.campaign_discount > 0){
                                discount = parseFloat(data.campaign_discount);
                                html += '<li><span class="lbltxt">Campaign Discount</span><span class="valtxt">- '+vvFormatCurrency(data.campaign_discount)+'</span></li>';
                                total = total - discount;
                                discount_t += discount;
                            }


                            if(data.basic_discount > 0){
                                discount    = total * (data.basic_discount/100);
                                html        += '<li><span class="lbltxt"><?php echo vv_basic_discount_name() ?> ('+data.basic_discount+'%)'+'</span><span class="valtxt">- '+vvFormatCurrency(discount)+'</span></li>';
                                total       = total - discount;
                                discount_t  += discount;
                            }

                             <?php if(vv_fee() > 0){ ?>
                                vv_fee   = (total * (<?php echo vv_fee()/100 ?>));
                                total   += vv_fee;
                                html    += '<li><span class="lbltxt">Booking fee (<?php echo vv_fee() ?>%)</span><span class="valtxt">'+vvFormatCurrency(vv_fee)+'</span></li>';
                            <?php } ?>


                            <?php if(gArrayItem($apartment,'cleaning_fee') > 0){ ?>
                                total += <?php echo $apartment['cleaning_fee'] ?>;
                                html += '<li><span class="lbltxt">Cleaning fee</span><span class="valtxt">'+vvFormatCurrency(<?php echo $apartment['cleaning_fee'] ?>)+'</span></li>';
                            <?php } ?>


                            html += '<li><span class="lbltxt">Total</span><span class="valtxt"><i>'+vvFormatCurrency(discount_t)+'</i> '+vvFormatCurrency(total)+'</span></li>';

                            $formBooking.find('.book_now_btn').show();
                        }else{
                            html = '<li><div class="alert alert-danger">'+data.error+'</div></li>';
                        }

                        $formBooking.find('.app_price').find('ul').html(html);


                    });
                }

                //console.log(date1);
                //console.log(date2);

            }else{
                $formBooking.find('.app_price').find('ul').html('<li>-</li>');
            }
        }
        $(function() {        
            $formBooking = $('#formBooking');

            $('.start_date').daterangepicker({
                autoUpdateInput: false,
                alwaysShowCalendars:true,
                singleDatePicker: true,
                autoApply:true,
                minDate:new Date(),
                locale: { cancelLabel: 'Clear', format: 'MM/DD/YYYY' }
            });


            $('.end_date').daterangepicker({
                autoUpdateInput: false,
                alwaysShowCalendars:true,
                singleDatePicker: true,
                autoApply:true,
                minDate:new Date(),
                locale: {
                    cancelLabel: 'Clear', format: 'MM/DD/YYYY'
                }
            });

            $('.start_date').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('MM/DD/YYYY'));
                var nextDay = picker.startDate.clone().add(1, 'days');
                
                drp2 = $('.end_date').data('daterangepicker');
                currentStart = drp2.startDate;

                drp2.minDate = nextDay;
                drp2.setStartDate(nextDay);
                $('.end_date').val(nextDay.format('MM/DD/YYYY'));
                $('input[name="check_in_date"]').val(picker.startDate.format("YYYY-MM-DD"));
                $('input[name="check_out_date"]').val(drp2.startDate.format("YYYY-MM-DD"));
                calculateBooking();
            });

            $('.end_date').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('MM/DD/YYYY'));
                $('input[name="check_out_date"]').val(picker.startDate.format("YYYY-MM-DD"));
                calculateBooking();
            });


            $('.minus').click(function () {
                $input = $(this).parent().find('input');
                count = parseInt($input.val());
                min = $input.attr('data-min');
                if(count > min) count--;
                $input.val(count);
                $input.change();
                calculateBooking();
                return false;
            });

            $('.plus').click(function () {
                var $input = $(this).parent().find('input');
                $input.val(parseInt($input.val()) + 1);
                $input.change();
                calculateBooking();
                return false;
            });

            $('#btnUpdateRoomGuest').on('click',function (e){
                num_adults      = $formBooking.find('input[name="num_adults"]').val();
                num_children    = $formBooking.find('input[name="num_children"]').val();

                txt = (num_adults > 1) ? num_adults + ' adults' : num_adults + ' adult';
                
                if(num_children > 1)  txt += ' & ' + num_children + ' children';
                else if(num_children == 1) txt += ' & 1 child';

                $formBooking.find('.guest_opt_left').find('span').html(txt);

                calculateBooking();
                $formBooking.find('.rooom_dropdown').hide();
                e.preventDefault();
            });

            calculateBooking();
        });
    </script>

    <?php 

    global $footer_codes;
    $footer_codes .= ob_get_clean();
}
get_footer(); 

?>
