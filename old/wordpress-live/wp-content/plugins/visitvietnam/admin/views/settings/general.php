<?php 
global $wpdb;

$pg_title   = 'General Settings';
$meta_title = vv_admin_meta_title( $pg_title );

?>
<form method="post">
    <input type="hidden" name="action" value="save_settings" >
    <div class="row">
        <div class="col-md-6">
            <div class="card">
            </div>
        </div><!-- .col -->
    </div>
    <div class="mt-4">
        <button type="submit" class="btn btn-primary"><?php vv_e( 'Save Changes' ); ?></button>
    </div>
</form>
