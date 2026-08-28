<?php
/**
 *
 * @package WordPress
 * @subpackage Custom
 * @since Custom 1.0
 */

get_header(); ?>    
    
    <div id="content">
    	<div class="container">
            <h2 class="h1">404</h2>
            <h3>Pagina niet gevonden</h3>      
            <p>De gevraagde pagina werd niet gevonden. Klik <a href="<?php bloginfo('url'); ?>">hier</a> om naar de homepage terug te keren.</p>
        </div>
	</div>

<?php get_footer(); ?>