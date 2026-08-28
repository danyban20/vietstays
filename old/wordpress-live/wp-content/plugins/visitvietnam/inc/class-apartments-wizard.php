<?php

/**
 * Host apartment wizard (2026) + manage page — separate from legacy apartment form.
 */
class vvApartmentsWizard {

	const WIZARD_META_KEY = 'vv_wizard_2026';
	const TABLE_AVAILABILITY_PERIODS = 'vv_apartment_availability_periods';
	const TABLE_BUILDING_REQUESTS    = 'vv_building_requests';

	public function __construct() {
		add_action( 'init', [ $this, 'maybe_create_tables' ], 5 );
		add_action( 'init', [ $this, 'maybe_upgrade_columns' ], 7 );
		add_action( 'init', [ $this, 'maybe_upgrade_building_request_columns' ], 8 );
		add_action( 'init', [ $this, 'maybe_upgrade_availability_period_columns' ], 9 );
	}

	public function maybe_upgrade_availability_period_columns() {
		global $wpdb;

		$table = self::TABLE_AVAILABILITY_PERIODS;
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) !== $table ) {
			return;
		}

		$columns = $wpdb->get_col( "DESCRIBE {$table}", 0 );
		if ( ! is_array( $columns ) ) {
			return;
		}

		$add = [
			'guest_email'          => "ADD COLUMN guest_email varchar(255) DEFAULT NULL AFTER guest_name",
			'external_platform'    => "ADD COLUMN external_platform varchar(32) DEFAULT NULL AFTER source",
			'external_booking_ref' => "ADD COLUMN external_booking_ref varchar(128) DEFAULT NULL AFTER external_platform",
			'conversion_id'        => "ADD COLUMN conversion_id bigint(20) unsigned DEFAULT NULL AFTER external_booking_ref",
		];

		foreach ( $add as $col => $sql_part ) {
			if ( ! in_array( $col, $columns, true ) ) {
				$wpdb->query( "ALTER TABLE {$table} {$sql_part}" );
			}
		}
	}

	public function maybe_upgrade_building_request_columns() {
		global $wpdb;

		$table = self::TABLE_BUILDING_REQUESTS;
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) !== $table ) {
			return;
		}

		$columns = $wpdb->get_col( "DESCRIBE {$table}", 0 );
		if ( ! is_array( $columns ) ) {
			return;
		}

		$add = [
			'admin_note'            => "ADD COLUMN admin_note text DEFAULT NULL AFTER notes",
			'resolved_building_id'  => "ADD COLUMN resolved_building_id bigint(20) unsigned DEFAULT NULL AFTER district_id",
		];

		foreach ( $add as $col => $sql_part ) {
			if ( ! in_array( $col, $columns, true ) ) {
				$wpdb->query( "ALTER TABLE {$table} {$sql_part}" );
			}
		}
	}

	public function maybe_create_tables() {
		global $wpdb;

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$charset = $wpdb->get_charset_collate();

		dbDelta(
			"CREATE TABLE " . self::TABLE_AVAILABILITY_PERIODS . " (
				ID bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				apartment_id bigint(20) unsigned NOT NULL,
				period_type varchar(32) NOT NULL DEFAULT 'manual_block',
				start_date date NOT NULL,
				end_date date NOT NULL,
				note text DEFAULT NULL,
				guest_name varchar(191) DEFAULT NULL,
				guest_email varchar(255) DEFAULT NULL,
				source varchar(64) DEFAULT NULL,
				external_platform varchar(32) DEFAULT NULL,
				external_booking_ref varchar(128) DEFAULT NULL,
				conversion_id bigint(20) unsigned DEFAULT NULL,
				dateadded datetime DEFAULT NULL,
				datemodified datetime DEFAULT NULL,
				PRIMARY KEY (ID),
				KEY apartment_id (apartment_id),
				KEY period_dates (start_date, end_date)
			) {$charset};"
		);

		dbDelta(
			"CREATE TABLE " . self::TABLE_BUILDING_REQUESTS . " (
				ID bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				user_id bigint(20) unsigned NOT NULL,
				apartment_id bigint(20) unsigned DEFAULT NULL,
				city_id bigint(20) unsigned DEFAULT NULL,
				district_id bigint(20) unsigned DEFAULT NULL,
				building_name varchar(255) NOT NULL,
				address text DEFAULT NULL,
				notes text DEFAULT NULL,
				status varchar(32) NOT NULL DEFAULT 'pending',
				dateadded datetime DEFAULT NULL,
				datemodified datetime DEFAULT NULL,
				PRIMARY KEY (ID),
				KEY user_id (user_id),
				KEY status (status)
			) {$charset};"
		);
	}

	public function maybe_upgrade_columns() {
		global $wpdb;

		$columns = $wpdb->get_col( 'DESCRIBE vv_apartments', 0 );
		if ( ! is_array( $columns ) ) {
			return;
		}

		$add = [
			'quality_standard'      => "ADD COLUMN quality_standard varchar(32) DEFAULT NULL AFTER price_level",
			'building_gallery_json' => "ADD COLUMN building_gallery_json longtext DEFAULT NULL AFTER quality_standard",
			'rejection_reason'      => "ADD COLUMN rejection_reason text DEFAULT NULL AFTER status",
			'admin_review_note'     => "ADD COLUMN admin_review_note text DEFAULT NULL AFTER rejection_reason",
		];

		foreach ( $add as $col => $sql_part ) {
			if ( ! in_array( $col, $columns, true ) ) {
				$wpdb->query( "ALTER TABLE vv_apartments {$sql_part}" );
			}
		}
	}

	public function post_actions( $action = '' ) {
		if ( $action === 'vv_save_apartment_wizard_step' ) {
			$this->save_wizard_step();
		} elseif ( $action === 'vv_save_apartment_wizard_finalize' ) {
			$this->finalize_wizard();
		} elseif ( $action === 'vv_save_apartment_wizard_manage' ) {
			$this->save_manage_page();
		} elseif ( $action === 'vv_save_apartment_wizard_images' ) {
			$this->save_manage_images();
		} elseif ( $action === 'vv_save_apartment_wizard_facilities' ) {
			$this->save_manage_facilities();
		} elseif ( $action === 'vv_submit_apartment_wizard' ) {
			$this->submit_for_approval();
		} elseif ( $action === 'vv_save_apartment_wizard_availability' ) {
			$this->save_manage_availability();
		} elseif ( $action === 'vv_submit_building_request' ) {
			$this->submit_building_request();
		} elseif ( $action === 'vv_reject_apartment_post' ) {
			$this->reject_apartment_post();
		} elseif ( $action === 'vv_update_building_request' ) {
			$this->update_building_request();
		} elseif ( $action === 'vv_create_conversion_from_period' ) {
			$this->create_conversion_from_period();
		}
	}

	public function get_actions( $action = '' ) {
		if ( $action === 'apt_wizard_images_html' && GET_Request( 'id' ) > 0 ) {
			$this->render_images_html( intval( GET_Request( 'id' ) ) );
			die();
		} elseif ( $action === 'apt_wizard_building_gallery' && GET_Request( 'building_id' ) > 0 ) {
			header( 'Content-Type: application/json; charset=utf-8' );
			echo wp_json_encode( self::get_building_gallery_images( intval( GET_Request( 'building_id' ) ) ) );
			die();
		} elseif ( $action === 'apt_wizard_location_cascade' ) {
			header( 'Content-Type: application/json; charset=utf-8' );
			echo wp_json_encode( self::get_location_cascade_json() );
			die();
		} elseif ( $action === 'vv_approve_apartment' && GET_Request( 'id' ) > 0 ) {
			$this->approve_apartment( intval( GET_Request( 'id' ) ) );
		} elseif ( $action === 'vv_reject_apartment' && GET_Request( 'id' ) > 0 ) {
			$this->reject_apartment( intval( GET_Request( 'id' ) ) );
		}
	}

	public static function quality_standards() {
		return [
			'standard'       => [ 'label' => 'Standard', 'price_level' => 'low' ],
			'good'           => [ 'label' => 'Good standard', 'price_level' => 'low' ],
			'above_average'  => [ 'label' => 'Above average', 'price_level' => 'normal' ],
			'high'           => [ 'label' => 'High standard', 'price_level' => 'normal' ],
			'premium'        => [ 'label' => 'Premium', 'price_level' => 'high' ],
			'top'            => [ 'label' => 'Top standard', 'price_level' => 'high' ],
		];
	}

	/** Three-tier selector shown in the 2026 wizard (matches Nav v3 mockup). */
	public static function quality_tiers() {
		return [
			'standard' => [
				'label'       => 'Standard',
				'sublabel'    => 'Good standard',
				'price_level' => 'low',
			],
			'above_average' => [
				'label'       => 'Above average',
				'sublabel'    => 'High standard',
				'price_level' => 'normal',
			],
			'premium' => [
				'label'       => 'Premium',
				'sublabel'    => 'Top standard',
				'price_level' => 'high',
			],
		];
	}

	public static function apartment_types() {
		return [
			'Studio' => 'Studio',
			'1BR'    => '1BR',
			'2BR'    => '2BR',
			'3BR'    => '3BR',
			'4BR'    => '4BR',
		];
	}

	public static function map_quality_to_price_level( $quality ) {
		$tiers = self::quality_tiers();
		if ( isset( $tiers[ $quality ] ) ) {
			return $tiers[ $quality ]['price_level'];
		}
		$standards = self::quality_standards();
		if ( isset( $standards[ $quality ] ) ) {
			return $standards[ $quality ]['price_level'];
		}
		return 'normal';
	}

	public static function rooms_from_type( $type ) {
		$map = [ 'Studio' => 1, '1BR' => 1, '2BR' => 2, '3BR' => 3, '4BR' => 4 ];
		return isset( $map[ $type ] ) ? $map[ $type ] : 1;
	}

	public static function get_building_gallery_images( $building_id ) {
		$items = vv_get_building_gallery_items( $building_id, true );
		$out   = [];
		foreach ( $items as $item ) {
			$out[] = [
				'id'         => intval( gArrayItem( $item, 'id' ) ),
				'url'        => gArrayItem( $item, 'url' ),
				'label'      => gArrayItem( $item, 'label', 'Building' ),
				'host_count' => intval( gArrayItem( $item, 'host_count', 0 ) ),
			];
		}
		return $out;
	}

	/** Selected building gallery items saved on the apartment (up to 3). */
	public static function get_selected_building_gallery_items( $apartment ) {
		$ids = json_decode( gArrayItem( $apartment, 'building_gallery_json' ), true );
		if ( ! is_array( $ids ) || empty( $ids ) ) {
			return [];
		}

		$building_id = intval( gArrayItem( $apartment, 'building_id' ) );
		if ( $building_id <= 0 ) {
			return [];
		}

		$by_id = [];
		foreach ( self::get_building_gallery_images( $building_id ) as $item ) {
			$by_id[ intval( $item['id'] ) ] = $item;
		}

		$out = [];
		foreach ( $ids as $id ) {
			$id = intval( $id );
			if ( $id > 0 && isset( $by_id[ $id ] ) ) {
				$out[] = $by_id[ $id ];
			}
		}

		return $out;
	}

	public static function can_access_apartment( $apartment ) {
		global $logged_user_role;

		if ( $logged_user_role === 'administrator' ) {
			return true;
		}
		if ( $logged_user_role === 'partner' && intval( gArrayItem( $apartment, 'user_id' ) ) === get_current_user_id() ) {
			return true;
		}
		return false;
	}

	public static function can_use_wizard() {
		global $logged_user_role;
		return in_array( $logged_user_role, [ 'administrator', 'partner' ], true );
	}

	public static function can_publish_apartment() {
		return vv_is_admin_role();
	}

	protected function save_wizard_step() {
		global $wpdb;

		if ( ! self::can_use_wizard() ) {
			setErrorMsg( 'Permission denied.' );
			wp_redirect( vv_admin_url( 'apartments' ) );
			die();
		}

		$step         = intval( POST_Request( 'wizard_step' ) );
		$apartment_id = intval( POST_Request( 'apartment_id' ) );

		if ( $step === 1 ) {
			$apartment_id = $this->save_step_one( $apartment_id );
			wp_redirect( vv_admin_url( 'apartments/add?step=2&id=' . $apartment_id ) );
			die();
		}

		if ( $apartment_id <= 0 ) {
			wp_redirect( vv_admin_url( 'apartments/add?step=1' ) );
			die();
		}

		$apartment = vv_get_apartment( $apartment_id );
		if ( ! self::can_access_apartment( $apartment ) ) {
			setErrorMsg( 'Permission denied.' );
			wp_redirect( vv_admin_url( 'apartments' ) );
			die();
		}

		if ( $step === 2 ) {
			$this->save_step_two( $apartment_id );
			wp_redirect( vv_admin_url( 'apartments/add?step=3&id=' . $apartment_id ) );
			die();
		}

		if ( $step === 3 ) {
			$this->save_step_three( $apartment_id );
			wp_redirect( vv_admin_url( 'apartments/add?step=4&id=' . $apartment_id ) );
			die();
		}

		wp_redirect( vv_admin_url( 'apartments/add?step=1' ) );
		die();
	}

	protected function save_step_one( $apartment_id ) {
		global $wpdb;

		$building_id            = intval( POST_Request( 'building_id' ) );
		$apartment_type         = sanitize_text_field( POST_Request( 'apartment_type' ) );
		$distinguishing_feature = sanitize_text_field( POST_Request( 'distinguishing_feature' ) );
		$name                   = sanitize_text_field( POST_Request( 'name' ) );
		$room_number            = sanitize_text_field( POST_Request( 'room_number' ) );
		$quality_standard       = sanitize_key( POST_Request( 'quality_standard' ) );
		$about_short            = '';

		$i18n_short_post = POST_Request( 'i18n_short' );
		if ( is_array( $i18n_short_post ) ) {
			$default_locale = class_exists( 'vvI18n' ) ? vvI18n::default_locale() : 'en';
			foreach ( $i18n_short_post as $locale => $text ) {
				$locale = sanitize_key( $locale );
				$text   = wp_kses_post( $text );
				if ( $locale === $default_locale ) {
					$about_short = $text;
				}
			}
		}
		if ( $about_short === '' ) {
			$about_short = wp_kses_post( POST_Request( 'about_this_short' ) );
		}

		$building = vv_get_neighbourhood( $building_id );
		$district = intval( gArrayItem( $building, 'post_parent' ) );

		if ( $building_id <= 0 || $apartment_type === '' || $distinguishing_feature === '' ) {
			setErrorMsg( 'Please fill in building, apartment type, and distinguishing feature.' );
			wp_redirect( vv_admin_url( 'apartments/add?step=1' . ( $apartment_id ? '&id=' . $apartment_id : '' ) ) );
			die();
		}

		if ( $name === '' && class_exists( 'vvApartmentPlatform' ) ) {
			$building_name  = gArrayItem( $building, 'post_title' );
			$district_label = vv_get_district_display_name( intval( gArrayItem( $building, 'post_parent' ) ) );
			if ( $district_label === '' ) {
				$district_label = vv_get_neighbourhood_display_name( $building_id );
			}
			$name = vvApartmentPlatform::generate_apartment_name( $building_name, $distinguishing_feature, $district_label, $apartment_type );
		}

		$user_id = get_current_user_id();
		if ( vv_is_admin_role() && intval( POST_Request( 'user_id' ) ) > 0 ) {
			$user_id = intval( POST_Request( 'user_id' ) );
		}

		$price_level = self::map_quality_to_price_level( $quality_standard );
		$rooms       = self::rooms_from_type( $apartment_type );

		$suggested = 0;
		if ( class_exists( 'vvApartmentPlatform' ) ) {
			$suggested = vvApartmentPlatform::suggest_daily_price( $apartment_type, $price_level, $building_id );
		}

		$data = [
			'name'                   => $name,
			'display_name'           => $name,
			'building_id'            => $building_id,
			'district'               => $district,
			'distinguishing_feature' => $distinguishing_feature,
			'apartment_type'         => $apartment_type,
			'quality_standard'       => $quality_standard,
			'price_level'            => $price_level,
			'room_number'            => $room_number,
			'about_this_short'       => $about_short,
			'rooms'                  => $rooms,
			'user_id'                => $user_id,
			'status'                 => 'draft',
			'price_daily'            => $suggested > 0 ? $suggested : 0,
			'url_slug'               => vv_slugify( strtolower( $name ) ),
			'datemodified'           => current_time( 'mysql' ),
		];

		if ( $apartment_id > 0 ) {
			$wpdb->update( 'vv_apartments', $data, [ 'ID' => $apartment_id ] );
		} else {
			$data['dateadded'] = current_time( 'mysql' );
			$data['pricing']   = wp_json_encode( [] );
			$wpdb->insert( 'vv_apartments', $data );
			$apartment_id = intval( $wpdb->insert_id );
		}

		update_option( 'vv_apt_wizard_2026_' . $apartment_id, 1, false );

		if ( class_exists( 'vvI18n' ) && is_array( $i18n_short_post ) ) {
			$default_locale = vvI18n::default_locale();
			foreach ( $i18n_short_post as $locale => $text ) {
				$locale = sanitize_key( $locale );
				if ( $locale === $default_locale ) {
					continue;
				}
				vvI18n::save_apartment_i18n( $apartment_id, $locale, [ 'about_this_short' => wp_kses_post( $text ) ] );
			}
		}

		return $apartment_id;
	}

	protected function save_step_two( $apartment_id ) {
		global $wpdb;

		$images_url     = POST_Request( 'images_url' );
		$images_id      = POST_Request( 'images_id' );
		$images_caption = POST_Request( 'images_caption' );
		$building_picks = POST_Request( 'building_gallery_ids' );

		$images = [];
		if ( is_array( $images_url ) ) {
			for ( $i = 0; $i < count( $images_url ); $i++ ) {
				$images[] = [
					'order'    => $i + 1,
					'thumb'    => $images_url[ $i ],
					'image_id' => is_array( $images_id ) ? gArrayItem( $images_id, $i ) : '',
					'caption'  => is_array( $images_caption ) ? gArrayItem( $images_caption, $i ) : '',
				];
			}
		}

		$gallery_ids = [];
		if ( is_array( $building_picks ) ) {
			$gallery_ids = array_slice( array_values( array_filter( array_map( 'intval', $building_picks ) ) ), 0, 3 );
		}

		$wpdb->update(
			'vv_apartments',
			[
				'images'                => wp_json_encode( $images ),
				'building_gallery_json' => wp_json_encode( $gallery_ids ),
				'datemodified'          => current_time( 'mysql' ),
			],
			[ 'ID' => $apartment_id ]
		);
	}

	protected function save_step_three( $apartment_id ) {
		global $wpdb;

		$facilities = POST_Request( 'facilities' );
		if ( ! is_array( $facilities ) ) {
			$facilities = [];
		}

		$building_id = intval( $wpdb->get_var( $wpdb->prepare( 'SELECT building_id FROM vv_apartments WHERE ID = %d', $apartment_id ) ) );
		$building_facility_ids = [];
		if ( $building_id > 0 ) {
			$raw = get_post_meta( $building_id, 'facilities', true );
			if ( is_array( $raw ) ) {
				$building_facility_ids = array_map( 'intval', $raw );
			}
		}

		$merged = array_values( array_unique( array_merge( array_map( 'intval', $facilities ), $building_facility_ids ) ) );

		$wpdb->update(
			'vv_apartments',
			[
				'facilities'   => wp_json_encode( $merged ),
				'datemodified' => current_time( 'mysql' ),
			],
			[ 'ID' => $apartment_id ]
		);
	}

	protected function finalize_wizard() {
		global $wpdb;

		if ( ! self::can_use_wizard() ) {
			setErrorMsg( 'Permission denied.' );
			wp_redirect( vv_admin_url( 'apartments' ) );
			die();
		}

		$apartment_id = intval( POST_Request( 'apartment_id' ) );
		$apartment    = vv_get_apartment( $apartment_id );

		if ( ! self::can_access_apartment( $apartment ) ) {
			setErrorMsg( 'Permission denied.' );
			wp_redirect( vv_admin_url( 'apartments' ) );
			die();
		}

		$min_images = 10;
		if ( class_exists( 'vvApartmentPlatform' ) ) {
			$min_images = intval( vvApartmentPlatform::get_settings()['min_images_publish'] );
		}

		$image_count = vv_count_listing_photos( $apartment );
		if ( $image_count < $min_images ) {
			setErrorMsg( sprintf( 'Upload at least %d photos before creating the apartment (apartment + building photos count).', $min_images ) );
			wp_redirect( vv_admin_url( 'apartments/add?step=2&id=' . $apartment_id ) );
			die();
		}

		$cleaning_default = 35;
		if ( class_exists( 'vvApartmentPlatform' ) ) {
			$cleaning_default = vvApartmentPlatform::default_cleaning_fee( gArrayItem( $apartment, 'apartment_type', '2BR' ) );
		}

		$wpdb->update(
			'vv_apartments',
			[
				'status'               => 'pending',
				'cleaning_fee'         => $cleaning_default,
				'cleaning_fee_enabled' => 1,
				'datemodified'         => current_time( 'mysql' ),
			],
			[ 'ID' => $apartment_id ]
		);

		$apartments_class = new vvApartments();
		$apartments_class->update_apartment_number( $apartment_id );

		setSuccessMsg( 'Apartment created. Configure pricing and submit for approval.' );
		wp_redirect( vv_admin_url( 'apartments/manage?id=' . $apartment_id ) );
		die();
	}

	protected function save_manage_page() {
		global $wpdb;

		$apartment_id = intval( POST_Request( 'apartment_id' ) );
		$apartment    = vv_get_apartment( $apartment_id );
		$tab          = sanitize_key( POST_Request( 'manage_tab' ) );

		if ( ! self::can_access_apartment( $apartment ) ) {
			setErrorMsg( 'Permission denied.' );
			wp_redirect( vv_admin_url( 'apartments' ) );
			die();
		}

		$quality_standard = sanitize_key( POST_Request( 'quality_standard' ) );
		$price_level      = self::map_quality_to_price_level( $quality_standard );
		$pricing_model    = sanitize_text_field( POST_Request( 'pricing_model' ) );
		$status           = sanitize_text_field( POST_Request( 'status' ) );

		$pricing = json_decode( gArrayItem( $apartment, 'pricing' ), true );
		if ( ! is_array( $pricing ) ) {
			$pricing = [];
		}
		$pricing['discount_3days']  = POST_Request( 'discount_3days' );
		$pricing['discount_7days']  = POST_Request( 'discount_7days' );
		$pricing['discount_30days'] = POST_Request( 'discount_30days' );

		$seasonal_pricing = [];
		$seasonal_month   = POST_Request( 'seasonal_price' );
		if ( is_array( $seasonal_month ) ) {
			foreach ( $seasonal_month as $month => $price ) {
				$seasonal_pricing[ intval( $month ) ] = vv_currency_to_decimal( $price );
			}
		}

		$data = [
			'price_daily'          => vv_currency_to_decimal( POST_Request( 'price_daily' ) ),
			'quality_standard'     => $quality_standard,
			'price_level'          => $price_level,
			'pricing_model'        => in_array( $pricing_model, [ 'fixed', 'seasonal' ], true ) ? $pricing_model : 'fixed',
			'cleaning_fee'         => vv_currency_to_decimal( POST_Request( 'cleaning_fee' ) ),
			'cleaning_fee_enabled' => POST_Request( 'cleaning_fee_enabled' ) ? 1 : 0,
			'pricing'              => wp_json_encode( $pricing ),
			'seasonal_pricing'     => wp_json_encode( $seasonal_pricing ),
			'datemodified'         => current_time( 'mysql' ),
		];

		$data = $this->apply_status_change( $apartment, $data, $status );

		$wpdb->update( 'vv_apartments', $data, [ 'ID' => $apartment_id ] );

		$blocked_raw = POST_Request( 'blocked_dates' );
		if ( class_exists( 'vvApartmentPlatform' ) ) {
			$blocked_dates = [];
			if ( is_array( $blocked_raw ) ) {
				$blocked_dates = $blocked_raw;
			} elseif ( trim( (string) $blocked_raw ) !== '' ) {
				$blocked_dates = preg_split( '/[\s,]+/', trim( (string) $blocked_raw ) );
			}
			vvApartmentPlatform::save_blocked_dates( $apartment_id, $blocked_dates );
		}

		setSuccessMsg( 'Apartment updated.' );
		wp_redirect( vv_admin_url( 'apartments/manage?id=' . $apartment_id . ( $tab !== '' ? '&tab=' . $tab : '' ) ) );
		die();
	}

	protected function save_manage_images() {
		global $wpdb;

		$apartment_id = intval( POST_Request( 'apartment_id' ) );
		$apartment    = vv_get_apartment( $apartment_id );

		if ( ! self::can_access_apartment( $apartment ) ) {
			setErrorMsg( 'Permission denied.' );
			wp_redirect( vv_admin_url( 'apartments' ) );
			die();
		}

		$this->save_step_two( $apartment_id );
		setSuccessMsg( 'Photos saved.' );
		wp_redirect( vv_admin_url( 'apartments/manage?id=' . $apartment_id . '&tab=images' ) );
		die();
	}

	protected function save_manage_facilities() {
		global $wpdb;

		$apartment_id = intval( POST_Request( 'apartment_id' ) );
		$apartment    = vv_get_apartment( $apartment_id );

		if ( ! self::can_access_apartment( $apartment ) ) {
			setErrorMsg( 'Permission denied.' );
			wp_redirect( vv_admin_url( 'apartments' ) );
			die();
		}

		$this->save_step_three( $apartment_id );

		$house_rules_selected = POST_Request( 'house_rules_selected' );
		$house_rules_json     = [];
		$house_rules          = gArrayItem( $apartment, 'house_rules' );
		if ( is_array( $house_rules_selected ) ) {
			$house_rules_json = array_map( 'sanitize_key', $house_rules_selected );
			$labels           = [];
			if ( class_exists( 'vvApartmentPlatform' ) ) {
				foreach ( vvApartmentPlatform::get_house_rules() as $rule ) {
					if ( in_array( gArrayItem( $rule, 'id' ), $house_rules_json, true ) ) {
						$labels[] = gArrayItem( $rule, 'label' );
					}
				}
			}
			if ( ! empty( $labels ) ) {
				$house_rules = implode( "\n", $labels );
			}
		}

		$wpdb->update(
			'vv_apartments',
			[
				'house_rules_json' => wp_json_encode( $house_rules_json ),
				'house_rules'      => $house_rules,
				'check_in_time1'   => sanitize_text_field( POST_Request( 'check_in_time1' ) ),
				'check_in_time2'   => sanitize_text_field( POST_Request( 'check_in_time2' ) ),
				'features_description' => wp_kses_post( POST_Request( 'features_description' ) ),
				'property_safety'  => wp_kses_post( POST_Request( 'property_safety' ) ),
				'datemodified'     => current_time( 'mysql' ),
			],
			[ 'ID' => $apartment_id ]
		);

		setSuccessMsg( 'Facilities and house rules saved.' );
		wp_redirect( vv_admin_url( 'apartments/manage?id=' . $apartment_id . '&tab=facilities' ) );
		die();
	}

	protected function submit_for_approval() {
		global $wpdb;

		$apartment_id = intval( POST_Request( 'apartment_id' ) );
		$apartment    = vv_get_apartment( $apartment_id );

		if ( ! self::can_access_apartment( $apartment ) ) {
			setErrorMsg( 'Permission denied.' );
			wp_redirect( vv_admin_url( 'apartments' ) );
			die();
		}

		if ( class_exists( 'vvApartmentPlatform' ) ) {
			$check = vvApartmentPlatform::validate_publish( $apartment, 'active' );
			if ( is_wp_error( $check ) ) {
				setErrorMsg( $check->get_error_message() );
				wp_redirect( vv_admin_url( 'apartments/manage?id=' . $apartment_id ) );
				die();
			}
		}

		$wpdb->update(
			'vv_apartments',
			[
				'status'           => 'pending',
				'rejection_reason' => null,
				'datemodified'     => current_time( 'mysql' ),
			],
			[ 'ID' => $apartment_id ]
		);

		setSuccessMsg( 'Submitted for Vietstays approval.' );
		wp_redirect( vv_admin_url( 'apartments/manage?id=' . $apartment_id ) );
		die();
	}

	protected function approve_apartment( $apartment_id ) {
		global $wpdb;

		if ( ! self::can_publish_apartment() ) {
			setErrorMsg( 'Permission denied.' );
			wp_redirect( vv_admin_url( 'apartments' ) );
			die();
		}

		$apartment = vv_get_apartment( $apartment_id );
		if ( ! is_array( $apartment ) || intval( gArrayItem( $apartment, 'ID' ) ) <= 0 ) {
			setErrorMsg( 'Apartment not found.' );
			wp_redirect( vv_admin_url( 'apartments' ) );
			die();
		}

		if ( class_exists( 'vvApartmentPlatform' ) ) {
			$check = vvApartmentPlatform::validate_publish( $apartment, 'active' );
			if ( is_wp_error( $check ) ) {
				setErrorMsg( $check->get_error_message() );
				wp_redirect( vv_admin_url( 'apartments/manage?id=' . $apartment_id ) );
				die();
			}
		}

		$wpdb->update(
			'vv_apartments',
			[
				'status'            => 'active',
				'rejection_reason'  => null,
				'admin_review_note' => null,
				'datemodified'      => current_time( 'mysql' ),
			],
			[ 'ID' => $apartment_id ]
		);

		setSuccessMsg( 'Apartment published.' );
		wp_redirect( vv_admin_url( 'apartments/manage?id=' . $apartment_id ) );
		die();
	}

	protected function reject_apartment( $apartment_id, $reason = '', $admin_note = '' ) {
		global $wpdb;

		if ( ! self::can_publish_apartment() ) {
			setErrorMsg( 'Permission denied.' );
			wp_redirect( vv_admin_url( 'apartments' ) );
			die();
		}

		$apartment = vv_get_apartment( $apartment_id );
		if ( ! is_array( $apartment ) || intval( gArrayItem( $apartment, 'ID' ) ) <= 0 ) {
			setErrorMsg( 'Apartment not found.' );
			wp_redirect( vv_admin_url( 'apartments' ) );
			die();
		}

		$wpdb->update(
			'vv_apartments',
			[
				'status'             => 'draft',
				'rejection_reason'   => $reason !== '' ? wp_kses_post( $reason ) : null,
				'admin_review_note'  => $admin_note !== '' ? wp_kses_post( $admin_note ) : null,
				'datemodified'       => current_time( 'mysql' ),
			],
			[ 'ID' => $apartment_id ]
		);

		setSuccessMsg( 'Listing rejected and returned to draft.' );
		wp_redirect( vv_admin_url( 'apartments/manage?id=' . $apartment_id ) );
		die();
	}

	protected function reject_apartment_post() {
		$apartment_id = intval( POST_Request( 'apartment_id' ) );
		$reason       = sanitize_textarea_field( POST_Request( 'rejection_reason' ) );
		$admin_note   = sanitize_textarea_field( POST_Request( 'admin_review_note' ) );

		if ( $reason === '' ) {
			setErrorMsg( 'Please provide a rejection reason for the host.' );
			wp_redirect( vv_admin_url( 'apartments/manage?id=' . $apartment_id ) );
			die();
		}

		$this->reject_apartment( $apartment_id, $reason, $admin_note );
	}

	protected function apply_status_change( array $apartment, array $data, $requested_status ) {
		$allowed = self::can_publish_apartment()
			? [ 'draft', 'pending', 'active' ]
			: [ 'draft', 'pending' ];

		if ( ! in_array( $requested_status, $allowed, true ) ) {
			if ( $requested_status === 'active' && ! self::can_publish_apartment() ) {
				setErrorMsg( 'Only Vietstays admins can publish listings.' );
			}
			return $data;
		}

		if ( $requested_status === 'active' && class_exists( 'vvApartmentPlatform' ) ) {
			$check = vvApartmentPlatform::validate_publish( array_merge( $apartment, $data ), 'active' );
			if ( is_wp_error( $check ) ) {
				setErrorMsg( $check->get_error_message() );
				wp_redirect( vv_admin_url( 'apartments/manage?id=' . intval( gArrayItem( $apartment, 'ID' ) ) ) );
				die();
			}
		}

		$data['status'] = $requested_status;
		return $data;
	}

	public static function period_type_labels() {
		return [
			'manual_block'    => 'Manual block',
			'external_airbnb' => 'External booking',
			'vietstays'       => 'Vietstays booking',
		];
	}

	public static function external_platform_labels() {
		if ( class_exists( 'vvConversions' ) ) {
			return vvConversions::platforms();
		}
		return [
			'airbnb'  => 'Airbnb',
			'booking' => 'Booking.com',
			'trip'    => 'Trip.com',
			'other'   => 'Other',
		];
	}

	public static function get_availability_periods( $apartment_id ) {
		global $wpdb;

		$rows = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT * FROM ' . self::TABLE_AVAILABILITY_PERIODS . ' WHERE apartment_id = %d ORDER BY start_date ASC, ID ASC',
				intval( $apartment_id )
			),
			ARRAY_A
		);

		return is_array( $rows ) ? $rows : [];
	}

	public static function get_calendar_day_map( $apartment_id, $year, $month, $booking_class = null ) {
		$apartment_id = intval( $apartment_id );
		$year         = intval( $year );
		$month        = intval( $month );
		$days_in_month = intval( date( 't', mktime( 0, 0, 0, $month, 1, $year ) ) );
		$range_start  = sprintf( '%04d-%02d-01', $year, $month );
		$range_end    = sprintf( '%04d-%02d-%02d', $year, $month, $days_in_month );

		$map = [];
		for ( $d = 1; $d <= $days_in_month; $d++ ) {
			$map[ sprintf( '%04d-%02d-%02d', $year, $month, $d ) ] = '';
		}

		if ( class_exists( 'vvApartmentPlatform' ) ) {
			foreach ( vvApartmentPlatform::get_blocked_dates( $apartment_id ) as $block_date ) {
				if ( isset( $map[ $block_date ] ) ) {
					$map[ $block_date ] = 'blocked';
				}
			}
		}

		foreach ( self::get_availability_periods( $apartment_id ) as $period ) {
			$type = gArrayItem( $period, 'period_type', 'manual_block' );
			$cls  = $type === 'external_airbnb' ? 'external' : 'blocked';
			$start = gArrayItem( $period, 'start_date' );
			$end   = gArrayItem( $period, 'end_date' );
			foreach ( array_keys( $map ) as $date_str ) {
				if ( $date_str >= $start && $date_str <= $end ) {
					if ( $map[ $date_str ] === '' || $map[ $date_str ] === 'blocked' ) {
						$map[ $date_str ] = $cls;
					}
				}
			}
		}

		$bookings = [];
		if ( $booking_class && method_exists( $booking_class, 'get_bookings' ) ) {
			$bookings = $booking_class->get_bookings( [
				'apartment_ids' => [ $apartment_id ],
				'overlap_start' => $range_start,
				'overlap_end'   => $range_end,
			] );
		}

		foreach ( $bookings as $booking ) {
			$ci = gArrayItem( $booking, 'check_in_date' );
			$co = gArrayItem( $booking, 'check_out_date' );
			foreach ( array_keys( $map ) as $date_str ) {
				if ( $date_str >= $ci && $date_str < $co ) {
					$map[ $date_str ] = 'booked';
				}
			}
		}

		return $map;
	}

	public static function get_merged_availability_timeline( $apartment_id, $booking_class = null ) {
		$timeline = [];
		$labels   = self::period_type_labels();

		if ( $booking_class && method_exists( $booking_class, 'get_bookings' ) ) {
			$bookings = $booking_class->get_bookings( [
				'apartment_ids' => [ intval( $apartment_id ) ],
				'orderby'       => 'check_in_date ASC',
			] );
			foreach ( $bookings as $booking ) {
				$booking_id = intval( gArrayItem( $booking, 'ID' ) );
				$user_id    = intval( gArrayItem( $booking, 'user_id' ) );
				$guest_email = '';
				if ( $user_id > 0 ) {
					$user = get_user_by( 'ID', $user_id );
					if ( $user ) {
						$guest_email = $user->user_email;
					}
				}
				$ci = gArrayItem( $booking, 'check_in_date' );
				$co = gArrayItem( $booking, 'check_out_date' );
				$nights = 0;
				if ( $ci && $co ) {
					$nights = max( 0, intval( ( strtotime( $co ) - strtotime( $ci ) ) / DAY_IN_SECONDS ) );
				}
				$timeline[] = [
					'id'            => 'booking-' . $booking_id,
					'type'          => 'vietstays',
					'type_label'    => gArrayItem( $labels, 'vietstays', 'Vietstays booking' ),
					'start_date'    => $ci,
					'end_date'      => $co,
					'guest_name'    => trim( gArrayItem( $booking, 'guest_name' ) . ' ' . gArrayItem( $booking, 'guest_surname' ) ),
					'guest_email'   => $guest_email,
					'note'          => '',
					'editable'      => false,
					'source'        => 'vietstays',
					'booking_id'    => $booking_id,
					'booking_num'   => gArrayItem( $booking, 'booking_num' ),
					'booking_status'=> gArrayItem( $booking, 'status' ),
					'total'         => floatval( gArrayItem( $booking, 'total' ) ),
					'payment_method'=> gArrayItem( $booking, 'payment_method' ),
					'promo_code'    => gArrayItem( $booking, 'promo_code' ),
					'cleaning_fee'  => floatval( gArrayItem( $booking, 'cleaning_fee' ) ),
					'nights'        => $nights,
					'detail_url'    => vv_admin_url( 'booking/edit?id=' . $booking_id ),
				];
			}
		}

		foreach ( self::get_availability_periods( $apartment_id ) as $period ) {
			$type         = gArrayItem( $period, 'period_type', 'manual_block' );
			$platform     = gArrayItem( $period, 'external_platform' );
			if ( $platform === '' && gArrayItem( $period, 'source' ) !== '' ) {
				$platform = gArrayItem( $period, 'source' );
			}
			$platform_labels = self::external_platform_labels();
			$type_label = gArrayItem( $labels, $type, $type );
			if ( $type === 'external_airbnb' && isset( $platform_labels[ $platform ] ) ) {
				$type_label = sprintf( '%s (%s)', gArrayItem( $labels, 'external_airbnb', 'External booking' ), $platform_labels[ $platform ] );
			}
			$conversion_id = intval( gArrayItem( $period, 'conversion_id' ) );
			$conversion    = [];
			if ( $conversion_id > 0 && class_exists( 'vvConversions' ) ) {
				$conv_class = new vvConversions();
				$conversion = $conv_class->get_conversion( $conversion_id );
			}
			$timeline[] = [
				'id'                   => 'period-' . intval( gArrayItem( $period, 'ID' ) ),
				'type'                 => $type,
				'type_label'           => $type_label,
				'start_date'           => gArrayItem( $period, 'start_date' ),
				'end_date'             => gArrayItem( $period, 'end_date' ),
				'guest_name'           => gArrayItem( $period, 'guest_name' ),
				'guest_email'          => gArrayItem( $period, 'guest_email' ),
				'note'                 => gArrayItem( $period, 'note' ),
				'editable'             => true,
				'source'               => gArrayItem( $period, 'source' ),
				'period_id'            => intval( gArrayItem( $period, 'ID' ) ),
				'external_platform'    => $platform,
				'external_booking_ref' => gArrayItem( $period, 'external_booking_ref' ),
				'conversion_id'        => $conversion_id,
				'conversion_ref'       => gArrayItem( $conversion, 'conversion_ref' ),
				'conversion_status'    => gArrayItem( $conversion, 'status' ),
				'conversion_promo'     => gArrayItem( $conversion, 'promo_code' ),
				'conversion_expires'   => gArrayItem( $conversion, 'expires_at' ),
			];
		}

		usort(
			$timeline,
			function ( $a, $b ) {
				return strcmp( gArrayItem( $a, 'start_date' ), gArrayItem( $b, 'start_date' ) );
			}
		);

		return $timeline;
	}

	public static function get_listing_completion( $apartment ) {
		$apartment_id = intval( gArrayItem( $apartment, 'ID' ) );
		$min_images   = 10;
		if ( class_exists( 'vvApartmentPlatform' ) ) {
			$min_images = intval( vvApartmentPlatform::get_settings()['min_images_publish'] );
		}

		$photo_count = function_exists( 'vv_count_listing_photos' )
			? vv_count_listing_photos( $apartment )
			: count( vv_get_apartment_images( $apartment ) );

		$desc_ok = trim( gArrayItem( $apartment, 'about_this_short' ) ) !== '';
		if ( class_exists( 'vvI18n' ) ) {
			foreach ( array_keys( vvI18n::supported_locales() ) as $loc ) {
				$row = vvI18n::get_apartment_i18n( $apartment_id, $loc );
				if ( trim( gArrayItem( $row, 'about_this_short' ) ) !== '' || trim( gArrayItem( $row, 'description' ) ) !== '' ) {
					$desc_ok = true;
					break;
				}
			}
		}

		$facilities = json_decode( gArrayItem( $apartment, 'facilities' ), true );
		$rules_json = json_decode( gArrayItem( $apartment, 'house_rules_json' ), true );

		$items = [
			'building'      => intval( gArrayItem( $apartment, 'building_id' ) ) > 0,
			'photos'        => $photo_count >= $min_images,
			'description'   => $desc_ok,
			'pricing'       => floatval( gArrayItem( $apartment, 'price_daily' ) ) > 0,
			'house_rules'   => ( is_array( $rules_json ) && count( $rules_json ) > 0 ) || trim( gArrayItem( $apartment, 'house_rules' ) ) !== '',
			'check_in'      => trim( gArrayItem( $apartment, 'check_in_time1' ) ) !== '',
			'facilities'    => is_array( $facilities ) && count( $facilities ) > 0,
			'practical_info'=> trim( gArrayItem( $apartment, 'features_description' ) ) !== '' || trim( gArrayItem( $apartment, 'property_safety' ) ) !== '',
		];

		$done    = count( array_filter( $items ) );
		$total   = count( $items );
		$percent = $total > 0 ? intval( round( ( $done / $total ) * 100 ) ) : 0;

		return [
			'percent' => $percent,
			'done'    => $done,
			'total'   => $total,
			'items'   => $items,
		];
	}

	public static function count_booked_nights_in_range( array $bookings, $range_start, $range_end ) {
		$start_ts = strtotime( $range_start );
		$end_ts   = strtotime( $range_end );
		if ( ! $start_ts || ! $end_ts || $end_ts < $start_ts ) {
			return 0;
		}

		$nights = 0;
		foreach ( $bookings as $booking ) {
			$ci = gArrayItem( $booking, 'check_in_date' );
			$co = gArrayItem( $booking, 'check_out_date' );
			if ( ! $ci || ! $co ) {
				continue;
			}
			$ci_ts = strtotime( $ci );
			$co_ts = strtotime( $co );
			if ( $co_ts <= $start_ts || $ci_ts > $end_ts ) {
				continue;
			}
			$overlap_start = max( $ci_ts, $start_ts );
			$overlap_end   = min( $co_ts, $end_ts + DAY_IN_SECONDS );
			if ( $overlap_end > $overlap_start ) {
				$nights += ( $overlap_end - $overlap_start ) / DAY_IN_SECONDS;
			}
		}

		return intval( round( $nights ) );
	}

	public static function get_apartment_dashboard_stats( $apartment_id, $booking_class = null ) {
		$apartment_id = intval( $apartment_id );
		$today        = date( 'Y-m-d' );
		$past_30      = date( 'Y-m-d', strtotime( '-30 days' ) );
		$future_30    = date( 'Y-m-d', strtotime( '+30 days' ) );

		$bookings_all = [];
		if ( $booking_class && method_exists( $booking_class, 'get_bookings' ) ) {
			$bookings_all = $booking_class->get_bookings( [
				'apartment_ids' => [ $apartment_id ],
			] );
			if ( ! is_array( $bookings_all ) ) {
				$bookings_all = [];
			}
		}

		$bookings_past_30 = array_filter(
			$bookings_all,
			function ( $booking ) use ( $past_30, $today ) {
				$ci = gArrayItem( $booking, 'check_in_date' );
				$co = gArrayItem( $booking, 'check_out_date' );
				return $ci && $co && $ci <= $today && $co > $past_30;
			}
		);

		$revenue_30 = 0.0;
		foreach ( $bookings_past_30 as $booking ) {
			$ci = gArrayItem( $booking, 'check_in_date' );
			if ( $ci >= $past_30 && $ci <= $today ) {
				$revenue_30 += floatval( gArrayItem( $booking, 'total' ) );
			}
		}

		$booked_nights_30 = self::count_booked_nights_in_range( $bookings_past_30, $past_30, $today );
		$occupancy_30     = min( 100, intval( round( ( $booked_nights_30 / 30 ) * 100 ) ) );

		$upcoming = [];
		foreach ( $bookings_all as $booking ) {
			$ci = gArrayItem( $booking, 'check_in_date' );
			if ( $ci && $ci >= $today && $ci <= $future_30 ) {
				$upcoming[] = $booking;
			}
		}
		usort(
			$upcoming,
			function ( $a, $b ) {
				return strcmp( gArrayItem( $a, 'check_in_date' ), gArrayItem( $b, 'check_in_date' ) );
			}
		);

		$next_check_in = ! empty( $upcoming ) ? $upcoming[0] : null;

		$external_count = 0;
		foreach ( self::get_availability_periods( $apartment_id ) as $period ) {
			if ( gArrayItem( $period, 'period_type' ) !== 'external_airbnb' ) {
				continue;
			}
			$start = gArrayItem( $period, 'start_date' );
			$end   = gArrayItem( $period, 'end_date' );
			if ( $start <= $future_30 && $end >= $today ) {
				$external_count++;
			}
		}

		$recent = array_slice(
			array_reverse(
				array_filter(
					$bookings_all,
					function ( $booking ) use ( $today ) {
						return gArrayItem( $booking, 'check_in_date' ) <= $today;
					}
				)
			),
			0,
			5
		);

		return [
			'bookings_30'       => count( $bookings_past_30 ),
			'revenue_30'        => $revenue_30,
			'booked_nights_30'  => $booked_nights_30,
			'occupancy_30'      => $occupancy_30,
			'upcoming_count'    => count( $upcoming ),
			'external_30'       => $external_count,
			'next_check_in'     => $next_check_in,
			'upcoming_bookings' => array_slice( $upcoming, 0, 5 ),
			'recent_bookings'   => $recent,
			'total_bookings'    => count( $bookings_all ),
			'total_revenue'     => array_sum( array_map( function ( $b ) { return floatval( gArrayItem( $b, 'total' ) ); }, $bookings_all ) ),
		];
	}

	public static function get_location_cascade_json() {
		$city_id     = intval( GET_Request( 'city_id' ) );
		$district_id = intval( GET_Request( 'district_id' ) );

		if ( $district_id > 0 ) {
			$buildings = vv_get_neighbourhoods( [
				'per_page'    => 'all',
				'district_id' => $district_id,
				'orderby'     => 'post_title ASC',
			] );
			$out = [];
			foreach ( $buildings as $building ) {
				$bid = intval( gArrayItem( $building, 'ID' ) );
				$out[] = [
					'id'              => $bid,
					'name'            => gArrayItem( $building, 'post_title' ),
					'location_label'  => vv_get_building_location_label( $bid ),
					'district_id'     => $district_id,
					'district_label'  => vv_get_district_display_name( $district_id ),
				];
			}
			return [ 'buildings' => $out ];
		}

		if ( $city_id > 0 ) {
			$districts_all = vv_get_districts( [ 'per_page' => 'all', 'orderby' => 'post_title ASC' ] );
			$out           = [];
			foreach ( $districts_all as $district ) {
				$did = intval( gArrayItem( $district, 'ID' ) );
				$meta_city = intval( get_post_meta( $did, 'city', true ) );
				if ( $meta_city <= 0 && function_exists( 'get_field' ) ) {
					$meta_city = intval( get_field( 'city', $did ) );
				}
				if ( $meta_city === $city_id ) {
					$out[] = [
						'id'   => $did,
						'name' => gArrayItem( $district, 'post_title' ),
					];
				}
			}
			return [ 'districts' => $out ];
		}

		$cities = vv_get_cities( [ 'per_page' => 'all', 'orderby' => 'post_title ASC' ] );
		$out    = [];
		foreach ( $cities as $city ) {
			$out[] = [
				'id'   => intval( gArrayItem( $city, 'ID' ) ),
				'name' => gArrayItem( $city, 'post_title' ),
			];
		}

		return [ 'cities' => $out ];
	}

	protected function save_manage_availability() {
		global $wpdb;

		$apartment_id = intval( POST_Request( 'apartment_id' ) );
		$apartment    = vv_get_apartment( $apartment_id );

		if ( ! self::can_access_apartment( $apartment ) ) {
			setErrorMsg( 'Permission denied.' );
			wp_redirect( vv_admin_url( 'apartments' ) );
			die();
		}

		$sub_action = sanitize_key( POST_Request( 'availability_action' ) );

		if ( $sub_action === 'delete' ) {
			$period_id = intval( POST_Request( 'period_id' ) );
			$wpdb->delete(
				self::TABLE_AVAILABILITY_PERIODS,
				[
					'ID'           => $period_id,
					'apartment_id' => $apartment_id,
				]
			);
			setSuccessMsg( 'Availability period removed.' );
			wp_redirect( vv_admin_url( 'apartments/manage?id=' . $apartment_id . '&tab=availability' ) );
			die();
		}

		$period_type = sanitize_key( POST_Request( 'period_type' ) );
		$allowed     = [ 'manual_block', 'external_airbnb' ];
		if ( ! in_array( $period_type, $allowed, true ) ) {
			$period_type = 'manual_block';
		}

		$start_date = sanitize_text_field( POST_Request( 'start_date' ) );
		$end_date   = sanitize_text_field( POST_Request( 'end_date' ) );
		if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $start_date ) || ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $end_date ) ) {
			setErrorMsg( 'Invalid date format. Use YYYY-MM-DD.' );
			wp_redirect( vv_admin_url( 'apartments/manage?id=' . $apartment_id . '&tab=availability' ) );
			die();
		}
		if ( $end_date < $start_date ) {
			$end_date = $start_date;
		}

		$wpdb->insert(
			self::TABLE_AVAILABILITY_PERIODS,
			[
				'apartment_id'         => $apartment_id,
				'period_type'          => $period_type,
				'start_date'           => $start_date,
				'end_date'             => $end_date,
				'note'                 => sanitize_textarea_field( POST_Request( 'period_note' ) ),
				'guest_name'           => sanitize_text_field( POST_Request( 'guest_name' ) ),
				'guest_email'          => sanitize_email( POST_Request( 'guest_email' ) ),
				'source'               => sanitize_key( POST_Request( 'external_platform' ) ) ?: ( $period_type === 'external_airbnb' ? 'airbnb' : 'host' ),
				'external_platform'    => sanitize_key( POST_Request( 'external_platform' ) ) ?: ( $period_type === 'external_airbnb' ? 'airbnb' : null ),
				'external_booking_ref' => sanitize_text_field( POST_Request( 'external_booking_ref' ) ),
				'dateadded'            => current_time( 'mysql' ),
				'datemodified'         => current_time( 'mysql' ),
			]
		);

		$period_id = intval( $wpdb->insert_id );
		$msg       = 'Availability period added.';

		if ( $period_type === 'external_airbnb' && POST_Request( 'create_conversion' ) && class_exists( 'vvConversions' ) ) {
			$guest_email = sanitize_email( POST_Request( 'guest_email' ) );
			if ( $guest_email === '' ) {
				setErrorMsg( 'Guest email is required to create a conversion invite.' );
				wp_redirect( vv_admin_url( 'apartments/manage?id=' . $apartment_id . '&tab=availability' ) );
				die();
			}

			$host_id = intval( gArrayItem( $apartment, 'user_id' ) );
			$conv    = new vvConversions();
			$result  = $conv->create_from_external_booking( [
				'host_id'                => $host_id,
				'apartment_id'           => $apartment_id,
				'guest_email'            => $guest_email,
				'guest_name'             => sanitize_text_field( POST_Request( 'guest_name' ) ),
				'external_platform'      => sanitize_key( POST_Request( 'external_platform' ) ) ?: 'airbnb',
				'external_booking_ref'   => sanitize_text_field( POST_Request( 'external_booking_ref' ) ),
				'check_in_date'          => $start_date,
				'check_out_date'         => $end_date,
				'availability_period_id' => $period_id,
				'send_invite'            => (bool) POST_Request( 'send_conversion_invite' ),
			] );

			if ( is_wp_error( $result ) ) {
				setErrorMsg( $result->get_error_message() );
				wp_redirect( vv_admin_url( 'apartments/manage?id=' . $apartment_id . '&tab=availability' ) );
				die();
			}

			$wpdb->update(
				self::TABLE_AVAILABILITY_PERIODS,
				[
					'conversion_id' => intval( gArrayItem( $result, 'conversion_id' ) ),
					'datemodified'  => current_time( 'mysql' ),
				],
				[ 'ID' => $period_id ]
			);

			$msg = POST_Request( 'send_conversion_invite' )
				? 'External booking added and conversion invite sent.'
				: 'External booking added and conversion created (draft).';
		}

		setSuccessMsg( $msg );
		wp_redirect( vv_admin_url( 'apartments/manage?id=' . $apartment_id . '&tab=availability' ) );
		die();
	}

	protected function create_conversion_from_period() {
		global $wpdb;

		$apartment_id = intval( POST_Request( 'apartment_id' ) );
		$period_id    = intval( POST_Request( 'period_id' ) );
		$apartment    = vv_get_apartment( $apartment_id );

		if ( ! self::can_access_apartment( $apartment ) ) {
			setErrorMsg( 'Permission denied.' );
			wp_redirect( vv_admin_url( 'apartments' ) );
			die();
		}

		$period = $wpdb->get_row(
			$wpdb->prepare(
				'SELECT * FROM ' . self::TABLE_AVAILABILITY_PERIODS . ' WHERE ID = %d AND apartment_id = %d',
				$period_id,
				$apartment_id
			),
			ARRAY_A
		);

		if ( empty( $period['ID'] ) ) {
			setErrorMsg( 'Period not found.' );
			wp_redirect( vv_admin_url( 'apartments/manage?id=' . $apartment_id . '&tab=availability' ) );
			die();
		}

		if ( intval( gArrayItem( $period, 'conversion_id' ) ) > 0 ) {
			setErrorMsg( 'This period already has a conversion linked.' );
			wp_redirect( vv_admin_url( 'apartments/manage?id=' . $apartment_id . '&tab=availability' ) );
			die();
		}

		$guest_email = sanitize_email( POST_Request( 'guest_email' ) );
		if ( $guest_email === '' ) {
			$guest_email = sanitize_email( gArrayItem( $period, 'guest_email' ) );
		}
		if ( $guest_email === '' ) {
			setErrorMsg( 'Guest email is required.' );
			wp_redirect( vv_admin_url( 'apartments/manage?id=' . $apartment_id . '&tab=availability&detail=period-' . $period_id ) );
			die();
		}

		if ( ! class_exists( 'vvConversions' ) ) {
			setErrorMsg( 'Conversion module not available.' );
			wp_redirect( vv_admin_url( 'apartments/manage?id=' . $apartment_id . '&tab=availability' ) );
			die();
		}

		$conv   = new vvConversions();
		$result = $conv->create_from_external_booking( [
			'host_id'                => intval( gArrayItem( $apartment, 'user_id' ) ),
			'apartment_id'           => $apartment_id,
			'guest_email'            => $guest_email,
			'guest_name'             => gArrayItem( $period, 'guest_name' ),
			'external_platform'      => gArrayItem( $period, 'external_platform' ) ?: gArrayItem( $period, 'source', 'airbnb' ),
			'external_booking_ref'   => gArrayItem( $period, 'external_booking_ref' ),
			'check_in_date'          => gArrayItem( $period, 'start_date' ),
			'check_out_date'         => gArrayItem( $period, 'end_date' ),
			'availability_period_id' => $period_id,
			'send_invite'            => (bool) POST_Request( 'send_conversion_invite' ),
		] );

		if ( is_wp_error( $result ) ) {
			setErrorMsg( $result->get_error_message() );
			wp_redirect( vv_admin_url( 'apartments/manage?id=' . $apartment_id . '&tab=availability' ) );
			die();
		}

		$wpdb->update(
			self::TABLE_AVAILABILITY_PERIODS,
			[
				'conversion_id' => intval( gArrayItem( $result, 'conversion_id' ) ),
				'guest_email'   => $guest_email,
				'datemodified'  => current_time( 'mysql' ),
			],
			[ 'ID' => $period_id ]
		);

		setSuccessMsg( POST_Request( 'send_conversion_invite' ) ? 'Conversion created and invite sent.' : 'Conversion created.' );
		wp_redirect( vv_admin_url( 'apartments/manage?id=' . $apartment_id . '&tab=availability' ) );
		die();
	}

	protected function submit_building_request() {
		global $wpdb;

		$user_id      = get_current_user_id();
		$apartment_id = intval( POST_Request( 'apartment_id' ) );
		$city_id      = intval( POST_Request( 'request_city_id' ) );
		$district_id  = intval( POST_Request( 'request_district_id' ) );
		$name         = sanitize_text_field( POST_Request( 'request_building_name' ) );
		$address      = sanitize_textarea_field( POST_Request( 'request_address' ) );
		$notes        = sanitize_textarea_field( POST_Request( 'request_notes' ) );

		if ( $name === '' ) {
			setErrorMsg( 'Please enter a building name for your request.' );
			wp_redirect( vv_admin_url( 'apartments/add?step=1' . ( $apartment_id ? '&id=' . $apartment_id : '' ) ) );
			die();
		}

		$wpdb->insert(
			self::TABLE_BUILDING_REQUESTS,
			[
				'user_id'      => $user_id,
				'apartment_id' => $apartment_id > 0 ? $apartment_id : null,
				'city_id'      => $city_id > 0 ? $city_id : null,
				'district_id'  => $district_id > 0 ? $district_id : null,
				'building_name'=> $name,
				'address'      => $address,
				'notes'        => $notes,
				'status'       => 'pending',
				'dateadded'    => current_time( 'mysql' ),
				'datemodified' => current_time( 'mysql' ),
			]
		);

		setSuccessMsg( 'Building request submitted. Vietstays will review and add the building to the list.' );
		wp_redirect( vv_admin_url( 'apartments/add?step=1' . ( $apartment_id ? '&id=' . $apartment_id : '' ) ) );
		die();
	}

	public static function building_request_status_labels() {
		return [
			'pending'  => 'Pending',
			'reviewed' => 'Under review',
			'approved' => 'Approved',
			'rejected' => 'Rejected',
		];
	}

	public static function get_building_requests( $filter = [] ) {
		global $wpdb;

		$table  = self::TABLE_BUILDING_REQUESTS;
		$where  = ' WHERE 1=1 ';
		$status = gArrayItem( $filter, 'status' );

		if ( $status === 'all' ) {
			// no status filter
		} elseif ( $status !== '' ) {
			$where .= $wpdb->prepare( ' AND status = %s', sanitize_key( $status ) );
		} else {
			$where .= " AND status = 'pending' ";
		}

		$search = trim( gArrayItem( $filter, 'search' ) );
		if ( $search !== '' ) {
			$like = '%' . $wpdb->esc_like( $search ) . '%';
			$where .= $wpdb->prepare(
				' AND (building_name LIKE %s OR address LIKE %s OR notes LIKE %s)',
				$like,
				$like,
				$like
			);
		}

		$per_page = gArrayItem( $filter, 'per_page', 50 );
		$limit    = '';
		if ( $per_page !== 'all' ) {
			$limit = ' LIMIT ' . intval( $per_page );
		}

		$sql  = "SELECT * FROM {$table}{$where} ORDER BY dateadded DESC{$limit}";
		$rows = $wpdb->get_results( $sql, ARRAY_A );

		return is_array( $rows ) ? $rows : [];
	}

	public static function get_building_request( $request_id ) {
		global $wpdb;

		$row = $wpdb->get_row(
			$wpdb->prepare(
				'SELECT * FROM ' . self::TABLE_BUILDING_REQUESTS . ' WHERE ID = %d',
				intval( $request_id )
			),
			ARRAY_A
		);

		return is_array( $row ) ? $row : [];
	}

	public static function complete_building_request( $request_id, $building_id, $admin_note = '' ) {
		global $wpdb;

		$wpdb->update(
			self::TABLE_BUILDING_REQUESTS,
			[
				'status'               => 'approved',
				'resolved_building_id' => intval( $building_id ),
				'admin_note'           => $admin_note !== '' ? sanitize_textarea_field( $admin_note ) : null,
				'datemodified'         => current_time( 'mysql' ),
			],
			[ 'ID' => intval( $request_id ) ]
		);
	}

	protected function update_building_request() {
		global $wpdb;

		if ( ! vv_is_admin_role() ) {
			setErrorMsg( 'Permission denied.' );
			wp_redirect( vv_admin_url( 'building-requests' ) );
			die();
		}

		$request_id = intval( POST_Request( 'request_id' ) );
		$request    = self::get_building_request( $request_id );
		if ( empty( $request['ID'] ) ) {
			setErrorMsg( 'Request not found.' );
			wp_redirect( vv_admin_url( 'building-requests' ) );
			die();
		}

		$sub_action = sanitize_key( POST_Request( 'request_action' ) );
		$admin_note = sanitize_textarea_field( POST_Request( 'admin_note' ) );
		$data       = [
			'datemodified' => current_time( 'mysql' ),
		];

		if ( $sub_action === 'reject' ) {
			if ( $admin_note === '' ) {
				setErrorMsg( 'Please add a note explaining the rejection.' );
				wp_redirect( vv_admin_url( 'building-requests/view?id=' . $request_id ) );
				die();
			}
			$data['status']     = 'rejected';
			$data['admin_note'] = $admin_note;
			setSuccessMsg( 'Building request rejected.' );
		} elseif ( $sub_action === 'reviewed' ) {
			$data['status'] = 'reviewed';
			if ( $admin_note !== '' ) {
				$data['admin_note'] = $admin_note;
			}
			setSuccessMsg( 'Request marked as under review.' );
		} elseif ( $sub_action === 'reopen' ) {
			$data['status'] = 'pending';
			setSuccessMsg( 'Request reopened.' );
		} else {
			setErrorMsg( 'Unknown action.' );
			wp_redirect( vv_admin_url( 'building-requests/view?id=' . $request_id ) );
			die();
		}

		$wpdb->update( self::TABLE_BUILDING_REQUESTS, $data, [ 'ID' => $request_id ] );
		wp_redirect( vv_admin_url( 'building-requests/view?id=' . $request_id ) );
		die();
	}

	public function render_images_html( $apartment_id ) {
		global $apartment;

		$apartment = vv_get_apartment( $apartment_id );
		if ( ! self::can_access_apartment( $apartment ) ) {
			echo '<p class="text-danger">' . esc_html( vv__( 'Permission denied.' ) ) . '</p>';
			return;
		}

		$images = vv_get_apartment_images( $apartment );
		include dirname( __DIR__ ) . '/admin/views/apartments/_add-images-grid.php';
	}
}

function vv_apartments_quality_standards() {
	return vvApartmentsWizard::quality_standards();
}

function vv_apartments_quality_tiers() {
	return vvApartmentsWizard::quality_tiers();
}

function vv_apartments_types() {
	return vvApartmentsWizard::apartment_types();
}
