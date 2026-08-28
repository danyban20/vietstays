<?php
/**
 *
 * @package WordPress
 * @subpackage Custom
 * @since Custom 1.0
 */

get_header(); ?>	
    
    <div id="content">
   		<div class="container">             
            <div class="col-row">
                <?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>
                <div class="col-md-4 col-sm-6">                    	
                    <div class="news-block">
                        <div class="img">
                            <?php if(has_post_thumbnail()) { ?>
                                <a href="<?php the_permalink(); ?>"><?php the_post_thumbnail('thumbnail'); ?></a>
                            <?php } else { ?>
                                <a href="<?php the_permalink(); ?>"><img src="<?php bloginfo('template_url'); ?>/images/no-image.jpg" alt=""></a>
                            <?php } ?>
                        </div>
                        <div class="desc">
                            <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                            <div class="date"><?php echo get_the_date(); ?></div>
                            <?php the_excerpt(); ?>
                        </div>
                        <a href="<?php the_permalink(); ?>" class="btn-link">Meer info</a>                       
                    </div>                        
                </div>
                <?php endwhile; ?>
            </div>                                                  
            <?php if($wp_query->max_num_pages > 1): ?>
            <div class="paginate-links">
				<?php echo paginate_links(array('prev_next'=>false)); ?>
            </div> 
            <?php endif; ?>
		</div>
	</div>                    

<?php get_footer(); ?>