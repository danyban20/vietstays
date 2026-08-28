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
<?php wp_head(); ?>
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
                    	<a href="#" class="lang_btn">EN</a>
                    </div>
                    <?php $register_now = $header['register_now']; if($register_now){ ?>
	                    <a href="<?php echo $register_now['url']; ?>" target="<?php echo $register_now['target']; ?>" class="btn"><?php echo $register_now['title']; ?></a>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>