<?php
/**
 * HB_WC_Booking
 *
 * @author   ThimPress
 * @package  WP-Hotel-Booking/Woocommerce/Classes
 * @version  1.8
 */

// Prevent loading this file directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'HB_WC_Booking' ) ) {

	/**
	 * Class HB_WC_Booking
	 */
	class HB_WC_Booking {

		/**
		 * HB_WC_Booking constructor.
		 */
		public function __construct() {

			// booking change status
			add_action( 'woocommerce_order_status_changed', array( $this, 'woo_change_oder_status' ), 10, 3 );

			// booking status filter
			add_filter( 'hotel_booking_booking_total', array( $this, 'booking_status' ), 10, 3 );
		}

		/**
		 * Change order status, trigger change booking status.
		 *
		 * @param $id
		 * @param $old_status
		 * @param $new_status
		 */
		public function woo_change_oder_status( $id, $old_status, $new_status ) {
			if ( ! $booking_id = get_post_meta( $id, 'hb_wc_booking_id', true ) ) {
				return;
			}

			$book = WPHB_Booking::instance( $booking_id );

			switch ( $new_status ) {
				case 'processing':
					$status = 'processing';
					break;
				case 'pending':
					$status = 'pending';
					break;
				case 'completed':
					$status = 'completed';
					break;
				default:
					$status = 'pending';
					break;
			}
			$book->update_status( $status );
		}

		/**
		 * Booking status.
		 *
		 * @param $html
		 * @param $column_name
		 * @param $post_id
		 *
		 * @return string
		 */
		public function booking_status( $html, $column_name, $post_id ) {
			if ( ! $order_id = get_post_meta( $post_id, '_hb_woo_order_id', true ) ) {
				return $html;
			}

			$status = get_post_status( $post_id );

			if ( $column_name === 'total' ) {
				// display paid
				if ( $status === 'hb-processing' ) {
					$total    = get_post_meta( $post_id, '_hb_total', true );
					$currency = get_post_meta( $post_id, '_hb_currency', true );
					$html     = wc_price( $total, array( 'currency' => $currency ) );
				}
				$html .= '<br /><small><a href="' . esc_attr( get_edit_post_link( $order_id ) ) . '">(' . __( 'Via WooCommerce', 'wp-hotel-booking-woocommerce' ) . ')</a></small>';
			}

			return $html;
		}
	}
}

new HB_WC_Booking();
