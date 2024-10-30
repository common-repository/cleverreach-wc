<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Components\Services\BusinessLogic\SyncSettings;

use CleverReach\WooCommerce\Components\Services\BusinessLogic\Customers\Contact_Service;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Contracts\SyncServicePriority;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Config\SyncService;

/**
 * Class Contact_Sync_Service
 *
 * @package CleverReach\WooCommerce\Components\Services\BusinessLogic\SyncSettings
 */
class Contact_Sync_Service extends SyncService {


	const CLASS_NAME = __CLASS__;

	/**
	 * Contact_Sync_Service constructor.
	 */
	public function __construct() {
		parent::__construct(
			'contact-service',
			SyncServicePriority::LOWEST,
			Contact_Service::THIS_CLASS_NAME
		);
	}

	/**
	 * Creates instance of Contact_Sync_Service from array.
	 *
	 * @param mixed[] $data Array of data.
	 *
	 * @return Contact_Sync_Service
	 */
	public static function fromArray( array $data ) {
		return new self();
	}
}
