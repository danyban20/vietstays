<?php
$images          = vv_get_apartment_images( $apartment );
$min_images      = 10;
if ( class_exists( 'vvApartmentPlatform' ) ) {
	$min_images = intval( vvApartmentPlatform::get_settings()['min_images_publish'] );
}
$image_count     = count( $images );
$listing_count   = function_exists( 'vv_count_listing_photos' ) ? vv_count_listing_photos( $apartment ) : $image_count;
$building_id     = intval( gArrayItem( $apartment, 'building_id' ) );
$gallery_selected = json_decode( gArrayItem( $apartment, 'building_gallery_json' ), true );
if ( ! is_array( $gallery_selected ) ) {
	$gallery_selected = [];
}
$building_gallery_count = count( $gallery_selected );
$max_building_photos    = 3;
?>
<form method="post" id="vvApt2026Step2" enctype="multipart/form-data">
    <input type="hidden" name="vv_action" value="vv_save_apartment_wizard_step">
    <input type="hidden" name="wizard_step" value="2">
    <input type="hidden" name="apartment_id" value="<?php echo intval( $apartment_id ); ?>">

    <div class="vv-apt2026-photo-toolbar">
        <ul class="nav vv-apt2026-photo-tabs" role="tablist">
            <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#aptPhotoUpload" role="tab"><?php vv_e( 'Add Images' ); ?></a></li>
            <?php if ( $building_id > 0 ) { ?>
                <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#aptPhotoBuilding" role="tab" id="apt2026BuildingTab"><?php vv_e( 'Select from building images' ); ?></a></li>
            <?php } ?>
        </ul>
        <span class="vv-apt2026-photo-counter" id="apt2026PhotoCountBadge"><?php echo intval( $listing_count ); ?> / <?php echo intval( $min_images ); ?></span>
    </div>

    <div class="tab-content">
        <div class="tab-pane fade show active" id="aptPhotoUpload" role="tabpanel">
            <input type="file" id="apt2026FileInput" accept="image/jpeg,image/png" multiple class="d-none" aria-hidden="true" tabindex="-1">

            <div class="vv-apt2026-dropzone" id="apt2026Dropzone">
                <p class="vv-apt2026-dropzone__icon mb-2"><i class="fa fa-camera fa-2x"></i></p>
                <p class="mb-1"><strong><?php vv_e( 'Drag and drop images here' ); ?></strong></p>
                <p class="mb-2 text-muted small"><?php vv_e( 'or' ); ?></p>
                <button type="button" class="vv-apt2026-btn-pick" id="apt2026PickFiles"><?php vv_e( 'Select images from computer' ); ?></button>
                <p class="mt-3 mb-0 small text-muted"><?php printf( esc_html( vv__( 'Minimum %d images · JPG or PNG · Max 10 MB per image' ) ), intval( $min_images ) ); ?></p>
                <p class="mb-0 small text-muted vv-apt2026-dropzone-tip"><i class="fa fa-lightbulb-o"></i> <?php vv_e( 'The first image you upload becomes the cover image automatically — you can change this later.' ); ?></p>
            </div>

            <div id="apt2026ImagesWrap" class="mt-3">
                <?php
                $apt_wizard_class = isset( $this->apartments_wizard_class ) ? $this->apartments_wizard_class : new vvApartmentsWizard();
                $apt_wizard_class->render_images_html( $apartment_id );
                ?>
            </div>
        </div>

        <?php if ( $building_id > 0 ) { ?>
        <div class="tab-pane fade" id="aptPhotoBuilding" role="tabpanel">
            <p class="text-muted small"><?php vv_e( 'Select up to 3 images from the building gallery. Selected images shown first. Remove a selected image to select another.' ); ?></p>
            <div class="d-flex justify-content-between align-items-center mb-2">
                <strong id="apt2026BuildingGalleryTitle"><?php vv_e( 'Building gallery' ); ?></strong>
                <span class="badge badge-warning" id="apt2026BuildingPickBadge"><?php echo intval( $building_gallery_count ); ?> / <?php echo intval( $max_building_photos ); ?> <?php vv_e( 'selected' ); ?></span>
            </div>
            <div class="vv-apt2026-building-grid" id="apt2026BuildingGridInline"></div>
            <p class="small text-muted mt-2 mb-0" id="apt2026BuildingPickCount"></p>
        </div>
        <?php } ?>
    </div>

    <div id="apt2026BuildingGalleryInputs">
        <?php foreach ( $gallery_selected as $gid ) { ?>
            <input type="hidden" name="building_gallery_ids[]" value="<?php echo intval( $gid ); ?>">
        <?php } ?>
    </div>

    <?php if ( $listing_count >= $min_images ) { ?>
        <div class="vv-apt2026-upload-success mt-3">
            ✓ <?php printf( esc_html( vv__( '%d images uploaded and named — ready to proceed!' ) ), intval( $listing_count ) ); ?>
        </div>
    <?php } else { ?>
        <p class="alert alert-warning mt-3 mb-0 small" id="apt2026ImageWarning"><?php printf( esc_html( vv__( '%1$d of %2$d listing photos — apartment uploads and building photos both count. Full amount required before publishing.' ) ), intval( $listing_count ), intval( $min_images ) ); ?></p>
    <?php } ?>
</form>

<!-- Building gallery modal (legacy fallback) -->
<div class="modal fade vv-apt2026-building-modal" id="apt2026BuildingModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php vv_e( 'Building photos' ); ?></h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small"><?php vv_e( 'Photos taken by Vietstays showing the building. Select up to 3 for your listing.' ); ?></p>
                <div class="vv-apt2026-building-grid" id="apt2026BuildingGrid"></div>
                <p class="small text-muted mt-2 mb-0" id="apt2026BuildingPickCountModal"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal"><?php vv_e( 'Close and return to photos' ); ?></button>
            </div>
        </div>
    </div>
</div>
