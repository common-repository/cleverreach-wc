<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Components\WebHooks\Handlers;

use CleverReach\WooCommerce\Components\Services\BusinessLogic\Uninstall\Contracts\Uninstall_Service_Interface;
use CleverReach\WooCommerce\Components\Services\BusinessLogic\Uninstall\Uninstall_Service;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Task;

/**
 * Class Group_Deleted_Handler
 *
 * @package CleverReach\WooCommerce\Components\WebHooks\Handlers
 */
class Group_Deleted_Handler extends Task {

	/**
	 * Executes handler.
	 *
	 * @inheritDoc
	 *
	 * @return void
	 */
	public function execute() {
		/**
		 * Uninstall service
		 *
		 * @var Uninstall_Service $uninstall_service
		 */
		$uninstall_service = ServiceRegister::getService( Uninstall_Service_Interface::CLASS_NAME );
		$this->reportProgress( 30 );
		$uninstall_service->remove_data_on_group_delete();
		$this->reportProgress( 100 );
	}

	/**
	 * Check if is archivable.
	 *
	 * @inheritDoc
	 *
	 * @return false
	 */
	public function isArchivable() {
		return false;
	}
}
