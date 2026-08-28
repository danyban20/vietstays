<?php
/**
 * WP Hotel Booking admin setting advanced.
 *
 * @version     1.9.6
 * @author      ThimPress
 * @package     WP_Hotel_Booking/Classes
 * @category    Classes
 * @author      DoNgocPhuc
 */

/**
 * Prevent loading this file directly
 */
defined( 'ABSPATH' ) || exit;

class WPHB_Admin_Setting_Woocommerce extends WPHB_Admin_Setting_Page {

	public $id = 'woocommerce';

	public $title = null;

	public function __construct() {
		$this->title = __( 'Woocommerce', 'wp-hotel-booking' );

		parent::__construct();
	}

	public function get_settings() {
		return apply_filters(
			'hotel_booking_admin_setting_fields_' . $this->id,
			array(
				array(
					'type' => 'section_start',
					'id'   => 'tp_debug_mode',
				),
				array(
					'id'      => WPHB_Settings::instance()->get_field_name( 'wc_enable' ),
					'type'    => 'checkbox',
					'default' => 0,
					'title'   => __( 'Enable', 'wp-hotel-booking-woocommerce' ),
					'desc'    => __( 'Check this option to enable make booking payments via WooCommerce', 'wp-hotel-booking-woocommerce' ),
				),
				array(
					'type' => 'section_end',
					'id'   => 'tp_debug_mode',
				),
			)
		);
	}
}


return new WPHB_Admin_Setting_Woocommerce();
