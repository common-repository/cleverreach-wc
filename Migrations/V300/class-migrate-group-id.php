<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Migrations\V300;

use CleverReach\WooCommerce\Components\Util\Database;
use CleverReach\WooCommerce\Components\Util\Step;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Group\Contracts\GroupService;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\WooCommerce\Migrations\Exceptions\Failed_To_Execute_Migration_Step_Exception;
use Exception;

/**
 * Class Migrate_Group_Id.
 *
 * @package CleverReach\WooCommerce\Migrations\V300
 */
class Migrate_Group_Id extends Step {


	const CLASS_NAME = __CLASS__;

	/**
	 * Execute migration step.
	 *
	 * @throws Failed_To_Execute_Migration_Step_Exception Failed to execute migration step exception.
	 */
	public function execute() {
		try {
			$database = new Database( $this->db );
			$group_id = $database->get_old_config_value( 'CLEVERREACH_INTEGRATION_ID' );

			if ( ! empty( $group_id ) ) {
				$this->get_group_service()->setId( $group_id );
			}
		} catch ( Exception $e ) {
			throw new Failed_To_Execute_Migration_Step_Exception(
				'Failed to execute migration step because: ' . esc_html( $e->getMessage() )
			);
		}
	}

	/**
	 * Retrieve Group service
	 *
	 * @return GroupService
	 */
	private function get_group_service() {
		/**
		 * Group service.
		 *
		 * @var GroupService $group_service
		 */
		$group_service = ServiceRegister::getService( GroupService::CLASS_NAME );

		return $group_service;
	}
}
