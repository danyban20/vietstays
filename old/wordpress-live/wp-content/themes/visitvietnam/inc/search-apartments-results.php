<?php
$booking_data = gArrayItem($_SESSION,'BOOKING_DATA_FRONT');
if(!is_array($booking_data)) $booking_data = [];


if(!isset($city_page_id))       $city_page_id       = 0;
if(!isset($district_page_id))   $district_page_id   = 0;

if($city_page_id == 0)      $city_page_id       = gArrayItem($booking_data,'city_id');
if($district_page_id == 0)  $district_page_id   = gArrayItem($booking_data,'district_id');

if($city_page_id == 0)      $city_page_id       = GET_Request('city_id');
if($district_page_id == 0)  $district_page_id   = GET_Request('district_id');


if($city_page_id > 0){
    $city = get_post($city_page_id);
    $city_name = stripslashes($city->post_title);
}

if($district_page_id > 0){
    $districts_class = new vvDistricts;
    $districts[] = $districts_class->get_district($district_page_id);


}else{

    $filter = [];
    if($city_page_id > 0){
        $filter['city_id'] = $city_page_id;
    }
    $districts = vv_get_districts($filter);
}


//echo print_r_pre($apartments);



    //echo print_r_pre($_REQUEST);
    //echo print_r_pre($booking_data);

if($district_page_id == 0){
}

$districts_html     = '';
$titles_html        = '';
$apartments_html    = '';
$total_apartments   = 0;
$map_pins           = [];

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
    $filter = [];
    $filter['return_total'] = 1;
    $filter['district']     = $district['ID'];
    $filter['rooms']        = gArrayItem($booking_data,'rooms');
    $filter['adults']       = gArrayItem($booking_data,'adults');
    $filter['children']     = gArrayItem($booking_data,'children');
    $filter['facility']     = gArrayItem($booking_data,'facility');
    //if(gArrayItem($booking_data,'check_in_date')) $filter['check_in_date'] = $booking_data['check_in_date'];
    //if(gArrayItem($booking_data,'check_out_date')) $filter['check_out_date'] = $booking_data['check_out_date'];

    $apartment_class    = new vvApartments();
    $r_apartments       = $apartment_class->get_apartments($filter);
    $apartments         = gArrayItem($r_apartments,'apartments');
    $num_apartments     = gArrayItem($r_apartments,'total_rows');
    $total_apartments   = $total_apartments + $num_apartments;



    if(GET_Request('vv_action2') == 'generate_map_pins'){
        foreach($apartments as $apartment){
            $latitude   = trim(gArrayItem($apartment,'address_latitude'));
            $longitude  = trim(gArrayItem($apartment,'address_longitude'));
            if($latitude != '' && $longitude != ''){
                array_push($map_pins,[  'lat' => $latitude, 
                                        'lng' => $longitude, 
                                        'name' => stripslashes($apartment['name']), 
                                        'url' => vv_get_apartment_url($apartment),
                                        'address' => gArrayItem($apartment,'address')
                                    ]);
            }
        }
    }else{
        ob_start();

        $d_city_name = $city_name;
        $d_city_id = get_post_meta($district_id,'city',true);
        if($d_city_id > 0){
            $d_city = get_post($d_city_id);
            if($d_city){
                $d_city_name = $d_city->post_title;
            }
        }
        ?>
        <div class="district_block_1 district_block-<?php echo $district_id ?>">
            <div class="district_block_1_inner">
                <div class="img"><img src="<?php echo $thumbnail ?>" /></div>
                <div class="cap_1">
                    <p><a href="<?php the_permalink($d_city_id) ?>" target="_blank" ><?php echo $d_city_name ?></a></p>
                    <h5><a href="<?php the_permalink($district_id) ?>" target="_blank" ><?php echo $district_name ?></a></h5>
                </div>
                <div class="cap_2">
                    <span class="date_text"><?php echo $num_apartments; ?> <?php echo ($num_apartments > 1) ? 'apartments' : 'apartment' ?> available</span>
                    <div class="btn_wrap">
                        <a href="#" class="btn view_btn btnShowApartments" data-district_id="<?php echo $district_id ?>">Show apartments</a>
                    </div>
                </div>  
            </div>  
        </div>  
        
        <?php
        $districts_html .= ob_get_clean();

        ob_start();
        ?>
        <div class="district_headers district_headers_<?php echo $district_id; ?>" style="display: none;">
            <?php 
            if(intval($district_page_id) == 0){ 
                ?>
                <div>
                    <a href="#" class="back_btn" style="font-size:14px"><i class="fa fa-arrow-left"></i> Back to Districts</a>                                                
                </div>
                <?php 
            } 
            $from_txt = '';
            if($city_name != '') $from_txt .= ' from '.$city_name.', '.$district_name;
            if($num_apartments > 1)         echo '<h3>Now displaying '.$num_apartments.' apartments'.$from_txt.'</h3>';
            elseif($num_apartments == 1)    echo '<h3>Now displaying '.$num_apartments.' apartment'.$from_txt.'</h3>';
            else                            echo '<h3>Now apartment found'.$from_txt.'</h3>';
            ?>
        </div>
        <?php
        $titles_html .= ob_get_clean();

        ob_start();
        foreach($apartments as $apartment){
            $apartment_thumbnail = vv_get_apartment_thumbnail($apartment);

            $pricing    = json_decode(gArrayItem($apartment,'pricing'),true);
            if(!is_array($pricing)) $pricing = array();

            $price      = gArrayItem($apartment,'price_daily');
            $discount   = '';

            /*
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
            */
            ?>
            <div class="city_app_block_1 district_apartment district_apartment_<?php echo $district_id ?>">
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

        $apartments_html .= ob_get_clean();
    }

}


if(GET_Request('vv_action2') == 'generate_map_pins'){
    header("Content-Type: application/json");
    echo json_encode(['locations' => $map_pins]);
    die();
}else{

    ?>
    <div class="city_app_wrap ">
        <div class="title_wrap">
            <div class="row">
                <div class="col-md-9">
                    <div class="mt-4 mb-4">
                        <div class="district_headers district_headers_all_districts" >
                            <?php 
                            if($city_page_id > 0)    echo '<h3>Now displaying '.count($districts).' districts from '.$city_name.'</h3>';
                            else                echo '<h3>All Districts</h3>';
                            ?>
                        </div>
                        <div class="district_headers district_headers_all_apartments" style="display: none;">
                            <?php 
                            if($total_apartments > 0){
                                if($city_page_id > 0)    echo '<h3>Now displaying '.$total_apartments.' apartments from '.$city_name.'</h3>';
                                else                echo '<h3>Now displaying '.$total_apartments.' apartments</h3>';
                            }else{
                                if($city_page_id > 0)    echo '<h3>No Apartments found from '.$city_name.'</h3>';
                                else                echo '<h3>No Apartments found</h3>';
                            }
                            ?>
                        </div>
                        <?php echo $titles_html ?>
                    </div>
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
        <div class="list_wrap">
            <?php 
            echo '<div class="districts_list">'.$districts_html.'</div>';
            echo '<div class="apartments_list">'.$apartments_html.'</div>';
            ?>
            <div style="clea:both"></div>
        </div>
        <div class="map_wrap" >
            <div id="apartments_map" style="height: 500px; width: 100%;"></div>
        </div>
    </div>
    <?php
}


