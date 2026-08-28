<?php 
global $wpdb;

$pg_title   = 'Facilities';
$meta_title = vv_admin_meta_title( $pg_title );

$filter = [];


$facilities     = vv_get_facilities($filter);

$delete_confirm = esc_js( vv__( 'Delete this facility?' ) );

?>
<div class="row">
    <div class="col-md-6">
        <h3 class="mb-3"><?php vv_e( 'Apartment Facilities' ); ?></h3>
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
                            if($facility['type'] != 'location'){
                                ?>
                                <tr>
                                    <td>
                                        <input type="text" name="name[<?php echo intval( $facility['facility_id'] ); ?>]" value="<?php echo esc_attr( $facility['name'] ); ?>" class="form-control form-control-sm" >
                                        <input type="hidden" name="type[<?php echo intval( $facility['facility_id'] ); ?>]" value="apartment" >
                                    </td>
                                    <td class="text-right p-2">
                                        <a href="<?php echo esc_url( vv_admin_url('?action=delete-facility&id='.$facility['facility_id']) ); ?>" onclick="return confirm('<?php echo $delete_confirm; ?>')" ><i class="fa fa-trash"></i></a>
                                    </td>
                                </tr>
                                <?php 
                            }
                        }
                        ?>
                        <tr>
                            <td>
                                <input type="text" name="name[new]" value="" placeholder="<?php echo esc_attr( vv__( 'Add New' ) ); ?>" class="form-control form-control-sm" >
                                <input type="hidden" name="type[new]" value="apartment" >
                            </td>
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
    <div class="col-md-6">
        <h3 class="mb-3"><?php vv_e( 'Location Facilities' ); ?></h3>
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
                            if($facility['type'] == 'location'){
                                ?>
                                <tr>
                                    <td>
                                        <input type="text" name="name[<?php echo intval( $facility['facility_id'] ); ?>]" value="<?php echo esc_attr( $facility['name'] ); ?>" class="form-control form-control-sm" >
                                        <input type="hidden" name="type[<?php echo intval( $facility['facility_id'] ); ?>]" value="location" >
                                    </td>
                                    <td class="text-right p-2">
                                        <a href="<?php echo esc_url( vv_admin_url('?action=delete-facility&id='.$facility['facility_id']) ); ?>" onclick="return confirm('<?php echo $delete_confirm; ?>')" ><i class="fa fa-trash"></i></a>
                                    </td>
                                </tr>
                                <?php 
                            }
                        }
                        ?>
                        <tr>
                            <td>
                                <input type="text" name="name[new]" value="" placeholder="<?php echo esc_attr( vv__( 'Add New' ) ); ?>" class="form-control form-control-sm" >
                                <input type="hidden" name="type[new]" value="location" >
                            </td>
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
</div>
