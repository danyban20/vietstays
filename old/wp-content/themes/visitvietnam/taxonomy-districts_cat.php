<?php
/**
 *
 * @package WordPress
 * @subpackage Custom
 * @since Custom 1.0
 */

get_header(); ?>     
	
    <?php $districts_cat = get_queried_object(); ?>
    <?php $header_section = get_field('header_section',$districts_cat); ?>
    <div id="city_banner" style="background:url(<?php echo $header_section['bg_image']; ?>)">
		<div class="container">
			<h1 class="heading-2"><?php echo $districts_cat->name; ?></h1>
			<?php echo $header_section['text']; ?>
		</div>
	</div>	
	<div id="city_wrap">
		<div class="container">
        	<?php /*?><?php
				if ( function_exists('yoast_breadcrumb') ) {
				  yoast_breadcrumb( '<p id="breadcrumbs">','</p>' );
				}
			?><?php */?>
            <div class="breadcrumbs">
            	<ul>
                	<li><a href="<?php echo get_home_url(); ?>">Home</a><span class="sep">/</span></li>
                    <li>Cities<span class="sep">/</span></li>
                    <li><?php echo $districts_cat->name; ?></li>
                </ul>
            </div>
			<h2 class="heading-2">Apartments from <?php echo $districts_cat->name; ?></h2>
			<div class="city_filter">
				<div class="city_filter_left">
				<select name="boxes" class="sel_filter"><option value="all">Rooms & spaces</option><option value="opt1">Option 1</option><option value="opt2">Option 2</option><option value="opt3">Option 3</option></select>
					<select><option>Price</option></select>
					<select><option>Sort by recommended</option></select>
				</div>
				<div class="city_filter_right">
					<a href="#" class="clear_all_btn">Clear All</a>
				</div>		
			</div>
			<div class="city_feat">	
				<ul>
					<li class="all opt1"><a href="#"><span class="icon"><img src="<?php bloginfo('template_url'); ?>/images/beach.svg"><img src="<?php bloginfo('template_url'); ?>/images/beach_h.svg" class="h_icon"></span>Near beach (0)</a></li>
					<li class="all opt2"><a href="#"><span class="icon"><img src="<?php bloginfo('template_url'); ?>/images/view_to_city.svg"><img src="<?php bloginfo('template_url'); ?>/images/view_to_city_h.svg" class="h_icon"></span>view to city</a></li>
					<li class="all opt3"><a href="#"><span class="icon"><img src="<?php bloginfo('template_url'); ?>/images/sea_view.svg"><img src="<?php bloginfo('template_url'); ?>/images/sea_view_h.svg" class="h_icon"></span>sea view</a></li>
					<li class="all opt4"><a href="#"><span class="icon"><img src="<?php bloginfo('template_url'); ?>/images/mountain_view.svg"><img src="<?php bloginfo('template_url'); ?>/images/mountain_view_h.svg" class="h_icon"></span>mountain view</a></li>
					<li class="all"><a href="#"><span class="icon"><img src="<?php bloginfo('template_url'); ?>/images/fitness.svg"><img src="<?php bloginfo('template_url'); ?>/images/fitness_h.svg" class="h_icon"></span>fitness</a></li>
					<li class="all"><a href="#"><span class="icon"><img src="<?php bloginfo('template_url'); ?>/images/swimming_pool.svg"><img src="<?php bloginfo('template_url'); ?>/images/swimming_pool_h.svg" class="h_icon"></span>swimming pool</a></li>
					<li class="all"><a href="#"><span class="icon"><img src="<?php bloginfo('template_url'); ?>/images/kitchen.svg"><img src="<?php bloginfo('template_url'); ?>/images/kitchen_h.svg" class="h_icon"></span>kitchen</a></li>
					<li class="all"><a href="#"><span class="icon"><img src="<?php bloginfo('template_url'); ?>/images/security_cameras.svg"><img src="<?php bloginfo('template_url'); ?>/images/security_cameras_h.svg" class="h_icon"></span>security cameras</a></li>
				</ul>
			</div>	
            <?php 
				$i=0;
				$args = array(
						   'post_type' => 'apartments',
						   'posts_per_page' => -1,
						   'tax_query' => array(
						   array(
							 'taxonomy' => 'districts_cat',
							 'field'    => 'slug',
							'terms'    => $districts_cat->slug // Pass this to slug
						),
					),
				);                            
				$my_query = new WP_Query($args);
					while ($my_query->have_posts()) : $my_query->the_post(); ?>
                     <?php $i++; endwhile; wp_reset_postdata(); ?>
			<div class="city_app_wrap tab_content show_div" id="tab_content_1">
				<div class="city_display_title_left city_display_title_left_dist">
					<h3>Now displaying <?php echo $i; ?> apartments at <?php echo $districts_cat->name; ?></h3>
				</div>	
				<div class="city_app_list">
					<div class="row">
                    	<?php $args = array(
						   'post_type' => 'apartments',
						   'posts_per_page' => -1,
						   'tax_query' => array(
						   array(
							 'taxonomy' => 'districts_cat',
							 'field'    => 'slug',
							'terms'    => $districts_cat->slug // Pass this to slug
						),
					),
				);                            
				$my_query = new WP_Query($args);
					while ($my_query->have_posts()) : $my_query->the_post(); ?>
						<div class="col-sm-4">
							<div class="city_app_block_1">
								<a href="<?php the_permalink(); ?>" class="city_app_block_1_inner">
									<div class="img"><?php the_post_thumbnail(); ?></div>
									<span class="fav"></span>
									<?php if(get_field('discount')){ ?><span class="discount"><?php the_field('discount'); ?>%</span><?php } ?>
									<div class="cap">
										<?php if(get_field('normal_price')){ ?><div class="price"><strong><?php the_field('normal_price'); ?> NOK</strong><?php the_field('offer_price'); ?> NOK</div><?php } ?>
										<p><?php the_title(); ?></p>
									</div>
								</a>	
							</div>	
						</div>
               <?php endwhile; wp_reset_postdata(); ?>
					</div>
					<?php /*?><div class="show_more_btn"><a href="#">Show more</a></div><?php */?>
				</div>
			</div>
			
		</div>
		<div id="district_facility">
			<div class="container">
				<div class="row">
					<div class="col-sm-6">
						<div class="district_facility_left">
							<h2>Facilities</h2>
							<ul>
                                <?php
									$i=1;
									$field = get_field_object('facilities',$districts_cat);
									$selected_choices = get_field('facilities',$districts_cat);
									if ($field) {
										foreach ($field['choices'] as $value => $label) {
											if (in_array($value, $selected_choices)) { ?>
												<li><span class="icon"><img src="<?php bloginfo('template_url'); ?>/images/faclity_<?php echo $i; ?>.svg"></span><?php echo esc_html($label); ?></li>
											<?php } 
										$i++; }
									}
								?>
							</ul>  
                            <a href="#facilities_data" data-touch="false" data-fancybox class="see_all_btn">Show all facilities</a>
                            <div id="facilities_data" class="content_box facilities_pop_up" style="display:none;">
                                <div class="popup_scroll">
                                    <h2>All facilities</h2>
                                    <ul>
                                        <?php
											$i=1;
											$field = get_field_object('facilities_popup',$districts_cat);
											$selected_choices = get_field('facilities_popup',$districts_cat);
											if ($field) {
												foreach ($field['choices'] as $value => $label) {
													if (in_array($value, $selected_choices)) { ?>
														<li><span class="icon"><img src="<?php bloginfo('template_url'); ?>/images/faclity_<?php echo $i; ?>.svg"></span><?php echo esc_html($label); ?></li>
													<?php } 
												$i++; }
											}
										?>
                                    </ul>
                                </div>
                            </div>
						</div>
					</div>
					<div class="col-sm-6">
						<div class="district_facility_title">
							<h3>Nearby attractions</h3>
							<a href="#" class="open_map">Open in Google Maps</a>
						</div>
						<div class="district_facility_map"><img src="<?php bloginfo('template_url'); ?>/images/district_facility_map.png"></div>
					</div>
				</div>    
			</div>    
		</div>
		<div id="other_city">	
			<div class="container">
				<h2>Other Districts</h2>
				<div class="row">
                	<?php foreach(get_terms('districts_cat', array('parent' => 0, 'exclude'  => array($districts_cat->term_id))) as $districts_cat_data): ?>
                        <div class="col-sm-4">
                            <a href="<?php echo get_term_link($districts_cat_data); ?>" class="other_city_block" style="background-image:url(<?php the_field('image',$districts_cat_data); ?>);">
                                <div class="cap">
                                    <h3><?php echo $districts_cat_data->name; ?></h3>	
                                </div>
                                <span class="btn">Show available apartments</span>	
                            </a>
                        </div>
					<?php endforeach; ?>
				</div>
			</div>	
		</div>	
	</div>

<?php get_footer(); ?>