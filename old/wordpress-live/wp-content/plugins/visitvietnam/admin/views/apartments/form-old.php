<link rel="stylesheet" href="//code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
<link type="text/css" rel="stylesheet" href="<?php echo vv_plugins_url() ?>admin/css/bootstrap-datetimepicker.min.css">

<style type="text/css">
    .form-group label{ font-weight:bold }

    a.apartment_img-thumb > div {
        display: inline-block;
        width: 150px;
        height: 150px;
        background-repeat: no-repeat;
        background-size: contain;
        background-position-x: 50%;
        background-position-y: 50%;
    }

.tab-vertical .nav.nav-tabs {
    float: left;
    display: block;
    margin-right: 0px;
    border-bottom: 0;
}

.tab-vertical .nav.nav-tabs .nav-item {
    margin-bottom: 6px;
}

.tab-vertical .nav-tabs .nav-link {
    border: 1px solid transparent;
    border-top-left-radius: .25rem;
    border-top-right-radius: .25rem;
    background: #fff;
    padding: 17px 25px;
    color: #fff;
    background-color: #004041;
    -webkit-border-radius: 4px 0px 0px 4px;
    -moz-border-radius: 4px 0px 0px 4px;
    border-radius: 4px 0px 0px 4px;
}

.tab-vertical .nav-tabs .nav-link.active {
    color: #004041;
    background-color: #fff !important;
    border-color: transparent !important;
}

.tab-vertical .nav-tabs .nav-link {
    border: 1px solid transparent;
    border-top-left-radius: 4px !important;
    border-top-right-radius: 0px !important;
}

.tab-vertical .tab-content {
    overflow: auto;
    -webkit-border-radius: 0px 4px 4px 4px;
    -moz-border-radius: 0px 4px 4px 4px;
    border-radius: 0px 4px 4px 4px;
    background: #fff;
    padding: 30px;
    min-height:500px;
}


.tabs .tab-content{
    background-color: #fff;
    padding:20px;
    font-size:13px;
}
.tabs .tab-content .tab-inner-content{
    max-width: 900px;
}
.tabs .nav-tabs .nav-item{
    background-color: #004041;
    border-radius: 0;
    margin-right:5px;
}
.tabs .nav-tabs .nav-item a{
    color:#fff;
    border-radius: 0;
    font-size: 14px;
}
.tabs .nav-tabs .nav-item a.active{
    color:#004041;
}
#imagesTable{
    list-style: none;
    padding: 0;
    margin: 0;
}
#imagesTable li{
    display:inline-block;
    max-width:350px;
    margin:5px;
}

.ui-sortable-handle{ cursor:all-scroll; }
.ui-state-highlight{ min-height:150px; background-color:#FD780E; width:350px;}

</style>
<?php
global $wpdb;

$apartment_id           = gArrayItem($apartment,'ID');
$districts              = vv_get_districts();
$facilities             = vv_get_facilities();
$security_features      = vv_get_security_features();
$cleaners_checklists    = vv_get_cleaners_checklists();
include __DIR__ . '/_platform-init.php';
//echo print_r_pre($districts);

//echo print_r_pre($apartment);

$pricing = json_decode(gArrayItem($apartment,'pricing'),true);
if(!is_array($pricing)) $pricing = array();

$daysofweek = ['sun','mon','tue','wed','thu','fri','sat'];

$url_slug = gArrayItem($apartment,'url_slug');
if($url_slug == '') $url_slug = gArrayItem($apartment,'ID');




?>
<link rel="stylesheet" href="<?php echo vv_plugins_url() ?>/admin/plugins/ekko-lightbox/ekko-lightbox.css">
<link rel="stylesheet" href="<?php echo vv_plugins_url() ?>/admin/plugins/dropzone/min/dropzone.min.css">


<form method="post" id="formApartment" enctype="multipart/form-data">
    <input type="hidden" name="action" value="vv_save_apartment">
    <input type="hidden" name="apartment_id" value="<?php echo intval(gArrayItem($apartment,'ID')) ?>" >



        <div class="tabs">
            <ul class="nav nav-tabs" id="productTab" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="information-tab" data-toggle="tab" href="#information" role="tab" aria-controls="home" aria-selected="true"><?php vv_e( 'Apartment Info' ); ?></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="address-tab" data-toggle="tab" href="#address" role="tab" aria-controls="contact" aria-selected="false"><?php vv_e( 'Address' ); ?></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="pricing-discounts-tab" data-toggle="tab" href="#pricing-discounts" role="tab" aria-controls="contact" aria-selected="false"><?php vv_e( 'Pricing & Discounts' ); ?></a>
                </li>
                <?php if(gArrayItem($apartment,'ID') > 0){ ?>
                    <li class="nav-item">
                        <a class="nav-link" id="images-tab" data-toggle="tab" href="#images" role="tab" aria-controls="profile" aria-selected="false"><?php vv_e( 'Images' ); ?></a>
                    </li>
                <?php } ?>
                <li class="nav-item">
                    <a class="nav-link" id="foods-tab" data-toggle="tab" href="#foods" role="tab" aria-controls="contact" aria-selected="false"><?php vv_e( 'Foods' ); ?></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="facilities-tab" data-toggle="tab" href="#facilities" role="tab" aria-controls="contact" aria-selected="false"><?php vv_e( 'Facilities' ); ?></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="cleaners-tab" data-toggle="tab" href="#cleaners" role="tab" aria-controls="contact" aria-selected="false"><?php vv_e( 'Cleaners Checklist' ); ?></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="content-tab" data-toggle="tab" href="#contents" role="tab" aria-controls="contact" aria-selected="false"><?php vv_e( 'Page Content/Texts' ); ?></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="translations-tab" data-toggle="tab" href="#translations" role="tab" aria-controls="contact" aria-selected="false"><?php vv_e( 'Translations' ); ?></a>
                </li>
                <?php include __DIR__ . '/_platform-nav.php'; ?>
            </ul>
            <div class="tab-content" id="myTabContent3">
                <div class="tab-pane fade show active" id="information" role="tabpanel" aria-labelledby="information-tab">
                    <div class="tab-inner-content">
                        <h4 class="mb-2"><?php vv_e( 'Details' ); ?></h4>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label><?php vv_e( 'Publication status' ); ?></label>
                                    <select name="status" class="form-control form-control-sm">
                                        <?php foreach ( $status_labels as $key => $label ) { ?>
                                            <option value="<?php echo esc_attr( $key ); ?>"<?php selected( gArrayItem( $apartment, 'status', 'active' ), $key ); ?>><?php echo esc_html( vv__( $label ) ); ?></option>
                                        <?php } ?>
                                    </select>
                                    <small class="text-muted"><?php printf( esc_html( vv__( 'Images: %1$d / %2$d required to publish' ) ), $image_count, $min_images ); ?></small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label><?php vv_e( 'Building' ); ?></label>
                                    <select name="building_id" id="apartment_building_id" class="form-control form-control-sm vv-name-field">
                                        <option value="0"><?php vv_e( '— Select building —' ); ?></option>
                                        <?php foreach ( $buildings as $building ) { ?>
                                            <option value="<?php echo esc_attr( gArrayItem( $building, 'ID' ) ); ?>" data-name="<?php echo esc_attr( gArrayItem( $building, 'post_title' ) ); ?>"<?php selected( $building_id, gArrayItem( $building, 'ID' ) ); ?>><?php echo esc_html( gArrayItem( $building, 'post_title' ) ); ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label><?php vv_e( 'Apartment type' ); ?></label>
                                    <select name="apartment_type" id="apartment_type" class="form-control form-control-sm vv-name-field">
                                        <?php foreach ( $apartment_types as $key => $label ) { ?>
                                            <option value="<?php echo esc_attr( $key ); ?>"<?php selected( gArrayItem( $apartment, 'apartment_type', vvApartmentPlatform::rooms_to_type( gArrayItem( $apartment, 'rooms' ) ) ), $key ); ?>><?php echo esc_html( $label ); ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><?php vv_e( 'Distinguishing feature' ); ?></label>
                                    <input type="text" name="distinguishing_feature" id="distinguishing_feature" class="form-control form-control-sm vv-name-field" value="<?php echo esc_attr( gArrayItem( $apartment, 'distinguishing_feature' ) ); ?>" placeholder="<?php echo esc_attr( vv__( 'City View & Pool' ) ); ?>">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label><?php vv_e( 'Price level' ); ?></label>
                                    <select name="price_level" id="price_level" class="form-control form-control-sm">
                                        <?php foreach ( $price_levels as $key => $label ) { ?>
                                            <option value="<?php echo esc_attr( $key ); ?>"<?php selected( gArrayItem( $apartment, 'price_level', 'normal' ), $key ); ?>><?php echo esc_html( vv__( $label ) ); ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label><?php vv_e( 'Suggested daily rate' ); ?></label>
                                    <input type="text" id="suggested_daily_price" class="form-control form-control-sm" value="<?php echo esc_attr( vv_number_format( $suggested_price ) ); ?>" readonly>
                                </div>
                            </div>
                        </div>
                        <?php if ( ! empty( $building_facilities ) ) { ?>
                        <div class="form-group mb-3">
                            <label><?php vv_e( 'Building amenities' ); ?> <span class="text-muted small">(<?php vv_e( 'from building — locked' ); ?>)</span></label>
                            <div class="pl-2">
                                <?php foreach ( $building_facilities as $bf ) { ?>
                                    <div class="text-muted">• <?php echo esc_html( gArrayItem( $bf, 'name' ) ); ?></div>
                                <?php } ?>
                            </div>
                        </div>
                        <?php } ?>
                        <?php if ( ! empty( $house_rules_all ) ) { ?>
                        <div class="form-group mb-3">
                            <label><?php vv_e( 'House rules' ); ?></label>
                            <?php foreach ( $house_rules_all as $rule ) {
                                $rid = gArrayItem( $rule, 'id' );
                                $checked = in_array( $rid, $house_rules_sel, true ) ? 'checked' : '';
                                ?>
                                <div><label><input type="checkbox" name="house_rules_selected[]" value="<?php echo esc_attr( $rid ); ?>" <?php echo $checked; ?>> <?php echo esc_html( gArrayItem( $rule, 'label' ) ); ?></label></div>
                            <?php } ?>
                        </div>
                        <?php } ?>
                        <div class="mb-3">
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="btnGenerateApartmentName"><?php vv_e( 'Generate name from building & feature' ); ?></button>
                        </div>
                        <div class="row">
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label class="control-label"><?php vv_e( 'Name' ); ?></label>
                                    <input type="text" name="name" class="form-control form-control-sm update_display_name w-auto" value="<?php echo gArrayItem($apartment,'name') ?>" maxlength="25" size="25" required >
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="control-label"><?php vv_e( 'District' ); ?></label>
                                    <?php //echo print_r_pre($districts); ?>
                                    <select name="district" class="form-control form-control-sm update_display_name vv-name-field" id="apartment_district"  >
                                        <?php 
                                        echo vv_get_districts_select_options(gArrayItem($apartment,'district'), $districts);
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label><?php vv_e( 'Room #' ); ?></label>
                                    <input type="text" name="room_number" value="<?php echo gArrayItem($apartment,'room_number') ?>" class="form-control form-control-sm" >
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label><?php vv_e( 'Floor #' ); ?></label>
                                    <input type="text" name="floor_number" value="<?php echo gArrayItem($apartment,'floor_number') ?>" class="form-control form-control-sm" >
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label d-block"><?php vv_e( 'Display Name' ); ?></label>
                            <input type="text" name="display_name" class="form-control form-control-sm" value="<?php echo stripslashes(gArrayItem($apartment,'display_name')) ?>" disabled style="display:inline-block;width:calc(100% - 50px)" >
                            <button type="button" class="btn btn-sm btn-secondary align-top btnEditDisplayName" ><?php vv_e( 'Edit' ); ?></button>
                            <input type="hidden" name="display_name2" value="<?php echo stripslashes(gArrayItem($apartment,'display_name')) ?>" >

                        </div>
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label class="control-label d-block"><?php vv_e( 'URL Code' ); ?></label>
                                    <span><?php bloginfo('url') ?>/apartment/</span><input type="text" name="url_slug" class="form-control form-control-sm d-inline " value="<?php echo $url_slug ?>" style="width:calc(100% - 160px)" required >
                                </div>
                            </div>
                            <div class="col-md-4">
                                <?php 
                                if($logged_user_role == 'administrator'){


                                    $partners_r = $this->users_class->get_users(['vv_user_level' => 'partner']);
                                    $partners   = gArrayItem($partners_r,'users');
                                    ?>
                                    <div class="form-group">
                                        <label class="control-label"><?php vv_e( 'Owner' ); ?></label>
                                        <select name="user_id" class="form-control form-control-sm" >
                                            <option value="0"><?php vv_e( 'Visit Vietnam Owner' ); ?></option>
                                            <?php foreach($partners as $partner){ ?>
                                                <option value="<?php echo $partner['ID'] ?>" <?php if(gArrayItem($apartment,'user_id') == $partner['ID']) echo 'selected' ?> ><?php echo esc_html( vv__( 'Partner' ) . ': ' . gArrayItem($partner,'firstname').' '.gArrayItem($partner,'lastname') ); ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                    <?php 
                                }else{
                                    echo '<input type="hidden" name="user_id" value="'.$logged_user_id.'" >';
                                }
                                ?>
                            </div>
                        </div>
                        <hr>
                        <h4 class="mb-4"><?php vv_e( 'Capacity' ); ?></h4>
                        <div class="row">
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="control-label"><?php vv_e( 'Rooms' ); ?></label>
                                    <input type="text" name="rooms" class="form-control form-control-sm update_display_name" value="<?php echo gArrayItem($apartment,'rooms') ?>" required style="max-width: 50px;" >
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="control-label"><?php vv_e( 'Bedrooms' ); ?></label>
                                    <input type="text" name="num_beds" class="form-control form-control-sm update_display_name" value="<?php echo gArrayItem($apartment,'num_beds') ?>" required style="max-width: 50px;" >
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="control-label"><?php vv_e( 'Max Guests' ); ?></label>
                                    <input type="text" name="max_guests" class="form-control form-control-sm" value="<?php echo gArrayItem($apartment,'max_guests') ?>" required style="max-width: 50px;" >
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="control-label"><?php vv_e( 'Bathrooms' ); ?></label>
                                    <input type="text" name="num_bathrooms" class="form-control form-control-sm" value="<?php echo gArrayItem($apartment,'num_bathrooms') ?>" required style="max-width: 50px;" >
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="control-label"><?php vv_e( 'Area sqm' ); ?></label>
                                    <input type="text" name="area_sqm" class="form-control form-control-sm" value="<?php echo gArrayItem($apartment,'area_sqm') ?>" required  style="max-width: 50px;" >
                                </div>
                            </div>
                        </div>
                        <hr>
                        <h4 class="mb-4"><?php vv_e( 'Accessibility' ); ?></h4>
                        <table class="table w-auto table-borderless">
                            <tr>
                                <td>&nbsp;</td>
                                <td><?php vv_e( 'From' ); ?></td>
                                <td><?php vv_e( 'To' ); ?></td>
                                <td>&nbsp;</td>
                            </tr>
                            <tr>
                                <td><b><?php vv_e( 'Check In' ); ?></b></td>
                                <td><input type="text" class="form-control form-control-sm timepicker" name="check_in_time1" value="<?php echo gArrayItem($apartment,'check_in_time1') ?>" style="width:80px"></td>
                                <td><input type="text" class="form-control form-control-sm timepicker" name="check_in_time2" value="<?php echo gArrayItem($apartment,'check_in_time2') ?>" style="width:80px"></td>
                                <td><input type="checkbox" name="flexible_check_in" value="1" <?php echo (gArrayItem($apartment,'flexible_check_in')) ? 'checked' : '' ?> > <?php vv_e( 'Flexible' ); ?></td>
                            </tr>
                            <tr>
                                <td><b><?php vv_e( 'Check Out' ); ?></b></td>
                                <td>&nbsp;</td>
                                <td><input type="text" class="form-control form-control-sm timepicker" name="check_out_time" value="<?php echo gArrayItem($apartment,'check_out_time') ?>" style="width:80px"></td>
                                <td><input type="checkbox" name="allow_extension" value="1" <?php echo (gArrayItem($apartment,'allow_extension')) ? 'checked' : '' ?> > <?php vv_e( 'Option for extension' ); ?></td>
                            </tr>

                        </table>
                        <hr>
                        <div class="row">
                            <div class="col-md-6">
                                <h4 class="mb-2"><?php vv_e( 'Services' ); ?></h4>
                                <div class="form-group">
                                    <div>
                                        <input type="checkbox" name="checkin_without_host" value="1" <?php echo (gArrayItem($apartment,'checkin_without_host') == 1) ? 'checked' : '' ?> >
                                        <label><?php vv_e( 'Check-in without Host' ); ?></label>
                                    </div>
                                    <div>
                                        <input type="checkbox" name="airport_pickup" value="1" <?php echo (gArrayItem($apartment,'airport_pickup') == 1) ? 'checked' : '' ?> >
                                        <label><?php vv_e( 'Air Port Pick Up' ); ?></label>
                                    </div>
                                    <div>
                                        <input type="checkbox" name="flexible_reservation" value="1" <?php echo (gArrayItem($apartment,'flexible_reservation') == 1) ? 'checked' : '' ?> >
                                        <label><?php vv_e( 'Flexible reservation' ); ?></label>
                                    </div>
                                    <div>
                                        <input type="checkbox" name="scooter_rental" value="1" <?php echo (gArrayItem($apartment,'scooter_rental') == 1) ? 'checked' : '' ?> >
                                        <label><?php vv_e( 'Scooters availability' ); ?></label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h4 class="mb-2"><?php vv_e( 'Security Features' ); ?></h4>
                                <?php 
                                $apartment_security_features = json_decode(gArrayItem($apartment,'security_features'));
                                if(!is_array($apartment_security_features)) $apartment_security_features = [];
                                foreach($security_features as $security_feature){ 
                                    $checked = '';
                                    if(in_array(gArrayItem($security_feature,'security_feature_id'),$apartment_security_features,true)) $checked = 'checked';
                                    ?>
                                    <div class=""><input type="checkbox" name="security_features[]" value="<?php echo gArrayItem($security_feature,'security_feature_id') ?>" <?php echo $checked ?> > <b><?php echo gArrayItem($security_feature,'name') ?></b></div>
                                    <?php 
                                } 
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="address" role="tabpanel" aria-labelledby="address-tab">
                    <div class="tab-inner-content">
                        <h4 class="mb-2"><?php vv_e( 'Address/Map' ); ?></h4>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label"><?php vv_e( 'Address' ); ?></label>
                                    <input type="text" name="address" class="form-control form-control-sm map_field" value="<?php echo gArrayItem($apartment,'address') ?>" required >
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="control-label"><?php vv_e( 'Address - Latitude' ); ?></label>
                                            <input type="text" name="address_latitude" class="form-control form-control-sm map_field" value="<?php echo gArrayItem($apartment,'address_latitude') ?>" required >
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="control-label"><?php vv_e( 'Address - Longitude' ); ?></label>
                                            <input type="text" name="address_longitude" class="form-control form-control-sm map_field" value="<?php echo gArrayItem($apartment,'address_longitude') ?>" required >
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div id="apartment_map" style="height: 500px; width: 100%;"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="contents" role="tabpanel" aria-labelledby="pricing-discounts-tab">
                    <div class="tab-inner-content">
                        <div class="form-group">
                            <label class="control-label"><?php vv_e( 'Description' ); ?></label>
                            <textarea name="description" class="form-control form-control-sm wysiwyg_editor" ><?php echo gArrayItem($apartment,'description') ?></textarea>
                        </div>
                        <hr>
                        <h4 class="mb-2"><?php vv_e( 'Address/Map' ); ?></h4>
                        <div class="form-group">
                            <label><?php vv_e( 'Facilities Description' ); ?></label>
                            <textarea name="features_description" class="form-control form-control-sm wysiwyg_editor"><?php echo gArrayItem($apartment,'features_description') ?></textarea>
                        </div>
                        <div class="form-group">
                            <label class="control-label"><?php vv_e( 'About This (Short)' ); ?></label>
                            <textarea name="about_this_short" class="form-control form-control-sm wysiwyg_editor" ><?php echo gArrayItem($apartment,'about_this_short') ?></textarea>
                        </div>
                        <div class="form-group">
                            <label class="control-label"><?php vv_e( 'About This' ); ?></label>
                            <textarea name="about_this" class="form-control form-control-sm wysiwyg_editor" ><?php echo gArrayItem($apartment,'about_this') ?></textarea>
                        </div>
                    </div>
                </div>
                <?php
                $translation_locales = class_exists( 'vvI18n' ) ? vvI18n::translation_locales() : [];
                ?>
                <div class="tab-pane fade" id="translations" role="tabpanel" aria-labelledby="translations-tab">
                    <div class="tab-inner-content">
                        <p class="text-muted"><?php vv_e( 'English content is edited in the main tabs. Add translations for other languages below.' ); ?></p>
                        <?php
                        $locale_index = 0;
                        $locale_count = count( $translation_locales );
                        foreach ( $translation_locales as $locale_slug => $locale_info ) {
                            $locale_index++;
                            $apartment_i18n = [];
                            if ( gArrayItem( $apartment, 'ID' ) > 0 && class_exists( 'vvI18n' ) ) {
                                $apartment_i18n = vvI18n::get_apartment_i18n( intval( $apartment['ID'] ), $locale_slug );
                            }
                            ?>
                        <h4 class="mb-3 mt-4"><?php echo esc_html( vvI18n::locale_label( $locale_slug ) ); ?></h4>
                        <div class="form-group">
                            <label><?php vv_e( 'Apartment name' ); ?></label>
                            <input type="text" name="i18n[<?php echo esc_attr( $locale_slug ); ?>][name]" class="form-control form-control-sm" value="<?php echo esc_attr( gArrayItem( $apartment_i18n, 'name' ) ); ?>" maxlength="255">
                        </div>
                        <div class="form-group">
                            <label><?php vv_e( 'Display name' ); ?></label>
                            <input type="text" name="i18n[<?php echo esc_attr( $locale_slug ); ?>][display_name]" class="form-control form-control-sm" value="<?php echo esc_attr( gArrayItem( $apartment_i18n, 'display_name' ) ); ?>">
                        </div>
                        <div class="form-group">
                            <label><?php vv_e( 'Description' ); ?></label>
                            <textarea name="i18n[<?php echo esc_attr( $locale_slug ); ?>][description]" class="form-control form-control-sm wysiwyg_editor"><?php echo esc_textarea( gArrayItem( $apartment_i18n, 'description' ) ); ?></textarea>
                        </div>
                        <div class="form-group">
                            <label><?php vv_e( 'Facilities Description' ); ?></label>
                            <textarea name="i18n[<?php echo esc_attr( $locale_slug ); ?>][features_description]" class="form-control form-control-sm wysiwyg_editor"><?php echo esc_textarea( gArrayItem( $apartment_i18n, 'features_description' ) ); ?></textarea>
                        </div>
                        <div class="form-group">
                            <label><?php vv_e( 'About This (Short)' ); ?></label>
                            <textarea name="i18n[<?php echo esc_attr( $locale_slug ); ?>][about_this_short]" class="form-control form-control-sm wysiwyg_editor"><?php echo esc_textarea( gArrayItem( $apartment_i18n, 'about_this_short' ) ); ?></textarea>
                        </div>
                        <div class="form-group">
                            <label><?php vv_e( 'About This' ); ?></label>
                            <textarea name="i18n[<?php echo esc_attr( $locale_slug ); ?>][about_this]" class="form-control form-control-sm wysiwyg_editor"><?php echo esc_textarea( gArrayItem( $apartment_i18n, 'about_this' ) ); ?></textarea>
                        </div>
                        <div class="form-group">
                            <label><?php vv_e( 'House rules' ); ?></label>
                            <textarea name="i18n[<?php echo esc_attr( $locale_slug ); ?>][house_rules]" class="form-control form-control-sm wysiwyg_editor"><?php echo esc_textarea( gArrayItem( $apartment_i18n, 'house_rules' ) ); ?></textarea>
                        </div>
                        <div class="form-group">
                            <label><?php vv_e( 'Property safety' ); ?></label>
                            <textarea name="i18n[<?php echo esc_attr( $locale_slug ); ?>][property_safety]" class="form-control form-control-sm wysiwyg_editor"><?php echo esc_textarea( gArrayItem( $apartment_i18n, 'property_safety' ) ); ?></textarea>
                        </div>
                        <?php if ( $locale_index < $locale_count ) { ?><hr><?php } ?>
                        <?php } ?>
                    </div>
                </div>
                <?php include __DIR__ . '/_platform-tabs.php'; ?>
                <div class="tab-pane fade" id="pricing-discounts" role="tabpanel" aria-labelledby="pricing-discounts-tab">
                    <div class="tab-inner-content">
                        <?php 
                        $average_price_area = '';

                        $price_daily = gArrayItem($apartment,'price_daily');

                        $pricing        = json_decode(gArrayItem($apartment,'pricing'),true);
                        //echo print_r_pre($pricing);
                        $owner_id = gArrayItem($apartment,'user_id');
                        if($owner_id == '' ) $owner_id = get_current_user_id();
                        $currency = get_user_meta($owner_id,'vv_host_currency',true);
                        if($currency == '') $currency = 'USD';
                        $currencycurrency       = gArrayItem($pricing,'currency');
                        $addon_days2  = gArrayItem($pricing,'addon_days2');
                        $discount_3days  = gArrayItem($pricing,'discount_3days');
                        $discount_5days  = gArrayItem($pricing,'discount_5days');
                        $discount_7days  = gArrayItem($pricing,'discount_7days');
                        $discount_30days = gArrayItem($pricing,'discount_30days');
                        ?>
                        <h4 class="mb-2"><?php vv_e( 'Pricing' ); ?></h4>
                        <div class="row">
                            <div class="col-md-8">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label><?php vv_e( 'Your Daily Price' ); ?></label>
                                            <input type="text" name="price_daily" value="<?php echo vv_number_format($price_daily) ?>" class="form-control form-control-sm calculatePricing1" >
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label><?php vv_e( 'Currency' ); ?></label>
                                            <select name="currency"  class="form-control form-control-sm calculatePricing1" >
                                                <option value="USD" <?php echo($currency == 'USD') ? 'selected':'' ?>>USD</option>
                                                <option value="VND" <?php echo($currency == 'VND') ? 'selected':'' ?>>VND</option>
                                                <option value="NOK" <?php echo($currency == 'NOK') ? 'selected':'' ?>>NOK</option>
                                                <option value="EUR" <?php echo($currency == 'EUR') ? 'selected':'' ?>>EUR</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label><?php vv_e( 'Ambassador %' ); ?></label>
                                            <input type="text" name="ambassador_commission" value="<?php echo gArrayItem($apartment,'ambassador_commission') ?>" class="form-control form-control-sm calculatePricing1" >
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label><?php vv_e( 'Discount %' ); ?></label>
                                            <input type="text" name="promocode_discount" value="<?php echo gArrayItem($apartment,'promocode_discount') ?>" class="form-control form-control-sm calculatePricing1"  >
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label><?php vv_e( 'Average Price for similar 2BR in same area' ); ?></label>
                                    <input type="text" name="average_price_area" value="<?php echo $average_price_area ?>" class="form-control form-control-sm" disabled style="max-width:100px" >
                                </div>
                            </div>
                        </div>
                        <div class="mb-4">
                            <div class="float-left mr-2">
                                <div><?php vv_e( 'Price' ); ?></div>
                                <input type="text" name="d_daily_price" value="" class="form-control form-control-sm" disabled style="max-width:120px" >
                            </div>
                            <div class="float-left mr-2">
                                <div><?php vv_e( 'Ambassador' ); ?> <span class="aparment_currency"><?php echo $currency ?></span></div>
                                <input type="text" name="d_ambassador_commission_val" value="" class="form-control form-control-sm" disabled style="max-width:120px" >
                            </div>
                            <div class="float-left mr-2">
                                <div><?php vv_e( 'Discount' ); ?> <span class="aparment_currency"><?php echo $currency ?></span></div>
                                <input type="text" name="d_promocode_discount_val" value="" class="form-control form-control-sm" disabled style="max-width:120px" >
                            </div>
                            <div class="float-left mr-2">
                                <div><?php vv_e( 'Discount Price' ); ?></div>
                                <input type="text" name="d_discount_price" value="" class="form-control form-control-sm" disabled style="max-width:120px" >
                            </div>
                            <div class="float-left mr-2">
                                <div><?php vv_e( 'Guest Price' ); ?></div>
                                <input type="text" name="d_guest_price" value="" class="form-control form-control-sm" disabled style="max-width:120px" >
                            </div>
                            <div class="float-left mr-2">
                                <div><?php vv_e( 'Host Final Income' ); ?></div>
                                <input type="text" name="d_host_final_income" value="" class="form-control form-control-sm" disabled style="max-width:120px" >
                            </div>
                            <div style="clear:both"></div>
                        </div>

                        <h4 class="mb-2"><?php vv_e( 'Addon/Discounts' ); ?></h4>
                        <table class="table w-auto border addon_discount">
                            <tr>
                                <td>&nbsp;</td>
                                <td><?php vv_e( '%' ); ?></td>
                                <td><?php vv_e( 'Price' ); ?></td>
                                <td><?php vv_e( 'Orig Price' ); ?></td>
                                <td><?php vv_e( 'Outprice' ); ?></td>
                            </tr>
                            <tr>
                                <th><?php vv_e( 'Saturday to Thursday' ); ?></th>
                                <td>&nbsp;</td>
                                <td><input type="text" name="price_days1" value="" class="form-control form-control-sm" disabled  style="max-width:100px" ></td>
                                <td><input type="text" name="orig_price_days1" value="" class="form-control form-control-sm" disabled style="max-width:100px" ></td>
                                <td><input type="text" name="out_price_days1" value="" class="form-control form-control-sm" disabled style="max-width:100px" ></td>
                            </tr>
                            <tr>
                                <th><?php vv_e( 'Friday/Saturday' ); ?></th>
                                <td><input type="text" name="addon_days2" value="<?php echo $addon_days2 ?>" class="form-control form-control-sm d-inline mr-2"  style="max-width:50px" > <?php vv_e( 'Addon' ); ?></td>
                                <td><input type="text" name="price_days2" value="" class="form-control form-control-sm" disabled style="max-width:100px" ></td>
                                <td><input type="text" name="orig_price_days2" value="" class="form-control form-control-sm" disabled style="max-width:100px" ></td>
                                <td><input type="text" name="out_price_days2" value="" class="form-control form-control-sm" disabled style="max-width:100px" ></td>
                            </tr>
                            <tr>
                                <th><?php vv_e( '3 Days' ); ?></th>
                                <td><input type="text" name="discount_3days" value="<?php echo $discount_3days ?>" class="form-control form-control-sm d-inline mr-2"  style="max-width:50px" > <?php vv_e( 'Discount' ); ?></td>
                                <td><input type="text" name="price_3days" value="" class="form-control form-control-sm" disabled style="max-width:100px" ></td>
                                <td><input type="text" name="orig_price_3days" value="" class="form-control form-control-sm" disabled style="max-width:100px" ></td>
                                <td><input type="text" name="out_price_3days" value="" class="form-control form-control-sm" disabled style="max-width:100px" ></td>
                            </tr>
                            <tr>
                                <th><?php vv_e( '5 Days' ); ?></th>
                                <td><input type="text" name="discount_5days" value="<?php echo $discount_5days ?>" class="form-control form-control-sm d-inline mr-2"  style="max-width:50px" > <?php vv_e( 'Discount' ); ?></td>
                                <td><input type="text" name="price_5days" value="" class="form-control form-control-sm" disabled style="max-width:100px" ></td>
                                <td><input type="text" name="orig_price_5days" value="" class="form-control form-control-sm" disabled style="max-width:100px" ></td>
                                <td><input type="text" name="out_price_5days" value="" class="form-control form-control-sm" disabled style="max-width:100px" ></td>
                            </tr>
                            <tr>
                                <th><?php vv_e( '7 Days' ); ?></th>
                                <td><input type="text" name="discount_7days" value="<?php echo $discount_7days ?>" class="form-control form-control-sm d-inline mr-2"  style="max-width:50px" > <?php vv_e( 'Discount' ); ?></td>
                                <td><input type="text" name="price_7days" value="" class="form-control form-control-sm" disabled style="max-width:100px" ></td>
                                <td><input type="text" name="orig_price_7days" value="" class="form-control form-control-sm" disabled style="max-width:100px" ></td>
                                <td><input type="text" name="out_price_7days" value="" class="form-control form-control-sm" disabled style="max-width:100px" ></td>
                            </tr>
                            <tr>
                                <th><?php vv_e( '1 Month' ); ?></th>
                                <td><input type="text" name="discount_30days" value="<?php echo $discount_30days ?>" class="form-control form-control-sm d-inline mr-2"  style="max-width:50px" > <?php vv_e( 'Discount' ); ?></td>
                                <td><input type="text" name="price_30days" value="" class="form-control form-control-sm" disabled style="max-width:100px" ></td>
                                <td><input type="text" name="orig_price_30days" value="" class="form-control form-control-sm" disabled style="max-width:100px" ></td>
                                <td><input type="text" name="out_price_30days" value="" class="form-control form-control-sm" disabled style="max-width:100px" ></td>
                            </tr>
                        </table>

                        <hr>

                        <div class="form-group">
                            <label class="control-label"><?php vv_e( 'Campaign Discounts' ); ?></label>
                            <table class="discounts table w-auto border">
                                <tr>
                                    <th><?php vv_e( 'Start' ); ?></th>
                                    <th><?php vv_e( 'End' ); ?></th>
                                    <th><?php vv_e( 'Discount %' ); ?></th>
                                    <th><?php vv_e( 'Action' ); ?></th>
                                </tr>
                                <?php
                                $discounts = array(); 
                                if(gArrayItem($apartment,'ID') > 0){
                                    $discounts = $this->apartment_class->get_discounts(gArrayItem($apartment,'ID'));
                                }
                                foreach($discounts as $discount){
                                    ?>
                                    <tr>
                                        <td><input type="text" name="discount_start[]" autocomplete="no" class="form-control form-control-sm datepick" value="<?php echo ($discount['datestart'] != '') ? date("m/d/Y",strtotime($discount['datestart'])) : '' ?>" ></td>
                                        <td><input type="text" name="discount_end[]" autocomplete="no" class="form-control form-control-sm datepick" value="<?php echo ($discount['dateend'] != '') ? date("m/d/Y",strtotime($discount['dateend'])) : '' ?>" ></td>
                                        <td><input type="text" name="discount[]" class="form-control form-control-sm" value="<?php echo $discount['discount'] ?>" ></td>
                                        <td>
                                            <input type="hidden" name="discount_id[]" value="<?php echo $discount['apt_discount_id'] ?>" >
                                            <a href="#" onclick="return removeDiscount(this)" ><i class="fa fa-trash"></i></a>
                                        </td>
                                    </tr>
                                    <?php 
                                }
                                ?>
                                <tr> 
                                    <td><input type="text" name="discount_start[]" autocomplete="no" class="form-control form-control-sm datepick" value="" ></td>
                                    <td><input type="text" name="discount_end[]" autocomplete="no" class="form-control form-control-sm datepick" value="" ></td>
                                    <td><input type="text" name="discount[]" class="form-control form-control-sm" value="" ></td>
                                    <td>
                                        <input type="hidden" name="discount_id[]" value="0" >
                                        <em><?php vv_e( 'new' ); ?></em>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="form-group">
                            <label><?php vv_e( 'Cleaning Fee' ); ?></label>
                            <div class="mb-2">
                                <label class="mr-3"><input type="checkbox" name="cleaning_fee_enabled" value="1" <?php echo gArrayItem( $apartment, 'cleaning_fee_enabled', 1 ) ? 'checked' : ''; ?>> <?php vv_e( 'Enable cleaning fee' ); ?></label>
                            </div>
                            <input type="text" name="cleaning_fee" class="form-control form-control-sm d-inline-block" value="<?php echo vv_number_format(gArrayItem($apartment,'cleaning_fee')) ?>" style="width:100px" >
                            <label class="ml-3"><?php vv_e( 'Extra cleaning fee' ); ?></label>
                            <input type="text" name="extra_cleaning_fee" class="form-control form-control-sm d-inline-block" value="<?php echo vv_number_format(gArrayItem($apartment,'extra_cleaning_fee')) ?>" style="width:100px" >
                        </div>
                        <div class="form-group">
                            <label><?php vv_e( 'Pricing model' ); ?></label>
                            <select name="pricing_model" id="pricing_model" class="form-control form-control-sm" style="max-width:200px">
                                <option value="fixed"<?php selected( gArrayItem( $apartment, 'pricing_model', 'fixed' ), 'fixed' ); ?>><?php vv_e( 'Fixed year-round' ); ?></option>
                                <option value="seasonal"<?php selected( gArrayItem( $apartment, 'pricing_model' ), 'seasonal' ); ?>><?php vv_e( 'Seasonal (per month)' ); ?></option>
                            </select>
                        </div>
                        <div id="seasonal_pricing_wrap" class="form-group" style="<?php echo gArrayItem( $apartment, 'pricing_model' ) === 'seasonal' ? '' : 'display:none'; ?>">
                            <label><?php vv_e( 'Monthly seasonal prices' ); ?></label>
                            <div class="row">
                                <?php
                                $months = [ 1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'May', 6 => 'Jun', 7 => 'Jul', 8 => 'Aug', 9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dec' ];
                                foreach ( $months as $m => $mlabel ) {
                                    ?>
                                    <div class="col-md-3 col-6 form-group">
                                        <label class="small"><?php echo esc_html( $mlabel ); ?></label>
                                        <input type="text" name="seasonal_price[<?php echo $m; ?>]" class="form-control form-control-sm" value="<?php echo esc_attr( vv_number_format( gArrayItem( $seasonal, $m ) ) ); ?>">
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label><?php vv_e( 'Scooter Rental Fee' ); ?></label>
                            <input type="text" name="scooter_rental_fee" class="form-control form-control-sm" value="<?php echo vv_number_format(gArrayItem($apartment,'scooter_rental_fee')) ?>" style="width:100px" >
                        </div>
                    </div>
                </div>
                <?php if(gArrayItem($apartment,'ID') > 0){ ?>
                    <div class="tab-pane fade" id="images" role="tabpanel" aria-labelledby="images-tab">
                        <div class="tab-inner-content">
                            <div class="form-group">
                                <label class="control-label"><?php vv_e( 'Images' ); ?></label>
                                <div id="apartment_images_wrap">
                                <?php 
                                $this->apartment_class->get_apartment_images_html($apartment);
                                ?>                
                                </div>
                                <div class="mt-4"><button type="button" class="btn btn-info btn-sm btnAddImage" data-toggle="modal" data-target="#uploadImagesModal" ><?php vv_e( 'Add Image' ); ?></button></div>
                            </div>
                        </div>
                    </div>
                <?php } ?>
                <div class="tab-pane fade" id="foods" role="tabpanel" aria-labelledby="foods-tab">
                    <div class="tab-inner-content">
                        <div class="form-group">
                            <label class="control-label"><?php vv_e( 'Foods' ); ?></label>
                            <div class="form-wrap2">
                                <table class="foods FoodsWrap table border">
                                    <thead>
                                        <tr>
                                            <th><?php vv_e( 'Image' ); ?></th>
                                            <th><?php vv_e( 'Name' ); ?></th>
                                            <th><?php vv_e( 'Price' ); ?></th>
                                            <th><?php vv_e( 'Action' ); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $foods = array();
                                        if(gArrayItem($apartment,'ID') > 0){
                                            $foods = $this->apartment_class->get_apartment_foods(gArrayItem($apartment,'ID'));

                                            //echo print_r_pre($foods);
                                        }
                                        foreach($foods as $food){
                                            ?>
                                            <tr>
                                                <td>
                                                    <?php 
                                                    if($food['image'] != '') echo '<img src="'.$food['image'].'" class="img-fluid" style="width:80px" >';
                                                    else echo '&nbsp;';
                                                    ?>
                                                </td>
                                                <td><?php echo stripslashes(gArrayItem($food,'name'))  ?></td>
                                                <td><input type="text" name="food_price[]" autocomplete="no" class="form-control form-control-sm " value="<?php echo gArrayItem($food,'price')  ?>" ></td>
                                                <td>
                                                    <input type="hidden" name="food_name[]" value="<?php echo $food['name'] ?>" >
                                                    <input type="hidden" name="food_id[]" value="<?php echo $food['food_id'] ?>" >
                                                    <input type="hidden" name="apt_food_id[]" value="<?php echo $food['apt_food_id'] ?>" >
                                                    <a href="#" onclick="return removeFood(this)" ><i class="fa fa-trash"></i></a>
                                                </td>
                                            </tr>
                                            <?php 
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-2"><a href="#" data-toggle="modal" data-target="#addFoodModal" class="btn btn-info btn-sm" ><?php vv_e( 'Add Food' ); ?></a></div>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="facilities" role="tabpanel" aria-labelledby="facilities-tab">
                    <div class="tab-inner-content">
                        <div class="form-group">
                            <label><?php vv_e( 'Facilities' ); ?></label>
                            <?php 
                            $apartment_facilicies = json_decode(gArrayItem($apartment,'facilities'));
                            if(!is_array($apartment_facilicies)) $apartment_facilicies = [];
                            foreach($facilities as $facility){ 
                                $checked = '';
                                if(in_array(gArrayItem($facility,'facility_id'),$apartment_facilicies,true)) $checked = 'checked';
                                ?>
                                <div class=""><input type="checkbox" name="facilities[]" value="<?php echo gArrayItem($facility,'facility_id') ?>" <?php echo $checked ?> > <?php echo gArrayItem($facility,'name') ?></div>
                                <?php 
                            } 
                            ?>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="cleaners" role="tabpanel" aria-labelledby="cleaners-tab">
                    <div class="tab-inner-content">
                        <div class="form-group">
                            <label class="control-label"><?php vv_e( 'Cleaners Checklist' ); ?></label>
                            <div class="form-wrap2">
                                <table class="foods CleanersChecklistWrap table border">
                                    <thead>
                                        <tr>
                                            <th><?php vv_e( 'Name' ); ?></th>
                                            <th style="width:100px"><?php vv_e( 'Qty' ); ?></th>
                                            <th style="width:100px"><?php vv_e( 'Action' ); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $apt_cleaners_checklists = json_decode(gArrayItem($apartment,'cleaners_checklists'),true);
                                        if(!is_array($apt_cleaners_checklists)) $apt_cleaners_checklists = [];
                                        foreach($apt_cleaners_checklists as $checklist){
                                            ?>
                                            <tr>
                                                <td><?php echo stripslashes(gArrayItem($checklist,'name'))  ?></td>
                                                <td><input type="text" name="checklist_qty[]" autocomplete="no" class="form-control form-control-sm " value="<?php echo gArrayItem($checklist,'qty')  ?>" ></td>
                                                <td>
                                                    <input type="hidden" name="checklist_name[]" value="<?php echo gArrayItem($checklist,'name') ?>" >
                                                    <input type="hidden" name="checklist_id[]" value="<?php echo gArrayItem($checklist,'id') ?>" >
                                                    <input type="hidden" name="checklist_type[]" value="<?php echo gArrayItem($checklist,'type') ?>" >
                                                    <a href="#" onclick="return removeCleanersChecklist(this)" ><i class="fa fa-trash"></i></a>
                                                </td>
                                            </tr>
                                            <?php 
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-2"><a href="#" data-toggle="modal" data-target="#addCleanersChecklistModal" class="btn btn-info btn-sm" ><?php vv_e( 'Add Item' ); ?></a></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


    <hr>
    <div class="">
        <button type="submit" class="btn btn-primary pl-4 pr-4"><?php vv_e( 'Submit' ); ?></button> 
    </div>
</form>

<?php if ( $apartment_id > 0 ) { ?>
<form method="post" id="vvSendOfferForm" style="display:none;">
    <input type="hidden" name="vv_action" value="send_apartment_offer">
    <input type="hidden" name="apartment_id" value="<?php echo intval( $apartment_id ); ?>">
</form>
<?php } ?>

<?php 
ob_start();

$food_categories = vv_get_food_categories();
?>
<div class="modal fade" id="addFoodModal" tabindex="-1" role="dialog" aria-labelledby="addFoodModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form method="post">
                <div class="modal-header">
                    <h5 class="modal-title" id="addFoodModalLabel"><?php vv_e( 'Add Food' ); ?></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label><?php vv_e( 'Category' ); ?></label >
                        <select name="category" id="food_category" class="form-control" required>
                            <option value=""><?php vv_e( '-Select Category-' ); ?></option>
                            <?php 
                            foreach($food_categories as $food_cat){
                                ?>
                                <option value="<?php echo gArrayItem($food_cat,'food_category_id') ?>" ><?php echo $food_cat['name'] ?></option>
                                <?php 
                            }
                            ?>
                        </select>
                    </div>
                    <div id="addFoodList"></div>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="addCleanersChecklistModal" tabindex="-1" role="dialog" aria-labelledby="addCleanersChecklistModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="post">
                <div class="modal-header">
                    <h5 class="modal-title" id="addCleanersChecklistModalLabel"><?php vv_e( 'Add Cleaners Checklist' ); ?></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                            <table class="table">
                                <?php 
                                foreach($cleaners_checklists as $cleaners_checklist){
                                    ?>
                                    <tr>
                                        <td><?php echo $cleaners_checklist['name'] ?></td>
                                        <td style="width:100px">
                                            <?php 
                                            if($cleaners_checklist['type'] == 'qty'){
                                                ?>
                                                <input type="text" name="qty_<?php echo $cleaners_checklist['checklist_id'] ?>" class="form-control form-control-sm" value="1" required>
                                                <?php 
                                            }else{
                                                ?>
                                                <input type="hidden" name="qty_<?php echo $cleaners_checklist['checklist_id'] ?>" class="form-control form-control-sm" value="1">
                                                &nbsp;
                                                <?php 
                                            }
                                            ?>
                                        </td>
                                        <td style="width:60px">
                                            <button type="button" class="btn btn-info btn-sm btnAddCleanersChecklist" data-checklist_id="<?php echo $cleaners_checklist['checklist_id'] ?>" data-name="<?php echo $cleaners_checklist['name'] ?>" data-type="<?php echo $cleaners_checklist['type'] ?>"  ><?php vv_e( 'Add' ); ?></button>
                                        </td>
                                    </tr>
                                    <?php 
                                }
                                ?>
                            </table>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal" id="uploadImagesModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document" style="max-width: 900px;" >
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php vv_e( 'Upload Images' ); ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <div id="actions" class="row">
                    <div class="col-lg-6">
                        <div class="btn-group w-100">
                            <span class="btn btn-success col fileinput-button">
                                <i class="fas fa-plus"></i>
                                <span><?php vv_e( 'Add Images' ); ?></span>
                            </span>
                            <button type="submit" class="btn btn-primary col start">
                                <i class="fas fa-upload"></i>
                                <span><?php vv_e( 'Start upload' ); ?></span>
                            </button>
                            <button type="reset" class="btn btn-warning col cancel">
                                <i class="fas fa-times-circle"></i>
                                <span><?php vv_e( 'Cancel upload' ); ?></span>
                            </button>
                        </div>
                    </div>
                    <div class="col-lg-6 d-flex align-items-center">
                        <div class="fileupload-process w-100">
                            <div id="total-progress" class="progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
                                <div class="progress-bar progress-bar-success" style="width:0%;" data-dz-uploadprogress></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="table table-striped files" id="previews">
                    <div id="addImageTemplate" class="row mt-2">
                        <div class="col-auto"><span class="preview"><img src="data:," alt="" data-dz-thumbnail /></span></div>
                        <div class="col d-flex align-items-center">
                            <p class="mb-0"><span class="lead" data-dz-name></span>(<span data-dz-size></span>)</p>
                            <strong class="error text-danger" data-dz-errormessage></strong>
                        </div>
                        <div class="col-4 d-flex align-items-center">
                            <div class="progress progress-striped active w-100" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
                                <div class="progress-bar progress-bar-success" style="width:0%;" data-dz-uploadprogress></div>
                            </div>
                        </div>
                        <div class="col-auto d-flex align-items-center">
                            <div class="btn-group">
                                <button class="btn btn-primary start"><i class="fas fa-upload"></i><span><?php vv_e( 'Start' ); ?></span></button>
                                <button data-dz-remove class="btn btn-warning cancel"><i class="fas fa-times-circle"></i><span><?php vv_e( 'Cancel' ); ?></span></button>
                                <button data-dz-remove class="btn btn-danger delete"><i class="fas fa-trash"></i><span><?php vv_e( 'Delete' ); ?></span></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal" aria-label="Close"><?php vv_e( 'Cancel' ); ?></button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script type="text/javascript" src="<?php echo vv_plugins_url() ?>admin/plugins/dropzone/min/dropzone.min.js"></script>
<script type="text/javascript" src="<?php echo vv_plugins_url() ?>admin/plugins/ekko-lightbox/ekko-lightbox.min.js"></script>
<script type="text/javascript" src="<?php echo vv_plugins_url() ?>admin/js/bootstrap-datetimepicker.js" ></script>
<script type="text/javascript" src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script>
<script src="https://maps.googleapis.com/maps/api/js?key=<?php echo vv_get_google_map_api_key() ?>" ></script>

<?php
$vv_apartment_i18n = [
	'remove_discount' => esc_js( vv__( 'Remove discount?' ) ),
	'remove_food'     => esc_js( vv__( 'Remove food?' ) ),
	'delete_image'    => esc_js( vv__( 'Delete Image?' ) ),
	'remove_item'     => esc_js( vv__( 'Remove Item?' ) ),
	'image_deleted'   => esc_js( vv__( 'Image Deleted' ) ),
	'no_food_found'   => esc_js( vv__( 'No Food Found' ) ),
	'add'             => esc_js( vv__( 'Add' ) ),
];
?>

<script>
    var Foods;
    var apartment_map;

    function initMap(el){
        apartment_map = new google.maps.Map(document.getElementById('apartment_map'), {
            zoom: 12,
            center: { lat: 0, lng: 0 }
        });

        lat     = $('input[name="address_latitude"]').val();
        lng     = $('input[name="address_longitude"]').val();
        address = $('input[name="address"]').val();

        source = 'addr';
        if(el == null){
            if(lat != '' && lng != ''){
                source = 'co-ords';
            }
        }else{
            if(el.attr('name') == 'address_latitude' || el.attr('name') == 'address_longitude'){
                source = 'co-ords';
            }
        }

        if(source == 'co-ords'){
            lat = parseFloat(lat);
            lng = parseFloat(lng);
            var marker = new google.maps.Marker({
                position: { lat: lat, lng: lng },
                map: apartment_map,
            });

            apartment_map.setCenter({ lat: lat, lng: lng });
        }else{
            var geocoder = new google.maps.Geocoder();

            geocoder.geocode({ 'address': address }, function(results, status) {
            if (status === 'OK') {
                map.setCenter(results[0].geometry.location);
                var marker = new google.maps.Marker({
                map: apartment_map,
                    position: results[0].geometry.location
                });

            } else {
                //vvErrorMsg('Geocode was not successful for the following reason: ' + status);
            }
            });
        }

    }

    function removeDiscount(obj){
        if(confirm('<?php echo $vv_apartment_i18n['remove_discount']; ?>')){
            jQuery(obj).parent('td').parent('tr').find('input[name="discount[]"]').val('delete');
            jQuery(obj).parent('td').parent('tr').hide();
        }
        return false;
    }
    function addFood(food_id , obj){

        for(i = 0; i < Foods.length; i++){
            if(Foods[i].food_id == food_id){
                html = '<tr>';
                html += '<td>'

                if(Foods[i].image != ''){
                    html += '<img src="'+Foods[i].image+'" class="img-fluid" style="width:80px" >';
                }else{
                    html += '&nbsp;';
                }

                html += '</td>';
                html += '<td>'+Foods[i].name+'</td>';
                html += '<td><input type="text" name="food_price[]" autocomplete="no" class="form-control form-control-sm " value="'+Foods[i].price+'" ></td>';
                html += '<td>';
                html += '<input type="hidden" name="food_name[]" value="'+Foods[i].name+'" >';
                html += '<input type="hidden" name="food_id[]" value="'+Foods[i].food_id+'" >';
                html += '<input type="hidden" name="apt_food_id[]" value="new" >';
                html += '<a href="#" onclick="return removeFood(this)" ><i class="fa fa-trash"></i></a>';
                html += '</td>';
                html += '</tr>';

                $('table.foods').find('tbody').append(html);
            }
        }
        $('#addFoodModal').modal('hide');
    }
    function removeFood(obj){
        if(confirm('<?php echo $vv_apartment_i18n['remove_food']; ?>')){            
            jQuery(obj).parent('td').parent('tr').hide();
            jQuery(obj).parent('td').parent('tr').find('input[name="food_name[]"]').val('delete');    
        }
        return false;
    }

    function deleteImage(obj){
        if(confirm('<?php echo $vv_apartment_i18n['delete_image']; ?>')){
            prod_img = jQuery(obj);
            jQuery.get(prod_img.attr('href'),function (data){
                prod_img.parent('div').parent('div').parent('div').html('<div class="alert alert-danger"><?php echo $vv_apartment_i18n['image_deleted']; ?></div>');
            });
        }
        return false;
    }

    function removeCleanersChecklist(obj){
        if(confirm('<?php echo $vv_apartment_i18n['remove_item']; ?>')){
            jQuery(obj).parent('td').parent('tr').remove();
        }
        return false;
    }

    function init_images_sorting(){
        $( "#imagesTable" ).sortable({
            placeholder: "ui-state-highlight",
          handle: '.handle-sort'
        });
        $( "#imagesTable" ).disableSelection();

    }

    function calculatePricing1(){

        price_daily             = parseFloat(vvCurrencyToDecimal($('input[name="price_daily"]').val()));
        ambassador_commission    = parseFloat($('input[name="ambassador_commission"]').val());
        promocode_discount      = parseFloat($('input[name="promocode_discount"]').val());
        vv_fee                  = parseFloat(<?php echo vv_fee() ?>);

        console.log(ambassador_commission);

        if(price_daily > 0 && ambassador_commission > 0){
            ambassador_commission_val = price_daily * (ambassador_commission / 100);    
        }else{
            ambassador_commission_val = 0;
        }
        
        if(price_daily > 0 && promocode_discount > 0){
            promocode_discount_val  = price_daily * (promocode_discount / 100);    
            discount_price          = price_daily - promocode_discount_val;
            guest_price             = discount_price + (discount_price * (vv_fee/100));
            host_final_income       = discount_price - ambassador_commission_val;
        }else{
            promocode_discount_val  = 0;
            discount_price          = 0;
            guest_price             = price_daily + (price_daily * (vv_fee/100));
            host_final_income       = price_daily - ambassador_commission_val;
        }

        $('input[name="d_daily_price"]').val(vvFormatCurrency(price_daily,false));
        $('input[name="d_ambassador_commission_val"]').val(vvFormatCurrency(ambassador_commission_val,false));
        $('input[name="d_promocode_discount_val"]').val(vvFormatCurrency(promocode_discount_val,false));
        $('input[name="d_discount_price"]').val(vvFormatCurrency(discount_price,false));
        $('input[name="d_guest_price"]').val(vvFormatCurrency(guest_price,false));
        $('input[name="d_host_final_income"]').val(vvFormatCurrency(host_final_income,false));

        calculatePricing2();
    }
    function calculatePricing2(){

        let $table = $('.addon_discount');

        price_daily             = parseFloat(vvCurrencyToDecimal($('input[name="price_daily"]').val()));
        addon_days2             = parseFloat(vvCurrencyToDecimal($('input[name="addon_days2"]').val()));
        discount_3days          = parseFloat(vvCurrencyToDecimal($('input[name="discount_3days"]').val()));
        discount_5days          = parseFloat(vvCurrencyToDecimal($('input[name="discount_5days"]').val()));
        discount_7days          = parseFloat(vvCurrencyToDecimal($('input[name="discount_7days"]').val()));
        discount_30days         = parseFloat(vvCurrencyToDecimal($('input[name="discount_30days"]').val()));

        if(isNaN(addon_days2)) addon_days2 = 0;
        if(isNaN(discount_3days)) discount_3days = 0;
        if(isNaN(discount_5days)) discount_5days = 0;
        if(isNaN(discount_7days)) discount_7days = 0;
        if(isNaN(discount_30days)) discount_30days = 0;

        price_days2             = price_daily + ( price_daily * ( addon_days2 / 100) );

        price_3days             = price_daily;
        orig_price_3days        = price_daily * 3;
        out_price_3days        = (price_daily - ( price_daily * ( discount_3days / 100) )) * 3;

        price_5days             = price_daily;
        orig_price_5days        = price_daily * 5;
        out_price_5days        = (price_daily - ( price_daily * ( discount_5days / 100) )) * 5;

        price_7days             = price_daily;
        orig_price_7days        = price_daily * 7;
        out_price_7days        = (price_daily - ( price_daily * ( discount_7days / 100) )) * 7;

        price_30days             = price_daily;
        orig_price_30days       = price_daily * 30;
        out_price_30days       = (price_daily - ( price_daily * ( discount_30days / 100) )) * 30;


        $table.find('input[name="price_days1"]').val(vvFormatCurrency(price_daily,false));
        $table.find('input[name="orig_price_days1"]').val(vvFormatCurrency(price_daily,false));
        $table.find('input[name="out_price_days1"]').val(vvFormatCurrency(price_daily,false));

        $table.find('input[name="price_days2"]').val(vvFormatCurrency(price_days2,false));
        $table.find('input[name="orig_price_days2"]').val(vvFormatCurrency(price_days2,false));
        $table.find('input[name="out_price_days2"]').val(vvFormatCurrency(price_days2,false));

        $table.find('input[name="price_3days"]').val(vvFormatCurrency(price_3days,false));
        $table.find('input[name="orig_price_3days"]').val(vvFormatCurrency(orig_price_3days,false));
        $table.find('input[name="out_price_3days"]').val(vvFormatCurrency(out_price_3days,false));

        $table.find('input[name="price_5days"]').val(vvFormatCurrency(price_5days,false));
        $table.find('input[name="orig_price_5days"]').val(vvFormatCurrency(orig_price_5days,false));
        $table.find('input[name="out_price_5days"]').val(vvFormatCurrency(out_price_5days,false));

        $table.find('input[name="price_7days"]').val(vvFormatCurrency(price_7days,false));
        $table.find('input[name="orig_price_7days"]').val(vvFormatCurrency(orig_price_7days,false));
        $table.find('input[name="out_price_7days"]').val(vvFormatCurrency(out_price_7days,false));

        $table.find('input[name="price_30days"]').val(vvFormatCurrency(price_30days,false));
        $table.find('input[name="orig_price_30days"]').val(vvFormatCurrency(orig_price_30days,false));
        $table.find('input[name="out_price_30days"]').val(vvFormatCurrency(out_price_30days,false));
        $('select[name="currency"]').change(function(){
            $('.aparment_currency').html($(this).val());
        });


    }

    jQuery(document).ready(function (){

        $('#pricing_model').on('change', function(){
            if($(this).val() === 'seasonal'){
                $('#seasonal_pricing_wrap').show();
            }else{
                $('#seasonal_pricing_wrap').hide();
            }
        });

        function vvGenerateApartmentName(){
            var building = $('#apartment_building_id option:selected').data('name') || '';
            var feature = $('#distinguishing_feature').val() || '';
            var district = $('#apartment_district option:selected').text() || '';
            var type = $('#apartment_type').val() || '';
            district = district.replace(/^[^:]+:\s*/, '').trim();
            var parts = [];
            if(building) parts.push(building);
            if(feature) parts.push(feature);
            var name = parts.join('–');
            if(district) name += (name ? ' ' : '') + district;
            if(type) name += (name ? '–' : '') + type;
            if(name.length > 25) name = name.substring(0, 25);
            if(name) $('input[name="name"]').val(name).trigger('keyup');
        }
        $('#btnGenerateApartmentName').on('click', function(e){
            e.preventDefault();
            vvGenerateApartmentName();
        });

        $('.datepick, .datepick-offer').daterangepicker({
            timePicker: false,
            setStartDate : '',
            minDate: "<?php echo date("m/d/Y") ?>",
            minSpan: { "days" : 1 },
            autoApply: true,    
            singleDatePicker: true,
            locale: {
                format: 'MM/DD/YYYY',
                cancelLabel: 'Clear',
            },
            autoUpdateInput: false,
        });

        $('.datepick, .datepick-offer').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('MM/DD/YYYY'));
        });

        $('.btnAddImage').click(function (){
            $('#form-images').append('<div><input type="file" class="form-control" name="images[]" ></div>');
        });

        $('#food_category').change(function (){
            if($(this).val() > 0){
                $.get('<?php echo vv_admin_url() ?>/?action=get_foods_json&food_cat='+$(this).val(), function (data){
                    console.log(data);
                    if(data.status == 1){
                        Foods = data.data;
                        html = '';
                        for(i = 0; i < Foods.length; i++){
                            html += '<tr><td>';
                            if(Foods[i].image != '') html += '<img src="'+Foods[i].image+'" class="img-fluid" style="width:80px" >';
                            else html += '&nbsp;';
                            html += '</td>';
                            html += '<td>' + Foods[i].name+'</td>';
                            html += '<td>' + Foods[i].price + '</td>';
                            html += '<td class="text-right" ><button type="button" class="btn btn-sm btn-primary" onclick="addFood('+Foods[i].food_id+',this)" ><?php echo $vv_apartment_i18n['add']; ?></button></td>';
                            html += '</tr>';
                        }

                        if(html != ''){
                            html = '<table class="table border" >'+html+'</table>';
                        }else{
                            html = '<div class="alert alert-danger"><?php echo $vv_apartment_i18n['no_food_found']; ?></div>'
                        }
                        $('#addFoodList').html(html);
                    }
                });
            }
        });

        $('.btnAddCleanersChecklist').on('click',function (e){ 

            checklist_id = $(this).attr('data-checklist_id');
            name = $(this).attr('data-name');
            type = $(this).attr('data-type');
            qty = $(this).parent('td').parent('tr').find('input[name="qty_'+checklist_id+'"]').val();


            html = '<tr>';
            html += '<td>'+name+'</td>';
            if(type == 'qty'){
                html += '<td><input type="text" name="checklist_qty[]" autocomplete="no" class="form-control form-control-sm " value="'+qty+'" ></td>';
            }else{
                html += '<td><input type="hidden" name="checklist_qty[]" autocomplete="no" class="form-control form-control-sm " value="'+qty+'" ></td>';
            }
            html += '<td>';
            html += '<input type="hidden" name="checklist_name[]" value="'+name+'" >';
            html += '<input type="hidden" name="checklist_id[]" value="'+checklist_id+'" >';
            html += '<input type="hidden" name="checklist_type[]" value="'+type+'" >';
            html += '<a href="#" onclick="return removeCleanersChecklist(this)" ><i class="fa fa-trash"></i></a>';
            html += '</td>';
            html += '</tr>';

            $('.CleanersChecklistWrap').find('tbody').append(html);

            e.preventDefault();
        });

        <?php if(gArrayItem($apartment,'ID') > 0){ ?>

            // DropzoneJS Demo Code Start
            Dropzone.autoDiscover = false

            // Get the template HTML and remove it from the doumenthe template HTML and remove it from the doument
            var previewNode = document.querySelector("#addImageTemplate")
            previewNode.id = ""
            var previewTemplate = previewNode.parentNode.innerHTML
            previewNode.parentNode.removeChild(previewNode)

            var myDropzone = new Dropzone(document.body, { // Make the whole body a dropzone
                url: "<?php echo vv_admin_url()?>/?action=upload_apartment_image&id=<?php echo gArrayItem($apartment,'ID') ?>", // Set the url
                thumbnailWidth: 80,
                thumbnailHeight: 80,
                parallelUploads: 20,
                acceptedFiles: "image/jpeg,image/png,image/gif,image/webp",
                previewTemplate: previewTemplate,
                autoQueue: false, // Make sure the files aren't queued until manually added
                previewsContainer: "#previews", // Define the container to display the previews
                clickable: ".fileinput-button" // Define the element that should be used as click trigger to select files.
            })

            myDropzone.on("addedfile", function(file) {
                // Hookup the start button
                file.previewElement.querySelector(".start").onclick = function() { myDropzone.enqueueFile(file) }
            })

            // Update the total progress bar
            myDropzone.on("totaluploadprogress", function(progress) {
                document.querySelector("#total-progress .progress-bar").style.width = progress + "%"
            })

            myDropzone.on("sending", function(file) {
                // Show the total progress bar when upload starts
                document.querySelector("#total-progress").style.opacity = "1"
                // And disable the start button
                file.previewElement.querySelector(".start").setAttribute("disabled", "disabled")
            })

            // Hide the total progress bar when nothing's uploading anymore
            myDropzone.on("queuecomplete", function(progress) {
                $('#apartment_images_wrap').html('<img src="<?php echo vv_plugins_url() ?>admin/images/ajax-loader.gif" >');
                $.get('<?php echo vv_admin_url().'?action=get_apartment_images_html&id='.$apartment_id.'&t=' ?>' + new Date().getTime(), 
                    function (data){ 
                        $('#apartment_images_wrap').html(data);
                        $('#uploadImagesModal').modal('hide');
                        init_images_sorting();
                    }
                );
                
            })

            // Setup the buttons for all transfers
            // The "add files" button doesn't need to be setup because the config
            // `clickable` has already been specified.
            document.querySelector("#actions .start").onclick = function() {
                myDropzone.enqueueFiles(myDropzone.getFilesWithStatus(Dropzone.ADDED))
            }
            document.querySelector("#actions .cancel").onclick = function() {
                myDropzone.removeAllFiles(true)
            }
        <?php }else{ ?>
        <?php } ?>



        $('input[name="price_daily"]').blur(function (){
            val = $(this).val();
            val = val.replace(/\s+/g, '');
            $(this).val(vvFormatCurrency(val,false));
        });
        $('.calculatePricing1').change(function (){
            calculatePricing1();
        });

        $('.addon_discount').find('input').change(function (){
           calculatePricing2(); 
        });


        $('.update_display_name').change(function (){
            if($('input[name="display_name"]').is(":disabled")){
                rooms = parseInt($('input[name="rooms"]').val()) + parseInt($('input[name="num_beds"]').val());
                display_name = $('select[name="district"] option:selected').attr('data-district_label') + ', '+String(rooms)+'BR, '+$('input[name="name"]').val();
                $('input[name="display_name"]').val(display_name);
                $('input[name="display_name2"]').val(display_name);
            }
        });

        $('input[name="display_name"]').change(function (){
            $('input[name="display_name2"]').val($(this).val());
        });

        <?php if(trim(gArrayItem($apartment,'display_name')) == ''){ ?>
        $('.update_display_name').trigger('change');
        <?php } ?>

        $('.btnEditDisplayName').click(function (){
            $('input[name="display_name"]').prop('disabled',false);
            $('input[name="display_name"]').focus();
        });


        var hash = window.location.hash;
        if (hash) {
            $('#productTab a[href="' + hash + '"]').tab('show');
        }

        // Change hash in URL when tab is clicked
        $('#productTab a').on('shown.bs.tab', function (e) {
            history.replaceState(null, null, e.target.hash);
        });

        $('.timepicker').datetimepicker({
            format: 'HH:mm', // or 'HH:mm' for 24hr format
            defaultDate: moment().startOf('hour')
        });

        $('.map_field').change(function (){
            //initMap($(this));
        });
        //initMap(null);

        calculatePricing1();

        init_images_sorting();



    });
</script>
<?php 

$footer_codes .= ob_get_clean();
