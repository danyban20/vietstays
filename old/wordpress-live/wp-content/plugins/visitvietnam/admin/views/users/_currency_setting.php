<?php
if ( ! function_exists( 'vv_can_edit_user_currency' ) || ! vv_can_edit_user_currency( $user_id ) ) {
	return;
}

$host_currency = vv_get_user_host_currency( $user_id );
?>
<div class="form-group customlabel mb-0">
	<label><?php vv_e( 'Default currency' ); ?></label>
	<p class="text-muted small mb-2"><?php vv_e( 'Prices and payouts for your listings use this currency.' ); ?></p>
	<select name="host_currency" class="form-control form-control-sm">
		<?php foreach ( vv_get_site_currencies() as $currency ) { ?>
			<option value="<?php echo esc_attr( $currency ); ?>"<?php selected( $host_currency, $currency ); ?>><?php echo esc_html( $currency ); ?></option>
		<?php } ?>
	</select>
</div>
