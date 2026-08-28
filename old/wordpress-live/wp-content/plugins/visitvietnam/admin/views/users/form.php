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

    $email      = gArrayItem($user_data,'user_email');
    $firstname  = get_user_meta($user_id,'first_name',true);
    $lastname   = get_user_meta($user_id,'last_name',true);




    $user_level = get_user_meta($user_id,'vv_user_level',true);
    if($user_level == '') $user_level = 'customer';
    $user_roles = vv_get_user_roles( $user_id );

}

if($email == '') $email = POST_Request('email');
if($firstname == '') $firstname = POST_Request('firstname');
if($lastname == '') $lastname = POST_Request('lastname');

$user_levels = vvRoles::admin_assignable_roles();
$user_roles  = [ vvRoles::ROLE_GUEST ];

?>


<form method="post" enctype="multipart/form-data">
    <input type="hidden" name="vv_action" value="save_user">
    <input type="hidden" name="user_id" value="<?php echo $user_id ?>" >
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
                    $host_img       = get_user_meta($user_id,'user_avatar',true);
                    if($host_img > 0){
                        $host_img = wp_get_attachment_url($host_img);
                    }
                    if($host_img == ''){
                        $host_img = get_avatar_url($user_id, 64 );
                    }
                    ?>
                    <div class="form-group">
                        <?php 
                        if($host_img != ''){
                            ?>
                            <div class="user_photo">
                                <img src="<?php echo $host_img ?>" >
                            </div>
                            <?php
                        }   
                        ?>
                        <label><?php vv_e( 'Upload New Photo' ); ?></label>
                        <input type="file" name="user_photo" class="form-control form-control-sm" >
                    </div>
                    <?php
                    $host_verification_status = get_user_meta($user_id,'host_verification_status',true);
                    ?>
                    <div class="form-group">
                        <label><?php vv_e( 'Verification Status' ); ?></label>
                        <select name="host_verification_status" class="form-control form-control-sm">
                            <option value="pending"><?php vv_e( 'Pending' ); ?></option>
                            <option value="unverified" <?php echo ($host_verification_status == 'unverified') ? 'selected' : '' ?> ><?php vv_e( 'Unverified' ); ?></option>
                            <option value="verified" <?php echo ($host_verification_status == 'verified') ? 'selected' : '' ?> ><?php vv_e( 'Verified' ); ?></option>
                            <option value="superhost-verified" <?php echo ($host_verification_status == 'superhost-verified') ? 'selected' : '' ?> ><?php vv_e( 'Superhost Verified' ); ?></option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label><?php vv_e( 'About Host (Title)' ); ?></label>
                        <input type="text" name="about_host_title" class="form-control form-control-sm" value="<?php echo get_user_meta($user_id,'about_host_title',true) ?>" >
                    </div>
                    <div class="form-group">
                        <label><?php vv_e( 'About Host (Text)' ); ?></label>
                        <textarea name="about_host" class="form-control form-control-sm" ><?php echo get_user_meta($user_id,'about_host',true) ?></textarea>
                    </div>
                </div>
            </div>

        </div>
        <div class="col-md-5 formWrap">
            <div class="card">
                <div class="card-header"><h4><?php vv_e( 'Account Settings' ); ?></h4></div>
                <div class="card-body">
                    <?php if($logged_user_role == 'administrator'){ ?>
                        <div class="form-group customlabel">
                            <label><?php vv_e( 'Platform Roles' ); ?> <small class="text-muted"><?php vv_e( '(spec: one account, multiple roles)' ); ?></small></label>
                            <?php foreach ( $user_levels as $level ) {
                                if ( $level === vvRoles::ROLE_GUEST ) {
                                    continue;
                                }
                                $checked = in_array( vvRoles::normalize_role( $level ), $user_roles, true ) ? 'checked' : '';
                                ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="user_roles[]" id="role_<?php echo esc_attr( $level ) ?>" value="<?php echo esc_attr( $level ) ?>" <?php echo $checked ?>>
                                    <label class="form-check-label" for="role_<?php echo esc_attr( $level ) ?>"><?php echo esc_html( vv_get_status_name_text( $level ) ) ?></label>
                                </div>
                            <?php } ?>
                            <input type="hidden" name="user_level" id="user_level" value="<?php echo esc_attr( vvRoles::legacy_level_from_roles( $user_roles ) ) ?>">
                        </div>
                    <?php } ?>
                    <div class="form-group  customlabel" id="userPass" <?php echo ($user_level == 'contact') ? 'style="display:none"' : '' ?> >
                        <label for="newpassword"><?php vv_e( 'New Password' ); ?></label>
                        <input type="text" class="form-control form-control-sm"  name="newpassword" id="password" value="" autocomplete="new-password"<?php if(intval(gArrayItem($user_data,'ID')) == 0) echo 'required' ?>  >
                    </div>

                    <?php if ( class_exists( 'vvI18n' ) && vvI18n::can_edit_user_locale( $user_id ) ) { ?>
                        <hr>
                        <?php include dirname( __FILE__ ) . '/_language_setting.php'; ?>
                    <?php } ?>

                    <?php include dirname( __FILE__ ) . '/_currency_setting.php'; ?>

                </div>
            </div>
            <?php 
            if($user_id > 0){
                ?>
                <div class="card user_level_field user_level_ambassador">
                    <div class="card-header"><?php vv_e( 'Promo Codes' ); ?></div>
                    <div class="card-body">
                        <?php 
                        $filter = [];
                        $filter['user_id'] = $user_id;
                        $filter['return_total'] = 0;
                        $promocodes = $this->promocodes_class->get_promocodes($filter);
                        //echo print_r_pre($promocodes);
                        ?>
                        <table class="table table-stripped border mb-2">
                            <tr>
                                <th><?php vv_e( 'Code' ); ?></th>
                                <th><?php vv_e( 'Discount (%)' ); ?></th>
                            </tr>
                            <?php 
                            foreach($promocodes as $promocode){
                                ?>
                                <tr>
                                    <td><?php echo $promocode['code'] ?></td>
                                    <td><?php echo $promocode['discount'] ?></td>
                                </tr>
                                <?php
                            }
                            ?>
                        </table>
                        <div><a href="<?php echo vv_admin_url('settings/promocodes').'?user='.$user_id ?>" ><?php vv_e( 'See All' ); ?></a></div>
                    </div>
                </div>
                <?php 
            }
            ?>
        </div>
    </div>

    <div class="mt-4">
        <button class="btn btn-primary" ><?php vv_e( 'Submit' ); ?></button>
    </div>
</form>

<?php if ( $logged_user_role === 'partner' && intval( $user_id ) === get_current_user_id() ) { ?>
    <form method="post" action="<?php echo esc_url( vv_admin_url() ); ?>" class="mt-3">
        <input type="hidden" name="vv_action" value="host_welcome_show_again">
        <button type="submit" class="btn btn-outline-secondary btn-sm"><?php vv_e( 'Show welcome tour' ); ?></button>
        <span class="text-muted small ml-2"><?php vv_e( 'Reopen the host portal introduction slider.' ); ?></span>
    </form>
<?php } ?>


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
        $.get("<?php echo vv_admin_url().'?vv_action=get_states' ?>&country="+country, function (data){
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


        $.get("<?php echo vv_admin_url().'?vv_action=get_countries' ?>", function (data){

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

        function vvToggleRoleFields(){
            var hasAmbassador = $('input[name="user_roles[]"][value="ambassador"]').is(':checked');
            var hasHost = $('input[name="user_roles[]"][value="host"]').is(':checked');
            $('.user_level_field').hide();
            if(hasAmbassador) $('.user_level_ambassador').show();
            if(hasHost) $('.user_level_partner').show();
        }

        $('input[name="user_roles[]"]').on('change', vvToggleRoleFields);
        vvToggleRoleFields();


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

        // listen to "keyup", but also "change" to update when the user selects a country
        phoneInput.addEventListener('change', handleChange);
        phoneInput.addEventListener('keyup', handleChange);

        handleChange();
        */

    });
</script>

<?php $footer_codes .= ob_get_clean(); ?>
