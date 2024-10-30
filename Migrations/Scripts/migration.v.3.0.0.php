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
use CleverReach\WooCommerce\Migrations\V300\Create_Schedules;
use CleverReach\WooCommerce\Migrations\V300\Enqueue_Migration_Initial_Sync_Task;
use CleverReach\WooCommerce\Migrations\V300\Migrate_API_Credentials;
use CleverReach\WooCommerce\Migrations\V300\Migrate_Clever_Reach_Webhooks_Data;
use CleverReach\WooCommerce\Migrations\V300\Migrate_Dynamic_Content_Data;
use CleverReach\WooCommerce\Migrations\V300\Migrate_Group_Id;
use CleverReach\WooCommerce\Migrations\V300\Migrate_User_Config;
use CleverReach\WooCommerce\Migrations\V300\Save_User_Info;
use CleverReach\WooCommerce\Migrations\V300\Set_Default_Sync_Config;

/**
 * Class Migration_3_0_0
 *
 * @package CleverReach\WooCommerce\Migration
 */
class Migration_3_0_0 extends Update_Schema {

	/**
	 * Migration steps
	 *
	 * @var string[]
	 */
	private $migration_steps = array(
		Migrate_API_Credentials::CLASS_NAME,
		Save_User_Info::CLASS_NAME,
		Migrate_Group_Id::CLASS_NAME,
		Migrate_User_Config::CLASS_NAME,
		Migrate_Clever_Reach_Webhooks_Data::CLASS_NAME,
		Migrate_Dynamic_Content_Data::CLASS_NAME,
		Create_Schedules::CLASS_NAME,
		Set_Default_Sync_Config::CLASS_NAME,
		Enqueue_Migration_Initial_Sync_Task::CLASS_NAME,
	);

	/**
	 * Run migration
	 */
	public function update() {
		$this->create_new_table();

		$migrator = new Migrator( $this->migration_steps, $this->db );
		$migrator->migrate();

		$this->drop_old_tables();
	}

	/**
	 * Create new table
	 */
	private function create_new_table() {
		$query = 'CREATE TABLE IF NOT EXISTS `'
				 . Base_Repository::get_table_name( 'cleverreach_wc_entity' ) . '` (
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
	 * Drop old tables
	 */
	private function drop_old_tables() {
		$tables = array(
			'cleverreach_wc_config',
			'cleverreach_wc_process',
			'cleverreach_wc_queue',
		);

		foreach ( $tables as $table ) {
			$query = 'DROP TABLE IF EXISTS ' . Base_Repository::get_table_name( $table );
			$this->db->query( $query );
		}
	}
}
