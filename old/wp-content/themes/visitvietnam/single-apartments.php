<?php
/**
 *
 * @package WordPress
 * @subpackage Custom
 * @since Custom 1.0
 */

get_header(); ?>

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
	<?php $top_section = get_field('top_section'); ?>
	<div class="single_app_top" id="menucontent_1">
    	<div class="container">
        	<div class="app_title">
            	<div>
                	<h1 class="heading-2"><?php the_title(); ?></h1>
                    <h5><img src="<?php bloginfo('template_url'); ?>/images/np_pin1.svg" alt=""><?php echo $top_section['location']; ?></h5>
                </div>
               
                <a href="#" class="share_link"> <img src="<?php bloginfo('template_url'); ?>/images/share.svg" alt=""> Share link</a> 
            </div>
            <?php $main_img = $top_section['main_image']; ?>
            <div class="app_gall">
            	<div class="app_gall_left">
                	<div class="gall_img">
                    	<a href="#images_data" data-fancybox class="gall_img_inn">
	                        <img src="<?php echo $main_img['url']; ?>" alt="">
						</a>
					</div>
                </div>
                <div class="app_gall_right">
                	<div class="app_gall_right_inn">
                        <div class="gall_col_wrap">
                        	<?php $images = $top_section['gallery']; $i=1; foreach( $images as $image_data ){ ?>
                                <?php if($i==1 || $i==3 || $i==5){ ?><div class="gall_col"><?php } ?>
                                	<?php if($i<=6){ ?>
                                        <div class="gall_img"><a href="#images_data" data-fancybox class="gall_img_inn"><img src="<?php echo $image_data['url']; ?>" alt=""></a></div>
                                    <?php } ?>
                               <?php if($i==2 || $i==4 || $i==6){ ?> </div><?php } ?>
                            <?php $i++; } ?>
                        </div>
                        
                        <?php $images = $top_section['gallery']; $images_count = count($images); ?>
                        <a href="#images_data" class="show_all" data-fancybox>Show all images (<?php echo $images_count+1; ?>)</a>
                        <div id="images_data" class="content_box" style="display:none;">
                        	<h2>Show all images (<?php echo $images_count+1; ?>)</h2>
                        	<a href="<?php echo $main_img['url']; ?>" data-fancybox="gallery"><img src="<?php echo $main_img['url']; ?>" alt=""></a>
                            <h4><?php echo $main_img['caption']; ?></h4>
                            <?php foreach( $images as $image_data ){ ?>
                            	<a href="<?php echo $image_data['url']; ?>" data-fancybox="gallery"><img src="<?php echo $image_data['url']; ?>" alt="" /></a>
                                <h4><?php echo $image_data['caption']; ?></h4>
                            <?php $i++; } ?>
                        </div>                                                
                        
                	</div>
                </div>
            </div>
            <div class="app_feature">
            	<div class="row">
                	<div class="col-sm-12">
                        <h3><?php echo $top_section['features_title']; ?></h3>
                        <ul>
							<?php $features = $top_section['features']; foreach($features as $feature_data){ ?>
								<li>
									<div class="icon"><img src="<?php echo $feature_data['icon']; ?>" alt=""></div>
									<h4><?php echo $feature_data['title']; ?></h4>
								</li>
                            <?php } ?>
                        </ul>
                	</div>
                </div>
            </div>
        </div>
    </div>
	<?php $facilities = get_field('facilities'); ?>
    <div class="app_content" id="app_content">
    	<div class="container">
        	<div class="app_content_inn">
                <div class="app_leftbar" id="app_leftbar">
                    <div class="app_facility" id="menucontent_2">
                        <h3><?php echo $facilities['title']; ?></h3>
                        <ul>
							<?php $facilitie = $facilities['facilities']; foreach($facilitie as $facilitie_data){ ?>
								<li><span class="icon"><img src="<?php echo $facilitie_data['icon']; ?>" alt=""></span><?php echo $facilitie_data['title']; ?></li>
							<?php } ?>
                        </ul>
                        <?php
							$facility_pop_up_12 = get_field('facility_pop_up');
							$facility_pop_up_data_12 = $facility_pop_up_12['facility_pop_up'];
							foreach($facility_pop_up_data_12 as $facility_pop_up_data_123){
								$facilities_12 = $facility_pop_up_data_123['facilities'];
								$count += count($facilities_12);
							}
						?>
                        <?php $facility_pop_up = get_field('facility_pop_up'); ?>
                        <a href="#facilities_data" data-touch="false" data-fancybox class="btn border_btn">Show all facilities (<?php echo $count; ?>)</a>
                        <div id="facilities_data" class="content_box facilities_pop_up" style="display:none;">
                        	<div class="popup_scroll">
                                <h2>All facilities (<?php echo $count; ?>)</h2>
                                <?php $facility_pop_up_data = $facility_pop_up['facility_pop_up']; foreach($facility_pop_up_data as $facility_pop_up_data_new){ ?>
                                    <h4><?php echo $facility_pop_up_data_new['title']; ?></h4>
                                    <ul>
                                    <?php $count = count($facility_pop_up_data_new['facilities']); ?>
                                        <?php $facilitie = $facility_pop_up_data_new['facilities']; foreach($facilitie as $facilitie_data){ ?>
                                            <li><span class="icon"><img src="<?php echo $facilitie_data['icon']; ?>" alt=""></span><?php echo $facilitie_data['title']; ?></li>
                                        <?php } ?>
                                    </ul>
                                <?php } ?>
                        	</div>
                        </div>
                        <hr/>
                    </div>
					<?php $about = get_field('about'); ?>
                    <div class="app_about">
                        <?php echo $about['text']; ?>
                    </div>
					<?php $practical_information = get_field('practical_information'); ?>
                    <div class="app_per_info" id="menucontent_3">
                        <h3><?php echo $practical_information['title']; ?></h3>
                        <div class="app_per_info_inn">
                            <?php $practicals = $practical_information['practicals']; foreach($practicals as $practicals_data){ ?>
								<div class="block">
									<span class="icon"><img src="<?php echo $practicals_data['icon']; ?>" alt=""></span>
									<h4><?php echo $practicals_data['title']; ?></h4>
									<p><?php echo $practicals_data['text']; ?></p>
								</div>
                            <?php } ?>
                        </div>
                    </div>
					<?php $rules_security_and_property = get_field('rules_security_and_property'); ?>
                    <div class="app_per_info_bot">
						<?php foreach($rules_security_and_property as $rules_security_and_property_data){ ?>
							<div class="block">
								<?php echo $rules_security_and_property_data['text']; ?>
							</div>
                        <?php } ?>
                    </div>
                    <a href="#practical_information" data-touch="false" data-fancybox class="btn border_btn">Read more</a>
                    <div id="practical_information" class="content_box" style="display:none;">
                    	<div class="popup_scroll">
							<?php $practical_pop_up = get_field('practical_pop_up'); ?>
    	                    <?php echo $practical_pop_up['text']; ?>
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

<?php endwhile; // end of the loop. ?>

<?php get_footer(); ?>