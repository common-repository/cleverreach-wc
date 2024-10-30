<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Components\Services\BusinessLogic\Events;

use CleverReach\WooCommerce\Components\Util\Shop_Helper;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Group\GroupEventsService as BaseGroupEventsService;

/**
 * Class Group_Events_Service
 *
 * @package CleverReach\WooCommerce\Components\Services\BusinessLogic\Events
 */
class Group_Events_Service extends BaseGroupEventsService {
	/**
	 * Provides url that will listen to web hook requests.
	 *
	 * @return string
	 */
	public function getEventUrl() {
		return Shop_Helper::get_controller_url( 'Group_Event_Webhook', 'handle_group' );
	}
}
