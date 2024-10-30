<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Migrations\Contracts;

use CleverReach\WooCommerce\Components\Services\BusinessLogic\Configuration\Config_Service;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Configuration\Configuration;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\QueueService;
use wpdb;

/**
 * Class Update_Schema
 *
 * @package CleverReach\WooCommerce\Components\Utility
 */
abstract class Update_Schema {


	/**
	 * WordPress database
	 *
	 * @var wpdb WordPress database
	 */
	protected $db;

	/**
	 * Queue service
	 *
	 * @var QueueService
	 */
	protected $queue_service;

	/**
	 * Config service
	 *
	 * @var Config_Service
	 */
	protected $config_service;


	/**
	 * Update_Schema constructor.
	 */
	public function __construct() {
		global $wpdb;
		$this->db = $wpdb;

		/**
		 * Config service
		 *
		 * @var Config_Service $config_service
		 */
		$config_service       = ServiceRegister::getService( Configuration::CLASS_NAME );
		$this->config_service = $config_service;

		/**
		 * Queue service
		 *
		 * @var QueueService $queue_service
		 */
		$queue_service       = ServiceRegister::getService( QueueService::CLASS_NAME );
		$this->queue_service = $queue_service;
	}

	/**
	 * Run update logic for current migration.
	 *
	 * @return void
	 */
	abstract public function update();
}
