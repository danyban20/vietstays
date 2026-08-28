<?php
$all_locales      = vvI18n::supported_locales();
$uploadable       = vvI18n::get_uploadable_locales();
$uploadable_keys  = array_keys( $uploadable );
$requested_locale = GET_Request( 'locale' );

if ( $requested_locale !== '' && vvI18n::is_supported( vvI18n::normalize_locale( $requested_locale ) ) ) {
	$manage_locale = vvI18n::normalize_locale( $requested_locale );
} else {
	$manage_locale = $uploadable_keys ? $uploadable_keys[0] : 'no';
}

$translation_locale = ( $manage_locale === vvI18n::default_locale() )
	? ( $uploadable_keys ? $uploadable_keys[0] : 'no' )
	: $manage_locale;
$selected_info = vvI18n::get_locale_info( $manage_locale );
$preview_strings = [
	'Dashboard',
	'Booking',
	'Apartments',
	'Account Settings',
	'Language',
	'Save',
	'Save profile',
];
$active_slug = vvI18n::get_active_slug();
?>

<h1><?php vv_e( 'Language Files' ); ?></h1>

<div class="row">
	<div class="col-lg-5">
		<div class="card mb-4">
			<div class="card-header"><h4 class="mb-0"><?php vv_e( 'Installed languages' ); ?></h4></div>
			<div class="card-body p-0">
				<table class="table table-sm mb-0">
					<thead>
						<tr>
							<th><?php vv_e( 'Language' ); ?></th>
							<th><?php vv_e( 'Strings loaded' ); ?></th>
							<th></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $all_locales as $slug => $info ) {
							$file_info   = vvI18n::get_language_file_info( $slug );
							$is_default  = ( $slug === vvI18n::default_locale() );
							$is_selected = ( $slug === $manage_locale );
							?>
							<tr<?php echo $is_selected ? ' class="table-active"' : ''; ?>>
								<td>
									<strong><?php echo esc_html( gArrayItem( $info, 'label' ) ); ?></strong>
									<div class="text-muted small">
										<?php echo esc_html( strtoupper( $slug ) ); ?>
										<?php if ( $is_default ) { ?>
											<span class="badge badge-secondary ml-1"><?php vv_e( 'Default' ); ?></span>
										<?php } ?>
									</div>
								</td>
								<td>
									<?php if ( $is_default ) { ?>
										<span class="text-muted"><?php vv_e( 'Source language' ); ?></span>
									<?php } elseif ( $file_info['exists'] ) { ?>
										<?php echo intval( $file_info['strings'] ); ?>
										<div class="text-muted small"><?php echo esc_html( $file_info['modified'] ); ?></div>
									<?php } else { ?>
										<span class="text-muted">—</span>
									<?php } ?>
								</td>
								<td class="text-right">
									<a href="<?php echo esc_url( vv_admin_url( 'settings/languages' ) . '?locale=' . rawurlencode( $slug ) ); ?>" class="btn btn-sm btn-outline-secondary"><?php echo $is_default ? esc_html( vv__( 'Settings' ) ) : esc_html( vv__( 'Manage' ) ); ?></a>
								</td>
							</tr>
						<?php } ?>
					</tbody>
				</table>
			</div>
		</div>

		<div class="card mb-4">
			<div class="card-header"><h4 class="mb-0"><?php vv_e( 'Add language' ); ?></h4></div>
			<div class="card-body">
				<p class="text-muted small"><?php vv_e( 'Create a new language, set its display name, then upload or paste translations.' ); ?></p>
				<form method="post">
					<input type="hidden" name="vv_action" value="add_language">

					<div class="form-group">
						<label><?php vv_e( 'Language code' ); ?></label>
						<input type="text" name="language_code" class="form-control form-control-sm" maxlength="10" pattern="[a-z0-9_]{2,10}" placeholder="fr" required>
						<small class="text-muted"><?php vv_e( '2–10 lowercase letters or numbers, e.g. fr, de, ja' ); ?></small>
					</div>

					<div class="form-group">
						<label><?php vv_e( 'Display name' ); ?></label>
						<input type="text" name="language_label" class="form-control form-control-sm" maxlength="80" placeholder="French" required>
					</div>

					<div class="form-row">
						<div class="form-group col-md-6">
							<label><?php vv_e( 'Short code' ); ?></label>
							<input type="text" name="language_short" class="form-control form-control-sm" maxlength="5" placeholder="FR">
							<small class="text-muted"><?php vv_e( 'Optional. Shown in tabs and switchers.' ); ?></small>
						</div>
						<div class="form-group col-md-6">
							<label><?php vv_e( 'WordPress locale' ); ?></label>
							<input type="text" name="language_wp_locale" class="form-control form-control-sm" maxlength="20" placeholder="fr_FR">
							<small class="text-muted"><?php vv_e( 'Optional. Used for date/number formatting.' ); ?></small>
						</div>
					</div>

					<button type="submit" class="btn btn-primary btn-sm"><?php vv_e( 'Add language' ); ?></button>
				</form>
			</div>
		</div>

		<div class="card mb-4">
			<div class="card-header"><h4 class="mb-0"><?php vv_e( 'Current portal language' ); ?></h4></div>
			<div class="card-body">
				<p class="mb-2"><strong><?php echo esc_html( vvI18n::locale_label( $active_slug ) ); ?></strong> <span class="badge badge-info"><?php echo esc_html( strtoupper( $active_slug ) ); ?></span></p>
				<p class="text-muted small mb-0"><?php vv_e( 'Change your personal language under Account Settings, then reload the page to verify translations.' ); ?></p>
			</div>
		</div>
	</div>

	<div class="col-lg-7">
		<?php if ( is_array( $selected_info ) ) { ?>
		<div class="card mb-4">
			<div class="card-header"><h4 class="mb-0"><?php vv_e( 'Language settings' ); ?> — <?php echo esc_html( gArrayItem( $selected_info, 'label' ) ); ?></h4></div>
			<div class="card-body">
				<form method="post" class="mb-0">
					<input type="hidden" name="vv_action" value="save_language_locale">
					<input type="hidden" name="locale" value="<?php echo esc_attr( $manage_locale ); ?>">

					<div class="form-row">
						<div class="form-group col-md-4">
							<label><?php vv_e( 'Language code' ); ?></label>
							<input type="text" class="form-control form-control-sm" value="<?php echo esc_attr( strtoupper( $manage_locale ) ); ?>" readonly>
							<small class="text-muted"><?php vv_e( 'Cannot be changed after creation.' ); ?></small>
						</div>
						<div class="form-group col-md-8">
							<label><?php vv_e( 'Display name' ); ?></label>
							<input type="text" name="language_label" class="form-control form-control-sm" maxlength="80" value="<?php echo esc_attr( gArrayItem( $selected_info, 'label' ) ); ?>" required>
						</div>
					</div>

					<div class="form-row">
						<div class="form-group col-md-6">
							<label><?php vv_e( 'Short code' ); ?></label>
							<input type="text" name="language_short" class="form-control form-control-sm" maxlength="5" value="<?php echo esc_attr( gArrayItem( $selected_info, 'short' ) ); ?>">
						</div>
						<div class="form-group col-md-6">
							<label><?php vv_e( 'WordPress locale' ); ?></label>
							<input type="text" name="language_wp_locale" class="form-control form-control-sm" maxlength="20" value="<?php echo esc_attr( gArrayItem( $selected_info, 'locale' ) ); ?>">
						</div>
					</div>

					<div class="d-flex flex-wrap align-items-center">
						<button type="submit" class="btn btn-primary btn-sm mr-2"><?php vv_e( 'Save language' ); ?></button>
						<?php if ( ! vvI18n::is_protected_locale( $manage_locale ) ) { ?>
							<button type="submit" form="vv-delete-language-form" class="btn btn-outline-danger btn-sm" onclick="return confirm('<?php echo esc_js( vv__( 'Delete this language and its translation files?' ) ); ?>');"><?php vv_e( 'Delete language' ); ?></button>
						<?php } ?>
					</div>
				</form>

				<?php if ( ! vvI18n::is_protected_locale( $manage_locale ) ) { ?>
				<form id="vv-delete-language-form" method="post" class="d-none">
					<input type="hidden" name="vv_action" value="delete_language">
					<input type="hidden" name="locale" value="<?php echo esc_attr( $manage_locale ); ?>">
				</form>
				<?php } ?>
			</div>
		</div>
		<?php } ?>

		<?php if ( $manage_locale !== vvI18n::default_locale() ) { ?>

		<div class="card mb-4">
			<div class="card-header"><h4 class="mb-0"><?php vv_e( 'Upload translation file' ); ?> — <?php echo esc_html( vvI18n::locale_label( $translation_locale ) ); ?></h4></div>
			<div class="card-body">
				<p class="text-muted small"><?php vv_e( 'Upload a JSON file or paste JSON below. Keys must be English source strings; values are the translated text.' ); ?></p>

				<form method="post" enctype="multipart/form-data">
					<input type="hidden" name="vv_action" value="upload_language_file">
					<input type="hidden" name="locale" value="<?php echo esc_attr( $translation_locale ); ?>">

					<div class="form-group">
						<label><?php vv_e( 'Language' ); ?></label>
						<select name="locale" class="form-control form-control-sm" style="max-width:280px;" onchange="window.location='<?php echo esc_js( vv_admin_url( 'settings/languages' ) ); ?>?locale='+this.value;">
							<?php foreach ( $uploadable as $slug => $info ) { ?>
								<option value="<?php echo esc_attr( $slug ); ?>"<?php selected( $translation_locale, $slug ); ?>><?php echo esc_html( gArrayItem( $info, 'label' ) ); ?></option>
							<?php } ?>
						</select>
					</div>

					<div class="form-group">
						<label><?php vv_e( 'JSON file' ); ?></label>
						<input type="file" name="language_file" class="form-control-file" accept=".json,.php,application/json,text/plain">
						<small class="text-muted"><?php vv_e( 'Accepted: .json (recommended) or .php language arrays.' ); ?></small>
					</div>

					<div class="form-group">
						<label><?php vv_e( 'Or paste JSON' ); ?></label>
						<textarea name="language_json" class="form-control form-control-sm" rows="8" placeholder='{"Dashboard":"Pangunahing Panel","Save":"I-save"}'><?php
						if ( vvI18n::language_file_exists( $translation_locale ) ) {
							echo esc_textarea( wp_json_encode( vvI18n::get_translation_map( $translation_locale ), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE ) );
						}
						?></textarea>
					</div>

					<div class="d-flex flex-wrap align-items-center">
						<button type="submit" class="btn btn-primary btn-sm mr-2"><?php vv_e( 'Save translations' ); ?></button>
						<a class="btn btn-outline-secondary btn-sm mr-2" href="<?php echo esc_url( vv_admin_url( '?vv_action=download_language_file&locale=' . rawurlencode( $translation_locale ) ) ); ?>"><?php vv_e( 'Download JSON' ); ?></a>
						<a class="btn btn-outline-secondary btn-sm" href="<?php echo esc_url( vv_admin_url( '?vv_action=download_language_template' ) ); ?>"><?php vv_e( 'Download template' ); ?></a>
					</div>
				</form>
			</div>
		</div>

		<div class="card mb-4">
			<div class="card-header"><h4 class="mb-0"><?php vv_e( 'Translation preview' ); ?> — <?php echo esc_html( vvI18n::locale_label( $translation_locale ) ); ?></h4></div>
			<div class="card-body p-0">
				<table class="table table-sm mb-0">
					<thead>
						<tr>
							<th>English</th>
							<th><?php echo esc_html( vvI18n::locale_label( $translation_locale ) ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php
						$preview_map = vvI18n::get_translation_map( $translation_locale );
						foreach ( $preview_strings as $source ) {
							$translation = gArrayItem( $preview_map, $source );
							if ( $translation === '' ) {
								$translation = '—';
							}
							?>
							<tr>
								<td><code><?php echo esc_html( $source ); ?></code></td>
								<td><?php echo esc_html( $translation ); ?></td>
							</tr>
						<?php } ?>
					</tbody>
				</table>
			</div>
		</div>
		<?php } ?>
	</div>
</div>
