<?php
/*
    * Template Name: Booking
*/

if ( ! session_id() ) {
	session_start();
}

$booking_data = isset( $_SESSION['BOOKING_DATA_FRONT'] ) && is_array( $_SESSION['BOOKING_DATA_FRONT'] )
	? $_SESSION['BOOKING_DATA_FRONT']
	: [];

if ( empty( $booking_data['apartment_id'] ) ) {
	wp_redirect( home_url( '/apartments/' ) );
	exit;
}

$num_guests = intval(gArrayItem($booking_data,'num_adults')) + intval(gArrayItem($booking_data,'num_children'));
$airport_pickup = intval(gArrayItem($booking_data,'airport_pickup'));

$apartment_id       = gArrayItem($booking_data,'apartment_id');
$check_in_date      = gArrayItem($booking_data,'check_in_date');
$check_out_date     = gArrayItem($booking_data,'check_out_date');

$checkInDate    = new DateTime($check_in_date);
$checkOutDate   = new DateTime($check_out_date);


$interval           = $checkInDate->diff($checkOutDate);
$nights             = $interval->days;


$data               = vv_get_booking_pricing_data($apartment_id, $check_in_date,$check_out_date);
$label              = gArrayItem($data,'label');
$subtotal           = floatval(gArrayItem($data,'total'));
$basic_discount     = floatval(gArrayItem($data,'basic_discount'));
$campaign_discount  = floatval(gArrayItem($data,'campaign_discount'));

//echo print_r_pre($data);

$apartment_class    = new vvApartments;
$apartment          = $apartment_class->get_apartment($apartment_id);

//echo print_r_pre($apartment);

$cleaning_fee           = floatval(gArrayItem($apartment,'cleaning_fee'));
$num_cleaning           = intval(gArrayItem($booking_data,'num_cleaning'));
$airport_pickup         = gArrayItem($booking_data,'airport_pickup');
$airport_pickup_cost    = floatval(vv_get_config('airport_pickup_cost'));
$payment_method         = gArrayItem($booking_data,'payment_method');
$promo_code             = gArrayItem($booking_data,'promo_code');
$promo_code_discount    = floatval(gArrayItem($booking_data,'promo_code_discount'));
$promo_code_added       = intval(gArrayItem($booking_data,'promo_code_added'));


if($payment_method == '') $payment_method = 'onsite';


get_header(); 

$conversion_rates = [];
$host_id = gArrayItem($apartment,'user_id');
$host_currency = get_user_meta($host_id,'vv_host_currency',true);
if($host_currency == '') $host_currency = 'USD';
$conversion_rates= json_decode(get_option('vv_conversion_rates_'.$host_currency),true);
if(!is_array($conversion_rates))$conversion_rates = [];


?>
<script>
    var conversionRates = <?php echo json_encode($conversion_rates ); ?>;
</script>


<div id="booking_confirmation">
    <div class="container">
        <div class="booking_confirmation_inn">
            <h2><a href="<?php echo vv_get_apartment_url($apartment).'#book-now' ?>" class="back_arr"></a>Booking confirmation</h2>
            <?php showSuccessMsg( false ); showErrorMsg( false ); ?>
            <?php if ( $promo_code_added && $promo_code !== '' ) { ?>
                <div class="alert alert-success mb-3" role="alert">
                    <strong>Vietstays conversion discount applied:</strong>
                    <?php echo intval( $promo_code_discount ); ?>% off with code <code><?php echo esc_html( $promo_code ); ?></code>
                </div>
            <?php } ?>
            <form method="post" id="formBooking">
                <div class="row">
                    <div class="col-sm-7">
                        <div class="booking_conf_left">
                            <input type="hidden" name="action" value="vv_booking_step2">
                            <input type="hidden" name="promo_code_added" value="<?php echo intval( $promo_code_added ); ?>">
                            <input type="hidden" name="promo_code_discount" value="<?php echo esc_attr( $promo_code_discount ); ?>">
                            <div class="order_details">
                                <h4>1 - Your booking</h4>
                                <ul>
                                    <li>Dates<strong><?php echo $checkInDate->format("j. M Y").' – '.$checkOutDate->format("j. M Y") ?> </strong><a href="<?php echo vv_get_apartment_url($apartment).'#book-now' ?>" class="change_btn">Change</a></li>
                                    <li>Duration<strong><?php echo ($nights > 1) ? $nights.' nights' : $nights.' night' ?>  </strong></li>
                                    <li>Guests<strong><?php echo ($num_guests > 1) ? $num_guests.' guests' : $num_guests.' guest' ?>  </strong><a href="<?php echo vv_get_apartment_url($apartment).'#book-now' ?>" class="change_btn">Change</a></li>
                                </ul>
                            </div>
                            <hr>
                            <div class="stay_block_wrap">
                                <h4>2 - Add Services to Your Stay</h4>
                                <div class="stay_block_inner">
                                    <?php if($cleaning_fee > 0){ ?>
                                        <div class="stay_block">
                                            <h5>+ Extra Cleaning Service</h5>
                                            <p>Keep your apartment fresh and comfortable during your stay by booking additional cleaning<br><a href="#">More information</a></p>
                                            <select name="num_cleaning" >
                                                <option value="">NO</option>
                                                <?php for($i = 1; $i <= $nights; $i++){ ?>
                                                    <option value="<?php echo $i ?>" <?php echo ($num_cleaning == $i) ? 'selected' : '' ?> ><?php echo $i ?> <?php echo ($i > 1) ? 'times' : 'time' ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                    <?php } ?>
                                    <div class="stay_block">
                                        <h5>+ Airport Pick-Up Service</h5>
                                        <p>Start your trip stress-free with our convenient airport pick-up service. A trusted driver will greet you.<br><a href="#">More information</a></p>
                                        <div class="switch_btn">
                                            <input type="checkbox" name="airport_pickup" value="1" <?php echo ($airport_pickup) ? 'checked' : '' ?> id="checkbox1">
                                            <label for="checkbox1"><span class="yes_text">YES</span><span class="no_text">NO</span></label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="payment_method">
                                <h4>3 - Choose how you want to pay</h4>
                                <div class="payment_method_list">
                                    <div class="block">
                                        <h5>Pay by card now</h5>
                                        <p>Pay the full amount (8 610,53 kr) now, all clear to go</p>
                                        <a href="#">More information</a>
                                        <input type="radio" name="payment_method" value="card" <?php echo ($payment_method != 'onsite') ? 'checked' : '' ?> />
                                        <span class="circle_btn"></span>
                                        <span class="border_btn"></span>
                                    </div>
                                    <div class="block">
                                        <h5>Pay when you arrive</h5>
                                        <p>Reserve your order and pay the full amount (8 610,53 kr) when you arrive</p>
                                        <a href="#">More information</a>
                                        <input type="radio" name="payment_method" value="onsite"  <?php echo ($payment_method == 'onsite') ? 'checked' : '' ?>/>
                                        <span class="circle_btn"></span>
                                        <span class="border_btn"></span>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="login_reg_order">
                                <h4>4 - Login or register to order</h4>
                                <input type="email" name="email" placeholder="Email" class="form-input" required>
                                <div id="formCheckUser">
                                    <button type="button" class="btnCheckUser" >Proceed Order</button>
                                    <p>We will match up your email and send you a verification to confirm your account.</p>
                                    <div class="or_text"><span>eller</span></div>
                                    <div class="soc_btn_list">
                                        <div class="btn_block">
                                            <a href="#" class="soc_btn"><img src="<?php bloginfo('template_url'); ?>/images/fb_btn.png" alt=""></a>
                                        </div>
                                        <div class="btn_block">
                                            <a href="#" class="soc_btn"><img src="<?php bloginfo('template_url'); ?>/images/google_btn.png" alt=""></a>
                                        </div>
                                        <div class="btn_block">    
                                            <a href="#" class="soc_btn"><img src="<?php bloginfo('template_url'); ?>/images/apple_btn.png" alt=""></a>
                                        </div>
                                        <div class="btn_block">    
                                            <a href="#" class="soc_btn"><img src="<?php bloginfo('template_url'); ?>/images/telephone_btn.png" alt="">Fortsett med telefon</a>
                                        </div>
                                    </div>
                                </div>
                                <div id="formSignup" style="display: none;" class="mt-1">
                                    <button type="button" class="btnCheckUser" >Check User</button>
                                    <p>We could not locate an account under your email. Please fill up the form below to create a new account</p>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group customlabel">
                                                <input type="text" name="firstname"id="firstname" placeholder="First Name"  value="" class="form-input" >
                                            </div>
                                        </div>
                                        <div class="col-md-6">  
                                            <div class="form-group customlabel">
                                                <input type="text" name="lastname"id="lastname" placeholder="Last Name" value="" class="form-input" >
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group mt-0 customlabel">
                                        <input type="text" class="form-input" name="address_1" placeholder="Address 1" id="address_1"  value="" >
                                    </div>

                                    <div class="form-group mt-0  customlabel">
                                        <input type="text" class="form-input"  name="address_2" placeholder="Address 2" id="address_2"  value="" >
                                    </div>
                                    <div  class="row">
                                        <div class="col-md-6">
                                            <div class="form-group mt-0 customlabel">
                                                <input type="text" class="form-input" placeholder="City"  name="city" id="city"  value=""  >
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group mt-0 customlabel">
                                                <select class="form-input"  name="country" placeholder="Country" id="country"   >
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                             <div class="form-group mt-0 customlabel">
                                                <select class="form-input"  name="state" placeholder="State" id="state"   >
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group mt-0 customlabel">
                                                <input type="text" class="form-input"  name="postalcode" placeholder="Postal Code" id="postalcode" value=""  >
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group mt-0 customlabel">
                                                <input type="tel" name="phone" placeholder="+1 555-555-5555" value="" class="form-input" id="phone" placeholder="+14155552671" />
                                                <input type="hidden" name="phone_number" id="phone_number" value="" >
                                            </div>
                                        </div>
                                    </div>
                                    <button type="button" id="btnSignUp" >Proceed Order</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-5">
                        <div class="booking_conf_right">
                            <div class="your_order_block">
                                <div class="order_list">
                                    <div class="block">
                                        <div class="img"><img src="<?php bloginfo('template_url'); ?>/images/order_img_1.png" alt=""></div>
                                        <div class="desc">
                                            <h6>Apartment</h6>
                                            <h5>Luxury Aprt 3BR – Zenity, Center View,D1</h5>
                                        </div>
                                    </div>
                                </div>
                                <hr>
                                <div class="order_total">
                                    <h4>Your order</h4>
                                    <ul>
                                    </ul>
                                </div>

                                <div class="promocode_block">
                                    <h4><a href="#">Do you have a Promo Code</a></h4>
                                    <p>Enter your referral code or discount code here</p>
                                    <div class="promocode_block_inner" <?php echo ($promo_code != '') ? 'style="display:block"' : '' ?> >
                                        <input type="text" name="promo_code" placeholder="Enter Your Code" value="<?php echo $promo_code ?>" >
                                        <div class="submit_btn">
                                            <div class="loader" style="display:none"><img src="<?php echo vv_plugins_url() ?>admin/images/ajax-loader.gif" ></div>
                                            <div class="msg"></div>
                                            <input type="button" class="btn btnCheckPromoCode" value="Submit">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php 
ob_start();

?>
<script type="text/javascript">
    
    var subtotal = <?php echo $subtotal ?>;
    var basic_discount = <?php echo $basic_discount ?>;
    var campaign_discount = <?php echo $campaign_discount ?>;
    var cleaning_fee = <?php echo floatval($cleaning_fee) ?>;
    var airport_pickup_cost = <?php echo $airport_pickup_cost ?>;
    var promo_code_discount = <?php echo $promo_code_discount ?>;
    var promo_code_added = <?php echo $promo_code_added ?>;

    function getStates(country){
        $.get("<?php echo vv_admin_url().'?vv_action=get_states_json' ?>&country="+country, function (data){
            html = '<option value="">State</option>';
            for(i = 0; i < data.length; i++){
                selected = '';
                if("<?php echo gArrayItem($user_address,'state') ?>" == ''){ 
                }else{
                    if(data[i].name == "<?php echo gArrayItem($user_address,'state') ?>" || data[i].state_code == "<?php echo gArrayItem($user_address,'state') ?>" ) selected = 'selected';
                }
                html += '<option value="'+data[i].state_code+'" '+selected+' >'+data[i].name+'</option>';
            }

            $('#state').html(html);

        });
    }

    function calculateTotals(){
        
        num_cleaning    = $('select[name="num_cleaning"]').val();
        airport_pickup  = parseInt($('input[name="airport_pickup"]:checked').val());
        promo_code      = $('input[name="promo_code"]').val();



        html        = '';
        subtotal    = <?php echo floatval($subtotal) ?>;
        discount_t  = 0;
        html        = '<li><span><?php echo $label ?></span><span class="valtxt">'+vvFormatCurrency(subtotal)+'</span></li>';

        if(campaign_discount > 0){
            subtotal = subtotal - campaign_discount;
            discount_t += campaign_discount;
            html += '<li><span>Campaign Discount</span><span class="valtxt">- '+vvFormatCurrency(campaign_discount)+'</span></li>';
        }

        if(basic_discount > 0){
            discount = (subtotal * (basic_discount/100));
            subtotal -= discount;
            discount_t += discount;
            html += '<li><span><?php echo vv_basic_discount_name() ?> ('+basic_discount+'%)</span><span class="valtxt">- '+vvFormatCurrency(discount)+'</span></li>';
        }


        if(promo_code != '' && promo_code_added == 1){
            if(campaign_discount > 0){
                html += '<li><span>Promo Code ('+promo_code+')</span><span class="valtxt">- '+vvFormatCurrency(0)+'</span></li>';
            }else{
                discount = (subtotal * (promo_code_discount/100));
                subtotal -= discount;
                discount_t = discount_t;
                html += '<li><span>Promo Code Discount ('+promo_code+' - '+promo_code_discount+'%)</span><span class="valtxt">- '+vvFormatCurrency(discount)+'</span></li>';
            }
        }

         <?php if(vv_fee() > 0){ ?>
            vv_fee      = (subtotal * (<?php echo vv_fee()/100 ?>));
            subtotal   += vv_fee;
            html        += '<li><span>Booking fee (<?php echo vv_fee() ?>%)</span><span class="valtxt">'+vvFormatCurrency(vv_fee)+'</span></li>';
        <?php } ?>


        if(cleaning_fee > 0 && num_cleaning > 0){
            cleaning_total  = cleaning_fee * num_cleaning;
            subtotal        += cleaning_total;
            html            += '<li><span>Cleaning fee</span><span class="valtxt">'+vvFormatCurrency(cleaning_total)+'</span></li>';
        }


        if(airport_pickup == 1){
            subtotal    += airport_pickup_cost;
            html        += '<li><span>Airport Pickup</span><span class="valtxt">'+vvFormatCurrency(airport_pickup_cost)+'</span></li>';
        }

        html        += '<li><span>Total</span><span class="valtxt">'+vvFormatCurrency(subtotal)+'</span></li>';

        $('.order_total').find('ul').html(html);

    }

    function checkPromoCode(){
        $('.promocode_block_inner').find('.loader').show();
        $('.promocode_block_inner').find('.btn').hide();
        $('.promocode_block_inner').find('.msg').html('');
        $.get('<?php echo vv_base_url() ?>?vv_action=booking_check_promo_code&promo_code='+$('input[name="promo_code"]').val()+'&apartment=<?php echo $apartment_id ?>', function (data){
            $('.promocode_block_inner').find('.loader').hide();
            $('.promocode_block_inner').find('.btn').show();
            if(data.found == true){
                promo_code_added = 1;
                promo_code_discount = data.discount;
            }else{
                $('.promocode_block_inner').find('.msg').html('<div class="alert alert-danger mt-2">Promo code not found!</div>');
            }
            submitBookingForm();
            calculateTotals();
        });
    }

    function submitBookingForm(){
        $.post('<?php echo get_bloginfo('url') ?>?isajax=1', $('#formBooking').serialize(), function (){});
    }

    $(document).ready(function (){

        $.get("<?php echo vv_admin_url().'?vv_action=get_countries_json' ?>", function (data){

            //console.log(data);
            html = '<option value="">Country</option>';
            for(i = 0; i < data.length; i++){
                selected = '';
                if("<?php echo gArrayItem($user_address,'country') ?>" == ''){ 
                    if(data[i].code == 'US') selected = 'selected';
                }else{
                    if(data[i].code == "<?php echo gArrayItem($user_address,'country') ?>") selected = 'selected';
                }
                html += '<option value="'+data[i].code+'" '+selected+' >'+data[i].name+'</option>';
            }

            $('#country').html(html);
            getStates($('#country').val());

            $('#country').change(function (){
                getStates($('#country').val());
            });

            
        });


        $('input[name="airport_pickup"]').click(function (){
            submitBookingForm();
            calculateTotals();
        });

        $('input[name="payment_method"]').click(function (){
            submitBookingForm();
            calculateTotals();
        });

        $('select[name="num_cleaning"]').change(function (){
            submitBookingForm();
            calculateTotals();
        });

        $('.btnCheckUser').click(function (e){
            $.get('<?php echo vv_base_url() ?>?vv_action=booking_check_user&email='+$('input[name="email"]').val(), function (data){
                if(data.ID == 'notfound'){
                    $('#formCheckUser').hide();
                    $('#formSignup').show();
                }else{
                    $('#formBooking').submit();
                }
            });

            e.preventDefault();
        });

        $('.promocode_block').find('a').click(function (e){
            $('.promocode_block').find('.promocode_block_inner').slideDown();
            e.preventDefault();
        });

        $('.btnCheckPromoCode').click(function (e){
            if(campaign_discount > 0){
                if(confirm('We cannot add your promocode to your booking due to existing campaign discount" - Do you want to continue without discount?')){
                    checkPromoCode();
                }
            }else{
                checkPromoCode();
            }
        });


        calculateTotals();
    });

</script>
<?php

global $footer_codes;
$footer_codes .= ob_get_clean();

get_footer(); 
?>