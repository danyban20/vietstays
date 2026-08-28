<?php 
global $wpdb;

$filter = [];


$cities     = vv_get_cities($filter);


?>
<div class="row">
    <div class="col-md-8">
        <h1><?php vv_e( 'Cities' ); ?></h1>
    </div>
    <div class="col-md-4">
        <?php if($logged_user_role == 'administrator'){ ?>
            <div class="text-right mb-3">
                <a href="<?php echo get_bloginfo('url').'/wp-admin/post-new.php?post_type=city' ?>" class="btn btn-sm btn-primary"><?php vv_e( 'Add City' ); ?></a>
            </div>
        <?php } ?>
    </div>
</div>
<div class="form-wrap">
    <form method="post">
        <input type="hidden" name="action" value="vv_save_city" >
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
                    foreach($cities as $city){
                        ?>
                        <tr>
                            <td><?php echo $city['post_title'] ?></td>
                            <td class="text-right p-2">
                                <a href="<?php echo get_bloginfo('url').'/wp-admin/post.php?post='.$city['ID'].'&action=edit' ?>" target="_blank" title="<?php echo esc_attr( vv__( 'Edit' ) ); ?>"><i class="fa fa-pencil-alt"></i></a>
                                <a href="<?php echo get_the_permalink($city['ID']) ?>" target="_blank" title="<?php echo esc_attr( vv__( 'View' ) ); ?>"><i class="fa fa-eye"></i></a>
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
