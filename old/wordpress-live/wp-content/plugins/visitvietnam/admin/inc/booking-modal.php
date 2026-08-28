<?php

if(isset($bookings) && is_array($bookings)){
    foreach($bookings as $booking){




        ?>
        <div class="modal fade" id="bookingModal<?php echo gArrayItem($booking,'ID') ?>" tabindex="-1" role="dialog" aria-labelledby="bookingModal<?php echo gArrayItem($booking,'ID') ?>Label" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <form method="post">
                        <?php /*
                        <input type="hidden" name="action" value="booking-save">
                        <input type="hidden" name="booking_id" value="<?php echo gArrayItem($booking,'ID') ?>">
                        <input type="hidden" name="check_in_date" value="<?php echo date("Y-m-d",gArrayItem($booking,'check_in_date')) ?>">
                        <input type="hidden" name="check_out_date" value="<?php echo date("Y-m-d",gArrayItem($booking,'check_out_date')) ?>">
                        */ ?>
                        <div class="modal-header">
                            <h5 class="modal-title" id="bookingModal<?php echo gArrayItem($booking,'ID') ?>Label">Booking Details</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="bookinginfo">
                                <div class="p-3">
                                <div class="row mb-2">
                                        <div class="col-md-3">Booking #</div>
                                        <div class="col-md-9"><div class="border-bottom px-2"><?php echo vv_get_booking_num($booking) ?></div></div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-md-3">Check-In/Out</div>
                                        <div class="col-md-9"><div class="border-bottom px-2"><?php echo date("M j, Y",gArrayItem($booking,'check_in_date')).' - '.date("M j, Y",gArrayItem($booking,'check_out_date')) ?></div></div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-md-3">Name</div>
                                        <div class="col-md-9">
                                            <div class="row px-0">
                                                <div class="col-6"><div class="border-bottom px-2"><?php echo gArrayItem($booking,'firstname') ?></div></div>
                                                <div class="col-6"><div class="border-bottom px-2"><?php echo gArrayItem($booking,'lastname') ?></div></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-md-3">Email</div>  
                                        <div class="col-md-9"><div class="border-bottom px-2"><?php echo gArrayItem($booking,'email') ?></div></div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-md-3">District</div>
                                        <div class="col-md-9"><div class="border-bottom px-2"><?php echo gArrayItem($booking,'district_name') ?></div></div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-md-3">Apartment</div>
                                        <div class="col-md-9"><div class="border-bottom px-2"><?php echo gArrayItem($booking,'apartment_name') ?></div></div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-md-3">Rooms</div>
                                        <div class="col-md-2"><div class="border-bottom px-2"><?php echo gArrayItem($booking,'rooms') ?></div></div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-md-3">Adults</div>
                                        <div class="col-md-2"><div class="border-bottom px-2"><?php echo gArrayItem($booking,'adults') ?></div></div>
                                    </div>
                                    <?php
                                    $children_ages = json_decode(gArrayItem($booking,'child_ages'),true); 
                                    if(!is_array($children_ages)) $children_ages = array();
                                    if(count($children_ages) > 0){
                                        echo '<div class="row mb-2">';
                                        echo '<div class="col-md-3">Children</div>';
                                        echo '<div class="col-md-9">';
                                        for($i = 1; $i <= gArrayItem($booking,'children'); $i++){
                                            ?>
                                            <div class="border-bottom px-2 d-inline mr-2" ><?php echo gArrayItem($children_ages,$i-1) ?> yrs. old</div>
                                            <?php 
                                        }
                                        echo '</div>';
                                        echo '</div>';
                                    }
                                    ?>
                                    <div class="row mb-2">
                                        <div class="col-md-3">Status</div>
                                        <div class="col-md-2"><div class="border-bottom px-2"><?php echo gArrayItem($booking,'status') ?></div></div>
                                    </div>
                                    <?php 
                                    echo vv_get_booking_tables_html($booking);
                                    ?>
                                </div>
                                <div class="msg"></div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <a href="<?php echo vv_admin_url().'booking-edit/?id='.gArrayItem($booking,'ID') ?>"  class="btn btn-sm btn-info btnUpdateDetails">Edit</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php 
    }
}
?>

