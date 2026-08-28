<?php 
global $wpdb;

$filter = $_GET;
if(!is_array($filter)) $filter = [];

if($logged_user_role == 'partner'){
    $filter["user_id"] = $logged_user_id;
}
$filter['status'] = 'all';


$apartments     = vv_get_apartments($filter);
$districts      = vv_get_districts();

$users      = array();
if($logged_user_role == 'administrator'){
    $owner_ids = array();
    foreach($apartments as $apartment){
        if(!in_array($apartment['user_id'],$owner_ids) && $apartment['user_id'] != 0) array_push($owner_ids,$apartment['user_id']);
    }
    foreach($owner_ids as $owner_id){
        array_push($users, get_user_by('ID',$owner_id));
    }
}

$delete_confirm = esc_js( vv__( 'Warning! Deleting apartment will delete the rooms under it. Delete this apartment?' ) );
$approve_confirm = esc_js( vv__( 'Publish this apartment?' ) );
$reject_confirm  = esc_js( vv__( 'Reject and return to draft?' ) );

?>
    <div class="row">
        <div class="col">
            <?php if($logged_user_role == 'administrator'){ ?>
                <div class="text-right mb-3">
                    <a href="<?php echo vv_admin_url('apartments/gantt') ?>" class="btn btn-sm btn-outline-secondary mr-1"><?php vv_e( 'Portfolio calendar' ); ?></a>
                    <a href="<?php echo vv_admin_url('apartments/add') ?>" class="btn btn-sm btn-primary mr-1"><?php vv_e( 'Add new apartment' ); ?></a>
                    <a href="<?php echo vv_admin_url('apartments/add-old') ?>" class="btn btn-sm btn-outline-secondary"><?php vv_e( 'Legacy editor' ); ?></a>
                </div>
            <?php } elseif ( $logged_user_role === 'partner' ) { ?>
                <div class="text-right mb-3">
                    <a href="<?php echo vv_admin_url('apartments/gantt') ?>" class="btn btn-sm btn-outline-secondary mr-1"><?php vv_e( 'Portfolio calendar' ); ?></a>
                    <a href="<?php echo vv_admin_url('apartments/add') ?>" class="btn btn-sm btn-primary"><?php vv_e( 'Add new apartment' ); ?></a>
                </div>
            <?php } ?>
            <div class="table-responsive table--no-card m-b-30">
                <table class="table table-borderless table-striped table-earning">
                    <thead>
                        <tr>
                            <th style="width:150px"><?php vv_e( 'ID' ); ?></th>
                            <th><?php vv_e( 'Name' ); ?></th>
                            <th style="width:200px;"><?php vv_e( 'District' ); ?></th>
                            <?php if($logged_user_role == 'administrator'){ ?>
                                <th style="width:200px;"><?php vv_e( 'Owner' ); ?></th>
                            <?php } ?>
                            <th class="text-center" style="width:100px;"><?php vv_e( '# of Rooms' ); ?></th>
                            <th class="text-center" style="width:100px;"><?php vv_e( 'Max Guests Allowed' ); ?></th>
                            <th class="text-center" style="width:100px;"><?php vv_e( 'Status' ); ?></th>
                            <th class="text-right" style="width:100px;"><?php vv_e( 'Pricing' ); ?></th>
                            <th class="text-center" style="width:100px;"><?php vv_e( 'Action' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        foreach($apartments as $apartment){
                            $district_name = vv_get_district_display_name(gArrayItem($apartment,'district'),$districts);

                            $owner = vv__( 'Visit Vietnam' );
                            foreach($users as $user){
                                if($user->ID == $apartment['user_id']) $owner = $user->display_name;
                            }

                            $price_daily = floatval( gArrayItem( $apartment, 'price_daily' ) );
                            $price_str   = $price_daily > 0 ? vv_number_format( $price_daily, true ) : '';
                            $status_key = gArrayItem( $apartment, 'status', 'active' );
                            $status_label = gArrayItem( vvApartments::status_labels(), $status_key, $status_key );
                            $edit_url = ( $logged_user_role === 'partner' || $logged_user_role === 'administrator' )
                                ? vv_admin_url( 'apartments/manage?id=' . $apartment['ID'] )
                                : vv_admin_url( 'apartments/edit/?id=' . $apartment['ID'] );
                            ?>
                            <tr>
                                <td><a href="<?php echo esc_url( $edit_url ); ?>" ><?php echo $apartment['apartment_num'] ?></a></td>
                                <td><a href="<?php echo esc_url( $edit_url ); ?>" ><?php echo stripslashes($apartment['name']) ?></a></td>
                                <td><?php echo $district_name ?></td>
                                <?php if($logged_user_role == 'administrator'){ ?>
                                    <td><?php echo esc_html( $owner ); ?></td>
                                <?php } ?>
                                <td class="text-center"><?php echo gArrayItem($apartment,'rooms') ?></td>
                                <td class="text-center"><?php echo gArrayItem($apartment,'max_guests') ?></td>
                                <td class="text-center"><span class="badge badge-<?php echo $status_key === 'active' ? 'success' : ( $status_key === 'pending' ? 'warning' : 'secondary' ); ?>"><?php echo esc_html( vv__( $status_label ) ); ?></span></td>
                                <td><?php echo $price_str ?></td>
                                <td class="text-right pr-2">
                                    <?php if ( $logged_user_role === 'administrator' && $status_key === 'pending' ) { ?>
                                        <a href="<?php echo esc_url( vv_admin_url( '?action=vv_approve_apartment&id=' . $apartment['ID'] ) ); ?>" class="mr-1 text-success" title="<?php echo esc_attr( vv__( 'Approve & publish' ) ); ?>" onclick="return confirm('<?php echo $approve_confirm; ?>')"><i class="fa fa-check"></i></a>
                                        <a href="<?php echo esc_url( vv_admin_url( '?action=vv_reject_apartment&id=' . $apartment['ID'] ) ); ?>" class="mr-1 text-danger" title="<?php echo esc_attr( vv__( 'Reject' ) ); ?>" onclick="return confirm('<?php echo $reject_confirm; ?>')"><i class="fa fa-times"></i></a>
                                    <?php } ?>
                                    <a href="<?php echo vv_get_apartment_url($apartment) ?>" class="mr-1" target="_blank" title="<?php echo esc_attr( vv__( 'View' ) ); ?>"><i class="fa fa-eye"></i></a>
                                    <a href="<?php echo esc_url( $edit_url ); ?>" title="<?php echo esc_attr( vv__( 'Edit' ) ); ?>"><i class="fa fa-pencil-alt"></i></a>
                                    <a href="<?php echo vv_admin_url('?action=delete-apartment&id='.$apartment['ID']) ?>" onclick="return confirm('<?php echo $delete_confirm; ?>')" title="<?php echo esc_attr( vv__( 'Delete' ) ); ?>"><i class="fa fa-trash"></i></a>
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
