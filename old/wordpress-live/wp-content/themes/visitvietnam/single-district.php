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

    $district_name      = get_the_title();
    $district_id        = get_the_ID();

    $city_id             = get_post_meta($district_id,'city',true);
    $district_facilities = get_post_meta($district_id,'facilities',true);

    if(!is_array($district_facilities)) $district_facilities = [];

    $main_image         = get_post_meta($district_id,'main_image',true);
    $header_bg_image_id = get_post_meta($district_id,'header_bg_image',true);
    $header_text        = get_post_meta($district_id,'header_text',true);

    $header_bg_image        = vv_get_image_array($header_bg_image_id);
    $header_bg_image_url    = gArrayItem($header_bg_image,'url'); 

    $facilities         = vv_get_facilities();

    //echo print_r_pre($facilities);
    //echo print_r_pre($district_facilities);

    $district_page_id   = $district_id;
    $city_page_id       = get_post_meta($district_id,'city',true);
    $city               = get_post($city_page_id);
    ?>        
    
    <div id="city_banner" style="<?php echo ($header_bg_image_url != '') ? 'background:url('.$header_bg_image_url.')' : '' ?>" >
        <div class="container">
            <h1 class="heading-2"><?php the_title() ?></h1>
            <?php echo $header_text; ?>
        </div>
    </div>  
    <div id="city_wrap">
        <div class="container">
            <div class="breadcrumbs">
                <ul>
                    <li><a href="<?php echo get_home_url(); ?>">Home</a><span class="sep">/</span></li>
                    <li>Cities<span class="sep">/</span></li>
                    <li><a href="<?php echo get_the_permalink($city_page_id); ?>"><?php echo $city->post_title ?></a><span class="sep">/</span></li>
                    <li>Districts<span class="sep">/</span></li>
                    <li><?php echo $district_name; ?></li>
                </ul>
            </div>
            <h2 class="heading-2">Apartments in <?php echo $district_name ?></h2>
            <?php
            include('inc/search-apartments-filter.php');
            echo '<div id="search_result">';
            include('inc/search-apartments-results.php');
            echo '</div>';
            ?>            
        </div>
        <div id="district_facility">
            <div class="container">
                <div class="row">
                    <div class="col-sm-6">
                        <div class="district_facility_left">
                            <h2>Facilities</h2>
                            <ul>
                                <?php
                                    $shown = 0;
                                    foreach($facilities as $facility){
                                        if(in_array($facility['facility_id'],$district_facilities,true)){
                                            $name = gArrayItem($facility,'name');
                                            ?>
                                            <li><span class="icon"><img src="<?php bloginfo('template_url'); ?>/images/facilities/icon-<?php echo str_replace(" ","-",strtolower(trim($name))) ?>-2.svg" ></span><?php echo esc_html($name); ?></li>
                                            <?php 
                                            $shown++;
                                        } 
                                        if($shown == 9) break;
                                    }
                                ?>
                            </ul>  
                            <?php 
                            if(count($district_facilities) > 10){
                                ?>
                                <a href="#facilities_data" data-touch="false" data-fancybox class="see_all_btn">Show all facilities</a>
                                <div id="facilities_data" class="content_box facilities_pop_up" style="display:none;">
                                    <div class="popup_scroll">
                                        <h2>All facilities</h2>
                                        <ul>
                                            <?php
                                            $shown = 0;
                                            foreach($facilities as $facility){
                                                if(in_array($facility['facility_id'],$district_facilities,true)){
                                                    $name = gArrayItem($facility,'name');
                                                    ?>
                                                    <li><span class="icon"><img src="<?php bloginfo('template_url'); ?>/images/facilities/icon-<?php echo str_replace(" ","-",strtolower(trim($name))) ?>-2.svg" ></span><?php echo esc_html($name); ?></li>
                                                    <?php 
                                                    $shown++;
                                                } 
                                                if($shown == 9) break;
                                            }
                                            ?>
                                        </ul>
                                    </div>
                                </div>
                                <?php 
                            }
                            ?>
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


        <?php

        $args = array(
            'post_type' => 'district',
            'posts_per_page' => 6,
            'post_parent' => 0,
            'orderby' => 'rand',
            'post__not_in' => array($district_id),
        );

        $query = new WP_Query($args);

        if ($query->have_posts()) :
            ?>
            <div id="other_city">   
                <div class="container">
                    <h2>Other Districts</h2>
                    <div class="row">
                    <?php
                    while ($query->have_posts()) : $query->the_post();
                        $img            = get_field('main_image',get_the_ID());
                        $sizes          = gArrayItem($img,'sizes');
                        $thumbnail      = gArrayItem($sizes,'medium');
                        if($thumbnail == '') $thumbnail = gArrayItem($img,'url');
                        if($thumbnail == '') $thumbnail = vv_get_image_placeholder();
                        ?>
                        <div class="col-sm-4 mb-4">
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