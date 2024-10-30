<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Components\HookHandlers\Automation;

use CleverReach\WooCommerce\Components\HookHandlers\Base_Handler;
use CleverReach\WooCommerce\Components\Services\BusinessLogic\Automation\Cart_Automation_Service;
use CleverReach\WooCommerce\Components\Services\BusinessLogic\Automation\Recovery_Record_Service;
use CleverReach\WooCommerce\Components\Util\HTTP_Helper;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Exceptions\BaseException;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Logger\LogContextData;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Logger\Logger;

/**
 * Class Before_Checkout_Handler
 *
 * @package CleverReach\WooCommerce\Components\HookHandlers\Automation
 */
class Before_Checkout_Handler extends Base_Handler {


	/**
	 * Handles merging of carts before checkout.
	 *
	 * @return void
	 */
	public function handle() {
		$cr_redirect = HTTP_Helper::get_param( 'cr_redirect' );
		$token       = HTTP_Helper::get_param( 'token' );

		if ( ! $cr_redirect || ! $token || ! $this->should_handle_event() ) {
			return;
		}

		$recovery_record_service = new Recovery_Record_Service();
		$recovery_record         = null;
		try {
			$recovery_record = $recovery_record_service->find_by_token( $token );
		} catch ( BaseException $e ) {
			Logger::logError(
				'Recovery cart not found.',
				'Integration',
				array(
					new LogContextData( 'success', false ),
					new LogContextData( 'message', $e->getMessage() ),
				)
			);
		}

		$cart_automation_service = new Cart_Automation_Service();
		$cart_automation_service->merge_carts_before_checkout( $recovery_record );
	}
}
