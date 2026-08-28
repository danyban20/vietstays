<?php
$settings    = vvApartmentPlatform::get_settings();
$house_rules = vvApartmentPlatform::get_house_rules();
?>
<h1><?php vv_e( 'Apartment platform settings' ); ?></h1>

<div class="row">
    <div class="col-lg-6">
        <div class="card mb-4">
            <div class="card-header"><h4 class="mb-0"><?php vv_e( 'Pricing levels & publishing' ); ?></h4></div>
            <div class="card-body">
                <form method="post">
                    <input type="hidden" name="vv_action" value="save_apartment_platform_settings">
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label><?php vv_e( 'Low level %' ); ?></label>
                            <input type="number" step="0.1" name="level_low_pct" class="form-control form-control-sm" value="<?php echo esc_attr( $settings['level_low_pct'] ); ?>">
                        </div>
                        <div class="form-group col-md-4">
                            <label><?php vv_e( 'Normal level %' ); ?></label>
                            <input type="number" step="0.1" name="level_normal_pct" class="form-control form-control-sm" value="<?php echo esc_attr( $settings['level_normal_pct'] ); ?>">
                        </div>
                        <div class="form-group col-md-4">
                            <label><?php vv_e( 'High level %' ); ?></label>
                            <input type="number" step="0.1" name="level_high_pct" class="form-control form-control-sm" value="<?php echo esc_attr( $settings['level_high_pct'] ); ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label><?php vv_e( 'Minimum images to publish' ); ?></label>
                        <input type="number" name="min_images_publish" class="form-control form-control-sm" style="max-width:120px" value="<?php echo esc_attr( $settings['min_images_publish'] ); ?>">
                    </div>
                    <h5 class="mt-3"><?php vv_e( 'Suggested base daily rates' ); ?></h5>
                    <?php foreach ( [ '1br', '2br', '3br', '4br' ] as $br ) { ?>
                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label"><?php echo strtoupper( $br ); ?></label>
                            <div class="col-sm-4"><input type="number" step="0.01" name="base_suggest_<?php echo esc_attr( $br ); ?>" class="form-control form-control-sm" value="<?php echo esc_attr( $settings[ 'base_suggest_' . $br ] ); ?>"></div>
                            <div class="col-sm-2"><input type="number" step="0.01" name="price_min_<?php echo esc_attr( $br ); ?>" class="form-control form-control-sm" placeholder="Min" value="<?php echo esc_attr( $settings[ 'price_min_' . $br ] ); ?>"></div>
                            <div class="col-sm-2"><input type="number" step="0.01" name="price_max_<?php echo esc_attr( $br ); ?>" class="form-control form-control-sm" placeholder="Max" value="<?php echo esc_attr( $settings[ 'price_max_' . $br ] ); ?>"></div>
                        </div>
                    <?php } ?>
                    <h5 class="mt-3"><?php vv_e( 'Standard cleaning fees' ); ?></h5>
                    <?php foreach ( [ '1br', '2br', '3br', '4br' ] as $br ) { ?>
                        <div class="form-group row">
                            <label class="col-sm-3 col-form-label"><?php echo strtoupper( $br ); ?></label>
                            <div class="col-sm-4"><input type="number" step="0.01" name="cleaning_<?php echo esc_attr( $br ); ?>" class="form-control form-control-sm" value="<?php echo esc_attr( $settings[ 'cleaning_' . $br ] ); ?>"></div>
                        </div>
                    <?php } ?>
                    <button type="submit" class="btn btn-primary btn-sm"><?php vv_e( 'Save settings' ); ?></button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card mb-4">
            <div class="card-header"><h4 class="mb-0"><?php vv_e( 'House rules checklist' ); ?></h4></div>
            <div class="card-body">
                <p class="text-muted small"><?php vv_e( 'Predefined rules hosts select per apartment. Displayed to guests in multiple languages when translations are added.' ); ?></p>
                <form method="post">
                    <input type="hidden" name="vv_action" value="save_house_rules_settings">
                    <table class="table table-sm">
                        <?php foreach ( $house_rules as $rule ) { ?>
                            <tr>
                                <td><input type="hidden" name="rule_id[]" value="<?php echo esc_attr( gArrayItem( $rule, 'id' ) ); ?>"><input type="text" name="rule_label[]" class="form-control form-control-sm" value="<?php echo esc_attr( gArrayItem( $rule, 'label' ) ); ?>"></td>
                            </tr>
                        <?php } ?>
                        <tr>
                            <td><input type="text" name="rule_label_new" class="form-control form-control-sm" placeholder="<?php echo esc_attr( vv__( 'Add new rule' ) ); ?>"></td>
                        </tr>
                    </table>
                    <button type="submit" class="btn btn-primary btn-sm"><?php vv_e( 'Save house rules' ); ?></button>
                </form>
            </div>
        </div>
        <div class="card mb-4">
            <div class="card-body">
                <p class="mb-2"><?php vv_e( 'Manage building entities (name, amenities, district) under Locations → Neighbourhoods.' ); ?></p>
                <a href="<?php echo esc_url( vv_admin_url( 'locations/neighbourhoods' ) ); ?>" class="btn btn-sm btn-outline-secondary"><?php vv_e( 'Manage buildings' ); ?></a>
            </div>
        </div>
    </div>
</div>
