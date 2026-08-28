<?php
/**
 * Woocommerce Cart page.
 *
 * This template can be overridden by copying it to yourtheme/wp-hotel-booking-woocommerce/woocommerce/cart/cart.php.
 *
 * @author  ThimPress
 * @package WP-Hotel-Booking/Woocommerce/Template
 * @version 1.8.5
 */

$cart_items = WC()->cart->get_cart();
$have_room  = false;

foreach ( $cart_items as $key => $cart_item ) {
	if ( get_post_type( $cart_item['product_id'] ) == 'hb_room' ) {
		$have_room = true;

		break;
	}
}

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_cart' ); ?>

<form class="woocommerce-cart-form" action="<?php echo esc_url( wc_get_cart_url() ); ?>" method="post">
	<?php do_action( 'woocommerce_before_cart_table' ); ?>

    <table class="shop_table shop_table_responsive cart woocommerce-cart-form__contents" cellspacing="0">
        <thead>
        <tr>
            <th class="product-remove">&nbsp;</th>
            <th class="product-thumbnail">&nbsp;</th>
            <th class="product-name"><?php esc_html_e( 'Product', 'wp-hotel-booking-woocommerce' ); ?></th>
			<?php if ( $have_room ) { ?>
                <th class="check-in-out"><?php esc_html_e( 'Check In - Check Out', 'wp-hotel-booking-woocommerce' ); ?></th>
			<?php } ?>
            <th class="product-price"><?php esc_html_e( 'Price', 'wp-hotel-booking-woocommerce' ); ?></th>
            <th class="product-quantity"><?php esc_html_e( 'Quantity', 'wp-hotel-booking-woocommerce' ); ?></th>
            <th class="product-subtotal"><?php esc_html_e( 'Total', 'wp-hotel-booking-woocommerce' ); ?></th>
        </tr>
        </thead>
        <tbody>
		<?php do_action( 'woocommerce_before_cart_contents' ); ?>

		<?php foreach ( $cart_items as $cart_item_key => $cart_item ) {
			$_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
			$product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

			if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
				$product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
				?>
                <tr class="woocommerce-cart-form__cart-item <?php echo esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key ) ); ?>">

                    <td class="product-remove">
						<?php
						// @codingStandardsIgnoreLine
						echo apply_filters( 'woocommerce_cart_item_remove_link', sprintf(
							'<a href="%s" class="remove" aria-label="%s" data-product_id="%s" data-product_sku="%s">&times;</a>',
							esc_url( wc_get_cart_remove_url( $cart_item_key ) ),
							__( 'Remove this item', 'wp-hotel-booking-woocommerce' ),
							esc_attr( $product_id ),
							esc_attr( $_product->get_sku() )
						), $cart_item_key );
						?>
                    </td>

                    <td class="product-thumbnail">
						<?php
                        if( get_post_type( $product_id ) !== 'hb_extra_room' ){
	                        $thumbnail = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key );

	                        if ( ! $product_permalink ) {
		                        echo $thumbnail; // PHPCS: XSS ok.
	                        } else {
		                        printf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $thumbnail ); // PHPCS: XSS ok.
	                        }
                        }
						?>
                    </td>

                    <td class="product-name"
                        data-title="<?php esc_attr_e( 'Product', 'wp-hotel-booking-woocommerce' ); ?>">
						<?php
						if ( ! $product_permalink ) {
							echo wp_kses_post( apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key ) . '&nbsp;' );
						} else {
							echo wp_kses_post( apply_filters( 'woocommerce_cart_item_name', sprintf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $_product->get_name() ), $cart_item, $cart_item_key ) );
						}

						do_action( 'woocommerce_after_cart_item_name', $cart_item, $cart_item_key );

						// Meta data.
						echo wc_get_formatted_cart_item_data( $cart_item ); // PHPCS: XSS ok.

						// Backorder notification.
						if ( $_product->backorders_require_notification() && $_product->is_on_backorder( $cart_item['quantity'] ) ) {
							echo wp_kses_post( apply_filters( 'woocommerce_cart_item_backorder_notification', '<p class="backorder_notification">' . esc_html__( 'Available on backorder', 'wp-hotel-booking-woocommerce' ) . '</p>', $product_id ) );
						}
						?>
                    </td>

					<?php if ( $have_room ) { ?>
                        <td class="product-check-in-out"><?php echo isset( $cart_item['check_in_date'] ) ? date_i18n( hb_get_date_format(), strtotime( $cart_item['check_in_date'] ) ) . ' - ' . date_i18n( hb_get_date_format(), strtotime( $cart_item['check_out_date'] ) ) : ''; ?></td>
					<?php } ?>

                    <td class="product-price"
                        data-title="<?php esc_attr_e( 'Price', 'wp-hotel-booking-woocommerce' ); ?>">
						<?php
						echo apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key ); // PHPCS: XSS ok.
						?>
                    </td>

                    <td class="product-quantity"
                        data-title="<?php esc_attr_e( 'Quantity', 'wp-hotel-booking-woocommerce' ); ?>">
						<?php
						if ( $_product->is_sold_individually() ) {
							$product_quantity = sprintf( '1 <input type="hidden" name="cart[%s][qty]" value="1" />', $cart_item_key );
						} else {
							$product_quantity = woocommerce_quantity_input( array(
								'input_name'   => "cart[{$cart_item_key}][qty]",
								'input_value'  => $cart_item['quantity'],
								'max_value'    => get_post_meta($_product->get_id(), '_hb_num_of_rooms', true ),
								'min_value'    => '0',
								'product_name' => $_product->get_name(),
							), $_product, false );
						}

						echo apply_filters( 'woocommerce_cart_item_quantity', $product_quantity, $cart_item_key, $cart_item ); // PHPCS: XSS ok.
						?>
                    </td>

                    <td class="product-subtotal"
                        data-title="<?php esc_attr_e( 'Total', 'wp-hotel-booking-woocommerce' ); ?>">
						<?php
						echo apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key ); // PHPCS: XSS ok.
						?>
                    </td>
                </tr>
				<?php
			}
		}
		?>

		<?php do_action( 'woocommerce_cart_contents' ); ?>

        <tr>
            <td colspan="7" class="actions">

				<?php if ( wc_coupons_enabled() ) { ?>
                    <div class="coupon">
                        <label for="coupon_code"><?php esc_html_e( 'Coupon:', 'wp-hotel-booking-woocommerce' ); ?></label>
                        <input
                                type="text" name="coupon_code" class="input-text" id="coupon_code" value=""
                                placeholder="<?php esc_attr_e( 'Coupon code', 'wp-hotel-booking-woocommerce' ); ?>"/>
                        <button type="submit" class="button" name="apply_coupon"
                                value="<?php esc_attr_e( 'Apply coupon', 'wp-hotel-booking-woocommerce' ); ?>"><?php esc_attr_e( 'Apply coupon', 'wp-hotel-booking-woocommerce' ); ?></button>
						<?php do_action( 'woocommerce_cart_coupon' ); ?>
                    </div>
				<?php } ?>

                <button type="submit" class="button" name="update_cart"
                        value="<?php esc_attr_e( 'Update cart', 'wp-hotel-booking-woocommerce' ); ?>"><?php esc_html_e( 'Update cart', 'wp-hotel-booking-woocommerce' ); ?></button>

				<?php do_action( 'woocommerce_cart_actions' ); ?>

				<?php wp_nonce_field( 'woocommerce-cart', 'woocommerce-cart-nonce' ); ?>
            </td>
        </tr>

		<?php do_action( 'woocommerce_after_cart_contents' ); ?>
        </tbody>
    </table>
	<?php do_action( 'woocommerce_after_cart_table' ); ?>
</form>

<div class="cart-collaterals">
	<?php
	/**
	 * Cart collaterals hook.
	 *
	 * @hooked woocommerce_cross_sell_display
	 * @hooked woocommerce_cart_totals - 10
	 */
	do_action( 'woocommerce_cart_collaterals' );
	?>
</div>

<?php do_action( 'woocommerce_after_cart' ); ?>
