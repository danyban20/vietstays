<?php
/**
 *
 * @package WordPress
 * @subpackage Custom
 * @since Custom 1.0
 */

get_header(); ?>     

	<?php $cat = get_queried_object(); ?>
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
                <?php $args = array(
						   'post_type' => 'apartments',
						   'posts_per_page' => -1,
						   'tax_query' => array(
						   array(
							 'taxonomy' => 'apartments_cat',
							 'field'    => 'slug',
							'terms'    => $cat->slug // Pass this to slug
						),
					),
				);                            
				$my_query = new WP_Query($args);
					while ($my_query->have_posts()) : $my_query->the_post(); ?>
					  <div class="swiper-slide">
						  <a href="<?php the_permalink(); ?>" class="explore_block" style="background-image:url(<?php the_post_thumbnail_url(); ?>);">
							  <div class="cap">
								  <h4><?php the_field('city_name'); ?></h4>
								  <h3><?php the_title(); ?></h3>	
							  </div>
							  <span class="btn">Show apartment</span>	
						  </a>
					  </div>
                 <?php endwhile; wp_reset_postdata(); ?>
                </div>
            </div>
        </div>
    </div>

<?php get_footer(); ?>