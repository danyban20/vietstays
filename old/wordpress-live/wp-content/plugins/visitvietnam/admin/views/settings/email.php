<?php 
global $wpdb;

$pg_title   = 'Email Settings';
$meta_title = vv_admin_meta_title( $pg_title );

$settings = json_decode(get_option('vv-sending_receiving_settings'),true);
if(!is_array($settings)) $settings = [];

?>
<h1><?php vv_e( 'Email Settings' ); ?></h1>
<div class="card">
    <div class="card-body p-0">
        <form method="post" id="formEmailSettings" >
            <input type="hidden" name="vv_action" value="save_email_settings" >
            <div class="row">
                <div class="col-md-6">
                    <h5 class="card-header"><?php vv_e( 'Incoming - Imap Settings' ); ?></h5>
                    <div class="p-4">
                        <div class="form-group">
                            <label><?php vv_e( 'Host' ); ?></label>
                            <input type="text" name="host" class="form-control form-control-sm" value="<?php echo esc_attr( gArrayItem($settings,'host') ); ?>" >
                        </div>
                        <div class="form-group">
                            <label><?php vv_e( 'Port' ); ?></label>
                            <input type="text" name="port" class="form-control form-control-sm" value="<?php echo esc_attr( gArrayItem($settings,'port') ); ?>" >
                        </div>
                        <div class="form-group">
                            <label><?php vv_e( 'Username' ); ?></label>
                            <input type="text" name="username" class="form-control form-control-sm" value="<?php echo esc_attr( gArrayItem($settings,'username') ); ?>" >
                        </div>
                        <div class="form-group">
                            <label><?php vv_e( 'Password' ); ?></label>
                            <input type="password" name="password" class="form-control form-control-sm" value="<?php echo esc_attr( gArrayItem($settings,'password') ); ?>" >
                        </div>
                        <div class="form-group">
                            <label><?php vv_e( 'Encryption' ); ?></label>
                            <?php $encryption = gArrayItem($settings,'encryption');
                            ?>
                            <select name="encryption" class="form-control form-control-sm" >
                                <option value="ssl">SSL</option>
                                <option value="tls" <?php echo ($encryption == 'tls') ? 'selected' : '' ?> >TLS</option>
                            </select>
                        </div>
                        <p><button type="button" class="btn btn-secondary btnTestImap" ><?php vv_e( 'Test Connection' ); ?></button></p>
                    </div>
                </div><!-- .col -->
                <div class="col-md-6">
                    <h5 class="card-header"><?php vv_e( 'Outgoing - SMTP Settings' ); ?></h5>
                    <div class="p-4">
                        <div class="form-group">
                            <label><?php vv_e( 'Sender Email' ); ?></label>
                            <input type="text" name="sender_email" class="form-control form-control-sm" value="<?php echo esc_attr( gArrayItem($settings,'sender_email') ); ?>" >
                        </div>
                        <div class="form-group">
                            <label><?php vv_e( 'Sender Name' ); ?></label>
                            <input type="text" name="sender_name" class="form-control form-control-sm" value="<?php echo esc_attr( gArrayItem($settings,'sender_name') ); ?>" >
                        </div>
                        <div class="form-group">
                            <label><?php vv_e( 'Host' ); ?></label>
                            <input type="text" name="host_out" class="form-control form-control-sm" value="<?php echo esc_attr( gArrayItem($settings,'host_out') ); ?>" >
                        </div>
                        <div class="form-group">
                            <label><?php vv_e( 'Port' ); ?></label>
                            <input type="text" name="port_out" class="form-control form-control-sm" value="<?php echo esc_attr( gArrayItem($settings,'port_out') ); ?>" >
                        </div>
                        <div class="form-group">
                            <label><?php vv_e( 'Username' ); ?></label>
                            <input type="text" name="username_out" class="form-control form-control-sm" value="<?php echo esc_attr( gArrayItem($settings,'username_out') ); ?>" >
                        </div>
                        <div class="form-group">
                            <label><?php vv_e( 'Password' ); ?></label>
                            <input type="password" name="password_out" class="form-control form-control-sm" value="<?php echo esc_attr( gArrayItem($settings,'password_out') ); ?>" >
                        </div>
                        <div class="form-group">
                            <label><?php vv_e( 'Encryption' ); ?></label>
                            <?php $encryption_out = gArrayItem($settings,'encryption_out');
                            ?>
                            <select name="encryption_out" class="form-control form-control-sm" >
                                <option value="ssl">SSL</option>
                                <option value="tls" <?php echo ($encryption_out == 'tls') ? 'selected' : '' ?> >TLS</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label><?php vv_e( 'Disable SSL Verification' ); ?></label>
                            <input type="checkbox" name="disable_ssl_verification_out" value="1" <?php echo (gArrayItem($settings,'disable_ssl_verification_out') == 1) ? 'checked' : '' ?> >
                        </div>
                        <hr>
                        <h4><?php vv_e( 'Test SMTP Settings' ); ?></h4>
                        <div class="form-group">
                            <label><?php vv_e( 'Send Test Email To' ); ?></label>
                            <input type="text" class="form-control form-control-sm" name="send_test_email_to" >
                        </div>
                        <p><button type="button" class="btn btn-secondary btnSendTestEmail" ><?php vv_e( 'Send Test Email' ); ?></button></p>
                    </div>
                </div><!-- .col -->
            </div>
            <div class="p-4">
                <button type="submit" class="btn btn-primary"><?php vv_e( 'Save Changes' ); ?></button>
            </div>
        </form>
    </div>
</div>


<?php
$send_test_confirm = esc_js( vv__( 'Send Test Email?' ) );
$sending_label     = esc_js( vv__( 'Sending...' ) );
$send_test_label   = esc_js( vv__( 'Send Test Email' ) );
$testing_label     = esc_js( vv__( 'Testing...' ) );
$test_conn_label   = esc_js( vv__( 'Test Connection' ) );
ob_start();
?>
    <!-- full calendar requires moment along jquery which is included above -->

    <script type="text/javascript">
        var isSendingTest = false;
        var isTestingImap = false;
        var isCronRunning = false;
        $(document).ready(function (){

            $('.btnSendTestEmail').click(function (e){
                let btnSendTestEmail = this;

                if(isSendingTest == false){

                    if(confirm('<?php echo $send_test_confirm; ?>')){
                        $(btnSendTestEmail).html('<?php echo $sending_label; ?>');
                        isSendingTest = true;

                        let send_to = $('input[name="send_test_email_to"]').val();
                        let sender_email = $('input[name="sender_email"]').val();
                        let sender_name = $('input[name="sender_name"]').val();
                        let host = $('input[name="host_out"]').val();
                        let port = $('input[name="port_out"]').val();
                        let username = $('input[name="username_out"]').val();
                        let password = $('input[name="password_out"]').val();
                        let encryption = $('select[name="encryption_out"]').val();

                        if($('input[name="disable_ssl_verification_out"]').is(":checked")) disable_ssl = 1;
                        else disable_ssl = 0;


                        formData = { 'vv_action' : 'test_smtp', 'send_to' : send_to,  'sender_email' : sender_email, 'sender_name' : sender_name, 'host' : host, 'port' : port, 'username' : username, 'password' : password, 'encryption' : encryption, 'disable_ssl' : disable_ssl }

                        $.post('<?php echo vv_admin_url() ?>', formData , function (data){
                            alert(data.message);
                            isSendingTest = false;
                            $(btnSendTestEmail).html('<?php echo $send_test_label; ?>');
                        });
                    }
                }else{
                }
            });

            $('.btnTestImap').click(function (){
                let btnObj;

                if(isTestingImap == false){
                    $(btnObj).html('<?php echo $testing_label; ?>');
                    isTestingImap = true;
                    
                    let host = $('input[name="host"]').val();
                    let port = $('input[name="port"]').val();
                    let username = $('input[name="username"]').val();
                    let password = $('input[name="password"]').val();
                    let encryption = $('select[name="encryption"]').val();

                    formData = { 'vv_action' : 'test_imap', 'host' : host, 'port' : port, 'username' : username, 'password' : password, 'encryption' : encryption }

                    $.post('<?php echo vv_admin_url() ?>', formData , function (data){
                        alert(data);
                        isTestingImap = false;
                        $(btnObj).html('<?php echo $test_conn_label; ?>');
                    });
                }
                
            });
        });
    </script>
<?php $footer_codes .= ob_get_clean(); ?>
