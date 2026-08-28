<?php
/**
 * Step 1 — Building & type (Nav v3 mockup layout).
 */
$cities        = vv_get_cities( [ 'per_page' => 'all', 'orderby' => 'post_title ASC' ] );
$quality_tiers = vv_apartments_quality_tiers();
$types         = vv_apartments_types();

$building_id          = intval( gArrayItem( $apartment, 'building_id' ) );
$selected_building    = [];
$selected_district_id = 0;
$selected_city_id     = 0;
if ( $building_id > 0 ) {
	$selected_building    = vv_get_neighbourhood( $building_id );
	$selected_district_id = intval( gArrayItem( $selected_building, 'post_parent' ) );
	if ( $selected_district_id > 0 ) {
		$selected_city_id = intval( get_post_meta( $selected_district_id, 'city', true ) );
		if ( $selected_city_id <= 0 && function_exists( 'get_field' ) ) {
			$selected_city_id = intval( get_field( 'city', $selected_district_id ) );
		}
	}
}
$cascade_url            = vv_admin_url( '?action=apt_wizard_location_cascade' );
$apartment_type         = gArrayItem( $apartment, 'apartment_type', '' );
$distinguishing_feature = gArrayItem( $apartment, 'distinguishing_feature' );
$name                   = gArrayItem( $apartment, 'name' );
$room_number            = gArrayItem( $apartment, 'room_number' );
$quality_standard       = gArrayItem( $apartment, 'quality_standard', 'above_average' );
if ( ! isset( $quality_tiers[ $quality_standard ] ) ) {
	$quality_standard = 'above_average';
}
$about_short = gArrayItem( $apartment, 'about_this_short' );
$host_user_id = intval( gArrayItem( $apartment, 'user_id', get_current_user_id() ) );
?>
<form method="post" id="vvApt2026Step1" class="vv-apt-wizard-form">
    <input type="hidden" name="vv_action" value="vv_save_apartment_wizard_step">
    <input type="hidden" name="wizard_step" value="1">
    <input type="hidden" name="apartment_id" value="<?php echo intval( $apartment_id ); ?>">
    <?php if ( $logged_user_role === 'administrator' ) { ?>
        <input type="hidden" name="user_id" value="<?php echo esc_attr( $host_user_id ); ?>">
    <?php } ?>

    <div class="vv-apt2026-field-group vv-apt2026-location-cascade" data-cascade-url="<?php echo esc_url( $cascade_url ); ?>" data-selected-city="<?php echo intval( $selected_city_id ); ?>" data-selected-district="<?php echo intval( $selected_district_id ); ?>" data-selected-building="<?php echo intval( $building_id ); ?>">
        <div class="row vv-apt2026-form-row">
            <div class="col-md-6">
                <label class="vv-apt2026-label" for="apt2026_country_id"><?php vv_e( 'Country' ); ?></label>
                <select id="apt2026_country_id" class="vv-apt2026-field is-filled" disabled>
                    <option value="VN" selected><?php vv_e( 'Vietnam' ); ?></option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="vv-apt2026-label" for="apt2026_city_id"><?php vv_e( 'City' ); ?></label>
                <select id="apt2026_city_id" class="vv-apt2026-field">
                    <option value=""><?php vv_e( 'Select city…' ); ?></option>
                    <?php foreach ( $cities as $city ) {
                        $cid = intval( gArrayItem( $city, 'ID' ) );
                        ?>
                        <option value="<?php echo $cid; ?>" <?php selected( $selected_city_id, $cid ); ?>><?php echo esc_html( gArrayItem( $city, 'post_title' ) ); ?></option>
                    <?php } ?>
                </select>
            </div>
        </div>
        <div class="row vv-apt2026-form-row">
            <div class="col-md-6">
                <label class="vv-apt2026-label" for="apt2026_district_id"><?php vv_e( 'District / neighbourhood' ); ?></label>
                <select id="apt2026_district_id" class="vv-apt2026-field" <?php echo $selected_city_id <= 0 ? 'disabled' : ''; ?>>
                    <option value=""><?php vv_e( 'Select district…' ); ?></option>
                </select>
                <p class="vv-apt2026-hint mb-0"><?php vv_e( 'Selected after city' ); ?></p>
            </div>
            <div class="col-md-6">
                <label class="vv-apt2026-label" for="apt2026_building_id"><?php vv_e( 'Building' ); ?></label>
                <select name="building_id" id="apt2026_building_id" class="vv-apt2026-field" required <?php echo $selected_district_id <= 0 ? 'disabled' : ''; ?>>
                    <option value=""><?php vv_e( 'Select building…' ); ?></option>
                    <?php if ( $building_id > 0 && ! empty( $selected_building['post_title'] ) ) {
                        $location_label = vv_get_building_location_label( $building_id );
                        ?>
                        <option value="<?php echo intval( $building_id ); ?>" selected
                            data-name="<?php echo esc_attr( gArrayItem( $selected_building, 'post_title' ) ); ?>"
                            data-district-label="<?php echo esc_attr( vv_get_district_display_name( $selected_district_id ) ); ?>"
                            data-location="<?php echo esc_attr( $location_label ); ?>"><?php echo esc_html( gArrayItem( $selected_building, 'post_title' ) ); ?></option>
                    <?php } ?>
                </select>
                <p class="vv-apt2026-hint mb-0"><?php vv_e( 'Selected after district' ); ?></p>
            </div>
        </div>
        <p class="vv-apt2026-hint mb-1" id="apt2026BuildingLocationHint" style="<?php echo $building_id > 0 ? '' : 'display:none'; ?>">
            <?php
            if ( $building_id > 0 ) {
                echo esc_html( vv_get_building_location_label( $building_id ) );
            }
            ?>
        </p>
        <p class="mb-0">
            <button type="button" class="btn btn-link btn-sm p-0 vv-apt2026-link-orange" id="apt2026ToggleBuildingRequest">+ <?php vv_e( 'Suggest new building' ); ?></button>
        </p>
    </div>

    <div class="vv-apt2026-building-request" id="apt2026BuildingRequest" style="display:none">
        <h6><?php vv_e( 'Suggest a new building' ); ?></h6>
        <p class="vv-apt2026-hint"><?php vv_e( 'Vietstays will review your request and add the building so you can finish listing setup.' ); ?></p>
        <div class="row vv-apt2026-form-row">
            <div class="col-md-6">
                <label class="vv-apt2026-label"><?php vv_e( 'Building name' ); ?></label>
                <input type="text" name="request_building_name" class="vv-apt2026-field" maxlength="200">
            </div>
            <div class="col-md-6">
                <label class="vv-apt2026-label"><?php vv_e( 'Street address' ); ?></label>
                <input type="text" name="request_address" class="vv-apt2026-field">
            </div>
        </div>
        <div class="mb-2">
            <label class="vv-apt2026-label"><?php vv_e( 'Additional notes' ); ?></label>
            <textarea name="request_notes" class="vv-apt2026-field" rows="2"></textarea>
        </div>
        <input type="hidden" name="request_city_id" id="apt2026_request_city_id" value="<?php echo intval( $selected_city_id ); ?>">
        <input type="hidden" name="request_district_id" id="apt2026_request_district_id" value="<?php echo intval( $selected_district_id ); ?>">
        <button type="submit" formaction="<?php echo esc_url( vv_admin_url( '' ) ); ?>" formmethod="post" name="vv_action" value="vv_submit_building_request" class="vv-apt2026-btn-pick vv-apt2026-btn-pick--sm"><?php vv_e( 'Send request' ); ?></button>
    </div>

    <div class="vv-apt2026-field-group">
        <label class="vv-apt2026-label"><?php vv_e( 'Apartment type' ); ?></label>
        <div class="vv-apt2026-type-pills vv-apt2026-type-pills--spaced">
            <?php foreach ( $types as $key => $label ) { ?>
                <label>
                    <input type="radio" name="apartment_type" value="<?php echo esc_attr( $key ); ?>" <?php checked( $apartment_type, $key ); ?> required>
                    <span><?php echo esc_html( $label ); ?></span>
                </label>
            <?php } ?>
        </div>
    </div>

    <div class="vv-apt2026-field-group">
        <div class="vv-apt2026-label-row">
            <label class="vv-apt2026-label mb-0" for="apt2026_feature"><?php vv_e( 'Distinctive feature' ); ?></label>
            <span class="vv-apt2026-label-note"><?php vv_e( 'Used in the apartment name' ); ?></span>
        </div>
        <input type="text" name="distinguishing_feature" id="apt2026_feature" class="vv-apt2026-field" maxlength="120"
            value="<?php echo esc_attr( $distinguishing_feature ); ?>"
            placeholder="<?php echo esc_attr( vv__( "e.g. 'City View & Pool'…" ) ); ?>" required>
        <p class="vv-apt2026-hint"><?php vv_e( 'Apartment name is generated automatically as you fill in the fields.' ); ?></p>
        <p class="vv-apt2026-name-preview" id="apt2026_name_preview"></p>
        <input type="hidden" name="name" id="apt2026_name" value="<?php echo esc_attr( $name ); ?>">
    </div>

    <div class="row vv-apt2026-form-row">
        <div class="col-md-6">
            <div class="vv-apt2026-field-group mb-0">
                <div class="vv-apt2026-label-row">
                    <label class="vv-apt2026-label mb-0" for="apt2026_room"><?php vv_e( 'Floor / unit ID' ); ?></label>
                    <span class="vv-apt2026-label-note"><?php vv_e( 'optional' ); ?></span>
                </div>
                <input type="text" name="room_number" id="apt2026_room" class="vv-apt2026-field" value="<?php echo esc_attr( $room_number ); ?>"
                    placeholder="<?php echo esc_attr( vv__( 'e.g. 8A, floor 12…' ) ); ?>">
            </div>
        </div>
        <div class="col-md-6">
            <div class="vv-apt2026-field-group mb-0">
                <label class="vv-apt2026-label"><?php vv_e( 'Apartment standard' ); ?></label>
                <div class="vv-apt2026-standard-pills">
                    <?php foreach ( $quality_tiers as $key => $tier ) { ?>
                        <label>
                            <input type="radio" name="quality_standard" value="<?php echo esc_attr( $key ); ?>" <?php checked( $quality_standard, $key ); ?>>
                            <span>
                                <strong><?php echo esc_html( vv__( $tier['label'] ) ); ?></strong>
                                <?php if ( ! empty( $tier['sublabel'] ) ) { ?>
                                    <small><?php echo esc_html( vv__( $tier['sublabel'] ) ); ?></small>
                                <?php } ?>
                            </span>
                        </label>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>

    <div class="vv-apt2026-field-group">
        <div class="vv-apt2026-label-row">
            <label class="vv-apt2026-label mb-0"><?php vv_e( 'Short description' ); ?></label>
            <span class="vv-apt2026-label-note"><?php vv_e( 'max 300 characters' ); ?></span>
        </div>
        <textarea name="i18n_short[en]" class="vv-apt2026-field apt2026-short-desc" rows="5" maxlength="300" placeholder="<?php echo esc_attr( vv__( 'Describe the apartment for guests…' ) ); ?>"><?php echo esc_textarea( $about_short ); ?></textarea>
        <div class="vv-apt2026-char-count"><span class="apt2026-char-num">0</span> / 300</div>
    </div>
</form>
