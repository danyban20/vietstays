<?php
global $wpdb;
$categories = vv_get_food_categories();
?>
<form method="post" enctype="multipart/form-data">
    <input type="hidden" name="action" value="vv_save_food">
    <input type="hidden" name="food_id" value="<?php echo intval(gArrayItem($food,'food_id')) ?>" >
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label class="control-label"><?php vv_e( 'Name' ); ?></label>
                <input type="text" name="name" class="form-control form-control-sm" value="<?php echo gArrayItem($food,'name') ?>" >
            </div>
            <div class="form-group">
                <label><?php vv_e( 'Category' ); ?></label>
                <div class="border p-2">
                    <div style="max-height:300px">
                        <?php 
                        foreach($categories as $category){
                            $food_cats = explode(",",str_replace("|",'',gArrayItem($food,'category')));
                            $checked = '';
                            foreach($food_cats as $food_cat){
                                if($food_cat == $category['food_category_id']) $checked = 'checked';
                            }
                            ?>
                            <div><input type="checkbox" name="category[]" value="<?php echo $category['food_category_id'] ?>" <?php echo $checked ?> > <?php echo $category['name'] ?></div>
                            <?php 
                        }
                        ?>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label"><?php vv_e( 'Price' ); ?></label>
                <input type="text" name="price" class="form-control form-control-sm" value="<?php echo gArrayItem($food,'price') ?>" >
            </div>
        </div>
        <div class="col-md-6">
            <?php 
            if(gArrayItem($food,'food_id') > 0 && gArrayItem($food,'image') != ''){
                ?>
                <label><?php vv_e( 'Image' ); ?></label>
                <div class="d-block">
                    <img src="<?php echo $food['image'] ?>" class="border p-2" >
                </div>
                <div><?php vv_e( 'Replace Image:' ); ?> <input type="file" id="file-input" name="file-input" class="form-control-file w-auto"></div>
                <?php 
            }else{
                ?>
                <?php vv_e( 'Upload New Image:' ); ?> <input type="file" id="file-input" name="file-input" class="form-control-file w-auto">
                <?php
            }
            ?>
        </div>
    </div>
    <div class="">
        <button type="submit" class="btn btn-primary pl-4 pr-4"><?php vv_e( 'Submit' ); ?></button> 
    </div>
</form>



