<?php

$facilities = vv_get_facilities();

$filter = gArrayItem($_SESSION,'BOOKING_DATA_FRONT');

//echo print_r_pre($filter);

$rooms                  = (gArrayItem($filter,'rooms')) ? gArrayItem($filter,'rooms') :  1;
$adults                 = (gArrayItem($filter,'adults')) ? gArrayItem($filter,'adults') :  2;
$children               = (gArrayItem($filter,'children')) ? gArrayItem($filter,'children') :  0;
$selected_facilities    = is_array(gArrayItem($filter,'facility'))  ? gArrayItem($filter,'facility') :  [];

if(!isset($cities)) $cities = vv_get_cities();

if(!isset($city_page_id))       $city_page_id       = 0;
if(!isset($district_page_id))   $district_page_id   = 0;


if($district_page_id > 0){

    ?>
    <style>
        .districts_list, .district_apartment, district_headers{
            display: none;
        }
        .apartments_list, .district_headers_<?php echo $district_page_id ?>, .district_apartment_<?php echo $district_page_id ?>{
            display:block;
        }
    </style>
    <?php

}
?>

<form id="searchApartments" method="GET" >
    <input type="hidden" name="vv_action" value="search_apartments" >
    <div class="city_filter">
        <div class="city_filter_left position-relative">
            <?php
            if($city_page_id > 0){
                ?>
                <input type="hidden" name="city_id" value="<?php echo $city_id ?>" >
                <?php
            }else{
                $city_id = (gArrayItem($filter,'city_id')) ? gArrayItem($filter,'city_id') :  0;
                ?>
                <select name="city_id">
                    <option value="">All Cities</option>
                    <?php 
                    foreach($cities as $city){
                        echo '<option value="'.$city['ID'].'" ' ;
                        if($city['ID'] == $city_id) echo ' selected ';
                        echo ' >'.$city['post_title'].'</option>';
                    }
                    ?>
                </select>
                <?php
            }
            if($district_page_id > 0){
                ?>
                <input type="hidden" name="district_id" value="<?php echo $district_page_id ?>" >
                <?php
            }
            ?>
            <div class="dropdown_wrap input_wrap room_sel">
                <a href="#" class="room_btn rooms_guests_label"><?php echo $rooms ?> Rooms / <?php echo $adults + $children ?> Guests</a>
                <i class="fas fa-angle-down" ></i>
                <div class="rooom_dropdown">
                  <ul>
                      <li>
                          <span class="lbltxt">Rooms<i>Choose amount of rooms</i></span>
                          <div class="number">
                            <span class="minus"></span>
                            <input type="text" name="rooms" data-min="1" value="<?php echo $rooms ?>"/>
                            <span class="plus"></span>
                        </div>
                      </li>
                      <li>
                          <span class="lbltxt">Adults<i>13 years old or older</i></span>
                          <div class="number">
                            <span class="minus"></span>
                            <input type="text" name="adults" data-min="1" value="<?php echo $adults ?>"/>
                            <span class="plus"></span>
                        </div>
                      </li>
                      <li>
                          <span class="lbltxt">Children<i>2 - 12 years old</i></span>
                          <div class="number">
                            <span class="minus"></span>
                            <input type="text" name="children" value="<?php echo $children ?>"/>
                            <span class="plus"></span>
                        </div>
                      </li>
                  </ul>
                  <div class="text-center"><a href="#" class="btn btn-sm btn-primary btnUpdateRoomsGuests" >Search</a></div>
                </div>
            </div>
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
                    $active = (in_array(gArrayItem($facility,'facility_id'),$selected_facilities,true)) ? 'active' : '';
                    ?>
                    <li>
                        <a href="#" class="btnSetFacility <?php echo $active ?>" data-value="<?php echo gArrayItem($facility,'facility_id') ?>" >
                            <div style="display:none"><input type="checkbox" name="facility[]" value="<?php echo gArrayItem($facility,'facility_id') ?>" <?php echo ($active == 'active') ? 'checked' : '' ?> ></div>
                            <span class="icon"><img src="<?php echo get_bloginfo('template_url') ?>/images/facilities/icon-<?php echo str_replace(" ","-",strtolower(trim($name))) ?>.svg" alt=""></span>
                            <span class="name"><?php echo $name ?></span>
                        </a>
                    </li>
                    <?php
                }
                $ctr++;
            }
            ?>
        </ul>
    </div>
</form>