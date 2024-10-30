<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Migrations\V330;

use CleverReach\WooCommerce\Components\Repositories\Base_Repository;
use CleverReach\WooCommerce\Components\Util\Step;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Logger\Logger;
use Exception;

/**
 * Class Create_Archive_Table
 *
 * @package CleverReach\WooCommerce\Migrations\V330
 */
class Create_Archive_Table extends Step {

	const CLASS_NAME = __CLASS__;

	/**
	 * Execute.
	 *
	 * @inheritDoc
	 * @return void
	 */
	public function execute() {
		try {
			$this->create_archive_table();
		} catch ( Exception $exception ) {
			Logger::logError( 'Failed to create archive table.' );
		}
	}

	/**
	 * Create archive table
	 *
	 * @return void
	 */
	private function create_archive_table() {
		$query = 'CREATE TABLE IF NOT EXISTS `'
				. Base_Repository::get_table_name( 'cleverreach_wc_archive' ) . '` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `type` VARCHAR(255) NOT NULL,
            `index_1` VARCHAR(255),
            `index_2` VARCHAR(255),
            `index_3` VARCHAR(255),
            `index_4` VARCHAR(255),
            `index_5` VARCHAR(255),
            `index_6` VARCHAR(255),
            `index_7` VARCHAR(255),
            `index_8` VARCHAR(255),
            `index_9` VARCHAR(255),
            `index_10` VARCHAR(255),
            `data` LONGTEXT,
            PRIMARY KEY (`id`),
            INDEX configKey (type, index_1),
            INDEX latestByType (index_2, index_5),
            INDEX typeStatus (index_1, index_3, index_8)
        ) DEFAULT CHARACTER SET utf8';

		$this->db->query( $query );
	}
}
