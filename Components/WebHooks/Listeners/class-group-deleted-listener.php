<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Components\WebHooks\Listeners;

use CleverReach\WooCommerce\Components\WebHooks\Handlers\Group_Deleted_Handler;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException;

/**
 * Class Group_Deleted_Listener
 *
 * @package CleverReach\WooCommerce\Components\WebHooks\Listeners
 */
class Group_Deleted_Listener extends Listener {
	const CLASS_NAME = __CLASS__;

	/**
	 * Handles group deleted event.
	 *
	 * @return void
	 *
	 * @throws QueueStorageUnavailableException Queue storage unavailable.
	 * @throws QueryFilterInvalidParamException Query filter invalid parameter.
	 */
	public static function handle() {
		static::enqueue( new Group_Deleted_Handler() );
	}
}
