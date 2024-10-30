<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Migrations\Scripts;

use CleverReach\WooCommerce\Components\Util\Migrator;
use CleverReach\WooCommerce\Migrations\Contracts\Update_Schema;
use CleverReach\WooCommerce\Migrations\V334\Subscribe_To_Group_Webhooks;

/**
 * Class Migration_3_3_4
 *
 * @package CleverReach\WooCommerce\Migrations\Scripts
 */
class Migration_3_3_4 extends Update_Schema {

	/**
	 * Migration steps.
	 *
	 * @var array<string>
	 */
	private $migration_steps = array(
		Subscribe_To_Group_Webhooks::CLASS_NAME,
	);

	/**
	 * Executes migration.
	 *
	 * @return void
	 *
	 * @inheritDoc
	 */
	public function update() {
		$migrator = new Migrator( $this->migration_steps, $this->db );
		$migrator->migrate();
	}
}
