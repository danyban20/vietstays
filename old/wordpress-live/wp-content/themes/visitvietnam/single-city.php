<?php
/**
*/

$booking_data = gArrayItem($_SESSION,'BOOKING_DATA_FRONT');
if(!is_array($booking_data)) $booking_data = [];

ob_start();
global $head_codes;
?>
<style type="text/css">
    #city_wrap .city_tab a:hover{
        text-decoration: none;
    }
</style>
<?php
include('inc/search-apartments-styles.php');
$head_codes .= ob_get_clean();

get_header(); ?>  

<?php if ( have_posts() ) while ( have_posts() ) : the_post(); 

    $city_name      = get_the_title();
    $city_id        = get_the_ID();
    $city_page_id   = $city_id;
    ?>        
    
    <?php $header_section = get_field('header_section',$current_city); ?>
    <div id="city_banner" style="background:url(<?php echo $header_section['bg_image']; ?>)">
        <div class="container">
            <h1 class="heading-2"><?php the_title() ?></h1>
            <?php echo $header_section['text']; ?>
        </div>
    </div>  
    <div id="city_wrap">
        <div class="container">
            <div class="breadcrumbs">
                <ul>
                    <li><a href="<?php echo get_home_url(); ?>">Home</a><span class="sep">/</span></li>
                    <li>Cities<span class="sep">/</span></li>
                    <li><?php echo $city_name; ?></li>
                </ul>
            </div>
            <h2 class="heading-2">Districts in <?php echo $city_name ?></h2>
            <?php
            include('inc/search-apartments-filter.php');
            echo '<div id="search_result">';
            include('inc/search-apartments-results.php');
            echo '</div>';
            ?>            
        </div>
        <?php $features_section = get_field('features_section',$city_id); ?>
        <div id="unique_city">
            <div class="container">
                <div class="unique_city_top">
                    <h2><?php echo $features_section['title']; ?></h2>
                    <?php echo $features_section['text']; ?>
                </div>
                <div class="unique_city_slider">
                    <div class="swiper mySwiper2">
                        <div class="swiper-wrapper">
                            <?php 
                            $features = gArrayItem($features_section,'features'); 
                            if(!is_array($features)) $features = [];
                            foreach($features as $features_data){ ?>
                                <div class="swiper-slide">
                                    <div class="unique_city_slider_block">
                                        <div style="max-height: 520px;overflow: hidden;"><?php echo wp_get_attachment_image($features_data['image'], ''); ?></div>
                                        <div class="cap">
                                            <h2><?php echo $features_data['title']; ?></h2>
                                            <?php echo $features_data['text']; ?>
                                        </div>  
                                    </div>  
                                </div>
                            <?php } ?>
                        </div>
                        <div class="swiper-pagination"></div>
                        <div class="swiper-button-next"></div>
                        <div class="swiper-button-prev"></div>
                    </div>      
                </div>
                <?php $content_section = get_field('content_section',$city_id); ?>
                <div class="unique_img_desc">
                    <div class="row v_flex">
                        <div class="col-sm-6">
                            <div class="img">
                                <?php echo wp_get_attachment_image($content_section['image'], ''); ?>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="desc">
                                <h2><?php echo $content_section['title']; ?></h2>
                                <?php echo $content_section['text']; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php

        $args = array(
            'post_type' => 'city',
            'posts_per_page' => 6,
            'post_parent' => 0,
            'orderby' => 'rand',
            'post__not_in' => array($city_id),
        );

        $query = new WP_Query($args);

        if ($query->have_posts()) :
            ?>
            <div id="other_city">   
                <div class="container">
                    <h2>Other Cities</h2>
                    <div class="row">
                    <?php
                    while ($query->have_posts()) : $query->the_post();
                        $img            = get_field('main_image',get_the_ID());
                        $sizes          = gArrayItem($img,'sizes');
                        $thumbnail      = gArrayItem($sizes,'medium');
                        if($thumbnail == '') $thumbnail = gArrayItem($img,'url');
                        ?>
                        <div class="col-sm-4">
                            <a href="<?php echo get_the_permalink(); ?>" class="other_city_block" style="background-image:url(<?php echo $thumbnail; ?>);">
                                <div class="cap">
                                    <h3><?php the_title() ?></h3> 
                                </div>
                                <span class="btn">Show available apartments</span>  
                            </a>
                        </div>
                        <?php
                    endwhile;
                    ?>
                    </div>
                </div>  
            </div>
            <?php
        endif;
        wp_reset_postdata();        
        ?>
    </div>
<?php endwhile; ?>

<?php

global $footer_codes;
ob_start();

include('inc/search-apartments-scripts.php');

$footer_codes .= ob_get_clean();

get_footer(); 

?>