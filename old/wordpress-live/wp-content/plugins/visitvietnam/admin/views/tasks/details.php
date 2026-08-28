<?php 
$back_link  = vv_admin_url('tasks/list');
$back_txt   = 'Back to Tasks';
$task = get_post(GET_Request('id'));
$task_data = [];
$pg_title   = 'Edit Task';
if($task){
    $task_data =$post_array = get_object_vars($task);

}

//echo print_r_pre($task_data);
vv_show_page_title_bar(['pg_title' => $pg_title, 'back_link' => $back_link, 'back_txt' => $back_txt]);
$meta_title = vv_admin_meta_title( $pg_title );

$task_address   = [];
$firstname      = '';
$lastname       = '';
$task_id        = gArrayItem($task_data,'ID');
$email          = '';
$company_name   = '';
$company_id     = 0;

if($task_id > 0){
    //echo get_post_meta($task_id,'task_address',true);



    $staff_id = intval(get_post_meta($task_id,'staff_id',true));

    if($staff_id>0){
        $staff = get_user_by('ID',$staff_id);
        $email = $staff->data->user_email;
        $firstname = get_user_meta($staff_id,'first_name',true);
        $lastname = get_user_meta($staff_id,'last_name',true);

    }
}


if($email == '') $email = POST_Request('email');
if($firstname == '') $firstname = POST_Request('firstname');
if($lastname == '') $lastname = POST_Request('lastname');

$task_levels = vv_get_config('task_levels');
//echo print_r_pre($task_data);
$return = $this->tasks_class->get_Comments(['task_id' => $task_id]);
$comments = gArrayItem($return,'comments');
if(!is_array($comments)) $comments = [];

?>


<form method="post" enctype="multipart/form-data">
    <input type="hidden" name="vv_action" value="save_task">
    <input type="hidden" name="task_id" value="<?php echo $task_id ?>" >
    <input type="hidden" name="staff_id" id="staff_id" value="<?php echo $staff_id ?>" >
    <div class="row">
        <div class="col-md-7 formWrap">
            <div class="card">
                <div class="card-header"><h4><?php vv_e( 'Task Details' ); ?></h4></div>
                <div class="card-body">

                    <div class="form-group"> 
                        <label><?php vv_e( 'Title' ); ?></label>
                        <div class="border p-3 rounded"><?php echo gArrayItem($task_data,'post_title') ?></div>
                    </div>
                    <div class="form-group"> 
                        <label><?php vv_e( 'Details' ); ?></label>
                        <div class="border p-3 rounded"><?php echo nl2br(gArrayItem($task_data,'post_content')) ?></div>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-header"><h4><?php vv_e( 'Comments' ); ?></h4></div>
                <div class="card-body" id="task_comments">
                    <?php
                    $comments = get_comments( array('post_id' => $task_id,'status' => 'approve'));
                    if(count($comments) == 0){
                        echo '<div class="alert alert-warning">' . esc_html( vv__( 'No Comment available' ) ) . '</div>';
                    }else{
                        $args = array(
                            'style'      => 'ul', // Wrap in <ul>
                            'short_ping' => true,
                            'avatar_size'=> 50
                        );
                        wp_list_comments( $args, $comments);
                    }
                    ?>
                    <hr>
                    <h3><?php vv_e( 'Write Comment:' ); ?></h3>
                    <form method="post" enctype="multipart/form-data">
                        <input type="hidden" name="vv_action" value="save_task_comment">
                        <input type="hidden" name="task_id" value="<?php echo $task_id ?>" >
                        <textarea name="comment" class="form-control form-control-sm wysiwyg_editor" ></textarea>
                        <div><button type="submit" class="btn btn-primary"><?php vv_e( 'Submit' ); ?></button></div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-5 formWrap">
            <div class="card task_level_field task_level_partner">
                <div class="card-header"><?php vv_e( 'Assignee' ); ?></div>
                <div class="card-body">
                    <div class="form-group"> 
                        <label><?php vv_e( 'Email' ); ?></label>
                        <div class="border p-3 rounded"><?php echo $email ?></div>
                    </div>
                    <div class="form-group"> 
                        <label><?php vv_e( 'First Name' ); ?></label>
                        <div class="border p-3 rounded"><?php echo $firstname ?></div>
                    </div>
                    <div class="form-group"> 
                        <label><?php vv_e( 'Last name' ); ?></label>
                        <div class="border p-3 rounded"><?php echo $lastname ?></div>
                    </div>
                    <?php
                    $staff_img       = get_user_meta($staff_id,'staff_avatar',true);
                    if($staff_img > 0){
                        $staff_img = wp_get_attachment_url($staff_img);
                    }
                    if($staff_img == ''){
                        $staff_img = get_avatar_url($user_id, 64 );
                    }
                    ?>
                    <div class="form-group p-4">
                        <?php 
                        if($staff_img != ''){
                            ?>
                            <div class="staff_photo" id="staff_photo">
                                <img src="<?php echo $staff_img ?>" id="staff_avatar" >
                            </div>
                            <?php
                        }   
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4">
        <form method="post">
            <input type="hidden" name="vv_action" value="mark_task_completed" >
            <input  type="hidden" name="task_id" value="<?php echo $task_id ?>">
            <button class="btn btn-primary" onclick="return confirm('<?php echo esc_js( vv__( 'Mark this task completed?' ) ); ?>')" ><?php vv_e( 'Mark Task Completed' ); ?></button>
        </form>
    </div>
    </div>
</form>


<?php ob_start(); ?>
<?php /*
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@23.8.0/build/css/intlTelInput.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/spectrum/1.8.1/spectrum.min.css">
*/ ?>
<style>
    .staff_photo img{
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
                if("<?php echo gArrayItem($task_address,'state') ?>" == ''){ 
                }else{
                    if(data[i].name == "<?php echo gArrayItem($task_address,'state') ?>" || data[i].state_code == "<?php echo gArrayItem($task_address,'state') ?>" ) selected = 'selected';
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

        $('#search_staff').autoComplete({
            resolverSettings: {
                url: '<?php echo vv_admin_url () ?>?action=staffs_autocomplete'
            }
        });

        $('#search_staff').on('keyup',function (){
            $('#staff_id').val(0);
            $('#staff_email').val('');
            $('#staff_firstname').val('');
            $('#staff_lastname').val('');
            $('.signup_wrap').show();
        });

        $('#search_staff').on('autocomplete.select', function (evt, item) {
            console.log(item);
            if(typeof item == 'undefined'){
               $('#staff_email').focus();
            }else{
                $('#staff_id').val(item.value);
                $('#staff_email').val(item.text);
                $('#staff_firstname').val(item.firstname);
                $('#staff_lastname').val(item.lastname);
                $('.signup_wrap').hide();
                $('#staff_avatar').attr('src',item.staff_img);
                $('#staff_photo').html('<img src="'+item.staff_img+'"class="staff_photo"  >');
            }
        });    


        $.get("<?php echo vv_admin_url().'?vv_action=get_countries' ?>", function (data){

            //console.log(data);
            html = '<option value=""><?php echo esc_js( vv__( 'Country' ) ); ?></option>';
            for(i = 0; i < data.length; i++){
                selected = '';
                if("<?php echo gArrayItem($task_address,'country') ?>" == ''){ 
                    if(data[i].code == 'US') selected = 'selected';
                }else{
                    if(data[i].code == "<?php echo gArrayItem($task_address,'country') ?>") selected = 'selected';
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

        $('#task_level').change(function (){
            $('.task_level_field').hide();
            $('.task_level_'+$(this).val()).show();
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

        // listen to "keyup", but also "change" to update when the task selects a country
        phoneInput.addEventListener('change', handleChange);
        phoneInput.addEventListener('keyup', handleChange);

        handleChange();
        */

    });
</script>

<?php $footer_codes .= ob_get_clean(); ?>
