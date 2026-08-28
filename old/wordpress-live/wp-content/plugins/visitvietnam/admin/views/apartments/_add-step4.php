<?php
$images       = vv_get_apartment_images( $apartment );
$image_count  = count( $images );
$cover        = $image_count > 0 ? gArrayItem( $images[0], 'thumb' ) : '';
$thumbs       = array_slice( $images, 1, 3 );
$building_id  = intval( gArrayItem( $apartment, 'building_id' ) );
$building     = $building_id > 0 ? vv_get_neighbourhood( $building_id ) : [];
$district_name = vv_get_district_display_name( intval( gArrayItem( $apartment, 'district' ) ) );
$city_name     = '';
if ( $district_name !== '' && function_exists( 'get_post_meta' ) ) {
	$city_id = intval( get_post_meta( intval( gArrayItem( $apartment, 'district' ) ), 'city', true ) );
	if ( $city_id > 0 ) {
		$city_post = get_post( $city_id );
		if ( $city_post ) {
			$city_name = $city_post->post_title;
		}
	}
}
$location_parts = array_filter( [
	gArrayItem( $building, 'post_title' ),
	$district_name,
	$city_name,
] );
$location_label = implode( ', ', $location_parts );

$facilities = json_decode( gArrayItem( $apartment, 'facilities' ), true );
$facility_items = [];
if ( is_array( $facilities ) ) {
	foreach ( vv_get_facilities() as $f ) {
		if ( in_array( gArrayItem( $f, 'facility_id' ), $facilities, false ) ) {
			$facility_items[] = $f;
		}
	}
}
$facility_items = array_slice( $facility_items, 0, 6 );

if ( ! function_exists( 'vv_wizard_facility_icon' ) ) {
	function vv_wizard_facility_icon( $name ) {
		$name = strtolower( $name );
		$map  = [
			'wifi' => 'fa-wifi', 'tv' => 'fa-television', 'ac' => 'fa-snowflake-o', 'air' => 'fa-snowflake-o',
			'kitchen' => 'fa-cutlery', 'pool' => 'fa-tint', 'parking' => 'fa-car', 'elevator' => 'fa-arrows-v',
			'balcony' => 'fa-building-o', 'coffee' => 'fa-coffee', 'workspace' => 'fa-laptop',
		];
		foreach ( $map as $key => $icon ) {
			if ( strpos( $name, $key ) !== false ) {
				return $icon;
			}
		}
		return 'fa-check-circle-o';
	}
}
?>
<form method="post" id="vvApt2026Step4">
    <input type="hidden" name="vv_action" value="vv_save_apartment_wizard_finalize">
    <input type="hidden" name="apartment_id" value="<?php echo intval( $apartment_id ); ?>">

    <p class="vv-apt2026-preview-intro"><?php vv_e( 'Preview your listing. Price and practical details are configured on the next page after creation.' ); ?></p>

    <div class="vv-apt2026-preview-card">
        <div class="vv-apt2026-preview-card__layout">
            <div class="vv-apt2026-preview-card__media">
                <div class="vv-apt2026-preview-card__hero<?php echo $cover === '' ? ' is-empty' : ''; ?>"<?php echo $cover !== '' ? ' style="background-image:url(' . esc_url( $cover ) . ')"' : ''; ?>>
                    <?php if ( $cover === '' ) { ?><i class="fa fa-image" aria-hidden="true"></i><?php } ?>
                    <?php if ( $cover !== '' ) { ?><span class="vv-apt2026-preview-card__cover-badge">★ <?php vv_e( 'Cover photo' ); ?></span><?php } ?>
                </div>
                <?php if ( ! empty( $thumbs ) ) { ?>
                    <div class="vv-apt2026-preview-card__thumbs">
                        <?php foreach ( $thumbs as $thumb ) { ?>
                            <div class="vv-apt2026-preview-card__thumb" style="background-image:url('<?php echo esc_url( gArrayItem( $thumb, 'thumb' ) ); ?>')"></div>
                        <?php } ?>
                    </div>
                <?php } ?>
            </div>
            <div class="vv-apt2026-preview-card__body">
                <h3 class="vv-apt2026-preview-card__title"><?php echo esc_html( gArrayItem( $apartment, 'name' ) ); ?></h3>
                <?php if ( $location_label !== '' ) { ?>
                    <p class="vv-apt2026-preview-card__location"><?php echo esc_html( $location_label ); ?></p>
                <?php } ?>
                <div class="vv-apt2026-preview-card__price">
                    <strong><?php echo esc_html( vv_site_currency() ); ?> — / <?php vv_e( 'night' ); ?></strong>
                    <small><?php vv_e( 'Configured after creation' ); ?></small>
                </div>
                <?php if ( ! empty( $facility_items ) ) { ?>
                    <div class="vv-apt2026-preview-tags">
                        <?php foreach ( $facility_items as $f ) {
                            $icon = vv_wizard_facility_icon( gArrayItem( $f, 'name' ) );
                            ?>
                            <span><i class="fa <?php echo esc_attr( $icon ); ?>" aria-hidden="true"></i> <?php echo esc_html( gArrayItem( $f, 'name' ) ); ?></span>
                        <?php } ?>
                    </div>
                <?php } ?>
                <?php if ( $image_count > 4 ) { ?>
                    <p class="vv-apt2026-preview-card__more-photos">+ <?php echo intval( $image_count - 4 ); ?> <?php vv_e( 'more images' ); ?></p>
                <?php } elseif ( $image_count > 1 && $image_count <= 4 ) { ?>
                    <p class="vv-apt2026-preview-card__more-photos">+ <?php echo intval( $image_count - 1 ); ?> <?php vv_e( 'more images' ); ?></p>
                <?php } ?>
                <div class="vv-apt2026-preview-card__rules">
                    <?php vv_e( 'House rules and practical information filled in after creation.' ); ?>
                </div>
            </div>
        </div>
        <?php if ( trim( gArrayItem( $apartment, 'about_this_short' ) ) !== '' ) { ?>
            <div class="vv-apt2026-preview-card__about">
                <h6><?php vv_e( 'About the apartment' ); ?></h6>
                <p><?php echo esc_html( gArrayItem( $apartment, 'about_this_short' ) ); ?></p>
            </div>
        <?php } ?>
    </div>
</form>
