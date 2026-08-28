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
                <div class="news-date"><?php echo get_the_date(); ?></div>
                <h2 class="h1"><?php the_title(); ?></h2>    
                <div class="news-image">
                    <?php the_post_thumbnail('large'); ?>
                </div>      
                <?php the_content(); ?> 
			</div>
		</div>                               
		<?php while ( have_rows('content_blocks') ) : the_row(); ?>        
            <?php if(get_row_layout()=='text_block'): get_template_part('blocks/text-block'); endif; ?>
            <?php if(get_row_layout()=='single_image'): get_template_part('blocks/single-image'); endif; ?>
            <?php if(get_row_layout()=='gallery'): get_template_part('blocks/gallery'); endif; ?>
            <?php if(get_row_layout()=='blockquote'): get_template_part('blocks/blockquote'); endif; ?>
            <?php if(get_row_layout()=='table'): get_template_part('blocks/table'); endif; ?>                                     
        <?php endwhile; ?>
	</div>    

<?php endwhile; // end of the loop. ?>

<?php get_footer(); ?>