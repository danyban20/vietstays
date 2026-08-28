<?php
/** Availability tab — dual calendar, period list, add period. Expects $apartment, $apartment_id, $this->booking_class. */
$cal_month = intval( GET_Request( 'cal_month' ) );
$cal_year  = intval( GET_Request( 'cal_year' ) );
if ( $cal_month < 1 || $cal_month > 12 ) {
	$cal_month = intval( date( 'n' ) );
}
if ( $cal_year < 2000 ) {
	$cal_year = intval( date( 'Y' ) );
}

$month2 = $cal_month + 1;
$year2  = $cal_year;
if ( $month2 > 12 ) {
	$month2 = 1;
	$year2++;
}

$booking_class = isset( $this->booking_class ) ? $this->booking_class : null;
$map1          = vvApartmentsWizard::get_calendar_day_map( $apartment_id, $cal_year, $cal_month, $booking_class );
$map2          = vvApartmentsWizard::get_calendar_day_map( $apartment_id, $year2, $month2, $booking_class );
$timeline      = vvApartmentsWizard::get_merged_availability_timeline( $apartment_id, $booking_class );

$prev_month = $cal_month - 1;
$prev_year  = $cal_year;
if ( $prev_month < 1 ) {
	$prev_month = 12;
	$prev_year--;
}
$next_month = $cal_month + 1;
$next_year  = $cal_year;
if ( $next_month > 12 ) {
	$next_month = 1;
	$next_year++;
}

$base_url = vv_admin_url( 'apartments/manage?id=' . $apartment_id . '&tab=availability' );

$host_id           = intval( gArrayItem( $apartment, 'user_id' ) );
$can_conversions   = class_exists( 'vvConversions' ) && vvConversions::can_manage();
$conversion_stats  = [ 'used' => 0, 'limit' => 30, 'remaining' => 30 ];
if ( $can_conversions ) {
	$conv_tmp         = new vvConversions();
	$conversion_stats = $conv_tmp->host_monthly_stats( $host_id );
}
$platforms         = vvApartmentsWizard::external_platform_labels();
$conv_status_labels = class_exists( 'vvConversions' ) ? vvConversions::status_labels() : [];
$redirect_avail    = esc_url( vv_admin_url( 'apartments/manage?id=' . $apartment_id . '&tab=availability' ) );
$detail_open       = sanitize_text_field( GET_Request( 'detail' ) );

if ( ! function_exists( 'vv_manage2026_render_calendar' ) ) {
	function vv_manage2026_render_calendar( $year, $month, $day_map ) {
	$days_in_month = intval( date( 't', mktime( 0, 0, 0, $month, 1, $year ) ) );
	$first_dow     = intval( date( 'N', mktime( 0, 0, 0, $month, 1, $year ) ) );
	$month_label   = date( 'F Y', mktime( 0, 0, 0, $month, 1, $year ) );
	?>
	<div class="vv-manage2026-cal-block">
		<h6 class="vv-manage2026-cal-title"><?php echo esc_html( $month_label ); ?></h6>
		<table class="vv-manage2026-mini-cal table table-bordered table-sm">
			<thead><tr><?php foreach ( [ 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa', 'Su' ] as $d ) { ?><th><?php echo esc_html( $d ); ?></th><?php } ?></tr></thead>
			<tbody><tr>
			<?php
			for ( $p = 1; $p < $first_dow; $p++ ) {
				echo '<td></td>';
			}
			for ( $d = 1; $d <= $days_in_month; $d++ ) {
				$date_str = sprintf( '%04d-%02d-%02d', $year, $month, $d );
				$cls      = gArrayItem( $day_map, $date_str, '' );
				echo '<td class="' . esc_attr( $cls ) . '">' . intval( $d ) . '</td>';
				if ( ( $first_dow + $d - 1 ) % 7 === 0 && $d < $days_in_month ) {
					echo '</tr><tr>';
				}
			}
			?>
			</tr></tbody>
		</table>
	</div>
	<?php
	}
}
?>
<div class="row">
	<div class="col-lg-8">
		<div class="card mb-4">
			<div class="card-header d-flex justify-content-between align-items-center">
				<strong><?php vv_e( 'Calendar' ); ?></strong>
				<div class="vv-manage2026-cal-nav">
					<a href="<?php echo esc_url( $base_url . '&cal_month=' . $prev_month . '&cal_year=' . $prev_year ); ?>" class="btn btn-sm btn-outline-secondary">&larr;</a>
					<a href="<?php echo esc_url( $base_url . '&cal_month=' . $next_month . '&cal_year=' . $next_year ); ?>" class="btn btn-sm btn-outline-secondary">&rarr;</a>
				</div>
			</div>
			<div class="card-body">
				<div class="row">
					<div class="col-md-6"><?php vv_manage2026_render_calendar( $cal_year, $cal_month, $map1 ); ?></div>
					<div class="col-md-6"><?php vv_manage2026_render_calendar( $year2, $month2, $map2 ); ?></div>
				</div>
				<p class="small text-muted mb-0 mt-2">
					<span class="booked px-1">■</span> <?php vv_e( 'Vietstays booking' ); ?>
					&nbsp; <span class="blocked px-1">■</span> <?php vv_e( 'Blocked' ); ?>
					&nbsp; <span class="external px-1">■</span> <?php vv_e( 'External booking' ); ?>
				</p>
			</div>
		</div>

		<div class="card mb-4">
			<div class="card-header d-flex justify-content-between align-items-center">
				<strong><?php vv_e( 'Availability periods' ); ?></strong>
				<button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#manage2026AddPeriod"><?php vv_e( 'Add period' ); ?></button>
			</div>
			<div class="card-body p-0">
				<?php if ( empty( $timeline ) ) { ?>
					<p class="text-muted small p-3 mb-0"><?php vv_e( 'No bookings or blocked periods yet.' ); ?></p>
				<?php } else { ?>
					<div class="table-responsive">
						<table class="table table-sm mb-0 vv-manage2026-period-table">
							<thead>
								<tr>
									<th><?php vv_e( 'Type' ); ?></th>
									<th><?php vv_e( 'Dates' ); ?></th>
									<th><?php vv_e( 'Guest / note' ); ?></th>
									<th><?php vv_e( 'Conversion' ); ?></th>
									<th></th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ( $timeline as $row ) {
									$editable = ! empty( $row['editable'] );
									$pid      = intval( gArrayItem( $row, 'period_id' ) );
									$row_id   = gArrayItem( $row, 'id' );
									$type_cls = 'vv-period-type--' . esc_attr( gArrayItem( $row, 'type' ) );
									$conv_id  = intval( gArrayItem( $row, 'conversion_id' ) );
									?>
									<tr>
										<td><span class="vv-manage2026-period-badge <?php echo esc_attr( $type_cls ); ?>"><?php echo esc_html( gArrayItem( $row, 'type_label' ) ); ?></span></td>
										<td>
											<?php echo esc_html( gArrayItem( $row, 'start_date' ) ); ?>
											&rarr;
											<?php echo esc_html( gArrayItem( $row, 'end_date' ) ); ?>
										</td>
										<td class="small">
											<?php
											$guest = trim( gArrayItem( $row, 'guest_name' ) );
											$note  = trim( gArrayItem( $row, 'note' ) );
											if ( $guest !== '' ) {
												echo esc_html( $guest );
											}
											if ( $note !== '' ) {
												echo $guest !== '' ? '<br>' : '';
												echo esc_html( $note );
											}
											if ( $guest === '' && $note === '' ) {
												echo '—';
											}
											?>
										</td>
										<td class="small">
											<?php if ( $conv_id > 0 ) { ?>
												<code><?php echo esc_html( gArrayItem( $row, 'conversion_ref' ) ); ?></code><br>
												<span class="text-muted"><?php echo esc_html( vv__( gArrayItem( $conv_status_labels, gArrayItem( $row, 'conversion_status' ), gArrayItem( $row, 'conversion_status' ) ) ) ); ?></span>
											<?php } elseif ( gArrayItem( $row, 'type' ) === 'external_airbnb' ) { ?>
												<span class="text-muted">—</span>
											<?php } else {
												echo '—';
											} ?>
										</td>
										<td class="text-right text-nowrap">
											<button type="button" class="btn btn-link btn-sm p-0 manage2026PeriodDetail" data-row-id="<?php echo esc_attr( $row_id ); ?>"><?php vv_e( 'Details' ); ?></button>
											<?php if ( $editable && $pid > 0 ) { ?>
												<form method="post" class="d-inline" onsubmit="return confirm('<?php echo esc_js( vv__( 'Remove this period?' ) ); ?>')">
													<input type="hidden" name="vv_action" value="vv_save_apartment_wizard_availability">
													<input type="hidden" name="apartment_id" value="<?php echo intval( $apartment_id ); ?>">
													<input type="hidden" name="availability_action" value="delete">
													<input type="hidden" name="period_id" value="<?php echo $pid; ?>">
													<button type="submit" class="btn btn-link btn-sm text-danger p-0 ml-1"><?php vv_e( 'Remove' ); ?></button>
												</form>
											<?php } ?>
										</td>
									</tr>
								<?php } ?>
							</tbody>
						</table>
					</div>
				<?php } ?>
			</div>
		</div>
	</div>

	<div class="col-lg-4">
		<div class="vv-manage2026-sidebar mb-3">
			<h6 class="mb-2"><?php vv_e( 'How availability works' ); ?></h6>
			<ul class="small pl-3 mb-3">
				<li><?php vv_e( 'Vietstays bookings sync automatically from confirmed reservations.' ); ?></li>
				<li><?php vv_e( 'Manual blocks prevent guests from booking those dates.' ); ?></li>
				<li><?php vv_e( 'External bookings block dates and can trigger a 10% conversion invite.' ); ?></li>
			</ul>
			<?php if ( $can_conversions ) { ?>
				<hr>
				<h6 class="mb-1"><?php vv_e( 'Conversion invites' ); ?></h6>
				<p class="small mb-2"><?php printf( esc_html( vv__( '%1$d / %2$d used this month' ) ), intval( $conversion_stats['used'] ), intval( $conversion_stats['limit'] ) ); ?></p>
				<a href="<?php echo esc_url( vv_admin_url( 'conversions?apartment_id=' . $apartment_id ) ); ?>" class="btn btn-sm btn-outline-secondary btn-block"><?php vv_e( 'All conversions' ); ?></a>
			<?php } ?>
		</div>
	</div>
</div>

<script type="application/json" id="manage2026TimelineData"><?php echo wp_json_encode( $timeline ); ?></script>

<div class="modal fade" id="manage2026PeriodDetail" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="manage2026DetailTitle"><?php vv_e( 'Period details' ); ?></h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="<?php echo esc_attr( vv__( 'Close' ) ); ?>"><span aria-hidden="true">&times;</span></button>
			</div>
			<div class="modal-body" id="manage2026DetailBody">
				<p class="text-muted mb-0"><?php vv_e( 'Loading…' ); ?></p>
			</div>
			<div class="modal-footer" id="manage2026DetailFooter"></div>
		</div>
	</div>
</div>

<div class="modal fade" id="manage2026AddPeriod" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<form method="post" class="modal-content">
			<input type="hidden" name="vv_action" value="vv_save_apartment_wizard_availability">
			<input type="hidden" name="apartment_id" value="<?php echo intval( $apartment_id ); ?>">
			<input type="hidden" name="availability_action" value="add">
			<div class="modal-header">
				<h5 class="modal-title"><?php vv_e( 'Add availability period' ); ?></h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="<?php echo esc_attr( vv__( 'Close' ) ); ?>"><span aria-hidden="true">&times;</span></button>
			</div>
			<div class="modal-body">
				<div class="form-group">
					<label><?php vv_e( 'Period type' ); ?></label>
					<select name="period_type" id="manage2026PeriodType" class="form-control form-control-sm">
						<option value="manual_block"><?php vv_e( 'Manual block' ); ?></option>
						<option value="external_airbnb"><?php vv_e( 'External booking' ); ?></option>
					</select>
				</div>
				<div class="row">
					<div class="col-6 form-group">
						<label><?php vv_e( 'Start date' ); ?></label>
						<input type="date" name="start_date" class="form-control form-control-sm" required>
					</div>
					<div class="col-6 form-group">
						<label><?php vv_e( 'End date' ); ?></label>
						<input type="date" name="end_date" class="form-control form-control-sm" required>
					</div>
				</div>
				<div class="manage2026ExternalFields" style="display:none">
					<div class="form-group">
						<label><?php vv_e( 'Platform' ); ?></label>
						<select name="external_platform" class="form-control form-control-sm">
							<?php foreach ( $platforms as $pkey => $plabel ) { ?>
								<option value="<?php echo esc_attr( $pkey ); ?>"><?php echo esc_html( $plabel ); ?></option>
							<?php } ?>
						</select>
					</div>
					<div class="form-group">
						<label><?php vv_e( 'External booking ref' ); ?> <span class="text-muted"><?php vv_e( 'optional' ); ?></span></label>
						<input type="text" name="external_booking_ref" class="form-control form-control-sm" maxlength="120" placeholder="<?php echo esc_attr( vv__( 'Confirmation code' ) ); ?>">
					</div>
					<div class="row">
						<div class="col-6 form-group">
							<label><?php vv_e( 'Guest name' ); ?> <span class="text-muted"><?php vv_e( 'optional' ); ?></span></label>
							<input type="text" name="guest_name" class="form-control form-control-sm" maxlength="120">
						</div>
						<div class="col-6 form-group">
							<label><?php vv_e( 'Guest email' ); ?> <span class="manage2026ConvRequired text-danger" style="display:none">*</span></label>
							<input type="email" name="guest_email" id="manage2026GuestEmail" class="form-control form-control-sm">
						</div>
					</div>
					<?php if ( $can_conversions ) { ?>
					<div class="border rounded p-2 mb-2 bg-light">
						<label class="small mb-1"><input type="checkbox" name="create_conversion" value="1" id="manage2026CreateConversion" checked> <?php vv_e( 'Create conversion invite (10% discount)' ); ?></label>
						<label class="small d-block mb-0 ml-3"><input type="checkbox" name="send_conversion_invite" value="1" id="manage2026SendConversion" checked> <?php vv_e( 'Send invite email immediately' ); ?></label>
						<p class="small text-muted mb-0 mt-1 ml-3"><?php printf( esc_html( vv__( '%d invites remaining this month' ) ), intval( $conversion_stats['remaining'] ) ); ?></p>
					</div>
					<?php } ?>
				</div>
				<div class="form-group mb-0">
					<label><?php vv_e( 'Note' ); ?> <span class="text-muted"><?php vv_e( 'optional' ); ?></span></label>
					<textarea name="period_note" class="form-control form-control-sm" rows="2"></textarea>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal"><?php vv_e( 'Cancel' ); ?></button>
				<button type="submit" class="btn btn-primary btn-sm"><?php vv_e( 'Add period' ); ?></button>
			</div>
		</form>
	</div>
</div>
