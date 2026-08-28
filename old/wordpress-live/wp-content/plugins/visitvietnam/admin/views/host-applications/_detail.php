<?php
if ( ! is_array( $application ) || intval( gArrayItem( $application, 'ID' ) ) <= 0 ) {
	echo '<div class="alert alert-danger">' . esc_html( vv__( 'Host application not found for this partner.' ) ) . '</div>';
	return;
}

$district_ids      = vvHostApplications::decode_json_field( gArrayItem( $application, 'districts' ) );
$all_status_labels = vvHostApplications::status_labels();
$can_approve       = ( $logged_user_role === 'administrator' );
$detail_user_id    = intval( gArrayItem( $application, 'user_id' ) );
$email_verified    = $detail_user_id > 0 ? vv_is_host_email_verified( $detail_user_id ) : false;

$status_labels = $all_status_labels;
unset( $status_labels['activated'] );
if ( ! $can_approve ) {
	$status_labels = [
		'under_review' => $all_status_labels['under_review'],
	];
}
$rejection_reasons = vvHostApplications::rejection_reasons();
$years_options     = vvHostApplications::years_managing_options();
$guest_profiles    = vvHostApplications::guest_profile_options();
$can_edit_profile  = ( $detail_user_id > 0 && ( get_current_user_id() === $detail_user_id || $logged_user_role === 'administrator' ) );
$city_options      = vvHostApplications::get_city_options();
$primary_city_id   = intval( gArrayItem( $application, 'primary_city_id' ) );
$selected_districts = vvHostApplications::decode_json_field( gArrayItem( $application, 'districts' ) );
if ( ! is_array( $selected_districts ) ) {
	$selected_districts = [];
}
$selected_districts = array_map( 'intval', $selected_districts );
$districts_by_city  = [];
foreach ( vvHostApplications::get_district_checklist() as $item ) {
	$city_key = (string) intval( gArrayItem( $item, 'city_id' ) );
	if ( ! isset( $districts_by_city[ $city_key ] ) ) {
		$districts_by_city[ $city_key ] = [];
	}
	$districts_by_city[ $city_key ][] = [
		'id'    => intval( gArrayItem( $item, 'id' ) ),
		'label' => gArrayItem( $item, 'label' ),
	];
}
$platform_options   = vvHostApplications::platform_options();
$selected_platforms = vvHostApplications::decode_json_field( gArrayItem( $application, 'platforms_used' ) );
if ( ! is_array( $selected_platforms ) ) {
	$selected_platforms = [];
}
$years_key = gArrayItem( $application, 'years_managing' );
$guest_key = gArrayItem( $application, 'guest_profile' );
?>

<div class="row">
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header"><h4 class="mb-0"><?php vv_e( 'Applicant details' ); ?></h4></div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr><th style="width:180px"><?php vv_e( 'Application ID' ); ?></th><td><?php echo esc_html( gArrayItem( $application, 'application_ref' ) ); ?></td></tr>
                    <tr><th><?php vv_e( 'Application status' ); ?></th><td><?php echo esc_html( vvHostApplications::status_label( gArrayItem( $application, 'status' ) ) ); ?></td></tr>
                    <?php if ( $detail_user_id > 0 ) { ?>
                        <?php $account_status = vv_get_user_account_status( $detail_user_id ); ?>
                        <tr><th><?php vv_e( 'Account role' ); ?></th><td><?php echo esc_html( get_user_meta( $detail_user_id, 'user_role', true ) ?: 'host' ); ?></td></tr>
                        <tr><th><?php vv_e( 'Account status' ); ?></th><td><?php echo esc_html( vvHostApplications::user_status_label( $account_status ) ); ?></td></tr>
                        <tr><th><?php vv_e( 'Email verified' ); ?></th><td><?php echo $email_verified ? esc_html( vv__( 'Yes' ) ) : esc_html( vv__( 'No' ) ); ?></td></tr>
                    <?php } ?>
                    <tr><th><?php vv_e( 'Host type' ); ?></th><td><?php echo ( gArrayItem( $application, 'applicant_type' ) === 'single_property' ) ? esc_html( vv__( 'One apartment' ) ) : esc_html( vv__( 'Multiple apartments' ) ); ?></td></tr>
                    <?php if ( ! $can_edit_profile ) { ?>
                    <tr><th><?php vv_e( 'Full name' ); ?></th><td><?php echo esc_html( gArrayItem( $application, 'full_name' ) ); ?></td></tr>
                    <tr><th><?php vv_e( 'Email' ); ?></th><td><a href="mailto:<?php echo esc_attr( gArrayItem( $application, 'email' ) ); ?>"><?php echo esc_html( gArrayItem( $application, 'email' ) ); ?></a></td></tr>
                    <?php } ?>
                    <tr><th><?php vv_e( 'Phone' ); ?></th><td><?php echo esc_html( gArrayItem( $application, 'phone' ) ); ?></td></tr>
                    <tr><th><?php vv_e( 'Submitted' ); ?></th><td><?php echo esc_html( date( 'j M Y H:i', strtotime( gArrayItem( $application, 'dateadded' ) ) ) ); ?></td></tr>
                </table>
            </div>
        </div>

        <?php if ( $can_edit_profile ) { ?>
        <form method="post">
            <input type="hidden" name="vv_action" value="save_partner_profile">
            <input type="hidden" name="application_id" value="<?php echo intval( $application['ID'] ); ?>">
            <input type="hidden" name="user_id" value="<?php echo $detail_user_id; ?>">

            <div class="card mb-4">
                <div class="card-header"><h4 class="mb-0"><?php vv_e( 'Contact details' ); ?></h4></div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="partner_full_name"><?php vv_e( 'Full name' ); ?></label>
                                <input type="text" name="full_name" id="partner_full_name" class="form-control form-control-sm" value="<?php echo esc_attr( gArrayItem( $application, 'full_name' ) ); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-md-0">
                                <label for="partner_email"><?php vv_e( 'Email' ); ?></label>
                                <input type="email" name="email" id="partner_email" class="form-control form-control-sm" value="<?php echo esc_attr( gArrayItem( $application, 'email' ) ); ?>" required>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header"><h4 class="mb-0"><?php vv_e( 'Address' ); ?></h4></div>
                <div class="card-body">
                    <div class="form-group mb-0">
                        <label for="partner_address"><?php vv_e( 'Address' ); ?></label>
                        <textarea name="address" id="partner_address" class="form-control form-control-sm" rows="3"><?php echo esc_textarea( gArrayItem( $application, 'address' ) ); ?></textarea>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header"><h4 class="mb-0"><?php vv_e( 'Portfolio & manager profile' ); ?></h4></div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="primary_city_id"><?php vv_e( 'Primary city' ); ?></label>
                                <select name="primary_city_id" id="primary_city_id" class="form-control form-control-sm" required>
                                    <option value=""><?php vv_e( 'Select city' ); ?></option>
                                    <?php foreach ( $city_options as $city_id => $city_name ) { ?>
                                        <option value="<?php echo intval( $city_id ); ?>"<?php selected( $primary_city_id, intval( $city_id ) ); ?>><?php echo esc_html( $city_name ); ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="num_properties"><?php vv_e( 'Number of apartments' ); ?></label>
                                <input type="number" min="1" name="num_properties" id="num_properties" class="form-control form-control-sm" value="<?php echo max( 1, intval( gArrayItem( $application, 'num_properties' ) ) ); ?>" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label><?php vv_e( 'Districts' ); ?></label>
                        <div id="partner_districts_list" class="border rounded p-2" style="max-height:220px;overflow:auto;"></div>
                        <p class="text-muted small mb-0 mt-2" id="partner_districts_placeholder"><?php vv_e( 'Select a primary city to choose districts.' ); ?></p>
                    </div>
                    <div class="form-group">
                        <label for="company_name"><?php vv_e( 'Company / brand' ); ?></label>
                        <input type="text" name="company_name" id="company_name" class="form-control form-control-sm" value="<?php echo esc_attr( gArrayItem( $application, 'company_name' ) ); ?>">
                    </div>
                    <div class="form-group">
                        <label for="portfolio_url"><?php vv_e( 'Portfolio link' ); ?></label>
                        <input type="url" name="portfolio_url" id="portfolio_url" class="form-control form-control-sm" value="<?php echo esc_attr( gArrayItem( $application, 'portfolio_url' ) ); ?>" placeholder="https://">
                    </div>
                    <div class="form-group mb-0">
                        <label for="partner_description"><?php vv_e( 'Manager / portfolio description' ); ?></label>
                        <textarea name="description" id="partner_description" class="form-control form-control-sm" rows="5" required><?php echo esc_textarea( gArrayItem( $application, 'description' ) ); ?></textarea>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header"><h4 class="mb-0"><?php vv_e( 'Experience & fit' ); ?></h4></div>
                <div class="card-body">
                    <p class="text-muted small"><?php vv_e( 'Optional — helps our team understand your hosting background.' ); ?></p>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="years_managing"><?php vv_e( 'Years managing' ); ?></label>
                                <select name="years_managing" id="years_managing" class="form-control form-control-sm">
                                    <?php foreach ( $years_options as $value => $label ) { ?>
                                        <option value="<?php echo esc_attr( $value ); ?>"<?php selected( $years_key, $value ); ?>><?php echo esc_html( $label ); ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="guest_profile"><?php vv_e( 'Typical guests' ); ?></label>
                                <select name="guest_profile" id="guest_profile" class="form-control form-control-sm">
                                    <?php foreach ( $guest_profiles as $value => $label ) { ?>
                                        <option value="<?php echo esc_attr( $value ); ?>"<?php selected( $guest_key, $value ); ?>><?php echo esc_html( $label ); ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group mb-0">
                        <label><?php vv_e( 'Platforms used' ); ?></label>
                        <div class="border rounded p-2">
                            <?php foreach ( $platform_options as $value => $label ) { ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="platforms_used[]" id="platform_<?php echo esc_attr( $value ); ?>" value="<?php echo esc_attr( $value ); ?>"<?php checked( in_array( $value, $selected_platforms, true ) ); ?>>
                                    <label class="form-check-label" for="platform_<?php echo esc_attr( $value ); ?>"><?php echo esc_html( $label ); ?></label>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header"><h4 class="mb-0"><?php vv_e( 'Account Settings' ); ?></h4></div>
                <div class="card-body">
                    <?php
                    $user_id = $detail_user_id;
                    include dirname( __DIR__ ) . '/users/_language_setting.php';
                    include dirname( __DIR__ ) . '/users/_currency_setting.php';
                    ?>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-sm mb-4"><?php vv_e( 'Save profile' ); ?></button>
        </form>
        <?php } else { ?>
        <div class="card mb-4">
            <div class="card-header"><h4 class="mb-0"><?php vv_e( 'Address' ); ?></h4></div>
            <div class="card-body">
                <?php if ( trim( gArrayItem( $application, 'address' ) ) !== '' ) { ?>
                    <p class="mb-0"><?php echo nl2br( esc_html( gArrayItem( $application, 'address' ) ) ); ?></p>
                <?php } else { ?>
                    <p class="text-muted mb-0">—</p>
                <?php } ?>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header"><h4 class="mb-0"><?php vv_e( 'Portfolio & manager profile' ); ?></h4></div>
            <div class="card-body">
                <p><strong><?php vv_e( 'Primary city' ); ?>:</strong> <?php echo esc_html( gArrayItem( $application, 'home_city' ) ); ?></p>
                <p><strong><?php vv_e( 'Number of apartments' ); ?>:</strong> <?php echo intval( gArrayItem( $application, 'num_properties' ) ); ?></p>
                <p><strong><?php vv_e( 'Districts' ); ?>:</strong> <?php echo esc_html( vvHostApplications::format_district_labels( $district_ids ) ); ?></p>
                <?php if ( gArrayItem( $application, 'company_name' ) !== '' ) { ?>
                    <p><strong><?php vv_e( 'Company / brand' ); ?>:</strong> <?php echo esc_html( gArrayItem( $application, 'company_name' ) ); ?></p>
                <?php } ?>
                <?php if ( gArrayItem( $application, 'portfolio_url' ) !== '' ) { ?>
                    <p><strong><?php vv_e( 'Portfolio link' ); ?>:</strong> <a href="<?php echo esc_url( gArrayItem( $application, 'portfolio_url' ) ); ?>" target="_blank" rel="noopener"><?php echo esc_html( gArrayItem( $application, 'portfolio_url' ) ); ?></a></p>
                <?php } ?>
                <p><strong><?php vv_e( 'Manager / portfolio description' ); ?>:</strong></p>
                <div><?php echo nl2br( esc_html( gArrayItem( $application, 'description' ) ) ); ?></div>
            </div>
        </div>
        <?php } ?>

        <?php
        if ( ! $can_edit_profile ) {
            $platforms = vvHostApplications::format_platform_labels( gArrayItem( $application, 'platforms_used' ) );
            ?>
            <div class="card mb-4">
                <div class="card-header"><h4 class="mb-0"><?php vv_e( 'Experience & fit' ); ?></h4></div>
                <div class="card-body">
                    <p><strong><?php vv_e( 'Years managing' ); ?>:</strong> <?php echo esc_html( gArrayItem( $years_options, $years_key, vv__( 'Prefer not to say' ) ) ); ?></p>
                    <p><strong><?php vv_e( 'Typical guests' ); ?>:</strong> <?php echo esc_html( gArrayItem( $guest_profiles, $guest_key, vv__( 'Prefer not to say' ) ) ); ?></p>
                    <?php if ( $platforms !== '' ) { ?>
                        <p><strong><?php vv_e( 'Platforms used' ); ?>:</strong> <?php echo esc_html( $platforms ); ?></p>
                    <?php } ?>
                </div>
            </div>
        <?php } ?>

        <?php
        if ( gArrayItem( $application, 'applicant_type' ) === 'single_property' ) {
            $apartment_id     = intval( gArrayItem( $application, 'apartment_id' ) );
            $linked_apartment = $apartment_id > 0 ? vv_get_apartment( $apartment_id ) : null;
            $property_images  = vvHostApplications::decode_json_field( gArrayItem( $application, 'property_images' ) );
            ?>
            <div class="card mb-4">
                <div class="card-header"><h4 class="mb-0"><?php vv_e( 'Registered apartment' ); ?></h4></div>
                <div class="card-body">
                    <?php if ( is_array( $linked_apartment ) && intval( gArrayItem( $linked_apartment, 'ID' ) ) > 0 ) { ?>
                        <p><strong><?php vv_e( 'Listing' ); ?>:</strong> <a href="<?php echo esc_url( vv_admin_url( 'apartments/edit/?id=' . intval( $linked_apartment['ID'] ) ) ); ?>"><?php echo esc_html( gArrayItem( $linked_apartment, 'display_name', gArrayItem( $linked_apartment, 'name' ) ) ); ?></a></p>
                        <p><strong><?php vv_e( 'Listing status' ); ?>:</strong> <?php echo esc_html( gArrayItem( vvApartments::status_labels(), gArrayItem( $linked_apartment, 'status' ), gArrayItem( $linked_apartment, 'status' ) ) ); ?></p>
                        <p><strong><?php vv_e( 'Address' ); ?>:</strong> <?php echo esc_html( gArrayItem( $linked_apartment, 'address', gArrayItem( $application, 'property_address' ) ) ); ?></p>
                    <?php } else { ?>
                        <p><strong><?php vv_e( 'Address' ); ?>:</strong> <?php echo esc_html( gArrayItem( $application, 'property_address' ) ); ?></p>
                    <?php } ?>
                    <p><strong><?php vv_e( 'Guest access' ); ?>:</strong> <?php echo esc_html( vvHostApplications::space_type_label( gArrayItem( $application, 'property_type' ) ) ); ?></p>
                    <?php if ( floatval( gArrayItem( $application, 'property_price_daily' ) ) > 0 ) { ?>
                        <p><strong><?php vv_e( 'Nightly price' ); ?>:</strong> <?php echo esc_html( vv_site_currency() . ' ' . gArrayItem( $application, 'property_price_daily' ) ); ?></p>
                    <?php } ?>
                    <?php if ( intval( gArrayItem( $application, 'property_beds' ) ) > 0 ) { ?>
                        <p><strong><?php vv_e( 'Capacity' ); ?>:</strong> <?php echo intval( gArrayItem( $application, 'property_beds' ) ); ?> <?php vv_e( 'beds' ); ?> · <?php echo intval( gArrayItem( $application, 'property_bathrooms' ) ); ?> <?php vv_e( 'baths' ); ?></p>
                    <?php } ?>
                    <?php
                    $amenity_labels = vvHostApplications::format_property_amenity_labels( gArrayItem( $application, 'property_amenities' ) );
                    if ( $amenity_labels !== '' ) {
                        ?>
                        <p><strong><?php vv_e( 'Amenities' ); ?>:</strong> <?php echo esc_html( $amenity_labels ); ?></p>
                    <?php } ?>
                    <?php if ( is_array( $property_images ) && count( $property_images ) > 0 ) { ?>
                        <p><strong><?php vv_e( 'Photos' ); ?>:</strong> <?php echo intval( count( $property_images ) ); ?> <?php vv_e( 'uploaded' ); ?></p>
                    <?php } ?>
                    <p class="text-muted small mb-0"><?php vv_e( 'Pending listings become active when the host application is approved.' ); ?></p>
                </div>
            </div>
        <?php } ?>
    </div>

    <div class="col-lg-4">
        <?php if ( $can_approve || $logged_user_role === 'sales_support' ) { ?>
        <div class="card">
            <div class="card-header"><h4 class="mb-0"><?php vv_e( 'Update status' ); ?></h4></div>
            <div class="card-body">
                <form method="post">
                    <input type="hidden" name="vv_action" value="save_host_application_status">
                    <input type="hidden" name="application_id" value="<?php echo intval( $application['ID'] ); ?>">
                    <input type="hidden" name="user_id" value="<?php echo $detail_user_id; ?>">

                    <div class="form-group">
                        <label><?php vv_e( 'Status' ); ?></label>
                        <select name="status" class="form-control form-control-sm" required>
                            <?php foreach ( $status_labels as $key => $label ) { ?>
                                <option value="<?php echo esc_attr( $key ); ?>" <?php selected( gArrayItem( $application, 'status' ), $key ); ?>><?php echo esc_html( $label ); ?></option>
                            <?php } ?>
                            <?php if ( ! isset( $status_labels[ gArrayItem( $application, 'status' ) ] ) ) { ?>
                                <option value="<?php echo esc_attr( gArrayItem( $application, 'status' ) ); ?>" selected><?php echo esc_html( vvHostApplications::status_label( gArrayItem( $application, 'status' ) ) ); ?></option>
                            <?php } ?>
                        </select>
                    </div>

                    <div id="rejectionFields" style="<?php echo ( gArrayItem( $application, 'status' ) === 'rejected' ) ? '' : 'display:none'; ?>">
                        <div class="form-group">
                            <label><?php vv_e( 'Rejection reason' ); ?></label>
                            <select name="rejection_reason" class="form-control form-control-sm">
                                <option value=""><?php vv_e( 'Select reason' ); ?></option>
                                <?php foreach ( $rejection_reasons as $reason_key => $reason_label ) { ?>
                                    <option value="<?php echo esc_attr( $reason_key ); ?>" <?php selected( gArrayItem( $application, 'rejection_reason' ), $reason_key ); ?>><?php echo esc_html( $reason_label ); ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label><?php printf( esc_html( vv__( 'Comment (max %d chars)' ) ), intval( vvHostApplications::REJECTION_COMMENT_MAX ) ); ?></label>
                            <textarea name="rejection_comment" class="form-control form-control-sm" rows="3" maxlength="<?php echo intval( vvHostApplications::REJECTION_COMMENT_MAX ); ?>"><?php echo esc_textarea( gArrayItem( $application, 'rejection_comment' ) ); ?></textarea>
                        </div>
                    </div>

                    <?php if ( ! $can_approve ) { ?>
                        <p class="text-muted small"><?php vv_e( 'Sales/Support can set Under review only. Final approval requires an administrator.' ); ?></p>
                    <?php } elseif ( gArrayItem( $application, 'status' ) === 'approved' && ! $email_verified ) { ?>
                        <p class="text-muted small"><?php vv_e( 'The applicant must verify their email via the approval link before the account becomes Activated.' ); ?></p>
                    <?php } ?>

                    <button type="submit" class="btn btn-primary btn-sm"><?php vv_e( 'Save status' ); ?></button>
                </form>

                <?php if ( gArrayItem( $application, 'status' ) === 'approved' && ! $email_verified ) { ?>
                    <hr>
                    <p class="small text-muted mb-0"><?php vv_e( 'An activation email with a password setup link was sent when this application was approved.' ); ?></p>
                <?php } elseif ( gArrayItem( $application, 'status' ) === 'activated' || $email_verified ) { ?>
                    <hr>
                    <p class="small text-muted mb-0"><?php vv_e( 'This host can sign in at' ); ?> <a href="<?php echo esc_url( vv_admin_url( 'login' ) ); ?>"><?php vv_e( 'the host portal' ); ?></a> <?php vv_e( 'to create and manage listings.' ); ?></p>
                <?php } else { ?>
                    <hr>
                    <p class="small text-muted mb-0"><?php vv_e( 'Listings are created in the host portal only after approval and activation.' ); ?></p>
                <?php } ?>
            </div>
        </div>
        <?php } else { ?>
        <div class="card">
            <div class="card-header"><h4 class="mb-0"><?php vv_e( 'Application status' ); ?></h4></div>
            <div class="card-body">
                <p class="mb-2"><strong><?php echo esc_html( vvHostApplications::status_label( gArrayItem( $application, 'status' ) ) ); ?></strong></p>
                <?php if ( gArrayItem( $application, 'status' ) === 'activated' || $email_verified ) { ?>
                    <p class="small text-muted mb-0"><?php vv_e( 'Your host account is active. Manage your listings from the host portal.' ); ?></p>
                <?php } ?>
            </div>
        </div>
        <?php } ?>

        <?php
        if ( $can_approve || $logged_user_role === 'sales_support' ) {
        $status_history = vvHostApplications::get_status_history( $application );
        if ( is_array( $status_history ) && count( $status_history ) > 0 ) {
            ?>
            <div class="card mt-4">
                <div class="card-header"><h4 class="mb-0"><?php vv_e( 'Status history' ); ?></h4></div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th><?php vv_e( 'Date' ); ?></th>
                                <th><?php vv_e( 'Status' ); ?></th>
                                <th><?php vv_e( 'Changed by' ); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ( array_reverse( $status_history ) as $entry ) { ?>
                                <tr>
                                    <td><?php echo esc_html( date( 'j M Y H:i', strtotime( gArrayItem( $entry, 'changed_at' ) ) ) ); ?></td>
                                    <td><?php echo esc_html( vvHostApplications::status_label( gArrayItem( $entry, 'status' ) ) ); ?></td>
                                    <td><?php echo esc_html( gArrayItem( $entry, 'changed_by_name', vv__( 'System' ) ) ); ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php } ?>
        <?php } ?>
    </div>
</div>

<?php ob_start(); ?>
<script type="application/json" id="partner_districts_data"><?php echo wp_json_encode( $districts_by_city ); ?></script>
<script type="application/json" id="partner_districts_selected"><?php echo wp_json_encode( array_values( $selected_districts ) ); ?></script>
<script>
jQuery(function ($) {
    $('select[name="status"]').on('change', function () {
        if ($(this).val() === 'rejected') {
            $('#rejectionFields').show();
        } else {
            $('#rejectionFields').hide();
        }
    });

    var $city = $('#primary_city_id');
    var $list = $('#partner_districts_list');
    var $placeholder = $('#partner_districts_placeholder');
    if ($city.length && $list.length) {
        var districtsByCity = {};
        var selectedDistricts = [];
        try { districtsByCity = JSON.parse($('#partner_districts_data').text() || '{}'); } catch (e) {}
        try { selectedDistricts = JSON.parse($('#partner_districts_selected').text() || '[]'); } catch (e) {}

        function renderDistricts() {
            var cityId = String($city.val() || '');
            var items = districtsByCity[cityId] || [];
            if (!cityId) {
                $list.empty().hide();
                $placeholder.show();
                return;
            }
            $placeholder.hide();
            if (!items.length) {
                $list.html('<p class="text-muted small mb-0"><?php echo esc_js( vv__( 'No districts for this city.' ) ); ?></p>').show();
                return;
            }
            var html = '';
            for (var i = 0; i < items.length; i++) {
                var checked = selectedDistricts.indexOf(parseInt(items[i].id, 10)) !== -1 ? ' checked' : '';
                html += '<div class="form-check"><input class="form-check-input" type="checkbox" name="districts[]" id="partner_district_' + items[i].id + '" value="' + items[i].id + '"' + checked + '><label class="form-check-label" for="partner_district_' + items[i].id + '">' + items[i].label + '</label></div>';
            }
            $list.html(html).show();
        }

        $city.on('change', function () {
            selectedDistricts = [];
            renderDistricts();
        });
        renderDistricts();
    }
});
</script>
<?php $footer_codes .= ob_get_clean(); ?>
