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
 * Class Create_Events_Buffer_Entity_Table
 *
 * @package CleverReach\WooCommerce\Migrations\V333
 */
class Create_Events_Buffer_Entity_Table extends Step {

	const CLASS_NAME = __CLASS__;

	/**
	 * Executes migration.
	 *
	 * @return void
	 */
	public function execute() {
		try {
			$this->create_events_buffer_entity_table();
		} catch ( \Exception $exception ) {
			Logger::logError( 'Failed to create events buffer entity table.' );
		}
	}

	/**
	 * Create events buffer entity table
	 *
	 * @return void
	 */
	private function create_events_buffer_entity_table() {
		$query = 'CREATE TABLE IF NOT EXISTS `'
				. Base_Repository::get_table_name( 'cleverreach_wc_events_buffer_entity' ) . '` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `type` VARCHAR(255) NOT NULL,
            `index_1` VARCHAR(255),
            `index_2` VARCHAR(255),
            `data` LONGTEXT,
            PRIMARY KEY (`id`),
            INDEX index1 (index_1),
            INDEX index1_index2 (index_1, index_2)
        ) DEFAULT CHARACTER SET utf8';

		$this->db->query( $query );
	}
}
