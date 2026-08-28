<?php

class vvApartmentPlatform {

	const OPTION_SETTINGS   = 'vv_apartment_platform_settings';
	const OPTION_HOUSE_RULES  = 'vv_house_rules';
	const META_BUILDING_PRICE_MATRIX = 'building_price_matrix';
	const TABLE_BLOCKED       = 'vv_apartment_blocked_dates';
	const TABLE_OFFERS        = 'vv_apartment_offers';

	public function __construct() {
		add_action( 'init', [ $this, 'maybe_create_tables' ], 5 );
		add_action( 'init', [ $this, 'maybe_upgrade_apartment_columns' ], 6 );
	}

	public function maybe_create_tables() {
		global $wpdb;

		$charset = $wpdb->get_charset_collate();

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		dbDelta( "CREATE TABLE " . self::TABLE_BLOCKED . " (
			ID bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			apartment_id bigint(20) unsigned NOT NULL,
			block_date date NOT NULL,
			note varchar(255) DEFAULT NULL,
			dateadded datetime NOT NULL,
			PRIMARY KEY (ID),
			UNIQUE KEY apt_date (apartment_id, block_date),
			KEY apartment_id (apartment_id)
		) {$charset};" );

		dbDelta( "CREATE TABLE " . self::TABLE_OFFERS . " (
			ID bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			apartment_id bigint(20) unsigned NOT NULL,
			guest_email varchar(255) NOT NULL,
			guest_name varchar(255) DEFAULT NULL,
			check_in_date date NOT NULL,
			check_out_date date NOT NULL,
			offer_price decimal(12,2) DEFAULT NULL,
			token varchar(64) NOT NULL,
			status varchar(20) NOT NULL DEFAULT 'sent',
			created_by bigint(20) unsigned DEFAULT NULL,
			dateadded datetime NOT NULL,
			datemodified datetime NOT NULL,
			PRIMARY KEY (ID),
			KEY apartment_id (apartment_id),
			KEY token (token)
		) {$charset};" );

	}

	public function maybe_upgrade_apartment_columns() {
		global $wpdb;

		$columns = $wpdb->get_col( 'DESCRIBE vv_apartments', 0 );
		if ( ! is_array( $columns ) ) {
			return;
		}

		$add = [
			'building_id'           => "ADD COLUMN building_id bigint(20) unsigned NOT NULL DEFAULT 0 AFTER district",
			'distinguishing_feature' => "ADD COLUMN distinguishing_feature varchar(255) DEFAULT NULL AFTER building_id",
			'apartment_type'        => "ADD COLUMN apartment_type varchar(10) DEFAULT NULL AFTER distinguishing_feature",
			'price_level'           => "ADD COLUMN price_level varchar(20) NOT NULL DEFAULT 'normal' AFTER apartment_type",
			'pricing_model'         => "ADD COLUMN pricing_model varchar(20) NOT NULL DEFAULT 'fixed' AFTER price_level",
			'seasonal_pricing'      => "ADD COLUMN seasonal_pricing longtext DEFAULT NULL AFTER pricing_model",
			'cleaning_fee_enabled'  => "ADD COLUMN cleaning_fee_enabled tinyint(1) NOT NULL DEFAULT 1 AFTER cleaning_fee",
			'extra_cleaning_fee'    => "ADD COLUMN extra_cleaning_fee decimal(12,2) DEFAULT NULL AFTER cleaning_fee_enabled",
			'house_rules_json'      => "ADD COLUMN house_rules_json longtext DEFAULT NULL AFTER house_rules",
			'assigned_staff'        => "ADD COLUMN assigned_staff longtext DEFAULT NULL AFTER house_rules_json",
		];

		foreach ( $add as $col => $sql_part ) {
			if ( ! in_array( $col, $columns, true ) ) {
				$wpdb->query( "ALTER TABLE vv_apartments {$sql_part}" );
			}
		}
	}

	public static function default_settings() {
		return [
			'level_low_pct'     => -10,
			'level_normal_pct'  => 0,
			'level_high_pct'    => 10,
			'min_images_publish'=> 10,
			'cleaning_1br'      => 25,
			'cleaning_2br'      => 35,
			'cleaning_3br'      => 45,
			'cleaning_4br'      => 55,
			'price_min_1br'     => 30,
			'price_max_1br'     => 500,
			'price_min_2br'     => 40,
			'price_max_2br'     => 800,
			'price_min_3br'     => 60,
			'price_max_3br'     => 1200,
			'price_min_4br'     => 80,
			'price_max_4br'     => 2000,
			'base_suggest_1br'  => 55,
			'base_suggest_2br'  => 75,
			'base_suggest_3br'  => 95,
			'base_suggest_4br'  => 120,
		];
	}

	public static function get_settings() {
		$stored = get_option( self::OPTION_SETTINGS, [] );
		if ( ! is_array( $stored ) ) {
			$stored = [];
		}
		return array_merge( self::default_settings(), $stored );
	}

	public static function save_settings( array $data ) {
		$defaults = self::default_settings();
		$clean    = [];
		foreach ( $defaults as $key => $default ) {
			if ( isset( $data[ $key ] ) && $data[ $key ] !== '' ) {
				$clean[ $key ] = is_numeric( $default ) ? floatval( $data[ $key ] ) : sanitize_text_field( $data[ $key ] );
			}
		}
		update_option( self::OPTION_SETTINGS, $clean, false );
		return array_merge( $defaults, $clean );
	}

	public static function apartment_types() {
		return [
			'1BR' => '1BR',
			'2BR' => '2BR',
			'3BR' => '3BR',
			'4BR' => '4BR',
		];
	}

	public static function price_levels() {
		return [
			'low'    => 'Low',
			'normal' => 'Normal',
			'high'   => 'High',
		];
	}

	public static function get_house_rules() {
		$rules = get_option( self::OPTION_HOUSE_RULES, [] );
		if ( ! is_array( $rules ) || empty( $rules ) ) {
			$rules = [
				[ 'id' => 'no-smoking', 'label' => 'No smoking', 'i18n' => [] ],
				[ 'id' => 'no-parties', 'label' => 'No parties or events', 'i18n' => [] ],
				[ 'id' => 'no-pets', 'label' => 'No pets', 'i18n' => [] ],
				[ 'id' => 'quiet-hours', 'label' => 'Quiet hours after 10 PM', 'i18n' => [] ],
			];
		}
		return $rules;
	}

	public static function save_house_rules( array $rules ) {
		$clean = [];
		foreach ( $rules as $rule ) {
			if ( ! is_array( $rule ) ) {
				continue;
			}
			$id = sanitize_key( gArrayItem( $rule, 'id' ) );
			$label = sanitize_text_field( gArrayItem( $rule, 'label' ) );
			if ( $id === '' || $label === '' ) {
				continue;
			}
			$i18n = [];
			if ( is_array( gArrayItem( $rule, 'i18n' ) ) ) {
				foreach ( $rule['i18n'] as $locale => $text ) {
					$i18n[ sanitize_key( $locale ) ] = sanitize_text_field( $text );
				}
			}
			$clean[] = [ 'id' => $id, 'label' => $label, 'i18n' => $i18n ];
		}
		update_option( self::OPTION_HOUSE_RULES, $clean, false );
		return $clean;
	}

	public static function level_factor( $level ) {
		$s = self::get_settings();
		$map = [
			'low'    => floatval( $s['level_low_pct'] ),
			'normal' => floatval( $s['level_normal_pct'] ),
			'high'   => floatval( $s['level_high_pct'] ),
		];
		return isset( $map[ $level ] ) ? $map[ $level ] : 0;
	}

	public static function price_matrix_types() {
		return [ 'Studio', '1BR', '2BR', '3BR', '4BR' ];
	}

	public static function apartment_type_to_matrix_key( $apartment_type ) {
		$type = strtolower( trim( (string) $apartment_type ) );
		if ( $type === 'studio' ) {
			return 'studio';
		}
		return strtolower( str_replace( 'BR', 'br', trim( (string) $apartment_type ) ) );
	}

	public static function get_building_price_matrix( $building_id ) {
		$building_id = intval( $building_id );
		if ( $building_id <= 0 ) {
			return [];
		}

		$raw = get_post_meta( $building_id, self::META_BUILDING_PRICE_MATRIX, true );
		if ( ! is_array( $raw ) ) {
			return [];
		}

		$clean = [];
		foreach ( self::price_matrix_types() as $type ) {
			$key = self::apartment_type_to_matrix_key( $type );
			if ( isset( $raw[ $key ] ) && $raw[ $key ] !== '' ) {
				$clean[ $key ] = floatval( $raw[ $key ] );
			}
		}

		return $clean;
	}

	public static function save_building_price_matrix( $building_id, array $matrix ) {
		$building_id = intval( $building_id );
		if ( $building_id <= 0 ) {
			return [];
		}

		$clean = [];
		foreach ( self::price_matrix_types() as $type ) {
			$key = self::apartment_type_to_matrix_key( $type );
			if ( isset( $matrix[ $key ] ) && $matrix[ $key ] !== '' ) {
				$clean[ $key ] = floatval( $matrix[ $key ] );
			}
		}

		update_post_meta( $building_id, self::META_BUILDING_PRICE_MATRIX, $clean );
		return $clean;
	}

	public static function get_price_limits( $apartment_type ) {
		$s   = self::get_settings();
		$key = self::apartment_type_to_matrix_key( $apartment_type );
		if ( $key === 'studio' ) {
			$key = '1br';
		}
		return [
			'min' => floatval( gArrayItem( $s, 'price_min_' . $key, 0 ) ),
			'max' => floatval( gArrayItem( $s, 'price_max_' . $key, 0 ) ),
		];
	}

	public static function suggest_daily_price( $apartment_type, $price_level, $building_id = 0 ) {
		$s    = self::get_settings();
		$key  = self::apartment_type_to_matrix_key( $apartment_type );
		if ( $key === 'studio' ) {
			$key = '1br';
		}
		$base = 0.0;

		if ( intval( $building_id ) > 0 ) {
			$matrix_key = self::apartment_type_to_matrix_key( $apartment_type );
			$matrix     = self::get_building_price_matrix( $building_id );
			if ( ! empty( $matrix[ $matrix_key ] ) ) {
				$base = floatval( $matrix[ $matrix_key ] );
			}
		}

		if ( $base <= 0 ) {
			$base = floatval( gArrayItem( $s, 'base_suggest_' . $key, 75 ) );
		}

		$factor = self::level_factor( $price_level );
		return round( $base * ( 1 + ( $factor / 100 ) ), 2 );
	}

	public static function default_cleaning_fee( $apartment_type ) {
		$s   = self::get_settings();
		$key = 'cleaning_' . strtolower( str_replace( 'BR', 'br', $apartment_type ) );
		return floatval( gArrayItem( $s, $key, 35 ) );
	}

	public static function rooms_to_type( $rooms ) {
		$rooms = intval( $rooms );
		if ( $rooms <= 1 ) {
			return '1BR';
		}
		if ( $rooms === 2 ) {
			return '2BR';
		}
		if ( $rooms === 3 ) {
			return '3BR';
		}
		return '4BR';
	}

	public static function generate_apartment_name( $building_name, $feature, $district_label, $apartment_type ) {
		$building_name = trim( (string) $building_name );
		$feature       = trim( (string) $feature );
		$district_label = trim( (string) $district_label );
		$apartment_type = trim( (string) $apartment_type );

		$parts = array_filter( [ $building_name, $feature ] );
		$name  = implode( '–', $parts );
		if ( $district_label !== '' ) {
			$name .= ( $name !== '' ? ' ' : '' ) . $district_label;
		}
		if ( $apartment_type !== '' ) {
			$name .= ( $name !== '' ? '–' : '' ) . $apartment_type;
		}
		return substr( $name, 0, 25 );
	}

	public static function get_building_facilities( $building_id ) {
		$building_id = intval( $building_id );
		if ( $building_id <= 0 ) {
			return [];
		}
		$ids = get_post_meta( $building_id, 'facilities', true );
		if ( ! is_array( $ids ) ) {
			$ids = [];
		}
		$out = [];
		foreach ( vv_get_facilities() as $facility ) {
			if ( in_array( gArrayItem( $facility, 'facility_id' ), $ids, false ) ) {
				$out[] = $facility;
			}
		}
		return $out;
	}

	public static function get_blocked_dates( $apartment_id ) {
		global $wpdb;
		$apartment_id = intval( $apartment_id );
		if ( $apartment_id <= 0 ) {
			return [];
		}
		$rows = $wpdb->get_col(
			$wpdb->prepare(
				'SELECT block_date FROM ' . self::TABLE_BLOCKED . ' WHERE apartment_id = %d ORDER BY block_date ASC',
				$apartment_id
			)
		);
		return is_array( $rows ) ? $rows : [];
	}

	public static function save_blocked_dates( $apartment_id, array $dates ) {
		global $wpdb;
		$apartment_id = intval( $apartment_id );
		if ( $apartment_id <= 0 ) {
			return;
		}
		$wpdb->delete( self::TABLE_BLOCKED, [ 'apartment_id' => $apartment_id ] );
		foreach ( $dates as $date ) {
			$date = sanitize_text_field( $date );
			if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date ) ) {
				continue;
			}
			$wpdb->insert(
				self::TABLE_BLOCKED,
				[
					'apartment_id' => $apartment_id,
					'block_date'   => $date,
					'dateadded'    => current_time( 'mysql' ),
				]
			);
		}
	}

	public static function is_date_blocked( $apartment_id, $date ) {
		global $wpdb;
		$count = $wpdb->get_var(
			$wpdb->prepare(
				'SELECT COUNT(*) FROM ' . self::TABLE_BLOCKED . ' WHERE apartment_id = %d AND block_date = %s',
				intval( $apartment_id ),
				$date
			)
		);
		return intval( $count ) > 0;
	}

	public static function apartment_has_blocked_range( $apartment_id, $check_in, $check_out ) {
		$date1 = strtotime( $check_in );
		$date2 = strtotime( $check_out );
		if ( ! $date1 || ! $date2 ) {
			return false;
		}
		while ( $date1 < $date2 ) {
			if ( self::is_date_blocked( $apartment_id, date( 'Y-m-d', $date1 ) ) ) {
				return true;
			}
			$date1 = strtotime( '+1 day', $date1 );
		}
		return false;
	}

	public static function count_apartment_images( $apartment ) {
		if ( function_exists( 'vv_count_listing_photos' ) ) {
			return vv_count_listing_photos( $apartment );
		}
		$images = vv_get_apartment_images( $apartment );
		return is_array( $images ) ? count( $images ) : 0;
	}

	public static function validate_publish( array $apartment, $new_status ) {
		if ( $new_status !== 'active' ) {
			return true;
		}
		$s = self::get_settings();
		$min = intval( $s['min_images_publish'] );
		$errors = [];
		if ( trim( gArrayItem( $apartment, 'name' ) ) === '' ) {
			$errors[] = 'Apartment name is required.';
		}
		if ( intval( gArrayItem( $apartment, 'district' ) ) <= 0 ) {
			$errors[] = 'District is required.';
		}
		if ( floatval( gArrayItem( $apartment, 'price_daily' ) ) <= 0 ) {
			$errors[] = 'Daily price is required.';
		}
		if ( self::count_apartment_images( $apartment ) < $min ) {
			$errors[] = sprintf( 'At least %d images are required to publish.', $min );
		}
		if ( ! empty( $errors ) ) {
			return new WP_Error( 'publish_validation', implode( ' ', $errors ) );
		}
		return true;
	}

	public static function get_assigned_staff_ids( $apartment ) {
		$raw = gArrayItem( $apartment, 'assigned_staff' );
		if ( is_string( $raw ) && $raw !== '' ) {
			$decoded = json_decode( $raw, true );
			return is_array( $decoded ) ? array_map( 'intval', $decoded ) : [];
		}
		return is_array( $raw ) ? array_map( 'intval', $raw ) : [];
	}

	public static function get_offers( $apartment_id ) {
		global $wpdb;
		return $wpdb->get_results(
			$wpdb->prepare(
				'SELECT * FROM ' . self::TABLE_OFFERS . ' WHERE apartment_id = %d ORDER BY dateadded DESC LIMIT 50',
				intval( $apartment_id )
			),
			ARRAY_A
		);
	}

	public function post_actions( $action = '' ) {
		if ( $action === 'save_apartment_platform_settings' ) {
			$this->save_platform_settings();
		} elseif ( $action === 'save_house_rules_settings' ) {
			$this->save_house_rules_settings();
		} elseif ( $action === 'send_apartment_offer' ) {
			$this->send_apartment_offer();
		} elseif ( $action === 'save_building_price_matrix' ) {
			$this->save_building_price_matrix_page();
		}
	}

	public function save_platform_settings() {
		if ( ! current_user_can( 'manage_options' ) && ! vv_is_admin_role() ) {
			setErrorMsg( 'Permission denied.' );
			wp_redirect( vv_admin_url( 'settings/apartment-platform' ) );
			die();
		}
		self::save_settings( $_POST );
		setSuccessMsg( 'Apartment platform settings saved.' );
		wp_redirect( vv_admin_url( 'settings/apartment-platform' ) );
		die();
	}

	public function save_house_rules_settings() {
		if ( ! current_user_can( 'manage_options' ) && ! vv_is_admin_role() ) {
			setErrorMsg( 'Permission denied.' );
			wp_redirect( vv_admin_url( 'settings/apartment-platform' ) );
			die();
		}
		$rules = [];
		$ids   = POST_Request( 'rule_id' );
		$labels = POST_Request( 'rule_label' );
		if ( is_array( $ids ) ) {
			foreach ( $ids as $i => $id ) {
				$rules[] = [
					'id'    => $id,
					'label' => is_array( $labels ) ? gArrayItem( $labels, $i ) : '',
					'i18n'  => [],
				];
			}
		}
		if ( trim( POST_Request( 'rule_label_new' ) ) !== '' ) {
			$rules[] = [
				'id'    => sanitize_title( POST_Request( 'rule_label_new' ) ),
				'label' => POST_Request( 'rule_label_new' ),
				'i18n'  => [],
			];
		}
		self::save_house_rules( $rules );
		setSuccessMsg( 'House rules updated.' );
		wp_redirect( vv_admin_url( 'settings/apartment-platform' ) );
		die();
	}

	public function save_building_price_matrix_page() {
		if ( ! current_user_can( 'manage_options' ) && ! vv_is_admin_role() ) {
			setErrorMsg( 'Permission denied.' );
			wp_redirect( vv_admin_url( 'settings/building-price-matrix' ) );
			die();
		}

		$matrix_post = POST_Request( 'building_matrix' );
		if ( is_array( $matrix_post ) ) {
			foreach ( $matrix_post as $building_id => $types ) {
				if ( ! is_array( $types ) ) {
					continue;
				}
				self::save_building_price_matrix( intval( $building_id ), $types );
			}
		}

		setSuccessMsg( 'Building price matrix saved.' );
		wp_redirect( vv_admin_url( 'settings/building-price-matrix' ) );
		die();
	}

	public function send_apartment_offer() {
		global $wpdb;

		$apartment_id = intval( POST_Request( 'apartment_id' ) );
		$email        = sanitize_email( POST_Request( 'offer_guest_email' ) );
		$name         = sanitize_text_field( POST_Request( 'offer_guest_name' ) );
		$check_in     = sanitize_text_field( POST_Request( 'offer_check_in' ) );
		$check_out    = sanitize_text_field( POST_Request( 'offer_check_out' ) );
		$price        = floatval( POST_Request( 'offer_price' ) );

		if ( $apartment_id <= 0 || $email === '' || $check_in === '' || $check_out === '' ) {
			setErrorMsg( 'Offer requires apartment, guest email, and dates.' );
			wp_redirect( vv_admin_url( 'apartments/edit/?id=' . $apartment_id ) );
			die();
		}

		$token = wp_generate_password( 32, false );
		$now   = current_time( 'mysql' );

		$wpdb->insert(
			self::TABLE_OFFERS,
			[
				'apartment_id'   => $apartment_id,
				'guest_email'    => $email,
				'guest_name'     => $name,
				'check_in_date'  => date( 'Y-m-d', strtotime( $check_in ) ),
				'check_out_date' => date( 'Y-m-d', strtotime( $check_out ) ),
				'offer_price'    => $price,
				'token'          => $token,
				'status'         => 'sent',
				'created_by'     => get_current_user_id(),
				'dateadded'      => $now,
				'datemodified'   => $now,
			]
		);

		$apartment = vv_get_apartment( $apartment_id );
		$link      = add_query_arg(
			[
				'vv_offer' => $token,
				'apt'      => $apartment_id,
			],
			home_url( '/vv-users/booking' )
		);

		$subject = sprintf( 'Private booking offer — %s', gArrayItem( $apartment, 'display_name', gArrayItem( $apartment, 'name' ) ) );
		$body    = '<p>Hello ' . esc_html( $name !== '' ? $name : 'there' ) . ',</p>';
		$body   .= '<p>You have received a private booking offer for <strong>' . esc_html( gArrayItem( $apartment, 'display_name' ) ) . '</strong>.</p>';
		$body   .= '<p>Dates: ' . esc_html( $check_in ) . ' → ' . esc_html( $check_out ) . '</p>';
		if ( $price > 0 ) {
			$body .= '<p>Offer price: ' . esc_html( vv_number_format( $price, true ) ) . '</p>';
		}
		$body .= '<p><a href="' . esc_url( $link ) . '">View offer and complete booking</a></p>';

		wp_mail( $email, $subject, $body, [ 'Content-Type: text/html; charset=UTF-8' ] );

		setSuccessMsg( 'Private offer sent to ' . $email );
		wp_redirect( vv_admin_url( 'apartments/edit/?id=' . $apartment_id ) );
		die();
	}
}

function vv_is_admin_role() {
	global $logged_user_role;
	return $logged_user_role === 'administrator';
}

function vv_apartment_platform_settings() {
	return vvApartmentPlatform::get_settings();
}

function vv_get_house_rules() {
	return vvApartmentPlatform::get_house_rules();
}
