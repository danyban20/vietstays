<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="Wiise.no">
    <title>Visit Vietnam Admin | Login</title>
    <?php include('inc/head_codes.php'); ?>
    <style>
        .vv-admin-login-wrap .login-content { max-width: 460px; margin: 0 auto; }
        .vv-admin-login-wrap .login-form { background: #fff; border-radius: 12px; padding: 28px; box-shadow: 0 8px 30px rgba(0,0,0,.08); }
    </style>
</head>

<body class="animsition">
    <div class="page-wrapper vv-admin-login-wrap">
        <div class="notification-bar"></div>
        <div class="page-content--bge5">
            <div class="container">
                <div class="login-wrap">
                    <div class="login-content">
                        <div class="login-logo">
                            <a href="#">Visit Vietnam</a>
                        </div>
                        <div class="login-form">
                            <?php showSuccessMsg( false ); ?>
                            <?php showErrorMsg( false ); ?>
                            <?php
                            $portal = sanitize_key( GET_Request( 'portal' ) );
                            $vv_login_portal   = $portal === 'guest' ? 'guest' : 'host';
                            $vv_login_redirect = vv_admin_url( 'dashboard' );
                            $vv_login_compact  = true;
                            include dirname( __DIR__ ) . '/inc/partials/vv-login-portal.php';
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include('inc/footer_codes.php'); ?>
</body>

</html>
