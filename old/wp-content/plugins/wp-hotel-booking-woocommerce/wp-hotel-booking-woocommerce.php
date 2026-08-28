<?php
/**
 * Plugin Name: WP Hotel Booking WooCommerce
 * Plugin URI: http://thimpress.com/
 * Description: Support paying for a booking with the payment system provided by WooCommerce
 * Author: ThimPress
 * Version: 1.9.5
 * Author URI: http://thimpress.com
 * Tags: wphb
 * Requires at least: 3.5
 * Tested up to: 6.0
 * WC tested up to: 6.9.4
 * Text Domain: wp-hotel-booking-woocommerce
 * Domain Path: /lang/
 */

if ( ! class_exists( 'WP_Hotel_Booking_Woocommerce' ) ) {

	/**
	 * Class WP_Hotel_Booking_Woocommerce
	 */
	class WP_Hotel_Booking_Woocommerce {

		/**
		 * @var null
		 */
		protected static $_instance = null;

		/**
		 * @var bool
		 */
		protected static $_wc_loaded = false;

		/**
		 * WP_Hotel_Booking_Woocommerce constructor.
		 */
		public function __construct() {

			$this->_defines();


			require_once "includes/functions.php";
			require_once "includes/class-hb-wc-settings.php";

			if ( self::wc_enable() ) {
				$this->_includes();
				// define plugin enable
				define( 'HB_WC_ENABLE', true );
				add_action( 'wp_enqueue_scripts', array( $this, 'frontend_scripts' ) );

				//				// woommerce currency
				add_filter( 'hb_currency', array( $this, 'woocommerce_currency' ), 50 );
				add_filter( 'hotel_booking_payment_current_currency', array( $this, 'woocommerce_currency' ), 50 );
				add_filter( 'hb_currency_symbol', array( $this, 'woocommerce_currency_symbol' ), 50, 2 );
				add_filter( 'hb_price_format', array( $this, 'woocommerce_price_format' ), 50, 3 );

				// filter price
				add_filter( 'hotel_booking_room_total_price_incl_tax', array( $this, 'room_price_tax' ), 10, 2 );

				add_filter( 'hotel_booking_room_item_total_exclude_tax', array( $this, 'room_price_regular_excl' ), 10, 2 );
				add_filter( 'hotel_booking_room_item_total_include_tax', array( $this, 'room_price_regular_incl' ), 10, 2 );

				add_filter( 'hotel_booking_package_item_total_exclude_tax', array( $this, 'packages_regular_price_excl' ), 10, 2 );
				add_filter( 'hotel_booking_package_item_total_include_tax', array( $this, 'packages_regular_price_incl' ), 10, 2 );
				add_filter( 'hotel_booking_package_amount_singular', array( $this, 'packages_singular_price' ), 10, 2 );

				// extra package price
				add_filter( 'hotel_booking_extra_package_regular_price_incl_tax', array( $this, 'packages_regular_price_tax' ), 10, 3 );

				// cart amount(item total price)
				add_filter( 'hotel_booking_cart_item_total_amount', array(
					$this,
					'hotel_booking_cart_item_total_amount'
				), 10, 4 );
				add_filter( 'hotel_booking_cart_item_amount_singular', array(
					$this,
					'hotel_booking_cart_item_amount_singular'
				), 10, 4 );
				// tax enable
				add_filter( 'hb_price_including_tax', array( $this, 'hb_price_including_tax' ), 10, 2 );

				// trigger WC cart room item
				add_action( 'hotel_booking_added_cart', array( $this, 'hotel_add_to_cart' ), 10, 3 );
				// trigger WC remove cart room item
				add_action( 'hotel_booking_remove_cart_item', array( $this, 'hotel_remove_cart_item' ), 10, 2 );
				// return cart url
				add_filter( 'hb_cart_url', array( $this, 'hotel_cart_url' ) );
				// return checkout url
				add_filter( 'hb_checkout_url', array( $this, 'hotel_checkout_url' ), 999 );

				// display tax price
				add_filter( 'hotel_booking_cart_tax_display', array( $this, 'cart_tax_display' ) );
				add_filter( 'hotel_booking_get_cart_total', array( $this, 'cart_total_result_display' ) );
				add_action( 'template_redirect', array( $this, 'template_redirect' ), 50 );

				// add woo cart item
				add_filter( 'woocommerce_add_cart_item', array( $this, 'add_cart_item' ), 10, 2 );
				// remove woo cart item
				add_action( 'woocommerce_remove_cart_item', array( $this, 'woocommerce_remove_cart_item' ), 10, 2 );
				// update woo cart item
				add_filter( 'woocommerce_update_cart_validation', array( $this, 'woocommerce_update_cart' ), 10, 4 );
				// restore woo cart item
				add_action( 'woocommerce_restore_cart_item', array( $this, 'woocommerce_restore_cart_item' ), 10, 2 );

				add_filter( 'woocommerce_cart_item_class', array(
					$this,
					'woocommerce_cart_package_item_class'
				), 10, 3 );

				add_filter( 'woocommerce_cart_item_remove_link', array( $this, 'remove_cart_extra_required' ), 10, 2 );

				add_action( 'woocommerce_order_status_changed', array(
					$this,
					'woocommerce_order_status_changed'
				), 10, 4 );

				// sort room - product item
				add_action( 'woocommerce_cart_loaded_from_session', array( $this, 'woo_sort_rooms' ), 999 );

				add_filter( 'woocommerce_product_class', array( $this, 'product_class' ), 10, 4 );
				add_filter( 'woocommerce_get_cart_item_from_session', array(
					$this,
					'get_cart_item_from_session'
				), 10, 3 );
				// tax enable
				add_filter( 'hotel_booking_extra_tax_enable', array( $this, 'tax_enable' ) );

				// override woo mail templates
				add_filter( 'woocommerce_locate_template', array( $this, 'woo_booking_mail_template' ), 10, 3 );
			} else {
				define( 'HB_WC_ENABLE', false );
			}
		}

		/**
		 * Check plugin Woo activated.
		 */
		public static function check_woo_activated(): bool {
			if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
				add_action( 'admin_notices', array( __CLASS__, 'show_note_errors_install_plugin_woo' ) );

				deactivate_plugins( plugin_basename( __FILE__ ) );

				if ( isset( $_GET['activate'] ) ) {
					unset( $_GET['activate'] );
				}

				return false;
			}

			return true;
		}


		public static function show_note_errors_install_plugin_woo() {
			?>
            <div class="notice notice-error">
                <p><?php echo 'Please active plugin <strong>Woocomerce</strong> before active plugin <strong>WP Hotel Booking WooCommerce</strong>'; ?></p>
            </div>
			<?php
		}

		/**
		 * Override woo mail templates.
		 *
		 * @param $template
		 * @param $template_name
		 * @param $template_path
		 *
		 * @return string
		 * @since 2.0
		 *
		 */
		public function woo_booking_mail_template( $template, $template_name, $template_path ) {
			global $woocommerce;

			$_template = $template;
			if ( ! $template_path ) {
				$template_path = $woocommerce->template_url;
			}

			$plugin_path = WPHB_WOO_PAYMENT_ABSPATH . '/templates/woocommerce/';
			// Look within passed path within the theme - this is priority
			$template = locate_template( array( $template_path . $template_name, $template_name ) );

			// Modification: Get the template from this plugin, if it exists
			if ( ! $template && file_exists( $plugin_path . $template_name ) ) {
				$template = $plugin_path . $template_name;
			}

			// Use default template
			if ( ! $template ) {
				$template = $_template;
			}

			// Return what we found
			return $template;
		}

		// tax enable
		function tax_enable( $enable ) {
			// woocommercer option
			if ( get_option( 'woocommerce_tax_display_shop' ) === 'incl' ) {
				return true;
			}

			return false;
		}

		/**
		 * @param $cart_item_id
		 * @param $params
		 * @param $posts
		 *
		 * @return string
		 */
		public function hotel_add_to_cart( $cart_item_id, $params, $posts ) {
			global $woocommerce;
			wc_load_cart();

			if ( ! $woocommerce || ! $woocommerce->cart ) {
				return $cart_item_id;
			}

			$cart_items = $woocommerce->cart->get_cart();

			$woo_cart_param = array(
				'product_id'     => $params['product_id'],
				'check_in_date'  => $params['check_in_date'],
				'check_out_date' => $params['check_out_date']
			);

			if ( isset( $params['parent_id'] ) ) {
				$woo_cart_param['parent_id'] = $params['parent_id'];
			}
			if ( isset( $params['hb_optional_quantity_selected'] ) && isset( $params['hb_optional_quantity'] ) ) {
				$woo_cart_param['hb_optional_quantity_selected'] = $params['hb_optional_quantity_selected'];
				$woo_cart_param['hb_optional_quantity']          = $params['hb_optional_quantity'];
			}

			$woo_cart_param = apply_filters( 'hotel_booking_wc_cart_params', $woo_cart_param, $cart_item_id );

			$woo_cart_id = $woocommerce->cart->generate_cart_id( $woo_cart_param['product_id'], null, array(), $woo_cart_param );
			if ( array_key_exists( $woo_cart_id, $cart_items ) ) {
				$new_quantity = absint( $params['quantity'] ) + absint( $woocommerce->cart->cart_contents[ $woo_cart_id ]['quantity'] );
				$woocommerce->cart->set_quantity( $woo_cart_id, $new_quantity);
			} else {
				// WC()->cart->add_to_cart( $woo_cart_param['product_id'], $params['quantity'] );
				if ( class_exists( 'SitePress' ) ) {
					global $sitepress;

					$product_id = $woo_cart_param['product_id'];
					$post_type  = get_post_type( $product_id );
					if ( in_array( $post_type, array( 'hb_room', 'hb_extra_room' ) ) ) {
						$languages = $sitepress->get_ls_languages();

						//					foreach ( array_keys( $languages ) as $language ) {
						//						$duplicate_product_id = apply_filters( 'wpml_object_id', $woo_cart_param['product_id'], $post_type, false, $language );
						//						if ( $duplicate_product_id ) {
						//							$woocommerce->cart->add_to_cart( $duplicate_product_id, $params['quantity'], null, array(), $woo_cart_param );
						//						}
						//					}

						$woocommerce->cart->add_to_cart( $woo_cart_param['product_id'], $params['quantity'], null, array(), $woo_cart_param );
					}
				} else {
					$woocommerce->cart->add_to_cart( $woo_cart_param['product_id'], $params['quantity'], null, array(), $woo_cart_param );
				}
			}
			//						add_action( 'hotel_booking_added_cart', array( $this, 'hotel_add_to_cart' ), 10, 2 );
			do_action( 'hb_wc_after_add_to_cart', $cart_item_id, $params );

			return $cart_item_id;
		}

		/**
		 * @param $cart_item_id
		 * @param $remove_params
		 */
		public function hotel_remove_cart_item( $cart_item_id, $remove_params ) {
			remove_action( 'hotel_booking_remove_cart_item', array( $this, 'hotel_remove_cart_item' ), 10 );
			global $woocommerce;

			if ( ! $woocommerce->cart ) {
				return;
			}

			$woo_cart_items = $woocommerce->cart->cart_contents;

			$woo_cart_id = $woocommerce->cart->generate_cart_id( $remove_params['product_id'], null, array(), $remove_params );

			if ( array_key_exists( $woo_cart_id, $woo_cart_items ) ) {
				$woocommerce->cart->remove_cart_item( $woo_cart_id );
			}

			if ( ! isset( $remove_params['parent_id'] ) ) {
				foreach ( $woo_cart_items as $cart_id => $cart_item ) {
					if ( ! isset( $cart_item['check_in_date'] ) || ! isset( $cart_item['check_out_date'] ) || ! isset( $cart_item['parent_id'] ) ) {
						continue;
					}

					if ( $cart_item['parent_id'] === $cart_item_id ) {
						$woocommerce->cart->remove_cart_item( $cart_id );
					}
				}
			}

			add_action( 'hotel_booking_remove_cart_item', array( $this, 'hotel_remove_cart_item' ), 10, 2 );
			do_action( 'hb_wc_remove_cart_room_item', $cart_item_id );
		}

		/**
		 * @param $cart_item_key
		 * @param $cart WC_Cart
		 */
		public function woocommerce_remove_cart_item( $cart_item_key, $cart ) {
			remove_action( 'woocommerce_remove_cart_item', array( $this, 'woocommerce_remove_cart_item' ), 10 );
			if ( $cart_item = $cart->get_cart_item( $cart_item_key ) ) {
				if ( ! isset( $cart_item['check_in_date'] ) && ! isset( $cart_item['check_out_date'] ) ) {
					return;
				}

				add_action( 'woocommerce_remove_cart_item', array( $this, 'woocommerce_remove_cart_item' ), 10, 2 );
				$hotel_cart_param = array(
					'product_id'     => $cart_item['product_id'],
					'check_in_date'  => $cart_item['check_in_date'],
					'check_out_date' => $cart_item['check_out_date']
				);

				if ( isset( $cart_item['parent_id'] ) ) {
					$hotel_cart_param['parent_id'] = $cart_item['parent_id'];
				}

				$hotel_cart = WP_Hotel_Booking::instance()->cart;
				/**
				 * @var $hotel_cart WPHB_Cart
				 */
				$hotel_cart_id = $hotel_cart->generate_cart_id( $hotel_cart_param );

				$hotel_cart_contents = $hotel_cart->cart_contents;

				if ( array_key_exists( $hotel_cart_id, $hotel_cart_contents ) ) {
					$hotel_cart->remove_cart_item( $hotel_cart_id );
				}
			}
		}

		/**
		 * @param $return
		 * @param $cart_item_key
		 * @param $values
		 * @param $quantity
		 *
		 * @return mixed
		 */
		public function woocommerce_update_cart( $return, $cart_item_key, $values, $quantity ) {
			global $woocommerce;
			if ( $cart_item = $woocommerce->cart->get_cart_item( $cart_item_key ) ) {
				if ( ! isset( $cart_item['check_in_date'] ) && ! isset( $cart_item['check_out_date'] ) ) {
					return $return;
				}

				// param render hotel cart id
				$hotel_cart_param = array(
					'product_id'     => $cart_item['product_id'],
					'check_in_date'  => $cart_item['check_in_date'],
					'check_out_date' => $cart_item['check_out_date']
				);

				if ( isset( $cart_item['parent_id'] ) ) {
					$hotel_cart_param['parent_id'] = $cart_item['parent_id'];
				}

				// hotel cart id
				$hotel_cart = WP_Hotel_Booking::instance()->cart;
				/**
				 * @var $hotel_cart WPHB_Cart
				 */
				$hotel_cart_id = $hotel_cart->generate_cart_id( $hotel_cart_param );
				$hotel_cart->update_cart_item( $hotel_cart_id, $quantity );
			}

			do_action( 'hb_wc_update_cart', $return, $cart_item_key, $values, $quantity );

			return apply_filters( 'hb_wc_update_cart_return', $return, $cart_item_key, $values, $quantity );
		}

		/**
		 * @param $cart_item_id
		 * @param $cart WC_Cart
		 *
		 * @return bool;
		 */
		public function woocommerce_restore_cart_item( $cart_item_id, $cart ) {
			if ( ! $cart_item = $cart->get_cart_item( $cart_item_id ) ) {
				return false;
			}

			if ( ! isset( $cart_item['check_in_date'] ) || ! isset( $cart_item['check_out_date'] ) ) {
				return false;
			}

			do_action( 'hb_wc_restore_cart_item', $cart_item_id, $cart );

			// param render hotel cart id
			$hotel_cart_param = array(
				'product_id'     => $cart_item['product_id'],
				'check_in_date'  => $cart_item['check_in_date'],
				'check_out_date' => $cart_item['check_out_date']
			);

			if ( isset( $cart_item['parent_id'] ) ) {
				$hotel_cart_param['parent_id'] = $cart_item['parent_id'];
			}

			$hotel_cart = WP_Hotel_Booking::instance()->cart;
			/**
			 * @var $hotel_cart WPHB_Cart
			 */
			$hotel_cart->add_to_cart( $cart_item['product_id'], $hotel_cart_param, $cart_item['quantity'] );

			do_action( 'hb_wc_restored_cart_item', $cart_item_id, $cart );

			return true;
		}

		/**
		 * @param $session_data
		 * @param $values
		 * @param $key
		 *
		 * @return mixed
		 */
		public function get_cart_item_from_session( $session_data, $values, $key ) {
			$session_data['data']->set_props( $values );

			return $session_data;
		}

		/**
		 * @param $class
		 * @param $cart_item
		 * @param $cart_item_key
		 *
		 * @return string
		 */
		public function woocommerce_cart_package_item_class( $class, $cart_item, $cart_item_key ) {

			$class = array( $class );

			if ( ! isset( $cart_item['check_in_date'] ) || ! isset( $cart_item['check_in_date'] ) ) {
				return implode( ' ', $class );
			}

			if ( ! isset( $cart_item['parent_id'] ) ) {
				$class[] = 'hb_wc_cart_room_item';
			} else {

				$class[] = 'hb_wc_cart_package_item';

				$extra_id = $cart_item['product_id'];
				if ( get_post_meta( $extra_id, 'tp_hb_extra_room_required' ) ) {
					$class[] = 'required-extra';
				}
			}

			return implode( ' ', $class );
		}

		public function remove_cart_extra_required( $string, $cart_item_key ) {
			preg_match( '/data-product_id=\"(.*?)\"/', $string, $matches );

			if ( ! empty( $matches ) ) {
				if ( get_post_meta( absint( $matches[1] ), 'tp_hb_extra_room_required', true ) ) {
					$string = null;
				}
			}

			return $string;
		}

		/**
		 * Woo change status
		 *
		 * @param $order_id
		 * @param $old_status
		 * @param $new_status
		 * @param $order
		 */
		public function woocommerce_order_status_changed( $order_id, $old_status, $new_status, $order ) {
			remove_action( 'hb_booking_status_changed', 'hb_customer_email_order_changes_status', 10 );
			if ( $booking_id = hb_get_post_id_meta( '_hb_woo_order_id', $order_id ) ) {
				$booking = WPHB_Booking::instance( $booking_id );
				if ( in_array( $new_status, array( 'completed', 'pending', 'processing', 'cancelled' ) ) ) {
					$booking->update_status( $new_status );
				} else {
					$booking->update_status( 'pending' );
				}
			}
		}

		/**
		 * @param $url
		 *
		 * @return string
		 */
		public function hotel_cart_url( $url ) {
			global $woocommerce;
			if ( ! $woocommerce->cart ) {
				return $url;
			}

			$url = wc_get_cart_url() ? wc_get_cart_url() : $url;

			return $url;
		}

		/**
		 * @param $url
		 *
		 * @return string
		 */
		public function hotel_checkout_url( $url ) {
			global $woocommerce;
			if ( ! $woocommerce->cart ) {
				return $url;
			}

			$url = wc_get_checkout_url() ? wc_get_checkout_url() : $url;

			return $url;
		}

		// woo product class process
		function product_class( $classname, $product_type, $post_type, $product_id ) {
			if ( 'hb_room' == get_post_type( $product_id ) ) {
				$classname = 'HB_WC_Product_Room';
			} else if ( 'hb_extra_room' == get_post_type( $product_id ) ) {
				$classname = 'HB_WC_Product_Package';
			}

			return $classname;
		}

		/**
		 * @return bool|mixed
		 */
		private function _parse_request() {
			$segments = parse_url( hb_get_request( '_wp_http_referer' ) );
			$request  = false;
			if ( ! empty( $segments['query'] ) ) {
				parse_str( $segments['query'], $params );
				if ( ! empty( $params['hotel-booking-params'] ) ) {
					$param_str = base64_decode( $params['hotel-booking-params'] );
					$request   = unserialize( $param_str );
				}
			}

			return $request;
		}

		/**
		 * Add product class param.
		 *
		 * @param $cart_item
		 * @param $cart_id
		 *
		 * @return mixed
		 */
		function add_cart_item( $cart_item, $cart_id ) {
			$post_type = get_post_type( $cart_item['data']->get_id() );
			if ( in_array( $post_type, array( 'hb_room', 'hb_extra_room' ) ) ) {
				$cart_item['data']->set_props(
					array(
						'product_id'     => $cart_item['product_id'],
						'check_in_date'  => $cart_item['check_in_date'],
						'check_out_date' => $cart_item['check_out_date'],
						'woo_cart_id'    => $cart_id
					)
				);
				if ( $post_type === 'hb_extra_room' ) {
					$cart_item['data']->set_parent_id( $cart_item['parent_id'] );
				}
			}

			return $cart_item;
		}

		/**
		 * @return null|WP_Hotel_Booking_Woocommerce
		 */
		public static function instance() {
			if ( ! self::$_instance ) {
				self::$_instance = new self();
			}

			return self::$_instance;
		}

		/**
		 * Load function.
		 */
		public static function load() {

			if ( ! function_exists( 'is_plugin_active' ) ) {
				include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			}

			if ( ( class_exists( 'TP_Hotel_Booking' ) && is_plugin_active( 'tp-hotel-booking/tp-hotel-booking.php' ) ) || ( is_plugin_active( 'wp-hotel-booking/wp-hotel-booking.php' ) && class_exists( 'WP_Hotel_Booking' ) ) ) {
				self::$_wc_loaded = true;
			}

			if ( ! self::check_woo_activated() ) {
				return;
			}

			// if ( self::$_wc_loaded === true && class_exists( 'WC_Install' ) && is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			// 	self::$_wc_loaded = true;
			// } else {
			// 	self::$_wc_loaded = false;
			// }

			WP_Hotel_Booking_Woocommerce::instance();
			if ( ! self::$_wc_loaded ) {
				add_action( 'admin_notices', array( __CLASS__, 'admin_notice' ) );
			}

			self::load_text_domain();
		}

		/**
		 * Load text domain.
		 */
		public static function load_text_domain() {
			$default     = WP_LANG_DIR . '/plugins/wp-hotel-booking-woocommerce-' . get_locale() . '.mo';
			$plugin_file = HB_WC_PLUGIN_PATH . '/languages/wp-hotel-booking-woocommerce-' . get_locale() . '.mo';
			if ( file_exists( $default ) ) {
				$file = $default;
			} else {
				$file = $plugin_file;
			}
			if ( $file ) {
				load_textdomain( 'wp-hotel-booking-woocommerce', $file );
			}
		}

		/*
		 * Admin notice.
		 */
		public static function admin_notice() {
			if ( ! class_exists( 'WPHB_Settings' ) ) {
				return;
			}

			if ( function_exists( 'hb_wc_admin_view' ) ) {
				hb_wc_admin_view( 'wc-is-not-installed' );
			}
		}

		/**
		 * @return bool
		 */
		public static function wc_enable() {
			if ( ! class_exists( 'WPHB_Settings' ) ) {
				return false;
			}

			return self::$_wc_loaded && WPHB_Settings::instance()->get( 'wc_enable' ) == 'yes';
		}

		/**
		 * Frontend scripts.
		 */
		public function frontend_scripts() {
			wp_enqueue_script( 'hb_wc_checkout', HB_WC_PLUGIN_URL . 'assets/js/frontend/site.min.js', array( 'jquery' ) );
			wp_enqueue_style( 'hb_wc_site', HB_WC_PLUGIN_URL . 'assets/css/frontend/site.css' );
		}

		/**
		 * Define constants
		 */
		private function _defines() {
			define( 'WPHB_WOO_PAYMENT_ABSPATH', plugin_dir_path( __FILE__ ) );
			define( 'HB_WC_PLUGIN_PATH', trailingslashit( plugin_dir_path( __FILE__ ) ) );
			define( 'HB_WC_PLUGIN_URL', trailingslashit( plugins_url( '/', __FILE__ ) ) );
		}

		/**
		 * Including library files
		 */
		private function _includes() {
			if ( ! class_exists( 'WPHB_Settings' ) ) {
				return;
			}

			require_once "includes/class-hb-wc-product-room.php";
			require_once "includes/class-hb-wc-product-package.php";
			require_once "includes/class-hb-wc-checkout.php";
			require_once "includes/class-hb-wc-booking.php";
			$this->settings = WPHB_Settings::instance();
		}

		/**
		 * return currency of woocommerce setting
		 *
		 * @param $currency
		 *
		 * @return string
		 */
		public function woocommerce_currency( $currency ) {
			return get_woocommerce_currency();
		}

		/**
		 * Return currency symbol of woocommerce setting.
		 *
		 * @param $symbol
		 * @param $currency
		 *
		 * @return string
		 */
		public function woocommerce_currency_symbol( $symbol, $currency ) {
			return get_woocommerce_currency_symbol( $currency );
		}

		/**
		 * Get price within currency format using woocommerce setting.
		 *
		 * @param $price_format
		 * @param $price
		 * @param $with_currency
		 *
		 * @return string
		 */
		public function woocommerce_price_format( $price_format, $price, $with_currency ) {
			//$price = $price + ( hb_price_including_tax() ? ( $price * hb_get_tax_settings() ) : 0 );
			return wc_price( $price );
		}

		public function room_price_regular_excl( $total, $room ) {

			if ( wc_prices_include_tax() ) {
				$total = wc_get_price_excluding_tax( $room, array(
					'qty'   => $room->get_data( 'quantity' ),
					'price' => $room->amount_singular_exclude_tax
				) );
			}

			return $total;
		}

		public function room_price_regular_incl( $total, $room ) {
			if ( wc_prices_include_tax() ) {
				$total = wc_get_price_including_tax( $room, array(
					'qty'   => $room->get_data( 'quantity' ),
					'price' => $room->amount_singular_exclude_tax
				) );
			}

			return $total;
		}

		/**
		 * Room tax.
		 *
		 * @param $tax_price
		 * @param $room
		 *
		 * @return float|string
		 */
		public function room_price_tax( $tax_price, $room ) {
			remove_filter( 'hotel_booking_room_total_price_incl_tax', array( $this, 'room_price_tax' ), 10 );
			// woo get price
			$product = ( $room );

			add_filter( 'hotel_booking_room_total_price_incl_tax', array( $this, 'room_price_tax' ), 10, 2 );

			if ( ! function_exists( 'wc_get_price_including_tax' ) ) {
				// woo get price
				$product        = new WC_Product( $room->post->ID );
				$price_incl_tax = $product->get_price_including_tax( $room->get_data( 'quantity' ), $room->amount_singular_exclude_tax );
				$price_excl_tax = $product->get_price_excluding_tax( $room->get_data( 'quantity' ), $room->amount_singular_exclude_tax );
			} else {
				$price_incl_tax = wc_get_price_including_tax( $room, array(
					'qty'   => $room->get_data( 'quantity' ),
					'price' => $room->amount_singular_exclude_tax
				) );
				$price_excl_tax = wc_get_price_excluding_tax( $room, array(
					'qty'   => $room->get_data( 'quantity' ),
					'price' => $room->amount_singular_exclude_tax
				) );
			}

			return $price_incl_tax - $price_excl_tax;
		}

		public function packages_regular_price_incl( $total, $package ) {
			$product    = $package;
			$qty        = $package->quantity;
			$line_price = $package->amount_singular_exclude_tax() * $qty;

			if ( wc_prices_include_tax() ) {
				$total          = $line_price;
				$tax_rates      = WC_Tax::get_rates( $product->get_tax_class() );
				$base_tax_rates = WC_Tax::get_base_tax_rates( $product->get_tax_class( 'unfiltered' ) );

				if ( ! empty( WC()->customer ) && WC()->customer->get_is_vat_exempt() ) {
					$remove_taxes = apply_filters( 'woocommerce_adjust_non_base_location_prices', true ) ? WC_Tax::calc_tax( $line_price, $base_tax_rates, true ) : WC_Tax::calc_tax( $line_price, $tax_rates, true );

					if ( 'yes' === get_option( 'woocommerce_tax_round_at_subtotal' ) ) {
						$remove_taxes_total = array_sum( $remove_taxes );
					} else {
						$remove_taxes_total = array_sum( array_map( 'wc_round_tax_total', $remove_taxes ) );
					}

					$total = round( $line_price - $remove_taxes_total, wc_get_price_decimals() );

				} elseif ( $tax_rates !== $base_tax_rates && apply_filters( 'woocommerce_adjust_non_base_location_prices', true ) ) {
					$base_taxes   = WC_Tax::calc_tax( $line_price, $base_tax_rates, true );
					$modded_taxes = WC_Tax::calc_tax( $line_price - array_sum( $base_taxes ), $tax_rates, false );

					if ( 'yes' === get_option( 'woocommerce_tax_round_at_subtotal' ) ) {
						$base_taxes_total   = array_sum( $base_taxes );
						$modded_taxes_total = array_sum( $modded_taxes );
					} else {
						$base_taxes_total   = array_sum( array_map( 'wc_round_tax_total', $base_taxes ) );
						$modded_taxes_total = array_sum( array_map( 'wc_round_tax_total', $modded_taxes ) );
					}

					$total = round( $line_price - $base_taxes_total + $modded_taxes_total, wc_get_price_decimals() );
				}
			}

			return $total;
		}

		public function packages_singular_price( $price, $package ) {

			if ( wc_prices_include_tax() ) {
				$price = $package->amount_singular_exclude_tax();
			} else {
				$price = $package->amount_singular_include_tax();
			}

			return $price;

		}

		public function packages_regular_price_excl( $total, $package ) {
			$product    = $package;
			$qty        = $package->quantity;
			$line_price = $package->amount_singular_exclude_tax() * $qty;

			if ( wc_prices_include_tax() ) {
				$tax_rates      = WC_Tax::get_rates( $product->get_tax_class() );
				$base_tax_rates = WC_Tax::get_base_tax_rates( $product->get_tax_class( 'unfiltered' ) );
				$remove_taxes   = apply_filters( 'woocommerce_adjust_non_base_location_prices', true ) ? WC_Tax::calc_tax( $line_price, $base_tax_rates, true ) : WC_Tax::calc_tax( $line_price, $tax_rates, true );
				$total          = $line_price - array_sum( $remove_taxes );
			}

			return $total;
		}

		/**
		 * Package tax price.
		 *
		 * @param $tax_price
		 * @param $price
		 * @param $package WPHB_Product_Room_Base
		 *
		 * @return float|string
		 */
		public function packages_regular_price_tax( $tax_price, $price, $package ) {

			if ( ! class_exists( 'WC_Tax' ) ) {
				$product = wc_get_product( $package->ID );
				$price   = $package->amount_singular_exclude_tax();

				$price_incl_tax = $product->get_price_including_tax( 1, $price );
				$price_excl_tax = $product->get_price_excluding_tax( 1, $price );

				$tax_price = $price_incl_tax - $price_excl_tax;
			} else {
				$product      = $package;
				$line_price   = $package->amount_singular_exclude_tax();
				$return_price = $line_price;
				$price_excl   = $line_price;

				if ( ! wc_prices_include_tax() ) {
					$tax_rates = WC_Tax::get_rates( $product->get_tax_class() );
					$taxes     = WC_Tax::calc_tax( $line_price, $tax_rates, false );

					if ( 'yes' === get_option( 'woocommerce_tax_round_at_subtotal' ) ) {
						$taxes_total = array_sum( $taxes );
					} else {
						$taxes_total = array_sum( array_map( 'wc_round_tax_total', $taxes ) );
					}

					$return_price = round( $line_price + $taxes_total, wc_get_price_decimals() );
				} else {

					$tax_rates      = WC_Tax::get_rates( $product->get_tax_class() );
					$base_tax_rates = WC_Tax::get_base_tax_rates( $product->get_tax_class( 'unfiltered' ) );

					if ( ! empty( WC()->customer ) && WC()->customer->get_is_vat_exempt() ) {
						$remove_taxes = apply_filters( 'woocommerce_adjust_non_base_location_prices', true ) ? WC_Tax::calc_tax( $line_price, $base_tax_rates, true ) : WC_Tax::calc_tax( $line_price, $tax_rates, true );

						if ( 'yes' === get_option( 'woocommerce_tax_round_at_subtotal' ) ) {
							$remove_taxes_total = array_sum( $remove_taxes );
						} else {
							$remove_taxes_total = array_sum( array_map( 'wc_round_tax_total', $remove_taxes ) );
						}

						$return_price = round( $line_price - $remove_taxes_total, wc_get_price_decimals() );

					} elseif ( $tax_rates !== $base_tax_rates && apply_filters( 'woocommerce_adjust_non_base_location_prices', true ) ) {
						$base_taxes   = WC_Tax::calc_tax( $line_price, $base_tax_rates, true );
						$modded_taxes = WC_Tax::calc_tax( $line_price - array_sum( $base_taxes ), $tax_rates, false );

						if ( 'yes' === get_option( 'woocommerce_tax_round_at_subtotal' ) ) {
							$base_taxes_total   = array_sum( $base_taxes );
							$modded_taxes_total = array_sum( $modded_taxes );
						} else {
							$base_taxes_total   = array_sum( array_map( 'wc_round_tax_total', $base_taxes ) );
							$modded_taxes_total = array_sum( array_map( 'wc_round_tax_total', $modded_taxes ) );
						}

						$return_price = round( $line_price - $base_taxes_total + $modded_taxes_total, wc_get_price_decimals() );
					}

					$remove_excl_taxes = apply_filters( 'woocommerce_adjust_non_base_location_prices', true ) ? WC_Tax::calc_tax( $line_price, $base_tax_rates, true ) : WC_Tax::calc_tax( $line_price, $tax_rates, true );
					$price_excl        = $line_price - array_sum( $remove_excl_taxes );
				}
			}

			return $return_price - $price_excl;
		}

		/**
		 * @param $tax
		 * @param $cart
		 *
		 * @return bool
		 */
		public function hb_price_including_tax( $tax, $cart ) {
			if ( ! $cart ) {
				return $tax;
			}
			if ( wc_tax_enabled() && get_option( 'woocommerce_tax_display_cart' ) === 'incl' ) {
				$tax = true;
			}

			return $tax;
		}

		/**
		 * Cart item total amount.
		 *
		 * @param $amount
		 * @param $cart_id
		 * @param $cart_item
		 * @param $product
		 *
		 * @return mixed
		 */
		public function hotel_booking_cart_item_total_amount( $amount, $cart_id, $cart_item, $product ) {
			return $amount;
		}

		/**
		 * @param $amount
		 * @param $cart_id
		 * @param $cart_item
		 * @param $product WPHB_Room
		 *
		 * @return string
		 */
		public function hotel_booking_cart_item_amount_singular( $amount, $cart_id, $cart_item, $product ) {
			if ( wc_tax_enabled() && get_option( 'woocommerce_tax_display_cart' ) === 'incl' ) {
				// woo get price
				if ( get_post_type( $cart_item->product_id ) === 'hb_room' ) {
					$woo_product = wc_get_product( $cart_item->product_id );
					$price       = $product->get_total( $cart_item->check_in_date, $product->check_out_date, $cart_item->quantity, false, false );

					$amount = wc_get_price_including_tax( $woo_product, [ $price, $product->quantity ] );
				}
			}

			return $amount;
		}

		/**
		 * Return tax price total.
		 *
		 * @param $display
		 *
		 * @return string
		 */
		public function cart_tax_display( $display ) {
			global $woocommerce;

			return wc_price( $woocommerce->cart->get_taxes_total() );
		}

		/**
		 * Cart result total
		 *
		 * @param $display
		 *
		 * @return string
		 */
		public function cart_total_result_display( $display ) {
			global $woocommerce;

			return wc_price( $woocommerce->cart->total );
		}

		/**
		 * Redirect hotel cart, checkout to woo page
		 */
		public function template_redirect() {
			global $post;
			if ( ! $post ) {
				return;
			}
			if ( $post->ID == hb_get_page_id( 'cart' ) ) {
				wp_redirect( wc_get_cart_url() );
				exit();
			} else if ( $post->ID == hb_get_page_id( 'checkout' ) ) {
				wp_redirect( wc_get_checkout_url() );
				exit();
			}
		}

		/**
		 * sort room as product with extra packages
		 */
		public function woo_sort_rooms() {
			global $woocommerce;

			$woo_cart_contents = array();

			// cart contents items
			$cart_items = $woocommerce->cart->cart_contents;

			foreach ( $cart_items as $cart_id => $item ) {

				if ( ! isset( $item['check_in_date'] ) || ! isset( $item['check_out_date'] ) ) {
					$woo_cart_contents[ $cart_id ] = $item;
					continue;
				}

				if ( ! isset( $item['parent_id'] ) ) {
					$woo_cart_contents[ $cart_id ] = $item;

					$param = array(
						'product_id'     => $item['product_id'],
						'check_in_date'  => $item['check_in_date'],
						'check_out_date' => $item['check_out_date'],
					);
					if ( isset( $item['hb_optional_quantity'] ) && isset( $item['hb_optional_quantity_selected'] ) ) {
						$param['hb_optional_quantity']          = $item['hb_optional_quantity'];
						$param['hb_optional_quantity_selected'] = $item['hb_optional_quantity_selected'];

					}

					$parent_id = WP_Hotel_Booking::instance()->cart->generate_cart_id( $param );

					foreach ( $cart_items as $cart_package_id => $package ) {
						if ( ! isset( $package['parent_id'] ) || ! isset( $package['check_in_date'] ) || ! isset( $package['check_out_date'] ) ) {
							continue;
						}

						if ( $package['parent_id'] === $parent_id ) {
							$woo_cart_contents[ $cart_package_id ] = $package;
						}
					}
				}
			}

			$woocommerce->cart->cart_contents = $woo_cart_contents;
		}

	}
}

add_action( 'plugins_loaded', array( 'WP_Hotel_Booking_Woocommerce', 'load' ) );
