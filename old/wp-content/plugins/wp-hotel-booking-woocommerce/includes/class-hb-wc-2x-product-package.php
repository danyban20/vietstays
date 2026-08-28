<?php
/**
 * HB_WC_Product_Package
 *
 * @author   ThimPress
 * @package  WP-Hotel-Booking/Woocommerce/Classes
 * @version  1.8
 */

// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'HB_WC_Product_Package' ) ) {
	/**
	 * Class HB_WC_Product_Package
	 */
	class HB_WC_Product_Package extends WC_Product_Simple {

		/**
		 * @var null
		 */
		public $data = null;

		/**
		 * @var
		 */
		public $total;

		/**
		 * @var null
		 */
		public $package = null;

		/**
		 * HB_WC_Product_Package constructor.
		 *
		 * @param $the_product
		 * @param null $args
		 */
		public function __construct( $the_product, $args = null ) {
			parent::__construct( $the_product, $args );

			if ( ! class_exists( 'HB_Extra_Package' ) ) {
				return;
			}
		}

		/**
		 * @return string
		 */
		public function get_price() {
			$qty = 1;
			if ( ! isset( $this->data['parent_id'] ) ) {
				$parent = WPHB_Cart::instance()->get_cart_item( $this->data['parent_id'] );
				$qty    = $parent->quantity;
			} else if ( isset( $this->data['woo_cart_id'] ) ) {
				$parent = WC()->cart->get_cart_item( $this->data['woo_cart_id'] );
				$qty    = $parent['quantity'];
			}

			$this->package = HB_Extra_Package::instance( $this->post, array(
				'check_in_date'  => $this->data['check_in_date'],
				'check_out_date' => $this->data['check_out_date'],
				'room_quantity'  => $qty,
				'quantity'       => 1
			) );

			return $this->package->amount_singular_exclude_tax();
		}

		/**
		 * @return bool
		 */
		public function is_sold_individually() {
			if ( ! class_exists( 'HB_Extra_Package' ) ) {
				return parent::is_sold_individually();
			}

			$package = HB_Extra_Package::instance( $this->post );

			if ( ! $package->respondent ) {
				return parent::is_sold_individually();
			}

			if ( $package->respondent === 'trip' ) {
				return true;
			}

			return false;
		}

		/**
		 * @return bool
		 */
		public function is_purchasable() {
			return true;
		}
	}
}