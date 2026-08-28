<?php
/**
 * Template Name: Contact Page
 * @package WordPress
 * @subpackage Custom
 * @since Custom 1.0
 */

get_header(); ?>

<?php if ( have_posts() ) while ( have_posts() ) : the_post(); ?>    

<div id="google_map"></div>
<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAuYGLtTQFU2BFj8Utc5fkCPClaBqv70YU"></script>
<script type="text/javascript">
google.maps.event.addDomListener(window, 'load', init);    
function init() {
	var mapOptions = {
		zoom: 15,
		zoomControl: false,
		mapTypeControl: false,
		scaleControl: false,
		streetViewControl: false,
		rotateControl: false,
		fullscreenControl: false,		
		center: new google.maps.LatLng(50.503887, 4.469936)                       
	};
	var mapElement = document.getElementById('google_map');
	var map = new google.maps.Map(mapElement, mapOptions);
	var marker = new google.maps.Marker({
		position: new google.maps.LatLng(50.503887, 4.469936),
		icon: '/wp-content/themes/custom/images/map_marker.png',
		map: map
	});
}
</script>	               

<?php endwhile; // end of the loop. ?>

<?php get_footer(); ?>