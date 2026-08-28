<?php
/**
 * Tab panes for apartment platform features.
 */
if ( ! isset( $platform_settings ) ) {
	$platform_settings = vvApartmentPlatform::get_settings();
}
?>

                <div class="tab-pane fade" id="availability" role="tabpanel">
                    <div class="tab-inner-content">
                        <h4 class="mb-3"><?php vv_e( 'Blocked dates' ); ?></h4>
                        <p class="text-muted small"><?php vv_e( 'Enter dates when this apartment cannot be booked (one per line, YYYY-MM-DD).' ); ?></p>
                        <textarea name="blocked_dates" class="form-control form-control-sm" rows="6" placeholder="2026-07-01&#10;2026-07-02"><?php echo esc_textarea( implode( "\n", $blocked_dates ) ); ?></textarea>
                        <?php if ( $apartment_id > 0 ) { ?>
                            <p class="mt-3 mb-0"><a href="<?php echo esc_url( vv_admin_url( 'apartments/gantt?apt=' . $apartment_id ) ); ?>" class="btn btn-sm btn-outline-secondary"><?php vv_e( 'View portfolio calendar' ); ?></a></p>
                        <?php } ?>
                    </div>
                </div>

                <div class="tab-pane fade" id="staff-assign" role="tabpanel">
                    <div class="tab-inner-content">
                        <h4 class="mb-3"><?php vv_e( 'Assigned operations staff' ); ?></h4>
                        <?php if ( empty( $available_staff ) ) { ?>
                            <p class="text-muted"><?php vv_e( 'No staff found for this host. Add staff under Staffs first.' ); ?></p>
                        <?php } else { ?>
                            <?php foreach ( $available_staff as $staff ) {
                                $sid = intval( gArrayItem( $staff, 'ID' ) );
                                $checked = in_array( $sid, $assigned_staff_ids, true ) ? 'checked' : '';
                                ?>
                                <div class="mb-1">
                                    <label><input type="checkbox" name="assigned_staff[]" value="<?php echo esc_attr( $sid ); ?>" <?php echo $checked; ?>>
                                        <?php echo esc_html( gArrayItem( $staff, 'display_name' ) ); ?>
                                        <span class="text-muted small">(<?php echo esc_html( get_user_meta( $sid, 'vv_staff_position', true ) ); ?>)</span>
                                    </label>
                                </div>
                            <?php } ?>
                        <?php } ?>
                    </div>
                </div>

                <?php if ( $apartment_id > 0 ) { ?>
                <div class="tab-pane fade" id="send-offer" role="tabpanel">
                    <div class="tab-inner-content">
                        <h4 class="mb-3"><?php vv_e( 'Send private booking offer' ); ?></h4>
                        <div class="mb-4">
                            <div class="row">
                                <div class="col-md-4 form-group">
                                    <label><?php vv_e( 'Guest email' ); ?></label>
                                    <input type="email" form="vvSendOfferForm" name="offer_guest_email" class="form-control form-control-sm" required>
                                </div>
                                <div class="col-md-4 form-group">
                                    <label><?php vv_e( 'Guest name' ); ?></label>
                                    <input type="text" form="vvSendOfferForm" name="offer_guest_name" class="form-control form-control-sm">
                                </div>
                                <div class="col-md-4 form-group">
                                    <label><?php vv_e( 'Offer price' ); ?></label>
                                    <input type="text" form="vvSendOfferForm" name="offer_price" class="form-control form-control-sm" value="<?php echo esc_attr( vv_number_format( gArrayItem( $apartment, 'price_daily' ) ) ); ?>">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 form-group">
                                    <label><?php vv_e( 'Check-in' ); ?></label>
                                    <input type="text" form="vvSendOfferForm" name="offer_check_in" class="form-control form-control-sm datepick-offer" required>
                                </div>
                                <div class="col-md-4 form-group">
                                    <label><?php vv_e( 'Check-out' ); ?></label>
                                    <input type="text" form="vvSendOfferForm" name="offer_check_out" class="form-control form-control-sm datepick-offer" required>
                                </div>
                                <div class="col-md-4 form-group d-flex align-items-end">
                                    <button type="submit" form="vvSendOfferForm" class="btn btn-primary btn-sm"><?php vv_e( 'Send offer' ); ?></button>
                                </div>
                            </div>
                        </div>
                        <?php if ( ! empty( $offers ) ) { ?>
                            <h5><?php vv_e( 'Recent offers' ); ?></h5>
                            <table class="table table-sm table-bordered w-auto">
                                <tr><th><?php vv_e( 'Guest' ); ?></th><th><?php vv_e( 'Dates' ); ?></th><th><?php vv_e( 'Status' ); ?></th></tr>
                                <?php foreach ( $offers as $offer ) { ?>
                                    <tr>
                                        <td><?php echo esc_html( gArrayItem( $offer, 'guest_email' ) ); ?></td>
                                        <td><?php echo esc_html( gArrayItem( $offer, 'check_in_date' ) . ' → ' . gArrayItem( $offer, 'check_out_date' ) ); ?></td>
                                        <td><?php echo esc_html( gArrayItem( $offer, 'status' ) ); ?></td>
                                    </tr>
                                <?php } ?>
                            </table>
                        <?php } ?>
                    </div>
                </div>
                <?php } ?>
