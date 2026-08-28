<?php 
//echo print_r_pre($booking);

if(gArrayItem($booking,'ID') > 0){
    foreach($booking as $i => $v){
        if(POST_Request($i) != '') $booking[$i] = POST_Request($i);
    }


    $booking_items = $this->booking_class->get_booking_items(gArrayItem($booking,'ID'));

}elseif($_POST){
    $booking = $_POST;

    $booking_items = [];
}


?>

    <form method="post" id="formBooking" >
        <input type="hidden" name="action" value="booking-search">
        <input type="hidden" name="booking_id" value="<?php echo gArrayItem($booking,'ID') ?>">
        <input type="hidden" name="user_id" value="0">
        <input type="hidden" name="check_in_date" value="<?php echo gArrayItem($booking,'check_in_date') ?>">
        <input type="hidden" name="check_out_date" value="<?php echo gArrayItem($booking,'check_out_date') ?>">
        <div class="row">
            <div class="col-md-3">
                <div class="form-group">
                    <label>District</label>
                    <select name="district_id" class="form-control form-control-sm" required>
                        <option value="">- choose district-</option>
                        <?php foreach($districts as $district){ ?>
                            <option value="<?php echo $district['district_id'] ?>" <?php if(gArrayItem($booking,'district_id') == $district['district_id']) echo 'selected' ?> ><?php echo $district['name'] ?></option>
                        <?php } ?>
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <?php
                    $date = ''; 
                    if(gArrayItem($booking,'check_in_date') != '' && gArrayItem($booking,'check_out_date') != ''){
                        $date = date("M j",gArrayItem($booking,'check_in_date')).' - '.date("M j",gArrayItem($booking,'check_out_date'));
                    }
                    ?>
                    <label>Check in Date - Check out Date</label>
                    <input type="text" class="form-control form-control-sm" name="check_in_out_date" value="<?php echo $date ?>" required >
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>Rooms/Guests</label>
                    <div class="rooms_guests_wrap">
                        <div class="row mb-2">
                            <div class="col-6">Rooms: </div>
                            <div class="col-6"><input type="number" name="rooms" class="form-control form-control-sm " min="1" required value="<?php echo gArrayItem($booking,'rooms') ?>"  ></div>
                        </div>
                        <div class="row mt-2 mb-2">
                            <div class="col-6">Adults: </div>
                            <div class="col-6"><input type="number" name="adults" class="form-control form-control-sm " min="0" required value="<?php echo gArrayItem($booking,'adults') ?>"  ></div>
                        </div>
                        <div class="row mt-2 mb-2">
                            <div class="col-6">Children: </div>
                            <div class="col-6"><input type="number" name="children" class="form-control form-control-sm " min="0" value="<?php echo gArrayItem($booking,'children') ?>"  ></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <label>&nbsp;</label>
                <div id="children_wrap">
                    <?php 
                    $html = '';
                    $child_ages = json_decode(gArrayItem($booking,'child_ages'),true);

                    for($i = 0; $i < 50; $i++){
                        $style = 'display:none;';
                        if($i < gArrayItem($booking,'children')) $style = '';

                        $html .= '<div class="row mb-2 child_age" style="'.$style.'" >';
                        $html .= '<div class="col-6">Age for Child '.($i+1).': </div>';
                        $html .= '<div class="col-6">';
                        $html .= '<select name="child_age[]" class="form-control form-control-sm" >';
                        for($x = 1; $x < 14; $x++){
                            $selected = '';
                            if($x == gArrayItem($child_ages,$i)) $selected = 'selected';
                            $html .= '<option value="'.$x.'" '.$selected.' >'.$x.'</option>';
                        }
                        $html .= '</select>';
                        $html .= '</div>';
                        $html .= '</div>';
                    }
                    echo $html;
                    ?>
                </div>
            </div>
        </div>
        <div class="mt-4">
            <button type="submit" class="btn btn-primary">Next</button>
        </div>
    </form>
    <?php

    global $footer_codes;
    ob_start();
    ?>

    <script type="text/javascript" src="https://cdn.jsdelivr.net/jquery/latest/jquery.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

    <script>
        var num_children = 0;
        $(document).ready(function (){
            $('input[name="check_in_out_date"]').daterangepicker({
                timePicker: false,
                minDate: "<?php echo date("d/m/Y") ?>",
                minSpan: { "days" : 1 },
                autoApply: true,    
                locale: {
                    format: 'MMM D'
                }
            });

            $('input[name="check_in_out_date"]').on('apply.daterangepicker', function(ev, picker) {
                $('input[name="check_in_date"]').val(new Date(picker.startDate).getTime()/1000);
                $('input[name="check_out_date"]').val(new Date(picker.endDate).getTime()/1000);
            });

            $('input[name="children"]').change(function (){
                num_children = $(this).val();

                childObjs = $('#children_wrap').find('.child_age');

                for(i = 0; i < childObjs.length; i++){
                    if(i < num_children) $(childObjs[i]).show();
                    else $(childObjs[i]).hide();
                }



            });

            <?php if($_POST){ ?>
                $('input[name="children"]').trigger('change');
            <?php } ?>

        });
    </script>
    <?php 
    $footer_codes .= ob_get_clean();
