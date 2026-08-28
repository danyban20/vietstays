<?php

/**
 * Vietstays role model — aligned with Requirements Spec v0.1 (Chapter 2 & 3).
 *
 * One WordPress user may hold multiple platform roles simultaneously.
 * Legacy single-role storage (vv_user_level) is migrated on read.
 */
class vvRoles {

	const ROLE_GUEST             = 'guest';
	const ROLE_HOST              = 'host';
	const ROLE_AMBASSADOR        = 'ambassador';
	const ROLE_HOST_AGENT        = 'host_agent';
	const ROLE_ADMIN             = 'admin';
	const ROLE_SALES_SUPPORT     = 'sales_support';
	const ROLE_OPERATIONS_STAFF  = 'operations_staff';

	const META_ROLES       = 'vv_user_roles';
	const META_LEGACY_LEVEL = 'vv_user_level';

	/** Backend portal roles (spec §2 — Note: Interface). */
	const BACKEND_ROLES = [
		self::ROLE_HOST,
		self::ROLE_ADMIN,
		self::ROLE_SALES_SUPPORT,
		self::ROLE_OPERATIONS_STAFF,
	];

	/** Frontend role-based tool tabs (same app as guest). */
	const FRONTEND_TOOL_ROLES = [
		self::ROLE_AMBASSADOR,
		self::ROLE_HOST_AGENT,
	];

	/** Map legacy vv_user_level / WP role slugs to spec role types. */
	const LEGACY_MAP = [
		'customer'      => self::ROLE_GUEST,
		'guest'         => self::ROLE_GUEST,
		'partner'       => self::ROLE_HOST,
		'host'          => self::ROLE_HOST,
		'ambassador'    => self::ROLE_AMBASSADOR,
		'host_agent'    => self::ROLE_HOST_AGENT,
		'admin'         => self::ROLE_ADMIN,
		'administrator' => self::ROLE_ADMIN,
		'sales_support' => self::ROLE_SALES_SUPPORT,
		'staff'         => self::ROLE_OPERATIONS_STAFF,
		'operations_staff' => self::ROLE_OPERATIONS_STAFF,
	];

	/** Priority for picking the primary backend role (higher = wins). */
	const BACKEND_PRIORITY = [
		self::ROLE_ADMIN             => 100,
		self::ROLE_SALES_SUPPORT     => 80,
		self::ROLE_HOST              => 60,
		self::ROLE_OPERATIONS_STAFF  => 40,
		self::ROLE_AMBASSADOR        => 20,
		self::ROLE_HOST_AGENT        => 20,
		self::ROLE_GUEST             => 0,
	];

	/**
	 * Capabilities enforced at backend level (spec §2.6 — not UI-only).
	 * '*' = all capabilities for that role.
	 */
	const CAPABILITIES = [
		self::ROLE_ADMIN => ['*'],
		self::ROLE_SALES_SUPPORT => [
			'view_bookings',
			'view_user_profiles',
			'validate_discount_codes',
			'send_messages',
			'assist_cancellations',
			'assign_host_applications',
			'view_host_scores',
		],
		self::ROLE_HOST => [
			'manage_apartments',
			'manage_bookings',
			'manage_operations_staff',
			'manage_host_agents',
			'manage_discount_codes',
			'view_host_finance',
			'manage_conversions',
			'view_host_notifications',
		],
		self::ROLE_OPERATIONS_STAFF => [
			'view_assigned_tasks',
			'complete_checklists',
		],
		self::ROLE_AMBASSADOR => [
			'view_ambassador_dashboard',
			'view_cashpoints',
		],
		self::ROLE_HOST_AGENT => [
			'view_host_agent_dashboard',
			'view_commission',
		],
		self::ROLE_GUEST => [],
	];

	public static function normalize_role( $role ) {
		$role = strtolower( trim( (string) $role ) );
		return gArrayItem( self::LEGACY_MAP, $role, $role );
	}

	public static function all_platform_roles() {
		return [
			self::ROLE_GUEST,
			self::ROLE_HOST,
			self::ROLE_AMBASSADOR,
			self::ROLE_HOST_AGENT,
			self::ROLE_ADMIN,
			self::ROLE_SALES_SUPPORT,
			self::ROLE_OPERATIONS_STAFF,
		];
	}

	public static function admin_assignable_roles() {
		return [
			self::ROLE_GUEST,
			self::ROLE_HOST,
			self::ROLE_AMBASSADOR,
			self::ROLE_HOST_AGENT,
			self::ROLE_ADMIN,
			self::ROLE_SALES_SUPPORT,
			self::ROLE_OPERATIONS_STAFF,
		];
	}

	/**
	 * Read active roles for a user. Migrates legacy vv_user_level when needed.
	 *
	 * @return string[] Normalized, unique, active role type slugs.
	 */
	public static function get_user_roles( $user_id ) {
		$user_id = intval( $user_id );
		if ( $user_id <= 0 ) {
			return [ self::ROLE_GUEST ];
		}

		$stored = get_user_meta( $user_id, self::META_ROLES, true );
		$roles  = self::parse_roles_meta( $stored );

		if ( empty( $roles ) ) {
			$roles = self::migrate_legacy_roles( $user_id );
		}

		if ( ! in_array( self::ROLE_GUEST, $roles, true ) ) {
			array_unshift( $roles, self::ROLE_GUEST );
		}

		return array_values( array_unique( $roles ) );
	}

	public static function user_has_role( $user_id, $role ) {
		$role = self::normalize_role( $role );
		return in_array( $role, self::get_user_roles( $user_id ), true );
	}

	/**
	 * Primary role for backend UI routing (sidebar, redirects).
	 * Falls back to legacy vv_get_user_role behaviour.
	 */
	public static function get_primary_backend_role( $user_id ) {
		$user_id = intval( $user_id );
		$user    = get_user_by( 'ID', $user_id );

		if ( $user && is_array( $user->roles ) && in_array( 'administrator', $user->roles, true ) ) {
			return self::ROLE_ADMIN;
		}

		$roles    = self::get_user_roles( $user_id );
		$best     = self::ROLE_GUEST;
		$best_pri = -1;

		foreach ( $roles as $role ) {
			$pri = gArrayItem( self::BACKEND_PRIORITY, $role, 0 );
			if ( $pri > $best_pri ) {
				$best_pri = $pri;
				$best     = $role;
			}
		}

		return $best;
	}

	public static function user_can( $user_id, $capability ) {
		$roles = self::get_user_roles( $user_id );

		foreach ( $roles as $role ) {
			$caps = gArrayItem( self::CAPABILITIES, $role, [] );
			if ( in_array( '*', $caps, true ) || in_array( $capability, $caps, true ) ) {
				return true;
			}
		}

		return false;
	}

	public static function can_access_backend( $user_id ) {
		$roles = self::get_user_roles( $user_id );
		foreach ( self::BACKEND_ROLES as $backend_role ) {
			if ( in_array( $backend_role, $roles, true ) ) {
				return true;
			}
		}
		$user = get_user_by( 'ID', intval( $user_id ) );
		return $user && is_array( $user->roles ) && in_array( 'administrator', $user->roles, true );
	}

	public static function has_frontend_tools( $user_id ) {
		foreach ( self::FRONTEND_TOOL_ROLES as $role ) {
			if ( self::user_has_role( $user_id, $role ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Persist roles and keep legacy vv_user_level in sync for existing code paths.
	 *
	 * @param string[] $roles Normalized role slugs.
	 */
	public static function set_user_roles( $user_id, array $roles ) {
		$user_id = intval( $user_id );
		if ( $user_id <= 0 ) {
			return;
		}

		$normalized = [];
		foreach ( $roles as $role ) {
			$role = self::normalize_role( $role );
			if ( in_array( $role, self::all_platform_roles(), true ) ) {
				$normalized[] = $role;
			}
		}
		$normalized = array_values( array_unique( $normalized ) );

		if ( ! in_array( self::ROLE_GUEST, $normalized, true ) ) {
			array_unshift( $normalized, self::ROLE_GUEST );
		}

		$records = [];
		$now     = current_time( 'mysql' );
		foreach ( $normalized as $role ) {
			$records[] = [
				'role_type'   => $role,
				'is_active'   => true,
				'activated_at'=> $now,
			];
		}

		update_user_meta( $user_id, self::META_ROLES, vv_jsonEncode( $records ) );
		update_user_meta( $user_id, self::META_LEGACY_LEVEL, self::legacy_level_from_roles( $normalized ) );
	}

	/** Add a role without removing others (spec: same person, multiple roles). */
	public static function add_user_role( $user_id, $role ) {
		$roles = self::get_user_roles( $user_id );
		$roles[] = self::normalize_role( $role );
		self::set_user_roles( $user_id, $roles );
	}

	public static function legacy_level_from_roles( array $roles ) {
		if ( in_array( self::ROLE_ADMIN, $roles, true ) ) {
			return 'admin';
		}
		if ( in_array( self::ROLE_SALES_SUPPORT, $roles, true ) ) {
			return 'sales_support';
		}
		if ( in_array( self::ROLE_HOST, $roles, true ) ) {
			return 'partner';
		}
		if ( in_array( self::ROLE_OPERATIONS_STAFF, $roles, true ) ) {
			return 'staff';
		}
		if ( in_array( self::ROLE_AMBASSADOR, $roles, true ) ) {
			return 'ambassador';
		}
		if ( in_array( self::ROLE_HOST_AGENT, $roles, true ) ) {
			return 'host_agent';
		}
		return 'customer';
	}

	private static function parse_roles_meta( $stored ) {
		if ( is_string( $stored ) && $stored !== '' ) {
			$stored = json_decode( $stored, true );
		}
		if ( ! is_array( $stored ) ) {
			return [];
		}

		$roles = [];
		foreach ( $stored as $row ) {
			if ( is_string( $row ) ) {
				$roles[] = self::normalize_role( $row );
				continue;
			}
			if ( is_array( $row ) && gArrayItem( $row, 'is_active', true ) && gArrayItem( $row, 'role_type' ) !== '' ) {
				$roles[] = self::normalize_role( $row['role_type'] );
			}
		}

		return array_values( array_unique( $roles ) );
	}

	private static function migrate_legacy_roles( $user_id ) {
		$legacy = get_user_meta( $user_id, self::META_LEGACY_LEVEL, true );
		if ( $legacy === '' && function_exists( 'get_field' ) ) {
			$acf = get_field( 'vv_user_level', 'user_' . $user_id );
			if ( $acf !== '' && $acf !== null ) {
				$legacy = $acf;
			}
		}

		$roles = [ self::ROLE_GUEST ];
		if ( $legacy !== '' ) {
			$roles[] = self::normalize_role( $legacy );
		}

		$user = get_user_by( 'ID', $user_id );
		if ( $user && is_array( $user->roles ) && in_array( 'administrator', $user->roles, true ) ) {
			$roles[] = self::ROLE_ADMIN;
		}

		$roles = array_values( array_unique( $roles ) );
		self::set_user_roles( $user_id, $roles );

		return $roles;
	}
}
