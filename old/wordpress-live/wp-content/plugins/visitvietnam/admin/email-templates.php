<?php 
global $wpdb;

$filter = [];

$email_template_codes = vv_get_config('email_template_codes');

//echo print_r_pre($owner_ids);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Required meta tags-->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="Wiise.no">
    <meta name="keywords" content="">

    <!-- Title Page-->
    <title>Visit Vietnam Admin | Email Templates</title>

    <?php include('inc/head_codes.php'); ?>

    <style type="text/css">
        
        #tabsJustified .nav-link{
            border-top-right-radius: 0 !important;
            border-bottom-right-radius: 0 !important;
        }

    </style>
</head>

<body class="">
    <div class="page-wrapper">

        <?php include('inc/header-mobile.php'); ?>
        <?php include('inc/sidebar.php'); ?>


        <!-- PAGE CONTAINER-->
        <div class="page-container">
            <?php include('inc/header.php'); ?>
            <!-- MAIN CONTENT-->
            <div class="main-content">
                <div class="section__content section__content--p30">
                    <div class="container-fluid">

                        <h1 class="mb-4">Email Templates</h1>

                        <?php showAlertMessages() ?>

                        <div class="card">
                            <div class="card-body">

                                    <div class="d-flex">
                                        <ul id="tabsJustified" class="nav nav-pills flex-column">

                                            <?php 
                                            foreach($email_template_codes as $email_template_code){
                                                $email_template = $this->email_templates_class->get_email_template_by_code($email_template_code);
                                                $name = gArrayItem($email_template,'name');
                                                if($name == '') $name = ucwords(str_replace("_"," ",$email_template_code));
                                                ?>
                                                <li class="nav-item"><a href="#email_template_<?php echo $email_template_code ?>" data-target="#email_template_<?php echo $email_template_code ?>" data-toggle="tab" class="nav-link small"><?php echo $name ?></a></li>
                                                <?php 
                                            }
                                            ?>
                                        </ul>
                                        <div class="tab-content border rounded p-3 w-100">
                                            <?php 
                                            foreach($email_template_codes as $email_template_code){
                                                $email_template = $this->email_templates_class->get_email_template_by_code($email_template_code);
                                                $name = gArrayItem($email_template,'name');
                                                if($name == '') $name = ucwords(str_replace("_"," ",$email_template_code));
                                                ?>
                                                <div id="email_template_<?php echo $email_template_code ?>" class="tab-pane fade">
                                                    <form method="post" action="<?php echo vv_admin_url() ?>email-templates#email_template_<?php echo $email_template_code ?>">
                                                        <input type="hidden" name="action" value="email_template-save" >
                                                        <input type="hidden" name="code" value="<?php echo $email_template_code ?>" >
                                                        <div class="form-group">
                                                            <label>Name</label>
                                                            <input type="text" name="name" class="form-control form-control-sm" value="<?php echo $name ?>" >
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Subject</label>
                                                            <input type="text" name="subject" class="form-control form-control-sm" value="<?php echo gArrayItem($email_template,'subject') ?>" >
                                                        </div>
                                                        <div class="form-group">
                                                            <label>Message</label>
                                                            <?php wp_editor( stripslashes(gArrayItem($email_template,'body')).' ' , 'body-'.$email_template_code,  array('media_buttons' => false, 'textarea_name' => 'body', 'editor_height' => 500, 'wpautop' => false) ); ?>
                                                        </div>
                                                        <div class="mt-4">
                                                            <button type="submit" class="btn btn-primary">Save</button>
                                                        </div>
                                                    </form>
                                                </div>
                                                <?php 
                                            }
                                            ?>
                                        </div>
                                    </div>
                            </div>
                        </div>

                        <?php include('inc/footer.php'); ?>
                    </div>
                </div>
            </div>
            <!-- END MAIN CONTENT-->
            <!-- END PAGE CONTAINER-->
        </div>

    </div>

    
    <?php 
    include('inc/footer_codes.php');
    ?>

    <!-- full calendar requires moment along jquery which is included above -->

    <script type="text/javascript">
        
        $(document).ready(function (){
            $('#tabsJustified').find('li:first-child').find('a').trigger('click');
        });
    </script>

    <?php wp_footer() ?>

</body>

</html>
<!-- end document-->