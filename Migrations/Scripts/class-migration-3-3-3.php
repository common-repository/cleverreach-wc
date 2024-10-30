<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Migrations\Scripts;

use CleverReach\WooCommerce\Components\Util\Migrator;
use CleverReach\WooCommerce\Migrations\Contracts\Update_Schema;
use CleverReach\WooCommerce\Migrations\V333\Create_Buffer_Config_Table;
use CleverReach\WooCommerce\Migrations\V333\Create_Data_Resources_Entity_Table;
use CleverReach\WooCommerce\Migrations\V333\Create_Events_Buffer_Entity_Table;
use CleverReach\WooCommerce\Migrations\V333\Migrate_Data_Resources_Entities;

/**
 * Class Migration_3_3_3
 *
 * @package CleverReach\WooCommerce\Migrations\Scripts
 */
class Migration_3_3_3 extends Update_Schema {

	/**
	 * Migration steps.
	 *
	 * @var string[]
	 */
	private $migration_steps = array(
		Create_Data_Resources_Entity_Table::CLASS_NAME,
		Migrate_Data_Resources_Entities::CLASS_NAME,
		Create_Buffer_Config_Table::CLASS_NAME,
		Create_Events_Buffer_Entity_Table::CLASS_NAME,
	);

	/**
	 * Update.
	 *
	 * @return void
	 */
	public function update() {
		$migrator = new Migrator( $this->migration_steps, $this->db );
		$migrator->migrate();
	}
}
