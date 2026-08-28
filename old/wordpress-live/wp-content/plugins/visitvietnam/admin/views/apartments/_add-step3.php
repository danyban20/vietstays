<?php
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

/**
 * Host-selectable facilities: everything except building-confirmed location amenities
 * (those are auto-included and shown read-only on the building tab).
 */
$apt_facilities = [];
foreach ( $facilities_all as $facility ) {
	$fid = intval( gArrayItem( $facility, 'facility_id' ) );
	$is_location = gArrayItem( $facility, 'type' ) === 'location';
	$is_confirmed_on_building = in_array( $fid, $building_facility_ids, true );
	if ( $is_location && $is_confirmed_on_building ) {
		continue;
	}
	$apt_facilities[] = $facility;
}

$selected_apt_count = 0;
foreach ( $apt_facilities as $facility ) {
	if ( in_array( intval( gArrayItem( $facility, 'facility_id' ) ), $selected, false ) ) {
		$selected_apt_count++;
	}
}
$total_apt_facilities = count( $apt_facilities );

$building_confirmed_count = 0;
foreach ( $building_level_facilities as $facility ) {
	if ( in_array( intval( gArrayItem( $facility, 'facility_id' ) ), $building_facility_ids, true ) ) {
		$building_confirmed_count++;
	}
}
$total_building_facilities = count( $building_level_facilities );

if ( ! function_exists( 'vv_wizard_facility_icon' ) ) {
	function vv_wizard_facility_icon( $name ) {
		$name = strtolower( $name );
		$map  = [
			'wifi' => 'fa-wifi', 'tv' => 'fa-television', 'ac' => 'fa-snowflake-o', 'air' => 'fa-snowflake-o',
			'kitchen' => 'fa-cutlery', 'kjøkken' => 'fa-cutlery', 'pool' => 'fa-tint', 'basseng' => 'fa-tint',
			'parking' => 'fa-car', 'elevator' => 'fa-arrows-v', 'heis' => 'fa-arrows-v', 'pet' => 'fa-paw',
			'washer' => 'fa-recycle', 'vask' => 'fa-recycle', 'balcony' => 'fa-building-o', 'balkong' => 'fa-building-o',
			'safe' => 'fa-lock', 'heat' => 'fa-fire', 'varme' => 'fa-fire', 'coffee' => 'fa-coffee',
			'workspace' => 'fa-laptop', 'arbeid' => 'fa-laptop', 'bath' => 'fa-bath', 'smoke' => 'fa-smoking',
			'view' => 'fa-eye', 'mountain' => 'fa-tree', 'sea' => 'fa-tint',
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
<form method="post" id="vvApt2026Step3" class="vv-apt-wizard-form">
    <input type="hidden" name="vv_action" value="vv_save_apartment_wizard_step">
    <input type="hidden" name="wizard_step" value="3">
    <input type="hidden" name="apartment_id" value="<?php echo intval( $apartment_id ); ?>">

    <div class="vv-apt2026-facility-toolbar">
        <ul class="nav vv-apt2026-facility-tabs" role="tablist">
            <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#apt2026FacApt" role="tab"><?php vv_e( 'Apartment facilities' ); ?></a></li>
            <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#apt2026FacBld" role="tab"><?php vv_e( 'Building amenities' ); ?></a></li>
        </ul>
        <span class="vv-apt2026-facility-counter" id="apt2026FacilityCounterApt"><?php echo intval( $selected_apt_count ); ?> / <?php echo intval( $total_apt_facilities ); ?> <?php vv_e( 'facilities' ); ?></span>
        <span class="vv-apt2026-facility-counter d-none" id="apt2026FacilityCounterBld"><?php echo intval( $building_confirmed_count ); ?> / <?php echo intval( $total_building_facilities ); ?> <?php vv_e( 'confirmed' ); ?></span>
    </div>

    <div class="tab-content">
        <div class="tab-pane fade show active" id="apt2026FacApt" role="tabpanel">
            <h6 class="vv-apt2026-section-title"><?php vv_e( 'Apartment facilities' ); ?></h6>
            <p class="vv-apt2026-hint mb-3"><?php vv_e( 'Check the amenities available in your specific apartment. You can add your own beyond what is pre-filled.' ); ?></p>
            <?php if ( empty( $apt_facilities ) ) { ?>
                <p class="text-muted mb-0"><?php vv_e( 'No facilities configured yet. Ask Vietstays admin to add facility options.' ); ?></p>
            <?php } else { ?>
                <div class="vv-apt2026-facility-grid vv-apt2026-facility-grid--4col">
                    <?php foreach ( $apt_facilities as $facility ) {
                        $fid     = intval( gArrayItem( $facility, 'facility_id' ) );
                        $fname   = gArrayItem( $facility, 'name' );
                        $checked = in_array( $fid, $selected, false );
                        $icon    = vv_wizard_facility_icon( $fname );
                        ?>
                        <label class="vv-apt2026-facility-item vv-apt2026-facility-item--apt<?php echo $checked ? ' is-checked' : ''; ?>">
                            <input type="checkbox" class="apt2026-facility-cb" name="facilities[]" value="<?php echo $fid; ?>" <?php checked( $checked ); ?>>
                            <span class="vv-apt2026-facility-box" aria-hidden="true"></span>
                            <i class="fa <?php echo esc_attr( $icon ); ?> vv-apt2026-facility-icon" aria-hidden="true"></i>
                            <span class="vv-apt2026-facility-label"><?php echo esc_html( $fname ); ?></span>
                        </label>
                    <?php } ?>
                </div>
            <?php } ?>
        </div>
        <div class="tab-pane fade" id="apt2026FacBld" role="tabpanel">
            <?php if ( $building_id <= 0 ) { ?>
                <p class="text-muted"><?php vv_e( 'Select a building in step 1 first.' ); ?></p>
            <?php } elseif ( empty( $building_level_facilities ) ) { ?>
                <p class="text-muted"><?php vv_e( 'No building amenities registered yet.' ); ?></p>
            <?php } else { ?>
                <h6 class="vv-apt2026-section-title"><?php vv_e( 'Building amenities' ); ?></h6>
                <p class="vv-apt2026-hint mb-3"><?php printf( esc_html( vv__( 'These are pre-registered by Vietstays and apply to all of %s. Confirmed amenities are included automatically. Select views and apartment-specific items on the Apartment facilities tab.' ) ), esc_html( gArrayItem( $building, 'post_title' ) ) ); ?></p>
                <div class="vv-apt2026-facility-grid vv-apt2026-facility-grid--4col vv-apt2026-facility-grid--building">
                    <?php
                    foreach ( $building_level_facilities as $facility ) {
                        $fid          = intval( gArrayItem( $facility, 'facility_id' ) );
                        $is_confirmed = in_array( $fid, $building_facility_ids, true );
                        $icon         = vv_wizard_facility_icon( gArrayItem( $facility, 'name' ) );
                        ?>
                        <div class="vv-apt2026-facility-item vv-apt2026-facility-item--building<?php echo $is_confirmed ? ' is-confirmed' : ' is-unconfirmed'; ?>">
                            <span class="vv-apt2026-facility-box" aria-hidden="true"><?php echo $is_confirmed ? '✓' : ''; ?></span>
                            <i class="fa <?php echo esc_attr( $icon ); ?> vv-apt2026-facility-icon" aria-hidden="true"></i>
                            <span class="vv-apt2026-facility-label"><?php echo esc_html( gArrayItem( $facility, 'name' ) ); ?></span>
                        </div>
                    <?php } ?>
                </div>
                <div class="vv-apt2026-info-box mt-3">
                    <span class="vv-apt2026-info-box__icon">&#128274;</span>
                    <p><?php vv_e( 'Green amenities are confirmed by Vietstays for this building. Grey items are not yet confirmed — you can still select views and apartment amenities on the Apartment facilities tab.' ); ?></p>
                </div>
            <?php } ?>
        </div>
    </div>
</form>
