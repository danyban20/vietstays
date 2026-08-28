<?php
/**
 * Template Name: Share your apartment page
 * @package WordPress
 * @subpackage Custom
 * @since Custom 1.0
 */

get_header(); ?>

<?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>    

    <div id="share_app">
    	<?php $you_earn = get_field('you_earn'); ?>
        <div id="howmuch_earn">
            <div class="container">
                <h1><?php echo $you_earn['title']; ?></h1>
                <div class="price"><?php echo $you_earn['price']; ?></div>
                <div class="desc">
                    <div class="price_img"><img src="<?php bloginfo('template_url'); ?>/images/price_img.png" alt=""></div>
                    <select><option>Choose your city</option></select>
                    <select><option>Choose rooms</option></select>
                    <div class="days_slider_wrap">
                        <input type="text" class="js-range-slider" id="days_slider" name="my_range" value="" />
                    </div>
                </div>
            </div>
        </div>
        <?php $customer_loyalty_programme = get_field('customer_loyalty_programme'); ?>
        <div id="cust_program">  
            <div class="container">
                <h2><?php echo $customer_loyalty_programme['title']; ?></h2>
                <h4><?php echo $customer_loyalty_programme['subtitle']; ?></h4>
                <div class="row">
                    <div class="col-sm-6">
                        <div class="img">
                        	<?php echo wp_get_attachment_image($customer_loyalty_programme['image'], ''); ?>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div id="accordian">
                        	<?php $points = $customer_loyalty_programme['points']; foreach($points as $points_data){ ?>
                                <div class="acc_box">
                                    <span class="icon"><img src="<?php bloginfo('template_url'); ?>/images/checkmark_icon.svg"></span>
                                    <h3><?php echo $points_data['title']; ?></h3>
                                    <div class="acc_content">
                                        <?php echo $points_data['text']; ?>
                                    </div>    
                                </div>    
                            <?php } ?>    
                        </div>    
                    </div>
                </div>    
            </div>
        </div>
        <?php $get_started = get_field('get_started'); ?>
        <div id="get_started">
            <div class="container">
                <h2><?php echo $get_started['title']; ?></h2>
                <div class="row">
                	<?php $i=1; $points_to_get_started = $get_started['points_to_get_started']; foreach($points_to_get_started as $points_to_get_started_data){ ?>
                        <div class="col-sm-4">
                            <div class="get_started_block">
                                <span class="num"><?php echo $i; ?></span>
                                <h3><?php echo $points_to_get_started_data['title']; ?></h3>
                                <?php echo $points_to_get_started_data['text']; ?>
                            </div>    
                        </div>
                    <?php $i++; } ?>    
                </div>
                <?php $button = $get_started['button']; if($button){ ?>
	                <div class="get_started_btn"><a href="<?php echo $button['url']; ?>" target="<?php echo $button['target']; ?>"><?php echo $button['title']; ?></a></div>
                <?php } ?>
            </div>
        </div>
        <?php $extended_services = get_field('extended_services'); ?>
        <div id="extended_services">  
            <div class="container">
                <h2><?php echo $extended_services['title']; ?></h2>
                <h4><?php echo $extended_services['subtitle']; ?></h4>
                <div class="row">
                    <div class="col-sm-6">
                        <div id="accordian">
                        	<?php $extended_services_points = $extended_services['points']; foreach($extended_services_points as $extended_services_points_data){ ?>
                                <div class="acc_box">
                                    <span class="icon"><?php echo wp_get_attachment_image($extended_services_points_data['icon'], ''); ?></span>
                                    <h3><?php echo $extended_services_points_data['title']; ?></h3>
                                    <div class="acc_content">
                                        <?php echo $extended_services_points_data['text']; ?>
                                    </div>    
                                </div>    
                            <?php } ?>    
                        </div>
                    </div>
                    <?php $images = $extended_services['images']; ?>
                    <div class="col-sm-6">
                        <div class="extended_services_slider">
                            <div class="swiper mySwiper2">
                                <div class="swiper-wrapper">
                                	<?php foreach($images as $images_data){ ?>
                                        <div class="swiper-slide">
                                            <div class="img"><?php echo wp_get_attachment_image($images_data['image'], ''); ?></div>
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
        <?php $any_questions = get_field('any_questions'); ?>
        <div id="any_que_form">  
            <div class="container">
                <h2><?php echo $any_questions['title']; ?></h2>
                <h4><?php echo $any_questions['subtitle']; ?></h4>
                <div class="any_que_form_inn">
                	<?php echo do_shortcode($any_questions['form_shortcode']); ?>   
                </div>    
            </div>    
        </div>    
    </div>  
        
<?php endwhile; // end of the loop. ?>

<?php get_footer(); ?>