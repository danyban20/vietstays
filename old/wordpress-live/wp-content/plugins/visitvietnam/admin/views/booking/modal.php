<?php

if(isset($bookings) && is_array($bookings)){
    foreach($bookings as $booking){


        $apartment = $this->apartment_class->get_apartment($booking['apartment_id']);
        $district_name = vv_get_district_display_name($booking['district_id']);
        $delete_booking_confirm = esc_js( vv__( 'Delete BOoking?' ) );
        ?>
        <div class="modal fade" id="bookingModal<?php echo gArrayItem($booking,'ID') ?>" tabindex="-1" role="dialog" aria-labelledby="bookingModal<?php echo gArrayItem($booking,'ID') ?>Label" aria-hidden="true">
            <div class="modal-dialog modal-lg " role="document" >
                <div class="modal-content">
                    <form method="post">
                        <?php /*
                        <input type="hidden" name="action" value="booking-save">
                        <input type="hidden" name="booking_id" value="<?php echo gArrayItem($booking,'ID') ?>">
                        <input type="hidden" name="check_in_date" value="<?php echo date("Y-m-d",gArrayItem($booking,'check_in_date')) ?>">
                        <input type="hidden" name="check_out_date" value="<?php echo date("Y-m-d",gArrayItem($booking,'check_out_date')) ?>">
                        */ ?>
                        <div class="modal-header">
                            <h5 class="modal-title" id="bookingModal<?php echo gArrayItem($booking,'ID') ?>Label"><?php vv_e( 'Booking Details' ); ?></h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="<?php echo esc_attr( vv__( 'Close' ) ); ?>">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="bookinginfo">
                                <div class="p-3">
                                <div class="row mb-2">
                                        <div class="col-md-3"><?php vv_e( 'Booking #' ); ?></div>
                                        <div class="col-md-9"><div class="border-bottom px-2"><?php echo vv_get_booking_num($booking) ?></div></div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-md-3"><?php vv_e( 'Check-In/Out' ); ?></div>
                                        <div class="col-md-9"><div class="border-bottom px-2"><?php echo vv_date_format(gArrayItem($booking,'check_in_date')).' - '.vv_date_format(gArrayItem($booking,'check_out_date')) ?></div></div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-md-3"><?php vv_e( 'Name' ); ?></div>
                                        <div class="col-md-9">
                                            <div class="row px-0">
                                                <div class="col-6"><div class="border-bottom px-2"><?php echo gArrayItem($booking,'firstname') ?></div></div>
                                                <div class="col-6"><div class="border-bottom px-2"><?php echo gArrayItem($booking,'lastname') ?></div></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-md-3"><?php vv_e( 'Email' ); ?></div>  
                                        <div class="col-md-9"><div class="border-bottom px-2"><?php echo gArrayItem($booking,'email') ?></div></div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-md-3"><?php vv_e( 'Status' ); ?></div>
                                        <div class="col-md-2"><div class="border-bottom px-2"><?php echo gArrayItem($booking,'status') ?></div></div>
                                    </div>


                                    <table class="table mt-4">
                                        <tr>
                                            <th><?php vv_e( 'Apartment' ); ?></th>
                                            <th><?php vv_e( 'Disctrict' ); ?></th>
                                            <th><?php vv_e( 'Bedrooms' ); ?></th>
                                            <th><?php vv_e( 'Max Guests' ); ?></th>
                                        </tr>
                                        <tr>
                                            <td><?php echo stripslashes(gArrayItem($booking,'apartment_name')) ?></td>
                                            <td><?php echo stripslashes($district_name) ?></td>
                                            <td><?php echo gArrayItem($apartment,'num_beds') + gArrayItem($apartment,'rooms') ?></td>
                                            <td><?php echo gArrayItem($apartment,'max_guests') ?></td>
                                        </tr>
                                    </table>

                                    <?php
                                    $children_ages = json_decode(gArrayItem($booking,'child_ages'),true); 
                                    if(!is_array($children_ages)) $children_ages = array();
                                    if(count($children_ages) > 0){
                                        echo '<div class="row mb-2">';
                                        echo '<div class="col-md-3">' . esc_html( vv__( 'Children' ) ) . '</div>';
                                        echo '<div class="col-md-9">';
                                        for($i = 1; $i <= gArrayItem($booking,'children'); $i++){
                                            ?>
                                            <div class="border-bottom px-2 d-inline mr-2" ><?php echo gArrayItem($children_ages,$i-1) ?> <?php vv_e( 'yrs. old' ); ?></div>
                                            <?php 
                                        }
                                        echo '</div>';
                                        echo '</div>';
                                    }
                                    ?>
                                    <?php 
                                    echo vv_get_booking_tables_html($booking);
                                    ?>
                                </div>
                                <div class="msg"></div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <a href="<?php echo vv_admin_url().'booking/move/?id='.gArrayItem($booking,'ID') ?>"  class="btn btn-sm btn-info"><?php vv_e( 'Move' ); ?></a>
                            <a href="<?php echo vv_admin_url().'booking/delete/?vv_action=delete-booking&id='.gArrayItem($booking,'ID') ?>" onclick="return confirm('<?php echo $delete_booking_confirm; ?>')"  class="btn btn-sm btn-danger "><?php vv_e( 'Delete' ); ?></a>
                            <a href="<?php echo vv_admin_url().'booking/edit/?id='.gArrayItem($booking,'ID') ?>"  class="btn btn-sm btn-info btnUpdateDetails"><?php vv_e( 'Edit' ); ?></a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php 
    }
}
?>
