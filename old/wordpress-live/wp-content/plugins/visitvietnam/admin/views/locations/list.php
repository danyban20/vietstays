<?php 
global $wpdb;

$filter = [];

if(GET_Request('district_id') > 0) $filter['district_id'] = GET_Request('district_id');

$locations          = vv_get_locations($filter);
$cities             = vv_get_cities();
$facilities         = vv_get_facilities(['type' => 'location']);
$security_features  = vv_get_security_features(['type' => 'location']);
?>
<div class="row">
    <div class="col-md-8">
        <h1><?php vv_e( 'Locations' ); ?></h1>
    </div>
    <div class="col-md-4">
        <?php if($logged_user_role == 'administrator'){ ?>
            <div class="text-right mb-3">
                <a href="<?php echo vv_admin_url('locations/add') ?>" class="btn btn-sm btn-primary"><?php vv_e( 'Add Location' ); ?></a>
            </div>
        <?php } ?>
    </div>
</div>
<div>
    <form method="post">
        <input type="hidden" name="action" value="vv_save_location" >
        <div class="table-responsive table--no-card m-b-30">
            <table class="table table-borderless table-striped table-earning">
                <thead>
                    <tr>
                        <th><?php vv_e( 'Name' ); ?></th>
                        <th><?php vv_e( 'District' ); ?></th>
                        <th><?php vv_e( 'City' ); ?></th>
                        <th><?php vv_e( 'Facilities' ); ?></th>
                        <th><?php vv_e( 'Security Features' ); ?></th>
                        <th><?php vv_e( 'Apartments Listed' ); ?></th>
                        <th class="text-center" style="width:50px;"><?php vv_e( 'Action' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    foreach($locations as $location){
                        $location_id  = $location['ID'];
                        $district_id = $location['post_parent'];
                        $district = $this->district_class->get_district($district_id);

                        $locations = gArrayItem($location,'locations');
                        $city_name = '';
                        foreach($cities as $city){
                            if($city['ID'] == get_field('city',$district_id)){
                                $city_name = $city['post_title'];
                                break;
                            }
                        }

                        $facility_names = [];
                        $facility_ids = get_post_meta($location_id,'facilities',true);
                        if(is_array($facility_ids)){
                            foreach($facility_ids as $facility_id){
                                foreach($facilities as $facility){
                                    if($facility['facility_id'] == $facility_id) $facility_names[] = $facility['name'];
                                }
                            }
                        }

                        $security_features_names = [];
                        $security_features_ids = get_post_meta($location_id,'security_features',true);
                        if(is_array($security_features_ids)){
                            foreach($security_features_ids as $security_feature_id){
                                foreach($security_features as $security_feature){
                                    if($security_feature['security_feature_id'] == $security_feature_id) $security_features_names[] = $security_feature['name'];
                                }
                            }
                        }

                        $num_apartments = gArrayItem($wpdb->get_row("SELECT COUNT(*) as num_apartments FROM vv_apartments WHERE district IN (".implode(",",[$location_id,$district_id]).") ",ARRAY_A),'num_apartments');

                        ?>
                        <tr>
                            <td><?php echo $location['post_title'] ?></td>
                            <td><?php echo gArrayItem($district,'post_title') ?></td>
                            <td><?php echo $city_name ?></td>
                            <td><?php echo implode(", ",$facility_names) ?></td>
                            <td><?php echo implode(", ",$security_features_names) ?></td>
                            <td class="text-center">
                                <?php echo $num_apartments ?>
                            </td>
                            <td class="text-right p-2">
                                <a href="<?php echo vv_admin_url('locations/edit').'?id='.$location['ID'] ?>" target="_blank" title="<?php echo esc_attr( vv__( 'Edit' ) ); ?>"><i class="fa fa-pencil-alt"></i></a>
                            </td>
                        </tr>
                        <?php 
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </form>
</div>
