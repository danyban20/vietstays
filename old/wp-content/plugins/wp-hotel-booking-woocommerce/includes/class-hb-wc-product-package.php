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

if ( ! class_exists( 'WC_Product_Simple' ) ) {
	return;
}

global $woocommerce;

if ( $woocommerce && version_compare( $woocommerce->version, '3.0.0', '<' ) ) {
	require_once 'class-hb-wc-2x-product-package.php';

	return;
} else {
	if ( ! class_exists( 'HB_WC_Product_Package' ) ) {
		/**
		 * Class HB_WC_Product_Package
		 */
		class HB_WC_Product_Package extends WC_Product_Simple {

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
				$cart = $woocommerce->cart->get_cart();

				$qty = $night = 1;

				$this->package = HB_Extra_Package::instance( $this->get_id(), array(
					'room_quantity' => $qty,
					'quantity'      => 1
				) );

				foreach ( $cart as $key => $item ) {
					if ( $item['product_id'] == $this->get_id() ) {
						if ( get_post_meta( $this->get_id(), 'tp_hb_extra_room_respondent', true ) == 'number' ) {
							$night = hb_count_nights_two_dates( $item['check_out_date'], $item['check_in_date'] );
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
				return $this->get_id() && ( get_post_type( $this->get_id() ) == 'hb_extra_room' ) && ( ! in_array( get_post_status( $this->get_id() ), array(
						'draft',
						'auto-draft'
					) ) );
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
}
