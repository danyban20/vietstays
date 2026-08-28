<?php 
global $wpdb;

$pg_title   = 'Security Features';
$meta_title = vv_admin_meta_title( $pg_title );

$filter = [];


$security_features     = vv_get_security_features($filter);

$delete_confirm = esc_js( vv__( 'Delete this security feature?' ) );

?>
<h1><?php vv_e( 'Security Features' ); ?></h1>
<div class="form-wrap">
        <form method="post">
            <input type="hidden" name="action" value="vv_save_security_feature" >
            <div class="table-responsive table--no-card m-b-30">
                <table class="table table-borderless table-striped table-earning">
                    <thead>
                        <tr>
                            <th><?php vv_e( 'Name' ); ?></th>
                            <th><?php vv_e( 'Type' ); ?></th>
                            <th class="text-center" style="width:50px;"><?php vv_e( 'Action' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        foreach($security_features as $security_feature){
                            ?>
                            <tr>
                                <td><input type="text" name="name[<?php echo intval( $security_feature['security_feature_id'] ); ?>]" value="<?php echo esc_attr( $security_feature['name'] ); ?>" class="form-control form-control-sm" ></td>
                                <td>
                                    <select name="type[<?php echo intval( $security_feature['security_feature_id'] ); ?>]" class="form-control form-control-sm">
                                        <option value="apartment"><?php vv_e( 'Apartment' ); ?></option>
                                        <option value="location" <?php echo ($security_feature['type'] == 'location') ? 'selected' : '' ?> ><?php vv_e( 'Location' ); ?></option>
                                    </select>
                                </td>
                                <td class="text-right p-2">
                                    <a href="<?php echo esc_url( vv_admin_url('?action=delete-security_feature&id='.$security_feature['security_feature_id']) ); ?>" onclick="return confirm('<?php echo $delete_confirm; ?>')" ><i class="fa fa-trash"></i></a>
                                </td>
                            </tr>
                            <?php 
                        }
                        ?>
                        <tr>
                            <td><input type="text" name="name[new]" value="" placeholder="<?php echo esc_attr( vv__( 'Add New' ) ); ?>" class="form-control form-control-sm" ></td>
                            <td>
                                <select name="type[new]" class="form-control form-control-sm">
                                    <option value="apartment"><?php vv_e( 'Apartment' ); ?></option>
                                    <option value="location"><?php vv_e( 'Location' ); ?></option>
                                </select>
                            </td>
                            <td class="text-right p-2">&nbsp;</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="mt-4 mb-4">
                <button type="submit" class="btn btn-primary"><?php vv_e( 'Save Security Features' ); ?></button>
            </div>
        </form>
</div>
