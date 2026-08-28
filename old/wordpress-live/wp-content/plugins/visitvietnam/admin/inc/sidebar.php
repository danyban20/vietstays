    <?php
    require_once __DIR__ . '/sidebar-menus.php';
    require_once __DIR__ . '/sidebar-render.php';

    $staff_position = '';
    if ( $logged_user_role === 'staff' ) {
        $staff_position = get_user_meta( get_current_user_id(), 'vv_staff_position', true );
    }

    $sidebar_menus = vv_get_backend_sidebar_menus( $logged_user_role, $staff_position );
    ?>

        <!-- MENU SIDEBAR-->
        <aside class="menu-sidebar vv-wp-sidebar d-none d-lg-block">
            <div class="logo">
                <a href="<?php bloginfo( 'url' ); ?>">
                   <img src="<?php echo vv_plugins_url() . 'admin/images/visitvietnam-logo.png'; ?>" alt="Visit Vietnam">
                </a>
            </div>
            <div class="menu-sidebar__content js-scrollbar1">
                <nav class="navbar-sidebar vv-admin-menu">
                    <?php vv_render_sidebar_nav( $sidebar_menus ); ?>
                </nav>
            </div>
        </aside>
        <!-- END MENU SIDEBAR-->

        <style>
        .vv-sidebar-section {
            margin-top: 1rem;
            padding: 0.35rem 1.5rem 0.15rem;
            list-style: none;
        }
        .vv-sidebar-section__label {
            display: block;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.45);
        }
        .vv-admin-menu .vv-menu-soon {
            opacity: 0.72;
        }
        </style>

        <?php
        $active_path = '';
        if ( isset( $admin_file ) && $admin_file !== '' ) {
            $active_path = vv_sidebar_active_menu_path( $admin_file );
        }

        if ( $active_path === '' ) {
            $url    = $_SERVER['REQUEST_URI'];
            $parsed = parse_url( $url );
            $path   = $parsed['path'];
            $path   = str_replace( '/vv-admin/', '', $path );
            $active_path = trim( $path, '/' );
        }

        $sidebar_active_url = vv_admin_url( $active_path );

        ob_start();
        ?>

        <script type="text/javascript">
            jQuery(document).ready(function () {
                var url = '<?php echo esc_js( $sidebar_active_url ); ?>';
                $('.vv-admin-menu').find('a[href="' + url + '"]').addClass('active');
                $('.vv-admin-menu').find('a.active').each(function () {
                    var $parent = $(this).closest('.has-sub');
                    $parent.addClass('open');
                    $parent.children('a.js-arrow').addClass('open');
                });
            });
        </script>
        <?php
        $footer_codes .= ob_get_clean();
        ?>
