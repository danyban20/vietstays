<?php

class vvHostApplications {

	const SESSION_KEY = 'HOST_APPLICATION_DATA';
	const TABLE       = 'vv_host_applications';
	const META_USER_ROLE = 'user_role';
	const META_STATUS    = 'status';
	const ROLE_HOST      = 'host';
	const STATUS_PENDING = 'pending-approval';
	const META_EMAIL_VERIFIED       = 'vv_email_verified';
	const META_ACTIVATION_KEY       = 'vv_host_activation_key';
	const META_ACTIVATION_EXPIRES   = 'vv_host_activation_expires';
	const META_APPROVED_AT          = 'vv_host_approved_at';
	const META_REMINDER_SENT        = 'vv_host_verification_reminder_sent';
	const META_SHOW_WELCOME         = 'vv_host_show_welcome';
	const META_STATUS_KEY           = 'vv_host_application_status_key';
	const REJECTION_COMMENT_MAX     = 150;
	const REVIEW_BUSINESS_DAYS      = 3;

	public function __construct() {
		add_action( 'init', [ $this, 'maybe_create_table' ] );
		add_action( 'init', [ $this, 'maybe_send_verification_reminders' ] );
	}

	public function maybe_create_table() {
		global $wpdb;

		$table   = self::TABLE;
		$charset = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table} (
			ID bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			application_ref varchar(32) NOT NULL,
			user_id bigint(20) unsigned DEFAULT NULL,
			applicant_type varchar(32) NOT NULL DEFAULT 'multi_property',
			full_name varchar(255) NOT NULL,
			home_city varchar(255) NOT NULL,
			email varchar(255) NOT NULL,
			phone varchar(64) NOT NULL,
			address text DEFAULT NULL,
			num_properties int(11) DEFAULT NULL,
			districts text DEFAULT NULL,
			description longtext DEFAULT NULL,
			primary_city_id bigint(20) unsigned DEFAULT NULL,
			company_name varchar(255) DEFAULT NULL,
			portfolio_url varchar(512) DEFAULT NULL,
			years_managing varchar(64) DEFAULT NULL,
			guest_profile varchar(32) DEFAULT NULL,
			platforms_used longtext DEFAULT NULL,
			apartment_id bigint(20) unsigned DEFAULT NULL,
			property_address text DEFAULT NULL,
			property_type varchar(32) DEFAULT NULL,
			property_kind varchar(32) DEFAULT NULL,
			property_beds int(11) DEFAULT NULL,
			property_bathrooms int(11) DEFAULT NULL,
			property_price_daily decimal(10,2) DEFAULT NULL,
			property_amenities longtext DEFAULT NULL,
			property_images longtext DEFAULT NULL,
			status varchar(32) NOT NULL DEFAULT 'submitted',
			rejection_reason varchar(64) DEFAULT NULL,
			rejection_comment varchar(150) DEFAULT NULL,
			status_history longtext DEFAULT NULL,
			assigned_to bigint(20) unsigned DEFAULT NULL,
			dateadded datetime NOT NULL,
			datemodified datetime NOT NULL,
			PRIMARY KEY (ID),
			UNIQUE KEY application_ref (application_ref),
			KEY email (email),
			KEY status (status)
		) {$charset};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	public function post_actions( $action = '' ) {
		if ( in_array( $action, [ 'save_host_application_status', 'save_partner_profile', 'host_welcome_dismiss', 'host_welcome_show_again' ], true ) ) {
			if ( strpos( gArrayItem( $_SERVER, 'REQUEST_URI' ), '/vv-admin' ) === false ) {
				return;
			}
			if ( $action === 'save_host_application_status' ) {
				$this->save_admin_status();
			} elseif ( $action === 'save_partner_profile' ) {
				$this->save_partner_profile();
			} elseif ( $action === 'host_welcome_dismiss' ) {
				$this->dismiss_host_welcome();
			} elseif ( $action === 'host_welcome_show_again' ) {
				$this->show_host_welcome_again();
			}
			return;
		}

		if ( $action === 'vv_host_application_step' ) {
			$this->save_step();
		} elseif ( $action === 'vv_host_application_submit' ) {
			$this->submit_application();
		} elseif ( $action === 'vv_host_activate_account' ) {
			$this->activate_host_account();
		}
	}

	private function get_current_backend_role() {
		$user = wp_get_current_user();
		if ( ! $user || ! $user->ID ) {
			return '';
		}
		return vv_get_user_role( $user );
	}

	private function can_manage_applications() {
		if ( current_user_can( 'manage_options' ) ) {
			return true;
		}
		return in_array( $this->get_current_backend_role(), [ 'administrator', 'sales_support' ], true );
	}

	public function get_actions( $action = '' ) {
		if ( $action === 'vv_host_application_reset' ) {
			unset( $_SESSION[ self::SESSION_KEY ] );
			wp_redirect( $this->form_url() );
			die();
		}

		if ( $action === 'host_application_status' ) {
			$this->render_application_status_page();
		}
	}

	public static function get_session_data() {
		$data = gArrayItem( $_SESSION, self::SESSION_KEY );
		return is_array( $data ) ? $data : [];
	}

	public static function form_url() {
		$page_id = intval( get_option( 'vv_host_application_page_id', 0 ) );
		if ( $page_id > 0 ) {
			return get_permalink( $page_id );
		}
		return trailingslashit( get_bloginfo( 'url' ) ) . 'host-application/';
	}

	public static function activation_url() {
		$page_id = intval( get_option( 'vv_host_activate_page_id', 0 ) );
		if ( $page_id > 0 ) {
			return get_permalink( $page_id );
		}
		return trailingslashit( get_bloginfo( 'url' ) ) . 'host-activate/';
	}

	public static function rejection_reasons() {
		return [
			'incomplete_application'  => 'Incomplete application',
			'portfolio_not_suitable'  => 'Portfolio does not match our criteria',
			'insufficient_experience' => 'Insufficient management experience',
			'duplicate_application'   => 'Duplicate application',
			'other'                   => 'Other',
		];
	}

	public static function rejection_reason_label( $key = '' ) {
		return gArrayItem( self::rejection_reasons(), $key, $key );
	}

	public function save_step() {
		$data           = self::get_session_data();
		$step           = intval( POST_Request( 'form_step' ) );
		$applicant_type = gArrayItem( $data, 'applicant_type', '' );
		$kind           = self::step_at( $step, $applicant_type );

		if ( $kind === 'type' ) {
			$applicant_type = POST_Request( 'applicant_type' );
			if ( in_array( $applicant_type, [ 'single_property', 'multi_property' ], true ) ) {
				$data['applicant_type'] = $applicant_type;
				if ( $applicant_type === 'single_property' ) {
					$data['property_kind']  = 'apartment';
					$data['num_properties'] = 1;
				}
			}
		}

		if ( $kind === 'space_type' ) {
			$data['property_space_type'] = sanitize_text_field( POST_Request( 'property_space_type' ) );
			$data['property_type']       = $data['property_space_type'];
			$errors                      = $this->validate_step_kind( $kind, $data );
		} elseif ( $kind === 'location' ) {
			$data['property_address']     = sanitize_textarea_field( POST_Request( 'property_address' ) );
			$data['property_city_id']     = intval( POST_Request( 'property_city_id' ) );
			$data['property_district_id'] = intval( POST_Request( 'property_district_id' ) );
			$errors                       = $this->validate_step_kind( $kind, $data );
		} elseif ( $kind === 'basics' ) {
			$data['property_max_guests']  = intval( POST_Request( 'property_max_guests' ) );
			$data['property_rooms']       = intval( POST_Request( 'property_rooms' ) );
			$data['property_beds']        = intval( POST_Request( 'property_beds' ) );
			$data['property_bathrooms']   = intval( POST_Request( 'property_bathrooms' ) );
			$errors                       = $this->validate_step_kind( $kind, $data );
		} elseif ( $kind === 'amenities' ) {
			$data['property_amenities'] = array_values( array_filter( array_map( 'intval', (array) POST_Request( 'property_amenities' ) ) ) );
			$errors                     = $this->validate_step_kind( $kind, $data );
		} elseif ( $kind === 'photos' ) {
			$new_images = $this->upload_property_images( $_FILES );
			$existing   = self::decode_json_field( gArrayItem( $data, 'property_images' ) );
			if ( ! is_array( $existing ) ) {
				$existing = [];
			}
			$data['property_images'] = count( $new_images ) > 0 ? array_merge( $existing, $new_images ) : $existing;
			$errors                  = $this->validate_step_kind( $kind, $data );
		} elseif ( $kind === 'listing' ) {
			$data['property_name']        = sanitize_text_field( POST_Request( 'property_name' ) );
			$data['property_description']   = sanitize_textarea_field( POST_Request( 'property_description' ) );
			$errors                         = $this->validate_step_kind( $kind, $data );
		} elseif ( $kind === 'pricing' ) {
			$data['property_price_daily'] = vv_currency_to_decimal( POST_Request( 'property_price_daily' ) );
			$errors                        = $this->validate_step_kind( $kind, $data );
		}

		if ( isset( $errors ) && count( $errors ) > 0 ) {
			$_SESSION['HOST_APPLICATION_ERRORS'] = $errors;
			wp_redirect( add_query_arg( 'step', $step, self::form_url() ) );
			die();
		}

		if ( $kind === 'contact' ) {
			$city_id = intval( POST_Request( 'primary_city_id' ) );
			$data['full_name']        = sanitize_text_field( POST_Request( 'full_name' ) );
			$data['email']            = sanitize_email( POST_Request( 'email' ) );
			$data['phone']            = sanitize_text_field( POST_Request( 'phone' ) );
			$data['primary_city_id']  = $city_id;
			$data['home_city']        = self::get_city_name( $city_id );
			if ( self::is_single_applicant( $data ) ) {
				$data['num_properties'] = 1;
			} else {
				$data['num_properties'] = intval( POST_Request( 'num_properties' ) );
			}
			$data['company_name']     = sanitize_text_field( POST_Request( 'company_name' ) );
			$data['portfolio_url']    = esc_url_raw( POST_Request( 'portfolio_url' ) );
			$data['description']      = sanitize_textarea_field( POST_Request( 'description' ) );
			if ( self::is_single_applicant( $data ) ) {
				$data['num_properties']  = 1;
				$data['primary_city_id'] = intval( gArrayItem( $data, 'property_city_id' ) );
				$data['home_city']       = self::get_city_name( intval( $data['primary_city_id'] ) );
				$district_id             = intval( gArrayItem( $data, 'property_district_id' ) );
				$data['districts']       = $district_id > 0 ? [ $district_id ] : [];
			} else {
				$data['districts'] = self::filter_district_ids_for_city(
					$city_id,
					array_map( 'intval', (array) POST_Request( 'districts' ) )
				);
			}
		}

		if ( $kind === 'experience' ) {
			$data['years_managing']  = sanitize_text_field( POST_Request( 'years_managing' ) );
			$data['guest_profile']   = sanitize_text_field( POST_Request( 'guest_profile' ) );
			$data['platforms_used']  = array_values( array_intersect(
				array_map( 'sanitize_text_field', (array) POST_Request( 'platforms_used' ) ),
				array_keys( self::platform_options() )
			) );
		}

		$_SESSION[ self::SESSION_KEY ] = $data;

		$next = $this->next_step( $step, gArrayItem( $data, 'applicant_type' ) );
		wp_redirect( add_query_arg( 'step', $next, self::form_url() ) );
		die();
	}

	public function submit_application() {
		global $wpdb;

		$data   = self::get_session_data();
		$errors = $this->validate_for_submit( $data );

		if ( POST_Request( 'confirm_application' ) !== '1' ) {
			$errors[] = self::is_single_applicant( $data )
				? 'Please confirm you understand your apartment stays hidden until host approval.'
				: 'Please confirm you understand this is an application and listings are created only after approval.';
		}

		if ( count( $errors ) > 0 ) {
			$_SESSION['HOST_APPLICATION_ERRORS'] = $errors;
			wp_redirect( add_query_arg( 'step', self::summary_step( gArrayItem( $data, 'applicant_type' ) ), self::form_url() ) );
			die();
		}

		$user_result = $this->create_or_update_pending_host_user( $data );
		if ( is_wp_error( $user_result ) ) {
			$_SESSION['HOST_APPLICATION_ERRORS'] = [ $user_result->get_error_message() ];
			wp_redirect( add_query_arg( 'step', self::summary_step( gArrayItem( $data, 'applicant_type' ) ), self::form_url() ) );
			die();
		}

		$user_id = intval( $user_result );

		$now = current_time( 'mysql' );
		$row = $this->build_application_row( $data, $user_id, $this->generate_application_ref(), $now );

		$inserted = $wpdb->insert( self::TABLE, $row );
		if ( ! $inserted ) {
			$_SESSION['HOST_APPLICATION_ERRORS'] = [ 'Could not save your application. Please try again.' ];
			wp_redirect( add_query_arg( 'step', self::summary_step( gArrayItem( $data, 'applicant_type' ) ), self::form_url() ) );
			die();
		}

		$application_id = intval( $wpdb->insert_id );
		update_user_meta( $user_id, 'vv_host_application_id', $application_id );
		update_user_meta( $user_id, 'vv_host_application_ref', $row['application_ref'] );
		update_user_meta( $user_id, 'vv_host_application_data', vv_jsonEncode( $data ) );

		$this->ensure_application_status_key( $user_id );
		$this->append_status_history( $application_id, 'submitted', $user_id, 'Applicant' );

		if ( self::is_single_applicant( $data ) ) {
			$apartments = new vvApartments();
			$apartment_id = $apartments->create_from_host_application( $user_id, $data, 'pending' );
			if ( $apartment_id > 0 ) {
				$wpdb->update(
					self::TABLE,
					[ 'apartment_id' => $apartment_id ],
					[ 'ID' => $application_id ]
				);
				$row['apartment_id'] = $apartment_id;
				update_user_meta( $user_id, 'vv_host_application_apartment_id', $apartment_id );
			}
		}

		$this->send_submitted_email( $row );
		unset( $_SESSION[ self::SESSION_KEY ] );

		wp_redirect( add_query_arg(
			[
				'submitted' => 1,
				'ref'       => rawurlencode( $row['application_ref'] ),
			],
			self::form_url()
		) );
		die();
	}

	/**
	 * Create or update a wp_users entry for a host applicant.
	 * Account type is stored in vv_user_level (partner = host).
	 */
	private function create_or_update_pending_host_user( $data ) {
		$email = sanitize_email( gArrayItem( $data, 'email' ) );
		if ( ! isEmailFormat( $email ) ) {
			return new WP_Error( 'invalid_email', 'A valid email address is required.' );
		}

		$full_name  = trim( gArrayItem( $data, 'full_name' ) );
		$name_parts = preg_split( '/\s+/', $full_name, 2 );
		$firstname  = gArrayItem( $name_parts, 0 );
		$lastname   = gArrayItem( $name_parts, 1, '' );

		$existing   = get_user_by( 'email', $email );
		$user_id    = 0;
		$is_new     = false;

		if ( $existing ) {
			$user_id    = intval( $existing->ID );
			$user_level = get_user_meta( $user_id, 'vv_user_level', true );

			if ( in_array( $user_level, [ 'admin' ], true ) ) {
				return new WP_Error( 'invalid_account', 'This email belongs to an admin account and cannot be used for a host application.' );
			}

			$host_status = get_user_meta( $user_id, 'host_verification_status', true );
			if ( $user_level === 'partner' && in_array( $host_status, [ 'verified', 'superhost-verified' ], true ) ) {
				return new WP_Error( 'already_host', 'This email is already registered as an approved host.' );
			}
		} else {
			$password = wp_generate_password( 12, false );
			$user_id  = wp_insert_user( [
				'user_login'   => $email,
				'user_email'   => $email,
				'user_pass'    => $password,
				'role'         => 'subscriber',
				'display_name' => $full_name,
				'first_name'   => $firstname,
				'last_name'    => $lastname,
				'user_nicename'=> sanitize_title( $firstname . '-' . $lastname ),
			] );

			if ( is_wp_error( $user_id ) ) {
				return $user_id;
			}

			$is_new = true;
		}

		update_user_meta( $user_id, 'first_name', $firstname );
		update_user_meta( $user_id, 'last_name', $lastname );

		$user_address = json_decode( get_user_meta( $user_id, 'user_address', true ), true );
		if ( ! is_array( $user_address ) ) {
			$user_address = [];
		}
		$user_address['phone_number'] = sanitize_text_field( gArrayItem( $data, 'phone' ) );
		$user_address['city']         = sanitize_text_field( gArrayItem( $data, 'home_city' ) );
		update_user_meta( $user_id, 'user_address', vv_jsonEncode( $user_address ) );

		update_user_meta( $user_id, self::META_USER_ROLE, self::ROLE_HOST );
		update_user_meta( $user_id, self::META_STATUS, self::STATUS_PENDING );
		update_user_meta( $user_id, 'vv_user_level', 'partner' );
		update_user_meta( $user_id, 'host_verification_status', 'pending' );
		update_user_meta( $user_id, self::META_EMAIL_VERIFIED, 0 );
		delete_user_meta( $user_id, self::META_ACTIVATION_KEY );
		delete_user_meta( $user_id, self::META_ACTIVATION_EXPIRES );
		delete_user_meta( $user_id, self::META_APPROVED_AT );
		delete_user_meta( $user_id, self::META_REMINDER_SENT );
		update_user_meta( $user_id, 'vv_host_home_city', sanitize_text_field( gArrayItem( $data, 'home_city' ) ) );
		update_user_meta( $user_id, 'vv_host_applicant_type', sanitize_text_field( gArrayItem( $data, 'applicant_type' ) ) );

		vvRoles::set_user_roles( $user_id, [ vvRoles::ROLE_GUEST, vvRoles::ROLE_HOST ] );

		return $user_id;
	}

	public static function application_status_to_user_status( $application_status = '' ) {
		$map = [
			'submitted'    => self::STATUS_PENDING,
			'under_review' => 'under-review',
			'approved'     => 'approved',
			'rejected'     => 'rejected',
			'activated'    => 'active',
		];

		return gArrayItem( $map, $application_status, self::STATUS_PENDING );
	}

	public static function user_status_labels() {
		return [
			self::STATUS_PENDING => 'Pending approval',
			'under-review'       => 'Under review',
			'approved'           => 'Approved',
			'rejected'           => 'Rejected',
			'active'             => 'Active',
		];
	}

	public static function user_status_label( $status = '' ) {
		$labels = self::user_status_labels();
		if ( isset( $labels[ $status ] ) ) {
			return $labels[ $status ];
		}

		return vv_get_status_name_text( str_replace( '-', ' ', $status ) );
	}

	public function sync_user_from_application_status( $user_id, $application_status ) {
		$user_id = intval( $user_id );
		if ( $user_id <= 0 ) {
			return;
		}

		$user_status = self::application_status_to_user_status( $application_status );
		update_user_meta( $user_id, self::META_USER_ROLE, self::ROLE_HOST );
		update_user_meta( $user_id, self::META_STATUS, $user_status );

		if ( $application_status === 'activated' ) {
			update_user_meta( $user_id, 'host_verification_status', 'verified' );
			update_user_meta( $user_id, self::META_EMAIL_VERIFIED, 1 );
			delete_user_meta( $user_id, self::META_ACTIVATION_KEY );
			delete_user_meta( $user_id, self::META_ACTIVATION_EXPIRES );
		} elseif ( $application_status === 'approved' ) {
			update_user_meta( $user_id, 'host_verification_status', 'pending' );
			update_user_meta( $user_id, self::META_EMAIL_VERIFIED, 0 );
			update_user_meta( $user_id, self::META_APPROVED_AT, current_time( 'mysql' ) );
			update_user_meta( $user_id, self::META_REMINDER_SENT, 0 );
		} elseif ( $application_status === 'rejected' ) {
			update_user_meta( $user_id, 'host_verification_status', 'unverified' );
			delete_user_meta( $user_id, self::META_ACTIVATION_KEY );
			delete_user_meta( $user_id, self::META_ACTIVATION_EXPIRES );
		} else {
			update_user_meta( $user_id, 'host_verification_status', 'pending' );
		}
	}

	/**
	 * Host applicants and hosts stored as WordPress users (user_role + status meta).
	 */
	public function get_host_applicant_users( $filter = [] ) {
		$this->backfill_host_user_meta();

		$meta_query = [
			'relation' => 'AND',
			[
				'key'     => self::META_USER_ROLE,
				'value'   => self::ROLE_HOST,
				'compare' => '=',
			],
		];

		$status = gArrayItem( $filter, 'status' );
		if ( $status === '' && gArrayItem( $filter, 'include_all_statuses' ) !== true ) {
			$status = self::STATUS_PENDING;
		}
		if ( $status !== '' ) {
			$meta_query[] = [
				'key'     => self::META_STATUS,
				'value'   => $status,
				'compare' => '=',
			];
		}

		$args = [
			'number'     => intval( gArrayItem( $filter, 'per_page', 50 ) ),
			'paged'      => max( 1, intval( gArrayItem( $filter, 'pgnum', 1 ) ) ),
			'meta_query' => $meta_query,
			'orderby'    => 'registered',
			'order'      => 'DESC',
		];

		$search = trim( gArrayItem( $filter, 'search' ) );
		if ( $search !== '' ) {
			$args['search']         = '*' . esc_attr( $search ) . '*';
			$args['search_columns'] = [ 'user_email', 'display_name', 'user_login' ];
		}

		$query = new WP_User_Query( $args );
		$users = $query->get_results();
		if ( ! is_array( $users ) ) {
			$users = [];
		}

		$rows = [];
		foreach ( $users as $user ) {
			$user_id = intval( $user->ID );
			$this->maybe_backfill_host_user_meta( $user_id );
			$application = $this->get_application_by_user_id( $user_id );

			$rows[] = [
				'user_id'          => $user_id,
				'email'            => $user->user_email,
				'full_name'        => $user->display_name,
				'user_registered'  => $user->user_registered,
				'user_role'        => get_user_meta( $user_id, self::META_USER_ROLE, true ),
				'status'           => vv_get_user_account_status( $user_id ),
				'application_ref'  => is_array( $application ) ? gArrayItem( $application, 'application_ref' ) : get_user_meta( $user_id, 'vv_host_application_ref', true ),
				'application_id'   => is_array( $application ) ? intval( gArrayItem( $application, 'ID' ) ) : intval( get_user_meta( $user_id, 'vv_host_application_id', true ) ),
				'applicant_type'   => is_array( $application ) ? gArrayItem( $application, 'applicant_type' ) : get_user_meta( $user_id, 'vv_host_applicant_type', true ),
				'num_properties'   => is_array( $application ) ? gArrayItem( $application, 'num_properties' ) : '',
				'home_city'        => is_array( $application ) ? gArrayItem( $application, 'home_city' ) : '',
				'dateadded'        => is_array( $application ) ? gArrayItem( $application, 'dateadded' ) : $user->user_registered,
			];
		}

		return [
			'total_users' => intval( $query->get_total() ),
			'users'       => $rows,
		];
	}

	private function maybe_backfill_host_user_meta( $user_id ) {
		$user_id = intval( $user_id );
		if ( $user_id <= 0 ) {
			return;
		}

		if ( get_user_meta( $user_id, self::META_USER_ROLE, true ) !== '' ) {
			return;
		}

		if ( get_user_meta( $user_id, 'vv_user_level', true ) !== 'partner' ) {
			return;
		}

		update_user_meta( $user_id, self::META_USER_ROLE, self::ROLE_HOST );

		if ( get_user_meta( $user_id, self::META_STATUS, true ) === '' ) {
			$host_status = get_user_meta( $user_id, 'host_verification_status', true );
			if ( in_array( $host_status, [ 'verified', 'superhost-verified' ], true ) ) {
				update_user_meta( $user_id, self::META_STATUS, 'active' );
			} elseif ( $host_status === 'unverified' ) {
				update_user_meta( $user_id, self::META_STATUS, 'rejected' );
			} else {
				update_user_meta( $user_id, self::META_STATUS, self::STATUS_PENDING );
			}
		}
	}

	private function backfill_host_user_meta() {
		$query = new WP_User_Query( [
			'number'     => 200,
			'meta_query' => [
				'relation' => 'AND',
				[
					'key'     => 'vv_user_level',
					'value'   => 'partner',
					'compare' => '=',
				],
				[
					'key'     => self::META_USER_ROLE,
					'compare' => 'NOT EXISTS',
				],
			],
		] );

		foreach ( $query->get_results() as $user ) {
			$this->maybe_backfill_host_user_meta( intval( $user->ID ) );
		}
	}

	public function get_application_by_user_id( $user_id = 0 ) {
		global $wpdb;

		$user_id = intval( $user_id );
		if ( $user_id <= 0 ) {
			return null;
		}

		$application = $wpdb->get_row(
			$wpdb->prepare(
				'SELECT * FROM ' . self::TABLE . ' WHERE user_id = %d ORDER BY dateadded DESC LIMIT 1',
				$user_id
			),
			ARRAY_A
		);

		if ( is_array( $application ) && intval( gArrayItem( $application, 'ID' ) ) > 0 ) {
			return $application;
		}

		$application_id = intval( get_user_meta( $user_id, 'vv_host_application_id', true ) );
		if ( $application_id > 0 ) {
			$application = $this->get_application( $application_id );
			if ( is_array( $application ) && intval( gArrayItem( $application, 'ID' ) ) > 0 ) {
				return $this->link_application_to_user( $application, $user_id );
			}
		}

		$user = get_user_by( 'ID', $user_id );
		if ( $user && is_email( $user->user_email ) ) {
			$application = $wpdb->get_row(
				$wpdb->prepare(
					'SELECT * FROM ' . self::TABLE . ' WHERE email = %s ORDER BY dateadded DESC LIMIT 1',
					$user->user_email
				),
				ARRAY_A
			);
			if ( is_array( $application ) && intval( gArrayItem( $application, 'ID' ) ) > 0 ) {
				return $this->link_application_to_user( $application, $user_id );
			}
		}

		return null;
	}

	/**
	 * Resolve application for admin view — links orphaned rows or creates one from the WP user.
	 */
	public function ensure_application_for_user( $user_id = 0 ) {
		$user_id = intval( $user_id );
		if ( $user_id <= 0 ) {
			return null;
		}

		$application = $this->get_application_by_user_id( $user_id );
		if ( is_array( $application ) && intval( gArrayItem( $application, 'ID' ) ) > 0 ) {
			return $application;
		}

		return $this->create_application_from_user( $user_id );
	}

	private function link_application_to_user( $application, $user_id ) {
		global $wpdb;

		$user_id = intval( $user_id );
		if ( ! is_array( $application ) || $user_id <= 0 ) {
			return $application;
		}

		$application_id = intval( gArrayItem( $application, 'ID' ) );
		if ( intval( gArrayItem( $application, 'user_id' ) ) !== $user_id ) {
			$wpdb->update(
				self::TABLE,
				[
					'user_id'      => $user_id,
					'datemodified' => current_time( 'mysql' ),
				],
				[ 'ID' => $application_id ]
			);
			$application['user_id'] = $user_id;
		}

		update_user_meta( $user_id, 'vv_host_application_id', $application_id );
		if ( gArrayItem( $application, 'application_ref' ) !== '' ) {
			update_user_meta( $user_id, 'vv_host_application_ref', $application['application_ref'] );
		}

		return $application;
	}

	private function create_application_from_user( $user_id ) {
		global $wpdb;

		$user = get_user_by( 'ID', intval( $user_id ) );
		if ( ! $user ) {
			return null;
		}

		$this->maybe_create_table();

		$stored = json_decode( get_user_meta( $user_id, 'vv_host_application_data', true ), true );
		if ( ! is_array( $stored ) ) {
			$stored = [];
		}

		$user_address = json_decode( get_user_meta( $user_id, 'user_address', true ), true );
		if ( ! is_array( $user_address ) ) {
			$user_address = [];
		}

		$now = current_time( 'mysql' );
		$ref = get_user_meta( $user_id, 'vv_host_application_ref', true );
		if ( $ref === '' ) {
			$ref = $this->generate_application_ref();
		}

		$row = [
			'application_ref'  => $ref,
			'user_id'          => $user_id,
			'applicant_type'   => gArrayItem( $stored, 'applicant_type', get_user_meta( $user_id, 'vv_host_applicant_type', true ) ?: 'multi_property' ),
			'full_name'        => gArrayItem( $stored, 'full_name', $user->display_name ),
			'home_city'        => gArrayItem( $stored, 'home_city', get_user_meta( $user_id, 'vv_host_home_city', true ) ),
			'email'            => gArrayItem( $stored, 'email', $user->user_email ),
			'phone'            => gArrayItem( $stored, 'phone', gArrayItem( $user_address, 'phone_number' ) ),
			'num_properties'   => gArrayItem( $stored, 'num_properties' ) ?: null,
			'primary_city_id'  => intval( gArrayItem( $stored, 'primary_city_id' ) ),
			'districts'        => vv_jsonEncode( gArrayItem( $stored, 'districts', [] ) ),
			'description'      => gArrayItem( $stored, 'description' ),
			'company_name'     => gArrayItem( $stored, 'company_name' ),
			'portfolio_url'    => gArrayItem( $stored, 'portfolio_url' ),
			'years_managing'   => gArrayItem( $stored, 'years_managing' ),
			'guest_profile'    => gArrayItem( $stored, 'guest_profile' ),
			'platforms_used'   => vv_jsonEncode( gArrayItem( $stored, 'platforms_used', [] ) ),
			'status'           => 'submitted',
			'dateadded'        => $now,
			'datemodified'     => $now,
		];

		$inserted = $wpdb->insert( self::TABLE, $row );
		if ( ! $inserted ) {
			return null;
		}

		$application_id = intval( $wpdb->insert_id );
		update_user_meta( $user_id, 'vv_host_application_id', $application_id );
		update_user_meta( $user_id, 'vv_host_application_ref', $ref );

		return $this->get_application( $application_id );
	}

	public function get_application( $id = 0 ) {
		global $wpdb;
		return $wpdb->get_row(
			$wpdb->prepare( 'SELECT * FROM ' . self::TABLE . ' WHERE ID = %d', intval( $id ) ),
			ARRAY_A
		);
	}

	public function get_applications( $filter = [] ) {
		global $wpdb;

		$where  = '1=1';
		$params = [];

		if ( gArrayItem( $filter, 'status' ) !== '' ) {
			$where   .= ' AND status = %s';
			$params[] = $filter['status'];
		}

		if ( gArrayItem( $filter, 'search' ) !== '' ) {
			$search   = '%' . $wpdb->esc_like( $filter['search'] ) . '%';
			$where   .= ' AND (full_name LIKE %s OR email LIKE %s OR application_ref LIKE %s OR phone LIKE %s)';
			$params[] = $search;
			$params[] = $search;
			$params[] = $search;
			$params[] = $search;
		}

		$sql = 'SELECT * FROM ' . self::TABLE . ' WHERE ' . $where . ' ORDER BY dateadded DESC';

		if ( count( $params ) > 0 ) {
			$sql = $wpdb->prepare( $sql, $params );
		}

		return $wpdb->get_results( $sql, ARRAY_A );
	}

	public static function status_labels() {
		return [
			'submitted'     => 'Submitted',
			'under_review'  => 'Under review',
			'approved'      => 'Approved',
			'rejected'      => 'Rejected',
			'activated'     => 'Activated',
		];
	}

	public static function status_label( $status ) {
		$labels = self::status_labels();
		return gArrayItem( $labels, $status, ucwords( str_replace( '_', ' ', $status ) ) );
	}

	public static function decode_json_field( $value ) {
		if ( is_array( $value ) ) {
			return $value;
		}
		if ( ! is_string( $value ) || $value === '' ) {
			return [];
		}
		$decoded = json_decode( $value, true );
		return is_array( $decoded ) ? $decoded : [];
	}

	public function save_admin_status() {
		global $wpdb;

		if ( ! $this->can_manage_applications() ) {
			setErrorMsg( 'You do not have permission to update applications.' );
			wp_redirect( vv_admin_url( 'host-applications' ) );
			die();
		}

		$logged_user_role = $this->get_current_backend_role();

		$id      = intval( POST_Request( 'application_id' ) );
		$user_id = intval( POST_Request( 'user_id' ) );
		$status  = sanitize_text_field( POST_Request( 'status' ) );
		$allowed = array_keys( self::status_labels() );

		if ( $id <= 0 && $user_id > 0 ) {
			$application = $this->ensure_application_for_user( $user_id );
			$id          = is_array( $application ) ? intval( gArrayItem( $application, 'ID' ) ) : 0;
		}

		if ( $id <= 0 || ! in_array( $status, $allowed, true ) ) {
			setErrorMsg( 'Invalid application or status.' );
			wp_redirect( vv_admin_url( 'host-applications' ) );
			die();
		}

		$previous = $this->get_application( $id );
		$old_status = is_array( $previous ) ? gArrayItem( $previous, 'status' ) : '';

		if ( $status === 'activated' ) {
			setErrorMsg( 'Activated status is set automatically when the host verifies their email.' );
			wp_redirect( vv_admin_url( 'host-applications/view' ) . '?id=' . intval( gArrayItem( $previous, 'user_id' ) ) );
			die();
		}

		if ( $logged_user_role === 'sales_support' && ! in_array( $status, [ 'under_review' ], true ) ) {
			setErrorMsg( 'Sales/Support can only set applications to Under review. Final approval requires an administrator.' );
			wp_redirect( vv_admin_url( 'host-applications/view' ) . '?id=' . intval( gArrayItem( $previous, 'user_id' ) ) );
			die();
		}

		$update = [
			'status'       => $status,
			'datemodified' => current_time( 'mysql' ),
		];

		if ( $status === 'rejected' ) {
			$reason_key = sanitize_text_field( POST_Request( 'rejection_reason' ) );
			$update['rejection_reason']  = $reason_key;
			$update['rejection_comment'] = substr( sanitize_text_field( POST_Request( 'rejection_comment' ) ), 0, self::REJECTION_COMMENT_MAX );
		}

		if ( in_array( $logged_user_role, [ 'administrator', 'sales_support' ], true ) ) {
			$update['assigned_to'] = get_current_user_id();
		}

		$wpdb->update( self::TABLE, $update, [ 'ID' => $id ] );

		$application = $this->get_application( $id );
		if ( is_array( $application ) && intval( gArrayItem( $application, 'user_id' ) ) > 0 ) {
			$app_user_id = intval( $application['user_id'] );
			$this->sync_user_from_application_status( $app_user_id, $status );
			if ( $old_status !== $status ) {
				$this->append_status_history( $id, $status, get_current_user_id() );
				$this->send_status_change_email( $application, $old_status, $status );
				$apartments = new vvApartments();
				$apartments->sync_for_host_application_status( $app_user_id, $status );
			}
		}

		setSuccessMsg( 'Application status updated.' );

		$user_id = is_array( $application ) ? intval( gArrayItem( $application, 'user_id' ) ) : 0;
		$referer = gArrayItem( $_SERVER, 'HTTP_REFERER' );
		if ( $user_id > 0 && ( strpos( $referer, '/partners/edit' ) !== false || strpos( $referer, '/account' ) !== false ) ) {
			wp_redirect( vv_admin_self_profile_url( $user_id ) );
		} elseif ( $user_id > 0 ) {
			wp_redirect( vv_admin_url( 'host-applications/view' ) . '?id=' . $user_id );
		} else {
			wp_redirect( vv_admin_url( 'host-applications/view' ) . '?id=' . $id );
		}
		die();
	}

	public function save_partner_profile() {
		global $wpdb, $logged_user_role;

		$application_id = intval( POST_Request( 'application_id' ) );
		$user_id        = intval( POST_Request( 'user_id' ) );
		$application    = $this->get_application( $application_id );

		if ( ! is_array( $application ) || intval( gArrayItem( $application, 'ID' ) ) <= 0 ) {
			setErrorMsg( 'Partner profile not found.' );
			wp_redirect( vv_admin_url( 'partners' ) );
			die();
		}

		if ( $user_id <= 0 ) {
			$user_id = intval( gArrayItem( $application, 'user_id' ) );
		}

		if ( $user_id <= 0 || intval( gArrayItem( $application, 'user_id' ) ) !== $user_id ) {
			setErrorMsg( 'Invalid partner profile.' );
			wp_redirect( vv_admin_url( 'partners' ) );
			die();
		}

		$current_user_id = get_current_user_id();
		if ( $current_user_id !== $user_id && $logged_user_role !== 'administrator' ) {
			setErrorMsg( 'You cannot edit this profile.' );
			wp_redirect( vv_admin_self_profile_url( $user_id ) );
			die();
		}

		$primary_city_id = intval( POST_Request( 'primary_city_id' ) );
		$districts       = POST_Request( 'districts' );
		if ( ! is_array( $districts ) ) {
			$districts = [];
		}
		$districts = self::filter_district_ids_for_city( $primary_city_id, $districts );

		$home_city = self::get_city_name( $primary_city_id );
		if ( $home_city === '' ) {
			$home_city = sanitize_text_field( POST_Request( 'home_city' ) );
		}

		$full_name = sanitize_text_field( POST_Request( 'full_name' ) );
		$email     = sanitize_email( POST_Request( 'email' ) );

		if ( trim( $full_name ) === '' ) {
			setErrorMsg( 'Full name is required.' );
			wp_redirect( vv_admin_self_profile_url( $user_id ) );
			die();
		}

		if ( ! isEmailFormat( $email ) ) {
			setErrorMsg( 'A valid email address is required.' );
			wp_redirect( vv_admin_self_profile_url( $user_id ) );
			die();
		}

		$existing_email = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT ID FROM {$wpdb->users} WHERE user_email = %s AND ID != %d",
				strtolower( $email ),
				$user_id
			)
		);
		if ( intval( $existing_email ) > 0 ) {
			setErrorMsg( 'That email address is already in use.' );
			wp_redirect( vv_admin_self_profile_url( $user_id ) );
			die();
		}

		$years_managing = sanitize_text_field( POST_Request( 'years_managing' ) );
		if ( ! array_key_exists( $years_managing, self::years_managing_options() ) ) {
			$years_managing = '';
		}

		$guest_profile = sanitize_text_field( POST_Request( 'guest_profile' ) );
		if ( ! array_key_exists( $guest_profile, self::guest_profile_options() ) ) {
			$guest_profile = '';
		}

		$platforms_used = array_values( array_intersect(
			array_map( 'sanitize_text_field', (array) POST_Request( 'platforms_used' ) ),
			array_keys( self::platform_options() )
		) );

		$update = [
			'full_name'         => $full_name,
			'email'             => $email,
			'address'           => sanitize_textarea_field( POST_Request( 'address' ) ),
			'home_city'         => $home_city,
			'primary_city_id'   => $primary_city_id > 0 ? $primary_city_id : null,
			'num_properties'    => max( 1, intval( POST_Request( 'num_properties' ) ) ),
			'districts'         => wp_json_encode( array_values( $districts ) ),
			'company_name'      => sanitize_text_field( POST_Request( 'company_name' ) ),
			'portfolio_url'     => esc_url_raw( POST_Request( 'portfolio_url' ) ),
			'description'       => sanitize_textarea_field( POST_Request( 'description' ) ),
			'years_managing'    => $years_managing,
			'guest_profile'     => $guest_profile,
			'platforms_used'    => wp_json_encode( $platforms_used ),
			'datemodified'      => current_time( 'mysql' ),
		];

		$wpdb->update( self::TABLE, $update, [ 'ID' => $application_id ] );

		$name_parts = preg_split( '/\s+/', $full_name, 2 );
		$firstname  = gArrayItem( $name_parts, 0 );
		$lastname   = gArrayItem( $name_parts, 1, '' );

		wp_update_user( [
			'ID'           => $user_id,
			'user_email'   => $email,
			'display_name' => $full_name,
			'first_name'   => $firstname,
			'last_name'    => $lastname,
		] );

		$user_address = json_decode( get_user_meta( $user_id, 'user_address', true ), true );
		if ( ! is_array( $user_address ) ) {
			$user_address = [];
		}
		$user_address['address_1'] = $update['address'];
		$user_address['city']      = $update['home_city'];
		update_user_meta( $user_id, 'user_address', vv_jsonEncode( $user_address ) );

		if ( POST_Request( 'admin_locale' ) !== '' && class_exists( 'vvI18n' ) ) {
			vvI18n::save_user_locale_preference( $user_id, POST_Request( 'admin_locale' ) );
		}

		if ( POST_Request( 'host_currency' ) !== '' ) {
			vv_save_user_host_currency( $user_id, POST_Request( 'host_currency' ) );
		}

		setSuccessMsg( 'Partner profile updated.' );
		wp_redirect( vv_admin_self_profile_url( $user_id ) );
		die();
	}

	private function validate_for_submit( $data ) {
		$errors = [];

		if ( ! in_array( gArrayItem( $data, 'applicant_type' ), [ 'single_property', 'multi_property' ], true ) ) {
			$errors[] = 'Please select whether you manage one or multiple apartments.';
		}
		if ( trim( gArrayItem( $data, 'full_name' ) ) === '' ) {
			$errors[] = 'Full name is required.';
		}
		if ( intval( gArrayItem( $data, 'primary_city_id' ) ) <= 0 && trim( gArrayItem( $data, 'home_city' ) ) === '' ) {
			$errors[] = 'Primary city is required.';
		}
		if ( ! isEmailFormat( gArrayItem( $data, 'email' ) ) ) {
			$errors[] = 'A valid email address is required.';
		}
		if ( trim( gArrayItem( $data, 'phone' ) ) === '' ) {
			$errors[] = 'Phone number is required.';
		}
		if ( intval( gArrayItem( $data, 'num_properties' ) ) < 1 ) {
			$errors[] = 'Number of apartments is required (minimum 1).';
		}
		if ( self::is_single_applicant( $data ) ) {
			$errors = array_merge( $errors, $this->validate_listing_complete( $data ) );
		}
		if ( ! is_array( gArrayItem( $data, 'districts' ) ) || count( array_filter( $data['districts'] ) ) === 0 ) {
			$errors[] = 'Select at least one district within your primary city.';
		}
		if ( trim( gArrayItem( $data, 'description' ) ) === '' ) {
			$errors[] = 'Please describe yourself as a property manager and your portfolio.';
		}

		$city_id = intval( gArrayItem( $data, 'primary_city_id' ) );
		if ( $city_id > 0 && is_array( gArrayItem( $data, 'districts' ) ) ) {
			$valid = self::filter_district_ids_for_city( $city_id, $data['districts'] );
			if ( count( $valid ) === 0 ) {
				$errors[] = 'Selected districts must belong to your primary city.';
			}
		}

		return $errors;
	}

	private function build_application_row( array $data, $user_id, $application_ref, $timestamp ) {
		$city_id = intval( gArrayItem( $data, 'primary_city_id' ) );
		if ( self::is_single_applicant( $data ) && $city_id <= 0 ) {
			$city_id = intval( gArrayItem( $data, 'property_city_id' ) );
		}

		$row = [
			'application_ref'   => $application_ref,
			'user_id'           => intval( $user_id ),
			'applicant_type'    => $data['applicant_type'],
			'full_name'         => $data['full_name'],
			'home_city'         => gArrayItem( $data, 'home_city', self::get_city_name( $city_id ) ),
			'email'             => $data['email'],
			'phone'             => $data['phone'],
			'num_properties'    => self::is_single_applicant( $data ) ? 1 : intval( gArrayItem( $data, 'num_properties' ) ),
			'primary_city_id'   => $city_id > 0 ? $city_id : null,
			'districts'         => vv_jsonEncode( gArrayItem( $data, 'districts', [] ) ),
			'description'       => gArrayItem( $data, 'description' ),
			'company_name'      => gArrayItem( $data, 'company_name' ),
			'portfolio_url'     => gArrayItem( $data, 'portfolio_url' ),
			'years_managing'    => gArrayItem( $data, 'years_managing' ),
			'guest_profile'     => gArrayItem( $data, 'guest_profile' ),
			'platforms_used'    => vv_jsonEncode( gArrayItem( $data, 'platforms_used', [] ) ),
			'status'            => 'submitted',
			'dateadded'         => $timestamp,
			'datemodified'      => $timestamp,
		];

		if ( self::is_single_applicant( $data ) ) {
			$row['property_address']       = gArrayItem( $data, 'property_address' );
			$row['property_kind']          = gArrayItem( $data, 'property_kind', self::default_property_kind() );
			$row['property_type']          = gArrayItem( $data, 'property_space_type', gArrayItem( $data, 'property_type' ) );
			$row['property_beds']          = intval( gArrayItem( $data, 'property_beds' ) );
			$row['property_bathrooms']     = intval( gArrayItem( $data, 'property_bathrooms' ) );
			$row['property_price_daily']   = floatval( gArrayItem( $data, 'property_price_daily' ) );
			$row['property_amenities']     = vv_jsonEncode( gArrayItem( $data, 'property_amenities', [] ) );
			$row['property_images']        = vv_jsonEncode( gArrayItem( $data, 'property_images', [] ) );
		}

		return $row;
	}

	private function generate_application_ref() {
		global $wpdb;

		$year = date( 'Y' );
		$like = '#HR-' . $year . '-%';
		$count = intval( $wpdb->get_var(
			$wpdb->prepare(
				'SELECT COUNT(*) FROM ' . self::TABLE . ' WHERE application_ref LIKE %s',
				$like
			)
		) );

		return sprintf( '#HR-%s-%04d', $year, $count + 1 );
	}

	private function send_submitted_email( $application ) {
		$this->send_host_template(
			'host_application_submitted',
			gArrayItem( $application, 'email' ),
			$this->application_email_tokens( $application )
		);
	}

	public function generate_activation_key( $user_id ) {
		$user_id = intval( $user_id );
		$key     = wp_generate_password( 48, false, false );
		update_user_meta( $user_id, self::META_ACTIVATION_KEY, wp_hash_password( $key ) );
		update_user_meta( $user_id, self::META_ACTIVATION_KEY . '_plain', $key );
		update_user_meta( $user_id, self::META_ACTIVATION_EXPIRES, time() + ( 7 * DAY_IN_SECONDS ) );
		return $key;
	}

	public function validate_activation_key( $key = '' ) {
		global $wpdb;

		$key = sanitize_text_field( $key );
		if ( $key === '' ) {
			return new WP_Error( 'invalid_key', 'Invalid activation link.' );
		}

		$users = get_users( [
			'meta_key'   => self::META_ACTIVATION_KEY . '_plain',
			'meta_value' => $key,
			'number'     => 1,
		] );

		if ( ! is_array( $users ) || count( $users ) === 0 ) {
			return new WP_Error( 'invalid_key', 'This activation link is invalid or has already been used.' );
		}

		$user    = $users[0];
		$user_id = intval( $user->ID );
		$expires = intval( get_user_meta( $user_id, self::META_ACTIVATION_EXPIRES, true ) );
		if ( $expires > 0 && time() > $expires ) {
			return new WP_Error( 'expired_key', 'This activation link has expired. Please contact ' . vv_admin_contact_email() . ' for a new link.' );
		}

		$application = $this->get_application_by_user_id( $user_id );
		if ( ! is_array( $application ) ) {
			return new WP_Error( 'missing_application', 'Application record not found.' );
		}

		if ( gArrayItem( $application, 'status' ) !== 'approved' && gArrayItem( $application, 'status' ) !== 'activated' ) {
			return new WP_Error( 'not_approved', 'This application has not been approved yet.' );
		}

		$application['email']     = $user->user_email;
		$application['full_name'] = $user->display_name;
		$application['user_id']   = $user_id;

		return $application;
	}

	public function activate_host_account() {
		global $wpdb;

		$key      = sanitize_text_field( POST_Request( 'activation_key' ) );
		$password = POST_Request( 'password' );
		$confirm  = POST_Request( 'password_confirm' );
		$errors   = [];

		$application = $this->validate_activation_key( $key );
		if ( is_wp_error( $application ) ) {
			$errors[] = $application->get_error_message();
		}

		if ( strlen( $password ) < 8 ) {
			$errors[] = 'Password must be at least 8 characters.';
		}
		if ( $password !== $confirm ) {
			$errors[] = 'Passwords do not match.';
		}

		if ( count( $errors ) > 0 ) {
			$_SESSION['HOST_ACTIVATE_ERRORS'] = $errors;
			wp_redirect( add_query_arg( 'key', rawurlencode( $key ), self::activation_url() ) );
			die();
		}

		$user_id = intval( $application['user_id'] );
		wp_set_password( $password, $user_id );

		update_user_meta( $user_id, self::META_EMAIL_VERIFIED, 1 );
		update_user_meta( $user_id, self::META_STATUS, 'active' );
		update_user_meta( $user_id, 'host_verification_status', 'verified' );
		update_user_meta( $user_id, self::META_SHOW_WELCOME, 1 );
		delete_user_meta( $user_id, self::META_ACTIVATION_KEY );
		delete_user_meta( $user_id, self::META_ACTIVATION_KEY . '_plain' );
		delete_user_meta( $user_id, self::META_ACTIVATION_EXPIRES );

		$wpdb->update(
			self::TABLE,
			[
				'status'       => 'activated',
				'datemodified' => current_time( 'mysql' ),
			],
			[ 'ID' => intval( $application['ID'] ) ]
		);

		$this->append_status_history( intval( $application['ID'] ), 'activated', $user_id, 'Host (email verified)' );

		$apartments = new vvApartments();
		$apartments->sync_for_host_application_status( $user_id, 'activated' );

		$this->send_host_welcome_email( $user_id, $application );

		setSuccessMsg( 'Your host account is activated. You can now sign in to the host portal.' );
		wp_redirect( vv_admin_url( 'login' ) );
		die();
	}

	private function send_status_change_email( $application, $old_status, $new_status ) {
		if ( $old_status === $new_status || ! is_array( $application ) ) {
			return;
		}

		if ( $new_status === 'under_review' ) {
			$this->send_under_review_email( $application );
		} elseif ( $new_status === 'approved' ) {
			$this->send_approved_email( $application );
		} elseif ( $new_status === 'rejected' ) {
			$this->send_rejected_email( $application );
		}
	}

	private function send_under_review_email( $application ) {
		$this->send_host_template(
			'host_application_under_review',
			gArrayItem( $application, 'email' ),
			$this->application_email_tokens( $application )
		);
	}

	private function send_approved_email( $application ) {
		$user_id = intval( gArrayItem( $application, 'user_id' ) );
		$key     = $this->generate_activation_key( $user_id );
		$link    = add_query_arg( 'key', rawurlencode( $key ), self::activation_url() );

		$tokens = $this->application_email_tokens( $application );
		$tokens['ACTIVATION_LINK'] = esc_url( $link );

		$this->send_host_template(
			'host_application_approved',
			gArrayItem( $application, 'email' ),
			$tokens
		);
	}

	private function send_rejected_email( $application ) {
		$reason_key = gArrayItem( $application, 'rejection_reason' );
		$reason     = self::rejection_reason_label( $reason_key );
		$comment    = gArrayItem( $application, 'rejection_comment' );

		$tokens = $this->application_email_tokens( $application );
		$tokens['REJECTION_REASON_BLOCK']  = ( $reason !== '' )
			? '<p><strong>Reason:</strong> ' . esc_html( $reason ) . '</p>'
			: '';
		$tokens['REJECTION_COMMENT_BLOCK'] = ( $comment !== '' )
			? '<p>' . esc_html( $comment ) . '</p>'
			: '';

		$this->send_host_template(
			'host_application_rejected',
			gArrayItem( $application, 'email' ),
			$tokens
		);
	}

	private function send_host_welcome_email( $user_id, $application ) {
		$user = get_user_by( 'ID', intval( $user_id ) );
		if ( ! $user ) {
			return;
		}

		$portal_link = esc_url( vv_admin_url( 'login' ) );
		$tokens      = $this->application_email_tokens( $application );
		$tokens['FULL_NAME']         = esc_html( $user->display_name );
		$tokens['HOST_PORTAL_LINK']  = $portal_link;

		$this->send_host_template( 'host_welcome', $user->user_email, $tokens );
	}

	public function maybe_send_verification_reminders() {
		if ( get_transient( 'vv_host_verification_reminder_run' ) ) {
			return;
		}
		set_transient( 'vv_host_verification_reminder_run', 1, HOUR_IN_SECONDS );

		$users = get_users( [
			'number'     => 50,
			'meta_query' => [
				'relation' => 'AND',
				[ 'key' => self::META_USER_ROLE, 'value' => self::ROLE_HOST, 'compare' => '=' ],
				[ 'key' => self::META_STATUS, 'value' => 'approved', 'compare' => '=' ],
				[ 'key' => self::META_EMAIL_VERIFIED, 'value' => '1', 'compare' => '!=' ],
				[ 'key' => self::META_REMINDER_SENT, 'value' => '1', 'compare' => '!=' ],
			],
		] );

		foreach ( $users as $user ) {
			$user_id     = intval( $user->ID );
			$approved_at = get_user_meta( $user_id, self::META_APPROVED_AT, true );
			if ( $approved_at === '' ) {
				continue;
			}
			if ( ( time() - strtotime( $approved_at ) ) < ( 48 * HOUR_IN_SECONDS ) ) {
				continue;
			}

			$application = $this->get_application_by_user_id( $user_id );
			if ( ! is_array( $application ) ) {
				continue;
			}

			$key  = get_user_meta( $user_id, self::META_ACTIVATION_KEY . '_plain', true );
			if ( $key === '' ) {
				$key = $this->generate_activation_key( $user_id );
			}
			$link = add_query_arg( 'key', rawurlencode( $key ), self::activation_url() );

			$link = add_query_arg( 'key', rawurlencode( $key ), self::activation_url() );

			$tokens = [
				'FULL_NAME'       => esc_html( $user->display_name ),
				'ACTIVATION_LINK' => esc_url( $link ),
			];

			$this->send_host_template( 'host_activation_reminder', $user->user_email, $tokens );
			update_user_meta( $user_id, self::META_REMINDER_SENT, 1 );
		}
	}

	private function application_email_tokens( $application ) {
		$contact  = vv_admin_contact_email();
		$user_id  = intval( gArrayItem( $application, 'user_id' ) );
		$ref      = gArrayItem( $application, 'application_ref' );
		$status_link = '';

		if ( $user_id > 0 && $ref !== '' ) {
			$status_key  = $this->ensure_application_status_key( $user_id );
			$status_link = self::application_status_url( $ref, $status_key );
		}

		return [
			'FULL_NAME'       => esc_html( gArrayItem( $application, 'full_name' ) ),
			'APPLICATION_REF' => esc_html( $ref ),
			'CONTACT_EMAIL'   => esc_html( $contact ),
			'STATUS_LINK'     => esc_url( $status_link ),
		];
	}

	public static function application_status_url( $application_ref, $status_key ) {
		return add_query_arg(
			[
				'vv_action' => 'host_application_status',
				'ref'       => rawurlencode( $application_ref ),
				'key'       => rawurlencode( $status_key ),
			],
			home_url( '/' )
		);
	}

	private function ensure_application_status_key( $user_id ) {
		$user_id = intval( $user_id );
		if ( $user_id <= 0 ) {
			return '';
		}

		$key = get_user_meta( $user_id, self::META_STATUS_KEY, true );
		if ( $key === '' ) {
			$key = wp_generate_password( 32, false, false );
			update_user_meta( $user_id, self::META_STATUS_KEY, $key );
		}

		return $key;
	}

	public function get_application_by_ref( $application_ref ) {
		global $wpdb;

		$application_ref = sanitize_text_field( $application_ref );
		if ( $application_ref === '' ) {
			return null;
		}

		return $wpdb->get_row(
			$wpdb->prepare(
				'SELECT * FROM ' . self::TABLE . ' WHERE application_ref = %s LIMIT 1',
				$application_ref
			),
			ARRAY_A
		);
	}

	public static function get_status_history( $application ) {
		return self::decode_json_field( gArrayItem( $application, 'status_history' ) );
	}

	private function append_status_history( $application_id, $status, $changed_by_id = 0, $changed_by_name = '' ) {
		global $wpdb;

		$application_id = intval( $application_id );
		if ( $application_id <= 0 || $status === '' ) {
			return;
		}

		$application = $this->get_application( $application_id );
		if ( ! is_array( $application ) ) {
			return;
		}

		$history = self::get_status_history( $application );
		if ( ! is_array( $history ) ) {
			$history = [];
		}

		$changed_by_id = intval( $changed_by_id );
		if ( $changed_by_name === '' && $changed_by_id > 0 ) {
			$user = get_userdata( $changed_by_id );
			$changed_by_name = $user ? $user->display_name : 'User #' . $changed_by_id;
		}
		if ( $changed_by_name === '' ) {
			$changed_by_name = 'System';
		}

		$history[] = [
			'status'          => $status,
			'changed_at'      => current_time( 'mysql' ),
			'changed_by'      => $changed_by_id > 0 ? $changed_by_id : null,
			'changed_by_name' => $changed_by_name,
		];

		$wpdb->update(
			self::TABLE,
			[
				'status_history' => vv_jsonEncode( $history ),
				'datemodified'   => current_time( 'mysql' ),
			],
			[ 'ID' => $application_id ]
		);
	}

	public function dismiss_host_welcome() {
		$user_id = get_current_user_id();
		if ( ! vv_host_can_access_admin( $user_id ) ) {
			wp_die( 'Unauthorized', 403 );
		}

		update_user_meta( $user_id, self::META_SHOW_WELCOME, 0 );

		if ( POST_Request( 'ajax' ) === '1' ) {
			wp_send_json_success();
		}

		wp_safe_redirect( wp_get_referer() ? wp_get_referer() : vv_admin_url( 'dashboard' ) );
		die();
	}

	public function show_host_welcome_again() {
		$user_id = get_current_user_id();
		if ( ! vv_host_can_access_admin( $user_id ) ) {
			wp_die( 'Unauthorized', 403 );
		}

		update_user_meta( $user_id, self::META_SHOW_WELCOME, 1 );
		setSuccessMsg( 'Welcome tour enabled. It will appear when you open the host portal.' );
		wp_safe_redirect( vv_admin_url( 'dashboard' ) );
		die();
	}

	public function render_application_status_page() {
		$ref = sanitize_text_field( GET_Request( 'ref' ) );
		$key = sanitize_text_field( GET_Request( 'key' ) );

		if ( $ref === '' || $key === '' ) {
			$this->render_status_page_shell( 'Invalid link', '<p>This application status link is incomplete. Please use the link from your confirmation email.</p>' );
		}

		$application = $this->get_application_by_ref( $ref );
		if ( ! is_array( $application ) ) {
			$this->render_status_page_shell( 'Application not found', '<p>We could not find an application with that reference.</p>' );
		}

		$user_id    = intval( gArrayItem( $application, 'user_id' ) );
		$stored_key = $user_id > 0 ? get_user_meta( $user_id, self::META_STATUS_KEY, true ) : '';
		if ( $stored_key === '' || ! hash_equals( (string) $stored_key, (string) $key ) ) {
			$this->render_status_page_shell( 'Invalid link', '<p>This status link is invalid or has expired. Contact us at <a href="mailto:' . esc_attr( vv_admin_contact_email() ) . '">' . esc_html( vv_admin_contact_email() ) . '</a> and include your application ID.</p>' );
		}

		$status      = gArrayItem( $application, 'status' );
		$status_label = self::status_label( $status );
		$submitted   = gArrayItem( $application, 'dateadded' );
		$contact     = vv_admin_contact_email();

		$next_steps = '';
		if ( $status === 'submitted' ) {
			$next_steps = '<p>Our team normally reviews applications within 2–3 business days. We will email you when the status changes.</p>';
		} elseif ( $status === 'under_review' ) {
			$next_steps = '<p>Your application is being reviewed. We will notify you by email once a decision is made.</p>';
		} elseif ( $status === 'approved' ) {
			$next_steps = '<p>Please check your email for the activation link to verify your address and set your password.</p>';
		} elseif ( $status === 'rejected' ) {
			$next_steps = '<p>If you believe this decision was made in error, you may appeal by emailing <a href="mailto:' . esc_attr( $contact ) . '">' . esc_html( $contact ) . '</a>.</p>';
		} elseif ( $status === 'activated' ) {
			$next_steps = '<p>Your host account is active. <a href="' . esc_url( vv_admin_url( 'login' ) ) . '">Sign in to the host portal</a> to get started.</p>';
		}

		$content = '<p>Hi ' . esc_html( gArrayItem( $application, 'full_name' ) ) . ',</p>'
			. '<p><strong>Application ID:</strong> ' . esc_html( $ref ) . '</p>'
			. '<p><strong>Current status:</strong> ' . esc_html( $status_label ) . '</p>'
			. ( $submitted !== '' ? '<p><strong>Submitted:</strong> ' . esc_html( date( 'j M Y', strtotime( $submitted ) ) ) . '</p>' : '' )
			. $next_steps
			. '<p class="text-muted small">Bookmark this page to check your status anytime.</p>';

		$this->render_status_page_shell( 'Application status', $content );
	}

	private function render_status_page_shell( $title, $content_html ) {
		status_header( 200 );
		nocache_headers();
		?>
		<!DOCTYPE html>
		<html lang="en">
		<head>
			<meta charset="UTF-8">
			<meta name="viewport" content="width=device-width, initial-scale=1">
			<title><?php echo esc_html( $title ); ?> | Vietstays</title>
			<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
			<style>
				body { background: #f5f6f8; padding: 2rem 1rem; }
				.status-card { max-width: 560px; margin: 0 auto; }
			</style>
		</head>
		<body>
			<div class="card status-card shadow-sm">
				<div class="card-header"><h1 class="h5 mb-0"><?php echo esc_html( $title ); ?></h1></div>
				<div class="card-body"><?php echo $content_html; ?></div>
			</div>
		</body>
		</html>
		<?php
		die();
	}

	private function send_host_template( $code, $to, array $tokens ) {
		$templates = new vvEmailTemplates();
		if ( ! $templates->send_by_code( $code, $to, $tokens ) ) {
			error_log( 'Vietstays: missing or empty email template "' . $code . '". Run ?vv_action=seed_host_email_templates as admin.' );
		}
	}

	private function next_step( $current, $applicant_type = '' ) {
		$current = intval( $current );
		if ( $current < 1 ) {
			return 1;
		}
		$max = self::total_steps( $applicant_type );
		if ( $current >= $max ) {
			return $max;
		}
		return $current + 1;
	}

	public static function is_single_applicant( $data ) {
		return gArrayItem( $data, 'applicant_type' ) === 'single_property';
	}

	public static function step_at( $step, $applicant_type = '' ) {
		$step = intval( $step );
		if ( $step <= 1 ) {
			return 'type';
		}

		if ( $applicant_type === 'single_property' ) {
			$map = [
				2  => 'space_type',
				3  => 'location',
				4  => 'basics',
				5  => 'amenities',
				6  => 'photos',
				7  => 'listing',
				8  => 'pricing',
				9  => 'contact',
				10 => 'experience',
				11 => 'review',
			];
			return gArrayItem( $map, $step, 'type' );
		}

		$map = [
			2 => 'contact',
			3 => 'experience',
			4 => 'review',
		];

		return gArrayItem( $map, $step, 'type' );
	}

	public static function step_label( $step, $applicant_type = '' ) {
		$labels = self::step_labels( $applicant_type );
		return gArrayItem( $labels, intval( $step ), '' );
	}

	public static function step_labels( $applicant_type = '' ) {
		if ( $applicant_type === '' ) {
			$data = self::get_session_data();
			$applicant_type = gArrayItem( $data, 'applicant_type' );
		}

		if ( $applicant_type === 'single_property' ) {
			return [
				1  => 'Start',
				2  => 'Space',
				3  => 'Location',
				4  => 'Basics',
				5  => 'Amenities',
				6  => 'Photos',
				7  => 'Listing',
				8  => 'Price',
				9  => 'About you',
				10 => 'Experience',
				11 => 'Review',
			];
		}

		return [
			1 => 'Start',
			2 => 'Details',
			3 => 'Experience',
			4 => 'Review',
		];
	}

	public static function summary_step( $applicant_type = '' ) {
		return self::total_steps( $applicant_type );
	}

	public static function total_steps( $applicant_type = '' ) {
		if ( $applicant_type === '' ) {
			$data = self::get_session_data();
			$applicant_type = gArrayItem( $data, 'applicant_type' );
		}

		return ( $applicant_type === 'single_property' ) ? 11 : 4;
	}

	public static function default_property_kind() {
		return 'apartment';
	}

	public static function place_kind_options() {
		return [
			'apartment' => 'Apartment',
		];
	}

	public static function space_type_options() {
		return [
			'entire_place' => 'An entire place',
			'private_room' => 'A private room',
			'shared_room'  => 'A shared room',
		];
	}

	public static function place_kind_label( $key = '' ) {
		return gArrayItem( self::place_kind_options(), $key, $key );
	}

	public static function space_type_label( $key = '' ) {
		return gArrayItem( self::space_type_options(), $key, $key );
	}

	/** @deprecated Use space_type_label() */
	public static function property_type_label( $key = '' ) {
		$label = self::space_type_label( $key );
		if ( $label !== $key ) {
			return $label;
		}
		return gArrayItem( self::place_kind_options(), $key, $key );
	}

	public static function property_type_rooms( $type = '' ) {
		if ( $type === 'private_room' || $type === 'shared_room' ) {
			return 1;
		}
		return 2;
	}

	private function validate_step_kind( $kind, $data ) {
		$errors = [];

		switch ( $kind ) {
			case 'space_type':
				if ( ! array_key_exists( gArrayItem( $data, 'property_space_type' ), self::space_type_options() ) ) {
					$errors[] = 'Select what guests will have access to.';
				}
				break;

			case 'location':
				if ( trim( gArrayItem( $data, 'property_address' ) ) === '' ) {
					$errors[] = 'Enter your property address.';
				}
				if ( intval( gArrayItem( $data, 'property_district_id' ) ) <= 0 ) {
					$errors[] = 'Select the district where your property is located.';
				}
				break;

			case 'basics':
				if ( intval( gArrayItem( $data, 'property_max_guests' ) ) < 1 ) {
					$errors[] = 'Enter how many guests your place can accommodate.';
				}
				if ( intval( gArrayItem( $data, 'property_rooms' ) ) < 0 ) {
					$errors[] = 'Enter a valid number of bedrooms.';
				}
				if ( intval( gArrayItem( $data, 'property_beds' ) ) < 1 ) {
					$errors[] = 'Enter how many beds you provide.';
				}
				if ( intval( gArrayItem( $data, 'property_bathrooms' ) ) < 1 ) {
					$errors[] = 'Enter how many bathrooms guests can use.';
				}
				break;

			case 'amenities':
				$amenities = gArrayItem( $data, 'property_amenities', [] );
				if ( ! is_array( $amenities ) || count( array_filter( $amenities ) ) === 0 ) {
					$errors[] = 'Select at least one amenity.';
				}
				break;

			case 'photos':
				$images = self::decode_json_field( gArrayItem( $data, 'property_images' ) );
				if ( ! is_array( $images ) || count( $images ) === 0 ) {
					$errors[] = 'Upload at least one photo of your place.';
				}
				break;

			case 'listing':
				if ( trim( gArrayItem( $data, 'property_name' ) ) === '' ) {
					$errors[] = 'Give your listing a title.';
				}
				if ( trim( gArrayItem( $data, 'property_description' ) ) === '' ) {
					$errors[] = 'Write a description for your listing.';
				}
				break;

			case 'pricing':
				if ( floatval( gArrayItem( $data, 'property_price_daily' ) ) <= 0 ) {
					$errors[] = 'Set a nightly price for your listing.';
				}
				break;
		}

		return $errors;
	}

	private function validate_listing_complete( $data ) {
		if ( self::is_single_applicant( $data ) && gArrayItem( $data, 'property_kind' ) === '' ) {
			$data['property_kind'] = self::default_property_kind();
		}

		$errors = [];
		foreach ( [ 'space_type', 'location', 'basics', 'amenities', 'photos', 'listing', 'pricing' ] as $kind ) {
			$errors = array_merge( $errors, $this->validate_step_kind( $kind, $data ) );
		}
		return $errors;
	}

	private function upload_property_images( $files ) {
		if ( ! is_array( $files ) || empty( $files['property_images'] ) ) {
			return [];
		}

		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';

		$uploaded = [];
		$batch    = $files['property_images'];
		$names    = gArrayItem( $batch, 'name' );
		$tmp      = gArrayItem( $batch, 'tmp_name' );
		$types    = gArrayItem( $batch, 'type' );
		$errors   = gArrayItem( $batch, 'error' );
		$sizes    = gArrayItem( $batch, 'size' );

		if ( ! is_array( $names ) ) {
			$names  = [ $names ];
			$tmp    = [ $tmp ];
			$types  = [ $types ];
			$errors = [ $errors ];
			$sizes  = [ $sizes ];
		}

		$max_files = 8;
		for ( $i = 0; $i < count( $names ) && count( $uploaded ) < $max_files; $i++ ) {
			if ( intval( gArrayItem( $errors, $i ) ) !== UPLOAD_ERR_OK || gArrayItem( $tmp, $i ) === '' ) {
				continue;
			}

			$file = [
				'name'     => $names[ $i ],
				'type'     => $types[ $i ],
				'tmp_name' => $tmp[ $i ],
				'error'    => $errors[ $i ],
				'size'     => gArrayItem( $sizes, $i, 0 ),
			];

			$check = wp_check_filetype( $file['name'] );
			if ( strpos( (string) gArrayItem( $check, 'type' ), 'image/' ) !== 0 ) {
				continue;
			}

			$result = wp_handle_upload( $file, [ 'test_form' => false ] );
			if ( ! empty( $result['error'] ) ) {
				continue;
			}

			$attachment_id = wp_insert_attachment(
				[
					'guid'           => $result['url'],
					'post_mime_type' => $result['type'],
					'post_title'     => basename( $result['file'] ),
					'post_content'   => '',
					'post_status'    => 'inherit',
				],
				$result['file']
			);

			if ( is_wp_error( $attachment_id ) || ! $attachment_id ) {
				continue;
			}

			wp_update_attachment_metadata(
				$attachment_id,
				wp_generate_attachment_metadata( $attachment_id, $result['file'] )
			);

			$uploaded[] = [
				'thumb'    => $result['url'],
				'image_id' => intval( $attachment_id ),
				'caption'  => '',
			];
		}

		return $uploaded;
	}

	public static function years_managing_options() {
		return [
			''           => 'Prefer not to say',
			'under_1'    => 'Less than 1 year',
			'1_2'        => '1–2 years',
			'3_5'        => '3–5 years',
			'over_5'     => '5+ years',
		];
	}

	public static function guest_profile_options() {
		return [
			''         => 'Prefer not to say',
			'tourists' => 'Tourists',
			'business' => 'Business travelers',
			'mixed'    => 'Mixed',
		];
	}

	public static function platform_options() {
		return [
			'airbnb'      => 'Airbnb',
			'booking_com' => 'Booking.com',
			'direct'      => 'Direct bookings',
			'other'       => 'Other',
		];
	}

	public static function format_property_amenity_labels( $amenity_ids ) {
		$amenity_ids = self::decode_json_field( $amenity_ids );
		if ( ! is_array( $amenity_ids ) || count( $amenity_ids ) === 0 ) {
			return '';
		}

		$facilities = vv_get_facilities();
		$labels     = [];
		foreach ( $facilities as $facility ) {
			$fid = intval( gArrayItem( $facility, 'facility_id' ) );
			if ( in_array( $fid, array_map( 'intval', $amenity_ids ), true ) ) {
				$labels[] = gArrayItem( $facility, 'name' );
			}
		}

		return implode( ', ', $labels );
	}

	public static function format_platform_labels( $platforms ) {
		$platforms = self::decode_json_field( $platforms );
		if ( ! is_array( $platforms ) || count( $platforms ) === 0 ) {
			return '';
		}

		$labels = self::platform_options();
		$out    = [];
		foreach ( $platforms as $key ) {
			if ( isset( $labels[ $key ] ) ) {
				$out[] = $labels[ $key ];
			}
		}

		return implode( ', ', $out );
	}

	public static function get_city_name( $city_id ) {
		$city_id = intval( $city_id );
		if ( $city_id <= 0 ) {
			return '';
		}

		$cities = vv_get_cities( [ 'per_page' => 'all' ] );
		if ( ! is_array( $cities ) ) {
			return '';
		}

		foreach ( $cities as $city ) {
			if ( intval( gArrayItem( $city, 'ID' ) ) === $city_id ) {
				return gArrayItem( $city, 'post_title' );
			}
		}

		return '';
	}

	public static function get_city_options() {
		$cities = vv_get_cities( [ 'per_page' => 'all' ] );
		if ( ! is_array( $cities ) ) {
			return [];
		}

		$options = [];
		foreach ( $cities as $city ) {
			$id = intval( gArrayItem( $city, 'ID' ) );
			if ( $id <= 0 ) {
				continue;
			}
			$options[ $id ] = gArrayItem( $city, 'post_title' );
		}

		return $options;
	}

	public static function filter_district_ids_for_city( $city_id, array $district_ids ) {
		$city_id = intval( $city_id );
		if ( $city_id <= 0 ) {
			return [];
		}

		$allowed = array_column( self::get_district_checklist_for_city( $city_id ), 'id' );
		$allowed = array_map( 'intval', $allowed );

		return array_values( array_intersect( array_map( 'intval', $district_ids ), $allowed ) );
	}

	public static function get_district_city_id( $district_id ) {
		$district_id = intval( $district_id );
		if ( $district_id <= 0 ) {
			return 0;
		}

		$city_id = intval( get_post_meta( $district_id, 'city', true ) );
		if ( $city_id > 0 ) {
			return $city_id;
		}

		if ( function_exists( 'get_field' ) ) {
			$city = get_field( 'city', $district_id );
			if ( is_object( $city ) && isset( $city->ID ) ) {
				return intval( $city->ID );
			}
			if ( is_array( $city ) && isset( $city['ID'] ) ) {
				return intval( $city['ID'] );
			}
			if ( is_numeric( $city ) ) {
				return intval( $city );
			}
		}

		return 0;
	}

	public static function get_district_checklist_for_city( $city_id ) {
		$city_id = intval( $city_id );
		if ( $city_id <= 0 ) {
			return [];
		}

		$items = [];
		foreach ( self::get_district_checklist() as $item ) {
			if ( intval( gArrayItem( $item, 'city_id' ) ) === $city_id ) {
				$items[] = $item;
			}
		}

		return $items;
	}

	public static function get_district_checklist() {
		$districts = vv_get_districts( [
			'per_page' => 'all',
			'parent'   => 'all',
		] );

		if ( ! is_array( $districts ) ) {
			return [];
		}

		$items = [];
		foreach ( $districts as $district ) {
			$district_id    = intval( gArrayItem( $district, 'ID' ) );
			$city_id        = self::get_district_city_id( $district_id );
			$district_title = gArrayItem( $district, 'post_title' );
			$neighbourhoods = gArrayItem( $district, 'neighbourhoods', [] );

			if ( $city_id <= 0 || $district_id <= 0 ) {
				continue;
			}

			if ( ! is_array( $neighbourhoods ) ) {
				$neighbourhoods = [];
			}

			if ( count( $neighbourhoods ) > 0 ) {
				foreach ( $neighbourhoods as $neighbourhood ) {
					$nid = intval( gArrayItem( $neighbourhood, 'ID' ) );
					if ( $nid <= 0 ) {
						continue;
					}
					$items[] = [
						'id'      => $nid,
						'city_id' => $city_id,
						'label'   => $district_title . ': ' . gArrayItem( $neighbourhood, 'post_title' ),
					];
				}
			} else {
				$items[] = [
					'id'      => $district_id,
					'city_id' => $city_id,
					'label'   => $district_title,
				];
			}
		}

		return $items;
	}

	public static function format_district_labels( $district_ids ) {
		if ( ! is_array( $district_ids ) ) {
			return '';
		}

		$labels = [];
		foreach ( $district_ids as $id ) {
			$id = intval( $id );
			if ( $id <= 0 ) {
				continue;
			}
			$name = vv_get_district_display_name( $id );
			if ( $name !== '' ) {
				$labels[] = $name;
			}
		}

		return implode( ', ', $labels );
	}
}
