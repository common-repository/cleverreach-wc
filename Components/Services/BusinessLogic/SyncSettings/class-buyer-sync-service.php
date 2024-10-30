<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Components\Services\BusinessLogic\SyncSettings;

use CleverReach\WooCommerce\Components\Services\BusinessLogic\Customers\Buyer_Service;
use CleverReach\WooCommerce\Components\Services\BusinessLogic\Merger\Buyer_Merger;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Contracts\SyncServicePriority;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Config\SyncService;

/**
 * Class Buyer_Sync_Settings
 *
 * @package CleverReach\WooCommerce\Components\Services\BusinessLogic\SyncSettings
 */
class Buyer_Sync_Service extends SyncService {


	const CLASS_NAME = __CLASS__;

	/**
	 * Buyer_Sync_Service constructor.
	 */
	public function __construct() {
		parent::__construct(
			'buyer-service',
			SyncServicePriority::MEDIUM,
			Buyer_Service::THIS_CLASS_NAME,
			Buyer_Merger::CLASS_NAME
		);
	}

	/**
	 * Creates instance of Buyer_Sync_Service from array.
	 *
	 * @param mixed[] $data Data array.
	 *
	 * @return Buyer_Sync_Service
	 */
	public static function fromArray( array $data ) {
		return new self();
	}
}
