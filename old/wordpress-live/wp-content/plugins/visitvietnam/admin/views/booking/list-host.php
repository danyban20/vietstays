<?php
/** Host bookings list — Nav v3 / 2026 design. */
global $wpdb;

$host_id   = intval( $logged_user_id );
$active_tab = sanitize_key( GET_Request( 'tab' ) );
if ( $active_tab === '' ) {
	$active_tab = 'all';
}

$pgnum = max( 1, intval( GET_Request( 'pgnum' ) ) );

$filter = [
	'per_page'        => 30,
	'pgnum'           => $pgnum,
	'return_total'    => 1,
	'apartment_owner' => $host_id,
	'booking_tab'     => $active_tab === 'all' ? '' : $active_tab,
];

$apartment_filter = intval( GET_Request( 'apartment_id' ) );
if ( $apartment_filter > 0 ) {
	$filter['apartment_ids'] = [ $apartment_filter ];
}

$search = trim( GET_Request( 'search' ) );
if ( $search !== '' ) {
	$filter['search'] = $search;
}

$data       = $this->booking_class->get_bookings( $filter );
$bookings   = gArrayItem( $data, 'bookings' );
$total_rows = intval( gArrayItem( $data, 'total_rows' ) );

if ( ! is_array( $bookings ) ) {
	$bookings = [];
}

$dashboard = $this->booking_class->get_host_booking_dashboard( $host_id );
$tab_counts = gArrayItem( $dashboard, 'tab_counts', [] );

$host_apartments = vv_get_apartments( [
	'user_id' => $host_id,
	'status'  => 'all',
] );
if ( ! is_array( $host_apartments ) ) {
	$host_apartments = [];
}

$currency = get_user_meta( $host_id, 'vv_host_currency', true );
if ( $currency === '' ) {
	$currency = 'USD';
}

$fee_pct = floatval( vv_get_config( 'platform_fee_percent' ) );
if ( $fee_pct <= 0 ) {
	$fee_pct = 5;
}

$tabs = [
	'all'      => vv__( 'All' ),
	'active'   => vv__( 'Active stays' ),
	'upcoming' => vv__( 'Upcoming' ),
	'past'     => vv__( 'Past' ),
	'pending'  => vv__( 'Pending approval' ),
	'urgent'   => vv__( 'Needs attention' ),
];

$base_url = vv_admin_url( 'booking' );

if ( ! function_exists( 'vv_bookings2026_status_badge' ) ) {
	function vv_bookings2026_status_badge( $status ) {
		$status = sanitize_key( $status );
		$class  = 'vv-manage2026-badge--draft';
		if ( in_array( $status, [ 'paid', 'confirmed', 'completed' ], true ) ) {
			$class = 'vv-manage2026-badge--active';
		} elseif ( $status === 'pending' ) {
			$class = 'vv-manage2026-badge--pending';
		}
		return '<span class="vv-manage2026-badge ' . esc_attr( $class ) . '">' . esc_html( ucfirst( $status ) ) . '</span>';
	}
}

$delete_booking_confirm = esc_js( vv__( 'Delete this booking?' ) );
$approve_confirm        = esc_js( vv__( 'Approve this booking request?' ) );
$reject_confirm         = esc_js( vv__( 'Reject this booking request?' ) );

ob_start();
?>
<link rel="stylesheet" href="<?php echo esc_url( vv_plugins_url() . 'admin/css/apartments-wizard.css?v=2026k' ); ?>" media="all">
<?php
$header_codes .= ob_get_clean();
?>
<div class="vv-manage2026 vv-bookings2026">
	<div class="vv-manage2026-header">
		<div>
			<h1><?php vv_e( 'My bookings' ); ?></h1>
			<p class="text-muted small mb-0"><?php vv_e( 'Manage reservations across all your apartments.' ); ?></p>
		</div>
		<div class="text-nowrap">
			<a href="<?php echo esc_url( vv_admin_url( 'apartments/gantt' ) ); ?>" class="btn btn-sm btn-outline-secondary mr-1"><?php vv_e( 'Portfolio calendar' ); ?></a>
			<button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#bookings2026AddModal"><?php vv_e( 'Add booking' ); ?></button>
		</div>
	</div>

	<div class="vv-manage2026-dashboard mb-4">
		<div class="row">
			<div class="col-md-3 col-6 mb-3">
				<div class="vv-manage2026-stat-card">
					<span class="vv-manage2026-stat-label"><?php vv_e( 'Total bookings' ); ?></span>
					<span class="vv-manage2026-stat-value"><?php echo intval( gArrayItem( $dashboard, 'total_bookings' ) ); ?></span>
					<span class="vv-manage2026-stat-sub"><?php echo intval( gArrayItem( $tab_counts, 'upcoming' ) ); ?> <?php vv_e( 'upcoming' ); ?></span>
				</div>
			</div>
			<div class="col-md-3 col-6 mb-3">
				<div class="vv-manage2026-stat-card">
					<span class="vv-manage2026-stat-label"><?php vv_e( 'Active stays' ); ?></span>
					<span class="vv-manage2026-stat-value"><?php echo intval( gArrayItem( $tab_counts, 'active' ) ); ?></span>
					<span class="vv-manage2026-stat-sub"><?php echo intval( gArrayItem( $tab_counts, 'pending' ) ); ?> <?php vv_e( 'awaiting approval' ); ?></span>
				</div>
			</div>
			<div class="col-md-3 col-6 mb-3">
				<div class="vv-manage2026-stat-card">
					<span class="vv-manage2026-stat-label"><?php vv_e( '30-day revenue' ); ?></span>
					<span class="vv-manage2026-stat-value vv-manage2026-stat-value--sm"><?php echo esc_html( vv_number_format( gArrayItem( $dashboard, 'revenue_30' ), true ) ); ?></span>
					<span class="vv-manage2026-stat-sub"><?php echo esc_html( $currency ); ?></span>
				</div>
			</div>
			<div class="col-md-3 col-6 mb-3">
				<div class="vv-manage2026-stat-card">
					<span class="vv-manage2026-stat-label"><?php vv_e( 'Your net (30 days)' ); ?></span>
					<span class="vv-manage2026-stat-value vv-manage2026-stat-value--sm"><?php echo esc_html( vv_number_format( gArrayItem( $dashboard, 'host_net_30' ), true ) ); ?></span>
					<span class="vv-manage2026-stat-sub"><?php printf( esc_html( vv__( 'After %s%% platform fee' ) ), esc_html( $fee_pct ) ); ?></span>
				</div>
			</div>
		</div>
	</div>

	<ul class="nav nav-tabs vv-manage2026-tabs mb-3">
		<?php foreach ( $tabs as $tab_key => $tab_label ) {
			$tab_url = add_query_arg( [ 'tab' => $tab_key ], $base_url );
			if ( $apartment_filter > 0 ) {
				$tab_url = add_query_arg( 'apartment_id', $apartment_filter, $tab_url );
			}
			$count = intval( gArrayItem( $tab_counts, $tab_key ) );
			?>
			<li class="nav-item">
				<a class="nav-link<?php echo $active_tab === $tab_key ? ' active' : ''; ?>" href="<?php echo esc_url( $tab_url ); ?>">
					<?php echo esc_html( $tab_label ); ?>
					<span class="badge badge-light ml-1"><?php echo $count; ?></span>
				</a>
			</li>
		<?php } ?>
	</ul>

	<div class="card mb-4">
		<div class="card-body pb-2">
			<form method="get" action="<?php echo esc_url( $base_url ); ?>" class="form-row align-items-end">
				<input type="hidden" name="tab" value="<?php echo esc_attr( $active_tab ); ?>">
				<div class="form-group col-md-4 col-sm-6">
					<label class="small mb-1"><?php vv_e( 'Apartment' ); ?></label>
					<select name="apartment_id" class="form-control form-control-sm">
						<option value=""><?php vv_e( 'All apartments' ); ?></option>
						<?php foreach ( $host_apartments as $apt ) { ?>
							<option value="<?php echo intval( $apt['ID'] ); ?>"<?php selected( $apartment_filter, intval( $apt['ID'] ) ); ?>><?php echo esc_html( stripslashes( gArrayItem( $apt, 'name' ) ) ); ?></option>
						<?php } ?>
					</select>
				</div>
				<div class="form-group col-md-4 col-sm-6">
					<label class="small mb-1"><?php vv_e( 'Search guest or booking #' ); ?></label>
					<input type="search" name="search" class="form-control form-control-sm" value="<?php echo esc_attr( $search ); ?>" placeholder="<?php echo esc_attr( vv__( 'Name, email, booking number…' ) ); ?>">
				</div>
				<div class="form-group col-md-4 col-sm-12">
					<button type="submit" class="btn btn-sm btn-secondary mr-1"><?php vv_e( 'Filter' ); ?></button>
					<a href="<?php echo esc_url( add_query_arg( 'tab', $active_tab, $base_url ) ); ?>" class="btn btn-sm btn-outline-secondary"><?php vv_e( 'Reset' ); ?></a>
				</div>
			</form>
		</div>
	</div>

	<div class="card">
		<div class="card-body p-0">
			<?php if ( empty( $bookings ) ) { ?>
				<p class="text-muted p-4 mb-0"><?php vv_e( 'No bookings match this filter.' ); ?></p>
			<?php } else { ?>
				<div class="table-responsive">
					<table class="table table-hover mb-0 vv-bookings2026-table">
						<thead>
							<tr>
								<th><?php vv_e( 'Booking' ); ?></th>
								<th><?php vv_e( 'Dates' ); ?></th>
								<th><?php vv_e( 'Apartment' ); ?></th>
								<th><?php vv_e( 'Guest' ); ?></th>
								<th class="text-right"><?php vv_e( 'Amount' ); ?></th>
								<th><?php vv_e( 'Status' ); ?></th>
								<th class="text-right"><?php vv_e( 'Actions' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $bookings as $booking ) {
								$booking_id     = intval( gArrayItem( $booking, 'ID' ) );
								$status         = gArrayItem( $booking, 'status' );
								$booked_dates   = vv_get_date_range_text( gArrayItem( $booking, 'check_in_date' ), gArrayItem( $booking, 'check_out_date' ) );
								$district_name  = vv_get_district_display_name( intval( gArrayItem( $booking, 'district_id' ) ) );
								$guest_name     = trim( gArrayItem( $booking, 'firstname' ) . ' ' . gArrayItem( $booking, 'lastname' ) );
								$apt_manage_url = vv_admin_url( 'apartments/manage?id=' . intval( gArrayItem( $booking, 'apartment_id' ) ) . '&tab=availability' );
								?>
								<tr>
									<td>
										<a href="#" data-toggle="modal" data-target="#bookingModal<?php echo $booking_id; ?>" class="font-weight-bold"><?php echo esc_html( vv_get_booking_num( $booking ) ); ?></a>
									</td>
									<td><?php echo esc_html( $booked_dates ); ?></td>
									<td>
										<div><?php echo esc_html( stripslashes( gArrayItem( $booking, 'apartment_name' ) ) ); ?></div>
										<div class="small text-muted"><?php echo esc_html( stripslashes( $district_name ) ); ?></div>
									</td>
									<td>
										<div><?php echo esc_html( $guest_name ); ?></div>
										<?php if ( trim( gArrayItem( $booking, 'email' ) ) !== '' ) { ?>
											<div class="small text-muted"><?php echo esc_html( gArrayItem( $booking, 'email' ) ); ?></div>
										<?php } ?>
									</td>
									<td class="text-right"><?php echo esc_html( vv_number_format( gArrayItem( $booking, 'total' ), true ) ); ?></td>
									<td><?php echo vv_bookings2026_status_badge( $status ); ?></td>
									<td class="text-right text-nowrap">
										<a href="#" data-toggle="modal" data-target="#bookingModal<?php echo $booking_id; ?>" class="btn btn-link btn-sm p-0 mr-2"><?php vv_e( 'Details' ); ?></a>
										<a href="<?php echo esc_url( $apt_manage_url ); ?>" class="btn btn-link btn-sm p-0 mr-2"><?php vv_e( 'Calendar' ); ?></a>
										<?php if ( $status === 'pending' ) { ?>
											<a href="<?php echo esc_url( vv_admin_url( '?vv_action=booking-set-status&id=' . $booking_id . '&status=confirmed' ) ); ?>" class="btn btn-link btn-sm text-success p-0 mr-1" onclick="return confirm('<?php echo $approve_confirm; ?>')"><?php vv_e( 'Approve' ); ?></a>
											<a href="<?php echo esc_url( vv_admin_url( '?vv_action=booking-set-status&id=' . $booking_id . '&status=cancelled' ) ); ?>" class="btn btn-link btn-sm text-danger p-0" onclick="return confirm('<?php echo $reject_confirm; ?>')"><?php vv_e( 'Reject' ); ?></a>
										<?php } ?>
									</td>
								</tr>
							<?php } ?>
						</tbody>
					</table>
				</div>
			<?php } ?>
		</div>
		<?php if ( $total_rows > 30 ) { ?>
			<div class="card-footer d-flex justify-content-between align-items-center small">
				<span><?php printf( esc_html( vv__( 'Showing page %1$d of %2$d (%3$d bookings)' ) ), $pgnum, max( 1, ceil( $total_rows / 30 ) ), $total_rows ); ?></span>
				<div>
					<?php
					$prev_args = array_filter( [
						'tab'          => $active_tab,
						'pgnum'        => $pgnum > 1 ? $pgnum - 1 : null,
						'apartment_id' => $apartment_filter > 0 ? $apartment_filter : null,
						'search'       => $search !== '' ? $search : null,
					] );
					$next_args = array_filter( [
						'tab'          => $active_tab,
						'pgnum'        => ( $pgnum * 30 ) < $total_rows ? $pgnum + 1 : null,
						'apartment_id' => $apartment_filter > 0 ? $apartment_filter : null,
						'search'       => $search !== '' ? $search : null,
					] );
					?>
					<?php if ( $pgnum > 1 ) { ?>
						<a href="<?php echo esc_url( add_query_arg( $prev_args, $base_url ) ); ?>" class="btn btn-sm btn-outline-secondary mr-1">&larr; <?php vv_e( 'Previous' ); ?></a>
					<?php } ?>
					<?php if ( ( $pgnum * 30 ) < $total_rows ) { ?>
						<a href="<?php echo esc_url( add_query_arg( $next_args, $base_url ) ); ?>" class="btn btn-sm btn-outline-secondary"><?php vv_e( 'Next' ); ?> &rarr;</a>
					<?php } ?>
				</div>
			</div>
		<?php } ?>
	</div>

	<div class="vv-manage2026-sidebar mt-4">
		<h6 class="mb-2"><?php vv_e( 'Tip' ); ?></h6>
		<p class="small mb-0"><?php vv_e( 'For day-to-day availability, open an apartment and use the Availability tab. Use this page to review all bookings and approve guest requests.' ); ?></p>
	</div>
</div>

<div class="modal fade" id="bookings2026AddModal" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<form method="post" class="modal-content vv-bookings2026-modal">
			<input type="hidden" name="vv_action" value="host_quick_booking">
			<div class="modal-header vv-bookings2026-modal__header">
				<h5 class="modal-title"><?php vv_e( 'Add booking' ); ?></h5>
				<button type="button" class="close text-white" data-dismiss="modal" aria-label="<?php echo esc_attr( vv__( 'Close' ) ); ?>"><span aria-hidden="true">&times;</span></button>
			</div>
			<div class="modal-body">
				<?php include __DIR__ . '/_quick-add-form.php'; ?>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal"><?php vv_e( 'Cancel' ); ?></button>
				<button type="submit" class="btn btn-sm btn-primary"><?php vv_e( 'Save booking' ); ?></button>
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
	<?php if ( GET_Request( 'add' ) === '1' ) { ?>
	$('#bookings2026AddModal').modal('show');
	<?php } ?>
});
</script>
<?php
include __DIR__ . '/modal.php';
$footer_codes .= ob_get_clean();
