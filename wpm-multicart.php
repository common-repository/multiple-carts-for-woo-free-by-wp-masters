<?php
/*
 * Plugin Name: Multiple Carts, Persistent Carts, Abandoned Carts, MultiVendors for Woo - Free by WP Masters
 * Plugin URI: https://wp-masters.com/products/multiple-carts-for-woocommerce
 * Description: Multiple Carts for WooCommerce allows customers to use multiple carts, addresses, vendors and save carts for a long time. Plugin also has an abandoned cart feature inside
 * Author: WP Masters
 * Description: This plugin adds One-Click Clone feature for each Woo Order at Orders List
 * Text Domain: wpm-woo-clone-order
 * Author URI: https://wp-masters.com
 * Version: 1.0.2
 *
 * @author      WP Masters
 * @version     v.1.0.2 (24/07/23)
 * @copyright   Copyright (c) 2022
*/

define( 'WPM_MULTICART_ASSETS', 'wpm_multicart_woocommerce' );

if ( ! session_id() ) {
	session_start();
}

require_once( 'helpers/array_helper.php' );

class WPM_MultiCart_WooCommerce {

	private $array_helper;

	/**
	 * Initialize functions
	 */
	public function __construct() {
		// Create DB Table
		register_activation_hook( __FILE__, [ $this, 'create_plugin_tables' ] );
		add_action('init', [$this, 'add_new_column_for_tables']);

		// Include Styles and Scripts
		add_action( 'wp_enqueue_scripts', [ $this, 'include_scripts_and_styles' ], 99 );
		add_action( 'admin_enqueue_scripts', [ $this, 'admin_scripts_and_styles' ] );

		// WooCommerce functions
		add_action( 'woocommerce_before_cart', [ $this, 'show_select_cart_session' ], 99 );
		add_action( 'woocommerce_cart_is_empty', [ $this, 'show_select_cart_session' ], 99 );
		add_filter( 'woocommerce_add_to_cart_validation', [ $this, 'validate_add_cart_item' ], 1, 5 );
		add_action( 'woocommerce_thankyou', [ $this, 'change_cart_status_items' ], 10, 1 );

		// Ajax functions
		add_action( 'wp_ajax_get_variations_product', [ $this, 'get_variations_product' ] );

		// Action with Sessions
		add_action( 'wp_loaded', [ $this, 'clear_all_carts' ] );
		add_action( 'wp_loaded', [ $this, 'cart_session_delete' ] );
		add_action( 'wp_loaded', [ $this, 'select_cart_session' ] );
		add_action( 'wp_loaded', [ $this, 'save_checkout_fields' ] );

		// Init functions
		add_action( 'add_meta_boxes', [ $this, 'create_meta_box' ] );
		add_action( 'init', [ $this, 'create_carts_post_type' ], 0 );
		add_action( 'init', [ $this, 'create_suppliers_post_type' ], 0 );
		add_action( 'init', [ $this, 'create_order_from_cart' ] );

		// Save Post Data
		add_action('save_post', [ $this, 'save_cart_meta_data' ], 1, 2);
		add_action('save_post', [$this, 'save_cart_items_data'], 99, 3);

		// Initialize List Columns
		add_action( 'load-edit.php', [ $this, 'add_custom_columns_to_list' ] );

		// Set CRON
		add_action( 'wpm_carts_reminder', [ $this, 'check_not_finished_carts' ] );
		add_filter( 'cron_schedules', [ $this, 'seo_cron_schedule' ] );

		$this->array_helper = WPM_MulticartSanitizer::get_instance();

		if(isset($_GET['test'])) {
			add_action('init', [$this, 'check_not_finished_carts']);
		}
	}

	/**
	 * Update DB Cart Items from Post Cart
	 */
	public static function save_cart_items_data($post_id, $post, $update)
	{
		if(get_post_type($post_id) != 'carts') {
			return;
		}

		global $wpdb;

		// Search already created Cart
		$cart_data = $wpdb->get_row( $wpdb->prepare("SELECT * FROM {$wpdb->prefix}vendors_carts WHERE post_id = %d", $post_id) );
		$cart_meta = get_post_meta($post_id, 'cart_settings', true);

		// Delete Cart from DB
		if(get_post_status($post_id) == 'trash') {
			$wpdb->delete("{$wpdb->prefix}vendors_carts", [ 'post_id' => $post_id ] );
			return;
		}

		// Prepare Address
		$address = [
			'billing' => isset($cart_meta['billing']) ? $cart_meta['billing'] : [],
			'shipping' => isset($cart_meta['shipping']) ? $cart_meta['shipping'] : [],
		];

		// Set Products
		$items_content = [];
		$product_vendor = 0;
		if ( isset( $cart_meta['product'] ) && count( $cart_meta['product'] ) > 0 ) {
			foreach ( $cart_meta['product'] as $item => $product_id ) {
				$id_product = $cart_meta['variation'][ $item ] > 0 ? $cart_meta['variation'][ $item ] : $product_id;
				$product = wc_get_product($id_product);
				$items_content[] = [
					'variation_id' => $cart_meta['variation'][ $item ],
					'product_id'   => $product_id,
					'name'         => $product->get_name(),
					'quantity'     => $cart_meta['quantity'][ $item ],
					'total'        => $product->get_price() * $cart_meta['quantity'][ $item ]
				];

				if(get_post_meta($product_id, 'product_supplier' ) && get_post_meta( $product_id, 'product_supplier', true ) > 0) {
					$product_vendor = get_post_meta( $product_id, 'product_supplier', true );
				}
			}
		}

		if(empty($items_content)) {
		    return;
        }

		// Check if it's founded
		if($cart_data) {
			$wpdb->update( "{$wpdb->prefix}vendors_carts", array(
				'user_id'    => $post->post_author,
				'cart_items' => serialize( $items_content ),
				'address'    => serialize( $address ),
				'order_status' => isset($cart_meta['status']) ? $cart_meta['status'] : 'created',
			), ['post_id' => $post_id] );
		} else {
			$wpdb->insert( "{$wpdb->prefix}vendors_carts", array(
				'post_id'      => $post_id,
				'vendor'       => $product_vendor,
				'user_id'      => $post->post_author,
				'cart_items'   => serialize( $items_content ),
				'address'      => serialize( $address ),
				'order_status' => isset($cart_meta['status']) ? $cart_meta['status'] : 'created',
				'last_remind'  => date( 'Y-m-d' )
			) );
		}
	}

	/**
	 * Search not finished Carts and Remind
	 */
	public function check_not_finished_carts() {
		global $wpdb;

		// Get Carts
		$search_date = date( 'Y-m-d', strtotime( '-3 days' ) );
		$carts_list  = $wpdb->get_results( $wpdb->prepare("SELECT * FROM {$wpdb->prefix}vendors_carts WHERE order_status='created' AND last_remind <= '{$search_date}'") );

		// Prepare to Send Email
		$subject = 'Order is not finished! Check your items in the Cart';
		$headers = array( 'Content-Type: text/html; charset=UTF-8' );

		// Check Cart
		$already_mailed = [];
		foreach ( $carts_list as $cart ) {

			// Send only one Mail Reminder per User
			if ( ! in_array( $cart->user_id, $already_mailed ) && $cart->user_id != 0 ) {
				// Get User
				$user = get_user_by( 'id', $cart->user_id );

				// Include HTML Email
				ob_start();
				include( 'templates/mail/remind_carts.php' );
				$body = ob_get_clean();

				// Send Mail
				if ( wp_mail( $user->user_email, $subject, $body, $headers ) ) {
					$already_mailed[] = $cart->user_id;
				}
			}

			$wpdb->update( "{$wpdb->prefix}vendors_carts", [ 'last_remind' => date( 'Y-m-d' ) ], [ 'id' => $cart->id ] );
		}
	}

	/**
	 * Add new Cron Schedules
	 */
	public function seo_cron_schedule( $schedules ) {
		$schedules['every_1_hour'] = array(
			'interval' => 3600,
			'display'  => __( 'Every 1 hour' ),
		);

		return $schedules;
	}

	/**
	 * Add MetaBox to editor
	 */
	public function add_custom_columns_to_list() {
		$screen = get_current_screen();

		if ( ! isset( $screen->post_type ) || 'carts' != $screen->post_type ) {
			return;
		}

		add_filter( "manage_{$screen->id}_columns", [ $this, 'add_columns_multicart_list' ] );
		add_action( "manage_{$screen->post_type}_posts_custom_column", [ $this, 'content_custom_multicart_columns' ], 10, 2 );
	}

	/**
	 * Add Columns to List
	 */
	public function add_columns_multicart_list( $cols ) {
		$new_cols = [];

		// Add Columns
		foreach ( $cols as $name => $col ) {
			$new_cols[ $name ] = $col;
			if ( $name == 'title' ) {
				$new_cols['status'] = 'Status';
			}
		}

		return $new_cols;
	}

	/**
	 * Add Columns Data Content
	 */
	public function content_custom_multicart_columns( $col, $post_id ) {
		if ( $col == 'status' ) {
			$cart_settings = get_post_meta( $post_id, 'cart_settings', true );
			if ( isset( $cart_settings['status'] ) && $cart_settings['status'] == 'created' ) { ?>
				<span class='cart_status created'><?php echo esc_html($cart_settings['status']); ?></span>
			<?php
			} elseif ( isset( $cart_settings['status'] ) ) { ?>
				<span class='cart_status finished'><?php echo esc_html($cart_settings['status']); ?></span>
			<?php
			}
		}
	}

	/**
	 * Save Cart Data
	 */
	public function save_cart_meta_data( $post_id ) {
		if ( isset( $_POST['wpm_multicart'] ) ) {
			$data = $this->array_helper->sanitize_array( $_POST['wpm_multicart'] );
			update_post_meta( $post_id, 'cart_settings', $data );
		}
		if ( isset( $_POST['product_supplier'] ) ) {
			update_post_meta( $post_id, 'product_supplier', sanitize_text_field( $_POST['product_supplier'] ) );
		}
	}

	/**
	 * Get variations for selected product in list for select
	 */
	public function get_variations_product() {
		if ( ! wp_verify_nonce( $_POST['ajax_nonce'], 'ajax_nonce' ) && ! isset( $_POST['product_id'] ) ) {
			return false;
		}

		// Prepare Data
		$product_id = sanitize_text_field( $_POST['product_id'] );
		$product    = wc_get_product( $product_id );
		$variations = [];

		// Include Template
		ob_start();
		include( 'templates/ajax/variations_options.php' );
		$html = ob_get_clean();

		wp_send_json( [
			'status' => 'true',
			'html'   => $html
		] );
	}

	/**
	 *  Create new Posts type - Carts
	 */
	public function create_carts_post_type() {
		// Set UI labels for Custom Post Type
		$labels = array(
			'name'               => _x( 'Carts', 'Post Type General Name', 'wpm_multicart' ),
			'singular_name'      => _x( 'Cart', 'Post Type Singular Name', 'wpm_multicart' ),
			'menu_name'          => __( 'Carts', 'wpm_multicart' ),
			'parent_item_colon'  => __( 'Parent Cart', 'wpm_multicart' ),
			'all_items'          => __( 'All Carts', 'wpm_multicart' ),
			'view_item'          => __( 'View Cart', 'wpm_multicart' ),
			'add_new_item'       => __( 'Add New Cart', 'wpm_multicart' ),
			'add_new'            => __( 'Add New', 'wpm_multicart' ),
			'edit_item'          => __( 'Edit Cart', 'wpm_multicart' ),
			'update_item'        => __( 'Update Cart', 'wpm_multicart' ),
			'search_items'       => __( 'Search Cart', 'wpm_multicart' ),
			'not_found'          => __( 'Not Found', 'wpm_multicart' ),
			'not_found_in_trash' => __( 'Not found in Trash', 'wpm_multicart' ),
		);

		// Set other options for Custom Post Type
		$args = array(
			'label'               => __( 'carts', 'wpm_multicart' ),
			'description'         => __( 'Customers not finished carts', 'wpm_multicart' ),
			'labels'              => $labels,
			'supports'            => array( 'title', 'author' ),
			'hierarchical'        => false,
			'public'              => false,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_nav_menus'   => true,
			'show_in_admin_bar'   => true,
			'menu_position'       => 5,
			'can_export'          => false,
			'has_archive'         => false,
			'exclude_from_search' => false,
			'publicly_queryable'  => false,
			'capability_type'     => 'post',
			'show_in_rest'        => false,
		);

		// Registering your Custom Post Type
		register_post_type( 'carts', $args );
	}

	/**
	 *  Create new Posts type - Carts
	 */
	public function create_suppliers_post_type() {
		// Set UI labels for Custom Post Type
		$labels = array(
			'name'               => _x( 'Suppliers', 'Post Type General Name', 'wpm_multicart' ),
			'singular_name'      => _x( 'Supplier', 'Post Type Singular Name', 'wpm_multicart' ),
			'menu_name'          => __( 'Suppliers', 'wpm_multicart' ),
			'parent_item_colon'  => __( 'Parent Supplier', 'wpm_multicart' ),
			'all_items'          => __( 'All Suppliers', 'wpm_multicart' ),
			'view_item'          => __( 'View Supplier', 'wpm_multicart' ),
			'add_new_item'       => __( 'Add New Supplier', 'wpm_multicart' ),
			'add_new'            => __( 'Add New', 'wpm_multicart' ),
			'edit_item'          => __( 'Edit Supplier', 'wpm_multicart' ),
			'update_item'        => __( 'Update Supplier', 'wpm_multicart' ),
			'search_items'       => __( 'Search Supplier', 'wpm_multicart' ),
			'not_found'          => __( 'Not Found', 'wpm_multicart' ),
			'not_found_in_trash' => __( 'Not found in Trash', 'wpm_multicart' ),
		);

		// Set other options for Custom Post Type
		$args = array(
			'label'               => __( 'suppliers', 'wpm_multicart' ),
			'description'         => __( 'Suppliers for Products', 'wpm_multicart' ),
			'labels'              => $labels,
			'supports'            => array( 'title', 'author' ),
			'hierarchical'        => false,
			'public'              => false,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_nav_menus'   => true,
			'show_in_admin_bar'   => true,
			'menu_position'       => 5,
			'can_export'          => false,
			'has_archive'         => false,
			'exclude_from_search' => false,
			'publicly_queryable'  => false,
			'capability_type'     => 'post',
			'show_in_rest'        => false,
		);

		// Registering your Custom Post Type
		register_post_type( 'suppliers', $args );
	}

	/**
	 *  Create Order from Cart
	 */
	public function create_order_from_cart() {
		if ( isset( $_GET['create_order_cart'] ) && isset( $_GET['post'] ) ) {
			// Get Author information
			$post_id       = sanitize_text_field( $_GET['post'] );
			$author_id     = get_post_field( 'post_author', $post_id );
			$cart_settings = get_post_meta( $post_id, 'cart_settings', true );

			// Create Order
			$order = wc_create_order( [
				'customer_id' => $author_id
			] );

			// Set Products
			if ( isset( $cart_settings['product'] ) && count( $cart_settings['product'] ) > 0 ) {
				foreach ( $cart_settings['product'] as $item => $product_id ) {
					$id_product = $cart_settings['variation'][ $item ] > 0 ? $cart_settings['variation'][ $item ] : $product_id;
					$order->add_product( get_product( $id_product ), $cart_settings['quantity'][ $item ] );
				}
			}

			// Set Billing Fields
			update_post_meta( $order->get_id(), '_customer_user', $author_id );
			update_post_meta( $order->get_id(), '_billing_city', $cart_settings['billing']['city'] );
			update_post_meta( $order->get_id(), '_billing_state', $cart_settings['billing']['state'] );
			update_post_meta( $order->get_id(), '_billing_postcode', $cart_settings['billing']['postcode'] );
			update_post_meta( $order->get_id(), '_billing_email', $cart_settings['billing']['email'] );
			update_post_meta( $order->get_id(), '_billing_phone', $cart_settings['billing']['phone'] );
			update_post_meta( $order->get_id(), '_billing_address_1', $cart_settings['billing']['address_1'] );
			update_post_meta( $order->get_id(), '_billing_address_2', $cart_settings['billing']['address_2'] );
			update_post_meta( $order->get_id(), '_billing_country', $cart_settings['billing']['country'] );
			update_post_meta( $order->get_id(), '_billing_first_name', $cart_settings['billing']['first_name'] );
			update_post_meta( $order->get_id(), '_billing_last_name', $cart_settings['billing']['last_name'] );
			update_post_meta( $order->get_id(), '_billing_company', $cart_settings['billing']['company'] );

			// Set Shipping Fields
			update_post_meta( $order->get_id(), '_shipping_country', $cart_settings['shipping']['country'] );
			update_post_meta( $order->get_id(), '_shipping_first_name', $cart_settings['shipping']['first_name'] );
			update_post_meta( $order->get_id(), '_shipping_last_name', $cart_settings['shipping']['last_name'] );
			update_post_meta( $order->get_id(), '_shipping_company', $cart_settings['shipping']['company'] );
			update_post_meta( $order->get_id(), '_shipping_address_1', $cart_settings['shipping']['address_1'] );
			update_post_meta( $order->get_id(), '_shipping_address_2', $cart_settings['shipping']['address_2'] );
			update_post_meta( $order->get_id(), '_shipping_city', $cart_settings['shipping']['city'] );
			update_post_meta( $order->get_id(), '_shipping_state', $cart_settings['shipping']['state'] );
			update_post_meta( $order->get_id(), '_shipping_postcode', $cart_settings['shipping']['postcode'] );

			// Save Cart ID
			update_post_meta( $post_id, 'order_id', $order->get_id() );
			update_post_meta( $order->get_id(), 'cart_id', $post_id );

			// Save Order
			$order->calculate_totals();
			$order->update_status( 'processing' );
			$order->add_order_note( "Order created by Carts Post ID {$post_id}" );
			$order->save();

			wp_redirect( $order->get_edit_order_url() );
			exit;
		}
	}

	/**
	 *  Create Order from Cart Edit Page Side Block
	 */
	public function create_meta_box() {
		if ( isset( $_GET['post'] ) ) {
			add_meta_box( 'create_order_from_cart', 'Create order', [ $this, 'create_order_from_cart_metabox' ], [ 'carts' ], 'side' );
		}
		add_meta_box( 'set_supplier_product', 'Select Supplier', [ $this, 'set_supplier_product_metabox' ], [ 'product' ], 'side' );
		add_meta_box( 'add_meta_data_carts', 'Cart Data', [ $this, 'carts_meta_data_fields' ], [ 'carts' ], 'normal' );
	}

	/**
	 * Select Supplier for Products
	 */
	public function set_supplier_product_metabox() {
		if ( isset( $_GET['post'] ) ) {
			$product_supplier = get_post_meta( sanitize_text_field($_GET['post']), 'product_supplier', true );
		}

		// Get All Suppliers
		$args      = array(
			'post_type'      => 'suppliers',
			'orderby'        => 'desc',
			'posts_per_page' => - 1
		);
		$suppliers = get_posts( $args );

		include 'templates/admin/supplier_product_metabox.php';
	}

	/**
	 * Create Order Content
	 */
	public function create_order_from_cart_metabox() {
		include 'templates/admin/create_order_metabox.php';
	}

	/**
	 * Meta Data Cart Post
	 */
	public function carts_meta_data_fields() {
		if ( isset( $_GET['post'] ) ) {
			$cart_settings = get_post_meta( sanitize_text_field($_GET['post']), 'cart_settings', true );
		}

		// Get Countries from Checkout
		if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			$wc_countries = new WC_Countries();
			$countries    = $wc_countries->get_countries();
		}

		// Get All Products
		$args     = array(
			'post_type'      => 'product',
			'orderby'        => 'desc',
			'posts_per_page' => - 1,
		);
		$products = get_posts( $args );

		// Get variations for selected products
		$variations = [];
		if ( isset( $cart_settings['product'] ) && count( $cart_settings['product'] ) > 0 ) {
			foreach ( $cart_settings['product'] as $item => $product_id ) {
				$product = wc_get_product( $product_id );
				if ( $product && $product->is_type( 'variable' ) ) {
					$variations[ $item ]['id'] = $product->get_children();
					foreach ( $variations[ $item ]['id'] as $variation_id ) {
						$product                        = wc_get_product( $variation_id );
						$variations[ $item ]['title'][] = $product->get_title();
					}
				}
			}
		}

		include 'templates/admin/meta_data_carts.php';
	}

	/**
	 * Change Cart Item in DB
	 */
	public function change_cart_status_items( $order_id ) {
		global $wpdb;

		$cart_data = $wpdb->get_row( $wpdb->prepare("SELECT * FROM {$wpdb->prefix}vendors_carts WHERE id = %d", $_SESSION['current_cart_id']) );

		if ( $cart_data ) {
			// Update Post Cart Data
			$data_post           = get_post_meta( $cart_data->post_id, 'cart_settings', true );
			$data_post['status'] = 'finished';

			update_post_meta( $cart_data->post_id, 'cart_settings', $data_post );

			$wpdb->update("{$wpdb->prefix}vendors_carts", array(
				'order_status' => 'finished',
			), [ 'id' => $_SESSION['current_cart_id'] ] );
		}
	}

	/**
	 * Add not created tables
	 */
	public function add_new_column_for_tables()
	{
		global $wpdb;

		$carts = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}vendors_carts");

		//Add column if not present.
		if(!isset($carts->security_id)){
			$wpdb->query("ALTER TABLE {$wpdb->prefix}vendors_carts ADD security_id VARCHAR(255) NOT NULL");
			$carts = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}vendors_carts");

			// Set new ID for security
			foreach($carts as $cart) {
				if(isset($cart->security_id) && $cart->security_id == '') {
					$wpdb->update( "{$wpdb->prefix}vendors_carts", array(
						'security_id'  => md5(wp_generate_password(8, false))
					), ['id' => $cart->id] );
				}
			}
		}
	}

	/**
	 * Create Table in DB for Store Ordered card Information
	 */
	public function create_plugin_tables() {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		// Setup CRON
		if ( ! wp_next_scheduled( 'wpm_carts_reminder' ) ) {
			wp_schedule_event( time(), 'every_1_hour', 'wpm_carts_reminder' );
		} else {
			wp_reschedule_event( time(), 'every_1_hour', 'wpm_carts_reminder' );
		}

		// Create table for correction link
		$sql        = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}vendors_carts (
         id INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
         security_id VARCHAR(255) NOT NULL,
         post_id INTEGER(10) UNSIGNED NOT NULL,
         vendor INTEGER(10) UNSIGNED NOT NULL,
         user_id INTEGER(10) UNSIGNED NOT NULL,
         cart_items LONGTEXT NOT NULL,
         address LONGTEXT NOT NULL,
         order_status VARCHAR(50) NOT NULL,
         last_remind DATE NOT NULL,
         timestamp TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, 
         PRIMARY KEY (id)
        ) {$charset_collate};";
		$wpdb->query( $sql );
	}

	/**
	 * Check if item in the Storage
	 */
	public function validate_add_cart_item( $passed, $product_id, $quantity, $variation_id = '', $variations = '' ) {
		if ( $passed == true ) {
			global $wpdb;

			// Get Vendor ID
			$product_vendor_id = 0;
			if ( get_post_meta( $product_id, 'product_supplier' ) ) {
				$product_vendor_id = get_post_meta( $product_id, 'product_supplier', true );
			}

			// Get Saved Cart Sessions
			$session_carts = isset( $_SESSION['session_carts'] ) && ! empty( $_SESSION['session_carts'] ) ? unserialize( $_SESSION['session_carts'] ) : [];

			// Current Product Object
			$product         = wc_get_product( $variation_id == '' ? $product_id : $variation_id );
			$current_product = [
				'variation_id' => $variation_id == '' ? 0 : $variation_id,
				'product_id'   => $product_id,
				'name'         => $product->get_name(),
				'quantity'     => $quantity,
				'total'        => $product->get_price() * $quantity
			];

			// Prepare Object to Save in DB if it's other Vendor
			$items_content = [];
			$other_vendor = false;
			foreach ( WC()->cart->get_cart() as $cart_item ) {

				// Check if Vendor ID current product is equal products on cart
				$other_product_vendor = get_post_meta( $cart_item['product_id'], 'product_supplier' ) ? get_post_meta( $cart_item['product_id'], 'product_supplier', true ) : 0;
				if ( $other_product_vendor != $product_vendor_id ) {
					$other_vendor = true;
				}

				// Prepare Cart to Save
				$product         = $cart_item['data'];
				$items_content[] = [
					'variation_id' => $cart_item['variation_id'],
					'product_id'   => $cart_item['product_id'],
					'name'         => $product->get_name(),
					'quantity'     => $cart_item['quantity'],
					'total'        => $product->get_price() * $cart_item['quantity']
				];
			}

			// Check if User Logged In
			$user_id = 0;
			if ( is_user_logged_in() ) {
				$user_id = get_current_user_id();
			}

			// Search in Cart Vendor Cart
			$cart_data = $wpdb->get_row( $wpdb->prepare("
                    SELECT * FROM {$wpdb->prefix}vendors_carts 
                    WHERE id IN (%s) 
                      AND vendor = %d 
                      AND order_status!='finished' OR vendor = %d 
                      AND user_id!='0' 
                      AND user_id= %d 
                      AND order_status!='finished'", implode( ',', $session_carts ), $product_vendor_id, $product_vendor_id, $user_id) );

			// Empty cart and search cart Data Vendor to Add new Item
			if ( $other_vendor ) {
				WC()->cart->empty_cart();
				$items_content = isset( $cart_data ) && ! empty( $cart_data ) ? unserialize( $cart_data->cart_items ) : [];

				// Add Products to New Cart
				foreach ( $items_content as $item ) {
					if ( $item['variation_id'] != 0 ) {
						WC()->cart->add_to_cart( $item['variation_id'], $item['quantity'] );
					} else {
						WC()->cart->add_to_cart( $item['product_id'], $item['quantity'] );
					}
				}
			}

			// Add Quantity to already added product
			$not_in_cart = true;
			foreach ( $items_content as &$item ) {
				if ( $item['variation_id'] == $current_product['variation_id'] && $item['product_id'] == $current_product['product_id'] ) {
					$item['quantity'] += $current_product['quantity'];
					$item['total']    += $current_product['total'];
					$not_in_cart      = false;
				}
			}

			// If not in cart add new product to list
			if ( $not_in_cart ) {
				$items_content[] = $current_product;
			}

			if ( isset( $cart_data ) && ! empty( $cart_data ) ) {
				$this->update_cart_item( $product_vendor_id, $user_id, $items_content, $this->get_current_cart_address(), [ 'id' => $cart_data->id ], $cart_data->post_id );
			} else {
				$cart_id = $this->insert_cart_item( $product_vendor_id, $user_id, $items_content, $this->get_current_cart_address() );

				// Add new ID to Session
				$session_carts[] = $cart_id;

				// Save Cart Session Changes
				$_SESSION['current_cart_id'] = $cart_id;
				$_SESSION['session_carts']   = serialize( $session_carts );
			}
		}

		return $passed;
	}

	/**
	 * Update Cart Object for Vendor
	 */
	public function update_cart_item( $product_vendor_id, $user_id, $items_content, $address, $where, $post_id ) {
		global $wpdb;

		if ( $post_id ) {
			// Prepare Meta Data
			$data_post = [
				'status'       => 'created',
				'created_date' => date( 'Y-m-d H:i:s' ),
				'billing'      => [
					'first_name' => $address['billing']['first_name'],
					'last_name'  => $address['billing']['last_name'],
					'company'    => $address['billing']['company'],
					'address_1'  => $address['billing']['address_1'],
					'address_2'  => $address['billing']['address_2'],
					'city'       => $address['billing']['city'],
					'postcode'   => $address['billing']['postcode'],
					'country'    => $address['billing']['country'],
					'state'      => $address['billing']['state'],
					'email'      => $address['billing']['email'],
					'phone'      => $address['billing']['phone']
				],
				'shipping'     => [
					'first_name' => $address['shipping']['first_name'],
					'last_name'  => $address['shipping']['last_name'],
					'company'    => $address['shipping']['company'],
					'address_1'  => $address['shipping']['address_1'],
					'address_2'  => $address['shipping']['address_2'],
					'city'       => $address['shipping']['city'],
					'postcode'   => $address['shipping']['postcode'],
					'country'    => $address['shipping']['country'],
					'state'      => $address['shipping']['state']
				],
			];

			// Set Products
			foreach ( $items_content as $product ) {
				$data_post['product'][]   = $product['product_id'];
				$data_post['variation'][] = $product['variation_id'];
				$data_post['quantity'][]  = $product['quantity'];
			}

			// Save Cart Meta
			update_post_meta( $post_id, 'cart_settings', $data_post );
		}

		$wpdb->update( "{$wpdb->prefix}vendors_carts", array(
			'vendor'     => $product_vendor_id,
			'user_id'    => $user_id,
			'cart_items' => serialize( $items_content ),
			'address'    => serialize( $address )
		), $where );

		return $wpdb->insert_id;
	}

	/**
	 * Insert Cart Object for Vendor
	 */
	public function insert_cart_item( $product_vendor_id, $user_id, $items_content, $address ) {
		global $wpdb;

		// Set Vendor Name
		$vendor_name = 'No Supplier';
		if ( $product_vendor_id > 0 ) {
			$supplier    = get_post( $product_vendor_id );
			$vendor_name = $supplier->post_title;
		}

		// Create Carts Post
		$post_id = wp_insert_post( array(
			'post_title'     => "{$vendor_name} - " . date( 'd.m.Y' ),
			'post_status'    => 'publish',
			'post_type'      => 'carts',
			'post_author'    => $user_id,
			'comment_status' => 'closed',
			'ping_status'    => 'closed'
		) );

		// Prepare Meta Data
		$data_post = [
			'status'       => 'created',
			'created_date' => date( 'Y-m-d H:i:s' ),
			'billing'      => [
				'first_name' => $address['billing']['first_name'],
				'last_name'  => $address['billing']['last_name'],
				'company'    => $address['billing']['company'],
				'address_1'  => $address['billing']['address_1'],
				'address_2'  => $address['billing']['address_2'],
				'city'       => $address['billing']['city'],
				'postcode'   => $address['billing']['postcode'],
				'country'    => $address['billing']['country'],
				'state'      => $address['billing']['state'],
				'email'      => $address['billing']['email'],
				'phone'      => $address['billing']['phone']
			],
			'shipping'     => [
				'first_name' => $address['shipping']['first_name'],
				'last_name'  => $address['shipping']['last_name'],
				'company'    => $address['shipping']['company'],
				'address_1'  => $address['shipping']['address_1'],
				'address_2'  => $address['shipping']['address_2'],
				'city'       => $address['shipping']['city'],
				'postcode'   => $address['shipping']['postcode'],
				'country'    => $address['shipping']['country'],
				'state'      => $address['shipping']['state']
			],
		];

		// Set Products
		foreach ( $items_content as $product ) {
			$data_post['product'][]   = $product['product_id'];
			$data_post['variation'][] = $product['variation_id'];
			$data_post['quantity'][]  = $product['quantity'];
		}

		// Save Cart Meta
		update_post_meta( $post_id, 'cart_settings', $data_post );

		$wpdb->insert( "{$wpdb->prefix}vendors_carts", array(
			'post_id'      => $post_id,
			'security_id'  => md5(wp_generate_password(8, false)),
			'vendor'       => $product_vendor_id,
			'user_id'      => $user_id,
			'cart_items'   => serialize( $items_content ),
			'address'      => serialize( $address ),
			'order_status' => 'created',
			'last_remind'  => date( 'Y-m-d' )
		) );

		update_post_meta( $post_id, 'vendors_carts', $wpdb->insert_id );

		return $wpdb->insert_id;
	}

	/**
	 * Get Billing and Shipping address
	 */
	public function get_current_cart_address() {
		$address = [
			"billing"  => [
				"first_name" => WC()->cart->get_customer()->get_billing_first_name(),
				"last_name"  => WC()->cart->get_customer()->get_billing_last_name(),
				"company"    => WC()->cart->get_customer()->get_billing_company(),
				"address_1"  => WC()->cart->get_customer()->get_billing_address(),
				"address_2"  => WC()->cart->get_customer()->get_billing_address_2(),
				"city"       => WC()->cart->get_customer()->get_billing_city(),
				"postcode"   => WC()->cart->get_customer()->get_billing_postcode(),
				"country"    => WC()->cart->get_customer()->get_billing_country(),
				"state"      => WC()->cart->get_customer()->get_billing_state(),
				"email"      => WC()->cart->get_customer()->get_billing_email(),
				"phone"      => WC()->cart->get_customer()->get_billing_phone()
			],
			"shipping" => [
				"first_name" => WC()->cart->get_customer()->get_shipping_first_name(),
				"last_name"  => WC()->cart->get_customer()->get_shipping_last_name(),
				"company"    => WC()->cart->get_customer()->get_shipping_company(),
				"address_1"  => WC()->cart->get_customer()->get_shipping_address(),
				"address_2"  => WC()->cart->get_customer()->get_shipping_address_2(),
				"city"       => WC()->cart->get_customer()->get_shipping_city(),
				"postcode"   => WC()->cart->get_customer()->get_shipping_postcode(),
				"country"    => WC()->cart->get_customer()->get_shipping_country(),
				"state"      => WC()->cart->get_customer()->get_shipping_state()
			]
		];

		return $address;
	}

	/**
	 * Save cart content
	 */
	public function save_checkout_fields() {
		if ( isset( $_POST['post_data'] ) ) {
			// Get Form data from Checkout
			$form_data = explode( '&', sanitize_text_field( $_POST['post_data'] ) );
			$filtered  = [];

			// Prepare Array
			foreach ( $form_data as $value ) {
				$sliced                 = explode( '=', $value );
				$filtered[ $sliced[0] ] = $sliced[1];
			}

			// Set Customer Billing Data
			WC()->customer->set_billing_first_name( isset( $filtered['billing_first_name'] ) ? $filtered['billing_first_name'] : null );
			WC()->customer->set_billing_last_name( isset( $filtered['billing_last_name'] ) ? $filtered['billing_last_name'] : null );
			WC()->customer->set_billing_company( isset( $filtered['billing_company'] ) ? $filtered['billing_company'] : null );
			WC()->customer->set_billing_address_1( isset( $filtered['billing_address_1'] ) ? $filtered['billing_address_1'] : null );
			WC()->customer->set_billing_address_2( isset( $filtered['billing_address_2'] ) ? $filtered['billing_address_2'] : null );
			WC()->customer->set_billing_city( isset( $filtered['billing_city'] ) ? $filtered['billing_city'] : null );
			WC()->customer->set_billing_postcode( isset( $filtered['billing_postcode'] ) ? $filtered['billing_postcode'] : null );
			WC()->customer->set_billing_country( isset( $filtered['billing_country'] ) ? $filtered['billing_country'] : null );
			WC()->customer->set_billing_state( isset( $filtered['billing_state'] ) ? $filtered['billing_state'] : null );
			WC()->customer->set_billing_phone( isset( $filtered['billing_phone'] ) ? $filtered['billing_phone'] : null );

			// Set Customer Shipping Data
			WC()->customer->set_shipping_first_name( isset( $filtered['shipping_first_name'] ) ? $filtered['shipping_first_name'] : null );
			WC()->customer->set_shipping_last_name( isset( $filtered['shipping_last_name'] ) ? $filtered['shipping_last_name'] : null );
			WC()->customer->set_shipping_company( isset( $filtered['shipping_company'] ) ? $filtered['shipping_company'] : null );
			WC()->customer->set_shipping_address_1( isset( $filtered['shipping_address_1'] ) ? $filtered['shipping_address_1'] : null );
			WC()->customer->set_shipping_address_2( isset( $filtered['shipping_address_2'] ) ? $filtered['shipping_address_2'] : null );
			WC()->customer->set_shipping_city( isset( $filtered['shipping_city'] ) ? $filtered['shipping_city'] : null );
			WC()->customer->set_shipping_postcode( isset( $filtered['shipping_postcode'] ) ? $filtered['shipping_postcode'] : null );
			WC()->customer->set_shipping_country( isset( $filtered['shipping_country'] ) ? $filtered['shipping_country'] : null );
			WC()->customer->set_shipping_state( isset( $filtered['shipping_state'] ) ? $filtered['shipping_state'] : null );
		}
	}

	/**
	 * Delete All User Carts
	 */
	public function clear_all_carts() {
		if ( isset( $_GET['clear_all_carts'] ) ) {
			global $wpdb;

			// Check if User Logged In and Get Cart Items
			if ( is_user_logged_in() ) {
				$user_id       = get_current_user_id();
				$session_carts = $wpdb->get_results( $wpdb->prepare("SELECT * FROM {$wpdb->prefix}vendors_carts WHERE user_id= %d AND order_status!='finished'", $user_id) );
			} else {
				// Get Sessions
				$session_ids   = isset( $_SESSION['session_carts'] ) && ! empty( $_SESSION['session_carts'] ) ? unserialize( $_SESSION['session_carts'] ) : [];
				$session_carts = $wpdb->get_results( $wpdb->prepare("SELECT * FROM {$wpdb->prefix}vendors_carts WHERE id IN (%s) AND order_status!='finished'", implode( ',', $session_ids )) );
			}

			// Delete Cart
			foreach($session_carts as $cart_data) {
				if(isset($cart_data->post_id)) {
					wp_delete_post( $cart_data->post_id );
					$wpdb->delete( "{$wpdb->prefix}vendors_carts", [ 'id' => $cart_data->id ] );
				}
			}

			// Redirect to Cart
			wp_redirect( wc_get_cart_url() );
			exit;
		}
	}

	/**
	 * Delete Cart Content
	 */
	public function cart_session_delete() {
		if ( isset( $_GET['cart_session_delete'] ) ) {
			global $wpdb;

			// Sanitize Selected Cart ID
			$cart_id = sanitize_text_field( $_GET['cart_session_delete'] );

			// Remove Post
			$cart_data = $wpdb->get_row( $wpdb->prepare("SELECT * FROM {$wpdb->prefix}vendors_carts WHERE security_id= %d", $cart_id) );

			if(isset($cart_data->post_id)) {
				wp_delete_post( $cart_data->post_id );
			    $wpdb->delete( "{$wpdb->prefix}vendors_carts", [ 'id' => $cart_id ] );
            }

			// Redirect to Cart
			wp_redirect( wc_get_cart_url() );
			exit;
		}
	}

	/**
	 * Save cart content
	 */
	public function select_cart_session() {
		if ( isset( $_GET['cart_session_set'] ) ) {
			global $wpdb;

			// Sanitize Selected Cart ID
			$cart_id = sanitize_text_field( $_GET['cart_session_set'] );

			// Get Cart Session
			$cart_data = $wpdb->get_row( $wpdb->prepare("SELECT * FROM {$wpdb->prefix}vendors_carts WHERE security_id= %d", $cart_id) );

			// Change Cart Session
			if ( $cart_data ) {
				$_SESSION['current_cart_id'] = $cart_id;

				// Empty cart
				WC()->cart->empty_cart();

				// Add Products to fresh Cart
				foreach ( unserialize( $cart_data->cart_items ) as $item ) {
					if ( $item['variation_id'] != 0 ) {
						WC()->cart->add_to_cart( $item['variation_id'], $item['quantity'] );
					} else {
						WC()->cart->add_to_cart( $item['product_id'], $item['quantity'] );
					}
				}

				// Get Shipping and Billing data
				$billing  = unserialize( $cart_data->address )['billing'];
				$shipping = unserialize( $cart_data->address )['shipping'];

				// Set Customer Billing Data
				WC()->customer->set_billing_first_name( isset( $billing['first_name'] ) ? $billing['first_name'] : null );
				WC()->customer->set_billing_last_name( isset( $billing['last_name'] ) ? $billing['last_name'] : null );
				WC()->customer->set_billing_company( isset( $billing['company'] ) ? $billing['company'] : null );
				WC()->customer->set_billing_address_1( isset( $billing['address_1'] ) ? $billing['address_1'] : null );
				WC()->customer->set_billing_address_2( isset( $billing['address_2'] ) ? $billing['address_2'] : null );
				WC()->customer->set_billing_city( isset( $billing['city'] ) ? $billing['city'] : null );
				WC()->customer->set_billing_postcode( isset( $billing['postcode'] ) ? $billing['postcode'] : null );
				WC()->customer->set_billing_country( isset( $billing['country'] ) ? $billing['country'] : null );
				WC()->customer->set_billing_state( isset( $billing['state'] ) ? $billing['state'] : null );
				WC()->customer->set_billing_email( isset( $billing['email'] ) ? $billing['email'] : null );
				WC()->customer->set_billing_phone( isset( $billing['phone'] ) ? $billing['phone'] : null );

				// Set Customer Shipping Data
				WC()->customer->set_shipping_first_name( isset( $shipping['first_name'] ) ? $shipping['first_name'] : null );
				WC()->customer->set_shipping_last_name( isset( $shipping['last_name'] ) ? $shipping['last_name'] : null );
				WC()->customer->set_shipping_company( isset( $shipping['company'] ) ? $shipping['company'] : null );
				WC()->customer->set_shipping_address_1( isset( $shipping['address_1'] ) ? $shipping['address_1'] : null );
				WC()->customer->set_shipping_address_2( isset( $shipping['address_2'] ) ? $shipping['address_2'] : null );
				WC()->customer->set_shipping_city( isset( $shipping['city'] ) ? $shipping['city'] : null );
				WC()->customer->set_shipping_postcode( isset( $shipping['postcode'] ) ? $shipping['postcode'] : null );
				WC()->customer->set_shipping_country( isset( $shipping['country'] ) ? $shipping['country'] : null );
				WC()->customer->set_shipping_state( isset( $shipping['state'] ) ? $shipping['state'] : null );
			}

			// Redirect to Cart
			wp_redirect( wc_get_cart_url() );
			exit;
		}
	}

	/**
	 * Show table with select Cart Session
	 */
	public function show_select_cart_session() {
		global $wpdb;

		// Check if User Logged In and Get Cart Items
		if ( is_user_logged_in() ) {
			$user_id       = get_current_user_id();
			$session_carts = $wpdb->get_results( $wpdb->prepare("SELECT * FROM {$wpdb->prefix}vendors_carts WHERE user_id= %d AND order_status!='finished'", $user_id) );
		} else {
			// Get Sessions
			$session_ids   = isset( $_SESSION['session_carts'] ) && ! empty( $_SESSION['session_carts'] ) ? unserialize( $_SESSION['session_carts'] ) : [];
			$session_carts = $wpdb->get_results( $wpdb->prepare("SELECT * FROM {$wpdb->prefix}vendors_carts WHERE id IN (%s) AND order_status!='finished'", implode( ',', $session_ids )) );
		}

		include( 'templates/frontend/select_multicart_session.php' );
	}

	/**
	 * Include Scripts And Styles on FrontEnd
	 */
	public function include_scripts_and_styles() {
		// Register styles
		wp_enqueue_style( WPM_MULTICART_ASSETS . '-core', plugins_url( 'templates/assets/css/frontend.css', __FILE__ ), false, '1.0.7', 'all' );

		// Register scripts
		wp_enqueue_script( WPM_MULTICART_ASSETS . '-core', plugins_url( 'templates/assets/js/frontend.js', __FILE__ ), array( 'jquery' ), '1.0.7', 'all' );
	}

	/**
	 * Include Scripts And Styles on Admin Pages
	 */
	public function admin_scripts_and_styles() {
		// Register styles
		wp_enqueue_style( WPM_MULTICART_ASSETS . '-font-awesome', plugins_url( 'templates/libs/font-awesome/scripts/all.min.css', __FILE__ ) );
		wp_enqueue_style( WPM_MULTICART_ASSETS . '-admin', plugins_url( 'templates/assets/css/admin.css', __FILE__ ), false, '1.0.20', 'all' );

		// Register scripts
		wp_enqueue_script( WPM_MULTICART_ASSETS . '-font-awesome', plugins_url( 'templates/libs/font-awesome/scripts/all.min.js', __FILE__ ), array( 'jquery' ), '1.0.2', 'all' );
		wp_enqueue_script( WPM_MULTICART_ASSETS . '-core-admin', plugins_url( 'templates/assets/js/admin.js', __FILE__ ) );
		wp_localize_script( WPM_MULTICART_ASSETS . '-core-admin', 'admin', array(
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'ajax_nonce' )
		) );
	}
}

new WPM_MultiCart_WooCommerce();

