<?php
/**
 *
 * @package WordPress
 * @subpackage Custom
 * @since Custom 1.0
 */
?>	
	<?php $footer = get_field('footer','option'); ?>
    <div id="footer">
    	<div class="skyline_img_2">
        	<img src="<?php bloginfo('template_url'); ?>/images/skyline2.png" alt="">
        </div>
        <div class="footer">
        	<div class="container">	
            	<div class="f_inn">
                	<p><?php echo $footer['copyright']; ?></p>
                    <div class="social">
                    	<a href="<?php echo $footer['facebook']; ?>" target="_blank"><img src="<?php bloginfo('template_url'); ?>/images/fb.svg" alt=""></a>
                        <a href="<?php echo $footer['instagram']; ?>" target="_blank"><img src="<?php bloginfo('template_url'); ?>/images/insta.svg" alt=""></a>
                    </div>
                    <?php wp_nav_menu( array( 'theme_location' => 'footer-menu', 'container' => '' ) ); ?>
                </div>
            </div>
        </div>
    </div>

<?php wp_footer(); ?>

<script src="https://kit.fontawesome.com/48ccd199be.js" crossorigin="anonymous"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/jquery/latest/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
<script type="text/javascript" src="<?php bloginfo('template_url') ?>/js/dan.js" ></script>
<script type="text/javascript">

function vv_number_format(number, showCurrency = false) {
    number = parseFloat(number);
    
    let currency = '<?php echo (vv_get_config('site_currency') != '') ? vv_get_config('site_currency') : 'NOK' ?>';
    let formatted;

    if (currency === 'NOK') {
        // Format with comma decimal and space thousands
        formatted = number.toLocaleString('nb-NO', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
        formatted = formatted.replace(',00', ',-');
    } else {
        // Just thousands separator, no decimals
        formatted = Math.round(number).toLocaleString();
    }

    return showCurrency ? `${formatted} ${currency}` : formatted;
}

</script>
<?php 
global $footer_codes;
echo $footer_codes;
?>

</body>
</html>