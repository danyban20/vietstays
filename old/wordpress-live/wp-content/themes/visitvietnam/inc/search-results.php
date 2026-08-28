<?php
$booking_data = gArrayItem($_SESSION,'BOOKING_DATA_FRONT');
if(!is_array($booking_data)) $booking_data = [];

$filter = [];
if(isset($city_id) && $city_id > 0){
    $filter['city_id'] = $city_id;

    $city = get_post($city_id);
    $city_name = stripslashes($city->post_title);
}
$districts = vv_get_districts($filter);

$filter = ['return_total' => 1];
//if(gArrayItem($booking_data,'check_in_date')) $filter['check_in_date'] = $booking_data['check_in_date'];
//if(gArrayItem($booking_data,'check_out_date')) $filter['check_out_date'] = $booking_data['check_out_date'];

$apartment_class    = new vvApartments();
$r_apartments       = $apartment_class->get_apartments($filter);
$apartments         = gArrayItem($r_apartments,'apartments');
$total_apartments   = gArrayItem($r_apartments,'total_rows');


$facilities = vv_get_facilities();
//echo print_r_pre($apartments);

?>

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
    <div class="facilities">
        <ul>
            <?php 
            $ctr = 0;
            foreach($facilities as $facility){
                $name = gArrayItem($facility,'name');
                if($ctr < 8){
                    ?>
                    <li>
                        <span class="icon"><img src="<?php echo get_bloginfo('template_url') ?>/images/facilities/icon-<?php echo str_replace(" ","-",strtolower(trim($name))) ?>.svg" alt=""></span>
                        <span class="name"><?php echo $name ?></span>
                    </li>
                    <?php
                }
                $ctr++;
            }
            ?>
        </ul>
    </div>

            <div class="city_app_wrap ">
                <div class="districts_list">
                    <div class="mt-4 mb-4">
                        <div class="row">
                            <div class="col-md-10">
                                <?php 
                                if($city_id > 0){
                                    echo '<h3>Now displaying '.count($districts).' districts from '.$city_name.'</h3>';
                                }else{
                                    echo '<h3>All Districts</h3>';
                                }
                                ?>
                            </div>
                            <div class="col-md-2 text-right city_display_title">
                                <div class="city_tab">
                                    <a href="#" class="district_tab_btn active" data-show_map="0">
                                        <span class="icon"><img src="<?php bloginfo('template_url'); ?>/images/list2.svg"><img src="<?php bloginfo('template_url'); ?>/images/list_h.svg" class="h_icon"></span>
                                        List
                                    </a>
                                    <a href="#" class="district_tab_btn " data-show_map="1">
                                    <span class="icon"><img src="<?php bloginfo('template_url'); ?>/images/map.svg"><img src="<?php bloginfo('template_url'); ?>/images/map_h.svg" class="h_icon"></span>
                                    Map
                                    </a>
                                </div>  
                            </div>
                        </div>
                    </div>
                    <div class="list">
                        <?php
                        foreach($districts as $district){

                            $district_name  = gArrayItem($district,'post_title');
                            $district_id    = gArrayItem($district,'ID');
                            $img            = get_field('main_image',$district_id);
                            $sizes          = gArrayItem($img,'sizes');
                            $thumbnail      = gArrayItem($sizes,'medium');
                            if($thumbnail == '') $thumbnail = gArrayItem($img,'url');
                            if($thumbnail == '') $thumbnail =  vv_get_image_placeholder();

                            $d_ids = [];
                            $d_ids[] = $district_id;

                            $locations = gArrayItem($district,'locations');
                            if(is_array($locations)){
                                foreach($locations as $location){
                                    $d_ids[] = $location['ID'];
                                }
                            }
                            //echo print_r_pre($d_ids);
                            $num_apartments = 0;
                            if(is_array($apartments)){
                                foreach($apartments as $apartment){
                                    if(in_array(gArrayItem($apartment,'district'),$d_ids,true)) $num_apartments++;
                                }
                            }

                            ?>
                            
                            <div class="district_block_1">
                                <div class="district_block_1_inner">
                                    <a href="<?php echo the_permalink() ?>">
                                        <div class="img"><img src="<?php echo $thumbnail ?>" /></div>
                                        <div class="cap_1">
                                            <p><?php echo $city_name ?></p>
                                            <h5><?php echo $district_name ?></h5>
                                        </div>
                                    </a>
                                    <div class="cap_2">
                                        <span class="date_text"><?php echo $num_apartments; ?> <?php echo ($num_apartments > 1) ? 'apartments' : 'apartment' ?> available</span>
                                        <div class="btn_wrap">
                                            <a href="#" class="btn view_btn btnShowApartments" data-district_id="<?php echo $district_id ?>">Show apartments</a>
                                        </div>
                                    </div>  
                                </div>  
                            </div>  
                            
                            <?php

                            ob_start();
                            ?>
                            <div class="district_headers district_headers_<?php echo $district_id; ?>" style="display: none;">
                                <div class="mt-4 mb-4">
                                    <div>
                                        <a href="#" class="back_btn" style="font-size:14px"><i class="fa fa-arrow-left"></i> Back to Districts</a>                                                
                                    </div>
                                    <div class="row">
                                        <div class="col-md-9">
                                            <?php 
                                            if($num_apartments > 0){
                                                if($city_id > 0){
                                                    echo '<h3>Now displaying '.$num_apartments.' apartments from '.$city_name.'</h3>';
                                                }else{
                                                    echo '<h3>Now displaying '.$num_apartments.' apartments</h3>';
                                                }
                                            }else{
                                                if($city_id > 0){
                                                    echo '<h3>No Apartments found from '.$city_name.'</h3>';
                                                }else{
                                                    echo '<h3>No Apartments found</h3>';
                                                }
                                            }
                                            ?>
                                        </div>
                                        <div class="col-md-3 text-right city_display_title">
                                            <div class="city_tab">
                                                <a href="#" class="apartments_tab_btn active" data-show_map="0">
                                                    <span class="icon"><img src="<?php bloginfo('template_url'); ?>/images/list2.svg"><img src="<?php bloginfo('template_url'); ?>/images/list_h.svg" class="h_icon"></span>
                                                    List
                                                </a>
                                                <a href="#" class="apartments_tab_btn " data-show_map="1">
                                                <span class="icon"><img src="<?php bloginfo('template_url'); ?>/images/map.svg"><img src="<?php bloginfo('template_url'); ?>/images/map_h.svg" class="h_icon"></span>
                                                Map
                                                </a>
                                            </div>  
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php
                            $district_headers .= ob_get_clean();

                        }
                        ?>
                    </div>
                    <div class="map">
                        <div id="map_districts" style="height: 500px; width: 100%;"></div>
                    </div>
                    <?php /*?><div class="show_more_btn"><a href="#">Show more</a></div><?php */?>
                    <div style="clea:both"></div>
                </div>
                <?php echo $district_headers ?>
                <div class="district_headers district_headers_all" style="display: none;">
                    <div class="mt-4 mb-4">
                        <?php 
                        if($total_apartments > 0){
                            ?>
                            <h3>Now displaying <?php echo $total_apartments; ?> <?php echo ($total_apartments > 1) ? 'apartments' : 'apartment' ?> from <?php echo $city_name ?></h3>
                            <?php 
                        }else{
                            ?>
                            <h3>No apartment found  from <?php echo $city_name ?></h3>
                            <?php 
                        }
                        ?>
                    </div>
                </div>
                <div class="apartments_list">
                    <div class="list">

                        <?php
                        foreach($apartments as $apartment){

                            $apartment_thumbnail = vv_get_apartment_thumbnail($apartment);

                            $pricing    = json_decode(gArrayItem($apartment,'pricing'),true);
                            if(!is_array($pricing)) $pricing = array();

                            $price = gArrayItem($apartment,'price_daily');

                            $discount = '';

                            $district_id = 0;
                            foreach($districts as $district){
                                if($district['ID'] == $apartment['district']){
                                    $district_id = $district['ID'];
                                }else{
                                    $locations = gArrayItem($district,'locations');
                                    if(!is_array($locations)) $locations = [];
                                    foreach($locations as $location){
                                        if($location['ID'] == $apartment['district']) $district_id = $district['ID'];
                                    }
                                }
                            }
                            ?>
                            <div class="city_app_block_1 district_apartment district_apartment_<?php echo $district_id ?>" style="display: none;">
                                <a href="#" class="fav"></a>
                                <a href="<?php echo vv_get_apartment_url($apartment) ?>" class="city_app_block_1_inner">
                                    <div class="img"><img src="<?php echo $apartment_thumbnail ?>" ></div>
                                    
                                    <div class="cap">
                                        <div class="origprice"></div>
                                        <div class="price"><?php echo vv_number_format($price,true) ?> </div>
                                        <div class="name"><?php echo stripslashes(gArrayItem($apartment,'name')) ?></div>
                                        <div class="address"><?php echo gArrayItem($apartment,'address') ?></div>
                                    </div>
                                </a>    
                            </div>  
                            <?php
                        }
                        ?>                        
                    </div>
                    <div class="map">
                        <div id="map_apartments" style="height: 500px; width: 100%;"></div>
                    </div>
                </div>

            </div>



<?php
ob_start();
global $footer_codes;
?>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCLPz_TVumpVb-tTog14fW0mPKPCF_L5g0"></script>
<script>
    function initMap() {
      var location_d = { lat: 10.3157, lng: 123.8854 }; // Example: Cebu
      var map_d = new google.maps.Map(document.getElementById('map_districts'), {
        zoom: 12,
        center: location
      });
      var marker_d = new google.maps.Marker({
        position: location_d,
        map: map_d
      });

      var location_a = { lat: 10.3157, lng: 123.8854 }; // Example: Cebu
      var map_a = new google.maps.Map(document.getElementById('map_apartments'), {
        zoom: 12,
        center: location
      });
      var marker_a = new google.maps.Marker({
        position: location_a,
        map: map_a
      });

    }

    $(document).ready(function (){
        let $ = jQuery.noConflict();
        
        initMap();

        $('.district_tab_btn').click(function (e){
            $('.district_tab_btn').removeClass('active');
            $(this).addClass('active');

            if($(this).attr('data-show_map') == 1){
                $('.districts_list').addClass('map_shown');
            }else{
                $('.districts_list').removeClass('map_shown');
            }

            e.preventDefault();

        });
        $('.apartments_tab_btn').click(function (e){
            $('.apartments_tab_btn').removeClass('active');
            $(this).addClass('active');

            if($(this).attr('data-show_map') == 1){
                $('.apartments_list').addClass('map_shown');
            }else{
                $('.apartments_list').removeClass('map_shown');
            }

            e.preventDefault();

        });

        $('.btnShowApartments').click(function (e){
            district_id = $(this).attr('data-district_id');
            $('.districts_list').hide();
            $('.apartments_list').show();
            $('.district_headers').hide();
            $('.district_headers_'+district_id).show();
            $('.district_apartment').hide();
            $('.district_apartment_'+district_id).show();
            e.preventDefault();
        });

        $('.back_btn').click(function (e){
            $('.districts_list').show();
            $('.apartments_list').hide();
            $('.district_headers').hide();
            $('.district_apartment').hide();
            e.preventDefault();
        });
    });
  </script>
<?php
$footer_codes .= ob_get_clean();
