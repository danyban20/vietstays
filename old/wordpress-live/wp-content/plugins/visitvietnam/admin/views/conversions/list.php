<?php
if ( ! class_exists( 'vvConversions' ) || ! vvConversions::can_manage() ) {
	echo '<div class="alert alert-danger">' . esc_html( vv__( 'You do not have access to this page.' ) ) . '</div>';
	return;
}

$pg_title   = 'Conversions';
$meta_title = vv_admin_meta_title( $pg_title );
vv_show_page_title_bar( [ 'pg_title' => $pg_title ] );

global $logged_user_role, $logged_user_id;

$status_filter = sanitize_text_field( GET_Request( 'status' ) );
$search        = sanitize_text_field( GET_Request( 'search' ) );

$filter = [
	'search'       => $search,
	'per_page'     => 50,
	'return_total' => 1,
];
if ( $status_filter !== '' && $status_filter !== 'all' ) {
	$filter['status'] = $status_filter;
}
if ( intval( GET_Request( 'apartment_id' ) ) > 0 ) {
	$filter['apartment_id'] = intval( GET_Request( 'apartment_id' ) );
}
if ( $logged_user_role === 'partner' ) {
	$filter['host_id'] = $logged_user_id;
}

$result      = $this->conversions_class->get_conversions( $filter );
$conversions = gArrayItem( $result, 'rows', [] );
$total_rows  = intval( gArrayItem( $result, 'total_rows' ) );

$month_stats = $this->conversions_class->host_monthly_stats(
	$logged_user_role === 'partner' ? $logged_user_id : get_current_user_id()
);

$apt_filter = [];
if ( $logged_user_role === 'partner' ) {
	$apt_filter['user_id'] = $logged_user_id;
}
$apt_filter['status'] = 'all';
$apartments = vv_get_apartments( $apt_filter );

$platforms      = vv_conversion_platforms();
$status_labels  = vvConversions::status_labels();
$cancel_confirm = esc_js( vv__( 'Cancel this conversion invite?' ) );
?>

<div class="row mb-3">
    <div class="col-md-8">
        <form method="get" class="form-inline">
            <input type="text" name="search" class="form-control form-control-sm mr-2 mb-2" placeholder="<?php echo esc_attr( vv__( 'Search guest, ref, apartment…' ) ); ?>" value="<?php echo esc_attr( $search ); ?>">
            <select name="status" class="form-control form-control-sm mr-2 mb-2">
                <option value=""><?php vv_e( 'All statuses' ); ?></option>
                <option value="all" <?php selected( $status_filter, 'all' ); ?>><?php vv_e( 'All (explicit)' ); ?></option>
                <?php foreach ( $status_labels as $key => $label ) { ?>
                    <option value="<?php echo esc_attr( $key ); ?>" <?php selected( $status_filter, $key ); ?>><?php echo esc_html( vv__( $label ) ); ?></option>
                <?php } ?>
            </select>
            <button type="submit" class="btn btn-sm btn-primary mb-2"><?php vv_e( 'Filter' ); ?></button>
            <?php if ( $search !== '' || $status_filter !== '' ) { ?>
                <a href="<?php echo esc_url( vv_admin_url( 'conversions' ) ); ?>" class="btn btn-sm btn-light ml-2 mb-2"><?php vv_e( 'Clear' ); ?></a>
            <?php } ?>
        </form>
    </div>
    <div class="col-md-4 text-md-right">
        <span class="badge badge-secondary"><?php printf( esc_html( vv__( '%1$d / %2$d conversions this month' ) ), intval( $month_stats['used'] ), intval( $month_stats['limit'] ) ); ?></span>
    </div>
</div>

<div class="row mb-4">
    <div class="col-lg-5">
        <div class="card">
            <div class="card-header"><strong><?php vv_e( 'Register external booking' ); ?></strong></div>
            <div class="card-body">
                <p class="text-muted small"><?php printf( esc_html( vv__( 'Invite a guest from Airbnb, Booking.com, or Trip.com to rebook on Vietstays with %d%% off. Single use, valid %d months.' ) ), intval( vvConversions::DISCOUNT_PCT ), intval( vvConversions::EXPIRY_MONTHS ) ); ?></p>
                <?php if ( intval( $month_stats['remaining'] ) <= 0 ) { ?>
                    <div class="alert alert-warning small"><?php printf( esc_html( vv__( 'Monthly limit reached (%d). Try again next month.' ) ), intval( $month_stats['limit'] ) ); ?></div>
                <?php } else { ?>
                <form method="post">
                    <input type="hidden" name="vv_action" value="vv_save_conversion">
                    <div class="form-group">
                        <label class="small font-weight-bold"><?php vv_e( 'Apartment' ); ?></label>
                        <select name="apartment_id" class="form-control form-control-sm" required>
                            <option value=""><?php vv_e( 'Select apartment…' ); ?></option>
                            <?php foreach ( $apartments as $apt ) { ?>
                                <option value="<?php echo intval( $apt['ID'] ); ?>"><?php echo esc_html( stripslashes( gArrayItem( $apt, 'name' ) ) ); ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="small font-weight-bold"><?php vv_e( 'External platform' ); ?></label>
                        <select name="external_platform" class="form-control form-control-sm">
                            <?php foreach ( $platforms as $key => $label ) { ?>
                                <option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="small font-weight-bold"><?php vv_e( 'External booking ref' ); ?> <span class="text-muted">(<?php vv_e( 'optional' ); ?>)</span></label>
                        <input type="text" name="external_booking_ref" class="form-control form-control-sm" placeholder="<?php echo esc_attr( vv__( 'e.g. Airbnb confirmation code' ) ); ?>">
                    </div>
                    <div class="row">
                        <div class="col-6 form-group">
                            <label class="small font-weight-bold"><?php vv_e( 'Guest email' ); ?></label>
                            <input type="email" name="guest_email" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-6 form-group">
                            <label class="small font-weight-bold"><?php vv_e( 'Guest name' ); ?></label>
                            <input type="text" name="guest_name" class="form-control form-control-sm">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6 form-group">
                            <label class="small font-weight-bold"><?php vv_e( 'Check-in' ); ?> <span class="text-muted">(<?php vv_e( 'optional' ); ?>)</span></label>
                            <input type="date" name="check_in_date" class="form-control form-control-sm">
                        </div>
                        <div class="col-6 form-group">
                            <label class="small font-weight-bold"><?php vv_e( 'Check-out' ); ?> <span class="text-muted">(<?php vv_e( 'optional' ); ?>)</span></label>
                            <input type="date" name="check_out_date" class="form-control form-control-sm">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm"><?php vv_e( 'Create conversion' ); ?></button>
                </form>
                <?php } ?>
            </div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong><?php vv_e( 'Conversion overview' ); ?></strong>
                <span class="text-muted small"><?php echo intval( $total_rows ); ?> <?php vv_e( 'total' ); ?></span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-striped mb-0">
                        <thead>
                            <tr>
                                <th><?php vv_e( 'Ref' ); ?></th>
                                <th><?php vv_e( 'Guest' ); ?></th>
                                <th><?php vv_e( 'Apartment' ); ?></th>
                                <th><?php vv_e( 'Promo' ); ?></th>
                                <th><?php vv_e( 'Platform' ); ?></th>
                                <th><?php vv_e( 'Status' ); ?></th>
                                <th><?php vv_e( 'Expires' ); ?></th>
                                <th class="text-right"><?php vv_e( 'Actions' ); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ( count( $conversions ) === 0 ) { ?>
                                <tr><td colspan="8" class="text-center text-muted py-4"><?php vv_e( 'No conversions yet.' ); ?></td></tr>
                            <?php } ?>
                            <?php foreach ( $conversions as $row ) {
                                $id     = intval( gArrayItem( $row, 'ID' ) );
                                $status = gArrayItem( $row, 'status' );
                                $badge  = 'secondary';
                                if ( $status === 'sent' || $status === 'opened' ) {
                                    $badge = 'warning';
                                } elseif ( $status === 'converted' ) {
                                    $badge = 'success';
                                } elseif ( $status === 'expired' || $status === 'cancelled' ) {
                                    $badge = 'dark';
                                }
                                $platform_label = gArrayItem( $platforms, gArrayItem( $row, 'external_platform' ), gArrayItem( $row, 'external_platform' ) );
                                $invite_link    = vvConversions::stay_welcome_url( gArrayItem( $row, 'conversion_token' ) );
                                $expires_label  = gArrayItem( $row, 'expires_at' )
                                    ? date_i18n( get_option( 'date_format' ), strtotime( gArrayItem( $row, 'expires_at' ) ) )
                                    : '—';
                                ?>
                                <tr>
                                    <td><code><?php echo esc_html( gArrayItem( $row, 'conversion_ref' ) ); ?></code></td>
                                    <td>
                                        <?php echo esc_html( gArrayItem( $row, 'guest_name' ) ?: '—' ); ?><br>
                                        <small class="text-muted"><?php echo esc_html( gArrayItem( $row, 'guest_email' ) ); ?></small>
                                    </td>
                                    <td><?php echo esc_html( gArrayItem( $row, 'apartment_name' ) ); ?></td>
                                    <td><code><?php echo esc_html( gArrayItem( $row, 'promo_code' ) ); ?></code></td>
                                    <td><?php echo esc_html( $platform_label ); ?></td>
                                    <td><span class="badge badge-<?php echo esc_attr( $badge ); ?>"><?php echo esc_html( vv__( vvConversions::status_label( $status ) ) ); ?></span></td>
                                    <td class="small"><?php echo esc_html( $expires_label ); ?></td>
                                    <td class="text-right text-nowrap">
                                        <?php if ( in_array( $status, [ 'sent', 'opened', 'registered', 'draft' ], true ) && gArrayItem( $row, 'conversion_token' ) ) { ?>
                                            <button type="button" class="btn btn-xs btn-outline-info btn-sm py-0 vv-copy-conv-link" data-link="<?php echo esc_attr( $invite_link ); ?>" title="<?php echo esc_attr( vv__( 'Copy invite link' ) ); ?>"><i class="fa fa-link"></i></button>
                                        <?php } ?>
                                        <?php if ( $status === 'draft' ) { ?>
                                            <form method="post" class="d-inline">
                                                <input type="hidden" name="vv_action" value="vv_send_conversion">
                                                <input type="hidden" name="conversion_id" value="<?php echo $id; ?>">
                                                <button type="submit" class="btn btn-xs btn-success btn-sm py-0"><?php vv_e( 'Send invite' ); ?></button>
                                            </form>
                                        <?php } elseif ( in_array( $status, [ 'sent', 'opened', 'registered' ], true ) ) { ?>
                                            <a href="<?php echo esc_url( vv_admin_url( '?action=vv_resend_conversion&id=' . $id ) ); ?>" class="btn btn-xs btn-outline-secondary btn-sm py-0" title="<?php echo esc_attr( vv__( 'Resend email' ) ); ?>"><i class="fa fa-envelope"></i></a>
                                        <?php } ?>
                                        <?php if ( ! in_array( $status, [ 'converted', 'cancelled' ], true ) ) { ?>
                                            <a href="<?php echo esc_url( vv_admin_url( '?action=vv_cancel_conversion&id=' . $id ) ); ?>" class="btn btn-xs btn-outline-danger btn-sm py-0" onclick="return confirm('<?php echo $cancel_confirm; ?>')" title="<?php echo esc_attr( vv__( 'Cancel' ) ); ?>"><i class="fa fa-times"></i></a>
                                        <?php } ?>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col">
        <div class="alert alert-light border small mb-0">
            <strong><?php vv_e( 'How it works' ); ?></strong>
            <ol class="mb-0 pl-3">
                <li><?php vv_e( 'Register the guest\'s external booking and create a conversion.' ); ?></li>
                <li><?php vv_e( 'Send the invite — guest receives a link with a unique promo code.' ); ?></li>
                <li><?php vv_e( 'Guest opens the welcome page, creates an account, and books with discount pre-applied.' ); ?></li>
                <li><?php vv_e( 'When they complete a Vietstays booking, the conversion is marked converted.' ); ?></li>
            </ol>
        </div>
    </div>
</div>

<script>
jQuery(function($){
    $('.vv-copy-conv-link').on('click', function(){
        var link = $(this).data('link');
        if (!link) return;
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(link).then(function(){
                alert('<?php echo esc_js( vv__( 'Invite link copied.' ) ); ?>');
            });
        } else {
            window.prompt('<?php echo esc_js( vv__( 'Copy invite link:' ) ); ?>', link);
        }
    });
});
</script>
