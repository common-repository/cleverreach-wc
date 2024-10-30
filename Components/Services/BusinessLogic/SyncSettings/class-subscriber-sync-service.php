<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Components\Services\BusinessLogic\SyncSettings;

use CleverReach\WooCommerce\Components\Services\BusinessLogic\Customers\Subscriber_Service;
use CleverReach\WooCommerce\Components\Services\BusinessLogic\Merger\Subscriber_Merger;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Contracts\SyncServicePriority;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Config\SyncService;

/**
 * Class Subscriber_Sync_Service
 *
 * @package CleverReach\WooCommerce\Components\Services\BusinessLogic\SyncSettings
 */
class Subscriber_Sync_Service extends SyncService {

	const CLASS_NAME = __CLASS__;

	/**
	 * Subscriber_Sync_Service constructor.
	 */
	public function __construct() {
		parent::__construct(
			'subscriber-service',
			SyncServicePriority::HIGH,
			Subscriber_Service::THIS_CLASS_NAME,
			Subscriber_Merger::CLASS_NAME
		);
	}

	/**
	 * Creates instance of Subscriber_Sync_Service from array.
	 *
	 * @param mixed[] $data Array of data.
	 *
	 * @return Subscriber_Sync_Service
	 */
	public static function fromArray( array $data ) {
		return new self();
	}
}
