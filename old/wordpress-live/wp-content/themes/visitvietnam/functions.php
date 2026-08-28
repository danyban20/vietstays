<?php
/** 
 *
 * @package WordPress
 * @subpackage Custom
 * @since Custom 1.0
 */
 
// Theme Settings
require_once('inc/theme-settings.php');

add_action( 'after_setup_theme', function () {
	load_theme_textdomain( 'visitvietnam', get_template_directory() . '/languages' );
} );
 
// load custom scripts
function custom_scripts(){
	wp_enqueue_style( 'google-fonts', 'https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,500,500i,600,600i,700,700i' );

	wp_enqueue_style( 'swiper-bundle', 'https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css' );
	wp_enqueue_style( 'ion-rangeSlide', get_template_directory_uri() . '/css/ion.rangeSlider.min.css' );
	wp_enqueue_style( 'fancybox', get_template_directory_uri() . '/css/fancybox.min.css' );
	wp_enqueue_style( 'fonts', get_template_directory_uri() . '/fonts/fonts.css' );	
	wp_enqueue_style( 'neno', get_template_directory_uri() . '/css/neno.css' );
	wp_enqueue_style( 'grid', get_template_directory_uri() . '/css/grid.css' );
	wp_enqueue_style( 'common-style', get_template_directory_uri() . '/css/common.css' );
	wp_enqueue_style( 'style', get_template_directory_uri() . '/css/style.css' );
	wp_enqueue_style( 'responsive', get_template_directory_uri() . '/css/responsive.css' );	
	

	
	wp_enqueue_script( 'ion.rangeSlider.min', get_template_directory_uri() . '/js/ion.rangeSlider.min.js', array( 'jquery' ) );
	wp_enqueue_script( 'swiper', get_template_directory_uri() . '/js/swiper.js', array( 'jquery' ) );
	wp_enqueue_script( 'jquery.fancybox', get_template_directory_uri() . '/js/jquery.fancybox.min.js', array( 'jquery' ) );
	wp_enqueue_script( 'stickyMojo', get_template_directory_uri() . '/js/stickyMojo.js', array( 'jquery' ) );
	wp_enqueue_script( 'neno', get_template_directory_uri() . '/js/neno.js', array( 'jquery' ) );
	wp_enqueue_script( 'scripts', get_template_directory_uri() . '/js/scripts.js', array( 'jquery' ) );
	
}
add_action( 'wp_enqueue_scripts', 'custom_scripts' );

// This theme uses wp_nav_menu() in one location.
register_nav_menus( array(
	'main-menu' => __( 'Main Menu', 'custom' ),
	'footer-menu' => __( 'Footer Menu', 'custom' )
) );

// Thumbnail sizes
add_image_size( 'medium-thumbnail', 550, 450, true );

// Sets the post excerpt length and link
add_filter( 'excerpt_length', function($length){ return 20; } );
add_filter( 'excerpt_more', function(){ return '...'; } );

// create post type
//create_post_type( $post_type = 'apartments', $name = 'Apartments', $singular_name = 'Apartments', $slug = 'apartments' );
//create_taxonomy( $taxonomy = 'districts_cat', $name = 'Districts categories', $singular_name = 'Districts categories', $slug = 'districts-category', $post_type = 'apartments' );
create_taxonomy( $taxonomy = 'city_cat', $name = 'City categories', $singular_name = 'City categories', $slug = 'city-category', $post_type = 'apartments' );
//create_taxonomy( $taxonomy = 'apartments_cat', $name = 'Apartments Categories', $singular_name = 'apartments Category', $slug = 'apartments-category', $post_type = 'apartments' );

// Disable Gutenberg
add_filter('use_block_editor_for_post', '__return_false', 10);
add_filter('use_block_editor_for_post_type', '__return_false', 10);


