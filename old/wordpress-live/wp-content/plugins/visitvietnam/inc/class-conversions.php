<?php

/**
 * Conversion module — external bookings (Airbnb/Booking.com) → guest invite → 10% direct discount.
 * Spec §1.9 — single use, 3-month expiry, max 30/month per host.
 */
class vvConversions {

	const TABLE           = 'vv_conversions';
	const DISCOUNT_PCT    = 10;
	const EXPIRY_MONTHS   = 3;
	const MONTHLY_LIMIT   = 30;

	public function __construct() {
		add_action( 'init', [ $this, 'maybe_create_table' ], 5 );
		add_action( 'init', [ $this, 'maybe_upgrade_columns' ], 6 );
		add_action( 'init', [ $this, 'maybe_expire_conversions' ], 20 );
	}

	public function maybe_upgrade_columns() {
		global $wpdb;

		$table = self::TABLE;
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) !== $table ) {
			return;
		}

		$columns = $wpdb->get_col( "DESCRIBE {$table}", 0 );
		if ( ! is_array( $columns ) ) {
			return;
		}

		if ( ! in_array( 'availability_period_id', $columns, true ) ) {
			$wpdb->query( "ALTER TABLE {$table} ADD COLUMN availability_period_id bigint(20) unsigned DEFAULT NULL AFTER apartment_id" );
		}
	}

	public function maybe_create_table() {
		global $wpdb;

		$table   = self::TABLE;
		$charset = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table} (
			ID bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			conversion_ref varchar(32) NOT NULL,
			host_id bigint(20) unsigned NOT NULL,
			apartment_id bigint(20) unsigned NOT NULL,
			availability_period_id bigint(20) unsigned DEFAULT NULL,
			guest_email varchar(255) NOT NULL,
			guest_name varchar(255) DEFAULT NULL,
			external_platform varchar(32) NOT NULL DEFAULT 'airbnb',
			external_booking_ref varchar(128) DEFAULT NULL,
			check_in_date date DEFAULT NULL,
			check_out_date date DEFAULT NULL,
			conversion_token varchar(64) NOT NULL,
			promo_code_id bigint(20) unsigned DEFAULT NULL,
			promo_code varchar(32) DEFAULT NULL,
			discount_pct decimal(5,2) NOT NULL DEFAULT 10.00,
			status varchar(32) NOT NULL DEFAULT 'draft',
			guest_user_id bigint(20) unsigned DEFAULT NULL,
			booking_id bigint(20) unsigned DEFAULT NULL,
			opened_at datetime DEFAULT NULL,
			converted_at datetime DEFAULT NULL,
			expires_at datetime DEFAULT NULL,
			dateadded datetime NOT NULL,
			datemodified datetime NOT NULL,
			PRIMARY KEY (ID),
			UNIQUE KEY conversion_ref (conversion_ref),
			UNIQUE KEY conversion_token (conversion_token),
			KEY host_id (host_id),
			KEY apartment_id (apartment_id),
			KEY guest_email (guest_email),
			KEY status (status),
			KEY promo_code (promo_code)
		) {$charset};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	public function post_actions( $action = '' ) {
		if ( $action === 'vv_save_conversion' ) {
			$this->save_conversion();
		} elseif ( $action === 'vv_send_conversion' ) {
			$this->send_conversion();
		} elseif ( $action === 'conversion_guest_account' ) {
			$this->handle_conversion_guest_account();
		} elseif ( $action === 'conversion_start_booking' ) {
			$this->handle_conversion_start_booking();
		}
	}

	public function get_actions( $action = '' ) {
		if ( $action === 'conversion_landing' ) {
			$this->handle_landing();
		} elseif ( $action === 'vv_resend_conversion' && GET_Request( 'id' ) > 0 ) {
			$this->resend_conversion( intval( GET_Request( 'id' ) ) );
		} elseif ( $action === 'vv_cancel_conversion' && GET_Request( 'id' ) > 0 ) {
			$this->cancel_conversion( intval( GET_Request( 'id' ) ) );
		}
	}

	public static function platforms() {
		return [
			'airbnb'  => 'Airbnb',
			'booking' => 'Booking.com',
			'trip'    => 'Trip.com',
			'other'   => 'Other',
		];
	}

	public static function status_labels() {
		return [
			'draft'      => 'Draft',
			'sent'       => 'Invite sent',
			'opened'     => 'Link opened',
			'registered' => 'Guest registered',
			'converted'  => 'Booked on Vietstays',
			'expired'    => 'Expired',
			'cancelled'  => 'Cancelled',
		];
	}

	public static function status_label( $status ) {
		return gArrayItem( self::status_labels(), $status, $status );
	}

	public static function can_manage( $user_id = 0 ) {
		if ( $user_id <= 0 ) {
			$user_id = get_current_user_id();
		}
		if ( current_user_can( 'manage_options' ) || vv_is_admin_role() ) {
			return true;
		}
		global $logged_user_role;
		if ( $logged_user_role === 'sales_support' ) {
			return true;
		}
		return vv_user_can( 'manage_conversions', $user_id );
	}

	public static function can_access_conversion( array $conversion, $user_id = 0 ) {
		if ( ! self::can_manage( $user_id ) ) {
			return false;
		}
		if ( current_user_can( 'manage_options' ) || vv_is_admin_role() ) {
			return true;
		}
		global $logged_user_role;
		if ( $logged_user_role === 'sales_support' ) {
			return true;
		}
		return intval( gArrayItem( $conversion, 'host_id' ) ) === ( $user_id > 0 ? $user_id : get_current_user_id() );
	}

	public function get_conversions( $filter = [] ) {
		global $wpdb;

		$where   = ' 1 ';
		$orderby = ' ORDER BY c.dateadded DESC ';
		$limit   = '';
		$join    = '';

		if ( intval( gArrayItem( $filter, 'host_id' ) ) > 0 ) {
			$where .= ' AND c.host_id = ' . intval( $filter['host_id'] );
		}
		if ( intval( gArrayItem( $filter, 'apartment_id' ) ) > 0 ) {
			$where .= ' AND c.apartment_id = ' . intval( $filter['apartment_id'] );
		}
		if ( trim( gArrayItem( $filter, 'status' ) ) !== '' && gArrayItem( $filter, 'status' ) !== 'all' ) {
			$where .= " AND c.status = '" . esc_sql( $filter['status'] ) . "' ";
		}
		if ( trim( gArrayItem( $filter, 'search' ) ) !== '' ) {
			$srch   = esc_sql( $filter['search'] );
			$where .= " AND ( c.conversion_ref LIKE '%{$srch}%' OR c.guest_email LIKE '%{$srch}%' OR c.guest_name LIKE '%{$srch}%' OR c.external_booking_ref LIKE '%{$srch}%' OR a.name LIKE '%{$srch}%' ) ";
		}

		$join = ' LEFT JOIN vv_apartments a ON c.apartment_id = a.ID ';

		$per_page     = intval( gArrayItem( $filter, 'per_page', 50 ) );
		$return_total = intval( gArrayItem( $filter, 'return_total' ) );
		$total_rows   = 0;

		if ( $return_total ) {
			$total_rows = intval(
				$wpdb->get_var(
					"SELECT COUNT(*) FROM " . self::TABLE . " c {$join} WHERE {$where}"
				)
			);
		}

		// Table name without prefix — project uses vv_ tables directly.
		$sql_base = "SELECT c.*, a.name AS apartment_name, a.apartment_num
			FROM " . self::TABLE . " c {$join} WHERE {$where} {$orderby}";

		if ( $per_page > 0 ) {
			$pgnum  = max( 1, intval( gArrayItem( $filter, 'pgnum', 1 ) ) );
			$offset = $per_page * ( $pgnum - 1 );
			$limit  = $wpdb->prepare( ' LIMIT %d OFFSET %d', $per_page, $offset );
		}

		$rows = $wpdb->get_results( $sql_base . $limit, ARRAY_A );

		if ( $return_total ) {
			return [ 'rows' => is_array( $rows ) ? $rows : [], 'total_rows' => $total_rows ];
		}

		return is_array( $rows ) ? $rows : [];
	}

	public function get_conversion( $id = 0 ) {
		global $wpdb;
		return $wpdb->get_row(
			$wpdb->prepare( 'SELECT * FROM ' . self::TABLE . ' WHERE ID = %d', intval( $id ) ),
			ARRAY_A
		);
	}

	public function get_conversion_by_token( $token ) {
		global $wpdb;
		$token = sanitize_text_field( $token );
		if ( $token === '' ) {
			return [];
		}
		$row = $wpdb->get_row(
			$wpdb->prepare( 'SELECT * FROM ' . self::TABLE . ' WHERE conversion_token = %s LIMIT 1', $token ),
			ARRAY_A
		);
		return is_array( $row ) ? $row : [];
	}

	public function count_host_conversions_this_month( $host_id ) {
		global $wpdb;
		$host_id = intval( $host_id );
		$start   = date( 'Y-m-01 00:00:00' );
		return intval(
			$wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM " . self::TABLE . " WHERE host_id = %d AND dateadded >= %s AND status NOT IN ('draft','cancelled')",
					$host_id,
					$start
				)
			)
		);
	}

	public function host_monthly_stats( $host_id ) {
		$used  = $this->count_host_conversions_this_month( $host_id );
		$limit = self::MONTHLY_LIMIT;
		return [
			'used'      => $used,
			'limit'     => $limit,
			'remaining' => max( 0, $limit - $used ),
		];
	}

	protected function generate_ref() {
		global $wpdb;
		do {
			$ref = 'CONV-' . date( 'Ymd' ) . '-' . strtoupper( wp_generate_password( 4, false, false ) );
			$found = intval(
				$wpdb->get_var(
					$wpdb->prepare( 'SELECT COUNT(*) FROM ' . self::TABLE . ' WHERE conversion_ref = %s', $ref )
				)
			);
		} while ( $found > 0 );
		return $ref;
	}

	protected function generate_unique_promo_code() {
		global $wpdb;
		do {
			$code = 'VS' . strtoupper( wp_generate_password( 8, false, false ) );
			$found = intval(
				$wpdb->get_var(
					$wpdb->prepare( "SELECT COUNT(*) FROM vv_promocodes WHERE UCASE(code) = %s", strtoupper( $code ) )
				)
			);
		} while ( $found > 0 );
		return $code;
	}

	protected function create_promo_for_conversion( $apartment_id ) {
		global $wpdb;

		$code = $this->generate_unique_promo_code();
		$now  = current_time( 'mysql' );

		$wpdb->insert(
			'vv_promocodes',
			[
				'code'           => $code,
				'discount'       => self::DISCOUNT_PCT,
				'apartment_ids'  => wp_json_encode( [ intval( $apartment_id ) ] ),
				'ambassador_id'  => 0,
				'status'         => 'active',
				'dateadded'      => $now,
				'datemodified'   => $now,
			]
		);

		return [
			'id'   => intval( $wpdb->insert_id ),
			'code' => $code,
		];
	}

	protected function apartment_belongs_to_host( $apartment_id, $host_id ) {
		global $wpdb;
		$owner = intval(
			$wpdb->get_var(
				$wpdb->prepare( 'SELECT user_id FROM vv_apartments WHERE ID = %d', intval( $apartment_id ) )
			)
		);
		return $owner === intval( $host_id );
	}

	/**
	 * Create conversion from external booking / availability period.
	 *
	 * @return array|WP_Error Keys: conversion_id, conversion_ref, promo_code, status
	 */
	public function create_from_external_booking( array $args ) {
		global $wpdb;

		if ( ! self::can_manage() ) {
			return new WP_Error( 'forbidden', 'Permission denied.' );
		}

		$host_id      = intval( gArrayItem( $args, 'host_id' ) );
		$apartment_id = intval( gArrayItem( $args, 'apartment_id' ) );
		$guest_email  = sanitize_email( gArrayItem( $args, 'guest_email' ) );

		if ( $host_id <= 0 ) {
			$host_id = get_current_user_id();
		}

		if ( $apartment_id <= 0 || $guest_email === '' ) {
			return new WP_Error( 'invalid', 'Apartment and guest email are required.' );
		}

		if ( ! vv_is_admin_role() && ! $this->apartment_belongs_to_host( $apartment_id, $host_id ) ) {
			return new WP_Error( 'forbidden', 'You can only create conversions for your own apartments.' );
		}

		if ( $this->count_host_conversions_this_month( $host_id ) >= self::MONTHLY_LIMIT ) {
			return new WP_Error( 'limit', sprintf( 'Monthly limit reached (%d conversions per month).', self::MONTHLY_LIMIT ) );
		}

		$platform = sanitize_key( gArrayItem( $args, 'external_platform', 'airbnb' ) );
		if ( ! isset( self::platforms()[ $platform ] ) ) {
			$platform = 'airbnb';
		}

		$check_in  = gArrayItem( $args, 'check_in_date' );
		$check_out = gArrayItem( $args, 'check_out_date' );

		$promo = $this->create_promo_for_conversion( $apartment_id );
		$token = wp_generate_password( 32, false, false );
		$now   = current_time( 'mysql' );

		$data = [
			'conversion_ref'          => $this->generate_ref(),
			'host_id'                 => $host_id,
			'apartment_id'            => $apartment_id,
			'availability_period_id'  => intval( gArrayItem( $args, 'availability_period_id' ) ) ?: null,
			'guest_email'             => $guest_email,
			'guest_name'              => sanitize_text_field( gArrayItem( $args, 'guest_name' ) ),
			'external_platform'       => $platform,
			'external_booking_ref'    => sanitize_text_field( gArrayItem( $args, 'external_booking_ref' ) ),
			'check_in_date'           => $check_in !== '' ? date( 'Y-m-d', strtotime( $check_in ) ) : null,
			'check_out_date'          => $check_out !== '' ? date( 'Y-m-d', strtotime( $check_out ) ) : null,
			'conversion_token'        => $token,
			'promo_code_id'           => $promo['id'],
			'promo_code'              => $promo['code'],
			'discount_pct'            => self::DISCOUNT_PCT,
			'status'                  => 'draft',
			'dateadded'               => $now,
			'datemodified'            => $now,
		];

		$wpdb->insert( self::TABLE, $data );
		$conversion_id = intval( $wpdb->insert_id );

		if ( ! empty( $args['send_invite'] ) ) {
			$conversion = $this->get_conversion( $conversion_id );
			$sent       = $this->send_invite_email( $conversion );
			if ( is_wp_error( $sent ) ) {
				return $sent;
			}
			$expires = date( 'Y-m-d H:i:s', strtotime( '+' . self::EXPIRY_MONTHS . ' months' ) );
			$wpdb->update(
				self::TABLE,
				[
					'status'       => 'sent',
					'expires_at'   => $expires,
					'datemodified' => current_time( 'mysql' ),
				],
				[ 'ID' => $conversion_id ]
			);
			$data['status']     = 'sent';
			$data['expires_at'] = $expires;
		}

		return [
			'conversion_id'  => $conversion_id,
			'conversion_ref' => $data['conversion_ref'],
			'promo_code'     => $data['promo_code'],
			'status'           => $data['status'],
		];
	}

	protected function conversion_redirect_url( $default = '' ) {
		$redirect = esc_url_raw( POST_Request( 'redirect_to' ) );
		if ( $redirect !== '' && strpos( $redirect, '/vv-admin/' ) !== false ) {
			return $redirect;
		}
		return $default !== '' ? $default : vv_admin_url( 'conversions' );
	}

	protected function save_conversion() {
		global $wpdb;

		if ( ! self::can_manage() ) {
			setErrorMsg( 'Permission denied.' );
			wp_redirect( vv_admin_url( 'conversions' ) );
			die();
		}

		$conversion_id = intval( POST_Request( 'conversion_id' ) );
		$host_id       = get_current_user_id();
		if ( vv_is_admin_role() && intval( POST_Request( 'host_id' ) ) > 0 ) {
			$host_id = intval( POST_Request( 'host_id' ) );
		}

		$apartment_id = intval( POST_Request( 'apartment_id' ) );
		if ( ! vv_is_admin_role() && ! $this->apartment_belongs_to_host( $apartment_id, $host_id ) ) {
			setErrorMsg( 'You can only create conversions for your own apartments.' );
			wp_redirect( vv_admin_url( 'conversions' ) );
			die();
		}

		if ( $this->count_host_conversions_this_month( $host_id ) >= self::MONTHLY_LIMIT && $conversion_id <= 0 ) {
			setErrorMsg( sprintf( 'Monthly limit reached (%d conversions per month).', self::MONTHLY_LIMIT ) );
			wp_redirect( vv_admin_url( 'conversions' ) );
			die();
		}

		$guest_email = sanitize_email( POST_Request( 'guest_email' ) );
		$guest_name  = sanitize_text_field( POST_Request( 'guest_name' ) );
		$platform    = sanitize_key( POST_Request( 'external_platform' ) );
		$ext_ref     = sanitize_text_field( POST_Request( 'external_booking_ref' ) );
		$check_in    = sanitize_text_field( POST_Request( 'check_in_date' ) );
		$check_out   = sanitize_text_field( POST_Request( 'check_out_date' ) );

		if ( $apartment_id <= 0 || $guest_email === '' ) {
			setErrorMsg( 'Apartment and guest email are required.' );
			wp_redirect( vv_admin_url( 'conversions' ) );
			die();
		}

		if ( ! isset( self::platforms()[ $platform ] ) ) {
			$platform = 'airbnb';
		}

		$now = current_time( 'mysql' );
		$data = [
			'host_id'               => $host_id,
			'apartment_id'          => $apartment_id,
			'guest_email'           => $guest_email,
			'guest_name'            => $guest_name,
			'external_platform'     => $platform,
			'external_booking_ref'  => $ext_ref,
			'check_in_date'         => $check_in !== '' ? date( 'Y-m-d', strtotime( $check_in ) ) : null,
			'check_out_date'        => $check_out !== '' ? date( 'Y-m-d', strtotime( $check_out ) ) : null,
			'discount_pct'          => self::DISCOUNT_PCT,
			'datemodified'          => $now,
		];

		if ( $conversion_id > 0 ) {
			$existing = $this->get_conversion( $conversion_id );
			if ( ! self::can_access_conversion( $existing ) ) {
				setErrorMsg( 'Permission denied.' );
				wp_redirect( vv_admin_url( 'conversions' ) );
				die();
			}
			if ( ! in_array( gArrayItem( $existing, 'status' ), [ 'draft', 'sent' ], true ) ) {
				setErrorMsg( 'This conversion can no longer be edited.' );
				wp_redirect( vv_admin_url( 'conversions' ) );
				die();
			}
			$wpdb->update( self::TABLE, $data, [ 'ID' => $conversion_id ] );
			setSuccessMsg( 'Conversion updated.' );
			wp_redirect( vv_admin_url( 'conversions' ) );
			die();
		}

		$promo = $this->create_promo_for_conversion( $apartment_id );
		$token = wp_generate_password( 32, false, false );

		$data['conversion_ref']    = $this->generate_ref();
		$data['conversion_token']  = $token;
		$data['promo_code_id']     = $promo['id'];
		$data['promo_code']        = $promo['code'];
		$data['status']            = 'draft';
		$data['dateadded']         = $now;

		$wpdb->insert( self::TABLE, $data );
		$new_id = intval( $wpdb->insert_id );

		setSuccessMsg( 'Conversion created. Send the invite when ready.' );
		wp_redirect( vv_admin_url( 'conversions' ) );
		die();
	}

	protected function send_conversion( $conversion_id = 0 ) {
		global $wpdb;

		if ( $conversion_id <= 0 ) {
			$conversion_id = intval( POST_Request( 'conversion_id' ) );
		}

		$conversion = $this->get_conversion( $conversion_id );
		if ( ! self::can_access_conversion( $conversion ) ) {
			setErrorMsg( 'Permission denied.' );
			wp_redirect( vv_admin_url( 'conversions' ) );
			die();
		}

		if ( in_array( gArrayItem( $conversion, 'status' ), [ 'converted', 'cancelled', 'expired' ], true ) ) {
			setErrorMsg( 'This conversion cannot be sent.' );
			wp_redirect( vv_admin_url( 'conversions' ) );
			die();
		}

		$host_id = intval( gArrayItem( $conversion, 'host_id' ) );
		if ( gArrayItem( $conversion, 'status' ) === 'draft' && $this->count_host_conversions_this_month( $host_id ) >= self::MONTHLY_LIMIT ) {
			setErrorMsg( sprintf( 'Monthly limit reached (%d conversions per month).', self::MONTHLY_LIMIT ) );
			wp_redirect( vv_admin_url( 'conversions' ) );
			die();
		}

		$sent = $this->send_invite_email( $conversion );
		if ( is_wp_error( $sent ) ) {
			setErrorMsg( $sent->get_error_message() );
			wp_redirect( vv_admin_url( 'conversions' ) );
			die();
		}

		$expires = date( 'Y-m-d H:i:s', strtotime( '+' . self::EXPIRY_MONTHS . ' months' ) );
		$wpdb->update(
			self::TABLE,
			[
				'status'       => 'sent',
				'expires_at'   => $expires,
				'datemodified' => current_time( 'mysql' ),
			],
			[ 'ID' => $conversion_id ]
		);

		setSuccessMsg( 'Conversion invite sent to guest.' );
		wp_redirect( $this->conversion_redirect_url() );
		die();
	}

	protected function resend_conversion( $conversion_id ) {
		$this->send_conversion( $conversion_id );
	}

	protected function cancel_conversion( $conversion_id ) {
		global $wpdb;

		$conversion = $this->get_conversion( $conversion_id );
		if ( ! self::can_access_conversion( $conversion ) ) {
			setErrorMsg( 'Permission denied.' );
			wp_redirect( vv_admin_url( 'conversions' ) );
			die();
		}

		if ( gArrayItem( $conversion, 'status' ) === 'converted' ) {
			setErrorMsg( 'Cannot cancel a converted booking.' );
			wp_redirect( vv_admin_url( 'conversions' ) );
			die();
		}

		$wpdb->update(
			self::TABLE,
			[
				'status'       => 'cancelled',
				'datemodified' => current_time( 'mysql' ),
			],
			[ 'ID' => $conversion_id ]
		);

		if ( intval( gArrayItem( $conversion, 'promo_code_id' ) ) > 0 ) {
			$wpdb->update(
				'vv_promocodes',
				[ 'status' => 'inactive', 'datemodified' => current_time( 'mysql' ) ],
				[ 'ID' => intval( $conversion['promo_code_id'] ) ]
			);
		}

		setSuccessMsg( 'Conversion cancelled.' );
		wp_redirect( vv_admin_url( 'conversions' ) );
		die();
	}

	public static function conversion_link( $token ) {
		return self::stay_welcome_url( $token );
	}

	public static function stay_welcome_url( $token = '' ) {
		$url = home_url( '/stay-welcome/' );
		if ( $token !== '' ) {
			$url = add_query_arg( 'token', rawurlencode( $token ), $url );
		}
		return $url;
	}

	/**
	 * Store conversion booking context in session for checkout.
	 */
	public static function apply_conversion_session( array $conversion ) {
		if ( ! session_id() ) {
			session_start();
		}

		$apartment_id = intval( gArrayItem( $conversion, 'apartment_id' ) );
		$existing     = gArrayItem( $_SESSION, 'BOOKING_DATA_FRONT', [] );
		if ( ! is_array( $existing ) ) {
			$existing = [];
		}

		$booking_data = [
			'apartment_id'        => $apartment_id,
			'promo_code'          => gArrayItem( $conversion, 'promo_code' ),
			'promo_code_discount' => floatval( gArrayItem( $conversion, 'discount_pct', self::DISCOUNT_PCT ) ),
			'promo_code_added'    => 1,
			'conversion_id'       => intval( gArrayItem( $conversion, 'ID' ) ),
		];

		if ( gArrayItem( $conversion, 'check_in_date' ) ) {
			$booking_data['check_in_date'] = gArrayItem( $conversion, 'check_in_date' );
		}
		if ( gArrayItem( $conversion, 'check_out_date' ) ) {
			$booking_data['check_out_date'] = gArrayItem( $conversion, 'check_out_date' );
		}
		if ( empty( $existing['num_adults'] ) ) {
			$booking_data['num_adults'] = 2;
		}

		$_SESSION['BOOKING_DATA_FRONT'] = array_merge( $existing, $booking_data );
		$_SESSION['VV_CONVERSION_ID']    = intval( gArrayItem( $conversion, 'ID' ) );
	}

	public static function mark_opened( $conversion_id ) {
		global $wpdb;

		$conversion_id = intval( $conversion_id );
		if ( $conversion_id <= 0 ) {
			return;
		}

		$row = $wpdb->get_row(
			$wpdb->prepare( 'SELECT status FROM ' . self::TABLE . ' WHERE ID = %d', $conversion_id ),
			ARRAY_A
		);

		if ( gArrayItem( $row, 'status' ) === 'sent' ) {
			$wpdb->update(
				self::TABLE,
				[
					'status'       => 'opened',
					'opened_at'    => current_time( 'mysql' ),
					'datemodified' => current_time( 'mysql' ),
				],
				[ 'ID' => $conversion_id ]
			);
		}
	}

	/**
	 * Link conversion invite to a guest account and persist entry-source metadata.
	 */
	public static function link_conversion_to_user( $conversion_id, $user_id ) {
		global $wpdb;

		$conversion_id = intval( $conversion_id );
		$user_id       = intval( $user_id );
		if ( $conversion_id <= 0 || $user_id <= 0 ) {
			return false;
		}

		$conversion = $wpdb->get_row(
			$wpdb->prepare( 'SELECT * FROM ' . self::TABLE . ' WHERE ID = %d', $conversion_id ),
			ARRAY_A
		);
		if ( ! is_array( $conversion ) || empty( $conversion['ID'] ) ) {
			return false;
		}

		$now    = current_time( 'mysql' );
		$status = gArrayItem( $conversion, 'status' );
		$update = [
			'guest_user_id' => $user_id,
			'datemodified'  => $now,
		];
		if ( in_array( $status, [ 'sent', 'opened' ], true ) ) {
			$update['status'] = 'registered';
		}

		$wpdb->update( self::TABLE, $update, [ 'ID' => $conversion_id ] );

		update_user_meta( $user_id, 'vv_entry_source', 'conversion' );
		update_user_meta( $user_id, 'vv_referral_code', gArrayItem( $conversion, 'promo_code' ) );
		update_user_meta( $user_id, 'vv_conversion_token', gArrayItem( $conversion, 'conversion_token' ) );
		update_user_meta( $user_id, 'vv_converted_from_platform', gArrayItem( $conversion, 'external_platform' ) );
		update_user_meta( $user_id, 'vv_converted_by_host_id', intval( gArrayItem( $conversion, 'host_id' ) ) );
		if ( gArrayItem( $conversion, 'expires_at' ) ) {
			update_user_meta( $user_id, 'vv_conversion_token_expires', gArrayItem( $conversion, 'expires_at' ) );
		}
		update_user_meta( $user_id, 'vv_conversion_discount_used', '0' );

		if ( class_exists( 'vvRoles' ) ) {
			vvRoles::add_user_role( $user_id, vvRoles::ROLE_GUEST );
		}

		return true;
	}

	public static function get_active_conversion_for_user( $user_id = 0 ) {
		global $wpdb;

		$user_id = intval( $user_id );
		if ( $user_id <= 0 ) {
			$user_id = get_current_user_id();
		}
		if ( $user_id <= 0 ) {
			return [];
		}

		$row = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM " . self::TABLE . " WHERE guest_user_id = %d AND status IN ('sent','opened','registered') ORDER BY dateadded DESC LIMIT 1",
				$user_id
			),
			ARRAY_A
		);

		if ( ! is_array( $row ) || empty( $row['ID'] ) ) {
			return [];
		}

		if ( is_wp_error( self::validate_conversion_active( $row ) ) ) {
			return [];
		}

		return $row;
	}

	protected function send_invite_email( array $conversion ) {
		$apartment = vv_get_apartment( intval( gArrayItem( $conversion, 'apartment_id' ) ) );
		$host      = get_user_by( 'ID', intval( gArrayItem( $conversion, 'host_id' ) ) );
		$link      = self::conversion_link( gArrayItem( $conversion, 'conversion_token' ) );

		$data = [
			'GUEST_NAME'       => gArrayItem( $conversion, 'guest_name' ) ?: 'Guest',
			'GUEST_EMAIL'      => gArrayItem( $conversion, 'guest_email' ),
			'CONVERSION_LINK'  => $link,
			'DISCOUNT_PCT'     => intval( gArrayItem( $conversion, 'discount_pct', self::DISCOUNT_PCT ) ),
			'APARTMENT_NAME'   => gArrayItem( $apartment, 'name' ),
			'HOST_NAME'        => $host ? $host->display_name : 'Your host',
			'PROMO_CODE'       => gArrayItem( $conversion, 'promo_code' ),
			'CHECK_IN'         => gArrayItem( $conversion, 'check_in_date' ),
			'CHECK_OUT'        => gArrayItem( $conversion, 'check_out_date' ),
			'CONTACT_EMAIL'    => get_option( 'admin_email' ),
		];

		$templates = new vvEmailTemplates();
		if ( $templates->get_email_template_by_code( 'conversion_invite' ) ) {
			$sent = $templates->send_by_code( 'conversion_invite', gArrayItem( $conversion, 'guest_email' ), $data );
			if ( ! $sent ) {
				return new WP_Error( 'email_failed', 'Could not send conversion email.' );
			}
			return true;
		}

		$subject = sprintf( 'Book %s directly on Vietstays — %d%% off', gArrayItem( $apartment, 'name' ), self::DISCOUNT_PCT );
		$body    = '<p>Hi ' . esc_html( $data['GUEST_NAME'] ) . ',</p>'
			. '<p>Your host invites you to rebook your stay on Vietstays and receive <strong>' . intval( self::DISCOUNT_PCT ) . '% off</strong> your next direct booking.</p>'
			. '<p><a href="' . esc_url( $link ) . '">Claim your discount and book</a></p>'
			. '<p>Your promo code: <strong>' . esc_html( gArrayItem( $conversion, 'promo_code' ) ) . '</strong></p>'
			. '<p>This offer expires in ' . intval( self::EXPIRY_MONTHS ) . ' months and can only be used once.</p>';

		$headers = [ 'Content-Type: text/html; charset=UTF-8' ];
		if ( ! wp_mail( gArrayItem( $conversion, 'guest_email' ), $subject, $body, $headers ) ) {
			return new WP_Error( 'email_failed', 'Could not send conversion email.' );
		}

		return true;
	}

	protected function handle_landing() {
		$token = sanitize_text_field( GET_Request( 'token' ) );
		if ( $token === '' ) {
			$token = sanitize_text_field( GET_Request( 'vv_conversion' ) );
		}

		if ( $token === '' ) {
			setErrorMsg( 'This conversion link is invalid.' );
			wp_redirect( home_url( '/' ) );
			die();
		}

		wp_redirect( self::stay_welcome_url( $token ) );
		die();
	}

	protected function handle_conversion_start_booking() {
		$token      = sanitize_text_field( POST_Request( 'conversion_token' ) );
		$conversion = $this->get_conversion_by_token( $token );

		if ( empty( $conversion['ID'] ) ) {
			setErrorMsg( 'This conversion link is invalid.' );
			wp_redirect( home_url( '/' ) );
			die();
		}

		$valid = self::validate_conversion_active( $conversion );
		if ( is_wp_error( $valid ) ) {
			setErrorMsg( $valid->get_error_message() );
			wp_redirect( self::stay_welcome_url( $token ) );
			die();
		}

		self::apply_conversion_session( $conversion );

		if ( is_user_logged_in() ) {
			self::link_conversion_to_user( intval( $conversion['ID'] ), get_current_user_id() );
		}

		setSuccessMsg(
			sprintf(
				'Your %d%% Vietstays discount is applied. Complete your booking below.',
				intval( gArrayItem( $conversion, 'discount_pct', self::DISCOUNT_PCT ) )
			)
		);

		wp_redirect( get_bloginfo( 'url' ) . '/booking' );
		die();
	}

	protected function handle_conversion_guest_account() {
		$token      = sanitize_text_field( POST_Request( 'conversion_token' ) );
		$mode       = sanitize_key( POST_Request( 'account_mode' ) );
		$conversion = $this->get_conversion_by_token( $token );

		if ( empty( $conversion['ID'] ) ) {
			setErrorMsg( 'This conversion link is invalid.' );
			wp_redirect( home_url( '/' ) );
			die();
		}

		$valid = self::validate_conversion_active( $conversion );
		if ( is_wp_error( $valid ) ) {
			setErrorMsg( $valid->get_error_message() );
			wp_redirect( self::stay_welcome_url( $token ) );
			die();
		}

		$invite_email = strtolower( trim( gArrayItem( $conversion, 'guest_email' ) ) );
		$user_id      = 0;

		if ( $mode === 'login' ) {
			$login    = trim( POST_Request( 'login' ) );
			$password = POST_Request( 'password' );

			if ( strtolower( $login ) !== $invite_email ) {
				setErrorMsg( 'Please sign in with the email address that received this invite.' );
				wp_redirect( self::stay_welcome_url( $token ) );
				die();
			}

			$user = wp_signon(
				[
					'user_login'    => $login,
					'user_password' => $password,
					'remember'      => true,
				],
				is_ssl()
			);

			if ( is_wp_error( $user ) ) {
				setErrorMsg( 'Invalid email or password.' );
				wp_redirect( self::stay_welcome_url( $token ) );
				die();
			}

			$user_id = intval( $user->ID );
		} else {
			$email      = sanitize_email( POST_Request( 'email' ) );
			$password   = POST_Request( 'password' );
			$firstname  = sanitize_text_field( POST_Request( 'firstname' ) );
			$lastname   = sanitize_text_field( POST_Request( 'lastname' ) );

			if ( $email === '' || strtolower( $email ) !== $invite_email ) {
				setErrorMsg( 'Please register with the email address that received this invite.' );
				wp_redirect( self::stay_welcome_url( $token ) );
				die();
			}

			if ( strlen( $password ) < 8 ) {
				setErrorMsg( 'Password must be at least 8 characters.' );
				wp_redirect( self::stay_welcome_url( $token ) );
				die();
			}

			if ( email_exists( $email ) ) {
				setErrorMsg( 'An account already exists for this email. Please sign in instead.' );
				wp_redirect( self::stay_welcome_url( $token ) );
				die();
			}

			$user_id = wp_create_user( $email, $password, $email );
			if ( is_wp_error( $user_id ) ) {
				setErrorMsg( $user_id->get_error_message() );
				wp_redirect( self::stay_welcome_url( $token ) );
				die();
			}

			wp_update_user(
				[
					'ID'           => $user_id,
					'first_name'   => $firstname,
					'last_name'    => $lastname,
					'display_name' => trim( $firstname . ' ' . $lastname ),
				]
			);
			update_user_meta( $user_id, 'vv_user_level', 'customer' );
			update_user_meta( $user_id, 'user_role', 'guest' );
			update_user_meta( $user_id, 'vv_email_verified', 1 );

			wp_set_current_user( $user_id );
			wp_set_auth_cookie( $user_id, true );
		}

		self::link_conversion_to_user( intval( $conversion['ID'] ), $user_id );
		self::apply_conversion_session( $conversion );
		self::mark_opened( intval( $conversion['ID'] ) );

		setSuccessMsg( 'Welcome! Your stay portal and discount are ready.' );
		wp_redirect( vv_users_url( 'conversion/stay' ) );
		die();
	}

	public function maybe_expire_conversions() {
		global $wpdb;

		$now = current_time( 'mysql' );
		$wpdb->query(
			$wpdb->prepare(
				"UPDATE " . self::TABLE . " SET status = 'expired', datemodified = %s
				WHERE status IN ('sent','opened','registered') AND expires_at IS NOT NULL AND expires_at < %s",
				$now,
				$now
			)
		);
	}

	public static function validate_conversion_active( array $conversion ) {
		if ( gArrayItem( $conversion, 'status' ) === 'converted' ) {
			return new WP_Error( 'conversion_used', 'This discount has already been used.' );
		}
		if ( gArrayItem( $conversion, 'status' ) === 'cancelled' ) {
			return new WP_Error( 'conversion_cancelled', 'This conversion invite was cancelled.' );
		}
		if ( gArrayItem( $conversion, 'status' ) === 'expired' ) {
			return new WP_Error( 'conversion_expired', 'This conversion offer has expired.' );
		}
		if ( gArrayItem( $conversion, 'expires_at' ) && strtotime( $conversion['expires_at'] ) < time() ) {
			return new WP_Error( 'conversion_expired', 'This conversion offer has expired.' );
		}
		if ( ! in_array( gArrayItem( $conversion, 'status' ), [ 'sent', 'opened', 'registered' ], true ) ) {
			return new WP_Error( 'conversion_invalid', 'This conversion link is not active.' );
		}
		return true;
	}

	public static function validate_promo_code( array $promo_row ) {
		global $wpdb;

		$code = strtoupper( gArrayItem( $promo_row, 'code' ) );
		$conversion = $wpdb->get_row(
			$wpdb->prepare(
				'SELECT * FROM ' . self::TABLE . ' WHERE promo_code = %s OR promo_code_id = %d LIMIT 1',
				$code,
				intval( gArrayItem( $promo_row, 'ID' ) )
			),
			ARRAY_A
		);

		if ( ! is_array( $conversion ) || empty( $conversion['ID'] ) ) {
			return true;
		}

		return self::validate_conversion_active( $conversion );
	}

	public static function mark_booking_converted( $booking_id, $promo_code = '' ) {
		global $wpdb;

		$promo_code = strtoupper( trim( (string) $promo_code ) );
		if ( $promo_code === '' || $booking_id <= 0 ) {
			return;
		}

		$conversion = $wpdb->get_row(
			$wpdb->prepare(
				'SELECT * FROM ' . self::TABLE . ' WHERE promo_code = %s LIMIT 1',
				$promo_code
			),
			ARRAY_A
		);

		if ( ! is_array( $conversion ) || empty( $conversion['ID'] ) ) {
			return;
		}

		$now = current_time( 'mysql' );
		$guest_user_id = intval(
			$wpdb->get_var(
				$wpdb->prepare( 'SELECT user_id FROM vv_bookings WHERE ID = %d', intval( $booking_id ) )
			)
		);

		$update = [
			'status'       => 'converted',
			'booking_id'   => intval( $booking_id ),
			'converted_at' => $now,
			'datemodified' => $now,
		];
		if ( $guest_user_id > 0 ) {
			$update['guest_user_id'] = $guest_user_id;
		}

		$wpdb->update(
			self::TABLE,
			$update,
			[ 'ID' => intval( $conversion['ID'] ) ]
		);

		if ( $guest_user_id > 0 ) {
			update_user_meta( $guest_user_id, 'vv_conversion_discount_used', '1' );
		}

		if ( intval( gArrayItem( $conversion, 'promo_code_id' ) ) > 0 ) {
			$wpdb->update(
				'vv_promocodes',
				[ 'status' => 'inactive', 'datemodified' => $now ],
				[ 'ID' => intval( $conversion['promo_code_id'] ) ]
			);
		}
	}
}

function vv_conversion_platforms() {
	return vvConversions::platforms();
}

function vv_get_conversions( $filter = [] ) {
	global $visitVietnam;
	if ( isset( $visitVietnam->conversions_class ) ) {
		return $visitVietnam->conversions_class->get_conversions( $filter );
	}
	return [];
}

function vv_get_conversion( $id = 0 ) {
	global $visitVietnam;
	if ( isset( $visitVietnam->conversions_class ) ) {
		return $visitVietnam->conversions_class->get_conversion( $id );
	}
	return [];
}

function vv_host_conversion_stats() {
	global $visitVietnam;
	if ( isset( $visitVietnam->conversions_class ) ) {
		return $visitVietnam->conversions_class->host_monthly_stats( get_current_user_id() );
	}
	return [ 'used' => 0, 'limit' => vvConversions::MONTHLY_LIMIT, 'remaining' => vvConversions::MONTHLY_LIMIT ];
}
