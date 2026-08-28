<?php
/** Facilities & house rules tab — manage page. Expects $apartment, $apartment_id. */
$building_id = intval( gArrayItem( $apartment, 'building_id' ) );
$facilities_all = vv_get_facilities();
$building_level_facilities = vv_get_facilities( [ 'type' => 'location' ] );
$selected = json_decode( gArrayItem( $apartment, 'facilities' ), true );
if ( ! is_array( $selected ) ) {
	$selected = [];
}
$building_facility_ids = [];
if ( $building_id > 0 ) {
	$raw = get_post_meta( $building_id, 'facilities', true );
	if ( is_array( $raw ) ) {
		$building_facility_ids = array_map( 'intval', $raw );
	}
}
$building = $building_id > 0 ? vv_get_neighbourhood( $building_id ) : [];
$building_level_ids = [];
foreach ( $building_level_facilities as $facility ) {
	$building_level_ids[] = intval( gArrayItem( $facility, 'facility_id' ) );
}

$house_rules_all = class_exists( 'vvApartmentPlatform' ) ? vvApartmentPlatform::get_house_rules() : [];
$house_rules_sel = json_decode( gArrayItem( $apartment, 'house_rules_json' ), true );
if ( ! is_array( $house_rules_sel ) ) {
	$house_rules_sel = [];
}
?>
<form method="post" id="vvManage2026Facilities">
    <input type="hidden" name="vv_action" value="vv_save_apartment_wizard_facilities">
    <input type="hidden" name="apartment_id" value="<?php echo intval( $apartment_id ); ?>">

    <ul class="nav nav-tabs mb-3 vv-apt2026-facility-tabs" role="tablist">
        <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#manage2026FacApt" role="tab"><?php vv_e( 'Apartment facilities' ); ?></a></li>
        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#manage2026FacBld" role="tab"><?php vv_e( 'Building facilities' ); ?></a></li>
        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#manage2026HouseRules" role="tab"><?php vv_e( 'House rules' ); ?></a></li>
        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#manage2026Practical" role="tab"><?php vv_e( 'Practical info' ); ?></a></li>
    </ul>

    <div class="tab-content">
        <div class="tab-pane fade show active" id="manage2026FacApt" role="tabpanel">
            <p class="text-muted small"><?php vv_e( 'Check facilities available in your apartment.' ); ?></p>
            <div class="vv-apt2026-facility-grid">
                <?php foreach ( $facilities_all as $facility ) {
                    $fid = intval( gArrayItem( $facility, 'facility_id' ) );
                    if ( in_array( $fid, $building_level_ids, true ) ) {
                        continue;
                    }
                    $checked = in_array( $fid, $selected, false ) ? 'checked' : '';
                    ?>
                    <label>
                        <input type="checkbox" name="facilities[]" value="<?php echo $fid; ?>" <?php echo $checked; ?>>
                        <?php echo esc_html( gArrayItem( $facility, 'name' ) ); ?>
                    </label>
                <?php } ?>
            </div>
        </div>
        <div class="tab-pane fade" id="manage2026FacBld" role="tabpanel">
            <?php if ( $building_id <= 0 ) { ?>
                <p class="text-muted"><?php vv_e( 'No building linked.' ); ?></p>
            <?php } else { ?>
                <p class="text-muted small"><?php printf( esc_html( vv__( 'Building facilities for %s.' ) ), esc_html( gArrayItem( $building, 'post_title' ) ) ); ?></p>
                <div class="vv-apt2026-facility-grid">
                    <?php foreach ( $building_level_facilities as $facility ) {
                        $fid       = intval( gArrayItem( $facility, 'facility_id' ) );
                        $is_locked = in_array( $fid, $building_facility_ids, true );
                        ?>
                        <label class="<?php echo $is_locked ? 'is-building is-locked' : 'is-unconfirmed'; ?>">
                            <?php if ( $is_locked ) { ?>
                                <input type="checkbox" checked disabled>
                                <span class="vv-apt2026-facility-lock">&#128274;</span>
                            <?php } else { ?>
                                <input type="checkbox" disabled>
                            <?php } ?>
                            <?php echo esc_html( gArrayItem( $facility, 'name' ) ); ?>
                        </label>
                    <?php } ?>
                </div>
            <?php } ?>
        </div>
        <div class="tab-pane fade" id="manage2026HouseRules" role="tabpanel">
            <?php if ( empty( $house_rules_all ) ) { ?>
                <p class="text-muted"><?php vv_e( 'No house rules configured.' ); ?></p>
            <?php } else { ?>
                <p class="text-muted small"><?php vv_e( 'Select the house rules that apply to this apartment.' ); ?></p>
                <?php foreach ( $house_rules_all as $rule ) {
                    $rid     = gArrayItem( $rule, 'id' );
                    $checked = in_array( $rid, $house_rules_sel, true ) ? 'checked' : '';
                    ?>
                    <div class="mb-1">
                        <label><input type="checkbox" name="house_rules_selected[]" value="<?php echo esc_attr( $rid ); ?>" <?php echo $checked; ?>> <?php echo esc_html( gArrayItem( $rule, 'label' ) ); ?></label>
                    </div>
                <?php } ?>
            <?php } ?>
        </div>
        <div class="tab-pane fade" id="manage2026Practical" role="tabpanel">
            <p class="text-muted small"><?php vv_e( 'Help guests know what to expect when they arrive.' ); ?></p>
            <div class="row">
                <div class="col-md-6 form-group">
                    <label><?php vv_e( 'Check-in from' ); ?></label>
                    <input type="text" name="check_in_time1" class="form-control form-control-sm" value="<?php echo esc_attr( gArrayItem( $apartment, 'check_in_time1' ) ); ?>" placeholder="<?php echo esc_attr( vv__( 'e.g. 14:00' ) ); ?>">
                </div>
                <div class="col-md-6 form-group">
                    <label><?php vv_e( 'Check-in until' ); ?></label>
                    <input type="text" name="check_in_time2" class="form-control form-control-sm" value="<?php echo esc_attr( gArrayItem( $apartment, 'check_in_time2' ) ); ?>" placeholder="<?php echo esc_attr( vv__( 'e.g. 22:00' ) ); ?>">
                </div>
            </div>
            <div class="form-group">
                <label><?php vv_e( 'Features & amenities description' ); ?></label>
                <textarea name="features_description" class="form-control form-control-sm" rows="4" placeholder="<?php echo esc_attr( vv__( 'Highlight standout features, workspace, views, appliances…' ) ); ?>"><?php echo esc_textarea( gArrayItem( $apartment, 'features_description' ) ); ?></textarea>
            </div>
            <div class="form-group mb-0">
                <label><?php vv_e( 'Property safety & access' ); ?></label>
                <textarea name="property_safety" class="form-control form-control-sm" rows="4" placeholder="<?php echo esc_attr( vv__( 'Entry instructions, security, emergency contacts…' ) ); ?>"><?php echo esc_textarea( gArrayItem( $apartment, 'property_safety' ) ); ?></textarea>
            </div>
        </div>
    </div>

    <button type="submit" class="btn btn-primary btn-sm mt-3"><?php vv_e( 'Save facilities & rules' ); ?></button>
</form>
