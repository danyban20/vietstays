<?php 
$user_address   = [];
$firstname      = '';
$lastname       = '';
$user_id        = gArrayItem($user_data,'ID');
$email          = '';
$company_name   = '';
$company_id     = 0;
if($user_id > 0){
    //echo get_user_meta($user_id,'user_address',true);
    $user_address = json_decode(get_user_meta($user_id,'user_address',true),true);
    if(!is_array($user_address)) $user_address = [];
    //echo print_r_pre($user_address);

    $email      = gArrayItem($user_data,'user_email');
    $firstname  = get_user_meta($user_id,'first_name',true);
    $lastname   = get_user_meta($user_id,'last_name',true);




    $user_level = get_user_meta($user_id,'vv_user_level',true);
    if($user_level == '') $user_level = 'customer';

}

if($email == '') $email = POST_Request('email');
if($firstname == '') $firstname = POST_Request('firstname');
if($lastname == '') $lastname = POST_Request('lastname');


?>


<form method="post" enctype="multipart/form-data">
    <input type="hidden" name="vv_action" value="save_staff">
    <input type="hidden" name="user_id" value="<?php echo $user_id ?>" >
    <input type="hidden" name"user_level" value="staff">
    <input type="hidden" name="host_id" value="<?php echo $host_id ?>" >
    <div class="row">
        <div class="col-md-7 formWrap">
            <div class="card">
                <div class="card-header"><h4><?php vv_e( 'Personal Details' ); ?></h4></div>
                <div class="card-body">


                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group customlabel">
                                <label><?php vv_e( 'Email' ); ?></label>
                                <input type="email" name="email" value="<?php echo $email ?>" class="form-control form-control-sm" required >
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mt-0 customlabel">
                                <label for="web_url"><?php vv_e( 'Title' ); ?></label>
                                <input type="text" class="form-control form-control-sm" name="title" id="title"  value="<?php echo gArrayItem($user_address,'title') ?>" >
                            </div>
                        </div>
                    </div>


                    <div class="row">
                        <div class="col-md-2">
                            <div class="form-group customlabel">
                                <label><?php vv_e( 'Prefix' ); ?></label>
                                <input type="text" name="prefix" value="<?php echo gArrayItem($user_address,'prefix') ?>" class="form-control form-control-sm"   >
                            </div>
                        </div>
                        <div class="col-md-4 ">
                            <div class="form-group customlabel">
                                <label><?php vv_e( 'First Name' ); ?></label>
                                <input type="text" name="firstname"id="firstname"  value="<?php echo $firstname ?>" class="form-control form-control-sm" >
                            </div>
                        </div>
                        <div class="col-md-4">  
                            <div class="form-group customlabel">
                                <label><?php vv_e( 'Last Name' ); ?></label>
                                <input type="text" name="lastname"id="lastname" value="<?php echo $lastname ?>" class="form-control form-control-sm" >
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group customlabel">
                                <label><?php vv_e( 'Suffix' ); ?></label>
                                <input type="text" name="suffix" value="<?php echo gArrayItem($user_address,'suffix') ?>" class="form-control form-control-sm"  >
                            </div>
                        </div>
                    </div>

                    <div class="form-group mt-0 customlabel">
                        <label for="address_1"><?php vv_e( 'Address 1' ); ?></label>
                        <input type="text" class="form-control form-control-sm" name="address_1" id="address_1"  value="<?php echo gArrayItem($user_address,'address_1') ?>"  >
                    </div>

                    <div class="form-group mt-0  customlabel">
                        <label for="address_2"><?php vv_e( 'Address 2' ); ?></label>
                        <input type="text" class="form-control form-control-sm"  name="address_2" id="address_2"  value="<?php echo gArrayItem($user_address,'address_2') ?>" >
                    </div>
                    <div  class="row">
                        <div class="col-md-6">
                            <div class="form-group mt-0 customlabel">
                                <label for="postalCode"><?php vv_e( 'City' ); ?></label>
                                <input type="text" class="form-control form-control-sm"  name="city" id="city"  value="<?php echo gArrayItem($user_address,'city') ?>"  >
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mt-0 customlabel">
                               <label for="postalCode"><?php vv_e( 'Country' ); ?></label>
                                <select class="form-control form-control-sm"  name="country" id="country"   >
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                             <div class="form-group mt-0 customlabel">
                                <label for="postalCode"><?php vv_e( 'State' ); ?></label>
                                <select class="form-control form-control-sm"  name="state" id="state"   >
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">

                            <div class="form-group mt-0 customlabel">
                                <label for="postalCode"><?php vv_e( 'Postal Code' ); ?></label>
                                <input type="text" class="form-control form-control-sm"  name="postalcode" id="postalcode" value="<?php echo gArrayItem($user_address,'postalcode') ?>"   >
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <?php
                            $phone_number = gArrayItem($user_address,'phone_number');
                            if($phone_number != '') $phone_number = '+'.ltrim($phone_number,'+');
                            ?>
                            <div class="form-group mt-0 customlabel">
                                <label for="postalCode" class="d-block"><?php vv_e( 'Phone' ); ?></label>
                                <input type="tel" name="phone" placeholder="+1 555-555-5555" value="<?php echo vv_clean_phone_number( $phone_number ) ?>" class="form-control form-control-sm" id="phone" placeholder="+14155552671" />
                                <input type="hidden" name="phone_number" id="phone_number" value="<?php echo vv_clean_phone_number( $phone_number ) ?>" >
                            </div>
                        </div>
                    </div>
                </div>


            </div>
            <div class="card user_level_field user_level_partner mt-4">
                <div class="card-header"><?php vv_e( 'Host Information' ); ?></div>
                <div class="card-body">
                    <?php
                    $staff_img       = get_user_meta($user_id,'staff_avatar',true);
                    if($staff_img > 0){
                        $staff_img = wp_get_attachment_url($staff_img);
                    }
                    if($staff_img == ''){
                        $staff_img = get_avatar_url($user_id, 64 );
                    }
                    ?>
                    <div class="form-group">
                        <?php 
                        if($staff_img != ''){
                            ?>
                            <div class="user_photo">
                                <img src="<?php echo $staff_img ?>" >
                            </div>
                            <?php
                        }   
                        ?>
                        <label><?php vv_e( 'Upload New Photo' ); ?></label>
                        <input type="file" name="user_photo" class="form-control form-control-sm" >
                    </div>
                </div>
            </div>

        </div>
        <div class="col-md-5 formWrap">
            <div class="card">
                <div class="card-header"><h4><?php vv_e( 'Account Settings' ); ?></h4></div>
                <div class="card-body">
                    <div class="form-group  customlabel" id="user_Pass" <?php echo ($user_level == 'contact') ? 'style="display:none"' : '' ?> >
                        <label for="newpassword"><?php vv_e( 'New Password' ); ?></label>
                        <input type="text" class="form-control form-control-sm"  name="newpassword" id="password" value="" autocomplete="new-password"<?php if(intval(gArrayItem($user_data,'ID')) == 0) echo 'required' ?>  >
                    </div>
                    <div class="form-group">
                        <?php 
                        $position = get_user_meta($user_id,'vv_staff_position',true);
                        ?>
                        <label><?php vv_e( 'Position' ); ?></label>
                        <select name="staff_position" class="form-control form-control-sm">
                            <option value="Key_card_manager" <?php echo($position == 'Key_card_manager') ? 'selected': '' ?>><?php vv_e( 'Key Card Manager' ); ?></option>
                            <option value="money_collector" <?php echo($position == 'money_collector') ? 'selected': '' ?>><?php vv_e( 'Money Collector' ); ?></option>
                            <option value="cleaner" <?php echo($position == 'cleaner') ? 'selected': '' ?>><?php vv_e( 'Cleaner' ); ?></option>
                            <option value="manager" <?php echo($position == 'manager') ? 'selected': '' ?>><?php vv_e( 'Manager' ); ?></option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="mt-4">
        <button class="btn btn-primary" ><?php vv_e( 'Submit' ); ?></button>
    </div>
</form>


<?php ob_start(); ?>
<?php /*
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@23.8.0/build/css/intlTelInput.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/spectrum/1.8.1/spectrum.min.css">
*/ ?>
<style>
    .user_photo img{
        border-radius: 100%;
        margin-bottom: 5px;
        max-width:150px;
    }

</style>
<?php $header_codes .= ob_get_clean(); ?>

<?php ob_start(); ?>
<?php /*
<script src="https://cdnjs.cloudflare.com/ajax/libs/spectrum/1.8.1/spectrum.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/intl-tel-input@23.8.0/build/js/intlTelInput.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/12.3.0/js/utils.js"></script>
*/ ?>
<script src="<?php echo vv_plugins_url()?>/admin/plugins/bootstrap-autocomplete/bootstrap-autocomplete.js"></script>
<script>
    function getStates(country){
        $.get("<?php echo vv_admin_url().'?vv_action=get_states_json' ?>&country="+country, function (data){
            html = '<option value=""><?php echo esc_js( vv__( 'State' ) ); ?></option>';
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
    var is_phone_valid = false;
    jQuery(document).ready(function (){

        <?php /*
        $('.colorpicker').spectrum({
            type: "color",
            showInput: true, // Show input field in the color picker
            preferredFormat: "hex", // Set the color format (e.g., HEX, RGB)
            allowEmpty: true // Allow clearing the input
        });
        */ ?>


        $.get("<?php echo vv_admin_url().'?vv_action=get_countries_json' ?>", function (data){

            //console.log(data);
            html = '<option value=""><?php echo esc_js( vv__( 'Country' ) ); ?></option>';
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

        $('#web_url').on('blur',function (){
            val = $(this).val();
            val = val.replaceAll('https://www.','');
            val = val.replaceAll('https://','');
            val = val.replaceAll('http://www.','');
            val = val.replaceAll('http://','');

            $(this).val(val);
        });

        $('#user_level').change(function (){
            $('.user_level_field').hide();
            $('.user_level_'+$(this).val()).show();
        });


        /*
        var phoneInput = document.querySelector("#phone");

        var iti =  window.intlTelInput(phoneInput, {
            utilsScript: "https://cdn.jsdelivr.net/npm/intl-tel-input@23.8.0/build/js/utils.js",
        });

        var handleChange = function() {
            phone = iti.getNumber(intlTelInputUtils.numberFormat.E164);
            phone = phone.replaceAll(' ','');
            phone = phone.replaceAll('-','');
            $('#phone_number').val(phone);
            if(iti.isValidNumber()){
                is_phone_valid = true;                
                $('#phone').css('outline','unset');
            }else{
                $('#phone').css('outline','1px solid #ff0000');
            }
        };

        // listen to "keyup", but also "change" to update when the user_ selects a country
        phoneInput.addEventListener('change', handleChange);
        phoneInput.addEventListener('keyup', handleChange);

        handleChange();
        */

    });
</script>

<?php $footer_codes .= ob_get_clean(); ?>
