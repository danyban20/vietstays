<?php 
global $wpdb;

$filter = ['apartment_id' => GET_Request('apartment')];

$apartment = $this->apartment_class->get_apartment(GET_Request('apartment'));

if($logged_user_role == 'partner'){

    if($apartment['user_id'] != $logged_user_id){        
        die( esc_html( vv__( 'Invalid URL' ) ) );
    }

}

$rooms = $this->room_class->get_rooms($filter);
$delete_confirm = esc_js( vv__( 'Delete this room?' ) );


?>
<div class="row mb-4">
    <div class="col-md-8">
        <h1><?php vv_e( 'Rooms of Apartment:' ); ?> <span class="text-info"><?php echo esc_html( gArrayItem($apartment,'name') ) ?></span></h1>
    </div>
    <div class="col-md-4">
        <div class="text-right mb-3">
            <a href="<?php echo vv_admin_url('apartments') ?>" class="btn btn-sm btn-secondary"><?php vv_e( 'Back to Apartments List' ); ?></a>
            <a href="<?php echo vv_admin_url('rooms/add/?apartment='.$apartment['ID']) ?>" class="btn btn-sm btn-primary"><?php vv_e( 'Add Room' ); ?></a>
        </div>
    </div>
</div>
<div class="row">
    <div class="col">
        <div class="table-responsive table--no-card m-b-30">
            <table class="table table-borderless table-striped table-earning">
                <thead>
                    <tr>
                        <th><?php vv_e( 'Name' ); ?></th>
                        <th><?php vv_e( 'Adult' ); ?></th>
                        <th><?php vv_e( 'Child' ); ?></th>
                        <th><?php vv_e( 'Extra' ); ?></th>
                        <th><?php vv_e( 'Price' ); ?></th>
                        <th class="text-right"><?php vv_e( 'Action' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    foreach($rooms as $room){
                        $pricing = json_decode($room['pricing'],true);
                        if(!is_array($pricing)) $pricing = array();

                        if(count($pricing) > 1){
                            for($i = 0; $i < count($pricing); $i++){
                                for($x = 0; $x < count($pricing)-1; $x++){
                                    if($pricing[$x] > $pricing[$x+1]){
                                        $tmp = $pricing[$x];
                                        $pricing[$x] = $pricing[$x+1];
                                        $pricing[$x+1] = $tmp;
                                    }
                                }
                            }
                            if(gArrayItem($pricing,0) != gArrayItem($pricing,count($pricing)-1)){
                                $price_str = '$'.gArrayItem($pricing,0).' - $'.gArrayItem($pricing,count($pricing)-1);
                            }else{
                                $price_str = '$'.gArrayItem($pricing,0);
                            }
                        }else{
                            $price_str = '$'.gArrayItem($pricing,0);
                        }



                        ?>
                        <tr>
                            <td><a href="<?php echo vv_admin_url('rooms/edit?id='.$room['ID']) ?>" ><?php echo $room['name'] ?></td></td>
                            <td><?php echo $room['adults'] ?></td>
                            <td><?php echo $room['children'] ?></td>
                            <td><?php echo $room['extra'] ?></td>
                            <td><?php echo $price_str ?></td>
                            <td class="text-right">
                                <a href="<?php echo vv_admin_url('rooms/edit?id='.$room['ID']) ?>" title="<?php echo esc_attr( vv__( 'Edit' ) ); ?>"><i class="fa fa-pencil-alt"></i></a>
                                <a href="<?php echo vv_admin_url('?action=delete-room&id='.$room['ID']) ?>" onclick="return confirm('<?php echo $delete_confirm; ?>')" title="<?php echo esc_attr( vv__( 'Delete' ) ); ?>"><i class="fa fa-trash"></i></a>
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
