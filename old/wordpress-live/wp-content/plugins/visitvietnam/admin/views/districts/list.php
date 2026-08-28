<?php 
global $wpdb;

$filter = [];


$districts     = vv_get_districts($filter);
$cities        = vv_get_cities();

?>
<div class="row">
    <div class="col-md-8">
        <h1><?php vv_e( 'Districts' ); ?></h1>
    </div>
    <div class="col-md-4">
        <?php if($logged_user_role == 'administrator'){ ?>
            <div class="text-right mb-3">
                <a href="<?php echo vv_admin_url('districts/add') ?>" class="btn btn-sm btn-primary"><?php vv_e( 'Add District' ); ?></a>
            </div>
        <?php } ?>
    </div>
</div>
<div class="form-wrap">
    <form method="post">
        <input type="hidden" name="action" value="vv_save_district" >
        <div class="table-responsive table--no-card m-b-30">
            <table class="table table-borderless table-striped table-earning">
                <thead>
                    <tr>
                        <th><?php vv_e( 'District Name' ); ?></th>
                        <th><?php vv_e( 'City' ); ?></th>
                        <th><?php vv_e( 'Locations' ); ?></th>
                        <th class="text-center" style="width:50px;"><?php vv_e( 'Action' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    foreach($districts as $district){
                        $district_id  = $district['ID'];
                        $locations = gArrayItem($district,'locations');
                        $city_name = '';
                        foreach($cities as $city){
                            if($city['ID'] == get_field('city',$district_id)){
                                $city_name = $city['post_title'];
                                break;
                            }
                        }
                        ?>
                        <tr>
                            <td><?php echo $district['post_title'] ?></td>
                            <td><?php echo $city_name ?></td>
                            <td class="text-center">
                                <a href="<?php echo vv_admin_url('locations').'?district_id='.$district_id ?> "><?php echo count($locations) ?></a>
                            </td>
                            <td class="text-right p-2">
                                <a href="<?php echo vv_admin_url('districts/edit').'?id='.$district['ID'] ?>" target="_blank" title="<?php echo esc_attr( vv__( 'Edit' ) ); ?>"><i class="fa fa-pencil-alt"></i></a>
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
