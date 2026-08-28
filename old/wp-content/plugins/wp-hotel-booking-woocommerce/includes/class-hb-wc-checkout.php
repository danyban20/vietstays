<?php
/**
 * WPHB_Checkout
 *
 * @author   ThimPress
 * @package  WP-Hotel-Booking/Woocommerce/Classes
 * @version  1.8.4
 */

// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WPHB_Checkout' ) ) {
	return;
}

if ( ! class_exists( 'HB_WC_Checkout' ) ) {
	/**
	 * Class HB_WC_Checkout
	 */
	class HB_WC_Checkout extends WPHB_Checkout {

		/**
		 * HB_WC_Checkout constructor.
		 */
		public function __construct() {
			parent::__construct();

			// woo add new order hook
			add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'woo_add_order' ) );

			// rooms transaction object
			add_filter( 'hb_transaction_rooms', array( $this, 'woo_transaction_rooms' ), 50, 1 );

			// transaction object
			add_filter( 'hb_generate_transaction_object', array( $this, 'woo_transaction_object' ), 50, 2 );

			// tax for woocommerce in WP Hotel Booking
			add_filter( 'hotel_booking_tax_metabox', array( $this, 'tax_order' ), 10, 1 );
			add_filter( 'hotel_booking_label_details', array( $this, 'booking_tax_price' ), 10, 1 );
			add_filter( 'hotel_booking_admin_book_details', array( $this, 'booking_details_tax_price' ), 10, 2 );

			add_filter( 'woocommerce_admin_html_order_item_class', array(
				$this,
				'admin_order_room_item_class'
			), 10, 3 );
		}

		/**
		 * @param $class
		 * @param $item  WC_Order_Item_Product
		 * @param $order WC_Order
		 *
		 * @return string
		 */
		public function admin_order_room_item_class( $class, $item, $order ) {

			return $class;
		}

		/**
		 * WooCoommerce create new order
		 *
		 * @param $order_id
		 *
		 * @return bool
		 */
		public function woo_add_order( $order_id ) {

			$order = wc_get_order( $order_id );

			$cart_contents = wc()->cart->cart_contents;

			$create = false;
			foreach ( $cart_contents as $cart_key => $cart_content ) {
				if ( get_post_type( $cart_content['product_id'] ) === 'hb_room' ) {
					$create = true;
					break;
				}
			}

			if ( $create === true ) {
				if ( $booking = $this->create_booking( $order ) ) {
					WP_Hotel_Booking::instance()->cart->empty_cart();
					if( is_user_logged_in() ) {//avoid case check out by guess with other custom payment method
						WC()->session->set( 'cart', null );
						WC()->session->set( 'cart_totals', null );
						WC()->session->set( 'applied_coupons', null );
						WC()->session->set( 'coupon_discount_totals', null );
						WC()->session->set( 'coupon_discount_tax_totals', null );
						WC()->session->set( 'removed_cart_contents', null );
					}
					update_post_meta( $order_id, '_hb_woo_order_id', $booking );

					return true;
				}
			}

			return false;
		}

		/**
		 * Create booking.
		 *
		 * @param null $order
		 *
		 * @return mixed|WP_Error
		 * @throws Exception
		 */
		public function create_booking( $order = null ) {
			return WPHB_Checkout::instance()->create_booking( $order );
		}

		/**
		 * Transaction rooms.
		 *
		 * @param $rooms
		 *
		 * @return array|bool
		 */
		public function woo_transaction_rooms( $rooms ) {

			global $woocommerce;

			if ( $woocommerce->cart->is_empty() ) {
				return false;
			}

			// parse cart item
			$rooms = array();
			if ( $_rooms = $woocommerce->cart->get_cart() ) {
				foreach ( $_rooms as $key => $room ) {
					$rooms[ $key ] = apply_filters( 'hb_generate_transaction_object_room', array(
						'parent_id'      => isset( $room['parent_id'] ) ? $room['parent_id'] : null,
						'product_id'     => $room['product_id'],
						'qty'            => $room['quantity'],
						'check_in_date'  => strtotime( $room['check_in_date'] ),
						'check_out_date' => strtotime( $room['check_out_date'] ),
						'subtotal'       => $room['line_subtotal'],
						'total'          => $room['line_subtotal_tax'],
						'tax_total'      => $room['line_subtotal_tax'] - $room['line_subtotal']
					), $room );
				}
			}

			return $rooms;
		}

		/**
		 * @param $transaction
		 * @param $order
		 *
		 * @return mixed
		 */
		public function woo_transaction_object( $transaction, $order ) {
			global $woocommerce;

			if ( ! $order ) {
				return $transaction;
			}

			$transaction->booking_info['_hb_method']          = 'woo-payment';
			$transaction->booking_info['_hb_method_title']    = 'Woocommerce';
			$transaction->booking_info['_hb_total']           = round( $woocommerce->cart->total, 2 );
			$transaction->booking_info['_hb_sub_total']       = $woocommerce->cart->subtotal_ex_tax;
			$transaction->booking_info['_hb_advance_payment'] = $woocommerce->cart->total;
			// currency of default
			$transaction->booking_info['_hb_currency']    = get_woocommerce_currency();
			$transaction->booking_info['_hb_description'] = $order->customer_message ? $order->customer_message : __( 'Empty Booking Notes', 'wp-hotel-booking-woocommerce' );
			// $transaction->booking_info['_hb_coupons']					= '';
			// $transaction->booking_info['_hb_coupons_total_discount'] 	= '';
			$transaction->booking_info['_hb_tax']                  = $woocommerce->cart->get_taxes_total();
			$transaction->booking_info['_hb_woo_order_id']         = $order->id;
			$transaction->booking_info['_hb_price_including_tax']  = wc_prices_include_tax() ? 1 : 0;
			$transaction->booking_info['_hb_addition_information'] = $order->customer_message ? $order->customer_message : __( 'Empty Booking Notes', 'wp-hotel-booking-woocommerce' );
			$transaction->booking_info['_hb_customer_first_name']  = $order->billing_first_name;
			$transaction->booking_info['_hb_customer_last_name']   = $order->billing_last_name;
			$transaction->booking_info['_hb_customer_address']     = $order->billing_address_1;
			$transaction->booking_info['_hb_customer_city']        = isset( $order->billing_city ) ? $order->billing_city : '';
			$transaction->booking_info['_hb_customer_state']       = isset( $order->billing_state ) ? $order->billing_state : '';
			$transaction->booking_info['_hb_customer_postal_code'] = isset( $order->billing_postcode ) ? $order->billing_postcode : '';
			$transaction->booking_info['_hb_customer_country']     = $woocommerce->countries->countries[ $order->billing_country ];
			$transaction->booking_info['_hb_customer_email']       = $order->billing_email;
			$transaction->booking_info['_hb_customer_phone']       = isset( $order->billing_phone ) ? $order->billing_phone : '';
			if ( WC()->cart->coupons_enabled() ) {
				$coupons = WC()->cart->get_coupons();
				if ( ! empty( $coupons ) ) {
					$transaction->booking_info['_hb_coupon']       = array();
					$transaction->booking_info['_hb_coupon_code']  = array();
					$transaction->booking_info['_hb_coupon_value'] = array();
					foreach ( $coupons as $code => $coupon ) {
						$transaction->booking_info['_hb_coupon'][]       = $coupon->id;
						$transaction->booking_info['_hb_coupon_code'][]  = $code;
						$transaction->booking_info['_hb_coupon_value'][] = WC()->cart->get_coupon_discount_amount( $coupon->code, WC()->cart->display_cart_ex_tax );
					}
				}
			}

			return $transaction;
		}

		/**
		 * Tax order.
		 *
		 * @param $tax
		 *
		 * @return mixed
		 */
		public function tax_order( $tax ) {
			global $post;

			if ( ! $order_ID = get_post_meta( $post->ID, '_hb_woo_order_id', true ) ) {
				return $tax;
			}

			return get_post_meta( $post->ID, '_hb_woo_tax_total', true );
		}

		/**
		 * Admin booking tax price meta box.
		 *
		 * @param $val
		 *
		 * @return string
		 */
		public function booking_tax_price( $val ) {
			global $post;

			if ( ! $order_woo = get_post_meta( $post->ID, '_hb_woo_order_id', true ) ) {
				return $val;
			}

			if ( ! $currency = get_post_meta( $post->ID, '_hb_currency', true ) ) {
				return $val;
			}

			return wc_price( get_post_meta( $post->ID, '_hb_tax', true ), array( 'currency' => $currency ) );
		}

		/**
		 * Admin booking details tax.
		 *
		 * @param $html
		 * @param $booking
		 *
		 * @return string
		 */
		public function booking_details_tax_price( $html, $booking ) {
			if ( ! $order_woo = $booking->woo_order_id ) {
				return $html;
			}

			return wc_price( $booking->tax, array( 'currency' => $booking->currency ) );
		}
	}
}

new HB_WC_Checkout();
