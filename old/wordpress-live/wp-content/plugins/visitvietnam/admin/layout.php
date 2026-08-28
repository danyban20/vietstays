<?php 
global $wcrm_admins;
$meta_title = vv_get_app_admin_title();
$header_codes = '';
$footer_codes = '';


$logged_user = wp_get_current_user();
$logged_user_firstname = get_user_meta($logged_user->ID,'first_name',true);


ob_start();
include("views/".$admin_file.'.php');
$page_content = ob_get_clean();


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
    <title><?php echo $meta_title ?></title>

    <?php include('inc/head_codes.php'); ?>

    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <script type="text/javascript">
        var vvSiteCurrency = '<?php echo vv_site_currency() ?>';
    </script>
    <?php echo $header_codes ?>
</head>

<body class="">
    <div class="page-wrapper">

        <div class="notification-bar"></div>
        <?php if ( function_exists( 'vv_is_impersonating_host' ) && vv_is_impersonating_host() ) { ?>
            <div class="vv-impersonation-bar alert alert-warning mb-0 rounded-0 text-center py-2">
                <?php vv_e( 'Viewing portal as' ); ?>
                <strong><?php echo esc_html( $logged_user->display_name ); ?></strong>.
                <a href="<?php echo esc_url( vv_stop_impersonating_host_url() ); ?>" class="alert-link font-weight-bold"><?php vv_e( 'Return to admin account' ); ?></a>
            </div>
        <?php } ?>
        <?php include('inc/header-mobile.php'); ?>
        <?php include('inc/sidebar.php'); ?>


        <!-- PAGE CONTAINER-->
        <div class="page-container">
            <?php include('inc/header.php'); ?>
            <!-- MAIN CONTENT-->
            <div class="main-content">
                <div class="section__content pl-2 pr-2">
                    <div class="container-fluid">
                        <?php echo $page_content ?>
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

    if ( isset( $logged_user_role ) && $logged_user_role === 'partner' ) {
        include dirname( __FILE__ ) . '/inc/host-welcome-modal.php';
    }

    echo $footer_codes;

    ?>


</body>

</html>
<!-- end document-->