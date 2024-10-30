<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Migrations\Scripts;

use CleverReach\WooCommerce\Components\Repositories\Base_Repository;
use CleverReach\WooCommerce\Components\Util\Migrator;
use CleverReach\WooCommerce\Migrations\Contracts\Update_Schema;
use CleverReach\WooCommerce\Migrations\V310\Create_Schedules;

/**
 * Class Migration_3_1_0
 *
 * @package CleverReach\WooCommerce\Migration
 */
class Migration_3_1_0 extends Update_Schema {

	/**
	 * Migration steps
	 *
	 * @var string[]
	 */
	private $migration_steps = array(
		Create_Schedules::CLASS_NAME,
	);

	/**
	 * Run migration
	 */
	public function update() {
		$this->increase_data_column_size();
		$this->create_new_table();

		$migrator = new Migrator( $this->migration_steps, $this->db );
		$migrator->migrate();
	}

	/**
	 * Create new table
	 */
	private function create_new_table() {
		$query = 'CREATE TABLE IF NOT EXISTS `'
				 . Base_Repository::get_table_name( 'cleverreach_wc_automation' ) . '` (
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
            `data` TEXT,
            PRIMARY KEY (`id`)
        ) DEFAULT CHARACTER SET utf8';

		$this->db->query( $query );
	}

	/**
	 * Sets data column of entity table to longtext
	 */
	private function increase_data_column_size() {
		$entity_table = Base_Repository::get_table_name( 'cleverreach_wc_entity' );
		$query = "ALTER TABLE $entity_table MODIFY data LONGTEXT";

		$this->db->query( $query );
	}
}
