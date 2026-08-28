<?php

//echo print_r_pre($apartment);

$images = vv_get_apartment_images($apartment);
//echo print_r_pre($images);
$facilities = vv_get_facilities();

get_header(); ?>

	<div id="topbar">
    	<div class="container">
        	<div class="topbar">
            	<div class="topbar_left">
                	<a href="/"><img src="<?php bloginfo('template_url'); ?>/images/logo.png" alt=""></a>
                </div>
                <div class="topbar_right">
                    <ul>
                        <li><a href="#sec-pictures">Pictures</a></li>
                        <li><a href="#sec-facilities">Facilities</a></li>
                        <li><a href="#sec-practical_info">Practical information</a></li>
                        <li><a href="#sec-host">Host</a></li>
                        <li><a href="#sec-price">Price 3544-</a></li>
                    </ul>
                    <a href="#" class="btn">Book now</a>
                </div>
            </div>
        </div>
    </div>
	<?php $top_section = get_field('top_section'); ?>
	<div class="single_app_top" id="sec-pictures">
    	<div class="container">
        	<div class="app_title">
            	<div>
                	<h1 class="heading-2"><?php echo gArrayItem($apartment,'name')  ?></h1>
                    <h5><img src="<?php bloginfo('template_url'); ?>/images/np_pin1.svg" alt=""><?php echo gArrayItem($apartment,'address') ?></h5>
                </div>
               
                <a href="#" class="share_link"> <img src="<?php bloginfo('template_url'); ?>/images/share.svg" alt=""> Share link</a> 
            </div>

            <?php 

            if(count($images) > 0){

                $main_img = wp_get_attachment_image_url($images[0]['image_id'],'full'); 
                ?>
                <div class="app_gall">
                	<div class="app_gall_left">
                    	<div class="gall_img">
                        	<a href="#images_data" data-fancybox class="gall_img_inn">
    	                        <img src="<?php echo $main_img; ?>" alt="">
    						</a>
    					</div>
                    </div>
                    <div class="app_gall_right">
                    	<div class="app_gall_right_inn">
                            <div class="gall_col_wrap">
                            	<?php 
                                for($i = 1; $i < count($images); $i++){
                                    if($i < 7){
                                        ?>
                                        <div class="gall_col">
                                        	<?php 
                                            if($i < count($images)){ 
                                                ?>
                                                <div class="gall_img"><a href="#images_data" data-fancybox class="gall_img_inn"><img src="<?php echo $images[$i]['thumb']; ?>" alt=""></a></div>
                                                <?php 
                                            } 
                                            $i++;
                                            if($i < count($images)){ 
                                                ?>
                                                <div class="gall_img"><a href="#images_data" data-fancybox class="gall_img_inn"><img src="<?php echo $images[$i]['thumb']; ?>" alt=""></a></div>
                                                <?php 
                                            } 
                                            ?>
                                        </div>
                                        <?php 
                                    }
                                } 
                                ?>
                            </div>
                            
                            <a href="#images_data" class="show_all" data-fancybox>Show all images (<?php echo count($images) ?>)</a>
                            <div id="images_data" class="content_box" style="display:none;">
                            	<h2>Show all images (<?php echo count($images) ?>)</h2>
                            	<a href="<?php echo $main_img; ?>" data-fancybox="gallery"><img src="<?php echo $main_img; ?>" alt=""></a>
                                <h4><?php echo gArrayItem($images[0],'caption')?></h4>
                                <?php 
                                for($i = 1; $i < count($images); $i++){ 
                                    $image_url = wp_get_attachment_image_url($images[$i]['image_id']);
                                    ?>
                                	<a href="<?php echo $image_url ?>" data-fancybox="gallery"><img src="<?php echo $image_url ?>" alt="" /></a>
                                    <h4><?php echo gArrayItem($images[$i],'caption') ?></h4>
                                    <?php 
                                } 
                                ?>
                            </div>                                                
                            
                    	</div>
                    </div>
                </div>
                <?php 
            }
            ?>
            <div class="app_feature">
            	<div class="row">
                	<div class="col-sm-12">
                        <h3><?php echo nl2br(gArrayItem($apartment,'description')) ?></h3>

                        <ul>
                            <li>
                                <div class="icon"><img src="<?php echo get_bloginfo('url') ?>/wp-content/uploads/2023/08/np_door_4955577_000000.svg" alt=""></div>
                                <h4><?php echo gArrayItem($apartment,'rooms') ?> rooms</h4>
                            </li>
                            <li>
                                <div class="icon"><img src="<?php echo get_bloginfo('url') ?>/wp-content/uploads/2023/08/np_bed_5969328_000000.svg" alt=""></div>
                                <h4><?php echo gArrayItem($apartment,'num_beds') ?> beds</h4>
                            </li>
                            <li>
                                <div class="icon"><img src="<?php echo get_bloginfo('url') ?>/wp-content/uploads/2023/08/np_users_1576306_000000.svg" alt=""></div>
                                <h4><?php echo gArrayItem($apartment,'max_guests') ?> guests</h4>
                            </li>
                            <li>
                                <div class="icon"><img src="<?php echo get_bloginfo('url') ?>/wp-content/uploads/2023/08/np_shower_1754028_000000.svg" alt=""></div>
                                <h4><?php echo gArrayItem($apartment,'num_bathrooms') ?> baths</h4>
                            </li>
                            <li>
                                <div class="icon"><img src="<?php echo get_bloginfo('url') ?>/wp-content/uploads/2023/08/np_size_3938157_000000.svg" alt=""></div>
                                <h4><?php echo gArrayItem($apartment,'area_sqm') ?> m2</h4>
                            </li>
                        </ul>
                	</div>
                </div>
            </div>
        </div>
    </div>
	<?php 
    $apt_facilities = json_decode(gArrayItem($apartment,'facilities'),true);
    if(!is_array($apt_facilities)) $apt_facilities = [];


    ?>
    <div class="app_content" id="app_content">
    	<div class="container">
        	<div class="app_content_inn">
                <div class="app_leftbar" id="app_leftbar">
                    <div class="app_facility" id="sec-facilities">
                        <?php 
                        if(count($apt_facilities) > 0){
                            ?>
                            <h3>Facilities</h3>
                            <ul>
                                <?php 
                                $i = 0;
                                foreach($apt_facilities as $f){
                                    if($i < 10){
                                        foreach($facilities as $facility){
                                            if($facility['facility_id'] == $f){
                                                $img =  get_bloginfo('url').'/assets/img/'.strtolower(str_replace(' ','-',$facility['name'])).'.svg';
                                                ?>
                								<li><span class="icon"><img src="<?php echo $img ?>" alt=""></span><?php echo $facility['name']; ?></li>
                                                <?php 
                                                $i++;
                                            }
                                        }
                                    }
                                }
                                ?>
                            </ul>
                            <?php
                            if(count($apt_facilities) > 10){    
        						?>
                                <a href="#facilities_data" data-touch="false" data-fancybox class="btn border_btn">Show all facilities (<?php echo count($apt_facilities) ?>)</a>
                                <div id="facilities_data" class="content_box facilities_pop_up" style="display:none;">
                                	<div class="popup_scroll">
                                        <h2>All facilities (<?php count($apt_facilities) ?>)</h2>
                                        <ul>
                                            <?php
                                            foreach($apt_facilities as $f){
                                                foreach($facilities as $facility){
                                                    if($facility['facility_id'] == $f){
                                                        $img =  get_bloginfo('url').'/assets/img/'.strtolower(str_replace(' ','-',$facility['name'])).'.svg';
                                                        ?>
                                                        <li><span class="icon"><img src="<?php echo $img ?>" alt=""></span><?php echo $facility['name']; ?></li>
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
                        }
                        ?>
                    </div>
                    <?php 
                    if(trim(gArrayItem($apartment,'about_this')) != ''){
                        ?>
                        <hr/>
                        <div class="app_about">
                            <h3>About this place</h3>
                            <?php 
                            if(trim(gArrayItem($apartment,'about_this_short')) != '') echo nl2br($apartment['about_this_short'].'..');
                            else nl2br(substr(gArrayItem($apartment,'about_this'),0,100).'..');
                            ?>
                        </div>
                        <?php 
                    }
                    ?>
                    <hr>
                    <div class="app_per_info" id="sec-practical_info">
                        <h3>Practical Information</h3>
                        <div class="app_per_info_inn">
                            <?php if(gArrayItem($apartment,'checkin_without_host') == 1){ ?>
                                <div class="block">
                                    <span class="icon"><img src="https://visitvietnam.dev.wiise.no/wp-content/uploads/2023/08/icon_10.svg" alt=""></span>
                                    <h4>Check-in without host</h4>
                                    <p>Personal check-in can be arranged</p>
                                </div>
                            <?php } ?>
                            <?php if(gArrayItem($apartment,'flexible_reservation') == 1){ ?>
                                <div class="block">
                                    <span class="icon"><img src="https://visitvietnam.dev.wiise.no/wp-content/uploads/2023/08/icon_11.svg" alt=""></span>
                                    <h4>Flexible reservation</h4>
                                    <p>Free of charge cancellation 2 days before arrival</p>
                                </div>
                            <?php } ?>
                            <?php if(gArrayItem($apartment,'airport_pickup') == 1){ ?>
                                <div class="block">
                                    <span class="icon"><img src="https://visitvietnam.dev.wiise.no/wp-content/uploads/2023/08/icon_12.svg" alt=""></span>
                                    <h4>Air Port Pick Up</h4>
                                    <p>Pick up at airport can be arranged</p>
                                </div>
                            <?php } ?>
                            <?php if(gArrayItem($apartment,'scooter_rental') == 1){ ?>
                                <div class="block">
                                    <span class="icon"><img src="https://visitvietnam.dev.wiise.no/wp-content/uploads/2023/08/icon_13.svg" alt=""></span>
                                    <h4>Scooters availability</h4>
                                    <p>We can arrange scooters for your stay</p>
                                </div>
                            <?php } ?>
                        </div>
                    </div>

                    <a href="#practical_information" data-touch="false" data-fancybox class="btn border_btn">Read more</a>
                    <div id="practical_information" class="content_box" style="display:none;">
                    	<div class="popup_scroll" style="color:#000">
							<?php $practical_pop_up = get_field('practical_pop_up'); ?>
    	                    <?php echo nl2br(gArrayItem($apartment,'about_this')) ?>
                        </div>
                    </div>
                    <hr/>
                    <div class="app_avability">
                        <h3>Apartment availability</h3>
                        <div class="input_wrap availability_sel">      
                          <input type="text" name="datefilter" placeholder="Select dates" class="availability_sel_picker" value="" />
                      </div>
                    </div>
                    <div class="app_here_app">
                        <h3>Here is the apartment<a href="#" class="map_open">Open in Google Maps</a></h3>
                        <div class="map_img">
                            <img src="<?php bloginfo('template_url'); ?>/images/map.png" alt="">
                        </div>
                        <div class="app_here_app_inn" id="sec-host">
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
                        <div class="app_book_block" id="app_book_block">
                            <h2>Book now</h2>
                            <h5>Add dates and check availability & prices</h5>
                            <hr>
                            
                                <script type="text/javascript" src="https://cdn.jsdelivr.net/jquery/latest/jquery.min.js"></script>
                                <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
                                <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
                                <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    
                                <script>
                                    $(function() {
                                        
                                        
                                            
                                            
                                                                      
                                          $('.start_date').daterangepicker({
                                              autoUpdateInput: false,
                                              alwaysShowCalendars:true,
                                              singleDatePicker: true,
                                              
                                                autoApply:true,
                                              minDate:new Date(),
                                              locale: {
                                                  cancelLabel: 'Clear'
                                              }
                                                
                                            });
                                        
                                    
                                          
    
    
                                          
                                          $('.end_date').daterangepicker({
                                              autoUpdateInput: false,
                                              alwaysShowCalendars:true,
                                              singleDatePicker: true,
                                              endDate: '08/29/2023',
                                                autoApply:true,
                                              minDate:new Date(),
                                              locale: {
                                                  cancelLabel: 'Clear'
                                              }
                                                
                                            });
                                        
                                          $('.end_date').on('apply.daterangepicker', function(ev, picker) {
                                              $(this).val(picker.startDate.format('MM/DD/YYYY'));
                                          });
                                        
                                          $('.end_date').on('cancel.daterangepicker', function(ev, picker) {
                                              $(this).val('');
                                          });
                                          
                                          
                                           
                                          
                                         
                                         
                                          
                                      
                                    });
                                    </script>
                                   
                             <div class="check_in_out_date">       
                                  <div class="input_wrap date_sel">      
                                      <label>Check in</label>
                                      <input type="text" name="datefilter" placeholder="Select dates" class="start_date" value="" />
                                  </div>
                                  <div class="input_wrap date_sel">      
                                      <label>Checkout</label>
                                      <input type="text" name="datefilter" placeholder="Select dates" class="end_date" value="" />
                                  </div>
                            </div>
                            <div class="guest_opt_wrap">
                                <div class="guest_opt">
                                    <div class="guest_opt_left">
                                        <label>Rooms</label>
                                        <span class="valtxt">Choose amount</span>
                                    </div>
                                    <div class="room_sel">
                                      <a href="#" class="room_btn">Guests</a>
                                      <div class="rooom_dropdown">
                                          <ul>
                                              <li>
                                                  <span class="lbltxt">Rooms<i>Choose amount of rooms</i></span>
                                                  <div class="number">
                                                    <span class="minus"></span>
                                                    <input type="text" value="2"/>
                                                    <span class="plus"></span>
                                                </div>
                                              </li>
                                              <li>
                                                  <span class="lbltxt">Adults<i>13 years old or older</i></span>
                                                  <div class="number">
                                                    <span class="minus"></span>
                                                    <input type="text" value="2"/>
                                                    <span class="plus"></span>
                                                </div>
                                              </li>
                                              <li>
                                                  <span class="lbltxt">Children<i>2 - 12 years old</i></span>
                                                  <div class="number">
                                                    <span class="minus"></span>
                                                    <input type="text" value="2"/>
                                                    <span class="plus"></span>
                                                </div>
                                              </li>
                                              <li>
                                                  <span class="lbltxt">Babies<i>Under 2 years old</i></span>
                                                  <div class="number">
                                                    <span class="minus"></span>
                                                    <input type="text" value="2"/>
                                                    <span class="plus"></span>
                                                </div>
                                              </li>
                                          </ul>
                                      </div>
                                  </div>
                                </div>
                            </div>
                            <a href="#" class="book_now_btn">Book now</a>
                            <div class="app_price">
                                <h2>Your price:</h2>
                                <ul>
                                    <li><span class="lbltxt">926 kr x 5 nights</span><span class="valtxt">4.630 kr</span></li>
                                    <li><span class="lbltxt">25% discount</span><span class="valtxt">- 1.157,5 kr</span></li>
                                    <li><span class="lbltxt">Cleaning fee</span><span class="valtxt">58 kr</span></li>
                                    <li><span class="lbltxt">Total</span><span class="valtxt"><i>4.688 kr</i>3.530,5 kr</span></li>
                                </ul>
                            </div>
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


<?php get_footer(); ?>