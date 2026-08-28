<?php 
global $wpdb;

$filter = [];


$features     = vv_get_features($filter);
$delete_confirm = esc_js( vv__( 'Delete this feature?' ) );

?>
<h1><?php vv_e( 'Features' ); ?></h1>
<div class="form-wrap">
        <form method="post">
            <input type="hidden" name="action" value="vv_save_feature" >
            <div class="table-responsive table--no-card m-b-30">
                <table class="table table-borderless table-striped table-earning">
                    <thead>
                        <tr>
                            <th><?php vv_e( 'Name' ); ?></th>
                            <th><?php vv_e( 'Icon Url' ); ?></th>
                            <th class="text-center" style="width:50px;"><?php vv_e( 'Action' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        foreach($features as $feature){
                            ?>
                            <tr>
                                <td>
                                    <input type="hidden" name="feature_id[]" value="<?php echo $feature['feature_id'] ?>" class="form-control form-control-sm" >
                                    <input type="text" name="name[]" value="<?php echo $feature['name'] ?>" class="form-control form-control-sm" >
                                </td>
                                <td><input type="text" name="icon[]" value="<?php echo $feature['icon'] ?>" class="form-control form-control-sm" ></td>
                                <td class="text-right p-2">
                                    <a href="<?php echo vv_admin_url('?action=delete-feature&id='.$feature['feature_id']) ?>" onclick="return confirm('<?php echo $delete_confirm; ?>')" title="<?php echo esc_attr( vv__( 'Delete' ) ); ?>"><i class="fa fa-trash"></i></a>
                                </td>
                            </tr>
                            <?php 
                        }
                        ?>
                        <tr>
                            <td>
                                <input type="hidden" name="feature_id[]" value="new">
                                <input type="text" name="name[]" value="" placeholder="<?php echo esc_attr( vv__( 'Add New' ) ); ?>" class="form-control form-control-sm" >
                            </td>
                            <td><input type="text" name="icon[]" value="" placeholder="<?php echo esc_attr( vv__( 'Add New' ) ); ?>" class="form-control form-control-sm" ></td>
                            <td class="text-right p-2">&nbsp;</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="mt-4 mb-4">
                <button type="submit" class="btn btn-primary"><?php vv_e( 'Save Features' ); ?></button>
            </div>
        </form>
</div>
