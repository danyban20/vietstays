<?php 
global $wpdb;

$filter = [];


$facilities     = vv_get_facilities($filter);
$delete_confirm = esc_js( vv__( 'Delete this facility?' ) );


?>
<h1><?php vv_e( 'Facilities' ); ?></h1>
<div class="form-wrap">
        <form method="post">
            <input type="hidden" name="action" value="vv_save_facility" >
            <div class="table-responsive table--no-card m-b-30">
                <table class="table table-borderless table-striped table-earning">
                    <thead>
                        <tr>
                            <th><?php vv_e( 'Name' ); ?></th>
                            <th class="text-center" style="width:50px;"><?php vv_e( 'Action' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        foreach($facilities as $facility){
                            ?>
                            <tr>
                                <td><input type="text" name="name[<?php echo $facility['facility_id'] ?>]" value="<?php echo $facility['name'] ?>" class="form-control form-control-sm" ></td>
                                <td class="text-right p-2">
                                    <a href="<?php echo vv_admin_url('?action=delete-facility&id='.$facility['facility_id']) ?>" onclick="return confirm('<?php echo $delete_confirm; ?>')" title="<?php echo esc_attr( vv__( 'Delete' ) ); ?>"><i class="fa fa-trash"></i></a>
                                </td>
                            </tr>
                            <?php 
                        }
                        ?>
                        <tr>
                            <td><input type="text" name="name['new']" value="" placeholder="<?php echo esc_attr( vv__( 'Add New' ) ); ?>" class="form-control form-control-sm" ></td>
                            <td class="text-right p-2">&nbsp;</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="mt-4 mb-4">
                <button type="submit" class="btn btn-primary"><?php vv_e( 'Save Facilities' ); ?></button>
            </div>
        </form>
</div>
