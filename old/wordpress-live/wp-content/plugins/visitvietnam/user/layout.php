<?php
global $head_codes, $footer_codes;
$head_codes   = isset( $head_codes ) ? $head_codes : '';
$footer_codes = isset( $footer_codes ) ? $footer_codes : '';

ob_start();
include $user_view_file;
$page_content = ob_get_clean();

get_header();
?>
<style>
	#guest-portal { padding: 40px 0 80px; background: #F0E8D5; min-height: 60vh; }
	#guest-portal .portal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; flex-wrap: wrap; gap: 15px; }
	#guest-portal h1 { color: #013735; font-size: 32px; margin-bottom: 0; }
	#guest-portal .btn-outline { border: 1px solid #013735; color: #013735; border-radius: 999px; padding: 8px 18px; }
	#guest-portal .btn-outline:hover { background: #013735; color: #fff; text-decoration: none; }
	#guest-portal .card-booking { background: #fff; border-radius: 20px; padding: 24px; margin-bottom: 20px; border: 1px solid #BEA473; }
	#guest-portal .status-paid { color: #28a745; }
	#guest-portal .alert-portal { border-radius: 12px; }
</style>
<div id="guest-portal">
	<div class="container">
		<?php showSuccessMsg( false ); ?>
		<?php showErrorMsg( false ); ?>
		<?php echo $page_content; ?>
	</div>
</div>
<?php
echo $footer_codes;
get_footer();
