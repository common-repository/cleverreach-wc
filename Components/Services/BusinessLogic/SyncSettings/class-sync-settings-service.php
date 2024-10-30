<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Components\Services\BusinessLogic\SyncSettings;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Config\SyncService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\SyncSettings\SyncSettingsService as BaseSyncSettingsService;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;

/**
 * Class Sync_Settings_Service
 *
 * @package CleverReach\WooCommerce\Components\Services\BusinessLogic\SyncSettings
 */
class Sync_Settings_Service extends BaseSyncSettingsService {


	/**
	 * Retrieves available services.
	 *
	 * @return SyncService[]
	 */
	public function getAvailableServices() {
		/**
		 * Subscriber sync service.
		 *
		 * @var Subscriber_Sync_Service $subscriber_sync_service
		 */
		$subscriber_sync_service = ServiceRegister::getService( Subscriber_Sync_Service::CLASS_NAME );
		/**
		 * Buyer sync service.
		 *
		 * @var Buyer_Sync_Service $buyer_sync_service
		 */
		$buyer_sync_service = ServiceRegister::getService( Buyer_Sync_Service::CLASS_NAME );
		/**
		 * Contact sync service.
		 *
		 * @var Contact_Sync_Service $contact_sync_service
		 */
		$contact_sync_service = ServiceRegister::getService( Contact_Sync_Service::CLASS_NAME );

		return array(
			$subscriber_sync_service,
			$buyer_sync_service,
			$contact_sync_service,
		);
	}
}
