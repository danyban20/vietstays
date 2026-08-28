<?php
if ( ! class_exists( 'vvI18n' ) || ! vvI18n::can_edit_user_locale( $user_id ) ) {
	return;
}

$stored_locale   = get_user_meta( $user_id, vvI18n::META_ADMIN_LOCALE, true );
$selected_locale = ( $stored_locale === '' || $stored_locale === 'auto' ) ? 'auto' : vvI18n::normalize_locale( $stored_locale );
$detected_locale = vvI18n::detect_locale_from_ip();
?>
<div class="form-group customlabel mb-0">
	<label><?php vv_e( 'Language' ); ?></label>
	<p class="text-muted small mb-2"><?php vv_e( 'Choose the language for the host/admin portal.' ); ?></p>
	<select name="admin_locale" class="form-control form-control-sm">
		<option value="auto"<?php selected( $selected_locale, 'auto' ); ?>><?php echo esc_html( vv__( 'Automatic (based on location)' ) . ' — ' . vvI18n::locale_label( $detected_locale ) ); ?></option>
		<?php foreach ( vvI18n::supported_locales() as $slug => $info ) { ?>
			<option value="<?php echo esc_attr( $slug ); ?>"<?php selected( $selected_locale, $slug ); ?>><?php echo esc_html( gArrayItem( $info, 'label' ) ); ?></option>
		<?php } ?>
	</select>
</div>
