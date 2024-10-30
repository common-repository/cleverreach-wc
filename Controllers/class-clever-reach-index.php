<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Controllers;

use CleverReach\WooCommerce\Components\Util\HTTP_Helper;

/**
 * Class Clever_Reach_Index
 *
 * @package CleverReach\WooCommerce\Controllers
 */
class Clever_Reach_Index extends Clever_Reach_Base_Controller {

	/**
	 * Controller index action.
	 *
	 * @return void
	 */
	public function index() {
		$controller_name = HTTP_Helper::get_param( 'cleverreach_wc_controller' );

		if ( ! $this->validate_controller_name( $controller_name ) ) {
			status_header( 404 );
			nocache_headers();

			require get_404_template();

			exit();
		}

		$class_name = '\CleverReach\WooCommerce\Controllers\Clever_Reach_' . $controller_name . '_Controller';
		/**
		 * Base controller
		 *
		 * @var Clever_Reach_Base_Controller $controller
		 */
		$controller = new $class_name();
		$controller->process();
	}

	/**
	 * Validates controller name by checking whether it exists in the list of known controller names.
	 *
	 * @param string $controller_name Controller name from request input.
	 *
	 * @return bool
	 */
	private function validate_controller_name( $controller_name ) {
		return in_array(
			$controller_name,
			array(
				'Async_Process',
				'Callback',
				'Check_Status',
				'Config',
				'Frontend',
				'Product_Search',
				'Settings',
				'Single_Sign_On',
				'Support',
				'Autoconfigure',
				'Auth',
				'Refresh',
				'Sync_Settings',
				'Newsletter_Settings',
				'Uninstall',
				'Initial_Sync',
				'Secondary_Sync',
				'Form_Event_Webhook',
				'Receiver_Event_Webhook',
				'Abandoned_Cart_Settings',
				'Automation_Event_Webhook',
				'Abandoned_Cart_Billing_Email_Listener',
				'Cart_Recovery',
				'Newsletter_Subscription',
				'Abandoned_Cart_Overview',
				'Abandoned_Cart_Overview_Options',
				'Group_Event_Webhook',
				'Interval',
			),
			true
		);
	}
}
