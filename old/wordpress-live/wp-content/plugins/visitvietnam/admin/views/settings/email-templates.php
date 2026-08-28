<?php 
global $wpdb;

$pg_title   = 'Email Templates';
$meta_title = vv_admin_meta_title( $pg_title );

$filter = [];

$email_template_codes = vv_get_config('email_template_codes');
$email_locales = class_exists( 'vvI18n' ) ? vvI18n::supported_locales() : [ 'en' => [ 'short' => 'EN' ] ];

?>

<h1><?php vv_e( 'Email Templates' ); ?></h1>

<?php //showAlertMessages() ?> 

<div class="card">
    <div class="card-body">

            <div class="d-flex">
                <ul id="tabsJustified" class="nav nav-pills flex-column">

                    <?php 
                    foreach($email_template_codes as $email_template_code){
                        $email_template = $this->email_templates_class->get_email_template_by_code($email_template_code, 'en');
                        $name = gArrayItem($email_template,'name');
                        if($name == '') $name = ucwords(str_replace("_"," ",$email_template_code));
                        ?>
                        <li class="nav-item"><a href="#email_template_<?php echo $email_template_code ?>" data-target="#email_template_<?php echo $email_template_code ?>" data-toggle="tab" class="nav-link small"><?php echo esc_html( $name ); ?></a></li>
                        <?php 
                    }
                    ?>
                </ul>
                <div class="tab-content border rounded p-3 w-100">
                    <?php 
                    foreach($email_template_codes as $email_template_code){
                        ?>
                        <div id="email_template_<?php echo $email_template_code ?>" class="tab-pane fade">
                            <ul class="nav nav-tabs mb-3 vv-email-locale-tabs">
                                <?php foreach ( $email_locales as $locale_slug => $locale_info ) { ?>
                                    <li class="nav-item">
                                        <a class="nav-link<?php echo $locale_slug === 'en' ? ' active' : ''; ?>" data-toggle="tab" href="#email_template_<?php echo esc_attr( $email_template_code ); ?>_<?php echo esc_attr( $locale_slug ); ?>">
                                            <?php echo esc_html( gArrayItem( $locale_info, 'short', strtoupper( $locale_slug ) ) ); ?>
                                        </a>
                                    </li>
                                <?php } ?>
                            </ul>
                            <div class="tab-content">
                                <?php foreach ( $email_locales as $locale_slug => $locale_info ) {
                                    $email_template = $this->email_templates_class->get_email_template_row( $email_template_code, $locale_slug, false );
                                    $name = gArrayItem( $email_template, 'name' );
                                    if ( $name === '' ) {
                                        $name = ucwords( str_replace( '_', ' ', $email_template_code ) );
                                    }
                                    ?>
                                    <div id="email_template_<?php echo esc_attr( $email_template_code ); ?>_<?php echo esc_attr( $locale_slug ); ?>" class="tab-pane fade<?php echo $locale_slug === 'en' ? ' show active' : ''; ?>">
                                        <form method="post" action="<?php echo vv_admin_url('settings/email-templates/') ?>#email_template_<?php echo $email_template_code ?>">
                                            <input type="hidden" name="action" value="email_template-save" >
                                            <input type="hidden" name="code" value="<?php echo esc_attr( $email_template_code ); ?>" >
                                            <input type="hidden" name="locale" value="<?php echo esc_attr( $locale_slug ); ?>" >
                                            <div class="form-group">
                                                <label><?php vv_e( 'Name' ); ?></label>
                                                <input type="text" name="name" class="form-control form-control-sm" value="<?php echo esc_attr( $name ); ?>" >
                                            </div>
                                            <div class="form-group">
                                                <label><?php vv_e( 'Subject' ); ?></label>
                                                <input type="text" name="subject" class="form-control form-control-sm" value="<?php echo esc_attr( stripslashes( gArrayItem( $email_template, 'subject' ) ) ); ?>" >
                                            </div>
                                            <div class="form-group">
                                                <label><?php vv_e( 'Message' ); ?></label>
                                                <textarea name="body" class="wysiwyg_editor"><?php echo esc_textarea( stripslashes( $this->email_templates_class->get_template_body( $email_template ) ) ); ?></textarea>
                                            </div>
                                            <div class="mt-4">
                                                <button type="submit" class="btn btn-primary"><?php vv_e( 'Save' ); ?></button>
                                            </div>
                                        </form>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                        <?php 
                    }
                    ?>
                </div>
            </div>
    </div>
</div>


<?php
ob_start();
?>
    <script type="text/javascript">
        
        $(document).ready(function (){
            var fullHash = window.location.hash;
            if(fullHash != ''){
                $('a[href="'+ fullHash+'"]').trigger('click');
            }else{
                $('#tabsJustified').closest('.card-body').find('> .tab-content > .tab-pane').first().addClass('show active');
                $('#tabsJustified').find('li:first-child').find('a').trigger('click');
            }

            $('#tabsJustified').closest('.card-body').find('form').on('submit', function () {
                $(this).find('.wysiwyg_editor').each(function () {
                    var $editor = $(this);
                    if ($editor.next('.note-editor').length) {
                        $editor.val($editor.summernote('code'));
                    }
                });
            });
        });
    </script>

<?php
$footer_codes .= ob_get_clean();
