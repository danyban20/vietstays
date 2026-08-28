<div class="row">
    <div class="col">
      <div class="au-card" style="overflow: auto;">
        <div class="table-responsive table--no-card m-b-30">
            <table class="table table-borderless table-striped table-earning Customers_list">
                <thead>
                    <tr>
                        <th style="width:20px">
                            <input name="checkAll" type="checkbox" value="1" >
                        </th>
                        <th class="text-left"><?php vv_e( 'Email' ); ?></th>
                        <th class="text-left"><?php vv_e( 'Name' ); ?></th>
                        <th class="text-left"><?php vv_e( 'Level' ); ?></th>
                        <th class="text-left"><?php vv_e( 'Position' ); ?></th>
                        <th class="text-left"><?php vv_e( 'Apartments' ); ?></th>
                        <th class="text-left"><?php vv_e( 'Bookings' ); ?></th>
                        <th style="width:150px"><?php vv_e( 'Status' ); ?></th>
                        <th style="width:40px">&nbsp;</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    foreach($staffs as $staff){
                        $staff_id    = $staff['ID'];
                        $data       = (array) $staff['data'];
                        $status     = vv_get_status_name($data['user_status']);

                        $position = get_user_meta($staff_id,'vv_staff_position',true);
                        //if($position == '') $position = 'customer';

                        $user_level = get_user_meta($staff_id,'vv_user_level',true);
                        if($user_level == '') $user_level = 'customer';
                        $staff_level = $user_level;

                        $num_apartments = 0;
                        if($staff_level == 'partner' || $staff_level == 'administration'){
                            $num_apartments = $this->apartment_class->get_num_apartments($staff_id);
                        }

                        $num_bookings = $this->booking_class->get_num_bookings($staff_id);

                        //echo print_r_pre($staff);
                        $host_num = date("Ymd",strtotime($data['user_registered'])).$staff_id;

                        $folder = $staff_level;
                        if($folder == '') $folder = 'staff';
                        $folder .= 's';

                        ?>
                        <tr data-Customer_id="<?php echo $staff_id ?>">
                            <td>
                                <input type="checkbox" name="user_id[]" id="user_<?php echo $staff_id ?>" value="<?php echo $staff_id ?>" required>
                            </td>
                            <td><a href="<?php echo vv_admin_url().$folder.'/edit?id='.$staff_id ?>" ><?php echo $data['user_email'] ?></a></td>
                            <td><?php echo stripslashes($data['display_name']) ?></td>
                            <td><?php echo ucwords(str_replace("_"," ",$user_level)) ?></td>
                            <td><?php echo ucwords(str_replace("_"," ",$position)) ?></td>
                            <td><a href="<?php echo vv_admin_url('apartments').'?user_id='.$staff_id ?>" target="_blank" ><?php echo $num_apartments ?></a></td>
                            <td><a href="<?php echo vv_admin_url('booking').'?user_id='.$staff_id ?>" target="_blank" ><?php echo $num_bookings ?></a></td>
                            <td><span class="badge status-<?php echo strtolower(str_replace(" ","-",$status)) ?>" ><?php echo $status ?></span></td>
                            <td class="text-right actions">
                                <div class="dropdown">
                                    <button class="btn btn-secondary dropdown-toggle btn-sm" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa fa-bars"></i></button>
                                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                        <a class="dropdown-item" href="<?php echo vv_admin_url($folder.'/edit').'?id='.$staff_id ?>" ><?php vv_e( 'Edit' ); ?></a>
                                        <a class="dropdown-item" href="<?php echo vv_admin_url($folder.'/list').'?vv_action=resend_staff_welcome_email&id='.$staff_id ?>" onclick="return confirm('<?php echo esc_js( vv__( 'Resend Welcome Email?' ) ); ?>')" ><?php vv_e( 'Resend Welcome email' ); ?></a>
                                        <?php
                                        if($staff_level == 'partner' && $logged_user_role == 'administrator'){
                                            ?>
                                            <a class="dropdown-item" href="#" data-toggle="modal" data-target="#addNewApartments<?php echo $staff_id ?>" ><?php vv_e( 'Add New Apartments' ); ?></a>
                                            <a class="dropdown-item" href="<?php bloginfo('url') ?>/host/<?php echo $host_num ?>" target="_blank"  ><?php vv_e( 'View Host Page' ); ?></a>

                                            <?php
                                            ob_start();
                                            ?>

                                            <div class="modal fade" id="addNewApartments<?php echo $staff_id ?>" tabindex="-1" role="dialog" aria-labelledby="addNewApartments<?php echo $staff_id ?>Label" aria-hidden="true">
                                                <div class="modal-dialog  " role="document" >
                                                    <div class="modal-content">
                                                        <form method="post">
                                                            <input type="hidden" name="vv_action" value="add_new_apartments">
                                                            <input type="hidden" name="user_id" value="<?php echo $staff_id ?>" >
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="addNewApartments<?php echo $staff_id ?>Label"><?php vv_e( 'Add New Apartments' ); ?></h5>
                                                                <button type="button" class="close" data-dismiss="modal" aria-label="<?php echo esc_attr( vv__( 'Close' ) ); ?>">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <p><?php vv_e( 'Enter how many apartments to add:' ); ?></p>
                                                                <div class="form-group">
                                                                    <input type="number" name="num_apartments" value="1" min="1" max="100" class="form-control form-control-sm" style="width:100px"  >
                                                                </div>
                                                                <div class="">
                                                                    <button type="submit" class="btn btn-sm btn-primary"><?php vv_e( 'Submit' ); ?></button>
                                                                </div>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>

                                            <?php
                                            $footer_codes = ob_get_clean();
                                        }
                                        ?>
                                        <a class="dropdown-item btnDeleteCustomer" data-user_id="<?php echo $staff_id ?>" href="#"  ><?php vv_e( 'Delete' ); ?></a>
                                    </div>
                                </div>                            
                            </td>
                        </tr>
                        <?php 
                    }
                    ?>
                </tbody>
            </table>
        </div>
      </div>
    </div><!-- .col -->
</div>
