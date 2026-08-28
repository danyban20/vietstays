<?php
/** 
 *
 * @package WordPress
 * @subpackage Custom
 * @since Custom 1.0
 */
 
// This theme uses title tag
add_theme_support( 'title-tag' );

// This theme uses post thumbnails
add_theme_support( 'post-thumbnails' );

// Add default posts and comments RSS feed links to head
add_theme_support( 'automatic-feed-links' );

// Filter do shortcode
add_filter( 'the_content', 'do_shortcode' );
add_filter( 'acf_the_content', 'do_shortcode' );

// Remove p tags on images and shortcodes
function remove_ptags_on_images_shortcodes($content) {
	$content = preg_replace('/<p>\s*(<a .*>)?\s*(<img .* \/>)\s*(<\/a>)?\s*<\/p>/iU', '\1\2\3', $content);
	$content = strtr($content, array('<p>[' => '[', ']</p>' => ']', ']<br />' => ']', ']<br>' => ']'));	
	return $content;
}
add_filter( 'the_content', 'remove_ptags_on_images_shortcodes', 30 );
add_filter( 'acf_the_content', 'remove_ptags_on_images_shortcodes', 30 );

// Remove special characters
function esc_char($string) {
	$result = preg_replace('~[^a-zA-Z0-9]+~', '', $string);
	return $result;
}

// Remove resource hints and shortlink
remove_action( 'wp_head', 'wp_resource_hints', 2 );
remove_action( 'wp_head', 'wp_shortlink_wp_head', 10 );
remove_action( 'wp_head', 'rsd_link' );
remove_action( 'wp_head', 'wlwmanifest_link' );
remove_action( 'wp_head', 'wp_oembed_add_discovery_links', 10 );

// Remove wp version param from any enqueued scripts
function vc_remove_wp_ver_css_js( $src ) {
    if ( strpos( $src, 'ver=' ) )
        $src = remove_query_arg( 'ver', $src );
    return $src;
}
add_filter( 'style_loader_src', 'vc_remove_wp_ver_css_js', 9999 );
add_filter( 'script_loader_src', 'vc_remove_wp_ver_css_js', 9999 );

// Register custom post type
function create_post_type($post_type, $name, $singular_name, $slug) {		
	$labels = array(
		'name'               => $name,
		'singular_name'      => $singular_name,
		'add_new'            => __('Add').' '.$singular_name,
		'add_new_item'       => __('Add').' '.$singular_name,
		'edit_item'          => __('Edit').' '.$singular_name,
		'new_item'           => __('Add').' '.$singular_name,
		'all_items'          => __('All').' '.$name,
		'view_item'          => __('View').' '.$singular_name,
		'search_items'       => __('Search').' '.$name,
		'not_found'          => __('No').' '.$name,
		'not_found_in_trash' => __('No').' '.$name,
		'parent_item_colon'  => '',
		'menu_name'          => $name
	);
	$args = array(
		'labels'             => $labels,
		'public'             => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'query_var'          => true,
		'rewrite'            => array( 'slug' => $slug, 'with_front' => false ),
		'capability_type'    => 'post',
		'has_archive'        => true,
		'hierarchical'       => false,
		'menu_position'      => 5,
		'supports'           => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments' )		
	);
	register_post_type( $post_type, $args );	
}

// Register custom taxonomy
function create_taxonomy($taxonomy, $name, $singular_name, $slug, $post_type) {	
	$labels = array(
		'name'              => $name,
		'singular_name'     => $singular_name,
		'search_items'      => __('Search').' '.$name,
		'all_items'         => __('All').' '.$name,
		'parent_item'       => __('Parent').' '.$singular_name,
		'parent_item_colon' => __('Parent').' '.$singular_name.':',
		'edit_item'         => __('Edit').' '.$singular_name,
		'update_item'       => __('Update').' '.$singular_name,
		'add_new_item'      => __('Add').' '.$singular_name,
		'new_item_name'     => __('Add').' '.$singular_name.' '.__('Name'),
		'menu_name'         => $name
	);
	$args = array(
		'hierarchical'      => true,
		'labels'            => $labels,
		'show_ui'           => true,
		'show_admin_column' => true,
		'query_var'         => true,
		'rewrite'           => array( 'slug' => $slug, 'with_front' => false, 'hierarchical' => true )
	);
	register_taxonomy( $taxonomy, $post_type, $args );
}

// ACF Setting
if( function_exists('acf_add_options_page') ) {
	acf_add_options_page("Theme Options");
}