<?php
/**
 * HB_WC_Settings
 *
 * @author   ThimPress
 * @package  WP-Hotel-Booking/Woocommerce/Classes
 * @version  1.8
 */

// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WPHB_Settings' ) ) {
	return;
}

if ( ! class_exists( 'HB_WC_Settings' ) ) {
	/**
	 * Class HB_WC_Settings
	 */
	class HB_WC_Settings extends WPHB_Settings {

		/**
		 * HB_WC_Settings constructor.
		 */
		public function __construct() {

			// register new settings tab with WP Hotel Booking
			add_filter( 'hb_admin_settings_tabs', array( $this, 'register_settings' ), 101 );
			// settings page
			add_action( 'hb_admin_settings_tab_woocommerce', array( $this, 'admin_settings' ) );
		}

		/**
		 * @param $tabs
		 *
		 * @return mixed
		 */
		public function register_settings( $tabs ) {
			$tabs['woocommerce'] = __( 'WooCommerce', 'wp-hotel-booking-woocommerce' );

			return $tabs;
		}

		/**
		 * Setting view.
		 */
		public function admin_settings() {
			include hb_wc_get_admin_view( 'wc-settings' );
		}
	}
}

return new HB_WC_Settings();
