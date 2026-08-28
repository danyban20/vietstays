<?php 
//echo print_r_pre($booking);


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


?>
        <form method="post" id="formBooking">
            <input type="hidden" name="action" value="booking-save">
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
            <h4 class="mb-2">Apartments</h4>
            <div class="table-responsive table--no-card m-b-30">
                <table class="table table-borderless table-striped table-earning apartments_list">
                    <thead>
                        <tr>
                            <th style="width:20px">&nbsp;</th>
                            <th class="text-left">District</th>
                            <th class="text-left">Apartment</th>
                            <th class="text-center">Rooms</th>
                            <th class="text-center">Max Guests</th>
                            <th>Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $ctr = 0;
                        foreach($apartments as $apartment){

                            $check_in_date = gArrayItem($booking,'check_in_date');
                            if($check_in_date == '') $check_in_date = date("Y-m-d");

                            $check_in_time =  strtotime($check_in_date);
                            $dayofweek = date("w",$check_in_time);


                            $pricing = json_decode($apartment['pricing'],true);
                            if(!is_array($pricing)) $pricing = array();

                            $price = 0;

                            if(count($pricing) > 1){
                                $price = gArrayItem($pricing,$dayofweek);
                            }

                            $discount = $this->apartment_class->get_discount_val($apartment['ID'],strtotime($check_in_date.' 00:00:00'));

                            $district_name = '';
                            foreach($districts as $district){
                                if($district['ID'] == gArrayItem($apartment,'district')) $district_name = $district['name'];
                            }

                            $checked = '';
                            if(gArrayItem($booking,'ID') > 0){
                                if($apartment['ID'] == gArrayItem($booking,'apartment_id')) $checked = 'checked';
                            }

                            if($discount > 0){
                                $price_str = '<span style="text-decoration:line-through;color:#aaa" >$'.$price.'</span> $'.number_format($price - ($price * ($discount/100)),2);
                            }else{
                                $price_str = '$'.number_format($price,2);
                            }

                            ?>
                            <tr data-apartment_id="<?php echo $apartment['ID'] ?>">
                                <td>
                                    <input type="hidden" name="price_<?php echo $apartment['ID'] ?>" value="<?php echo $price ?>" >
                                    <input type="hidden" name="discount_<?php echo $apartment['ID'] ?>" value="<?php echo $discount ?>" >

                                    <label class="switch switch-default switch-success-outline-alt switch-pill mr-2">
                                      <input type="radio" name="apartment_id" class="switch-input" <?php echo $checked ?> id="apartment_<?php echo $apartment['ID'] ?>" value="<?php echo $apartment['ID'] ?>" required >
                                      <span class="switch-label"></span>
                                      <span class="switch-handle"></span>
                                    </label>

                                </td>
                                <td><?php echo $district_name ?></td>
                                <td><?php echo stripslashes(gArrayItem($apartment,'name')) ?></td>
                                <td class="text-center"><?php echo gArrayItem($apartment,'rooms') ?></td>
                                <td class="text-center"><?php echo gArrayItem($apartment,'max_guests') ?></td>
                                <td><?php echo $price_str ?></td>
                            </tr>
                            <?php
                            $ctr++; 
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            <br>
            <h4 class="mb-2">Customer Info</h4>
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group"> 
                        <label>Email</label>
                        <input type="email" name="email" value="<?php echo gArrayItem($booking,'email') ?>" id="user_email" class="form-control form-control-sm" autocomplete="no" required >
                        <div class="position-relative"><div id="vv-autocomplete_result"></div></div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group"> 
                        <label>First Name</label>
                        <input type="text" name="firstname" value="<?php echo gArrayItem($booking,'firstname') ?>" id="user_firstname" class="form-control form-control-sm" required >
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group"> 
                        <label>Last name</label>
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
                    <span>Create an Account & Send Registration Email</span> 
                </div>
            </div>
            <br>
            <div class="row mb-2">
                <div class="col-6">
                    <h4>Addons </h4>
                </div>
                <div class="col-6 text-right">
                    <button type="button" class="btn btn-info btn-sm btnAddItem" >New Addon</button>
                </div>
            </div>
            <div class="table-responsive table--no-card m-b-30">
                <table class="table table-borderless table-striped table-earning items_list">
                    <thead>
                        <tr>
                            <th class="text-left">Name</th>
                            <th class="text-right" style="width:100px">Qty</th>
                            <th class="text-right" style="width:100px">Price</th>
                            <th class="text-right" style="width:100px">Amount</th>
                            <th style="width:100px">&nbsp;</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        foreach($booking_items as $item){
                            ?>
                            <tr class="booking_<?php echo $item['item_id'] ?>" >
                                <td>
                                    <input type="hidden" name="item_id[]" value="<?php echo $item['item_id'] ?>"  >
                                    <input type="hidden" name="item_name[]" value="<?php echo $item['name'] ?>"  >
                                    <input type="hidden" name="item_code[]" value="<?php echo $item['code'] ?>"  >
                                    <?php echo $item['name'] ?>
                                </td>
                                <td><input type="text" name="item_qty[]" class="form-control form-control-sm" value="<?php echo $item['qty'] ?>" ></td>
                                <td><input type="text" name="item_price[]"  class="form-control form-control-sm" value="<?php echo number_format($item['price'],2) ?>" ></td>
                                <td class="text-right"><?php echo '$'.number_format($item['price'] * $item['qty'],2) ?></td>
                                <td><a href="#" ><i class="fa fa-trash"></i></a></td>
                            </tr>
                            <?php 
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            <?php if(gArrayItem($booking,'ID') > 0){ ?>
                <div class="form-group">
                    <label class="switch switch-default switch-success-outline-alt switch-pill mr-2">
                        <input type="radio" name="resent_booking_confirmation" class="switch-input"  value="1"  > 
                        <span class="switch-label"></span>
                        <span class="switch-handle"></span>
                    </label>
                    <span>Resend Booking Confirmation</span> 
                </div>
            <?php } ?>
            <hr>
            <div class="mt-4">
                <?php 
                if(gArrayItem($booking,'ID') > 0) $url = vv_admin_url().'booking-edit/?id='.gArrayItem($booking,'ID');
                else $url = vv_admin_url().'booking-add';
                ?>
                <a href="<?php echo $url ?>" class="btn btn-secondary">Back</a>
                <button type="submit" class="btn btn-primary">Submit</button>
            </div>
        </form>


        <?php

        global $footer_codes;
        ob_start();
        ?>

        <div class="modal fade" id="newAddon" tabindex="-1" role="dialog" aria-labelledby="newAddonLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <form method="post" id="newAddonForm" >
                        <div class="modal-header">
                            <h5 class="modal-title" id="newAddonLabel">New Addon</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <label>Addon Type</label>
                                <select name="addon_type" class="form-control form-control-sm" required >
                                    <option value="" selected >-choose addon type-</option>
                                    <option value="airport_pickup" data-name="Airport Pick up" data-price="<?php echo vv_get_config('airport_pickup_cost') ?>" >Airport Pick up - $<?php echo number_format(vv_get_config('airport_pickup_cost'),2) ?></option>
                                    <option value="scooter_rental" data-name="Scooter Rental" data-price="<?php echo vv_get_config('scooter_rental_cost') ?>">Scooter Rental - $<?php echo number_format(vv_get_config('scooter_rental_cost'),2) ?></option>
                                    <option value="food">Food</option>
                                </select>
                            </div>
                            <div class="form-group addon_type addon_type_food" style="display:none;">
                                <label>Food</label>
                                <select name="food" class="form-control form-control-sm">
                                    <?php foreach($foods as $food){ ?>
                                        <option value="<?php echo $food['food_id'] ?>" data-name="<?php echo $food['name'] ?>" data-price="<?php echo $food['price'] ?>" ><?php echo $food['name'].' - '.'$'.number_format($food['price']) ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="form-group addon_type addon_type_food addon_type_scooter_rental" style="display: none;">
                                <label>Qty</label>
                                <input type="text" name="qty" value="1" class="form-control form-control-sm" >
                            </div>
                            <div class="mt-2"><button type="submit" class="btn btn-primary btn-sm" >Add</button></div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap-4-autocomplete/dist/bootstrap-4-autocomplete.min.js" crossorigin="anonymous"></script>


        <script type="text/javascript">
            var apartment_radio;

            function initAutocompleteClick(){
                $('#vv-autocomplete_result').find('a').click(function (e){

                    $('#user_id').val($(this).attr('data-user_id'));
                    $('#user_firstname').val($(this).attr('data-firstname'));
                    $('#user_lastname').val($(this).attr('data-lastname'));
                    $('#user_email').val($(this).attr('data-email'));

                    $('#vv-autocomplete_result').hide();

                    e.preventDefault();
                });
            }


            var num_children = 0;
            var autocompleteTimer;
            $(document).ready(function (){

                $('#user_email').on('keyup',function(){
                    search = $(this).val();
                    if(search.length > 2){
                        clearTimeout(autocompleteTimer);
                        autocompleteTimer = setTimeout(function (){
                            $.get('<?php echo vv_admin_url () ?>?action=users_autocomplete&search=' + search, function (data){
                                $('#vv-autocomplete_result').html(data);
                                if(data != ''){
                                    $('#vv-autocomplete_result').show();
                                }else{
                                    $('#vv-autocomplete_result').hide();
                                }
                                initAutocompleteClick();
                            });
                        },500);
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

                $('#newAddonForm').submit(function (e){

                    addon_type = $(this).find('select[name="addon_type"]').val();

                    qty = $(this).find('input[name="qty"]').val();

                    if(addon_type == 'food'){
                        price = $(this).find('select[name="food"]').find(':selected').attr('data-price');
                        name = $(this).find('select[name="food"]').find(':selected').attr('data-name');
                        code = 'food-'+ $(this).find('select[name="food"]').val();
                    }else{
                        price = $(this).find('select[name="addon_type"]').find(':selected').attr('data-price');
                        name = $(this).find('select[name="addon_type"]').find(':selected').attr('data-name');
                        code = addon_type;
                    }

                    amount = price * qty;

                    html = '';
                    html += '<tr>';
                    html += '<td>';
                    html += '<input type="hidden" name="item_id[]" value="new"  >';
                    html += '<input type="hidden" name="item_name[]" value="'+name+'"  >';
                    html += '<input type="hidden" name="item_code[]" value="'+code+'"  >';
                    html += name;
                    html += '</td>';
                    html += '<td><input type="text" name="item_qty[]" class="form-control form-control-sm" value="'+qty+'" ></td>';
                    html += '<td><input type="text" name="item_price[]"  class="form-control form-control-sm" value="'+number_format(price,2)+'" ></td>';
                    html += '<td class="text-right">$'+number_format(amount,2)+'</td>';
                    html += '<td><a href="#" ><i class="fa fa-trash"></i></a></td>';
                    html += '</tr>';

                    $('.items_list').find('tbody').append(html);

                    $('#newAddon').modal('hide');

                    e.preventDefault();
                });


                $('.btnAddItem').click(function (){

                    apartment_radio = $('input[name="apartment_id"]:checked');
                    if(apartment_radio.length){                        
                        $.get('<?php echo vv_admin_url().'?action=get_apartment_foods_json&apartment_id=' ?>' + apartment_radio.val(), function (data){
                            html = '';
                            for(i = 0; i < data.length; i++){
                                html += '<option value="'+data[i].food_id+'" data-name="'+data[i].name+'" data-price="<'+data[i].price+'" >'+data[i].name+' - $'+number_format(data[i].price,2)+'</option>';
                            }   
                            //alert(html);
                            $('#newAddon').find('select[name="food"]').html(html);
                            $('#newAddon').modal('show');
                        });
                    }else{
                        alert('Please choose an apartment');
                    }
                });

            });
        </script>
        <?php 
        $footer_codes .= ob_get_clean();
