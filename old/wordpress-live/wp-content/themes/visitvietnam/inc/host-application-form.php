<?php
/**
 * Host application — Airbnb-style listing wizard for single hosts.
 *
 * @package visitvietnam
 */

$app_data       = vvHostApplications::get_session_data();
$applicant_type = gArrayItem( $app_data, 'applicant_type', '' );
$current_step   = intval( GET_Request( 'step' ) );
if ( $current_step <= 0 ) {
	$current_step = 1;
}
if ( $current_step > 1 && $applicant_type === '' ) {
	$current_step = 1;
}

$total_steps  = vvHostApplications::total_steps( $applicant_type );
$step_labels  = vvHostApplications::step_labels( $applicant_type );
if ( $current_step > $total_steps && $applicant_type !== '' ) {
	$current_step = $total_steps;
}
$step_kind = ( $applicant_type !== '' ) ? vvHostApplications::step_at( $current_step, $applicant_type ) : 'type';

$errors = gArrayItem( $_SESSION, 'HOST_APPLICATION_ERRORS', [] );
if ( ! is_array( $errors ) ) {
	$errors = ( is_string( $errors ) && $errors !== '' ) ? [ $errors ] : [];
}
unset( $_SESSION['HOST_APPLICATION_ERRORS'] );

$city_options       = vvHostApplications::get_city_options();
$primary_city_id    = intval( gArrayItem( $app_data, 'primary_city_id' ) );
$property_city_id   = intval( gArrayItem( $app_data, 'property_city_id' ) );
$all_districts      = vvHostApplications::get_district_checklist();
$years_options      = vvHostApplications::years_managing_options();
$guest_profile_opts = vvHostApplications::guest_profile_options();
$platform_options   = vvHostApplications::platform_options();
$space_types        = vvHostApplications::space_type_options();
$facility_options   = vv_get_facilities();
$selected_districts = gArrayItem( $app_data, 'districts', [] );
$selected_platforms = gArrayItem( $app_data, 'platforms_used', [] );
$selected_amenities = gArrayItem( $app_data, 'property_amenities', [] );
$property_images    = vvHostApplications::decode_json_field( gArrayItem( $app_data, 'property_images' ) );

if ( ! is_array( $selected_districts ) ) {
	$selected_districts = [];
}
if ( ! is_array( $selected_platforms ) ) {
	$selected_platforms = [];
}
if ( ! is_array( $selected_amenities ) ) {
	$selected_amenities = [];
}
$selected_districts = array_map( 'intval', $selected_districts );
$selected_amenities = array_map( 'intval', $selected_amenities );

$districts_by_city = [];
foreach ( $all_districts as $item ) {
	$city_key = (string) intval( gArrayItem( $item, 'city_id' ) );
	if ( ! isset( $districts_by_city[ $city_key ] ) ) {
		$districts_by_city[ $city_key ] = [];
	}
	$districts_by_city[ $city_key ][] = [
		'id'    => intval( gArrayItem( $item, 'id' ) ),
		'label' => gArrayItem( $item, 'label' ),
	];
}

$logged_user_id = get_current_user_id();
if ( $applicant_type === 'single_property' && gArrayItem( $app_data, 'property_kind' ) === '' ) {
	$app_data['property_kind'] = vvHostApplications::default_property_kind();
}
if ( $logged_user_id > 0 && gArrayItem( $app_data, 'email' ) === '' ) {
	$user = get_user_by( 'ID', $logged_user_id );
	if ( $user ) {
		$app_data['email']     = $user->user_email;
		$app_data['full_name'] = trim( get_user_meta( $logged_user_id, 'first_name', true ) . ' ' . get_user_meta( $logged_user_id, 'last_name', true ) );
	}
}

$back_step = max( 1, $current_step - 1 );
$currency  = vv_site_currency();
?>

<div class="host-app-form">

	<?php if ( count( $errors ) > 0 ) { ?>
		<div class="host-app-alert host-app-alert-error">
			<ul class="mb-0">
				<?php foreach ( $errors as $error ) { ?>
					<li><?php echo esc_html( $error ); ?></li>
				<?php } ?>
			</ul>
		</div>
	<?php } ?>

	<?php if ( $applicant_type !== '' && $current_step <= $total_steps ) { ?>
		<div class="host-app-progress host-app-progress-labeled">
			<?php for ( $i = 1; $i <= $total_steps; $i++ ) {
				$label = gArrayItem( $step_labels, $i, (string) $i );
				?>
				<span class="host-app-progress-item <?php echo ( $i <= $current_step ) ? 'is-active' : ''; ?> <?php echo ( $i < $current_step ) ? 'is-done' : ''; ?>" title="<?php echo esc_attr( $label ); ?>">
					<span class="host-app-progress-step"><?php echo intval( $i ); ?></span>
					<span class="host-app-progress-label"><?php echo esc_html( $label ); ?></span>
				</span>
			<?php } ?>
		</div>
	<?php } ?>

	<?php if ( $step_kind === 'type' ) { ?>
		<form method="post" action="<?php echo esc_url( vvHostApplications::form_url() ); ?>">
			<input type="hidden" name="vv_action" value="vv_host_application_step">
			<input type="hidden" name="form_step" value="1">

			<h3 class="host-app-step-title">How many apartments are you listing today?</h3>
			<p class="host-app-step-desc">Vietstays is for apartments only. Register one apartment now, or apply as a portfolio manager with multiple units.</p>

			<div class="host-app-choice-grid">
				<label class="host-app-choice">
					<input type="radio" name="applicant_type" value="single_property" <?php checked( $applicant_type, 'single_property' ); ?> required>
					<span class="host-app-choice-box">
						<strong>One apartment</strong>
						<small>Set up your listing — space type, location, photos, price, and more</small>
					</span>
				</label>
				<label class="host-app-choice">
					<input type="radio" name="applicant_type" value="multi_property" <?php checked( $applicant_type, 'multi_property' ); ?> required>
					<span class="host-app-choice-box">
						<strong>Multiple apartments</strong>
						<small>Apply as a portfolio manager — add listings after approval</small>
					</span>
				</label>
			</div>

			<div class="host-app-actions">
				<button type="submit" class="btn">Continue</button>
			</div>
		</form>

	<?php } elseif ( $step_kind === 'space_type' ) { ?>
		<form method="post" action="<?php echo esc_url( vvHostApplications::form_url() ); ?>">
			<input type="hidden" name="vv_action" value="vv_host_application_step">
			<input type="hidden" name="form_step" value="<?php echo intval( $current_step ); ?>">

			<h3 class="host-app-step-title">What type of apartment will guests have?</h3>
			<p class="host-app-step-desc">Be accurate — guests use this to know what space they'll get.</p>

			<div class="host-app-choice-grid">
				<?php foreach ( $space_types as $key => $label ) { ?>
					<label class="host-app-choice">
						<input type="radio" name="property_space_type" value="<?php echo esc_attr( $key ); ?>" <?php checked( gArrayItem( $app_data, 'property_space_type', gArrayItem( $app_data, 'property_type' ) ), $key ); ?> required>
						<span class="host-app-choice-box"><strong><?php echo esc_html( $label ); ?></strong></span>
					</label>
				<?php } ?>
			</div>

			<div class="host-app-actions">
				<a href="<?php echo esc_url( add_query_arg( 'step', $back_step, vvHostApplications::form_url() ) ); ?>" class="btn btn-outline">Back</a>
				<button type="submit" class="btn">Continue</button>
			</div>
		</form>

	<?php } elseif ( $step_kind === 'location' ) { ?>
		<form method="post" action="<?php echo esc_url( vvHostApplications::form_url() ); ?>">
			<input type="hidden" name="vv_action" value="vv_host_application_step">
			<input type="hidden" name="form_step" value="<?php echo intval( $current_step ); ?>">

			<h3 class="host-app-step-title">Where's your place located?</h3>
			<p class="host-app-step-desc">Your exact address is only shared with guests after they book.</p>

			<div class="form-group">
				<label>Street address <span class="required">*</span></label>
				<textarea name="property_address" rows="2" required placeholder="Building, street, ward"><?php echo esc_textarea( gArrayItem( $app_data, 'property_address' ) ); ?></textarea>
			</div>

			<div class="row">
				<div class="col-md-6">
					<div class="form-group">
						<label>City <span class="required">*</span></label>
						<select name="property_city_id" id="host_app_property_city" required>
							<option value="">Select city</option>
							<?php foreach ( $city_options as $city_id => $city_name ) { ?>
								<option value="<?php echo esc_attr( $city_id ); ?>" <?php selected( $property_city_id, $city_id ); ?>><?php echo esc_html( $city_name ); ?></option>
							<?php } ?>
						</select>
					</div>
				</div>
				<div class="col-md-6">
					<div class="form-group">
						<label>District / neighbourhood <span class="required">*</span></label>
						<select name="property_district_id" id="host_app_property_district" required>
							<option value="">Select district</option>
						</select>
					</div>
				</div>
			</div>

			<div class="host-app-actions">
				<a href="<?php echo esc_url( add_query_arg( 'step', $back_step, vvHostApplications::form_url() ) ); ?>" class="btn btn-outline">Back</a>
				<button type="submit" class="btn">Continue</button>
			</div>

			<script type="application/json" id="host_app_property_districts_data"><?php echo wp_json_encode( $districts_by_city ); ?></script>
			<script type="application/json" id="host_app_property_district_selected"><?php echo wp_json_encode( intval( gArrayItem( $app_data, 'property_district_id' ) ) ); ?></script>
			<script>
			(function () {
				var citySelect = document.getElementById('host_app_property_city');
				var districtSelect = document.getElementById('host_app_property_district');
				var dataEl = document.getElementById('host_app_property_districts_data');
				var selectedEl = document.getElementById('host_app_property_district_selected');
				if (!citySelect || !districtSelect || !dataEl) return;
				var districtsByCity = {}, selectedDistrict = 0;
				try { districtsByCity = JSON.parse(dataEl.textContent || '{}'); } catch (e) {}
				if (selectedEl) { try { selectedDistrict = parseInt(JSON.parse(selectedEl.textContent || '0'), 10) || 0; } catch (e) {} }
				function renderDistricts() {
					var cityId = String(citySelect.value || '');
					var items = districtsByCity[cityId] || [];
					var html = '<option value="">Select district</option>';
					for (var i = 0; i < items.length; i++) {
						var selected = (parseInt(items[i].id, 10) === selectedDistrict) ? ' selected' : '';
						html += '<option value="' + parseInt(items[i].id, 10) + '"' + selected + '>' + items[i].label + '</option>';
					}
					districtSelect.innerHTML = html;
				}
				citySelect.addEventListener('change', function () { selectedDistrict = 0; renderDistricts(); });
				renderDistricts();
			})();
			</script>
		</form>

	<?php } elseif ( $step_kind === 'basics' ) { ?>
		<form method="post" action="<?php echo esc_url( vvHostApplications::form_url() ); ?>">
			<input type="hidden" name="vv_action" value="vv_host_application_step">
			<input type="hidden" name="form_step" value="<?php echo intval( $current_step ); ?>">

			<h3 class="host-app-step-title">Share some basics about your place</h3>
			<p class="host-app-step-desc">How many guests can you host? Add bedroom, bed, and bathroom counts.</p>

			<div class="host-app-counter-grid">
				<div class="form-group">
					<label>Guests <span class="required">*</span></label>
					<input type="number" name="property_max_guests" min="1" max="30" value="<?php echo esc_attr( gArrayItem( $app_data, 'property_max_guests', '2' ) ); ?>" required>
				</div>
				<div class="form-group">
					<label>Bedrooms</label>
					<input type="number" name="property_rooms" min="0" max="20" value="<?php echo esc_attr( gArrayItem( $app_data, 'property_rooms', '1' ) ); ?>">
				</div>
				<div class="form-group">
					<label>Beds <span class="required">*</span></label>
					<input type="number" name="property_beds" min="1" max="30" value="<?php echo esc_attr( gArrayItem( $app_data, 'property_beds', '1' ) ); ?>" required>
				</div>
				<div class="form-group">
					<label>Bathrooms <span class="required">*</span></label>
					<input type="number" name="property_bathrooms" min="1" max="20" step="1" value="<?php echo esc_attr( gArrayItem( $app_data, 'property_bathrooms', '1' ) ); ?>" required>
				</div>
			</div>

			<div class="host-app-actions">
				<a href="<?php echo esc_url( add_query_arg( 'step', $back_step, vvHostApplications::form_url() ) ); ?>" class="btn btn-outline">Back</a>
				<button type="submit" class="btn">Continue</button>
			</div>
		</form>

	<?php } elseif ( $step_kind === 'amenities' ) { ?>
		<form method="post" action="<?php echo esc_url( vvHostApplications::form_url() ); ?>">
			<input type="hidden" name="vv_action" value="vv_host_application_step">
			<input type="hidden" name="form_step" value="<?php echo intval( $current_step ); ?>">

			<h3 class="host-app-step-title">Tell guests what your place has to offer</h3>
			<p class="host-app-step-desc">Select the amenities you provide. You can add more after approval.</p>

			<?php if ( is_array( $facility_options ) && count( $facility_options ) > 0 ) { ?>
				<div class="host-app-check-grid">
					<?php foreach ( $facility_options as $facility ) {
						$fid = intval( gArrayItem( $facility, 'facility_id' ) );
						if ( $fid <= 0 ) {
							continue;
						}
						?>
						<label class="host-app-check">
							<input type="checkbox" name="property_amenities[]" value="<?php echo esc_attr( $fid ); ?>" <?php checked( in_array( $fid, $selected_amenities, true ) ); ?>>
							<?php echo esc_html( gArrayItem( $facility, 'name' ) ); ?>
						</label>
					<?php } ?>
				</div>
			<?php } ?>

			<div class="host-app-actions">
				<a href="<?php echo esc_url( add_query_arg( 'step', $back_step, vvHostApplications::form_url() ) ); ?>" class="btn btn-outline">Back</a>
				<button type="submit" class="btn">Continue</button>
			</div>
		</form>

	<?php } elseif ( $step_kind === 'photos' ) { ?>
		<form method="post" action="<?php echo esc_url( vvHostApplications::form_url() ); ?>" enctype="multipart/form-data">
			<input type="hidden" name="vv_action" value="vv_host_application_step">
			<input type="hidden" name="form_step" value="<?php echo intval( $current_step ); ?>">

			<h3 class="host-app-step-title">Add some photos of your place</h3>
			<p class="host-app-step-desc">You'll need at least one photo to continue. Bright, wide shots of each room work best.</p>

			<?php if ( is_array( $property_images ) && count( $property_images ) > 0 ) { ?>
				<div class="host-app-photo-preview">
					<?php foreach ( array_slice( $property_images, 0, 6 ) as $img ) {
						$thumb = gArrayItem( $img, 'thumb' );
						if ( $thumb === '' ) {
							continue;
						}
						?>
						<img src="<?php echo esc_url( $thumb ); ?>" alt="">
					<?php } ?>
					<?php if ( count( $property_images ) > 6 ) { ?>
						<span class="host-app-photo-more">+<?php echo count( $property_images ) - 6; ?> more</span>
					<?php } ?>
				</div>
				<p class="host-app-hint"><?php echo count( $property_images ); ?> photo(s) uploaded. Add more below if you like.</p>
			<?php } ?>

			<div class="form-group">
				<input type="file" name="property_images[]" accept="image/*" multiple <?php echo ( ! is_array( $property_images ) || count( $property_images ) === 0 ) ? 'required' : ''; ?>>
				<small class="host-app-hint d-block mt-1">JPEG or PNG, up to 8 images.</small>
			</div>

			<div class="host-app-actions">
				<a href="<?php echo esc_url( add_query_arg( 'step', $back_step, vvHostApplications::form_url() ) ); ?>" class="btn btn-outline">Back</a>
				<button type="submit" class="btn">Continue</button>
			</div>
		</form>

	<?php } elseif ( $step_kind === 'listing' ) { ?>
		<form method="post" action="<?php echo esc_url( vvHostApplications::form_url() ); ?>">
			<input type="hidden" name="vv_action" value="vv_host_application_step">
			<input type="hidden" name="form_step" value="<?php echo intval( $current_step ); ?>">

			<h3 class="host-app-step-title">Make your place stand out</h3>
			<p class="host-app-step-desc">Give your listing a short title and a description that highlights what makes it special.</p>

			<div class="form-group">
				<label>Listing title <span class="required">*</span></label>
				<input type="text" name="property_name" maxlength="80" value="<?php echo esc_attr( gArrayItem( $app_data, 'property_name' ) ); ?>" required placeholder="e.g. Bright 2BR with balcony near Ben Thanh">
				<small class="host-app-hint">Short and specific works best (max 80 characters).</small>
			</div>

			<div class="form-group">
				<label>Description <span class="required">*</span></label>
				<textarea name="property_description" rows="6" required placeholder="Describe the layout, neighbourhood, and what guests will love about staying here."><?php echo esc_textarea( gArrayItem( $app_data, 'property_description' ) ); ?></textarea>
			</div>

			<div class="host-app-actions">
				<a href="<?php echo esc_url( add_query_arg( 'step', $back_step, vvHostApplications::form_url() ) ); ?>" class="btn btn-outline">Back</a>
				<button type="submit" class="btn">Continue</button>
			</div>
		</form>

	<?php } elseif ( $step_kind === 'pricing' ) { ?>
		<form method="post" action="<?php echo esc_url( vvHostApplications::form_url() ); ?>">
			<input type="hidden" name="vv_action" value="vv_host_application_step">
			<input type="hidden" name="form_step" value="<?php echo intval( $current_step ); ?>">

			<h3 class="host-app-step-title">Now, set your price</h3>
			<p class="host-app-step-desc">Choose a nightly rate. You can change this anytime after your listing goes live.</p>

			<div class="form-group host-app-price-field">
				<label>Nightly price (<?php echo esc_html( $currency ); ?>) <span class="required">*</span></label>
				<input type="text" name="property_price_daily" inputmode="decimal" value="<?php echo esc_attr( gArrayItem( $app_data, 'property_price_daily' ) ); ?>" required placeholder="e.g. 45">
			</div>

			<div class="host-app-info-box">
				Your listing stays hidden until Vietstays approves your host application. The price you set here will be used when it goes live.
			</div>

			<div class="host-app-actions">
				<a href="<?php echo esc_url( add_query_arg( 'step', $back_step, vvHostApplications::form_url() ) ); ?>" class="btn btn-outline">Back</a>
				<button type="submit" class="btn">Continue</button>
			</div>
		</form>

	<?php } elseif ( $step_kind === 'contact' ) { ?>
		<form method="post" action="<?php echo esc_url( vvHostApplications::form_url() ); ?>">
			<input type="hidden" name="vv_action" value="vv_host_application_step">
			<input type="hidden" name="form_step" value="<?php echo intval( $current_step ); ?>">

			<h3 class="host-app-step-title"><?php echo ( $applicant_type === 'single_property' ) ? 'Tell us about yourself' : 'Contact &amp; manager information'; ?></h3>
			<p class="host-app-step-desc"><?php echo ( $applicant_type === 'single_property' ) ? 'Guests like to know who they\'re booking with. This is your host profile for review.' : 'Tell us about you and the portfolio you manage.'; ?></p>

			<div class="row">
				<div class="col-md-6">
					<div class="form-group">
						<label>Full name <span class="required">*</span></label>
						<input type="text" name="full_name" value="<?php echo esc_attr( gArrayItem( $app_data, 'full_name' ) ); ?>" required>
					</div>
				</div>
				<div class="col-md-6">
					<div class="form-group">
						<label>Email <span class="required">*</span></label>
						<input type="email" name="email" value="<?php echo esc_attr( gArrayItem( $app_data, 'email' ) ); ?>" required>
					</div>
				</div>
			</div>

			<div class="row">
				<div class="col-md-6">
					<div class="form-group">
						<label>Phone <span class="required">*</span></label>
						<input type="tel" name="phone" value="<?php echo esc_attr( gArrayItem( $app_data, 'phone' ) ); ?>" required>
					</div>
				</div>
				<?php if ( $applicant_type !== 'single_property' ) { ?>
					<div class="col-md-6">
						<div class="form-group">
							<label>Number of apartments <span class="required">*</span></label>
							<input type="number" name="num_properties" min="1" value="<?php echo esc_attr( gArrayItem( $app_data, 'num_properties', '1' ) ); ?>" required>
						</div>
					</div>
				<?php } ?>
			</div>

			<?php if ( $applicant_type !== 'single_property' ) { ?>
				<div class="form-group">
					<label>Primary city <span class="required">*</span></label>
					<select name="primary_city_id" id="host_app_primary_city" required>
						<option value="">Select city</option>
						<?php foreach ( $city_options as $city_id => $city_name ) { ?>
							<option value="<?php echo esc_attr( $city_id ); ?>" <?php selected( $primary_city_id, $city_id ); ?>><?php echo esc_html( $city_name ); ?></option>
						<?php } ?>
					</select>
				</div>

				<div class="form-group" id="host_app_districts_wrap">
					<label>Districts within your city <span class="required">*</span></label>
					<?php if ( count( $all_districts ) === 0 ) { ?>
						<p class="host-app-hint mb-0">No districts configured yet.</p>
					<?php } else { ?>
						<p class="host-app-hint host-app-districts-placeholder" id="host_app_districts_placeholder">Select a primary city to choose districts.</p>
						<div class="host-app-check-grid" id="host_app_districts_list"></div>
						<p class="host-app-hint host-app-districts-empty" id="host_app_districts_empty" style="display:none;">No districts for this city.</p>
						<script type="application/json" id="host_app_districts_data"><?php echo wp_json_encode( $districts_by_city ); ?></script>
						<script type="application/json" id="host_app_districts_selected"><?php echo wp_json_encode( array_values( $selected_districts ) ); ?></script>
					<?php } ?>
				</div>
			<?php } ?>

			<div class="row">
				<div class="col-md-6">
					<div class="form-group">
						<label>Company / brand <small>(optional)</small></label>
						<input type="text" name="company_name" value="<?php echo esc_attr( gArrayItem( $app_data, 'company_name' ) ); ?>">
					</div>
				</div>
				<div class="col-md-6">
					<div class="form-group">
						<label>Portfolio link <small>(optional)</small></label>
						<input type="url" name="portfolio_url" placeholder="https://" value="<?php echo esc_attr( gArrayItem( $app_data, 'portfolio_url' ) ); ?>">
					</div>
				</div>
			</div>

			<div class="form-group">
				<label><?php echo ( $applicant_type === 'single_property' ) ? 'About you as a host' : 'Portfolio description'; ?> <span class="required">*</span></label>
				<textarea name="description" rows="4" required><?php echo esc_textarea( gArrayItem( $app_data, 'description' ) ); ?></textarea>
			</div>

			<div class="host-app-actions">
				<a href="<?php echo esc_url( add_query_arg( 'step', $back_step, vvHostApplications::form_url() ) ); ?>" class="btn btn-outline">Back</a>
				<button type="submit" class="btn">Continue</button>
			</div>

			<?php if ( $applicant_type !== 'single_property' && count( $all_districts ) > 0 ) { ?>
			<script>
			(function () {
				var citySelect = document.getElementById('host_app_primary_city');
				var districtList = document.getElementById('host_app_districts_list');
				var placeholder = document.getElementById('host_app_districts_placeholder');
				var emptyNotice = document.getElementById('host_app_districts_empty');
				var dataEl = document.getElementById('host_app_districts_data');
				var selectedEl = document.getElementById('host_app_districts_selected');
				if (!citySelect || !districtList || !dataEl) return;
				var districtsByCity = {}, selectedDistricts = [];
				try { districtsByCity = JSON.parse(dataEl.textContent || '{}'); } catch (e) {}
				if (selectedEl) { try { selectedDistricts = JSON.parse(selectedEl.textContent || '[]'); } catch (e) {} }
				function isSelected(id) {
					id = parseInt(id, 10);
					for (var i = 0; i < selectedDistricts.length; i++) {
						if (parseInt(selectedDistricts[i], 10) === id) return true;
					}
					return false;
				}
				function renderDistricts() {
					var cityId = String(citySelect.value || '');
					var items = districtsByCity[cityId] || [];
					var html = '';
					for (var i = 0; i < items.length; i++) {
						var checked = isSelected(items[i].id) ? ' checked' : '';
						html += '<label class="host-app-check"><input type="checkbox" name="districts[]" value="' + parseInt(items[i].id, 10) + '"' + checked + '>' + items[i].label + '</label>';
					}
					districtList.innerHTML = html;
					if (!cityId) {
						if (placeholder) placeholder.style.display = '';
						districtList.style.display = 'none';
						if (emptyNotice) emptyNotice.style.display = 'none';
						return;
					}
					if (placeholder) placeholder.style.display = 'none';
					if (items.length > 0) {
						districtList.style.display = '';
						if (emptyNotice) emptyNotice.style.display = 'none';
					} else {
						districtList.style.display = 'none';
						if (emptyNotice) emptyNotice.style.display = '';
					}
				}
				citySelect.addEventListener('change', renderDistricts);
				renderDistricts();
			})();
			</script>
			<?php } ?>
		</form>

	<?php } elseif ( $step_kind === 'experience' ) { ?>
		<form method="post" action="<?php echo esc_url( vvHostApplications::form_url() ); ?>">
			<input type="hidden" name="vv_action" value="vv_host_application_step">
			<input type="hidden" name="form_step" value="<?php echo intval( $current_step ); ?>">

			<h3 class="host-app-step-title">Experience &amp; fit</h3>
			<p class="host-app-step-desc">Optional — helps our team review your application.</p>

			<div class="host-app-info-box">Vietstays reviews every host application manually.</div>

			<div class="form-group">
				<label>Years managing short-term rentals</label>
				<select name="years_managing">
					<?php foreach ( $years_options as $value => $label ) { ?>
						<option value="<?php echo esc_attr( $value ); ?>" <?php selected( gArrayItem( $app_data, 'years_managing' ), $value ); ?>><?php echo esc_html( $label ); ?></option>
					<?php } ?>
				</select>
			</div>

			<div class="form-group">
				<label>Typical guest profile</label>
				<select name="guest_profile">
					<?php foreach ( $guest_profile_opts as $value => $label ) { ?>
						<option value="<?php echo esc_attr( $value ); ?>" <?php selected( gArrayItem( $app_data, 'guest_profile' ), $value ); ?>><?php echo esc_html( $label ); ?></option>
					<?php } ?>
				</select>
			</div>

			<div class="form-group">
				<label>Platforms you use today</label>
				<div class="host-app-check-grid">
					<?php foreach ( $platform_options as $value => $label ) { ?>
						<label class="host-app-check">
							<input type="checkbox" name="platforms_used[]" value="<?php echo esc_attr( $value ); ?>" <?php checked( in_array( $value, $selected_platforms, true ) ); ?>>
							<?php echo esc_html( $label ); ?>
						</label>
					<?php } ?>
				</div>
			</div>

			<div class="host-app-actions">
				<a href="<?php echo esc_url( add_query_arg( 'step', $back_step, vvHostApplications::form_url() ) ); ?>" class="btn btn-outline">Back</a>
				<button type="submit" class="btn">Continue</button>
			</div>
		</form>

	<?php } elseif ( $step_kind === 'review' ) { ?>
		<form method="post" action="<?php echo esc_url( vvHostApplications::form_url() ); ?>">
			<input type="hidden" name="vv_action" value="vv_host_application_submit">

			<h3 class="host-app-step-title">Review and submit</h3>
			<p class="host-app-step-desc">Check everything looks right, then send your application for review.</p>

			<div class="host-app-summary">
				<dl>
					<?php if ( $applicant_type === 'single_property' ) { ?>
						<dt>Guest access</dt>
						<dd><?php echo esc_html( vvHostApplications::space_type_label( gArrayItem( $app_data, 'property_space_type', gArrayItem( $app_data, 'property_type' ) ) ) ); ?></dd>
						<dt>Listing title</dt>
						<dd><?php echo esc_html( gArrayItem( $app_data, 'property_name' ) ); ?></dd>
						<dt>Address</dt>
						<dd><?php echo esc_html( gArrayItem( $app_data, 'property_address' ) ); ?></dd>
						<dt>Capacity</dt>
						<dd><?php echo intval( gArrayItem( $app_data, 'property_max_guests' ) ); ?> guests · <?php echo intval( gArrayItem( $app_data, 'property_rooms' ) ); ?> bedrooms · <?php echo intval( gArrayItem( $app_data, 'property_beds' ) ); ?> beds · <?php echo intval( gArrayItem( $app_data, 'property_bathrooms' ) ); ?> baths</dd>
						<dt>Nightly price</dt>
						<dd><?php echo esc_html( $currency . ' ' . gArrayItem( $app_data, 'property_price_daily' ) ); ?></dd>
						<dt>Photos</dt>
						<dd><?php echo is_array( $property_images ) ? count( $property_images ) : 0; ?> uploaded</dd>
					<?php } else { ?>
						<dt>Host type</dt>
						<dd>Multiple apartments</dd>
					<?php } ?>

					<dt>Full name</dt>
					<dd><?php echo esc_html( gArrayItem( $app_data, 'full_name' ) ); ?></dd>
					<dt>Email</dt>
					<dd><?php echo esc_html( gArrayItem( $app_data, 'email' ) ); ?></dd>
					<dt>Phone</dt>
					<dd><?php echo esc_html( gArrayItem( $app_data, 'phone' ) ); ?></dd>

					<?php if ( $applicant_type !== 'single_property' ) { ?>
						<dt>Primary city</dt>
						<dd><?php echo esc_html( gArrayItem( $app_data, 'home_city' ) ); ?></dd>
						<dt>Apartments managed</dt>
						<dd><?php echo intval( gArrayItem( $app_data, 'num_properties' ) ); ?></dd>
						<dt>Districts</dt>
						<dd><?php echo esc_html( vvHostApplications::format_district_labels( (array) gArrayItem( $app_data, 'districts', [] ) ) ); ?></dd>
					<?php } ?>

					<dt>About the host</dt>
					<dd><?php echo nl2br( esc_html( gArrayItem( $app_data, 'description' ) ) ); ?></dd>
				</dl>
			</div>

			<div class="form-group host-app-confirm">
				<label class="host-app-check">
					<input type="checkbox" name="confirm_application" value="1" required>
					<?php if ( $applicant_type === 'single_property' ) { ?>
						I understand my listing stays hidden until Vietstays approves my host application, then it goes live at the price I set.
					<?php } else { ?>
						I understand this is an application and listings are created only after approval.
					<?php } ?>
				</label>
			</div>

			<div class="host-app-actions">
				<a href="<?php echo esc_url( add_query_arg( 'step', $back_step, vvHostApplications::form_url() ) ); ?>" class="btn btn-outline">Back</a>
				<button type="submit" class="btn">Submit application</button>
			</div>
		</form>
	<?php } ?>

</div>
