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
use Exception;

/**
 * Class Create_Data_Resources_Entity_Table
 *
 * @package CleverReach\WooCommerce\Migrations\V333
 */
class Create_Data_Resources_Entity_Table extends Step {

	const CLASS_NAME = __CLASS__;

	/**
	 * Execute.
	 *
	 * @inheritDoc
	 * @return void
	 */
	public function execute() {
		try {
			$this->create_data_resources_entity_table();
		} catch ( Exception $exception ) {
			Logger::logError( 'Failed to create data resources entity table.' );
		}
	}

	/**
	 * Create data resources entity table
	 *
	 * @return void
	 */
	private function create_data_resources_entity_table() {
		$query = 'CREATE TABLE IF NOT EXISTS `'
				. Base_Repository::get_table_name( 'cleverreach_wc_data_resources_entity' ) . '` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `type` VARCHAR(255) NOT NULL,
            `index_1` VARCHAR(255),
            `index_2` VARCHAR(255),
            `index_3` VARCHAR(255),
            `index_4` VARCHAR(255),
            `data` TEXT,
            PRIMARY KEY (`id`),
            INDEX type_index1 (type, index_1)
        ) DEFAULT CHARACTER SET utf8';

		$this->db->query( $query );
	}
}
