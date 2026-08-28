<?php
/**
 *
 * @package WordPress
 * @subpackage Custom
 * @since Custom 1.0
 */
?>	
	<?php $footer = get_field('footer','option'); ?>
    <div id="footer">
    	<div class="skyline_img_2">
        	<img src="<?php bloginfo('template_url'); ?>/images/skyline2.png" alt="">
        </div>
        <div class="footer">
        	<div class="container">	
            	<div class="f_inn">
                	<p><?php echo $footer['copyright']; ?></p>
                    <div class="social">
                    	<a href="<?php echo $footer['facebook']; ?>" target="_blank"><img src="<?php bloginfo('template_url'); ?>/images/fb.svg" alt=""></a>
                        <a href="<?php echo $footer['instagram']; ?>" target="_blank"><img src="<?php bloginfo('template_url'); ?>/images/insta.svg" alt=""></a>
                    </div>
                    <?php wp_nav_menu( array( 'theme_location' => 'footer-menu', 'container' => '' ) ); ?>
                </div>
            </div>
        </div>
    </div>

<?php wp_footer(); ?>

<?php 
global $footer_codes;
echo $footer_codes;
?>

</body>
</html>