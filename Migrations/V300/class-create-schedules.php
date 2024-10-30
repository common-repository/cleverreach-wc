<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Migrations\V300;

use CleverReach\WooCommerce\Components\Services\BusinessLogic\Schedule\Schedule_Service;
use CleverReach\WooCommerce\Components\Util\Step;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\WooCommerce\Migrations\Exceptions\Failed_To_Execute_Migration_Step_Exception;
use Exception;

/**
 * Class Create_Schedules.
 *
 * @package CleverReach\WooCommerce\Migrations\V300
 */
class Create_Schedules extends Step {


	const CLASS_NAME = __CLASS__;

	/**
	 * Execute migration step.
	 *
	 * @throws Failed_To_Execute_Migration_Step_Exception Failed to execute migration step exception|Query filter invalid param exception.
	 */
	public function execute() {
		try {
			$this->get_schedule_service()->register_schedules_v300();
		} catch ( Exception $e ) {
			throw new Failed_To_Execute_Migration_Step_Exception(
				'Failed to execute migration step because: ' . esc_html( $e->getMessage() )
			);
		}
	}

	/**
	 * Retrieve Schedule service.
	 *
	 * @return Schedule_Service
	 */
	private function get_schedule_service() {
		/**
		 * Schedule service.
		 *
		 * @var Schedule_Service $schedule_service
		 */
		$schedule_service = ServiceRegister::getService( Schedule_Service::CLASS_NAME );

		return $schedule_service;
	}
}
