<?php
/**
 * Template Name: Home Page
 * @package WordPress
 * @subpackage Custom
 * @since Custom 1.0
 */

$cities = vv_get_cities();

global $head_codes;
?>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<style type="text/css">
    #home_slider .book_block .input_wrap.choose_city_sel{
        min-width: 230px;
        padding: 10px 30px 10px 70px;
    }
    #home_slider .book_block .input_wrap.room_sel{
        min-width: 260px;
        padding: 10px 30px 10px 70px;
    }

    #home_slider .book_block .room_btn{
        font-size:13px;
    }
</style>
<?php
$head_codes .= ob_get_clean();

get_header(); ?>

<?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>    
	
    <?php while ( have_rows('content_blocks') ) : the_row(); ?>
    	<?php if( get_row_layout() == 'header' ){ ?>
            <div id="home_slider">
                <div class="cap">
                  <?php /*?><h1><?php the_sub_field('title'); ?></h1>
                  <?php the_sub_field('text'); ?><?php */?>
                  <form method="get" action="<?php bloginfo('url') ?>/apartments" id="formBooking" >
                        <input type="hidden" name="city_id" value="">
                        <input type="hidden" name="vv_action" value="booking_search">
                        <div class="book_block">
                          <div class="input_wrap choose_city_sel">
                              <label>Where</label>
                              <a href="#" class="room_btn city_label">Choose city</a>
                              <div class="city_dropdown book_dropdown">
                              	     <ul>
                                        <?php foreach($cities as $city){ ?>
                                  	         <li><a href="#" data-city_id="<?php echo gArrayItem($city,'ID') ?>" ><strong><?php echo gArrayItem($city,'post_title') ?></strong></a></li>
                                        <?php } ?>
                                    </ul>
                              </div>
                          </div>
                          <div class="input_wrap room_sel">
                              <label>Rooms & Guests</label>
                              <a href="#" class="room_btn rooms_guests_label">2 rooms & 2 guests</a>
                              <div class="rooom_dropdown book_dropdown">
                              	  <ul>
                                  	  <li>
                                      	  <span class="lbltxt">Rooms<i>Choose amount of rooms</i></span>
                                          <div class="number">
                                            <span class="minus"></span>
                                            <input type="text" name="rooms" data-min="1" value="2"/>
                                            <span class="plus"></span>
                                        </div>
                                      </li>
                                      <li>
                                      	  <span class="lbltxt">Adults<i>13 years old or older</i></span>
                                          <div class="number">
                                            <span class="minus"></span>
                                            <input type="text" name="adults" data-min="1" value="2"/>
                                            <span class="plus"></span>
                                        </div>
                                      </li>
                                      <li>
                                      	  <span class="lbltxt">Children<i>below 13 years old</i></span>
                                          <div class="number">
                                            <span class="minus"></span>
                                            <input type="text" name="children" value="0"/>
                                            <span class="plus"></span>
                                        </div>
                                      </li>
                                  </ul>
                              </div>
                          </div>
                          <div class="input_wrap date_sel">
                              <label>Check in & out</label>
                              <input type="text" name="datefilter" placeholder="Select dates" value="" />
                          </div>
                          <div class="input_submit">
                              <input type="submit" value="Search"> 
                          </div>
                        </div>
                    </form>
                </div>
                <div class="swiper mySwiper">
                    <div class="swiper-wrapper">
                      <?php $images = get_sub_field('images'); foreach($images as $image_data){ ?>
                          <div class="swiper-slide">
                          	  <div class="slider_cap">
    	                      	  <h1><?php echo $image_data['title']; ?></h1>
	                              <?php echo $image_data['text']; ?>
                              </div>
                              <div class="slider_img" style="background-image:url(<?php echo $image_data['image']; ?>);"></div>
                              
                          </div>
                      <?php } ?>
                    </div>
                    <div class="swiper-pagination"></div>
                </div>
            </div>
		<?php } ?>
        <?php if( get_row_layout() == 'districts' ){ ?>
            <div id="explore">
                <div class="container">
                    <div class="top_title">
                        <h2 class="heading-1">Explore our Exclusive Stays</h2>
                        <div class="prev_next">
                            <div class="swiper-button-prev"></div>
                            <div class="swiper-button-next"></div>
                        </div>
                    </div>
                    <div class="swiper explore_slider">
                        <div class="swiper-wrapper">
                        <?php foreach(get_terms('city_cat', array('parent' => 0)) as $cat): $cat_link = get_term_link( $cat ); ?>  
                          <div class="swiper-slide">
                              <a href="<?php echo esc_url( $cat_link ); ?>" class="explore_block" style="background-image:url(<?php the_field('image',$cat); ?>);">
                                  <div class="cap">
                                      <h4><?php the_field('city_name',$cat); ?></h4>
                                      <h3><?php echo $cat->name; ?></h3>	
                                  </div>
                                  <span class="btn">Show available apartments</span>	
                              </a>
                          </div>
                         <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
		<?php } ?>
        <?php if( get_row_layout() == 'membership' ){ ?>
            <div id="free_membership">
                <div class="shield_img"><img src="<?php bloginfo('template_url'); ?>/images/shield.png" alt=""></div>
                <div class="container">
                    <h2 class="heading-1"><?php the_sub_field('title'); ?></h2>
                    <?php the_sub_field('text'); ?>
                    <?php $button = get_sub_field('button'); if($button){ ?>
                    	<a href="<?php echo $button['url']; ?>" target="<?php echo $button['target']; ?>" class="btn"><?php echo $button['title']; ?></a>
                    <?php } ?>
                    <hr/>
                </div>
            </div>
		<?php } ?>
		<?php if( get_row_layout() == 'campaigns' ){ ?>
			<div id="campaigns">
				<div class="container">
					<h3><?php the_sub_field('title'); ?></h3>
					<div class="offer_list">
						<div class="row">
							<?php $i=1; $campaigns = get_sub_field('campaigns'); foreach($campaigns as $campaign_data){ ?>
								<div class="col-sm-4">
									<a href="<?php echo $campaign_data['link']; ?>" class="offer_block <?php if($i>3){ ?>disable<?php } ?>" style="background-image:url(<?php echo $campaign_data['image']; ?>);">
										<?php if($campaign_data['percentage']){ ?><span class="per"><?php echo $campaign_data['percentage']; ?>%</span><?php } ?>
										<div class="cap">
											<?php if($campaign_data['old_price']){ ?><div class="old_price"><?php echo $campaign_data['old_price']; ?> NOK</div><?php } ?>
											<?php if($campaign_data['new_price']){ ?><div class="new_price"><?php echo $campaign_data['new_price']; ?> NOK</div><?php } ?>
											<p><?php echo $campaign_data['text']; ?></p>
										</div>
									</a>
								</div>
							<?php $i++; } ?>
						</div>
					</div>
				</div>
				<?php $button_campaigns = get_sub_field('button'); if($button_campaigns){ ?>
					<a href="<?php echo $button_campaigns['url']; ?>" target="<?php echo $button_campaigns['target']; ?>" class="seel_all_btn btn"><?php echo $button_campaigns['title']; ?><img src="<?php bloginfo('template_url'); ?>/images/tag.svg" alt=""></a>
				<?php } ?>
			</div>
		<?php } ?>
        <?php if( get_row_layout() == 'we_make_it_easy_for_you_and_your_family' ){ ?>
            <div id="we_make">
                <div class="container">
                    <h2 class="heading-1"><?php the_sub_field('title'); ?></h2>
                    <div class="row v_flex">
                        <div class="col-md-6">
                        	<div class="we_make_left">
                            	<?php $k=1; $title_box = get_sub_field('title_box'); foreach($title_box as $title_box_data){ ?>
                                    <div class="title_box">
                                        <div class="icon"><img src="<?php echo $title_box_data['icon']; ?>" alt=""></div>
                                        <a href="#title_box_<?php echo $k; ?>" data-touch="false" data-fancybox>
                                            <h3><?php echo $title_box_data['title']; ?></h3>
                                            <span class="title_box_link"><img src="<?php bloginfo('template_url'); ?>/images/next_1.svg" /></span>
                                        </a>
                                        <div id="title_box_<?php echo $k; ?>" class="content_box" style="display:none;">
                                        	<div class="popup_scroll">
                                                <img src="<?php echo $title_box_data['image']; ?>" alt="" />
                                                <h2><?php echo $title_box_data['title']; ?></h2>
                                                <?php echo $title_box_data['text']; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php $k++; } ?>
                            </div>
                            <?php /*?><div class="we_make_left">
                            	<?php $k=1; $facility_pop_up = get_sub_field('facility_pop_up'); foreach($facility_pop_up as $facility_pop_up_data){ ?>
                                    <div class="title_box">
                                        <div class="icon"><img src="<?php echo $facility_pop_up_data['icon']; ?>" alt=""></div>
                                        <a href="#title_box_<?php echo $k; ?>" class="fancybox-inline">
                                            <h3><?php echo $facility_pop_up_data['title']; ?></h3>
                                            <span class="title_box_link"><img src="<?php bloginfo('template_url'); ?>/images/next_1.svg" /></span>
                                        </a>
                                        <div id="title_box_<?php echo $k; ?>" class="fancybox-hidden content_box">
                                        	<h3><?php echo $facility_pop_up_data['title']; ?></h3>
                                        	<ul>
												<?php $facilitie = $facility_pop_up_data['facilities']; foreach($facilitie as $facilitie_data){ ?>
                                                    <li><span class="icon"><img src="<?php echo $facilitie_data['icon']; ?>" alt=""></span><?php echo $facilitie_data['title']; ?></li>
                                                <?php } ?>
                                            </ul>
                                        </div>
                                    </div>
                                <?php $k++; } ?>
                            </div><?php */?>
                        </div>
                        <div class="col-md-6">
                            <div class="we_make_slider">
                                <div class="swiper mySwiper2">
                                    <div class="swiper-wrapper">
                                      <?php $image_video_silder = get_sub_field('image_video_silder'); foreach($image_video_silder as $image_video_silder_data){ ?>
                                          <div class="swiper-slide">
											<?php $image_video = $image_video_silder_data['image_video']; if($image_video == 'image'){ ?>
                                              <div class="slider_img">	
                                                  <img src="<?php echo $image_video_silder_data['image']; ?>" alt="">
                                              </div>
                                            <?php }else{ ?>
                                                <div class="video_block">
                                                    <img src="<?php echo $image_video_silder_data['video_image']; ?>" alt="">
                                                    <a href="<?php echo $image_video_silder_data['video']; ?>" class="play_btn" data-touch="false" data-fancybox></a>
                                                </div>
                                            <?php } ?>
                                          </div>
                                      <?php } ?>
                                    </div>
                                    <div class="swiper-pagination"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
		<?php } ?>
        <?php if( get_row_layout() == 'the_benefits_of_an_exclusive_stay' ){ ?>
            <div id="benifits">
                <div class="container">
                    <h2 class="heading-1"><?php the_sub_field('title'); ?></h2>
                    <div class="row v_flex">
                        <div class="col-md-6">
                        	<?php $image_video = get_sub_field('image_video'); if($image_video == 'image'){ ?>
                            	<div class="video_block">
                                    <img src="<?php the_sub_field('image'); ?>" alt="">
                                </div>
                            <?php }else{ ?>
                                <div class="video_block">
                                    <img src="<?php the_sub_field('video_image'); ?>" alt="">
                                    <a href="<?php the_sub_field('video'); ?>" class="play_btn" data-touch="false" data-fancybox></a>
                                </div>
                        	<?php } ?>
                        </div>
                        <div class="col-md-6">
                            <div class="desc">
                                <ul>
                                	<?php $points = get_sub_field('points'); foreach($points as $point_data){ ?>
	                                    <li><span class="icon"><img src="<?php echo $point_data['icon']; ?>" alt=""></span><?php echo $point_data['title']; ?></li>
                                    <?php } ?>
                                </ul>
                                <?php $button_book = get_sub_field('button'); if($button_book){ ?>
	                                <a href="<?php echo $button_book['url']; ?>" target="<?php echo $button_book['target']; ?>" class="btn yellow_btn"><?php echo $button_book['title']; ?></a>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
		<?php } ?>
    <?php endwhile; ?>
	
<?php endwhile; // end of the loop. ?>

<?php 
global $footer_codes;
ob_start();

?>
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

<script>
    function countRoomsGuests(){
        let $ = jQuery.noConflict();

        rooms = $('input[name="rooms"]').val();
        adults = $('input[name="adults"]').val();
        children = $('input[name="children"]').val();

        guests = parseInt(adults) + parseInt(children);

        label = rooms;
        label += (rooms > 1) ? ' rooms' : ' room';
        label += '/'+guests;
        label += (guests > 1) ? ' guests' : ' guest';

        $('.rooms_guests_label').html(label);
            
    }
    jQuery(function() {
        let $ = jQuery.noConflict();
        $('input[name="datefilter"]').daterangepicker({
              autoUpdateInput: false,
              autoApply:true,
              minDate:new Date(),

              locale: {
                  cancelLabel: 'Clear'
              }
          });

        $('input[name="datefilter"]').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('MM/DD/YYYY') + ' - ' + picker.endDate.format('MM/DD/YYYY'));
        });

        $('input[name="datefilter"]').on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
        });

        $('input[name="datefilter"]').on('show.daterangepicker', function(ev, picker) {
            $('body').addClass('book_overlay_open');
            $('#home_slider .book_block .room_sel').removeClass('open');
            $('#home_slider .book_block .choose_city_sel').removeClass('open');
        });

        $('input[name="datefilter"]').on('hide.daterangepicker', function(ev, picker) {
              $('body').removeClass('book_overlay_open');      
        });

        $('.minus').click(function () {
            $input = $(this).parent().find('input');
            count = parseInt($input.val());
            min = $input.attr('data-min');
            if(typeof min == 'undefined') min = 0;

            if(count > min) count--;
            $input.val(count);
            $input.change();
            countRoomsGuests();
            return false;
        });

        $('.plus').click(function () {
            var $input = $(this).parent().find('input');
            $input.val(parseInt($input.val()) + 1);
            $input.change();
            countRoomsGuests();
            return false;
        });
        
        $('.city_dropdown').find('a').click(function (e){
            text = $(this).text();
            id = $(this).attr('data-city_id');
            $('.city_label').html(text);
            $('input[name="city_id"]').val(id);
            $('.choose_city_sel ').removeClass('open');
            e.preventDefault();
        });

        $('body').click(function (event){
            if($(this).hasClass('book_overlay_open')){
            }
        });
    });

</script>
<?php
$footer_codes .= ob_get_clean();

get_footer(); ?>