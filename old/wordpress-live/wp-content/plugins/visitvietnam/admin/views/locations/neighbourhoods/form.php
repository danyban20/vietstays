<?php
$neighbourhood_id                = intval( gArrayItem( $neighbourhood, 'ID' ) );
$district_id                     = intval( gArrayItem( $neighbourhood, 'post_parent' ) );
$name                            = gArrayItem( $neighbourhood, 'post_title' );
$neighbourhood_facilities        = [];
$neighbourhood_security_features = [];
$main_image                      = 0;
$header_bg_image                 = 0;
$header_text                     = '';
$building_gallery                = [];
$cities                          = vv_get_cities( [ 'per_page' => 'all' ] );

if ( $neighbourhood_id > 0 ) {
	$neighbourhood_facilities = get_post_meta( $neighbourhood_id, 'facilities', true );
	if ( ! is_array( $neighbourhood_facilities ) ) {
		$neighbourhood_facilities = [];
	} else {
		$neighbourhood_facilities = array_values( array_filter( array_map( 'intval', $neighbourhood_facilities ) ) );
	}

	$neighbourhood_security_features = get_post_meta( $neighbourhood_id, 'security_features', true );
	if ( ! is_array( $neighbourhood_security_features ) ) {
		$neighbourhood_security_features = [];
	} else {
		$neighbourhood_security_features = array_values( array_filter( array_map( 'intval', $neighbourhood_security_features ) ) );
	}

	$main_image       = intval( get_post_meta( $neighbourhood_id, 'main_image', true ) );
	$header_bg_image  = intval( get_post_meta( $neighbourhood_id, 'header_bg_image', true ) );
	$header_text      = get_post_meta( $neighbourhood_id, 'header_text', true );
	$building_gallery = get_post_meta( $neighbourhood_id, 'building_gallery', true );
	if ( ! is_array( $building_gallery ) ) {
		$building_gallery = [];
	}
}

if ( $name === '' ) {
	$name = POST_Request( 'name' );
}

$facilities        = vv_get_facilities( [ 'type' => 'location' ] );
$security_features = vv_get_security_features( [ 'type' => 'location' ] );
$neighbourhoods_class = new vvNeighbourhoods();
?>
<form method="post" enctype="multipart/form-data">
    <input type="hidden" name="vv_action" value="save_neighbourhood">
    <input type="hidden" name="neighbourhood_id" value="<?php echo intval( $neighbourhood_id ); ?>">
    <?php if ( intval( GET_Request( 'from_request' ) ) > 0 ) { ?>
        <input type="hidden" name="building_request_id" value="<?php echo intval( GET_Request( 'from_request' ) ); ?>">
    <?php } ?>

    <div class="row" style="max-width:1100px">
        <div class="col-lg-7">
            <div class="card mb-3">
                <div class="card-header"><?php vv_e( 'Building information' ); ?></div>
                <div class="card-body">
                    <p class="text-muted small mb-3"><?php vv_e( 'Buildings are created by Vietstays admin. Hosts pick from this list when adding an apartment — they cannot create or edit buildings.' ); ?></p>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><?php vv_e( 'Building name' ); ?></label>
                                <input type="text" name="name" value="<?php echo esc_attr( $name ); ?>" class="form-control form-control-sm" required>
                                <small class="text-muted"><?php vv_e( 'e.g. Zenity Residences' ); ?></small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><?php vv_e( 'District & city' ); ?></label>
                                <select name="district_id" class="form-control form-control-sm" required>
                                    <?php
                                    foreach ( $cities as $city ) {
                                        $districts = vv_get_districts( [
                                            'exclude_locations' => 1,
                                            'city_id'           => $city['ID'],
                                            'per_page'          => 'all',
                                        ] );
                                        foreach ( $districts as $district ) {
                                            ?>
                                            <option value="<?php echo intval( $district['ID'] ); ?>" <?php selected( $district_id, intval( $district['ID'] ) ); ?>>
                                                <?php echo esc_html( $city['post_title'] . ', ' . $district['post_title'] ); ?>
                                            </option>
                                            <?php
                                        }
                                    }
                                    ?>
                                </select>
                                <small class="text-muted"><?php vv_e( 'Auto-filled for hosts when they select this building.' ); ?></small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><?php vv_e( 'Building facilities' ); ?></span>
                    <span class="badge badge-secondary"><?php vv_e( 'Locked for hosts' ); ?></span>
                </div>
                <div class="card-body">
                    <p class="text-muted small"><?php vv_e( 'Checked items appear as locked building amenities in the apartment wizard. Unchecked platform facilities show as grey / not confirmed for this building.' ); ?></p>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-2">
                                <label class="font-weight-bold"><?php vv_e( 'Facilities' ); ?></label>
                                <div><label class="mb-0 small"><input type="checkbox" name="chkAll" value="all"> <?php vv_e( 'All' ); ?></label></div>
                            </div>
                            <div class="vv-building-facility-list">
                                <?php
                                foreach ( $facilities as $facility ) {
                                    $fid     = intval( gArrayItem( $facility, 'facility_id' ) );
                                    $checked = in_array( $fid, $neighbourhood_facilities, true ) ? 'checked' : '';
                                    ?>
                                    <label class="d-block small mb-1">
                                        <input type="checkbox" name="facilities[]" value="<?php echo $fid; ?>" <?php echo $checked; ?>>
                                        <?php echo esc_html( gArrayItem( $facility, 'name' ) ); ?>
                                    </label>
                                    <?php
                                }
                                ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-2">
                                <label class="font-weight-bold"><?php vv_e( 'Security features' ); ?></label>
                                <div><label class="mb-0 small"><input type="checkbox" name="chkAll2" value="all"> <?php vv_e( 'All' ); ?></label></div>
                            </div>
                            <div class="vv-building-facility-list">
                                <?php
                                foreach ( $security_features as $security_feature ) {
                                    $sid     = intval( gArrayItem( $security_feature, 'security_feature_id' ) );
                                    $checked = in_array( $sid, $neighbourhood_security_features, true ) ? 'checked' : '';
                                    ?>
                                    <label class="d-block small mb-1">
                                        <input type="checkbox" name="security_features[]" value="<?php echo $sid; ?>" <?php echo $checked; ?>>
                                        <?php echo esc_html( gArrayItem( $security_feature, 'name' ) ); ?>
                                    </label>
                                    <?php
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header"><?php vv_e( 'Header settings (guest site)' ); ?></div>
                <div class="card-body">
                    <div class="form-group">
                        <label><?php vv_e( 'Background image' ); ?></label>
                        <?php
                        if ( $header_bg_image > 0 ) {
                            $image     = vv_get_image_array( $header_bg_image );
                            $sizes     = gArrayItem( $image, 'sizes' );
                            $thumbnail = gArrayItem( $sizes, 'thumbnail' );
                            if ( $thumbnail === '' ) {
                                $thumbnail = gArrayItem( $image, 'url' );
                            }
                            ?>
                            <div class="mb-2"><img src="<?php echo esc_url( $thumbnail ); ?>" style="max-width:200px" class="border p-2" alt=""></div>
                            <input type="file" name="header_bg_image" class="form-control form-control-sm d-inline w-auto"> <?php vv_e( 'Replace image' ); ?>
                            <?php
                        } else {
                            ?>
                            <input type="file" name="header_bg_image" class="form-control form-control-sm d-inline w-auto"> <?php vv_e( 'Upload image' ); ?>
                            <?php
                        }
                        ?>
                    </div>
                    <div class="form-group mb-0">
                        <label><?php vv_e( 'Header text' ); ?></label>
                        <textarea name="header_text" class="form-control form-control-sm wysiwyg_editor"><?php echo esc_textarea( $header_text ); ?></textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card mb-3">
                <div class="card-header"><?php vv_e( 'Building photo gallery' ); ?></div>
                <div class="card-body">
                    <p class="text-muted small"><?php vv_e( 'Photos taken by Vietstays. Hosts can pick up to 3 for their listing during the apartment wizard.' ); ?></p>

                    <div class="form-group">
                        <label><?php vv_e( 'Cover / facade image' ); ?></label>
                        <?php
                        if ( $main_image > 0 ) {
                            $image     = vv_get_image_array( $main_image );
                            $sizes     = gArrayItem( $image, 'sizes' );
                            $thumbnail = gArrayItem( $sizes, 'thumbnail' );
                            if ( $thumbnail === '' ) {
                                $thumbnail = gArrayItem( $image, 'url' );
                            }
                            ?>
                            <div class="mb-2"><img src="<?php echo esc_url( $thumbnail ); ?>" style="max-width:200px" class="border p-2" alt=""></div>
                            <input type="file" name="main_image" class="form-control form-control-sm"> <?php vv_e( 'Replace image' ); ?>
                            <?php
                        } else {
                            ?>
                            <input type="file" name="main_image" class="form-control form-control-sm"> <?php vv_e( 'Upload image' ); ?>
                            <?php
                        }
                        ?>
                        <small class="text-muted d-block mt-1"><?php vv_e( 'Also included in the host gallery picker as “Facade”.' ); ?></small>
                    </div>

                    <div id="vvBuildingGalleryList" class="vv-building-gallery-list">
                        <?php
                        foreach ( $building_gallery as $item ) {
                            $attachment_id = intval( is_array( $item ) ? gArrayItem( $item, 'id' ) : $item );
                            if ( $attachment_id <= 0 ) {
                                continue;
                            }
                            $url = wp_get_attachment_image_url( $attachment_id, 'thumbnail' );
                            if ( ! $url ) {
                                continue;
                            }
                            $label      = gArrayItem( $item, 'label', 'Building' );
                            $host_count = $neighbourhood_id > 0 ? $neighbourhoods_class->get_gallery_image_host_count( $neighbourhood_id, $attachment_id ) : 0;
                            ?>
                            <div class="vv-building-gallery-item">
                                <img src="<?php echo esc_url( $url ); ?>" alt="">
                                <div class="vv-building-gallery-item__fields">
                                    <input type="hidden" name="building_gallery_id[]" value="<?php echo $attachment_id; ?>">
                                    <input type="text" name="building_gallery_label[]" value="<?php echo esc_attr( $label ); ?>" class="form-control form-control-sm" placeholder="<?php echo esc_attr( vv__( 'Label e.g. Pool' ) ); ?>">
                                    <?php if ( $host_count > 0 ) { ?>
                                        <small class="text-muted"><?php printf( esc_html( vv__( 'Used by %d hosts' ) ), intval( $host_count ) ); ?></small>
                                    <?php } ?>
                                </div>
                                <button type="button" class="btn btn-link btn-sm text-danger vv-building-gallery-remove" title="<?php echo esc_attr( vv__( 'Remove' ) ); ?>">&times;</button>
                            </div>
                            <?php
                        }
                        ?>
                    </div>

                    <div class="mt-3">
                        <label class="font-weight-bold"><?php vv_e( 'Add gallery photos' ); ?></label>
                        <input type="file" name="building_gallery_upload[]" class="form-control form-control-sm" accept="image/jpeg,image/png" multiple>
                        <small class="text-muted"><?php vv_e( 'JPG or PNG. Labels can be edited after save.' ); ?></small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-2 mb-4">
        <button class="btn btn-primary"><?php vv_e( 'Save building' ); ?></button>
    </div>
</form>

<?php ob_start(); ?>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
<style>
.vv-building-facility-list {
    max-height: 220px;
    overflow-y: auto;
    border: 1px solid #eee;
    border-radius: 4px;
    padding: 8px 10px;
    background: #fafafa;
}
.vv-building-gallery-list { display: flex; flex-direction: column; gap: 10px; }
.vv-building-gallery-item {
    display: flex;
    gap: 10px;
    align-items: flex-start;
    padding: 8px;
    border: 1px solid #e8e8e8;
    border-radius: 6px;
    background: #fafafa;
}
.vv-building-gallery-item img {
    width: 72px;
    height: 54px;
    object-fit: cover;
    border-radius: 4px;
    flex-shrink: 0;
}
.vv-building-gallery-item__fields { flex: 1; min-width: 0; }
.vv-building-gallery-remove { line-height: 1; padding: 0 4px; font-size: 20px; }
.select2-container { display: block; }
.select2-container--default .select2-selection--single { border-color: #FD780E; }
.select2-container .select2-selection--single { height: 31px; }
</style>
<?php $header_codes .= ob_get_clean(); ?>

<?php ob_start(); ?>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
jQuery(function($){
    $('select[name="district_id"]').select2();

    $('input[name="chkAll"]').on('click', function(){
        $('input[name="facilities[]"]').prop('checked', $(this).is(':checked'));
    });
    $('input[name="chkAll2"]').on('click', function(){
        $('input[name="security_features[]"]').prop('checked', $(this).is(':checked'));
    });

    $('#vvBuildingGalleryList').on('click', '.vv-building-gallery-remove', function(){
        if (confirm('<?php echo esc_js( vv__( 'Remove this gallery photo?' ) ); ?>')) {
            $(this).closest('.vv-building-gallery-item').remove();
        }
    });
});
</script>
<?php $footer_codes .= ob_get_clean(); ?>
