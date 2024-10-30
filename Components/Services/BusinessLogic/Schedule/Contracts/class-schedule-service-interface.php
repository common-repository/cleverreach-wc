<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Components\Services\BusinessLogic\Schedule\Contracts;

use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException;

/**
 * Class Schedule_Service_Interface
 *
 * @package CleverReach\WooCommerce\Components\Services\BusinessLogic\Schedule\Contracts
 */
interface Schedule_Service_Interface {

	const CLASS_NAME = __CLASS__;

	const SCHEDULE_TYPE_MINUTE  = 'minute';
	const SCHEDULE_TYPE_HOURLY  = 'hourly';
	const SCHEDULE_TYPE_DAILY   = 'daily';
	const SCHEDULE_TYPE_MONTHLY = 'monthly';

	/**
	 * Register Schedules during auth process
	 *
	 * @return void
	 *
	 * @throws RepositoryNotRegisteredException Exception if repository not registered.
	 */
	public function register_schedules();

	/**
	 * Register schedule tasks for version 3.0.0
	 *
	 * @return void
	 *
	 * @throws RepositoryNotRegisteredException Exception if repository not registered.
	 */
	public function register_schedules_v300();
}
