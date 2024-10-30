<?php

namespace CleverReach\WooCommerce\Migrations\Scripts;

use CleverReach\WooCommerce\Components\Util\Migrator;
use CleverReach\WooCommerce\Migrations\Contracts\Update_Schema;
use CleverReach\WooCommerce\Migrations\V330\Add_Indexes_To_Entity_Table;
use CleverReach\WooCommerce\Migrations\V330\Create_Archive_Table;

class Migration_3_3_0 extends Update_Schema {

	private $migration_steps = array(
		Create_Archive_Table::CLASS_NAME,
		Add_Indexes_To_Entity_Table::CLASS_NAME
	);

	public function update() {
		$migrator = new Migrator( $this->migration_steps, $this->db );
		$migrator->migrate();
	}
}