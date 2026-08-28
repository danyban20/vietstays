<?php 
//echo print_r_pre($booking);



$cities = vv_get_cities();
//echo print_r_pre($districts);
//echo print_r_pre($districts);

ob_start();
?>
<style type="text/css">
    .filter .form-group{
        display:inline-block;
        width:100%;
        margin-right:10px;
    }    
</style>
<?php
$header_codes .= ob_get_clean();
?>

    <form method="post" id="formBooking" class="" >
        <input type="hidden" name="action" value="booking-search">
        <input type="hidden" name="booking_id" value="<?php echo gArrayItem($booking,'ID') ?>">
        <input type="hidden" name="step" value="<?php echo $step ?>" >
        <input type="hidden" name="user_id" value="0">
        <input type="hidden" name="check_in_date" value="<?php echo gArrayItem($booking,'check_in_date') ?>">
        <input type="hidden" name="check_out_date" value="<?php echo gArrayItem($booking,'check_out_date') ?>">
        <div class="filter">
            <div>
                <div class="form-group" style="max-width:400px">
                    <label><?php vv_e( 'Search' ); ?></label>
                    <input type="text" name="search" class="form-control form-control-sm" value="<?php echo gArrayItem($booking,'search') ?>" placeholder="<?php echo esc_attr( vv__( 'Search by apartment name, number or matchcode' ) ); ?>" >
                </div>
            </div>
            <div class="form-group" style="max-width:150px">
                <?php
                $date = ''; 
                if(gArrayItem($booking,'check_in_date') != '' && gArrayItem($booking,'check_out_date') != ''){
                    $date = date("M j",strtotime(gArrayItem($booking,'check_in_date'))).' - '.date("M j",strtotime(gArrayItem($booking,'check_out_date')));
                }
                ?>
                <label><?php vv_e( 'Check-In/Out Date' ); ?></label>
                <input type="text" class="form-control form-control-sm" name="check_in_out_date" value="<?php echo $date ?>" required  >
            </div>
            <div class="form-group" style="max-width:150px">
                <label><?php vv_e( 'City' ); ?></label>
                <select name="city_id" class="form-control form-control-sm" >
                    <option value=""><?php echo esc_html( vv__( '- choose district-' ) ); ?></option>
                    <?php 
                    foreach($cities as $city){
                        ?>
                        <option value="<?php echo $city['ID'] ?>" <?php echo (gArrayItem($booking,'city_id') == $city['ID']) ? 'selected' : '' ?>  ><?php echo $city['post_title'] ?></option>
                        <?php
                    }
                    ?>
                </select>
            </div>
            <div class="form-group" style="max-width:150px">
                <label><?php vv_e( 'District' ); ?></label>
                <select name="parent_district" class="form-control form-control-sm" >
                    <option value=""><?php echo esc_html( vv__( '- choose city first -' ) ); ?></option>
                    <?php 
                    foreach($districts as $district){
                        $d_city_id = get_post_meta($district['ID'],'city',true);
                        ?>
                        <option value="<?php echo $district['ID'] ?>" data-city="<?php echo $d_city_id ?>" style="display:none" <?php echo ($booking['parent_district'] == $district['ID']) ? 'selected' : '' ?>  ><?php echo $district['post_title'] ?></option>
                        <?php
                    }
                    ?>
                </select>
            </div>
            <div class="form-group" style="max-width:150px">
                <label><?php vv_e( 'Neighbourhood' ); ?></label>
                <select name="district_id" class="form-control form-control-sm" >
                    <option value=""><?php echo esc_html( vv__( '- choose city first -' ) ); ?></option>
                    <?php 
                    foreach($districts as $district){
                        $neighbourhoods = gArrayItem($district,'neighbourhoods');
                        if(is_array($neighbourhoods)){
                            foreach($neighbourhoods as $neighbourhood){
                                ?>
                                <option value="<?php echo $neighbourhood['ID'] ?>" data-district="<?php echo $district['ID'] ?>" style="display:none" ><?php echo $neighbourhood['post_title'] ?></option>
                                <?php
                            }
                        }
                    }
                    ?>
                </select>
            </div>
            <div class="form-group" style="max-width:120px">
                <label><?php vv_e( 'Rooms' ); ?></label>
                <input type="number" name="rooms" class="form-control form-control-sm " min="1"  value="<?php echo (gArrayItem($booking,'rooms') > 0) ? gArrayItem($booking,'rooms') : 1 ?>"   >
            </div>
            <div class="form-group" style="max-width:120px">
                <label><?php vv_e( 'Adults' ); ?></label>
                <input type="number" name="adults" class="form-control form-control-sm " min="1"  value="<?php echo (gArrayItem($booking,'adults') > 0) ? gArrayItem($booking,'adults') : 1 ?>" required   >
            </div>
            <div class="form-group" style="max-width:120px">
                <label><?php vv_e( 'Children' ); ?></label>
                <input type="number" name="children" class="form-control form-control-sm " min="0"  value="<?php echo intval(gArrayItem($booking,'children')) ?>"    >
            </div>
            <div class="form-group" style="max-width:120px">
                <label><?php vv_e( 'Max Daily Price' ); ?></label>
                <input type="number" name="max_daily_price" class="form-control form-control-sm " min="1"  value="<?php echo gArrayItem($booking,'max_daily_price') ?>"   >
            </div>
        </div>
        <div class="btnSearch" <?php if($step == 2) echo 'style="display:none"' ?> >
            <button type="submit" class="btn btn-primary btn-sm"><?php vv_e( 'Search' ); ?></button>
        </div>
    </form>
    <br>
    <?php

    ob_start();
    ?>

    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

    <?php
    $vv_booking_form_js = [
        'choose_district'       => esc_js( vv__( '- choose district -' ) ),
        'choose_district_first' => esc_js( vv__( '- choose city first -' ) ),
        'choose_neighbourhood'  => esc_js( vv__( '- choose neighbourhood -' ) ),
    ];
    ?>

    <script>
        <?php
        $apartment_id = gArrayItem($booking,'apartment_id');
        $cdate = strtotime($booking['check_in_date']);
        $month  = date("n",$cdate);
        $year   = date("Y",$cdate);
        $day    = date("j");

            //echo $day.' - '.date("t");
            if( $day == date("t") ) $month++; // SET TO NEXT MONTH IF THE NEXT DAY IS LAST DAY OF MONTH BECAUSE ITS BLOCK

        $start_date = date("Y-m-d",mktime(0,0,0,$month,1,$year));
        $end_date   = date("Y-m-d",mktime(23,59,59,$month,date('t',$start_date),$year));

        $filter = ['start_date' => $start_date, 'end_date' => $end_date,'apartment_ids' =>  [$apartment_id],'exclude_ids' => [$booking['ID']]];
        $bookings = $this->booking_class->get_bookings($filter);

        $invalidDates = [];
        foreach($bookings as $booking2){
            $dates = json_decode(gArrayItem($booking2,'dates'),true);
            if(is_array($dates)) $invalidDates = array_merge($invalidDates,$dates);
        }
        $invalidDates = array_unique($invalidDates);
        ?>

        var num_children = 0;
        $(document).ready(function (){
            $('input[name="check_in_out_date"]').daterangepicker({
                timePicker: false,
                minDate: "<?php echo date("Y-m-d") ?>",
                locale: {
                    format: 'MMM D'
                },
                isInvalidDate: function(date) {
                    // Dates you want to disable
                    const disabledDates = [<?php echo "'".implode("','",$invalidDates)."'" ?>];

                    found = false;
                    for(i = 0; i < disabledDates.length; i++){
                        console.log(disabledDates[i] + ' == '+date.format('YYYY-MM-DD'));
                        if(disabledDates[i] ==  date.format('YYYY-MM-DD')) found = true;
                    }
                    return found;
                }
            });

            $('input[name="check_in_out_date"]').on('apply.daterangepicker', function(ev, picker) {
                $('input[name="check_in_date"]').val(picker.startDate.format("YYYY-MM-DD"));
                $('input[name="check_out_date"]').val(picker.endDate.format("YYYY-MM-DD"));
            });

            $('input[name="children"]').change(function (){
                num_children = $(this).val();

                childObjs = $('#children_wrap').find('.child_age');

                for(i = 0; i < childObjs.length; i++){
                    if(i < num_children) $(childObjs[i]).show();
                    else $(childObjs[i]).hide();
                }

            });

            $('select[name="city_id"]').change(function (){
                val = $(this).val();

                $('select[name="parent_district"]').find('option').hide();
                $('select[name="district_id"]').find('option').hide();

                options_d = $('select[name="parent_district"]').find('option');
                $(options_d[0]).show();
                $(options_d[0]).prop('selected',true);
                $(options_d[0]).html('<?php echo $vv_booking_form_js['choose_district']; ?>');

                options_l = $('select[name="district_id"]').find('option');
                $(options_l[0]).show();
                $(options_l[0]).prop('selected',true);
                $(options_l[0]).html('<?php echo $vv_booking_form_js['choose_district_first']; ?>');

                $('select[name="parent_district"]').find('option[data-city="'+$(this).val()+'"]').show();
            });

            $('select[name="parent_district"]').change(function (){
                val = $(this).val();
                $('select[name="district_id"]').find('option').hide();
                options_l = $('select[name="district_id"]').find('option');
                $(options_l[0]).show();

                if(val > 0){
                    $(options_l[0]).html('<?php echo $vv_booking_form_js['choose_neighbourhood']; ?>');
                    $('select[name="district_id"]').find('option[data-district="'+$(this).val()+'"]').show();
                }else{
                    $(options_l[0]).html('<?php echo $vv_booking_form_js['choose_district_first']; ?>');
                }
            });

            <?php if($_POST){ ?>
                $('input[name="children"]').trigger('change');
            <?php } ?>


            <?php
            if(intval($booking_id) > 0){
                if($step < 3){            
                    ?>
                    $('select[name="city_id"]').trigger('change');
                    $('select[name="parent_district').val(<?php echo $booking['parent_district'] ?>);
                    $('select[name="parent_district"]').trigger('change');
                    $('select[name="district_id').val(<?php echo $booking['district_id'] ?>);
                    <?php
                }
            }
            ?>

            $('#formBooking').find('input').change(function (){
                $('#formBooking2').hide();
                $('#formBooking').find('.btnSearch').show();
            });
            $('#formBooking').find('select').change(function (){
                $('#formBooking2').hide();
                $('#formBooking').find('.btnSearch').show();
            });
        });
    </script>
    <?php 
    $footer_codes .= ob_get_clean();

