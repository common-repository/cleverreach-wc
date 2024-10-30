<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Components\Services\BusinessLogic\Notification\Contracts;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Notification\Contracts\NotificationService;

interface Notification_Service_Interface extends NotificationService {


	/**
	 * Returns whether CleverReach notification should be shown.
	 *
	 * @return bool
	 */
	public function should_show_notifications();

	/**
	 * Shows admin notification.
	 *
	 * @return void
	 */
	public function show_message();
}
