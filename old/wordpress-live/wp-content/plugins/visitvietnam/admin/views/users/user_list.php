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
                        <th class="text-left"><?php vv_e( 'Type' ); ?></th>
                        <th class="text-left"><?php vv_e( 'Apartments' ); ?></th>
                        <th class="text-left"><?php vv_e( 'Bookings' ); ?></th>
                        <th style="width:150px"><?php vv_e( 'Status' ); ?></th>
                        <?php if ( ! empty( $show_login_as_host ) ) { ?>
                        <th style="width:140px"><?php vv_e( 'Actions' ); ?></th>
                        <?php } ?>
                        <th style="width:40px">&nbsp;</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    global $logged_user_role;
                    $show_login_as_host = ! empty( $show_login_as_host );
                    foreach($users as $user){
                        $user_id    = $user['ID'];
                        $data       = (array) $user['data'];
                        $host_status = '';

                        $user_level = get_user_meta($user_id,'vv_user_level',true);
                        if ( $user_level === '' && ! empty( $is_admin_list ) && class_exists( 'vvRoles' ) && vvRoles::get_primary_backend_role( $user_id ) === vvRoles::ROLE_ADMIN ) {
                            $user_level = 'admin';
                        } elseif ( $user_level === '' ) {
                            $user_level = 'customer';
                        }

                        if ( $user_level === 'partner' ) {
                            $account_status = vv_get_user_account_status( $user_id );
                            if ( $account_status !== '' ) {
                                $host_status  = $account_status;
                                $status       = vv_get_user_account_status_label( $account_status );
                                $status_slug  = $account_status;
                            } else {
                                $host_status = get_user_meta( $user_id, 'host_verification_status', true );
                                if ( $host_status === '' ) {
                                    $host_status = 'pending';
                                }
                                $status      = vv_get_status_name_text( $host_status );
                                $status_slug = strtolower( str_replace( ' ', '-', $host_status ) );
                            }
                        } else {
                            $status     = vv_get_status_name( $data['user_status'] );
                            $status_slug = strtolower( str_replace( ' ', '-', $status ) );
                        }

                        $num_apartments = 0;
                        if($user_level == 'partner' || $user_level == 'administration'){
                            $num_apartments = $this->apartment_class->get_num_apartments($user_id);
                        }

                        $num_bookings = $this->booking_class->get_num_bookings($user_id);

                        //echo print_r_pre($user);
                        $host_num = date("Ymd",strtotime($data['user_registered'])).$user_id;

                        $edit_url = vv_admin_user_edit_url( $user_id, $user_level );

                        ?>
                        <tr data-Customer_id="<?php echo $user_id ?>">
                            <td>
                                <input type="checkbox" name="user_id[]" id="user_<?php echo $user_id ?>" value="<?php echo $user_id ?>" required>
                            </td>
                            <td><a href="<?php echo esc_url( $edit_url ); ?>" ><?php echo $data['user_email'] ?></a></td>
                            <td><?php echo stripslashes($data['display_name']) ?></td>
                            <td><?php echo ucwords($user_level) ?></td>
                            <td><a href="<?php echo vv_admin_url('apartments').'?user_id='.$user_id ?>" target="_blank" ><?php echo $num_apartments ?></a></td>
                            <td><a href="<?php echo vv_admin_url('booking').'?user_id='.$user_id ?>" target="_blank" ><?php echo $num_bookings ?></a></td>
                            <td><span class="badge status-<?php echo esc_attr( $status_slug ); ?> <?php echo ( isset( $host_status ) && in_array( $host_status, [ 'pending', 'pending-approval' ], true ) ) ? 'badge-warning' : ''; ?>" ><?php echo esc_html( $status ); ?></span></td>
                            <?php if ( $show_login_as_host && $user_level === 'partner' && $logged_user_role === 'administrator' ) { ?>
                            <td>
                                <a href="<?php echo esc_url( vv_login_as_host_url( $user_id ) ); ?>" class="btn btn-sm btn-primary vv-login-as-host" data-host-name="<?php echo esc_attr( stripslashes( $data['display_name'] ) ); ?>"><?php vv_e( 'Login as Host' ); ?></a>
                            </td>
                            <?php } elseif ( $show_login_as_host ) { ?>
                            <td>&nbsp;</td>
                            <?php } ?>
                            <td class="text-right actions">
                                <div class="dropdown">
                                    <button class="btn btn-secondary dropdown-toggle btn-sm" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa fa-bars"></i></button>
                                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                        <a class="dropdown-item" href="<?php echo esc_url( $edit_url ); ?>" ><?php vv_e( 'Edit' ); ?></a>
                                        <?php if ( $show_login_as_host && $user_level === 'partner' && $logged_user_role === 'administrator' ) { ?>
                                            <a class="dropdown-item vv-login-as-host" href="<?php echo esc_url( vv_login_as_host_url( $user_id ) ); ?>" data-host-name="<?php echo esc_attr( stripslashes( $data['display_name'] ) ); ?>"><?php vv_e( 'Login as Host' ); ?></a>
                                        <?php } ?>
                                        <?php
                                        if($user_level == 'partner' && $logged_user_role == 'administrator'){
                                            ?>
                                            <a class="dropdown-item" href="#" data-toggle="modal" data-target="#addNewApartments<?php echo $user_id ?>" ><?php vv_e( 'Add New Apartments' ); ?></a>
                                            <a class="dropdown-item" href="<?php bloginfo('url') ?>/host/<?php echo $host_num ?>" target="_blank"  ><?php vv_e( 'View Host Page' ); ?></a>

                                            <?php
                                            ob_start();
                                            ?>

                                            <div class="modal fade" id="addNewApartments<?php echo $user_id ?>" tabindex="-1" role="dialog" aria-labelledby="addNewApartments<?php echo $user_id ?>Label" aria-hidden="true">
                                                <div class="modal-dialog  " role="document" >
                                                    <div class="modal-content">
                                                        <form method="post">
                                                            <input type="hidden" name="vv_action" value="add_new_apartments">
                                                            <input type="hidden" name="user_id" value="<?php echo $user_id ?>" >
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="addNewApartments<?php echo $user_id ?>Label"><?php vv_e( 'Add New Apartments' ); ?></h5>
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
                                        <a class="dropdown-item btnDeleteCustomer" data-user_id="<?php echo $user_id ?>" href="#"  ><?php vv_e( 'Delete' ); ?></a>
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
