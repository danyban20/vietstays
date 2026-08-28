<?php
/**
 *
 * @package WordPress
 * @subpackage Custom
 * @since Custom 1.0
 */

get_header(); ?>

<?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>        
    
    <div id="content">
    	<div class="container small-container">
        	<div class="text-block typography">             
				<?php the_content(); ?> 
			</div>
		</div>
	</div>    

<?php endwhile; // end of the loop. ?>

<?php get_footer(); ?>