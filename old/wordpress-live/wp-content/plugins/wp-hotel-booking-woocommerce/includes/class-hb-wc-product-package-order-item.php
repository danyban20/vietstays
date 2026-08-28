<?php

/**
 * @class WC_Order_Item_WPEMS_Product
 */

defined( 'ABSPATH' ) || exit();

class WC_Order_Item_HBPackage_Product extends WC_Order_Item_Product {
	/**
	 * @throws Exception
	 */
	public function set_product_id( $value ) {
		if ( $value > 0 && 'hb_extra_room' !== get_post_type( absint( $value ) ) ) {
			$this->error( 'order_item_product_invalid_product_id', __( 'Invalid product ID', 'woocommerce' ) );
		}

		$package_id = wc_get_order_item_meta( $this->get_id(), '_hb_extra_room_id' );
		if ( 'hb_extra_room' == get_post_type( absint( $package_id ) ) ) {
			$value = $package_id;
		}

		$this->set_prop( 'product_id', absint( $value ) );
	}
}
