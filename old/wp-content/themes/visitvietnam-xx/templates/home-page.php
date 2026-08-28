<?php
/**
 * Template Name: Home Page
 * @package WordPress
 * @subpackage Custom
 * @since Custom 1.0
 */

get_header(); ?>

<?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>    
	
    <?php while ( have_rows('content_blocks') ) : the_row(); ?>
    	<?php if( get_row_layout() == 'header' ){ ?>
            <div id="home_slider">
                <div class="cap">
                  <h1><?php the_sub_field('title'); ?></h1>
                  <?php the_sub_field('text'); ?>
                  <div class="book_block">
                      <div class="input_wrap choose_city_sel">
                          <label>Where</label>
                          <select><option>Choose city</option></select>
                      </div>
                      <div class="input_wrap room_sel">
                          <label>Rooms</label>
                          <select><option>Guests</option></select>
                      </div>
                      <div class="input_wrap date_sel">
                          <label>Check in & out</label>
                          <select><option>Select dates</option></select>
                      </div>
                      <div class="input_submit">
                          <input type="submit" value="Search"> 
                      </div>
                  </div>
                </div>
                <div class="swiper mySwiper">
                    <div class="swiper-wrapper">
                      <?php $images = get_sub_field('images'); foreach($images as $image_data){ ?>
                          <div class="swiper-slide">
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
                        <?php foreach(get_terms('apartments_cat', array('parent' => 0)) as $cat): $cat_link = get_term_link( $cat ); ?>  
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
										<span class="per"><?php echo $campaign_data['percentage']; ?>%</span>
										<div class="cap">
											<div class="old_price"><?php echo $campaign_data['old_price']; ?> NOK</div>
											<div class="new_price"><?php echo $campaign_data['new_price']; ?> NOK</div>
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
                            	<?php $title_box = get_sub_field('title_box'); foreach($title_box as $title_box_data){ ?>
                                    <div class="title_box">
                                        <div class="icon"><img src="<?php echo $title_box_data['icon']; ?>" alt=""></div>
                                        <h3><?php echo $title_box_data['title']; ?> <a href="#" class="read_more">Read more</a></h3>
                                    </div>
                                <?php } ?>
                            </div>
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
                                                    <a href="<?php echo $image_video_silder_data['video']; ?>" class="play_btn fancybox-youtube"></a>
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
                                    <a href="<?php the_sub_field('video'); ?>" class="play_btn fancybox-youtube"></a>
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

<?php get_footer(); ?>