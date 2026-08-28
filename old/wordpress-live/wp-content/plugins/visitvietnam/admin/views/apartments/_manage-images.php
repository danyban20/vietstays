<?php
/** Photos tab — manage page. Expects $apartment, $apartment_id, $min_images, $building_id. */
$gallery_selected = json_decode( gArrayItem( $apartment, 'building_gallery_json' ), true );
if ( ! is_array( $gallery_selected ) ) {
	$gallery_selected = [];
}
?>
<form method="post" id="vvManage2026Images">
    <input type="hidden" name="vv_action" value="vv_save_apartment_wizard_images">
    <input type="hidden" name="apartment_id" value="<?php echo intval( $apartment_id ); ?>">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0"><?php vv_e( 'Apartment photos' ); ?></h5>
        <?php if ( $building_id > 0 ) { ?>
            <button type="button" class="btn btn-sm btn-outline-secondary" id="apt2026OpenBuildingGallery"><?php vv_e( 'Building photos (max 3)' ); ?></button>
        <?php } ?>
    </div>

    <input type="file" id="apt2026FileInput" accept="image/jpeg,image/png" multiple class="d-none" aria-hidden="true" tabindex="-1">

    <div class="vv-apt2026-dropzone" id="apt2026Dropzone">
        <p class="mb-1"><strong><?php vv_e( 'Drag and drop images here' ); ?></strong></p>
        <p class="mb-2 text-muted small"><?php vv_e( 'or choose files from your computer' ); ?></p>
        <button type="button" class="btn btn-sm btn-secondary" id="apt2026PickFiles"><?php vv_e( 'Choose images' ); ?></button>
        <p class="mt-3 mb-0 small text-muted"><?php printf( esc_html( vv__( 'Minimum %d images to publish · JPG or PNG' ) ), intval( $min_images ) ); ?></p>
    </div>

    <div id="manage2026ImagesWrap" class="mt-3">
        <?php
        $apt2026_class = isset( $this->apartments_wizard_class ) ? $this->apartments_wizard_class : new vvApartmentsWizard();
        $apt2026_class->render_images_html( $apartment_id );
        ?>
    </div>

    <div id="apt2026BuildingGalleryInputs">
        <?php foreach ( $gallery_selected as $gid ) { ?>
            <input type="hidden" name="building_gallery_ids[]" value="<?php echo intval( $gid ); ?>">
        <?php } ?>
    </div>

    <button type="submit" class="btn btn-primary btn-sm mt-3"><?php vv_e( 'Save photos' ); ?></button>
</form>

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
                <p class="small text-muted mt-2 mb-0" id="apt2026BuildingPickCount"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal"><?php vv_e( 'Close' ); ?></button>
            </div>
        </div>
    </div>
</div>
