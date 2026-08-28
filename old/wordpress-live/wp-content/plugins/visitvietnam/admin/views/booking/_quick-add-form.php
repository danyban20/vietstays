<?php
/**
 * Quick-add booking fields — host modal + add page.
 *
 * Expects: $host_apartments (array), optional $form_id, $wrap_class.
 */
$form_id    = isset( $form_id ) ? $form_id : 'bookings2026QuickAddForm';
$wrap_class = isset( $wrap_class ) ? $wrap_class : '';
$apt_select_id = isset( $apt_select_id ) ? $apt_select_id : 'bookings2026Apartment';
$price_input_id = isset( $price_input_id ) ? $price_input_id : 'bookings2026DailyPrice';
?>
<div class="<?php echo esc_attr( $wrap_class ); ?>">
    <div class="form-group">
        <label class="vv-apt2026-label"><?php vv_e( 'Apartment' ); ?></label>
        <select name="apartment_id" id="<?php echo esc_attr( $apt_select_id ); ?>" class="form-control vv-apt2026-field" required>
            <option value=""><?php vv_e( 'Select apartment…' ); ?></option>
            <?php foreach ( $host_apartments as $apt ) {
                $price_daily = floatval( gArrayItem( $apt, 'price_daily' ) );
                ?>
                <option value="<?php echo intval( $apt['ID'] ); ?>" data-price="<?php echo esc_attr( $price_daily ); ?>"><?php echo esc_html( stripslashes( gArrayItem( $apt, 'name' ) ) ); ?></option>
            <?php } ?>
        </select>
    </div>
    <div class="row vv-apt2026-form-row">
        <div class="col-md-6">
            <div class="form-group">
                <label class="vv-apt2026-label"><?php vv_e( 'Check-in' ); ?></label>
                <input type="date" name="check_in_date" class="form-control vv-apt2026-field" required>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label class="vv-apt2026-label"><?php vv_e( 'Check-out' ); ?></label>
                <input type="date" name="check_out_date" class="form-control vv-apt2026-field" required>
            </div>
        </div>
    </div>
    <div class="row vv-apt2026-form-row">
        <div class="col-md-6">
            <div class="form-group">
                <label class="vv-apt2026-label"><?php vv_e( 'Guest first name' ); ?></label>
                <input type="text" name="firstname" class="form-control vv-apt2026-field" required maxlength="120">
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label class="vv-apt2026-label"><?php vv_e( 'Guest last name' ); ?></label>
                <input type="text" name="lastname" class="form-control vv-apt2026-field" maxlength="120">
            </div>
        </div>
    </div>
    <div class="row vv-apt2026-form-row">
        <div class="col-md-6">
            <div class="form-group">
                <label class="vv-apt2026-label"><?php vv_e( 'Email' ); ?> <span class="vv-apt2026-label-note"><?php vv_e( 'optional' ); ?></span></label>
                <input type="email" name="email" class="form-control vv-apt2026-field" maxlength="190">
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label class="vv-apt2026-label"><?php vv_e( 'Phone' ); ?> <span class="vv-apt2026-label-note"><?php vv_e( 'optional' ); ?></span></label>
                <input type="text" name="phone" class="form-control vv-apt2026-field" maxlength="40">
            </div>
        </div>
    </div>
    <div class="row vv-apt2026-form-row">
        <div class="col-md-4">
            <div class="form-group mb-0">
                <label class="vv-apt2026-label"><?php vv_e( 'Adults' ); ?></label>
                <input type="number" name="adults" class="form-control vv-apt2026-field" min="1" value="2">
            </div>
        </div>
        <div class="col-md-8">
            <div class="form-group mb-0">
                <label class="vv-apt2026-label"><?php vv_e( 'Daily rate' ); ?></label>
                <input type="number" name="daily_price" id="<?php echo esc_attr( $price_input_id ); ?>" class="form-control vv-apt2026-field" min="0" step="0.01" placeholder="<?php echo esc_attr( vv__( 'Uses apartment rate if empty' ) ); ?>">
            </div>
        </div>
    </div>
</div>
