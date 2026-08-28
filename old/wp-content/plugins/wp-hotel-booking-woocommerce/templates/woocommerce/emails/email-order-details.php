<?php
/**
 * Order hotel item details in woocommerce email.
 *
 * This template can be overridden by copying it to yourtheme/wp-hotel-booking-woocommerce/woocommerce/emails/email-order-details.php.
 *
 * @author  ThimPress
 * @package WP-Hotel-Booking/Woocommerce/Template
 * @version 1.8.4
 */

/**
 * @var $sent_to_admin
 * @var $plain_text
 */

// get booking items
$booking_id = get_post_meta( $order->get_id(), '_hb_woo_order_id', true );
$room_items = hb_get_order_items( $booking_id );
$text_align = is_rtl() ? 'right' : 'left';

do_action( 'woocommerce_email_before_order_table', $order, $sent_to_admin, $plain_text, $email ); ?>

<?php if ( ! $sent_to_admin ) { ?>
    <h2><?php printf( __( 'Order #%s', 'wp-hotel-booking-woocommerce' ), $order->get_order_number() ); ?>
        (<?php printf( '<time datetime="%s">%s</time>', $order->get_date_created()->format( 'c' ), wc_format_datetime( $order->get_date_created() ) ); ?>
        )</h2>
<?php } else { ?>
    <h2><a class="link"
           href="<?php echo esc_url( admin_url( 'post.php?post=' . $order->get_id() . '&action=edit' ) ); ?>"><?php printf( __( 'Order #%s', 'wp-hotel-booking-woocommerce' ), $order->get_order_number() ); ?></a>
        (<?php printf( '<time datetime="%s">%s</time>', $order->get_date_created()->format( 'c' ), wc_format_datetime( $order->get_date_created() ) ); ?>
        )</h2>
<?php } ?>

<table class="td" cellspacing="0" cellpadding="6"
       style="width: 100%; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; margin-bottom: 40px;"
       border="1">
    <thead>
    <tr>
        <th class="td" scope="col"
            style="text-align:<?php echo $text_align; ?>;"><?php _e( 'Product', 'wp-hotel-booking-woocommerce' ); ?></th>
		<?php if ( $room_items ) { ?>
            <th class="td" scope="col" style="text-align:<?php echo $text_align; ?>;">
				<?php _e( 'Check in', 'wp-hotel-booking-woocommerce' ) ?>
            </th>
            <th class="td" scope="col" style="text-align:<?php echo $text_align; ?>;">
				<?php _e( 'Check out', 'wp-hotel-booking-woocommerce' ) ?>
            </th>
		<?php } ?>
        <th class="td" scope="col"
            style="text-align:<?php echo $text_align; ?>;"><?php _e( 'Quantity', 'wp-hotel-booking-woocommerce' ); ?></th>
        <th class="td" scope="col"
            style="text-align:<?php echo $text_align; ?>;"><?php _e( 'Price', 'wp-hotel-booking-woocommerce' ); ?></th>
    </tr>
    </thead>
    <tbody>

	<?php echo wc_get_email_order_items( $order, array(
		'show_sku'      => $sent_to_admin,
		'show_image'    => false,
		'image_size'    => array( 32, 32 ),
		'plain_text'    => $plain_text,
		'sent_to_admin' => $sent_to_admin,
	) ); ?>

    </tbody>
    <tfoot>

	<?php
	if ( $totals = $order->get_order_item_totals() ) {
		$i = 0;
		foreach ( $totals as $total ) {
			$i ++; ?>
            <tr>
                <th class="td" scope="row" colspan="4"
                    style="text-align:<?php echo $text_align; ?>; <?php echo ( 1 === $i ) ? 'border-top-width: 4px;' : ''; ?>"><?php echo $total['label']; ?></th>
                <td class="td"
                    style="text-align:<?php echo $text_align; ?>; <?php echo ( 1 === $i ) ? 'border-top-width: 4px;' : ''; ?>"><?php echo $total['value']; ?></td>
            </tr>
		<?php }
	}
	if ( $order->get_customer_note() ) { ?>
        <tr>
            <th class="td" scope="row" colspan="4"
                style="text-align:<?php echo $text_align; ?>;"><?php _e( 'Note:', 'wp-hotel-booking-woocommerce' ); ?></th>
            <td class="td"
                style="text-align:<?php echo $text_align; ?>;"><?php echo wptexturize( $order->get_customer_note() ); ?></td>
        </tr>
	<?php } ?>
    </tfoot>
</table>

<?php do_action( 'woocommerce_email_after_order_table', $order, $sent_to_admin, $plain_text, $email ); ?>
