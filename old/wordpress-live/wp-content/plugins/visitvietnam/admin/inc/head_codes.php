    <!-- Fontfaces CSS-->
    <link href="css/font-face.css" rel="stylesheet" media="all">
    <?php /*
    <link href="<?php echo vv_plugins_url() ?>admin/vendor/font-awesome-4.7/css/font-awesome.min.css" rel="stylesheet" media="all">
    <link href="<?php echo vv_plugins_url() ?>admin/vendor/font-awesome-5/css/fontawesome-all.min.css" rel="stylesheet" media="all">
    */ ?>
    <link href="<?php echo vv_plugins_url() ?>admin/vendor/mdi-font/css/material-design-iconic-font.min.css" rel="stylesheet" media="all">

    <!-- Bootstrap CSS-->
    <link href="<?php echo vv_plugins_url() ?>admin/vendor/bootstrap-4.1/bootstrap.min.css" rel="stylesheet" media="all">

    <!-- Vendor CSS-->
    <link href="<?php echo vv_plugins_url() ?>admin/vendor/animsition/animsition.min.css" rel="stylesheet" media="all">
    <link href="<?php echo vv_plugins_url() ?>admin/vendor/bootstrap-progressbar/bootstrap-progressbar-3.3.4.min.css" rel="stylesheet" media="all">
    <link href="<?php echo vv_plugins_url() ?>admin/vendor/wow/animate.css" rel="stylesheet" media="all">
    <link href="<?php echo vv_plugins_url() ?>admin/vendor/css-hamburgers/hamburgers.min.css" rel="stylesheet" media="all">
    <link href="<?php echo vv_plugins_url() ?>admin/vendor/slick/slick.css" rel="stylesheet" media="all">
    <link href="<?php echo vv_plugins_url() ?>admin/vendor/select2/select2.min.css" rel="stylesheet" media="all">
    <link href="<?php echo vv_plugins_url() ?>admin/vendor/perfect-scrollbar/perfect-scrollbar.css" rel="stylesheet" media="all">
    <link href="<?php echo vv_plugins_url() ?>admin/plugins/summernote/summernote-bs4.min.css" rel="stylesheet" media="all">

    <!-- Main CSS-->
    <link href="<?php echo vv_plugins_url() ?>admin/css/theme.css?t=<?php echo time() ?>" rel="stylesheet" media="all">

    <style type="text/css">
        body{
            color: #000;
            font-size:14px;
        }
        .form-control {
            -webkit-border-radius: 4px;
            -moz-border-radius: 4px;
            border-radius: 4px;
            border-color: #FD780E;
        }
        .form-control:disabled{
            border-color:#999;
        }
        .menu-sidebar, .menu-sidebar .logo{
            background: #004041;
            color:#fff;
        }
        .menu-sidebar .logo{
            border-right:0px none;
        }
        .menu-sidebar a, .navbar-sidebar .navbar__list li a{
            color:#fff;
        }

        .navbar-sidebar .navbar__list li a{
            padding-top:5px;
            padding-bottom:5px;
        }
        .menu-sidebar .navbar__list .navbar__sub-list li a{
            padding-top:5px;
            padding-bottom:5px;
        }

        /* WordPress-style admin sidebar: hover flyout + click to expand inline */
        .vv-wp-sidebar {
            z-index: 1001;
        }
        .vv-wp-sidebar .navbar__list > li.has-sub {
            position: relative;
        }
        .vv-wp-sidebar .navbar__list > li.has-sub > a.js-arrow {
            position: relative;
            padding-right: 24px;
            cursor: pointer;
        }
        .vv-wp-sidebar .navbar__list > li.has-sub > a.js-arrow::after {
            content: '\203A';
            position: absolute;
            right: 0;
            top: 50%;
            transform: translateY(-50%);
            font-size: 18px;
            line-height: 1;
            opacity: 0.75;
            transition: transform 0.15s ease;
        }
        .vv-wp-sidebar .navbar__list > li.has-sub.open > a.js-arrow::after {
            transform: translateY(-50%) rotate(90deg);
        }
        .vv-wp-sidebar .navbar__list > li.has-sub.open > a.js-arrow,
        .vv-wp-sidebar .navbar__list > li.has-sub:not(.open):hover > a.js-arrow {
            background: rgba(255, 255, 255, 0.08);
        }
        .vv-wp-sidebar .navbar__list .navbar__sub-list {
            display: none;
            margin: 0;
            padding: 0;
            list-style: none;
        }
        .vv-wp-sidebar .navbar__list > li.has-sub.open > .navbar__sub-list {
            display: block;
            position: static;
            padding: 4px 0 8px 34px;
        }
        .vv-wp-sidebar .navbar__list > li.has-sub.open > .navbar__sub-list li a {
            padding: 7px 0;
            font-size: 14px;
            opacity: 0.92;
        }
        .vv-wp-sidebar .navbar__sub-list.vv-flyout-visible {
            display: block;
            min-width: 200px;
            padding: 6px 0;
            background: #003032;
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-left: 3px solid #FD780E;
            box-shadow: 4px 4px 12px rgba(0, 0, 0, 0.25);
            z-index: 10050;
        }
        .vv-wp-sidebar .navbar__sub-list.vv-flyout-visible li a {
            padding: 9px 18px;
            font-size: 14px;
            white-space: nowrap;
        }
        .vv-wp-sidebar .navbar__sub-list.vv-flyout-visible li a:hover,
        .vv-wp-sidebar .navbar__list > li.has-sub.open > .navbar__sub-list li a:hover {
            background: rgba(255, 255, 255, 0.08);
            color: #fff;
        }
        .vv-wp-sidebar .navbar__sub-list li a.active {
            color: #FD780E;
            font-weight: 600;
        }

        .header-desktop{
            background:#F0E8D5;
            color:#004041;
        }
        .header-desktop a, .account-item > .content > a, .noti__item i{
            color:#004041;
        }


        h1{
            font-size:26px;
        }
        .table-earning thead th{
            padding-left:10px;
            padding-right:10px;
            font-size: 14px;
        }
        .table-earning tbody td{
            padding:10px;
            color:#000;
            font-size:12px;
        }


    .modal .bookinginfo{
        width:100%;
        max-height:calc(100vh - 200px);
        overflow-y:auto;
        overflow-x: hidden;
        font-size:14px;
        color:#000;
    }
    .modal .bookinginfo .row > div{
        font-size:14px;
        color:#000;
    }
    .modal .bookinginfo .form-control[disabled]{
        background-color: #fff;
        border-top:0px none !important;
        border-left:0px none !important;
        border-right:0px none !important;
    }
    .modal .bookinginfo .form-control{
        background-color: #feffe3;
        color:#000;
    }
    .modal .bookinginfo .form-control[disabled]:focus{
        outline:none;
    }

    .notification-bar{
        position: fixed;
        top:85px;
        z-index: 9999;
        width:100%;
        padding-left:20px;
        padding-right:20px;
        left:0;
    }
    .notification-bar .alert{
        margin-bottom:5px;
        box-shadow: 3px 3px 5px #adadad;
        position: relative;
        width:calc(100% - 305px);
        margin-left:300px;
        display: inline-block;
        text-align: left;
        padding-right:40px;
    }

    .notification-bar .alert .btnCloseNotification{
        cursor: pointer;
        position:absolute;
        top:5px;
        right:10px;
        font-size:20px;
    }


    .switch.switch-default .switch-label{
        background-color: #a0a0a0;
    }

    .modal .bookinginfo .booking_totals > div{
        max-width:430px;
        width:100%;
    }
    .modal .bookinginfo .booking_totals table{
        width:100%;
        font-size:13px;
    }
    .modal .bookinginfo .booking_totals table td{
    }
    .modal .bookinginfo .booking_totals table td:nth-child(2){
        width:100px;
    }
    .modal .bookinginfo .booking_totals table td:nth-child(3){
        width:120px;
    }
    .modal .bookinginfo .booking_totals:after{
        clear:both;
    }

    #vv-autocomplete_result{
        position:absolute;
        width:100%;
        top:-1px;
        left:0;
        z-index: 9;
        display:none;
        border:1px solid #ccc;
        border-bottom-right-radius: 3px;
        border-bottom-left-radius: 3px;
        padding:10px;
        background: #fff4d4;
        color:#fff;
        font-size:14px;
    }

    .form-wrap{
        max-width:500px;
    }
    .form-wrap2{
        max-width: 800px;
    }

    .form-group label{
        margin-bottom: 2px;
    }

    

    @media screen and (max-width: 1200px) {
        .notification-bar .alert{
            width:100%;
            margin-left:0px;
        }
    }


    </style>

    <?php
