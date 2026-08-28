<?php 
//echo print_r_pre($booking);



$cities = vv_get_cities();

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
    <h4><?php vv_e( 'Search' ); ?></h4>

    <form id="formBooking" class="" >
        <div class="filter">
            <div>
                <div class="form-group" style="max-width:400px">
                    <input type="text" name="search" class="form-control form-control-sm" value="<?php echo GET_Request('search') ?>" placeholder="<?php echo esc_attr( vv__( 'Search by Booking #, Apartment Name, Customer' ) ); ?>" >
                </div>
            </div>
            <div class="form-group" style="max-width:200px">
                <?php
                $date = ''; 
                if(GET_Request('check_in_date') != '' && GET_Request('check_out_date') != ''){
                    $date = date("M j",strtotime(GET_Request('check_in_date'))).' - '.date("M j",strtotime(GET_Request('check_out_date')));
                }
                $start = 2025;
                $curyear = date("Y");
                ?>
                <label><?php vv_e( 'Date' ); ?></label>
                <div>
                    <select name="month" class="form-control form-control-sm d-inline w-auto" >
                        <option value=""><?php vv_e( 'All' ); ?></option>
                        <?php 
                        for($m = 1; $m < 13; $m++){ 
                            $month = date("F",mktime(0,0,0,$m));
                            ?>
                            <option value="<?php echo ($m > 9) ? $m : '0'.$m ?>" <?php echo (GET_Request('month') == $m) ? 'selected' : '' ?> ><?php echo $month ?></option>
                            <?php 
                        } 
                        ?>
                    </select>
                    <select name="year" class="form-control form-control-sm d-inline w-auto" >
                        <option value=""><?php vv_e( 'All' ); ?></option>
                        <?php for($curyear; $curyear >= $start; $curyear--){ ?>
                            <option value="<?php $curyear ?>" <?php echo (GET_Request('year') == $curyear) ? 'selected' : '' ?> ><?php echo $curyear ?></option>
                        <?php } ?>
                    </select>
                </div>
            </div>
            <div class="form-group" style="max-width:150px">
                <label><?php vv_e( 'City' ); ?></label>
                <select name="city_id" class="form-control form-control-sm" >
                    <option value=""><?php echo esc_html( vv__( '- choose city-' ) ); ?></option>
                    <?php 
                    foreach($cities as $city){
                        ?>
                        <option value="<?php echo $city['ID'] ?>" <?php echo (GET_Request('city_id') == $city['ID']) ? 'selected' : '' ?>  ><?php echo $city['post_title'] ?></option>
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
                <label><?php vv_e( 'Location/Area/Building' ); ?></label>
                <select name="district_id" class="form-control form-control-sm" >
                    <option value=""><?php echo esc_html( vv__( '- choose city first -' ) ); ?></option>
                    <?php 
                    foreach($districts as $district){
                        $locations = gArrayItem($district,'locations');
                        if(is_array($locations)){
                            foreach($locations as $location){
                                ?>
                                <option value="<?php echo $location['ID'] ?>" data-district="<?php echo $district['ID'] ?>" style="display:none" ><?php echo $location['post_title'] ?></option>
                                <?php
                            }
                        }
                    }
                    ?>
                </select>
            </div>
            <?php /*
            <div class="form-group" style="max-width:120px">
                <label>Rooms</label>
                <input type="number" name="rooms" class="form-control form-control-sm " min="0"  value="<?php echo (GET_Request('rooms') > 0) ? GET_Request('rooms') : '' ?>"   >
            </div>
            <div class="form-group" style="max-width:120px">
                <label>Adults</label>
                <input type="number" name="adults" class="form-control form-control-sm " min="0"  value="<?php echo (GET_Request('adults') > 0) ? GET_Request('adults') : '' ?>"    >
            </div>
            <div class="form-group" style="max-width:120px">
                <label>Children</label>
                <input type="number" name="children" class="form-control form-control-sm " min="0"  value="<?php echo (GET_Request('children') > 0) ? GET_Request('children') : '' ?>"    >
            </div>
            */ ?>
        </div>
        <div class="btnSearch" <?php if($step == 2) echo 'style="display:none"' ?> >
            <button type="submit" class="btn btn-primary btn-sm"><?php vv_e( 'Search' ); ?></button>
        </div>
    </form>
    <br>
    <?php
    ob_start();
    ?>

    <?php
    $vv_booking_search_js = [
        'choose_district'       => esc_js( vv__( '- choose district -' ) ),
        'choose_district_first' => esc_js( vv__( '- choose city first -' ) ),
        'choose_location'       => esc_js( vv__( '- choose location -' ) ),
    ];
    ?>

    <script>
        var num_children = 0;
        $(document).ready(function (){

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
                $(options_d[0]).html('<?php echo $vv_booking_search_js['choose_district']; ?>');

                options_l = $('select[name="district_id"]').find('option');
                $(options_l[0]).show();
                $(options_l[0]).prop('selected',true);
                $(options_l[0]).html('<?php echo $vv_booking_search_js['choose_district_first']; ?>');

                $('select[name="parent_district"]').find('option[data-city="'+$(this).val()+'"]').show();
            });

            $('select[name="parent_district"]').change(function (){
                val = $(this).val();
                $('select[name="district_id"]').find('option').hide();
                options_l = $('select[name="district_id"]').find('option');
                $(options_l[0]).show();

                if(val > 0){
                    $(options_l[0]).html('<?php echo $vv_booking_search_js['choose_location']; ?>');
                    $('select[name="district_id"]').find('option[data-district="'+$(this).val()+'"]').show();
                }else{
                    $(options_l[0]).html('<?php echo $vv_booking_search_js['choose_district_first']; ?>');
                }
            });

            <?php if($_POST){ ?>
                $('input[name="children"]').trigger('change');
            <?php } ?>


            $('select[name="city_id"]').trigger('change');
            $('select[name="parent_district').val(<?php echo GET_Request('parent_district') ?>);
            $('select[name="parent_district"]').trigger('change');
            $('select[name="district_id').val(<?php echo GET_Request('district_id') ?>);

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

