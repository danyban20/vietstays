<?php
/**
 * Template Name: About Page
 * @package WordPress
 * @subpackage Custom
 * @since Custom 1.0
 */

get_header(); ?>

<?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>    
	               

<?php endwhile; // end of the loop. ?>

<?php get_footer(); ?>