<?php 
global $wpdb;

$filter = [];


$categories     = vv_get_food_categories($filter);
$delete_confirm = esc_js( vv__( 'Delete this category?' ) );


?>
<h1 class="mb-4"><?php vv_e( 'Food Categories' ); ?></h1>
<div class="form-wrap">
    <form method="post">
        <input type="hidden" name="action" value="vv_save_food_categories" >
        <div class="table-responsive table--no-card m-b-30">
            <table class="table table-borderless table-striped table-earning">
                <thead>
                    <tr>
                        <th><?php vv_e( 'Name' ); ?></th>
                        <th class="text-center" style="width:100px;"><?php vv_e( 'Action' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    foreach($categories as $category){
                        ?>
                        <tr>
                            <td><input type="text" name="name[<?php echo $category['food_category_id'] ?>]" value="<?php echo $category['name'] ?>" class="form-control form-control-sm" ></td>
                            <td class="text-right p-2">
                                <a href="<?php echo vv_admin_url('?action=delete-food_category&id='.$category['food_category_id']) ?>" onclick="return confirm('<?php echo $delete_confirm; ?>')" title="<?php echo esc_attr( vv__( 'Delete' ) ); ?>"><i class="fa fa-trash"></i></a>
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
            <button type="submit" class="btn btn-primary"><?php vv_e( 'Save Categories' ); ?></button>
        </div>
    </form>
</div>
