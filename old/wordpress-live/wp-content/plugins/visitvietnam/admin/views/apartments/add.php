<?php
if ( ! class_exists( 'vvApartmentsWizard' ) || ! vvApartmentsWizard::can_use_wizard() ) {
	echo '<div class="alert alert-danger">' . esc_html( vv__( 'You do not have access to this page.' ) ) . '</div>';
	return;
}

$step         = max( 1, min( 4, intval( GET_Request( 'step' ) ) ) );
$apartment_id = intval( GET_Request( 'id' ) );
$apartment    = [];

if ( $apartment_id > 0 ) {
	$apartment = vv_get_apartment( $apartment_id );
	if ( ! vvApartmentsWizard::can_access_apartment( $apartment ) ) {
		echo '<div class="alert alert-danger">' . esc_html( vv__( 'Permission denied.' ) ) . '</div>';
		return;
	}
} elseif ( $step > 1 ) {
	wp_redirect( vv_admin_url( 'apartments/add?step=1' ) );
	exit;
}

$step_labels = [
	1 => vv__( 'Building & type' ),
	2 => vv__( 'Photos' ),
	3 => vv__( 'Facilities' ),
	4 => vv__( 'Confirm' ),
];

$next_labels = [
	1 => vv__( 'Next: Photos' ) . ' →',
	2 => vv__( 'Next: Facilities' ) . ' →',
	3 => vv__( 'Next: Confirm' ) . ' →',
];

$back_labels = [
	2 => '← ' . vv__( 'Building & type' ),
	3 => '← ' . vv__( 'Photos' ),
	4 => '← ' . vv__( 'Facilities' ),
];
if ( $step === 2 && ! empty( $apartment ) ) {
	$step2_count = vv_count_listing_photos( $apartment );
}

ob_start();
?>
<link rel="stylesheet" href="<?php echo esc_url( vv_plugins_url() . 'admin/css/apartments-wizard.css?v=2026mock7' ); ?>" media="all">
<script>document.documentElement.classList.add('vv-apt2026-wizard-active');</script>
<?php
$header_codes .= ob_get_clean();

ob_start();
?>
<script>document.body.classList.add('vv-apt2026-wizard-active');</script>
<?php
$footer_codes .= ob_get_clean();
?>
<div class="vv-apt2026-overlay">
    <div class="vv-apt2026-modal">
        <div class="vv-apt2026-modal__header">
            <h2><?php vv_e( 'Add New Apartment' ); ?></h2>
            <a href="<?php echo esc_url( vv_admin_url( 'apartments' ) ); ?>" class="vv-apt2026-modal__close" title="<?php echo esc_attr( vv__( 'Close' ) ); ?>">&times;</a>
        </div>

        <nav class="vv-apt2026-step-tabs">
            <?php foreach ( $step_labels as $num => $label ) {
                $cls = '';
                $href = vv_admin_url( 'apartments/add?step=' . $num . ( $apartment_id ? '&id=' . $apartment_id : '' ) );
                if ( $num === $step ) {
                    $cls = 'is-active';
                } elseif ( $num < $step && ( $num === 1 || $apartment_id > 0 ) ) {
                    $cls = 'is-done';
                } else {
                    $cls = 'is-future';
                }
                if ( $num <= $step && ( $num === 1 || $apartment_id > 0 ) ) {
                    echo '<a href="' . esc_url( $href ) . '" class="' . esc_attr( $cls ) . '"><span class="num">' . intval( $num ) . '</span> ' . esc_html( $label ) . '</a>';
                } else {
                    echo '<span class="' . esc_attr( $cls ) . '"><span class="num">' . intval( $num ) . '</span> ' . esc_html( $label ) . '</span>';
                }
            } ?>
        </nav>

        <div class="vv-apt2026-body">
            <?php
            if ( $step === 1 ) {
                include __DIR__ . '/_add-step1.php';
            } elseif ( $step === 2 ) {
                include __DIR__ . '/_add-step2.php';
            } elseif ( $step === 3 ) {
                include __DIR__ . '/_add-step3.php';
            } else {
                include __DIR__ . '/_add-step4.php';
            }
            ?>
        </div>

        <div class="vv-apt2026-footer<?php echo $step === 1 ? ' vv-apt2026-footer--first' : ''; ?>">
            <div class="vv-apt2026-footer-left">
                <?php if ( $step > 1 && $apartment_id > 0 ) { ?>
                    <a href="<?php echo esc_url( vv_admin_url( 'apartments/add?step=' . ( $step - 1 ) . '&id=' . $apartment_id ) ); ?>" class="vv-apt2026-btn-back"><?php echo esc_html( gArrayItem( $back_labels, $step, '← ' . vv__( 'Back' ) ) ); ?></a>
                <?php } ?>
            </div>
            <a href="<?php echo esc_url( vv_admin_url( 'apartments' ) ); ?>" class="vv-apt2026-cancel"><?php vv_e( 'Cancel' ); ?></a>
            <div class="vv-apt2026-footer-right">
                <?php if ( $step === 1 ) { ?>
                    <p class="vv-apt2026-footer-hint" id="apt2026StepHint"><?php vv_e( 'Fill in required fields to proceed.' ); ?></p>
                <?php } elseif ( $step === 2 ) {
                    $step2_images = vv_get_apartment_images( $apartment );
                    $step2_count  = count( $step2_images );
                    $step2_publish_min = 10;
                    if ( class_exists( 'vvApartmentPlatform' ) ) {
                        $step2_publish_min = intval( vvApartmentPlatform::get_settings()['min_images_publish'] );
                    }
                    $listing_count = function_exists( 'vv_count_listing_photos' ) ? vv_count_listing_photos( $apartment ) : $step2_count;
                    $step2_wizard_min = 1;
                    ?>
                    <p class="vv-apt2026-footer-hint" id="apt2026Step2Hint">
                        <?php
                        if ( $listing_count < $step2_wizard_min ) {
                            vv_e( 'Upload at least 1 image to proceed' );
                        }
                        ?>
                    </p>
                <?php } ?>
                <?php if ( $step < 4 ) {
                    $step2_listing = ( $step === 2 && ! empty( $apartment ) ) ? ( function_exists( 'vv_count_listing_photos' ) ? vv_count_listing_photos( $apartment ) : count( vv_get_apartment_images( $apartment ) ) ) : 0;
                    $step2_wizard_min = 1;
                    $next_disabled = ( $step === 1 ) || ( $step === 2 && $step2_listing < $step2_wizard_min );
                    ?>
                    <button type="submit" form="vvApt2026Step<?php echo intval( $step ); ?>" class="vv-apt2026-btn-next" id="apt2026NextBtn"<?php echo $next_disabled ? ' disabled' : ''; ?>>
                        <?php echo esc_html( gArrayItem( $next_labels, $step, vv__( 'Next' ) . ' →' ) ); ?>
                    </button>
                <?php } else { ?>
                    <button type="submit" form="vvApt2026Step4" class="vv-apt2026-btn-next is-success">
                        ✓ <?php vv_e( 'Create Apartment and Go to Price Management' ); ?>
                    </button>
                <?php } ?>
            </div>
        </div>
    </div>
</div>

<?php
ob_start();
?>
<script>
jQuery(function($){
    var minImages = <?php echo intval( class_exists( 'vvApartmentPlatform' ) ? vvApartmentPlatform::get_settings()['min_images_publish'] : 10 ); ?>;
    var aptId = <?php echo intval( $apartment_id ); ?>;
    var buildingId = <?php echo intval( gArrayItem( $apartment, 'building_id' ) ); ?>;
    var currentStep = <?php echo intval( $step ); ?>;
    var nameEdited = aptId > 0;

    function markFilled($el){
        $el.toggleClass('is-filled', $.trim($el.val()) !== '');
    }
    $('.vv-apt2026-field').each(function(){ markFilled($(this)); }).on('input change', function(){ markFilled($(this)); });

    function updateNamePreview(){
        var $buildingOpt = $('#apt2026_building_id option:selected');
        var bName = $buildingOpt.attr('data-name') || '';
        var district = $buildingOpt.attr('data-district-label') || '';
        var feature = $.trim($('#apt2026_feature').val());
        var type = $('input[name="apartment_type"]:checked').val() || '';
        var parts = [];
        if (bName) parts.push(bName);
        if (feature) parts.push(feature);
        var name = parts.join('–');
        if (district) name += (name ? ' · ' : '') + district;
        if (type) name += (name ? ' · ' : '') + type;
        if (name.length > 80) name = name.substring(0, 80);
        if (name) {
            $('#apt2026_name_preview').text('<?php echo esc_js( vv__( 'Apartment name:' ) ); ?> ' + name);
        } else {
            $('#apt2026_name_preview').text('');
        }
        if (!nameEdited) {
            $('#apt2026_name').val(name);
        }
        validateStep1();
    }

    $('#apt2026_name').on('input', function(){ nameEdited = true; validateStep1(); });

    function updateFacilityCounter(){
        var total = $('.apt2026-facility-cb').length;
        var checked = $('.apt2026-facility-cb:checked').length;
        $('#apt2026FacilityCounterApt').text(checked + ' / ' + total + ' <?php echo esc_js( vv__( 'facilities' ) ); ?>');
    }
    $(document).on('change', '.apt2026-facility-cb', function(){
        $(this).closest('.vv-apt2026-facility-item--apt').toggleClass('is-checked', this.checked);
        updateFacilityCounter();
    });

    $('a[href="#apt2026FacApt"]').on('shown.bs.tab', function(){
        $('#apt2026FacilityCounterApt').removeClass('d-none');
        $('#apt2026FacilityCounterBld').addClass('d-none');
    });
    $('a[href="#apt2026FacBld"]').on('shown.bs.tab', function(){
        $('#apt2026FacilityCounterApt').addClass('d-none');
        $('#apt2026FacilityCounterBld').removeClass('d-none');
    });

    function validateStep1(){
        if (currentStep !== 1) return;
        var buildingIdVal = parseInt($('#apt2026_building_id').val(), 10);
        if (isNaN(buildingIdVal)) buildingIdVal = 0;
        var hasType = $('input[name="apartment_type"]:checked').length > 0;
        var hasFeature = $.trim($('#apt2026_feature').val()) !== '';
        var hasName = $.trim($('#apt2026_name').val()) !== '';
        var ok = buildingIdVal > 0 && hasType && hasFeature && hasName;
        $('#apt2026NextBtn').prop('disabled', !ok);
        $('#apt2026StepHint').toggle(!ok);
    }

    function showLocalePane(loc){ /* single-locale step 1 */ }

    function updateCharCount($ta){
        var n = ($ta.val() || '').length;
        $ta.closest('.vv-apt2026-field-group, .apt2026-locale-pane').find('.apt2026-char-num').text(n);
    }

    $(document).on('click', '#apt2026LocaleTabs .vv-apt2026-locale-tab', function(e){
        e.preventDefault();
    });

    if (currentStep === 1) {
        var $cascade = $('.vv-apt2026-location-cascade');
        var cascadeUrl = $cascade.attr('data-cascade-url') || '';
        var selectedDistrict = parseInt($cascade.attr('data-selected-district'), 10) || 0;
        var selectedBuilding = parseInt($cascade.attr('data-selected-building'), 10) || 0;

        function fillSelect($sel, items, placeholder, selectedId){
            $sel.empty().append($('<option>').val('').text(placeholder));
            (items || []).forEach(function(item){
                var $opt = $('<option>').val(item.id).text(item.name);
                if (item.location_label) $opt.attr('data-location', item.location_label);
                if (item.name) $opt.attr('data-name', item.name);
                if (item.district_label) $opt.attr('data-district-label', item.district_label);
                if (parseInt(item.id, 10) === selectedId) $opt.prop('selected', true);
                $sel.append($opt);
            });
        }

        function loadDistricts(cityId, pickDistrict){
            var $district = $('#apt2026_district_id');
            var $building = $('#apt2026_building_id');
            $district.prop('disabled', true).empty().append($('<option>').val('').text('<?php echo esc_js( vv__( 'Select district…' ) ); ?>'));
            $building.prop('disabled', true).empty().append($('<option>').val('').text('<?php echo esc_js( vv__( 'Choose building…' ) ); ?>'));
            if (!cityId) return $.Deferred().resolve().promise();
            return $.getJSON(cascadeUrl, { city_id: cityId }).done(function(res){
                fillSelect($district, res.districts || [], '<?php echo esc_js( vv__( 'Select district…' ) ); ?>', pickDistrict || 0);
                $district.prop('disabled', false);
            });
        }

        function loadBuildings(districtId, pickBuilding){
            var $building = $('#apt2026_building_id');
            $building.prop('disabled', true).empty().append($('<option>').val('').text('<?php echo esc_js( vv__( 'Choose building…' ) ); ?>'));
            if (!districtId) return $.Deferred().resolve().promise();
            return $.getJSON(cascadeUrl, { district_id: districtId }).done(function(res){
                fillSelect($building, res.buildings || [], '<?php echo esc_js( vv__( 'Choose building…' ) ); ?>', pickBuilding || 0);
                $building.prop('disabled', false);
                if (pickBuilding) {
                    var loc = $building.find('option:selected').attr('data-location') || '';
                    $('#apt2026BuildingLocationHint').text(loc).toggle(loc !== '');
                }
            });
        }

        $('#apt2026_city_id').on('change', function(){
            var cityId = parseInt($(this).val(), 10) || 0;
            $('#apt2026_request_city_id').val(cityId);
            $('#apt2026_request_district_id').val('');
            loadDistricts(cityId, 0);
            validateStep1();
            updateNamePreview();
        });

        $('#apt2026_district_id').on('change', function(){
            var districtId = parseInt($(this).val(), 10) || 0;
            $('#apt2026_request_district_id').val(districtId);
            loadBuildings(districtId, 0);
            validateStep1();
            updateNamePreview();
        });

        $('#apt2026ToggleBuildingRequest').on('click', function(){
            $('#apt2026BuildingRequest').slideToggle(150);
        });

        if (parseInt($('#apt2026_city_id').val(), 10) > 0 && selectedDistrict > 0) {
            loadDistricts(parseInt($('#apt2026_city_id').val(), 10), selectedDistrict).done(function(){
                loadBuildings(selectedDistrict, selectedBuilding).done(function(){
                    validateStep1();
                    updateNamePreview();
                });
            });
        }

        $('#vvApt2026Step1').on('input change', 'input, select, textarea', function(){
            if (this.id === 'apt2026_name') return;
            if (this.id === 'apt2026_building_id') {
                var loc = $('#apt2026_building_id option:selected').attr('data-location') || '';
                $('#apt2026BuildingLocationHint').text(loc).toggle(loc !== '');
            }
            if (this.id === 'apt2026_building_id' || this.id === 'apt2026_feature' || this.name === 'apartment_type') {
                updateNamePreview();
            } else {
                validateStep1();
            }
        });
        $('.apt2026-short-desc').on('input', function(){ updateCharCount($(this)); }).each(function(){ updateCharCount($(this)); });
        updateNamePreview();
        validateStep1();
    } else {
        $('.apt2026-short-desc').on('input', function(){ updateCharCount($(this)); }).each(function(){ updateCharCount($(this)); });
    }

    if (currentStep === 3) {
        updateFacilityCounter();
    }
});
</script>
<?php
$footer_codes .= ob_get_clean();

if ( $step === 2 && $apartment_id > 0 ) {
	ob_start();
	$min_images            = class_exists( 'vvApartmentPlatform' ) ? intval( vvApartmentPlatform::get_settings()['min_images_publish'] ) : 10;
	$wizard_min_images     = 1;
	$require_min_on_save   = true;
	$building_id           = intval( gArrayItem( $apartment, 'building_id' ) );
	$images_wrap_id        = 'apt2026ImagesWrap';
	$images_form_id        = 'vvApt2026Step2';
	$disable_next_selector = '#apt2026NextBtn';
	include __DIR__ . '/_apt-images-script.php';
	$footer_codes .= ob_get_clean();
}
