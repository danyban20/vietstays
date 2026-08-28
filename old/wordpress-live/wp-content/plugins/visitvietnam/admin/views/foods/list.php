<?php 
global $wpdb;

$filter = [];


$foods      = vv_get_foods($filter);
$categories = vv_get_food_categories();
$delete_confirm = esc_js( vv__( 'Delete this food?' ) );

?>
<div class="row">
    <div class="col-md-8">
        <h1 class="mb-4"><?php vv_e( 'Foods' ); ?></h1>
    </div>
    <div class="col-md-4 text-right mb-4">
        <a href="<?php echo vv_admin_url('foods/add') ?>" class="btn btn-sm btn-primary"><?php vv_e( 'Add Food' ); ?></a>
    </div>
</div>
<div class="row">
    <div class="col">
        <div class="table-responsive table--no-card m-b-30">
            <table class="table table-borderless table-striped table-earning">
                <thead>
                    <tr>
                        <th style="width:100px"><?php vv_e( 'Image' ); ?></th>
                        <th><?php vv_e( 'Name' ); ?></th>
                        <th style="width:200px"><?php vv_e( 'Category' ); ?></th>
                        <th style="width:100px"><?php vv_e( 'Price' ); ?></th>
                        <th class="text-center" style="width:100px;"><?php vv_e( 'Action' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    foreach($foods as $food){
                        $category_names = array();
                        $food_cats = explode("|,|",trim($food['category'],'|'));
                        foreach($food_cats as $food_cat){
                            if(intval($food_cat) > 0){
                                foreach($categories as $category){
                                    if(gArrayItem($category,'food_id') == $food_cat){
                                        array_push($category_names,gArrayItem($category,'name'));
                                    }
                                }
                            }
                        }
                        ?>
                        <tr>
                            <td>
                                <?php 
                                if(gArrayItem($food,'image') != ''){
                                    ?>
                                    <img src="<?php echo $food['image'] ?>" >
                                    <?php 
                                }else{
                                    ?>
                                    &nbsp;
                                    <?php 
                                }
                                ?>
                            </td>
                            <td><a href="<?php echo vv_admin_url('foods/edit/?id='.$food['food_id']) ?>" ><?php echo $food['name'] ?></a></td>
                            <td><?php echo implode(", ",$category_names); ?></td>
                            <td>$<?php echo number_format($food['price'],2) ?></td>
                            <td class="text-right p-2">
                                <a href="<?php echo vv_admin_url('foods/edit/?id='.$food['food_id']) ?>" title="<?php echo esc_attr( vv__( 'Edit' ) ); ?>"><i class="fa fa-pencil-alt"></i></a>
                                <a href="<?php echo vv_admin_url('?action=delete-food&id='.$food['food_id']) ?>" onclick="return confirm('<?php echo $delete_confirm; ?>')" title="<?php echo esc_attr( vv__( 'Delete' ) ); ?>"><i class="fa fa-trash"></i></a>
                            </td>
                        </tr>
                        <?php 
                    }
                    ?>
                </tbody>
            </table>
        </div>

    </div><!-- .col -->
</div>
