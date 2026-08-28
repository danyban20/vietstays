<?php
/*
    * Template Name: Apartments
*/

$apartment_id = intval(GET_Request('id'));


global $visitVietnam;

$filter = gArrayItem($_SESSION,'BOOKING_DATA_FRONT');


$city_id = intval(gArrayItem($filter,'city_id'));
if($city_id > 0){
    $city = get_post($city_id);
    $title = 'All apartments from '.stripcslashes($city->post_title);
}else{
    $title = 'All Apartments';
}

$apartments = $visitVietnam->apartment_class->get_apartments($filter);

//echo print_r_pre($apartments);

$images = vv_get_apartment_images($apartment);

//echo print_r_pre($apartment);
//echo print_r_pre($images);

$main_img_url = '';
if(count($images) > 0){
    $main_img = gArrayItem($images,0);

    $main_img_url = wp_get_attachment_url(gArrayItem($main_img,'image_id'));

}


$facilities = vv_get_facilities();


ob_start();
global $head_codes;
?>
<style type="text/css">
    #city_wrap .city_tab a:hover{
        text-decoration: none;
    }
    .breadcrumbs{
        margin:20px 0;
    }
    .breadcrumbs li:after{
        content:"/";
        margin-left:10px;
        margin-right:10px;
        display: inline-block;
    }
    .breadcrumbs li:last-child:after{
        display: none;
    }
    .apartments{
        background-color: #F0E8D5;
    }
    .apartments .title{
        margin:50px 0;
    }

    .apartments .head{
        margin-bottom: 20px;
    }



    .apartments-list ul{
        padding: 0;
        margin: 0;
    }

    .apartments-list ul li{
        display: inline-block;
        height:300px;
        overflow-y: hidden;
        width:33%;
    }

    .apartments-list ul li a > div{
        height:100%;
        border-radius: 20px;
        background-repeat: no-repeat;
        background-size: cover;
        background-position: center;
        background-color: #ccc;
    }
    .apartments-list ul li a > div > div{
        height:100%;
        border-radius: 20px;
        background-repeat: no-repeat;
        background-size: cover;
        background-position: center;
        /**/
        display: flex;
        align-items: flex-end; /* Aligns content to the bottom vertically */
        padding:20px;
    }
    .apartments-list ul li a > div > div > div{
        width: 100%;       /* Optional: if you want full width */
        flex-shrink: 0;     /* Prevent shrinking */
        color:#fff;
    }

    .apartments-list ul li a > div > div > div .origprice{
        font-size:26px;
        font-weight: 700;
        text-decoration: line-through;
    }
    .apartments-list ul li a > div > div > div .price{
        font-size:38px;
        font-weight: 700;
    }
    .apartments-list ul li a > div > div > div .name{
        font-size:18px;
    }

    .apartments-list ul li a > div > div:hover{
        background-color: #ffffff8c;
    }


    .custom-dropdown {
        position: relative;
        width: auto;
        display: inline-block;
        border:2px solid #004041;
        border-radius: 20px;
        padding:0px 20px;
        height:33px;
    }
    .custom-dropdown:hover{
        background-color: #fff;
    }
    .custom-dropdown-toggle {
        border-radius: 0.25rem;
        padding: 0px 30px 0px 0px;
        width: 100%;
        text-align: left;
        cursor: pointer;
        position:relative;
        line-height:29px;
    }
    .custom-dropdown-toggle:focus{
        border: 1px solid #ced4da;
    }
    .custom-dropdown-toggle i{
        position:absolute;
        right:5px;
        top:5px;
        font-size:20px;
    }
    .custom-dropdown-menu {
        position: absolute;
        width: 250px;
        max-height: 200px;
        overflow-y: auto;
        border: 1px solid #ced4da;
        border-radius: 0.25rem;
        background-color: #fff;
        z-index: 1000;
        display: none;
    }
    .custom-dropdown-menu.show {
        display: block;
    }
    .custom-dropdown-item {
        padding: 10px;
        border-bottom: 1px solid #e9ecef;
        cursor: pointer;
    }
    .custom-dropdown-item:last-child {
        border-bottom: none;
    }
    .custom-dropdown-item:hover {
        background-color: #f8f9fa;
    }


    #section2{
        color:#013735;
        padding-top:100px;
        padding-bottom: 80px;
    }
    #section2 .sec2_title{
        font-family: 'TrajanProRegular';
        font-size:35px;
        color:#013735;
        font-weight: 400;
    }

    #section2 .sec2_subtitle{
        font-size:18px;
        color:#013735;
    }

    #section2 .sec2_list{
        padding:20px;
    }

    #section2 .sec2_list h4{
        font-family: 'TrajanProRegular';
        font-size: 24px;
        font-weight: 400;
    }

    #section2 .sec2_list ul{
        margin-bottom: 0;
    }
    #section2 .sec2_list ul li{
        margin-bottom:10px;
        font-size:18px;
        font-weight: 400;
    }

    #section2 .sec2_list ul li::marker{
        color:#FC780E;
    }

    #section2 hr{
        background-color: #BEA473;
        border: 0;
        height: 1px;
    }

    #section2 .contact_form{
        background-color: #F0E8D5;
        border:1px solid #BEA473;
        padding:30px;
        border-radius: 30px;
    }

    #section2 .contact_form h3{
        font-family: 'TrajanProRegular';
        font-size: 24px;
        font-weight: 400;
        text-align: center;
        padding-top:20px;
        padding-left:30px;
        padding-right:30px;
    }

    #section2 .contact_form input, #section2 .contact_form textarea, #section2 .contact_form select{
        border-radius: 30px;
        background-color: #fff;
        border: 0px none;
    }
    #section2 .contact_form textarea{
        padding-top:20px;
    }
    #section2 .contact_form input::placeholder, #section2 .contact_form textarea::placeholder, #section2 .contact_form select::placeholder{
        font-size:14px;
    }
    #section2 .contact_form button{
        border-radius: 30px;
        background-color: #FC780E;
        text-align: center;
        color:#fff;
        width:100%;
    }

    #section3{
        color:#fff;
        background-color: #004041;
        padding-top:100px;
        padding-bottom: 80px;
    }

    #section3 h2{
        font-family: 'TrajanProRegular';
        font-size: 45px;
        font-weight: 400;
        text-align: center;
        color:#F0E8D5;
        margin-left:auto;
        margin-right:auto;
        display:block;
        max-width:750px;
    }
    #section3 .images{
        max-width: 865px;
        margin: 20px auto;
        position:relative;
    }
    #section3 .images img{
        border-radius: 30px;
        border:1px solid #BEA473;
    }
    #section3 .images .left_image{
        width:305px;
        position:absolute;
        left:-200px;
        top:calc(50% - 100px);
    }
    #section3 .images .right_image{
        width:305px;
        position:absolute;
        right:-200px;
        top:-10%;
    }

    #other_city h2{
        font-family: 'TrajanProRegular';
        font-size: 45px;
        font-weight: 400;
    }

</style>
<?php
include('inc/search-apartments-styles.php');
$head_codes .= ob_get_clean();

get_header(); 

?>

    <?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>        
    	<div id="topbar">
        	<div class="container">
            	<div class="topbar">
                	<div class="topbar_left">
                    	<a href="/"><img src="<?php bloginfo('template_url'); ?>/images/logo.png" alt=""></a>
                    </div>
                    <div class="topbar_right">
                        <ul>
                            <li><a href="#menucontent_1">Pictures</a></li>
                            <li><a href="#menucontent_2">Facilities</a></li>
                            <li><a href="#menucontent_3">Practical information</a></li>
                            <li><a href="#menucontent_4">Host</a></li>
                            <li><a href="#menucontent_4">Price 3544-</a></li>
                        </ul>
                        <a href="#" class="btn">Book now</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="apartments" id="city_wrap">
            <div class="container">
                <div class="head">
                    <div class="breadcrumbs">
                        <ul>
                            <li><a href="#">Home</a></li>
                            <li><a href="#">Apartment</a></li>
                        </ul>
                    </div>
                    <h1 class="title"><?php echo $title ?></h1>
                </div>
                <?php 
                include('inc/search-apartments-filter.php');
                echo '<div id="search_result">';
                include('inc/search-apartments-results.php');
                echo '</div>';
                ?>
            </div>
        </div>

        <?php 
        $section_2 = get_field('section_2'); 
        $left_side_title        = gArrayItem($section_2,'left_side_title');
        $left_side_subtitle     = gArrayItem($section_2,'left_side_subtitle');
        $left_side_list_1_title = gArrayItem($section_2,'left_side_list_1_title');
        $left_side_list_1       = explode("\n",gArrayItem($section_2,'left_side_list_1'));
        $left_side_list_2_title = gArrayItem($section_2,'left_side_list_2_title');
        $left_side_list_2       = explode("\n",gArrayItem($section_2,'left_side_list_2'));
        $right_side_form_title  = gArrayItem($section_2,'right_side_form_title');
        $right_side_form_embed  = gArrayItem($section_2,'right_side_form_embed');
        //echo print_r_pre($section_2);
        ?>
        <div id="section2">
            <div class="container">
                <div class="row">
                    <div class="col-md-7 sec2_left">
                        <h3 class="sec2_title"><?php echo $left_side_title ?></h3>
                        <div class="sec2_subtitle"><?php echo $left_side_subtitle ?></div>
                        <div class="sec2_list">
                            <?php 
                            if($left_side_list_1_title != ''){
                                echo '<h4>'.$left_side_list_1_title.'</h4>';
                            }
                            echo '<ul>';
                            foreach($left_side_list_1 as $l){
                                if(trim($l) != '') echo '<li>'.$l.'</li>';
                            }
                            echo '</ul>';
                            ?>
                        </div>
                        <hr>
                        <div class="sec2_list">
                            <?php 
                            if($left_side_list_1_title != ''){
                                echo '<h4>'.$left_side_list_1_title.'</h4>';
                            }
                            echo '<ul>';
                            foreach($left_side_list_1 as $l){
                                if(trim($l) != '') echo '<li>'.$l.'</li>';
                            }
                            echo '</ul>';
                            ?>
                        </div>
                    </div>
                    <div class="col-md-5 sec2_right">
                        <div class="contact_form">
                            <?php echo ($right_side_form_title != '') ? '<h3>'.$right_side_form_title.'</h3>' : ''; ?>
                            <?php 
                            if($right_side_form_embed != ''){
                                echo do_shortcode($right_side_form_embed);
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php 
        $section_3      = get_field('section_3'); 
        $title          = gArrayItem($section_3,'title');
        $subtitle       = gArrayItem($section_3,'subtitle');
        $button_text    = gArrayItem($section_3,'button_text');
        $button_link    = gArrayItem($section_3,'button_link');
        $left_image     = gArrayItem($section_3,'left_image');
        $right_image    = gArrayItem($section_3,'right_image');
        $center_image   = gArrayItem($section_3,'center_image');
        ?>

        <div id="section3">
            <div class="container">
                <?php echo ($title != '') ? '<h2>'.$title.'</h2>' : '' ?>
                <?php echo (trim(strip_tags($subtitle)) != '') ? '<div class="subtitle text-center">'.$subtitle.'</div>' : '' ?>
                <?php 
                if($button_link != '' && $button_text != ''){
                    ?>
                    <div class="text-center mb-4"><a href="<?php echo $button_link ?>" class="btn btn-primary btn-sm" ><?php echo $button_text ?></a></div>
                    <?php
                }
                ?>
                <div class="images">                
                    <?php echo ($left_image != '') ? '<img src="'.$left_image.'" class="left_image" >' : '' ?>
                    <?php echo ($center_image != '') ? '<img src="'.$center_image.'" class="center_image" >' : '' ?>
                    <?php echo ($right_image != '') ? '<img src="'.$right_image.'" class="right_image" >' : '' ?>
                </div>
            </div>
        </div>


        <?php

        $args = array(
            'post_type' => 'city',
            'posts_per_page' => 6,
            'post_parent' => 0,
            'orderby' => 'rand',
        );

        if($city_id > 0){
            $args['post__not_in'] = array($city_id);
        }

        $query = new WP_Query($args);

        if ($query->have_posts()) :
            ?>
            <div id="other_city" class="pb-4">   
                <div class="container pb-4">
                    <h2>Other Cities</h2>
                    <div class="row">
                    <?php
                    while ($query->have_posts()) : $query->the_post();
                        $img            = get_field('main_image',get_the_ID());
                        $sizes          = gArrayItem($img,'sizes');
                        $thumbnail      = gArrayItem($sizes,'medium');
                        if($thumbnail == '') $thumbnail = gArrayItem($img,'url');
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



    <?php endwhile; // end of the loop. ?>

    <?php
    global $footer_codes;
    ob_start();
    ?>

    <script type="text/javascript" src="https://cdn.jsdelivr.net/jquery/latest/jquery.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

    <?php 

    include('inc/search-apartments-scripts.php');

    $footer_codes .= ob_get_clean();


get_footer(); 

?>
