<?php
/** Nav v3 quick-add booking (replaces legacy multi-step search wizard). */
$host_id = intval( $logged_user_id );

$apt_filter = [ 'status' => 'all', 'per_page' => 'all' ];
if ( $logged_user_role === 'partner' ) {
	$apt_filter['user_id'] = $host_id;
}

$host_apartments = vv_get_apartments( $apt_filter );
if ( ! is_array( $host_apartments ) ) {
	$host_apartments = [];
}

ob_start();
?>
<link rel="stylesheet" href="<?php echo esc_url( vv_plugins_url() . 'admin/css/apartments-wizard.css?v=2026book1' ); ?>" media="all">
<?php
$header_codes .= ob_get_clean();
?>
<div class="vv-manage2026 vv-bookings2026">
    <div class="vv-manage2026-header mb-4">
        <div>
            <h1><?php vv_e( 'Add booking' ); ?></h1>
            <p class="text-muted small mb-0"><?php vv_e( 'Create a manual reservation for one of your apartments.' ); ?></p>
        </div>
        <div>
            <a href="<?php echo esc_url( vv_admin_url( 'booking' ) ); ?>" class="btn btn-sm btn-outline-secondary"><?php vv_e( 'Back to bookings' ); ?></a>
        </div>
    </div>

    <div class="vv-manage2026-card">
        <form method="post" id="bookings2026QuickAddForm">
            <input type="hidden" name="vv_action" value="host_quick_booking">
            <?php
            include __DIR__ . '/_quick-add-form.php';
            ?>
            <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                <a href="<?php echo esc_url( vv_admin_url( 'booking' ) ); ?>" class="vv-apt2026-cancel"><?php vv_e( 'Cancel' ); ?></a>
                <button type="submit" class="vv-apt2026-btn-next"><?php vv_e( 'Save booking' ); ?></button>
            </div>
        </form>
    </div>
</div>

<?php
ob_start();
?>
<script type="text/javascript">
jQuery(function ($) {
    $('#bookings2026Apartment').on('change', function () {
        var price = $(this).find(':selected').data('price');
        if (price && parseFloat(price) > 0) {
            $('#bookings2026DailyPrice').attr('placeholder', price);
        }
    });
});
</script>
<?php
$footer_codes .= ob_get_clean();
