<?php
/**
 * HB_WC_Product_Room
 *
 * @author   ThimPress
 * @package  WP-Hotel-Booking/Woocommerce/Classes
 * @version  1.8
 */

// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WC_Product_Simple' ) ) {
	return;
}

if ( ! class_exists( 'HB_WC_Product_Room' ) ) {
	/**
	 * Class HB_WC_Product_Room
	 */
	class HB_WC_Product_Room extends WC_Product_Simple {

		/**
		 * @var null
		 */
		public $data = null;

		/**
		 * @var
		 */
		public $total;

		/**
		 * HB_WC_Product_Room constructor.
		 *
		 * @param $the_product
		 * @param null $args
		 */
		public function __construct( $the_product, $args = null ) {
			parent::__construct( $the_product, $args );
		}

		/**
		 * @param string $context
		 *
		 * @return int|string
		 */
		public function get_image_id( $context = 'view' ) {
			$event_id = $this->get_id();
			if ( get_post_type( $event_id ) === 'hb_room' ) {

				return get_post_thumbnail_id( $event_id );
			}
		}

		/**
		 * @return string
		 */
		public function get_price() {
			$room = WPHB_Room::instance( $this->post, $this->data );

			return $room->amount_singular_exclude_tax;
		}

		/**
		 * Check if a product is purchasable
		 */
		public function is_purchasable() {
			return true;
		}
	}
}
