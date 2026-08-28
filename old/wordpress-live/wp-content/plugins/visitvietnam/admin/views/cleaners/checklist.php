<?php 
global $wpdb;

$pg_title   = 'Cleaners Checklist';
$meta_title = vv_admin_meta_title( $pg_title );

$filter = [];


$cleaners_checklists     = vv_get_cleaners_checklists($filter);

$delete_confirm = esc_js( vv__( 'Delete this cleaners checklist item?' ) );

?>
<h1><?php vv_e( 'Cleaners Checklist' ); ?></h1>
<div class="form-wrap">
    <form method="post">
        <input type="hidden" name="action" value="vv_save_cleaners_checklist" >
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
                    foreach($cleaners_checklists as $cleaners_checklist){
                        ?>
                        <tr>
                            <td><input type="text" name="name[<?php echo intval( $cleaners_checklist['checklist_id'] ); ?>]" value="<?php echo esc_attr( $cleaners_checklist['name'] ); ?>" class="form-control form-control-sm" ></td>
                            <td>
                                <select name="type[<?php echo intval( $cleaners_checklist['checklist_id'] ); ?>]" class="form-control form-control-sm" >
                                    <option value="checkbox" <?php if($cleaners_checklist['type'] == 'checkbox' ) echo 'selected' ?> ><?php vv_e( 'Checkbox' ); ?></option>
                                    <option value="qty" <?php if($cleaners_checklist['type'] == 'qty' ) echo 'selected' ?> ><?php vv_e( 'Quantity' ); ?></option>
                                </select>                                                            
                            </td>
                            <td class="text-right p-2">
                                <a href="<?php echo esc_url( vv_admin_url('?action=delete-cleaners_checklist&id='.$cleaners_checklist['checklist_id']) ); ?>" onclick="return confirm('<?php echo $delete_confirm; ?>')" ><i class="fa fa-trash"></i></a>
                            </td>
                        </tr>
                        <?php 
                    }
                    ?>
                    <tr>
                        <td><input type="text" name="name['new']" value="" placeholder="<?php echo esc_attr( vv__( 'Add New' ) ); ?>" class="form-control form-control-sm" ></td>
                        <td>
                            <select name="type['new']" class="form-control form-control-sm" >
                                <option value="checkbox"><?php vv_e( 'Checkbox' ); ?></option>
                                <option value="qty"><?php vv_e( 'Quantity' ); ?></option>
                            </select>                                                            
                        </td>
                        <td class="text-right p-2">&nbsp;</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="mt-4 mb-4">
            <button type="submit" class="btn btn-primary"><?php vv_e( 'Save Checklist' ); ?></button>
        </div>
    </form>
</div>
