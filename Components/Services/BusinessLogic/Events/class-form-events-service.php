<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Components\Services\BusinessLogic\Events;

use CleverReach\WooCommerce\Components\Util\Shop_Helper;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Form\FormEventsService;

/**
 * Class Form_Events_Service
 *
 * @package CleverReach\WooCommerce\Components\Services\BusinessLogic\Events
 */
class Form_Events_Service extends FormEventsService {

	/**
	 * Provides url that will listen to web hook requests.
	 *
	 * @return string
	 */
	public function getEventUrl() {
		return Shop_Helper::get_controller_url( 'Form_Event_Webhook', 'handle_form' );
	}
}
