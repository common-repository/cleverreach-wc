<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Components\HookHandlers;

use CleverReach\WooCommerce\Components\Exceptions\Unable_To_Create_Hook_Handler_Exception;
use CleverReach\WooCommerce\Components\HookHandlers\Automation\Before_Checkout_Handler;
use CleverReach\WooCommerce\Components\HookHandlers\Automation\Before_User_Login_Handler;
use CleverReach\WooCommerce\Components\HookHandlers\Automation\Update_Cart_Handler;

/**
 * Class Hook_Handler_Factory
 *
 * @package CleverReach\WooCommerce\Components\HookHandlers
 */
class Hook_Handler_Factory {

	/**
	 * Instantiates hook handler for a given action.
	 *
	 * @param string $action that defines what handler should be instantiated.
	 *
	 * @return Base_Handler
	 *
	 * @throws Unable_To_Create_Hook_Handler_Exception When action is unknown.
	 */
	public static function create( $action ) {
		switch ( $action ) {
			case 'add_user_to_blog':
			case 'user_register':
				return new Customer_Created_Handler();
			case 'profile_update_single_site':
				return new Customer_Updated_Handler();
			case 'add_user_role_single_site':
				return new Customer_Role_Added_Handler();
			case 'profile_update_multisite':
				return new Multisite_Customer_Updated_Handler();
			case 'add_user_role_multisite':
				return new Multisite_Customer_Role_Added_Handler();
			case 'delete_user':
			case 'remove_user_from_blog':
				return new Customer_Deleted_Handler();
			case 'update_option':
				return new Option_Changed_Handler();
			case 'order_created':
				return new Order_Saved_Handler();
			case 'update_order_from_request':
				return new Order_Update_From_Request_Handler();
			case 'add_newsletter_to_order':
				return new Add_Newsletter_To_Order_Handler();
			case 'view_product':
				return new Product_View_Handler();
			case 'update_cart':
				return new Update_Cart_Handler();
			case 'before_user_login':
				return new Before_User_Login_Handler();
			case 'before_checkout':
				return new Before_Checkout_Handler();
		}

		throw new Unable_To_Create_Hook_Handler_Exception( 'Unknown action: ' . esc_html( $action ) );
	}
}
