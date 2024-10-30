<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Components\Services\BusinessLogic\Automation\Multistore;

use CleverReach\WooCommerce\Components\Util\Shop_Helper;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Interfaces\Required\AutomationWebhooksService as AutomationWebhooksServiceInterface;

/**
 * Class Webhook_Service
 *
 * @package CleverReach\WooCommerce\Components\Services\BusinessLogic\Automation\Multistore
 */
class Webhook_Service implements AutomationWebhooksServiceInterface {


	/**
	 * Provides automation webhook url.
	 *
	 * @param int $automation_id Automation ID.
	 *
	 * @return string
	 */
	public function getWebhookUrl( $automation_id ) {
		return Shop_Helper::get_controller_url(
			'Automation_Event_Webhook',
			'execute',
			array( 'crAutomationId' => $automation_id )
		);
	}
}
