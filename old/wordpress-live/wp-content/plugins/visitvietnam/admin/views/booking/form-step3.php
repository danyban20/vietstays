<?php 
//echo print_r_pre($booking);


//echo print_r_pre($booking_items);

$foods = $this->foods_class->get_foods(['apartment_id' => gArrayItem($booking,'apartment_id')]);

/*
if(gArrayItem($booking,'ID') > 0){
    $found = false;
    foreach($apartments as $apartment){
        if($booking['apartment_id'] == $apartment['ID']) $found = true;
    }

    if($found == false){
        $apartment = $this->apartment_class->get_apartment($booking['apartment_id']);
        array_unshift($apartments, $apartment);
    }
}
*/

$apartment_id   = gArrayItem($booking,'apartment_id');
$apartment      = $this->apartment_class->get_apartment($apartment_id);
$district_name  = vv_get_district_display_name(gArrayItem($apartment,'district'), $districts);

$cleaning_fee           = (floatval(gArrayItem($booking,'cleaning_fee')) > 0)           ? floatval(gArrayItem($booking,'cleaning_fee')) : floatval(gArrayItem($apartment,'cleaning_fee'));
$scooter_rental_fee     = (floatval(gArrayItem($booking,'scooter_rental_fee')) > 0)     ? floatval(gArrayItem($booking,'scooter_rental_fee')) : floatval(gArrayItem($apartment,'scooter_rental_fee'));
$num_cleaning           = intval(gArrayItem($booking,'num_cleaning'));
$num_scooter_rental     = intval(gArrayItem($booking,'num_scooter_rental'));
$airport_pickup         = gArrayItem($booking,'airport_pickup');
$airport_pickup_cost    = (floatval(gArrayItem($booking,'airport_pickup_cost')) > 0) ? floatval(gArrayItem($booking,'airport_pickup_cost')) : floatval(vv_get_config('airport_pickup_cost'));
$payment_method         = gArrayItem($booking,'payment_method');
$promo_code             = gArrayItem($booking,'promo_code');
$promo_code_discount    = intval(gArrayItem($booking,'promo_code_discount'));
$promo_code_added       = intval(gArrayItem($booking,'promo_code_added'));


$check_in_date = gArrayItem($booking,'check_in_date');
if($check_in_date == '') $check_in_date = date("Y-m-d");

$check_out_date = gArrayItem($booking,'check_out_date');
if($check_out_date == '') $check_out_date = date("Y-m-d",strtotime("+1 Day"));

$checkInDate    = new DateTime($check_in_date);
$checkOutDate   = new DateTime($check_out_date);

$numDays        = $checkInDate->diff($checkOutDate)->days;

$data               = vv_get_booking_pricing_data($apartment_id, $check_in_date,$check_out_date);
$label              = gArrayItem($data,'label');
$subtotal           = floatval(gArrayItem($data,'total'));
$basic_discount     = floatval(gArrayItem($data,'basic_discount'));
$campaign_discount  = floatval(gArrayItem($data,'campaign_discount'));
$dprices            = gArrayItem($data,'dprices');
$dprices2            = gArrayItem($data,'dprices2');

$prices = [];
foreach($dprices as $p => $q) array_push($prices,$p);

if(count($prices) > 1){
    sort($prices);

    $price_range = vv_number_format($prices[0],false).' - '.vv_number_format($prices[count($prices)-1],true);
}elseif(count($prices) == 1){
    $price_range = vv_number_format($prices[0]);
}else{
    $price_range = '';
}

$booking_fee = vv_fee();

$ambassador_commission  = (gArrayItem($booking,'ambassador_commission') != '') ? gArrayItem($booking,'ambassador_commission') : gArrayItem($apartment,'ambassador_commission');
$promo_code_discount    = (gArrayItem($booking,'promo_code_discount') != '') ? gArrayItem($booking,'promo_code_discount') : intval(gArrayItem($apartment,'promocode_discount'));

$vv_booking_step3_js = [
    'campaign_discount'      => esc_js( vv__( 'Campaign Discount' ) ),
    'promo_code_discount'    => esc_js( vv__( 'Promo Code Discount' ) ),
    'discount_total'         => esc_js( vv__( 'Discount Total' ) ),
    'booking_fee'            => esc_js( vv__( 'Booking fee' ) ),
    'cleaning_fee'           => esc_js( vv__( 'Cleaning fee' ) ),
    'scooter_rental'         => esc_js( vv__( 'Scooter Rental' ) ),
    'airport_pickup'         => esc_js( vv__( 'Airport Pickup' ) ),
    'foods'                  => esc_js( vv__( 'Foods' ) ),
    'guest_total'            => esc_js( vv__( 'Guest Total' ) ),
    'ambassador_commission'  => esc_js( vv__( 'Ambassador Commission' ) ),
    'host_final_income'      => esc_js( vv__( 'Host Final Income' ) ),
    'delete_addon'           => esc_js( vv__( 'Delete addon?' ) ),
    'promo_code_added'       => esc_js( vv__( 'Promo code added.' ) ),
    'promo_code_not_found'   => esc_js( vv__( 'Promo code not found' ) ),
    'no_addon_available'     => esc_js( vv__( 'Selected apartment has no addon available.' ) ),
    'promo_code_changed'     => esc_js( vv__( 'Promo code changed. This will remove any discount applied. Continue?' ) ),
    'promo_campaign_conflict'=> esc_js( vv__( 'We cannot add your promocode to your booking due to existing campaign discount" - Do you want to continue without discount?' ) ),
];

?>
        <form method="post" id="formBooking">
            <input type="hidden" name="action" value="booking-save">
            <input type="hidden" name="source" value="admin">
            <input type="hidden" name="booking_id" value="<?php echo gArrayItem($booking,'ID') ?>">
            <input type="hidden" name="user_id" id="user_id" value="<?php echo intval(gArrayItem($booking,'user_id')) ?>">
            <input type="hidden" name="check_in_date" value="<?php echo gArrayItem($booking,'check_in_date') ?>" >
            <input type="hidden" name="check_out_date" value="<?php echo gArrayItem($booking,'check_out_date') ?>" >
            <input type="hidden" name="rooms" value="<?php echo gArrayItem($booking,'rooms') ?>" >
            <input type="hidden" name="adults" value="<?php echo gArrayItem($booking,'adults') ?>" >
            <input type="hidden" name="children" value="<?php echo gArrayItem($booking,'children') ?>" >
            <input type="hidden" name="district_id" value="<?php echo gArrayItem($apartment,'district') ?>" >
            <input type="hidden" name="apartment_id" value="<?php echo $apartment_id ?>" >
            <input type="hidden" name="ambassador_id" value="<?php echo gArrayItem($booking,'ambassador_id') ?>" >
            <input type="hidden" name="promo_code_added" value="<?php echo $promo_code_added ?>" >
            <?php 
            if($_POST){
                $child_age = POST_Request('child_age');
            }else{
                $child_age = json_decode(gArrayItem($booking,'child_ages'),true);
            }
            if(is_array($child_age)){
                for($i = 0; $i < gArrayItem($booking,'children'); $i++){
                    ?>
                    <input type="hidden" name="child_age[]" value="<?php echo $child_age[$i] ?>" >
                    <?php 
                }
            }
            ?>
            <h4 class="mb-2"><?php vv_e( 'Apartment' ); ?></h4>
            <table class="table border table-striped mb-4">
                <thead>
                    <tr>
                        <th><?php vv_e( 'Apartment' ); ?></th>
                        <th><?php vv_e( 'Disctrict' ); ?></th>
                        <th><?php vv_e( 'Bedrooms' ); ?></th>
                        <th><?php vv_e( 'Max Guests' ); ?></th>
                        <th><?php vv_e( 'Daily Price' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><?php echo stripslashes(gArrayItem($apartment,'name')) ?></td>
                        <td><?php echo stripslashes($district_name) ?></td>
                        <td><?php echo gArrayItem($apartment,'num_beds') ?></td>
                        <td><?php echo gArrayItem($apartment,'max_guests') ?></td>
                        <td><?php echo $price_range ?></td>
                    </tr>
                </tbody>
            </table>

            <div class="table-responsive table--no-card mt-4 m-b-30">
                <table class="table table-borderless table-striped table-earning ">
                    <thead>
                        <tr>
                            <th class="text-left"><?php vv_e( 'Apartment' ); ?></th>
                            <th class="text-center" style="width:100px"><?php vv_e( 'Adults' ); ?></th>
                            <th class="text-center" style="width:100px"><?php vv_e( 'Children' ); ?></th>
                            <th style="width:150px"><?php vv_e( 'Check-in/out' ); ?></th>
                            <th class="text-center" style="width:100px"><?php vv_e( 'Nights' ); ?></th>
                            <th class="text-right" style="width:150px"><?php vv_e( 'Daily Price' ); ?></th>
                            <th class="text-right" style="width:150px"><?php vv_e( 'Amount' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 

                        if($booking_id > 0){
                            //echo print_r_pre($booking_items);
                            foreach($booking_items as $item){
                                if($item['item_type'] == 'booking-date'){
                                    $name       = explode("-",$item['name']);
                                    $start      = new DateTime($name[0]);
                                    $end        = new DateTime($name[1]);
                                    $r_label    = vv_get_date_range_text($start->format("Y-m-d"),$end->format("Y-m-d"));
                                    ?>
                                    <tr>
                                        <td><?php echo $apartment['name'] ?></td>
                                        <td class="text-center"><?php echo gArrayItem($booking,'adults') ?></td>
                                        <td class="text-center"><?php echo gArrayItem($booking,'children') ?></td>
                                        <td><?php echo $r_label ?></td>
                                        <td class="text-center"><?php echo $item['qty'] ?></td>
                                        <td class="text-right">
                                            <input type="hidden" name="item_name[<?php echo $item['item_id'] ?>]" value="<?php echo $item['name'] ?>" >
                                            <input type="hidden" name="item_qty[<?php echo $item['item_id'] ?>]" value="<?php echo $item['qty'] ?>"  class="item_qty" >
                                            <input type="text" name="item_price[<?php echo $item['item_id'] ?>]" class="form-control form-control-sm item_price text-right" value="<?php echo vv_number_format($item['price']) ?>" >
                                        </td>
                                        <td class="text-right item_amount"><?php echo vv_number_format($item['price'] * $item['qty'],true) ?></td>
                                    </tr>
                                    <?php
                                }
                            }
                        }else{
                            $result = vv_break_dprices($dprices2);
                            //echo print_r_pre($result);
                            //die();

                            $ctr = 0;
                            foreach($result as $r){
                                $start = new DateTime($r['start']);
                                $end = new DateTime($r['end']);

                                $r_label = vv_get_date_range_text($start->format("Y-m-d"),$end->format("Y-m-d"));
                                ?>
                                <tr>
                                    <td><?php echo $apartment['name'] ?></td>
                                    <td class="text-center"><?php echo gArrayItem($booking,'adults') ?></td>
                                    <td class="text-center"><?php echo gArrayItem($booking,'children') ?></td>
                                    <td><?php echo $r_label ?></td>
                                    <td class="text-center"><?php echo $r['days'] ?></td>
                                    <td class="text-right">
                                        <input type="hidden" name="item_name[<?php echo $ctr ?>]" value="<?php echo $start->format('m/d/Y').'-'.$end->format('m/d/Y') ?>" >
                                        <input type="hidden" name="item_qty[<?php echo $ctr ?>]" value="<?php echo $r['days'] ?>" class="item_qty" >
                                        <input type="text" name="item_price[<?php echo $ctr ?>]" class="form-control form-control-sm item_price text-right" value="<?php echo vv_number_format($r['price']) ?>" >
                                    </td>
                                    <td class="text-right item_amount"><?php echo vv_number_format($r['price'] * $r['days'],true) ?></td>
                                </tr>
                                <?php
                                $ctr++;
                            }
                        }



                        ?>
                    </tbody>
                </table>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <h4 class="mb-2"><?php vv_e( 'Promo Code' ); ?></h4>
                    <div style="max-width:450px" class="border p-2 mb-4">
                        <div class="row mb-2">
                            <div class="col-md-7">
                                <label class="d-inline"><?php vv_e( 'Promo Code' ); ?></label>
                            </div>
                            <div class="col-md-5">
                                <input type="text" name="promo_code" class="form-control form-control-sm d-inline" style="width:100px" value="<?php echo gArrayItem($booking,'promo_code') ?>" > 
                                <button type="button" class="btn btn-primary btn-sm btnCheckPromoCode"><?php vv_e( 'Apply' ); ?></button>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-md-7">
                                <label class="d-inline"><?php vv_e( 'Promo Code Discount' ); ?></label>
                            </div>
                            <div class="col-md-5">
                                <input type="text" name="promo_code_discount" class="form-control form-control-sm d-inline calc_change" style="width:50px" value="<?php echo $promo_code_discount ?>" > %
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-md-7">
                                <label class="d-inline"><?php vv_e( 'Ambassador Commission' ); ?></label>
                            </div>
                            <div class="col-md-5">
                                <input type="text" name="ambassador_commission" class="form-control form-control-sm d-inline calc_change" style="width:50px" value="<?php echo $ambassador_commission ?>" >  %
                            </div>
                        </div>

                    </div>
                    <h4 class="mb-2"><?php vv_e( 'Additional Services' ); ?></h4>
                    <div style="max-width:450px" class="border p-2 mb-4">
                        <?php
                        if($booking_fee > 0){
                            ?>
                            <div class="row mb-2">
                                <div class="col-md-7">
                                    <label class="d-inline"><?php vv_e( 'Booking fee' ); ?></label>
                                </div>
                                <div class="col-md-5">
                                    <input type="text" name="booking_fee" class="form-control form-control-sm d-inline calc_change" style="width:50px" value="<?php echo $booking_fee ?>" > %
                                </div>
                            </div>
                            <?php

                        }
                        ?>                        <?php
                        if($cleaning_fee > 0){
                            ?>
                            <div class="row mb-2">
                                <div class="col-md-7">
                                    <label class="d-inline"><?php vv_e( 'Extra Cleaning Service' ); ?></label>
                                </div>
                                <div class="col-md-5">
                                    <select name="num_cleaning" class="form-control form-control-sm d-inline w-auto calc_change" >
                                        <option value=""><?php vv_e( 'NO' ); ?></option>
                                        <?php for($i = 1; $i <= $numDays; $i++){ ?>
                                            <option value="<?php echo $i ?>" <?php echo ($num_cleaning == $i) ? 'selected' : '' ?> ><?php echo $i ?> <?php echo esc_html( ($i > 1) ? vv__( 'times' ) : vv__( 'time' ) ); ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                            <?php

                        }
                        ?>
                        <div class="row">
                            <div class="col-md-7">
                                <label class="d-inline"><?php vv_e( 'Airport Pick-up' ); ?></label>
                            </div>
                            <div class="col-md-5">
                                <label class="switch switch-default switch-success-outline-alt switch-pill mr-2">
                                    <input type="checkbox" name="airport_pickup" id="airport_pickup" class="switch-input calc_change" <?php echo ($airport_pickup) ? 'checked' : '' ?>  value="1"  > 
                                    <span class="switch-label"></span>
                                    <span class="switch-handle"></span>
                                </label>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-md-7">
                                <label class="d-inline"><?php vv_e( 'Scooter Rental' ); ?></label>
                            </div>
                            <div class="col-md-5">
                                <select name="num_scooter_rental" class="form-control form-control-sm d-inline w-auto calc_change" >
                                    <option value=""><?php vv_e( 'NO' ); ?></option>
                                    <?php for($i = 1; $i <= $numDays; $i++){ ?>
                                        <option value="<?php echo $i ?>" <?php echo ($num_scooter_rental == $i) ? 'selected' : '' ?> ><?php echo $i ?> <?php echo esc_html( ($i > 1) ? vv__( 'times' ) : vv__( 'time' ) ); ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <h4 class="mb-2"><?php vv_e( 'Foods' ); ?></h4>
                    <div class="table-responsive table--no-card m-b-30">
                        <table class="table table-borderless table-striped table-earning items_list">
                            <thead>
                                <tr>
                                    <th class="text-left"><?php vv_e( 'Name' ); ?></th>
                                    <th class="text-center" style="width:100px"><?php vv_e( 'Qty' ); ?></th>
                                    <th class="text-center" style="width:100px"><?php vv_e( 'Price' ); ?></th>
                                    <th class="text-right" style="width:100px"><?php vv_e( 'Amount' ); ?></th>
                                    <th style="width:100px">&nbsp;</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                foreach($booking_items as $item){
                                    if(gArrayItem($item,'item_type') == 'food'){
                                        ?>
                                        <tr class="booking_<?php echo $item['food_id'] ?>" >
                                            <td>
                                                <input type="hidden" name="food_id[]" value="<?php echo $item['item_id'] ?>"  >
                                                <input type="hidden" name="food_name[]" value="<?php echo $item['name'] ?>"  >
                                                <input type="hidden" name="food_code[]" value="<?php echo $item['code'] ?>"  >
                                                <?php echo $item['name'] ?>
                                            </td>
                                            <td><input type="text" name="food_qty[]" class="form-control form-control-sm" value="<?php echo $item['qty'] ?>" ></td>
                                            <td><input type="text" name="food_price[]"  class="form-control form-control-sm" value="<?php echo number_format($item['price'],2) ?>" ></td>
                                            <td class="text-right"><?php echo '$'.number_format($item['price'] * $item['qty'],2) ?></td>
                                            <td><a href="#" class="deleteFood" ><i class="fa fa-trash"></i></a></td>
                                        </tr>
                                        <?php 
                                    }
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-left">
                        <button type="button" class="btn btn-info btn-sm btnAddItem" ><?php vv_e( 'New Food' ); ?></button>
                    </div>

                </div>
                <div class="col-md-6">
                    <table align="right" class="table w-auto table-border border bg-white order_total">
                    </table>
                </div>
            </div>
            <br>
            <h4 class="mb-2"><?php vv_e( 'Customer Info' ); ?></h4>
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group"> 
                        <label><?php vv_e( 'Search User' ); ?></label>
                        <input type="text" name="search_user" value="" id="search_user" class="form-control form-control-sm" autocomplete="no" placeholder="<?php echo esc_attr( vv__( 'Email, Name, or ID' ) ); ?>" >
                        <div class="position-relative"><div id="vv-autocomplete_result"></div></div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group"> 
                        <label><?php vv_e( 'Email' ); ?></label>
                        <input type="email" name="email" value="<?php echo gArrayItem($booking,'email') ?>" id="user_email" class="form-control form-control-sm" autocomplete="no" required >
                        <div class="position-relative"><div id="vv-autocomplete_result"></div></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group"> 
                        <label><?php vv_e( 'First Name' ); ?></label>
                        <input type="text" name="firstname" value="<?php echo gArrayItem($booking,'firstname') ?>" id="user_firstname" class="form-control form-control-sm" required >
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group"> 
                        <label><?php vv_e( 'Last name' ); ?></label>
                        <input type="text" name="lastname" value="<?php echo gArrayItem($booking,'lastname') ?>" id="user_lastname" class="form-control form-control-sm" required >
                    </div>
                </div>
            </div>
            <div class=" signup_wrap" <?php echo (gArrayItem($booking,'user_id') > 0) ? 'style="display:none"' : '' ?> >
                <div class="form-group">
                    <label class="switch switch-default switch-success-outline-alt switch-pill mr-2">
                        <input type="radio" name="register_user" class="switch-input" checked  value="1"  > 
                        <span class="switch-label"></span>
                        <span class="switch-handle"></span>
                    </label>
                    <span><?php vv_e( 'Create an Account & Send Registration Email' ); ?></span> 
                </div>
            </div>
            <?php if(gArrayItem($booking,'ID') > 0){ ?>
                <div class="form-group">
                    <label class="switch switch-default switch-success-outline-alt switch-pill mr-2">
                        <input type="radio" name="resent_booking_confirmation" class="switch-input"  value="1"  > 
                        <span class="switch-label"></span>
                        <span class="switch-handle"></span>
                    </label>
                    <span><?php vv_e( 'Resend Booking Confirmation' ); ?></span> 
                </div>
            <?php } ?>
            <hr>
            <div class="mt-4">
                <?php 
                if(gArrayItem($booking,'ID') > 0) $url = vv_admin_url().'booking/edit/?id='.gArrayItem($booking,'ID');
                else $url = vv_admin_url().'booking/add?step=2';
                ?>
                <a href="<?php echo $url ?>" class="btn btn-secondary"><?php vv_e( 'Back' ); ?></a>
                <button type="submit" class="btn btn-primary"><?php vv_e( 'Submit' ); ?></button>
            </div>
        </form>


        <?php

        global $footer_codes;
        ob_start();
        ?>

        <div class="modal fade" id="newFood" tabindex="-1" role="dialog" aria-labelledby="newFoodLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <form method="post" id="newFoodForm" >
                        <div class="modal-header">
                            <h5 class="modal-title" id="newFoodLabel"><?php vv_e( 'New Food' ); ?></h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="<?php echo esc_attr( vv__( 'Close' ) ); ?>">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="row mb-2">
                                <div class="col-md-4">
                                    <label><?php vv_e( 'Food' ); ?></label>
                                </div>
                                <div class="col-md-8">
                                    <select name="food" class="form-control form-control-sm">
                                        <?php foreach($foods as $food){ ?>
                                            <option value="<?php echo $food['food_id'] ?>" data-name="<?php echo $food['name'] ?>" data-price="<?php echo $food['price'] ?>" ><?php echo $food['name'].' - '.'$'.number_format($food['price']) ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <label><?php vv_e( 'Qty' ); ?></label>
                                </div>
                                <div class="col-md-8">
                                    <input type="text" name="qty" value="1" class="form-control form-control-sm" >
                                </div>
                            </div>
                            <div class="mt-2"><button type="submit" class="btn btn-primary btn-sm" ><?php vv_e( 'Add' ); ?></button></div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap-4-autocomplete/dist/bootstrap-4-autocomplete.min.js" crossorigin="anonymous"></script>


        <script type="text/javascript">
            var subtotal = <?php echo $subtotal ?>;
            var basic_discount = <?php echo $basic_discount ?>;
            var campaign_discount = <?php echo $campaign_discount ?>;
            var cleaning_fee = <?php echo floatval($cleaning_fee) ?>;
            var scooter_rental_fee = <?php echo floatval($scooter_rental_fee); ?>;
            var airport_pickup_cost = <?php echo $airport_pickup_cost ?>;
            var promo_code = '<?php echo $promo_code ?>';
            var daily_price = <?php echo floatval(gArrayItem($apartment,'price_daily')) ?>;


            function calculateTotals(){
                
                num_cleaning            = $('select[name="num_cleaning"]').val();
                num_scooter_rental      = $('select[name="num_scooter_rental"]').val();
                airport_pickup          = parseInt($('input[name="airport_pickup"]:checked').val());
                promo_code              = $('input[name="promo_code"]').val();
                promo_code_added        = parseInt($('input[name="promo_code_added"]').val());
                promo_code_discount     = parseInt($('input[name="promo_code_discount"]').val());
                ambassador_commission   = parseInt($('input[name="ambassador_commission"]').val());
                booking_fee             = parseFloat($('input[name="booking_fee"]').val());

                item_prices = $('.item_price');
                item_qtys   = $('.item_qty');

                subtotal  = 0;
                for(i = 0; i < item_prices.length; i++){
                    subtotal += parseFloat(vvCurrencyToDecimal($(item_prices[i]).val())) * parseFloat(vvCurrencyToDecimal($(item_qtys[i]).val()));
                }

                html        = '';
                //subtotal    = <?php echo floatval($subtotal) ?>;
                discount_t  = 0;
                html        = '<tr><td><?php echo $label ?></td><td>&nbsp;</td><td class="text-right">'+vvFormatCurrency(subtotal)+'</td></tr>';

                if(campaign_discount > 0){
                    subtotal    = subtotal - campaign_discount;
                    discount_t  += campaign_discount;
                    html        += '<tr><td><?php echo $vv_booking_step3_js['campaign_discount']; ?></td><td>&nbsp;</td><td class="text-right">- '+vvFormatCurrency(campaign_discount)+'</td></tr>';
                }

                if(basic_discount > 0){
                    discount = (subtotal * (basic_discount/100));
                    subtotal -= discount;
                    discount_t += discount;
                    html        += '<tr><td><?php echo vv_basic_discount_name() ?></td><td class="text-right">'+basic_discount+'%</td><td class="text-right">- '+vvFormatCurrency(discount)+'</td></tr>';
                }


                if(promo_code_added == 1){
                    if(campaign_discount > 0){
                    }else{
                        discount = (subtotal * (promo_code_discount/100));
                        subtotal -= discount;
                        discount_t += discount_t;
                        html        += '<tr><td><?php echo $vv_booking_step3_js['promo_code_discount']; ?></td><td class="text-right">'+promo_code+' - '+promo_code_discount+'%</td><td class="text-right">- '+vvFormatCurrency(discount)+'</td></tr>';
                    }
                }

                if(discount_t > 0){
                    html        += '<tr><td><b><?php echo $vv_booking_step3_js['discount_total']; ?></b></td><td class="text-right">&nbsp;</td><td class="text-right">- '+vvFormatCurrency(discount_t)+'</td></tr>';
                }

                if(booking_fee > 0){
                    vv_fee      = (subtotal * (booking_fee/100));
                    subtotal   += vv_fee;
                    html        += '<tr><td><?php echo $vv_booking_step3_js['booking_fee']; ?></td><td class="text-right"><?php echo vv_fee() ?>%</td><td class="text-right">'+vvFormatCurrency(vv_fee)+'</td></tr>';
                }


                if(cleaning_fee > 0 && num_cleaning > 0){
                    cleaning_total  = cleaning_fee * num_cleaning;
                    subtotal        += cleaning_total;
                    html            += '<tr><td><?php echo $vv_booking_step3_js['cleaning_fee']; ?></td><td class="text-right">'+vvFormatCurrency(cleaning_fee)+' &times; '+num_cleaning+'</td><td class="text-right">'+vvFormatCurrency(cleaning_total)+'</td></tr>';
                }

                if(scooter_rental_fee > 0 && num_scooter_rental > 0){
                    scooter_rental_total    = scooter_rental_fee * num_scooter_rental;
                    subtotal                += scooter_rental_total;
                    html                    += '<tr><td><?php echo $vv_booking_step3_js['scooter_rental']; ?></td><td class="text-right">'+vvFormatCurrency(scooter_rental_fee)+' &times; '+num_scooter_rental+'</td><td class="text-right">'+vvFormatCurrency(scooter_rental_total)+'</td></tr>';
                }


                if(airport_pickup == 1){
                    subtotal    += airport_pickup_cost;
                    html            += '<tr><td><?php echo $vv_booking_step3_js['airport_pickup']; ?></td><td class="text-right">&nbsp;</td><td class="text-right">'+vvFormatCurrency(airport_pickup_cost)+'</td></tr>';
                }


                $food_qtys = $('input[name="food_qty[]"]');
                $food_prices = $('input[name="food_price[]"');
                foods_total = 0;
                for(i = 0; i < $food_qtys.length; i++){
                    amount = parseInt($($food_qtys[i]).val()) * parseFloat($($food_prices[i]).val());
                    foods_total += amount;
                }

                if(foods_total > 0){
                    subtotal    += foods_total;
                    html        += '<tr><td><?php echo $vv_booking_step3_js['foods']; ?></td><td class="text-right">&nbsp;</td><td class="text-right">'+vvFormatCurrency(foods_total)+'</td></tr>';
                }


                html            += '<tr><td><b><?php echo $vv_booking_step3_js['guest_total']; ?></b></td><td class="text-right">&nbsp;</td><td class="text-right"><b>'+vvFormatCurrency(subtotal)+'</b></td></tr>';
                if(promo_code_added == 1 && ambassador_commission > 0){
                    ambassador_commission_val = (daily_price * (ambassador_commission/100)) * <?php echo $numDays ?>;
                    host_final_income = subtotal - ambassador_commission_val;
                    html        += '<tr><td><?php echo $vv_booking_step3_js['ambassador_commission']; ?></td><td class="text-right">&nbsp;</td><td class="text-right">'+vvFormatCurrency(ambassador_commission_val)+'</td></tr>';
                    html        += '<tr><td><?php echo $vv_booking_step3_js['host_final_income']; ?></td><td class="text-right">&nbsp;</td><td class="text-right">'+vvFormatCurrency(host_final_income)+'</td></tr>';
                }

                $('.order_total').html(html);

            }

            function initDeleteFood(){
                $('.deleteFood').click(function (e){
                    if(confirm('<?php echo $vv_booking_step3_js['delete_addon']; ?>')){
                        $(this).parent('td').parent('tr').remove();
                        calculateTotals();
                        submitBookingForm();
                    }
                    e.preventDefault();
                });
            }

            function submitBookingForm(){
                $.post('<?php echo vv_admin_url() ?>?isajax=1', $('#formBooking').serialize(), function (){});
            }

            function checkPromoCode(){
                $('.promocode_block_inner').find('.loader').show();
                $('.promocode_block_inner').find('.btn').hide();
                $('.promocode_block_inner').find('.msg').html('');
                $.get('<?php echo vv_base_url() ?>?vv_action=booking_check_promo_code&promo_code='+$('input[name="promo_code"]').val()+'&apartment=<?php echo $apartment_id ?>', function (data){
                    $('.promocode_block_inner').find('.loader').hide();
                    $('.promocode_block_inner').find('.btn').show();
                    if(data.found == true){
                        $('input[name="promo_code_added"]').val(1);
                        $('input[name="promo_code_discount"]').val(data.discount);
                        $('input[name="ambassador_id"]').val(data.user_id);
                        promo_code_added = 1;
                        promo_code_discount = data.discount;
                        vvSuccessMsg('<?php echo $vv_booking_step3_js['promo_code_added']; ?>');
                    }else{
                        $('input[name="promo_code_added"]').val(0);
                        vvErrorMsg('<?php echo $vv_booking_step3_js['promo_code_not_found']; ?>');
                    }
                    submitBookingForm();
                    calculateTotals();
                });
            }

            var num_children = 0;
            var autocompleteTimer;
            var isClickingPromoCodeButton = true;
            $(document).ready(function (){


                $('#search_user').autoComplete({
                    resolverSettings: {
                        url: '<?php echo vv_admin_url () ?>?action=users_autocomplete'
                    }
                });

                $('#search_user').on('keyup',function (){
                    $('#user_id').val(0);
                    $('#user_email').val('');
                    $('#user_firstname').val('');
                    $('#user_lastname').val('');
                    $('.signup_wrap').show();
                });

                $('#search_user').on('autocomplete.select', function (evt, item) {
                    console.log(item);
                    if(typeof item == 'undefined'){
                       $('#user_email').focus();
                    }else{

                        $('#user_id').val(item.value);
                        $('#user_email').val(item.text);
                        $('#user_firstname').val(item.firstname);
                        $('#user_lastname').val(item.lastname);
                        $('.signup_wrap').hide();
                        submitBookingForm();
                    }
                });    


                $('input[name="children"]').change(function (){
                    num_children = $(this).val();

                    childObjs = $('#children_wrap').find('.child_age');

                    console.log(childObjs);

                    for(i = 0; i < childObjs.length; i++){
                        if(i < num_children) $(childObjs[i]).show();
                        else $(childObjs[i]).hide();
                    }



                });

                $('#user_email').change(function (){
                    if(parseInt($('#user_id').val()) > 0){
                        $('.signup_wrap').hide();
                    }else{
                        $('.signup_wrap').show();
                    }
                });

                $('.apartments_list').find('td').click(function (e){
                    id = $(this).parent('tr').attr('data-apartment_id');
                    if($('#apartment_'+id).length > 0){
                        $('#apartment_'+id).prop('checked',true);
                    }
                    e.preventDefault();
                });

                $('select[name="addon_type"]').change(function (){
                    $('.addon_type').hide();
                    $('.addon_type_'+$(this).val()).show();
                });

                $('#newFoodForm').submit(function (e){

                    qty = $(this).find('input[name="qty"]').val();

                    price = $(this).find('select[name="food"]').find(':selected').attr('data-price');
                    name = $(this).find('select[name="food"]').find(':selected').attr('data-name');
                    code = 'food-'+ $(this).find('select[name="food"]').val();
                    amount = price * qty;

                    html = '';
                    html += '<tr>';
                    html += '<td>';
                    html += '<input type="hidden" name="food_id[]" value="new"  >';
                    html += '<input type="hidden" name="food_name[]" value="'+name+'"  >';
                    html += '<input type="hidden" name="food_code[]" value="'+code+'"  >';
                    html += name;
                    html += '</td>';
                    html += '<td><input type="text" name="food_qty[]" class="form-control form-control-sm" value="'+qty+'" ></td>';
                    html += '<td><input type="text" name="food_price[]"  class="form-control form-control-sm" value="'+vvFormatCurrency(price,false)+'" ></td>';
                    html += '<td class="text-right">'+vvFormatCurrency(amount,true)+'</td>';
                    html += '<td><a href="#" class="deleteFood"><i class="fa fa-trash"></i></a></td>';
                    html += '</tr>';

                    $('.items_list').find('tbody').append(html);

                    initDeleteFood();
                    calculateTotals();
                    submitBookingForm();

                    $('input[name="food_qty[]"]').change(function (){ calculateTotals(); });
                    $('input[name="food_price[]"]').change(function (){ calculateTotals(); });

                    $('#newFood').modal('hide');

                    e.preventDefault();
                });


                $('.btnAddItem').click(function (){

                    $.get('<?php echo vv_admin_url().'?action=get_apartment_foods_json&apartment_id='.$apartment_id ?>', function (jsondata){
                        data = jsondata.data;
                        if(data.length > 0){
                            html = '';
                            for(i = 0; i < data.length; i++){
                                html += '<option value="'+data[i].food_id+'" data-name="'+data[i].name+'" data-price="'+data[i].price+'" >'+data[i].name+' - '+vvFormatCurrency(data[i].price,true)+'</option>';
                            }   
                            //alert(html);
                            $('#newFood').find('select[name="food"]').html(html);
                            $('#newFood').modal('show');
                        }else{
                            vvAlert('<?php echo $vv_booking_step3_js['no_addon_available']; ?>');
                        }
                    });
                });

                $('.calc_change').change(function (){
                    calculateTotals();
                    submitBookingForm();
                });

                $('input[name="promo_code"]').blur(function (){

                    if($(this).val() != promo_code && promo_code_added == 1 && campaign_discount == 0 && isClickingPromoCodeButton == false){
                        if(confirm('<?php echo $vv_booking_step3_js['promo_code_changed']; ?>')){
                            $('input[name="promo_code_added"]').val(0);
                            calculateTotals();
                            submitBookingForm();
                        }else{
                            $(this).val(promo_code);
                        }
                    }
                });

                // Set flag when button is being clicked
                $('.btnCheckPromoCode').on('mousedown', function () {
                    isClickingPromoCodeButton = true;
                });

                // Reset the flag shortly after mouse up
                $('.btnCheckPromoCode').on('mouseup', function () {
                    setTimeout(() => {
                        isClickingPromoCodeButton = false;
                    }, 10);
                });

                $('.btnCheckPromoCode').click(function (e){
                    if(campaign_discount > 0){
                        if(confirm('<?php echo $vv_booking_step3_js['promo_campaign_conflict']; ?>')){
                            checkPromoCode();
                        }else{
                            $('input[name="promo_code_added"]').val(0);
                            calculateTotals();
                            submitBookingForm();
                        }
                    }else{
                        checkPromoCode();
                    }
                });

                $('.item_price').change(function (){
                    qty = parseInt($(this).closest('tr').find('.item_qty').val());
                    price = parseFloat(vvCurrencyToDecimal($(this).val()));
                    amount = qty * price;
                    $(this).closest('tr').find('.item_amount').html(vvFormatCurrency(amount));
                    calculateTotals();    
                });

                initDeleteFood();
                calculateTotals();

            });
        </script>
        <?php 
        $footer_codes .= ob_get_clean();
