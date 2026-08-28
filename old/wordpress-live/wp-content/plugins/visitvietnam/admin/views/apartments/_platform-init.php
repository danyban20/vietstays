<?php
$platform_settings = vvApartmentPlatform::get_settings();
$house_rules_all   = vvApartmentPlatform::get_house_rules();
$house_rules_sel   = json_decode( gArrayItem( $apartment, 'house_rules_json' ), true );
if ( ! is_array( $house_rules_sel ) ) {
	$house_rules_sel = [];
}
$buildings            = vv_get_neighbourhoods();
$building_id          = intval( gArrayItem( $apartment, 'building_id' ) );
$building_facilities  = vvApartmentPlatform::get_building_facilities( $building_id );
$blocked_dates        = $apartment_id > 0 ? vvApartmentPlatform::get_blocked_dates( $apartment_id ) : [];
$assigned_staff_ids   = vvApartmentPlatform::get_assigned_staff_ids( $apartment );
$offers               = $apartment_id > 0 ? vvApartmentPlatform::get_offers( $apartment_id ) : [];
$seasonal             = json_decode( gArrayItem( $apartment, 'seasonal_pricing' ), true );
if ( ! is_array( $seasonal ) ) {
	$seasonal = [];
}
$host_id = intval( gArrayItem( $apartment, 'user_id' ) );
if ( $host_id <= 0 ) {
	global $logged_user_id;
	$host_id = intval( $logged_user_id );
}
$staff_filter = [ 'host' => $host_id, 'vv_user_level' => 'staff' ];
$available_staff = [];
if ( isset( $this->staffs_class ) ) {
	$available_staff = $this->staffs_class->get_staffs( $staff_filter );
}
$suggested_price = vvApartmentPlatform::suggest_daily_price(
	gArrayItem( $apartment, 'apartment_type', vvApartmentPlatform::rooms_to_type( gArrayItem( $apartment, 'rooms' ) ) ),
	gArrayItem( $apartment, 'price_level', 'normal' ),
	$building_id
);
$apartment_types = vvApartmentPlatform::apartment_types();
$price_levels    = vvApartmentPlatform::price_levels();
$status_labels   = vvApartments::status_labels();
$image_count     = vvApartmentPlatform::count_apartment_images( $apartment );
$min_images      = intval( $platform_settings['min_images_publish'] );
