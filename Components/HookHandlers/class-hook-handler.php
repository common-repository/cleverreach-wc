<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Components\HookHandlers;

use CleverReach\WooCommerce\Components\Exceptions\Unable_To_Create_Hook_Handler_Exception;

/**
 * Class Hook_Handler
 *
 * @package CleverReach\WooCommerce\Components\HookHandlers
 */
class Hook_Handler {

	/**
	 * Registers hook handlers.
	 *
	 * @return void
	 *
	 * @throws Unable_To_Create_Hook_Handler_Exception When hook handler is unknown.
	 */
	public function register_hooks() {
		if ( is_multisite() ) {
			$this->register_hook( 'add_user_to_blog', 'add_user_to_blog', 100, 1 );
			$this->register_hook( 'profile_update', 'profile_update_multisite', 100, 2 );
			$this->register_hook( 'remove_user_from_blog', 'remove_user_from_blog', 100, 1 );
			$this->register_hook( 'set_user_role', 'add_user_role_multisite', 100, 3 );
			$this->register_hook( 'ure_user_permissions_update', 'add_user_role_multisite', 100, 1 );
		} else {
			$this->register_hook( 'profile_update', 'profile_update_single_site', 100, 2 );
			$this->register_hook( 'delete_user', 'delete_user', 100, 1 );
			$this->register_hook( 'set_user_role', 'add_user_role_single_site', 100, 3 );
			$this->register_hook( 'ure_user_permissions_update', 'add_user_role_single_site', 100, 1 );
		}

		$this->register_hook( 'user_register', 'user_register', 100, 1 );
		$this->register_hook( 'update_option', 'update_option', 100, 3 );
		$this->register_hook( 'woocommerce_thankyou', 'order_created', 100, 1 );
		$this->register_hook( 'woocommerce_process_shop_order_meta', 'order_created', 100, 1 );
		$this->register_hook( 'woocommerce_checkout_update_order_meta', 'add_newsletter_to_order', 100, 1 );
		$this->register_hook( 'woocommerce_before_single_product', 'view_product', 100, 0 );
		$this->register_hook( 'woocommerce_store_api_checkout_update_order_from_request', 'update_order_from_request', 100, 2 );

		// automation's hook.
		$this->register_hook( 'woocommerce_add_to_cart', 'update_cart', 100, 0 );
		$this->register_hook( 'woocommerce_remove_cart_item', 'update_cart', 100, 0 );
		$this->register_hook( 'woocommerce_restore_cart_item', 'update_cart', 100, 0 );
		$this->register_hook( 'woocommerce_cart_item_set_quantity', 'update_cart', 100, 0 );
		$this->register_hook( 'woocommerce_update_cart_action_cart_updated', 'update_cart', 100, 0 );

		$this->register_hook( 'wp_authenticate', 'before_user_login', 100, 2 );

		$this->register_hook( 'woocommerce_before_checkout_form', 'before_checkout', 100, 0 );
	}

	/**
	 * Registers hook.
	 *
	 * @param string $name Hook name.
	 *
	 * @param string $handler Hook handler.
	 *
	 * @param int    $priority Hook priority.
	 *
	 * @param int    $number_of_arguments Number of arguments of hook handler method.
	 *
	 * @return void
	 *
	 * @throws Unable_To_Create_Hook_Handler_Exception Unable to create hook handler exception.
	 */
	private function register_hook( $name, $handler, $priority, $number_of_arguments ) {
		add_action(
			$name,
			array( Hook_Handler_Factory::create( $handler ), 'handle' ),
			$priority,
			$number_of_arguments
		);
	}
}
