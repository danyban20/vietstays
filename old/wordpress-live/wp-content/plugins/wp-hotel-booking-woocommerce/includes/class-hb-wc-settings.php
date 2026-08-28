<?php
/**
 * HB_WC_Settings
 *
 * @author   ThimPress
 * @package  WP-Hotel-Booking/Woocommerce/Classes
 * @version  1.9.1
 */

// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'HB_WC_Settings' ) ) {
	/**
	 * Class HB_WC_Settings
	 */
	class HB_WC_Settings extends WPHB_Settings {
		public static $_instance;

		/**
		 * Get instance.
		 *
		 * @param null $prefix
		 * @param array $default *
		 *
		 * @return HB_WC_Settings|null
		 */
		public static function instance( $prefix = null, $default = array() ) {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}

			return self::$_instance;
		}

		/**
		 * HB_WC_Settings constructor.
		 */
		public function __construct() {
			// settings page
			add_filter( 'hotel_booking_admin_setting_pages', array( $this, 'admin_settings' ) );
		}

		/**
		 * Setting view.
		 */
		public function admin_settings( $tabs ) {
			$tabs[] = include_once 'admin/settings/class-wphb-admin-setting-woo.php';

			return $tabs;
		}
	}
}
