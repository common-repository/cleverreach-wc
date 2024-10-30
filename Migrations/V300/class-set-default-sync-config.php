<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Migrations\V300;

use CleverReach\WooCommerce\Components\Services\BusinessLogic\SyncSettings\Subscriber_Sync_Service;
use CleverReach\WooCommerce\Components\Services\BusinessLogic\SyncSettings\Sync_Settings_Service;
use CleverReach\WooCommerce\Components\Util\Step;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\WooCommerce\Migrations\Exceptions\Failed_To_Execute_Migration_Step_Exception;
use Exception;

/**
 * Class Set_Default_Sync_Config.
 *
 * @package CleverReach\WooCommerce\Migrations\V300
 */
class Set_Default_Sync_Config extends Step {


	const CLASS_NAME = __CLASS__;

	/**
	 * Execute migration step.
	 *
	 * @throws Failed_To_Execute_Migration_Step_Exception Failed to execute migration step exception.
	 */
	public function execute() {
		try {
			$this->get_sync_config_service()->setEnabledServices( array( new Subscriber_Sync_Service() ) );
		} catch ( Exception $e ) {
			throw new Failed_To_Execute_Migration_Step_Exception(
				'Failed to execute migration step because: ' . esc_html( $e->getMessage() )
			);
		}
	}

	/**
	 * Retrieve sync config service.
	 *
	 * @return Sync_Settings_Service
	 */
	private function get_sync_config_service() {
		/**
		 * Sync settings service.
		 *
		 * @var Sync_Settings_Service $sync_config_service
		 */
		$sync_config_service = ServiceRegister::getService( Sync_Settings_Service::CLASS_NAME );

		return $sync_config_service;
	}
}
