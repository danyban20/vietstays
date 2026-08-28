<?php 
/** 
 *
 * @package WordPress
 * @subpackage Custom
 * @since Custom 1.0
 */	
 
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta http-equiv="X-UA-Compatible" content="IE=Edge">
<meta name="format-detection" content="telephone=no">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="profile" href="http://gmpg.org/xfn/11">
<link rel="stylesheet" type="text/css" media="all" href="<?php bloginfo( 'stylesheet_url' ); ?>" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" crossorigin="anonymous">
<?php wp_head(); ?>
<script type="text/javascript">
    var vvSiteCurrency = '<?php echo vv_site_currency() ?>';
</script>
<?php 
global $head_codes;
echo $head_codes;
?>
<style type="text/css">
    .custom-dropdown-toggle, .apartments .facilities ul li .name{
        color:#004041;
    }
    .apartments .facilities ul li .icon img{
        margin-top:10px;
    }
    .apartments-list a:hover{
        text-decoration: none;
    }
    .vv-lang-switcher .lang_btn {
        display: inline-block;
        margin-left: 6px;
        opacity: 0.65;
        text-decoration: none;
    }
    .vv-lang-switcher .lang_btn.active {
        opacity: 1;
        font-weight: 700;
        text-decoration: underline;
    }

</style>
</head>

<body <?php body_class(); ?>>    
    
    <?php $header = get_field('header','option'); ?>
	<div id="header">
    	<div class="container">
        	<div class="header">
            	<div class="logo">
                	<a href="/">
                    	<img src="<?php echo $header['logo']; ?>" alt="">
                    </a>
                </div>
                <a href="#" id="menubtn"><span></span><span></span><span></span><span></span></a>
                <div class="head_right">
                	<div id="nav">
						<?php wp_nav_menu( array( 'theme_location' => 'main-menu', 'container' => '' ) ); ?>
                    </div>
                    <div class="lang">
                    	<?php
                    	$current_lang = ( function_exists( 'vvI18n' ) ) ? vvI18n::locale_short( vvI18n::current_locale() ) : 'EN';
                    	$lang_hint    = function_exists( 'vv__' ) ? vv__( 'Change language in Account settings' ) : 'Change language in Account settings';
                    	?>
                    	<span class="lang_btn" title="<?php echo esc_attr( $lang_hint ); ?>"><?php echo esc_html( $current_lang ); ?></span>
                    </div>
                    <?php $register_now = $header['register_now']; if($register_now){ ?>
	                    <a href="<?php echo $register_now['url']; ?>" target="<?php echo $register_now['target']; ?>" class="btn"><?php echo $register_now['title']; ?></a>
                    <?php } ?>
                    <?php if ( ! is_user_logged_in() && function_exists( 'vv_login_url' ) ) { ?>
                    	<a href="<?php echo esc_url( vv_login_url() ); ?>" class="btn btn-outline-secondary ml-2"><?php echo esc_html( function_exists( 'vv__' ) ? vv__( 'Sign in' ) : 'Sign in' ); ?></a>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>