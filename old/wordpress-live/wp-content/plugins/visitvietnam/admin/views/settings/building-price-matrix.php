<?php
if ( ! vv_is_admin_role() ) {
	echo '<div class="alert alert-danger">' . esc_html( vv__( 'Permission denied.' ) ) . '</div>';
	return;
}

$pg_title   = 'Building price matrix';
$meta_title = vv_admin_meta_title( $pg_title );
vv_show_page_title_bar( [
	'pg_title'  => $pg_title,
	'back_link' => vv_admin_url( 'settings/apartment-platform' ),
	'back_txt'  => 'Platform settings',
] );

$search   = sanitize_text_field( GET_Request( 'search' ) );
$buildings = vv_get_neighbourhoods( [ 'per_page' => 'all', 'orderby' => 'post_title ASC' ] );
$types    = vvApartmentPlatform::price_matrix_types();
$defaults = vvApartmentPlatform::get_settings();

if ( $search !== '' ) {
	$search_lc = strtolower( $search );
	$buildings = array_values(
		array_filter(
			$buildings,
			function ( $building ) use ( $search_lc ) {
				$bid   = intval( gArrayItem( $building, 'ID' ) );
				$title = strtolower( gArrayItem( $building, 'post_title' ) );
				$loc   = strtolower( vv_get_building_location_label( $bid ) );
				return strpos( $title, $search_lc ) !== false || strpos( $loc, $search_lc ) !== false;
			}
		)
	);
}
?>
<p class="text-muted"><?php vv_e( 'Set base nightly rates per building and apartment type. Host suggested prices use these values (plus quality level adjustment). Leave blank to fall back to global defaults.' ); ?></p>

<form method="get" class="form-inline mb-3">
    <input type="text" name="search" class="form-control form-control-sm mr-2" placeholder="<?php echo esc_attr( vv__( 'Filter buildings…' ) ); ?>" value="<?php echo esc_attr( $search ); ?>">
    <button type="submit" class="btn btn-sm btn-outline-secondary"><?php vv_e( 'Filter' ); ?></button>
    <?php if ( $search !== '' ) { ?>
        <a href="<?php echo esc_url( vv_admin_url( 'settings/building-price-matrix' ) ); ?>" class="btn btn-sm btn-light ml-2"><?php vv_e( 'Clear' ); ?></a>
    <?php } ?>
</form>

<form method="post">
    <input type="hidden" name="vv_action" value="save_building_price_matrix">
    <div class="table-responsive">
        <table class="table table-sm table-bordered vv-building-price-matrix">
            <thead class="thead-light">
                <tr>
                    <th><?php vv_e( 'Building' ); ?></th>
                    <th><?php vv_e( 'Location' ); ?></th>
                    <?php foreach ( $types as $type ) { ?>
                        <th><?php echo esc_html( $type ); ?></th>
                    <?php } ?>
                </tr>
            </thead>
            <tbody>
                <?php if ( count( $buildings ) === 0 ) { ?>
                    <tr><td colspan="<?php echo 2 + count( $types ); ?>" class="text-center text-muted py-4"><?php vv_e( 'No buildings found.' ); ?></td></tr>
                <?php } ?>
                <?php foreach ( $buildings as $building ) {
                    $bid    = intval( gArrayItem( $building, 'ID' ) );
                    $matrix = vvApartmentPlatform::get_building_price_matrix( $bid );
                    ?>
                    <tr>
                        <td>
                            <a href="<?php echo esc_url( vv_admin_url( 'locations/neighbourhoods/edit?id=' . $bid ) ); ?>"><?php echo esc_html( gArrayItem( $building, 'post_title' ) ); ?></a>
                        </td>
                        <td class="small text-muted"><?php echo esc_html( vv_get_building_location_label( $bid ) ); ?></td>
                        <?php foreach ( $types as $type ) {
                            $key     = vvApartmentPlatform::apartment_type_to_matrix_key( $type );
                            $default_key = $key === 'studio' ? '1br' : $key;
                            $placeholder = gArrayItem( $defaults, 'base_suggest_' . $default_key, '' );
                            $val = gArrayItem( $matrix, $key, '' );
                            ?>
                            <td style="min-width:88px">
                                <input type="number" step="0.01" min="0" class="form-control form-control-sm"
                                    name="building_matrix[<?php echo $bid; ?>][<?php echo esc_attr( $key ); ?>]"
                                    value="<?php echo $val !== '' ? esc_attr( $val ) : ''; ?>"
                                    placeholder="<?php echo esc_attr( $placeholder ); ?>">
                            </td>
                        <?php } ?>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
    <button type="submit" class="btn btn-primary btn-sm mt-2"><?php vv_e( 'Save matrix' ); ?></button>
</form>

<p class="small text-muted mt-3 mb-0">
    <?php vv_e( 'Global fallback rates are configured under Configuration → Price levels.' ); ?>
    <a href="<?php echo esc_url( vv_admin_url( 'settings/apartment-platform' ) ); ?>"><?php vv_e( 'Edit global defaults' ); ?></a>
</p>
