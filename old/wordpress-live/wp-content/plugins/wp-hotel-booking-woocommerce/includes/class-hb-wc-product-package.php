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

if ( ! class_exists( 'WC_Product' ) ) {
	return;
}

global $woocommerce;

if ( ! class_exists( 'HB_WC_Product_Package' ) ) {
	/**
	 * Class HB_WC_Product_Package
	 */
	class HB_WC_Product_Package extends WC_Product {

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
		 * @param int $product
		 */
		public function __construct( $product = 0 ) {
			if ( ! class_exists( 'HB_Extra_Package' ) ) {
				return;
			}

			if ( is_numeric( $product ) && $product > 0 ) {
				$this->set_id( $product );
			} elseif ( $product instanceof self ) {
				$this->set_id( absint( $product->get_id() ) );
			} elseif ( ! empty( $product->ID ) ) {
				$this->set_id( absint( $product->ID ) );
			}
		}

		/**
		 * @param string $context
		 *
		 * @return int|string
		 */
		public function get_image_id( $context = 'view' ) {
			$package_id = $this->get_id();
			if ( get_post_type( $package_id ) === 'hb_extra_room' ) {

				return get_post_thumbnail_id( $package_id );
			}
		}

		/**
		 * @param string $context
		 *
		 * @return float|int|string
		 */
		public function get_price( $context = 'view' ) {

			global $woocommerce;
			$cart    = $woocommerce->cart->get_cart();
			$hb_cart = WPHB_Cart::instance()->cart_contents;
			$qty     = 1;
			$night   = 1;

			$this->package = HB_Extra_Package::instance(
				$this->get_id(),
				array(
					'room_quantity' => $qty,
					'quantity'      => 1,
				)
			);

			foreach ( $cart as $key => $item ) {
				if ( $item['product_id'] == $this->get_id() ) {
					if ( get_post_meta( $this->get_id(), 'tp_hb_extra_room_respondent', true ) == 'number' ) {
						$parent_item = $hb_cart[ $item['parent_id'] ];
						if ( ! empty( $parent_item ) ) {
							$night = hb_count_nights_two_dates( $parent_item->check_out_date, $parent_item->check_in_date );
						}
					}
				}
			}

			return $this->package->amount_singular_exclude_tax() * $night;
		}

		/**
		 * @return bool
		 */
		public function is_sold_individually() {
			if ( ! class_exists( 'HB_Extra_Package' ) ) {
				return parent::is_sold_individually();
			}

			$package = HB_Extra_Package::instance( $this->get_id() );

			if ( ! $package->respondent ) {
				return parent::is_sold_individually();
			}

			if ( $package->respondent === 'trip' ) {
				return true;
			}

			return false;
		}

		/**
		 * @param string $context
		 *
		 * @return bool
		 */
		public function is_purchasable( $context = 'view' ) {
			return true;
		}

		/**
		 * @return bool
		 */
		public function is_in_stock() {
			return true;
		}

		/**
		 * @param string $context
		 *
		 * @return bool
		 */
		public function exists( $context = 'view' ) {
			return $this->get_id() && ( get_post_type( $this->get_id() ) == 'hb_extra_room' ) && ( ! in_array(
				get_post_status( $this->get_id() ),
				array(
					'draft',
					'auto-draft',
				)
			) );
		}

		/**
		 * @return bool
		 */
		public function is_virtual() {
			return true;
		}

		/**
		 * @param string $context
		 *
		 * @return string
		 */
		public function get_name( $context = 'view' ) {
			return get_the_title( $this->get_id() );
		}
	}
}
