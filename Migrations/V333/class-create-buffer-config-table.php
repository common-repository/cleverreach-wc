<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Migrations\V333;

use CleverReach\WooCommerce\Components\Repositories\Base_Repository;
use CleverReach\WooCommerce\Components\Util\Step;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Logger\Logger;

/**
 * Class Create_Buffer_Config_Table
 *
 * @package CleverReach\WooCommerce\Migrations\V333
 */
class Create_Buffer_Config_Table extends Step {

	const CLASS_NAME = __CLASS__;

	/**
	 * Executes migration.
	 *
	 * @return void
	 */
	public function execute() {
		try {
			$this->create_buffer_config_table();
		} catch ( \Exception $exception ) {
			Logger::logError( 'Failed to create buffer config table.' );
		}
	}

	/**
	 * Create buffer config table
	 *
	 * @return void
	 */
	private function create_buffer_config_table() {
		$query = 'CREATE TABLE IF NOT EXISTS `'
				. Base_Repository::get_table_name( 'cleverreach_wc_buffer_config' ) . '` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `context` VARCHAR(255),
            `interval_type` VARCHAR(255),
            `interval_time` INT,
            `next_run` INT,
            `has_events` TINYINT(1),
            PRIMARY KEY (`id`)
        ) DEFAULT CHARACTER SET utf8';

		$this->db->query( $query );
	}
}
