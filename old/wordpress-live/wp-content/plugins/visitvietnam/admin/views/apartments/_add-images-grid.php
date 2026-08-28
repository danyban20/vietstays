<?php
/** @var array $images */
$images = isset( $images ) && is_array( $images ) ? $images : [];
$min_images = 10;
if ( class_exists( 'vvApartmentPlatform' ) ) {
	$min_images = intval( vvApartmentPlatform::get_settings()['min_images_publish'] );
}

$building_photos = [];
if ( class_exists( 'vvApartmentsWizard' ) && ! empty( $apartment ) ) {
	$building_photos = vvApartmentsWizard::get_selected_building_gallery_items( $apartment );
}

$apt_count   = count( $images );
$total_count = function_exists( 'vv_count_listing_photos' ) ? vv_count_listing_photos( $apartment ) : ( $apt_count + count( $building_photos ) );
?>
<?php if ( empty( $images ) && empty( $building_photos ) ) { ?>
    <p class="text-muted small mb-0 vv-apt2026-no-photos"><?php vv_e( 'No photos yet — upload images or select building photos.' ); ?></p>
<?php } ?>

<?php if ( ! empty( $images ) ) { ?>
    <div class="vv-apt2026-images-grid vv-apt2026-images-grid--5col" id="vvApt2026ImagesGrid">
        <?php foreach ( $images as $i => $img ) {
            $is_cover = ( $i === 0 );
            ?>
            <div class="vv-apt2026-image-card<?php echo $is_cover ? ' is-cover is-selected' : ''; ?>" data-image-id="<?php echo esc_attr( gArrayItem( $img, 'image_id' ) ); ?>">
                <button type="button" class="vv-apt2026-image-card__menu" aria-label="<?php echo esc_attr( vv__( 'Options' ) ); ?>">&#8942;</button>
                <a href="<?php echo esc_url( vv_admin_url() . '?action=delete_apartment_image&id=' . intval( gArrayItem( $apartment, 'ID' ) ) . '&img=' . gArrayItem( $img, 'image_id' ) ); ?>" class="vv-apt2026-image-card__remove vv-apt2026-delete-img" title="<?php echo esc_attr( vv__( 'Remove' ) ); ?>">&times;</a>
                <?php if ( $is_cover ) { ?><span class="vv-apt2026-image-card__cover">★ <?php vv_e( 'Cover photo' ); ?></span><?php } ?>
                <div class="vv-apt2026-image-card__thumb">
                    <img src="<?php echo esc_url( gArrayItem( $img, 'thumb' ) ); ?>" alt="">
                </div>
                <div class="vv-apt2026-image-card__body">
                    <input type="hidden" name="images_url[]" value="<?php echo esc_attr( gArrayItem( $img, 'thumb' ) ); ?>">
                    <input type="hidden" name="images_id[]" value="<?php echo esc_attr( gArrayItem( $img, 'image_id' ) ); ?>">
                    <input type="text" name="images_caption[]" class="vv-apt2026-field vv-apt2026-field--caption" value="<?php echo esc_attr( gArrayItem( $img, 'caption' ) ); ?>" placeholder="<?php echo esc_attr( vv__( 'Click to name…' ) ); ?>">
                    <div class="vv-apt2026-image-card__actions">
                        <button type="button" class="vv-apt2026-img-move-left" title="<?php echo esc_attr( vv__( 'Move left' ) ); ?>">&#8592;</button>
                        <button type="button" class="vv-apt2026-img-move-right" title="<?php echo esc_attr( vv__( 'Move right' ) ); ?>">&#8594;</button>
                        <?php if ( ! $is_cover ) { ?>
                            <button type="button" class="vv-apt2026-img-set-cover" title="<?php echo esc_attr( vv__( 'Set as cover' ) ); ?>">★</button>
                        <?php } ?>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>
<?php } ?>

<div id="apt2026BuildingPhotosSection" class="<?php echo empty( $building_photos ) ? 'd-none' : 'mt-3'; ?>">
    <p class="vv-apt2026-section-title"><?php vv_e( 'Building photos' ); ?></p>
    <div class="vv-apt2026-images-grid vv-apt2026-images-grid--5col" id="apt2026BuildingPhotosGrid">
        <?php foreach ( $building_photos as $item ) { ?>
            <div class="vv-apt2026-image-card vv-apt2026-building-photo-card" data-building-photo-id="<?php echo intval( $item['id'] ); ?>">
                <span class="vv-apt2026-image-card__cover vv-apt2026-image-card__cover--building"><?php vv_e( 'Building' ); ?></span>
                <div class="vv-apt2026-image-card__thumb">
                    <img src="<?php echo esc_url( $item['url'] ); ?>" alt="">
                </div>
                <div class="vv-apt2026-image-card__body">
                    <p class="small mb-0 text-center"><?php echo esc_html( gArrayItem( $item, 'label', 'Building' ) ); ?></p>
                </div>
            </div>
        <?php } ?>
    </div>
</div>
