<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Migrations\Scripts;

use CleverReach\WooCommerce\Components\Util\Migrator;
use CleverReach\WooCommerce\Migrations\Contracts\Update_Schema;
use CleverReach\WooCommerce\Migrations\V330\Add_Indexes_To_Entity_Table;
use CleverReach\WooCommerce\Migrations\V330\Create_Archive_Table;

/**
 * Class Migration_3_3_0
 *
 * @package CleverReach\WooCommerce\Migrations\Scripts
 */
class Migration_3_3_0 extends Update_Schema {

	/**
	 * Migration steps.
	 *
	 * @var string[]
	 */
	private $migration_steps = array(
		Create_Archive_Table::CLASS_NAME,
		Add_Indexes_To_Entity_Table::CLASS_NAME,
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
