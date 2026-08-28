<?php
/**
 * Backend sidebar menus — Vietstays Backend Nav v3 structure.
 *
 * Icons use Font Awesome (bundled in admin). The PDF wireframes do not ship icon assets;
 * FA classes below match each section semantically.
 */

if ( ! function_exists( 'vv_sidebar_menu_url' ) ) {
	function vv_sidebar_menu_url( $path = '' ) {
		return vv_admin_url( $path );
	}
}

if ( ! function_exists( 'vv_sidebar_coming_soon_url' ) ) {
	function vv_sidebar_coming_soon_url( $feature = '' ) {
		$url = vv_admin_url( 'coming-soon' );
		if ( $feature !== '' ) {
			$url .= '?feature=' . rawurlencode( $feature );
		}
		return $url;
	}
}

if ( ! function_exists( 'vv_sidebar_active_menu_path' ) ) {
	/**
	 * Map the current admin view file to the sidebar menu URL segment.
	 * Child routes (edit/add) should highlight their parent list menu item.
	 */
	function vv_sidebar_active_menu_path( $admin_file ) {
		$admin_file = trim( (string) $admin_file, '/' );
		if ( $admin_file === '' ) {
			return '';
		}

		$map = [
			'locations/neighbourhoods/edit' => 'locations/neighbourhoods',
			'locations/neighbourhoods/add'  => 'locations/neighbourhoods',
			'locations/districts/edit'      => 'locations/districts',
			'locations/districts/add'       => 'locations/districts',
			'locations/cities/list'         => 'locations/cities',
			'apartments/add-old'       => 'apartments/add-old',
			'apartments/edit-old'      => 'apartments',
			'apartments/add'           => 'apartments/add',
			'apartments/manage'        => 'apartments',
			'apartments/edit'               => 'apartments',
			'partners/edit'                 => 'partners',
			'users/edit'                    => 'users',
			'staffs/edit'                   => 'staffs',
			'staffs/add'                    => 'staffs',
			'booking/add'                   => 'booking/add',
			'booking/edit'                  => 'booking',
			'booking/calendar'              => 'booking/calendar',
			'booking/revenue'               => 'booking/revenue',
			'apartments/gantt'              => 'apartments/gantt',
			'building-requests/view'        => 'building-requests',
			'conversions/list'              => 'conversions',
			'settings/general'              => 'settings',
			'settings/building-price-matrix'=> 'settings/building-price-matrix',
			'settings/apartment-platform'   => 'settings/apartment-platform',
		];

		if ( isset( $map[ $admin_file ] ) ) {
			return $map[ $admin_file ];
		}

		if ( preg_match( '#^locations/(cities|districts|neighbourhoods)/(edit|add)$#', $admin_file, $matches ) ) {
			return 'locations/' . $matches[1];
		}

		return preg_replace( '#/(list)$#', '', $admin_file );
	}
}

if ( ! function_exists( 'vv_get_backend_sidebar_menus' ) ) {
	/**
	 * @return array<int, array<string, mixed>>
	 */
	function vv_get_backend_sidebar_menus( $role, $staff_position = '' ) {
		switch ( $role ) {
			case 'administrator':
				return vv_sidebar_menus_admin();
			case 'partner':
				return vv_sidebar_menus_host();
			case 'sales_support':
				return vv_sidebar_menus_sales_support();
			case 'staff':
				return vv_sidebar_menus_operations_staff( $staff_position );
			default:
				return [];
		}
	}
}

if ( ! function_exists( 'vv_sidebar_menus_admin' ) ) {
	function vv_sidebar_menus_admin() {
		return [
			[
				'type'  => 'link',
				'label' => 'Dashboard',
				'icon'  => 'fas fa-tachometer-alt',
				'url'   => vv_sidebar_menu_url( 'dashboard' ),
			],
			[
				'type'     => 'group',
				'label'    => 'Apartments',
				'icon'     => 'fas fa-building',
				'children' => [
					[ 'label' => 'All apartments', 'url' => vv_sidebar_menu_url( 'apartments' ) ],
					[ 'label' => 'Add new apartment', 'url' => vv_sidebar_menu_url( 'apartments/add' ) ],
					[ 'label' => 'Portfolio calendar (Gantt)', 'url' => vv_sidebar_menu_url( 'apartments/gantt' ) ],
				],
			],
			[
				'type'     => 'group',
				'label'    => 'Bookings',
				'icon'     => 'fas fa-calendar-check',
				'children' => [
					[ 'label' => 'All bookings', 'url' => vv_sidebar_menu_url( 'booking' ) ],
					[ 'label' => 'Add booking', 'url' => vv_sidebar_menu_url( 'booking/add' ) ],
					[ 'label' => 'Calendar', 'url' => vv_sidebar_menu_url( 'booking/calendar' ) ],
					[ 'label' => 'Booking revenue', 'url' => vv_sidebar_menu_url( 'booking/revenue' ) ],
					[ 'label' => 'Airbnb conversion', 'url' => vv_sidebar_menu_url( 'conversions' ) ],
				],
			],
			[
				'type'     => 'group',
				'label'    => 'Hosts / Partners',
				'icon'     => 'fas fa-handshake',
				'children' => [
					[ 'label' => 'All hosts & managers', 'url' => vv_sidebar_menu_url( 'partners' ) ],
					[ 'label' => 'Management companies', 'url' => vv_sidebar_coming_soon_url( 'management-companies' ) ],
					[ 'label' => 'Operations teams', 'url' => vv_sidebar_menu_url( 'staffs' ) ],
					[ 'label' => 'Host agents', 'url' => vv_sidebar_coming_soon_url( 'host-agents' ) ],
				],
			],
			[
				'type'     => 'group',
				'label'    => 'Users',
				'icon'     => 'fas fa-users',
				'children' => [
					[ 'label' => 'All users', 'url' => vv_sidebar_menu_url( 'users' ) ],
					[ 'label' => 'Ambassadors', 'url' => vv_sidebar_menu_url( 'ambassadors' ) ],
					[ 'label' => 'Account lock / restriction', 'url' => vv_sidebar_coming_soon_url( 'account-restriction' ) ],
					[ 'label' => 'Admin accounts', 'url' => vv_sidebar_menu_url( 'admins' ) ],
				],
			],
			[
				'type'     => 'group',
				'label'    => 'Finance',
				'icon'     => 'fas fa-coins',
				'children' => [
					[ 'label' => 'Outstanding payouts', 'url' => vv_sidebar_menu_url( 'finances/payouts' ) ],
					[ 'label' => 'Platform fee per booking', 'url' => vv_sidebar_menu_url( 'finances/revenue-dashboard' ) ],
					[ 'label' => 'Commission costs', 'url' => vv_sidebar_menu_url( 'finances/ambassador-commissions' ) ],
					[ 'label' => 'Monthly report per apartment', 'url' => vv_sidebar_menu_url( 'finances/transaction-history' ) ],
				],
			],
			[
				'type'     => 'group',
				'label'    => 'Marketing',
				'icon'     => 'fas fa-bullhorn',
				'children' => [
					[ 'label' => 'Campaigns & promo codes', 'url' => vv_sidebar_menu_url( 'settings/promocodes' ) ],
					[ 'label' => 'Email campaigns', 'url' => vv_sidebar_menu_url( 'settings/email-templates' ) ],
					[ 'label' => 'Scheduled campaigns', 'url' => vv_sidebar_coming_soon_url( 'scheduled-campaigns' ) ],
				],
			],
			[
				'type'     => 'group',
				'label'    => 'Platform content',
				'icon'     => 'fas fa-layer-group',
				'children' => [
					[ 'label' => 'Buildings / neighbourhoods', 'url' => vv_sidebar_menu_url( 'locations/neighbourhoods' ) ],
					[ 'label' => 'Building requests', 'url' => vv_sidebar_menu_url( 'building-requests' ) ],
					[ 'label' => 'Districts', 'url' => vv_sidebar_menu_url( 'locations/districts' ) ],
					[ 'label' => 'Cities', 'url' => vv_sidebar_menu_url( 'locations/cities' ) ],
					[ 'label' => 'House rules (NO/EN/VI)', 'url' => vv_sidebar_menu_url( 'settings/apartment-platform' ) ],
					[ 'label' => 'Apartment facilities', 'url' => vv_sidebar_menu_url( 'settings/facilities' ) ],
					[ 'label' => 'Operations checklists', 'url' => vv_sidebar_menu_url( 'staffs/checklists' ) ],
				],
			],
			[
				'type'     => 'group',
				'label'    => 'Configuration',
				'icon'     => 'fas fa-sliders-h',
				'children' => [
					[ 'label' => 'Platform & booking fees', 'url' => vv_sidebar_menu_url( 'settings' ) ],
					[ 'label' => 'Price levels (Low/Normal/High)', 'url' => vv_sidebar_menu_url( 'settings/apartment-platform' ) ],
					[ 'label' => 'Building price matrix', 'url' => vv_sidebar_menu_url( 'settings/building-price-matrix' ) ],
					[ 'label' => 'Min / max price limits', 'url' => vv_sidebar_menu_url( 'settings/apartment-platform' ) ],
					[ 'label' => 'Cash points & tiers', 'url' => vv_sidebar_coming_soon_url( 'cash-points' ) ],
					[ 'label' => 'Currency & exchange rates', 'url' => vv_sidebar_menu_url( 'settings/currency' ) ],
					[ 'label' => 'Security features', 'url' => vv_sidebar_menu_url( 'settings/security-features' ) ],
					[ 'label' => 'Languages', 'url' => vv_sidebar_menu_url( 'settings/languages' ) ],
				],
			],
			[
				'type'     => 'group',
				'label'    => 'Payouts',
				'icon'     => 'fas fa-money-check-alt',
				'children' => [
					[ 'label' => 'Approve host payouts', 'url' => vv_sidebar_menu_url( 'finances/payouts' ) ],
					[ 'label' => 'Payout history', 'url' => vv_sidebar_menu_url( 'finances/transaction-history' ) ],
				],
			],
			[
				'type'     => 'group',
				'label'    => 'Reports',
				'icon'     => 'fas fa-chart-line',
				'children' => [
					[ 'label' => 'Ambassador reports', 'url' => vv_sidebar_menu_url( 'finances/ambassador-commissions' ) ],
					[ 'label' => 'Host reports', 'url' => vv_sidebar_menu_url( 'finances/revenue-dashboard' ) ],
					[ 'label' => 'Export CSV / Excel', 'url' => vv_sidebar_coming_soon_url( 'export-reports' ) ],
				],
			],
			[
				'type'  => 'link',
				'label' => 'Communication',
				'icon'  => 'fas fa-comments',
				'url'   => vv_sidebar_coming_soon_url( 'communication' ),
			],
			[
				'type'  => 'section',
				'label' => 'Administration',
			],
			[
				'type'  => 'link',
				'label' => 'Host applications',
				'icon'  => 'fas fa-file-signature',
				'url'   => vv_sidebar_menu_url( 'host-applications' ),
			],
			[
				'type'  => 'link',
				'label' => 'Host points system',
				'icon'  => 'fas fa-star',
				'url'   => vv_sidebar_coming_soon_url( 'host-points' ),
			],
			[
				'type'     => 'group',
				'label'    => 'Settings',
				'icon'     => 'fas fa-cog',
				'children' => [
					[ 'label' => 'General settings', 'url' => vv_sidebar_menu_url( 'settings' ) ],
					[ 'label' => 'Email settings', 'url' => vv_sidebar_menu_url( 'settings/email' ) ],
					[ 'label' => 'Notification preferences', 'url' => vv_sidebar_coming_soon_url( 'notifications' ) ],
					[ 'label' => 'Change password', 'url' => vv_sidebar_menu_url( 'admins/account' ) ],
				],
			],
		];
	}
}

if ( ! function_exists( 'vv_sidebar_menus_host' ) ) {
	function vv_sidebar_menus_host() {
		return [
			[
				'type'  => 'link',
				'label' => 'Dashboard',
				'icon'  => 'fas fa-tachometer-alt',
				'url'   => vv_sidebar_menu_url( 'dashboard' ),
			],
			[
				'type'  => 'section',
				'label' => 'Apartments & bookings',
			],
			[
				'type'     => 'group',
				'label'    => 'My apartments',
				'icon'     => 'fas fa-building',
				'children' => [
					[ 'label' => 'All my apartments', 'url' => vv_sidebar_menu_url( 'apartments' ) ],
					[ 'label' => 'Add new apartment', 'url' => vv_sidebar_menu_url( 'apartments/add' ) ],
					[ 'label' => 'Publication status', 'url' => vv_sidebar_menu_url( 'apartments' ) ],
					[ 'label' => 'Block dates', 'url' => vv_sidebar_menu_url( 'apartments/gantt' ) ],
				],
			],
			[
				'type'     => 'group',
				'label'    => 'My bookings',
				'icon'     => 'fas fa-calendar-check',
				'children' => [
					[ 'label' => 'All my bookings', 'url' => vv_sidebar_menu_url( 'booking' ) ],
					[ 'label' => 'Add booking', 'url' => vv_sidebar_menu_url( 'booking/add' ) ],
					[ 'label' => 'Calendar (all apartments)', 'url' => vv_sidebar_menu_url( 'apartments/gantt' ) ],
					[ 'label' => 'Booking calendar', 'url' => vv_sidebar_menu_url( 'booking/calendar' ) ],
					[ 'label' => 'Approve / reject requests', 'url' => vv_sidebar_menu_url( 'booking' ) . '?tab=pending' ],
					[ 'label' => 'Airbnb conversion', 'url' => vv_sidebar_menu_url( 'conversions' ) ],
				],
			],
			[
				'type'  => 'section',
				'label' => 'My team',
			],
			[
				'type'     => 'group',
				'label'    => 'Operations team',
				'icon'     => 'fas fa-user-cog',
				'children' => [
					[ 'label' => 'Add team member', 'url' => vv_sidebar_menu_url( 'staffs/add' ) ],
					[ 'label' => 'All operations staff', 'url' => vv_sidebar_menu_url( 'staffs' ) ],
					[ 'label' => 'Tasks', 'url' => vv_sidebar_menu_url( 'tasks' ) ],
					[ 'label' => 'Checklists & history', 'url' => vv_sidebar_menu_url( 'staffs/checklists' ) ],
				],
			],
			[
				'type'  => 'link',
				'label' => 'Management company',
				'icon'  => 'fas fa-briefcase',
				'url'   => vv_sidebar_coming_soon_url( 'management-company' ),
			],
			[
				'type'  => 'link',
				'label' => 'Host agents',
				'icon'  => 'fas fa-user-tie',
				'url'   => vv_sidebar_coming_soon_url( 'host-agents' ),
			],
			[
				'type'  => 'link',
				'label' => 'Ambassadors',
				'icon'  => 'fas fa-medal',
				'url'   => vv_sidebar_coming_soon_url( 'host-ambassadors' ),
			],
			[
				'type'     => 'group',
				'label'    => 'Campaigns',
				'icon'     => 'fas fa-percent',
				'children' => [
					[ 'label' => 'Add promo code', 'url' => vv_sidebar_coming_soon_url( 'host-promo-codes' ) ],
					[ 'label' => 'Active campaigns', 'url' => vv_sidebar_coming_soon_url( 'host-campaigns' ) ],
					[ 'label' => 'All promo codes', 'url' => vv_sidebar_coming_soon_url( 'host-promo-codes' ) ],
				],
			],
			[
				'type'  => 'section',
				'label' => 'Finance & reports',
			],
			[
				'type'     => 'group',
				'label'    => 'Finance',
				'icon'     => 'fas fa-coins',
				'children' => [
					[ 'label' => 'Outstanding payouts', 'url' => vv_sidebar_menu_url( 'booking/revenue' ) ],
					[ 'label' => 'Platform fee per booking', 'url' => vv_sidebar_menu_url( 'booking/revenue' ) ],
					[ 'label' => 'Agent commission costs', 'url' => vv_sidebar_coming_soon_url( 'agent-commissions' ) ],
					[ 'label' => 'Monthly report per apartment', 'url' => vv_sidebar_menu_url( 'booking/revenue' ) ],
				],
			],
			[
				'type'     => 'group',
				'label'    => 'Reports',
				'icon'     => 'fas fa-chart-line',
				'children' => [
					[ 'label' => 'Monthly revenue report', 'url' => vv_sidebar_menu_url( 'booking/revenue' ) ],
					[ 'label' => 'Occupancy per apartment', 'url' => vv_sidebar_menu_url( 'apartments/gantt' ) ],
					[ 'label' => 'Promo code usage', 'url' => vv_sidebar_coming_soon_url( 'promo-usage' ) ],
				],
			],
			[
				'type'  => 'section',
				'label' => 'Communication & account',
			],
			[
				'type'  => 'link',
				'label' => 'Communication',
				'icon'  => 'fas fa-comments',
				'url'   => vv_sidebar_coming_soon_url( 'communication' ),
			],
			[
				'type'  => 'link',
				'label' => 'Marketing',
				'icon'  => 'fas fa-bullhorn',
				'url'   => vv_sidebar_coming_soon_url( 'host-marketing' ),
			],
			[
				'type'  => 'link',
				'label' => 'Host points / Exit',
				'icon'  => 'fas fa-star',
				'url'   => vv_sidebar_coming_soon_url( 'host-points' ),
			],
			[
				'type'     => 'group',
				'label'    => 'Settings',
				'icon'     => 'fas fa-cog',
				'children' => [
					[ 'label' => 'Profile & contact', 'url' => vv_sidebar_menu_url( 'partners/edit' ) . '?id=' . get_current_user_id() ],
					[ 'label' => 'Currency', 'url' => vv_sidebar_menu_url( 'settings/currency' ) ],
					[ 'label' => 'Change password', 'url' => vv_sidebar_menu_url( 'admins/account' ) ],
				],
			],
		];
	}
}

if ( ! function_exists( 'vv_sidebar_menus_sales_support' ) ) {
	function vv_sidebar_menus_sales_support() {
		return [
			[
				'type'  => 'link',
				'label' => 'Dashboard',
				'icon'  => 'fas fa-tachometer-alt',
				'url'   => vv_sidebar_menu_url( 'dashboard' ),
			],
			[
				'type'  => 'section',
				'label' => 'Bookings & guests',
			],
			[
				'type'     => 'group',
				'label'    => 'Bookings',
				'icon'     => 'fas fa-calendar-check',
				'children' => [
					[ 'label' => 'All bookings', 'url' => vv_sidebar_menu_url( 'booking' ) ],
					[ 'label' => 'Calendar', 'url' => vv_sidebar_menu_url( 'booking/calendar' ) ],
					[ 'label' => 'Cancellation & refund', 'url' => vv_sidebar_menu_url( 'booking' ) ],
					[ 'label' => 'Validate promo codes', 'url' => vv_sidebar_menu_url( 'settings/promocodes' ) ],
				],
			],
			[
				'type'  => 'link',
				'label' => 'Users',
				'icon'  => 'fas fa-users',
				'url'   => vv_sidebar_menu_url( 'users' ),
			],
			[
				'type'  => 'section',
				'label' => 'Apartments',
			],
			[
				'type'     => 'group',
				'label'    => 'Apartments',
				'icon'     => 'fas fa-building',
				'children' => [
					[ 'label' => 'All apartments (read-only)', 'url' => vv_sidebar_menu_url( 'apartments' ) ],
					[ 'label' => 'Portfolio calendar', 'url' => vv_sidebar_menu_url( 'apartments/gantt' ) ],
					[ 'label' => 'Publication status', 'url' => vv_sidebar_menu_url( 'apartments' ) ],
				],
			],
			[
				'type'     => 'group',
				'label'    => 'Hosts / Partners',
				'icon'     => 'fas fa-handshake',
				'children' => [
					[ 'label' => 'All hosts & managers', 'url' => vv_sidebar_menu_url( 'partners' ) ],
					[ 'label' => 'Management companies', 'url' => vv_sidebar_coming_soon_url( 'management-companies' ) ],
				],
			],
			[
				'type'  => 'section',
				'label' => 'Sales & onboarding',
			],
			[
				'type'     => 'group',
				'label'    => 'Marketing',
				'icon'     => 'fas fa-bullhorn',
				'children' => [
					[ 'label' => 'Promo codes', 'url' => vv_sidebar_menu_url( 'settings/promocodes' ) ],
					[ 'label' => 'Onboarding support', 'url' => vv_sidebar_coming_soon_url( 'onboarding' ) ],
				],
			],
			[
				'type'  => 'link',
				'label' => 'Host applications',
				'icon'  => 'fas fa-file-signature',
				'url'   => vv_sidebar_menu_url( 'host-applications' ),
			],
			[
				'type'  => 'section',
				'label' => 'Platform',
			],
			[
				'type'     => 'group',
				'label'    => 'Platform content',
				'icon'     => 'fas fa-layer-group',
				'children' => [
					[ 'label' => 'Buildings / neighbourhoods', 'url' => vv_sidebar_menu_url( 'locations/neighbourhoods' ) ],
					[ 'label' => 'Districts', 'url' => vv_sidebar_menu_url( 'locations/districts' ) ],
					[ 'label' => 'Cities', 'url' => vv_sidebar_menu_url( 'locations/cities' ) ],
					[ 'label' => 'House rules', 'url' => vv_sidebar_menu_url( 'settings/apartment-platform' ) ],
					[ 'label' => 'Apartment facilities', 'url' => vv_sidebar_menu_url( 'settings/facilities' ) ],
					[ 'label' => 'Operations checklists', 'url' => vv_sidebar_menu_url( 'staffs/checklists' ) ],
				],
			],
			[
				'type'  => 'link',
				'label' => 'Communication',
				'icon'  => 'fas fa-comments',
				'url'   => vv_sidebar_coming_soon_url( 'communication' ),
			],
			[
				'type'  => 'link',
				'label' => 'Host points system',
				'icon'  => 'fas fa-star',
				'url'   => vv_sidebar_coming_soon_url( 'host-points' ),
			],
			[
				'type'  => 'section',
				'label' => 'Account',
			],
			[
				'type'     => 'group',
				'label'    => 'Settings',
				'icon'     => 'fas fa-cog',
				'children' => [
					[ 'label' => 'Profile', 'url' => vv_sidebar_menu_url( 'admins/account' ) ],
					[ 'label' => 'Change password', 'url' => vv_sidebar_menu_url( 'admins/account' ) ],
				],
			],
		];
	}
}

if ( ! function_exists( 'vv_sidebar_menus_operations_staff' ) ) {
	function vv_sidebar_menus_operations_staff( $staff_position = '' ) {
		$menus = [
			[
				'type'  => 'link',
				'label' => 'My tasks',
				'icon'  => 'fas fa-tasks',
				'url'   => vv_sidebar_menu_url( 'tasks' ),
			],
			[
				'type'  => 'link',
				'label' => 'Checklists',
				'icon'  => 'fas fa-clipboard-check',
				'url'   => vv_sidebar_menu_url( 'staffs/checklists' ),
			],
			[
				'type'  => 'link',
				'label' => 'Settings',
				'icon'  => 'fas fa-cog',
				'url'   => vv_sidebar_menu_url( 'admins/account' ),
			],
		];

		if ( $staff_position === 'manager' ) {
			array_splice(
				$menus,
				2,
				0,
				[
					[
						'type'  => 'section',
						'label' => 'Team management',
					],
					[
						'type'  => 'link',
						'label' => 'Operations team',
						'icon'  => 'fas fa-user-cog',
						'url'   => vv_sidebar_menu_url( 'staffs' ),
					],
					[
						'type'  => 'link',
						'label' => 'Activity log',
						'icon'  => 'fas fa-history',
						'url'   => vv_sidebar_menu_url( 'staffs/activity-log' ),
					],
				]
			);
		}

		return $menus;
	}
}
