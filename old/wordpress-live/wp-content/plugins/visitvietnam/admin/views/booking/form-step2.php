<?php 
//echo print_r_pre($booking);

/*
if(gArrayItem($booking,'ID') > 0){

    foreach($booking as $i => $v){
        if(!in_array($i,['booking_id','ID','user_id'])){
            if(POST_Request($i) != '' && $i != 'booking_id') $booking[$i] = POST_Request($i);
        }
    }


    $booking_items = $this->booking_class->get_booking_items(gArrayItem($booking,'ID'));

}elseif($_POST){
    $booking = $_POST;

    $booking_items = [];
}
*/

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
//echo print_r_pre($booking);

$choose_apartment_msg = esc_js( vv__( 'You need to choose an apartment to continue.' ) );

?>
        <form method="post" id="formBooking2">
            <input type="hidden" name="action" value="booking-add_apartment">
            <input type="hidden" name="step" value="<?php echo $step ?>">
            <input type="hidden" name="booking_id" value="<?php echo gArrayItem($booking,'ID') ?>">
            <input type="hidden" name="user_id" id="user_id" value="<?php echo intval(gArrayItem($booking,'user_id')) ?>">
            <input type="hidden" name="check_in_date" value="<?php echo gArrayItem($booking,'check_in_date') ?>" >
            <input type="hidden" name="check_out_date" value="<?php echo gArrayItem($booking,'check_out_date') ?>" >
            <input type="hidden" name="rooms" value="<?php echo gArrayItem($booking,'rooms') ?>" >
            <input type="hidden" name="adults" value="<?php echo gArrayItem($booking,'adults') ?>" >
            <input type="hidden" name="children" value="<?php echo gArrayItem($booking,'children') ?>" >
            <input type="hidden" name="district_id" value="<?php echo gArrayItem($booking,'district_id') ?>" >
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
            <h4 class="mb-2 mt-4"><?php vv_e( 'Search Results: Apartments' ); ?></h4>
            <div class="table-responsive  m-b-30">
                <table class="table  table-striped apartments_list">
                    <thead>
                        <tr>
                            <th style="width:20px"><?php vv_e( 'Choose' ); ?></th>
                            <th class="text-left"><?php vv_e( 'Apartment' ); ?></th>
                            <th style="max-width:200px" class="text-left"><?php vv_e( 'District' ); ?></th>
                            <th style="width:100px" class="text-center"><?php vv_e( 'Rooms' ); ?></th>
                            <th style="width:120px" class="text-center"><?php vv_e( 'Max Guests' ); ?></th>
                            <th style="width:200px"><?php vv_e( 'Daily Price' ); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $ctr = 0;
                        foreach($apartments as $apartment){

                            $apartment_id = $apartment['ID'];

                            $check_in_date = gArrayItem($booking,'check_in_date');
                            if($check_in_date == '') $check_in_date = date("Y-m-d");

                            $check_out_date = gArrayItem($booking,'check_out_date');
                            if($check_out_date == '') $check_out_date = date("Y-m-d",strtotime("+1 Day"));

                            $checkInDate    = new DateTime($check_in_date);
                            $checkOutDate   = new DateTime($check_out_date);

                            $numDays        = $checkInDate->diff($checkOutDate)->days + 1;

                            $data               = vv_get_booking_pricing_data($apartment_id, $check_in_date,$check_out_date);
                            $label              = gArrayItem($data,'label');
                            $subtotal           = floatval(gArrayItem($data,'total'));
                            $basic_discount     = floatval(gArrayItem($data,'basic_discount'));
                            $campaign_discount  = floatval(gArrayItem($data,'campaign_discount'));
                            $dprices            = gArrayItem($data,'dprices');

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

                            $district_name = vv_get_district_display_name(gArrayItem($apartment,'district'), $districts);

                            $checked = '';
                            if(gArrayItem($booking,'ID') > 0){
                                if($apartment['ID'] == gArrayItem($booking,'apartment_id')) $checked = 'checked';
                            }


                            ?>
                            <tr data-apartment_id="<?php echo $apartment['ID'] ?>">
                                <td>
                                    <input type="hidden" name="price_<?php echo $apartment['ID'] ?>" value="<?php echo $price ?>" >
                                    <input type="hidden" name="discount_<?php echo $apartment['ID'] ?>" value="<?php echo $discount ?>" >

                                    <label class="switch switch-default switch-success-outline-alt switch-pill mr-2">
                                      <input type="radio" name="apartment_id" class="switch-input" <?php echo $checked ?> id="apartment_<?php echo $apartment['ID'] ?>" value="<?php echo $apartment['ID'] ?>"  >
                                      <span class="switch-label"></span>
                                      <span class="switch-handle"></span>
                                    </label>

                                </td>
                                <td><?php echo stripslashes(gArrayItem($apartment,'name')) ?></td>
                                <td><?php echo $district_name ?></td>
                                <td class="text-center"><?php echo gArrayItem($apartment,'rooms') ?></td>
                                <td class="text-center"><?php echo gArrayItem($apartment,'max_guests') ?></td>
                                <td><?php echo $price_range ?></td>
                            </tr>
                            <tr class="apartment_info apartment_info_<?php echo $apartment_id ?>" style="display:none">
                                <td colspan="6">
                                    <table align="right" class="table w-auto table-border border bg-white">
                                        <tr class="bg-white">
                                            <td><?php echo $label ?></td>
                                            <td>&nbsp;</td>
                                            <td><?php echo vv_number_format($subtotal,true) ?></td>
                                        </tr>
                                        <?php 
                                        if($campaign_discount > 0){
                                            $subtotal -= $campaign_discount;
                                            ?>
                                            <tr class="bg-white">
                                                <td><?php vv_e( 'Campaign Discount' ); ?></td>
                                                <td>&nbsp;</td>
                                                <td class="text-right"><?php echo vv_number_format($campaign_discount,true) ?></td>
                                            </tr>
                                            <?php
                                        }
                                        if($basic_discount > 0){
                                            $basic_discount         = $basic_discount;
                                            $basic_discount_val     = $subtotal * ($basic_discount/100);
                                            $subtotal -= $basic_discount_val;
                                            ?>
                                            <tr class="bg-white">
                                                <td class="text-left"><?php echo vv_basic_discount_name() ?>: </td>
                                                <td class="text-right"><?php echo $basic_discount ?>%</td>
                                                <td class="text-right"><?php echo vv_number_format($basic_discount_val,true) ?></td>
                                            </tr>
                                            <?php
                                        }
                                        ?>
                                    </table>
                                </td>
                            </tr>
                            <?php
                            $ctr++; 
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                <button type="submit" class="btn btn-primary"><?php vv_e( 'Continue' ); ?></button>
            </div>
        </form>


        <?php
        ob_start();
        ?>




        <script type="text/javascript">
            var apartment_radio;

            var num_children = 0;
            var autocompleteTimer;
            $(document).ready(function (){


                $('.apartments_list').find('td').click(function (e){
                    id = $(this).parent('tr').attr('data-apartment_id');
                    if($('#apartment_'+id).length > 0){
                        $('#apartment_'+id).prop('checked',true);
                        $('.apartment_info').hide();
                        $(this).parent().next('tr').show();
                    }
                    e.preventDefault();
                });

                $('select[name="addon_type"]').change(function (){
                    $('.addon_type').hide();
                    $('.addon_type_'+$(this).val()).show();
                });


                $('.apartments_list').find('.switch').click(function (){
                    apartment_radio = $(this).find('input');
                    $('.apartment_info').hide();
                    setTimeout(function (){
                        if(apartment_radio.is(":checked")){
                            apartment_id = apartment_radio.val();
                            $('.apartment_info_'+apartment_id).show();
                        }    
                    },300);
                    
                });

                $('#formBooking2').submit(function (e){
                    apartment_radio = $('input[name="apartment_id"]:checked');
                    if(apartment_radio.length){                        
                    }else{
                        vvShowNotification('<div class="alert alert-danger"><?php echo $choose_apartment_msg; ?> <span class="btnCloseNotification">&times;</span></div>');
                        e.preventDefault();
                    }
                });


            });
        </script>
        <?php 
        $footer_codes .= ob_get_clean();
