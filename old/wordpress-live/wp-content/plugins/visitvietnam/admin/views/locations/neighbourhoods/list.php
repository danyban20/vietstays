<?php
global $wpdb;

$filter = [];

if ( GET_Request( 'district_id' ) > 0 ) {
	$filter['district_id'] = GET_Request( 'district_id' );
}

$neighbourhoods     = vv_get_neighbourhoods( $filter );
$cities             = vv_get_cities();
$facilities         = vv_get_facilities( [ 'type' => 'location' ] );
$security_features  = vv_get_security_features( [ 'type' => 'location' ] );
$neighbourhoods_class = new vvNeighbourhoods();
?>
<div class="row">
    <div class="col-md-8">
        <h1><?php vv_e( 'Buildings / neighbourhoods' ); ?></h1>
        <p class="text-muted small mb-0"><?php vv_e( 'Admin-managed buildings. Hosts select from this list when creating apartments.' ); ?></p>
    </div>
    <div class="col-md-4">
        <?php if ( $logged_user_role === 'administrator' || $logged_user_role === 'sales_support' ) { ?>
            <div class="text-right mb-3">
                <a href="<?php echo esc_url( vv_admin_url( 'locations/neighbourhoods/add' ) ); ?>" class="btn btn-sm btn-primary"><?php vv_e( 'Add building' ); ?></a>
            </div>
        <?php } ?>
    </div>
</div>
<div>
    <div class="table-responsive table--no-card m-b-30">
        <table class="table table-borderless table-striped table-earning">
            <thead>
                <tr>
                    <th><?php vv_e( 'Building' ); ?></th>
                    <th><?php vv_e( 'District' ); ?></th>
                    <th><?php vv_e( 'City' ); ?></th>
                    <th><?php vv_e( 'Gallery photos' ); ?></th>
                    <th><?php vv_e( 'Facilities' ); ?></th>
                    <th class="text-center"><?php vv_e( 'Apartments' ); ?></th>
                    <th class="text-center" style="width:50px;"><?php vv_e( 'Action' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ( $neighbourhoods as $neighbourhood ) {
                    $neighbourhood_id = intval( $neighbourhood['ID'] );
                    $district_id      = intval( $neighbourhood['post_parent'] );
                    $district         = $this->district_class->get_district( $district_id );

                    $city_name = '';
                    foreach ( $cities as $city ) {
                        if ( intval( $city['ID'] ) === intval( get_field( 'city', $district_id ) ) ) {
                            $city_name = $city['post_title'];
                            break;
                        }
                    }

                    $facility_names = [];
                    $facility_ids   = get_post_meta( $neighbourhood_id, 'facilities', true );
                    if ( is_array( $facility_ids ) ) {
                        foreach ( $facility_ids as $facility_id ) {
                            foreach ( $facilities as $facility ) {
                                if ( intval( gArrayItem( $facility, 'facility_id' ) ) === intval( $facility_id ) ) {
                                    $facility_names[] = gArrayItem( $facility, 'name' );
                                }
                            }
                        }
                    }

                    $gallery_count  = count( $neighbourhoods_class->get_building_gallery_items( $neighbourhood_id ) );
                    $num_apartments = intval(
                        gArrayItem(
                            $wpdb->get_row(
                                'SELECT COUNT(*) as num_apartments FROM vv_apartments WHERE building_id = ' . intval( $neighbourhood_id ),
                                ARRAY_A
                            ),
                            'num_apartments'
                        )
                    );
                    ?>
                    <tr>
                        <td><?php echo esc_html( $neighbourhood['post_title'] ); ?></td>
                        <td><?php echo esc_html( gArrayItem( $district, 'post_title' ) ); ?></td>
                        <td><?php echo esc_html( $city_name ); ?></td>
                        <td><?php echo intval( $gallery_count ); ?></td>
                        <td><?php echo esc_html( implode( ', ', array_slice( $facility_names, 0, 4 ) ) . ( count( $facility_names ) > 4 ? '…' : '' ) ); ?></td>
                        <td class="text-center"><?php echo intval( $num_apartments ); ?></td>
                        <td class="text-right p-2">
                            <a href="<?php echo esc_url( vv_admin_url( 'locations/neighbourhoods/edit' ) . '?id=' . $neighbourhood_id ); ?>" title="<?php echo esc_attr( vv__( 'Edit' ) ); ?>"><i class="fa fa-pencil-alt"></i></a>
                        </td>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
        </table>
    </div>
</div>
