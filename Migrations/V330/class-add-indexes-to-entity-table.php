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
 * Class Add_Indexes_To_Entity_Table
 *
 * @package CleverReach\WooCommerce\Migrations\V330
 */
class Add_Indexes_To_Entity_Table extends Step {

	const CLASS_NAME = __CLASS__;

	/**
	 * Execute.
	 *
	 * @inheritDoc
	 * @return void
	 */
	public function execute() {
		try {
			$this->add_indexes();
		} catch ( Exception $exception ) {
			Logger::logError( 'Failed to create archive table.' );
		}
	}

	/**
	 * Create archive table
	 *
	 * @return void
	 */
	private function add_indexes() {

		$query = 'CREATE INDEX configKey ON `'
				. Base_Repository::get_table_name( 'cleverreach_wc_entity' ) . '` (type, index_1);';
		$this->db->query( $query );

		$query = 'CREATE INDEX latestByType ON `'
				. Base_Repository::get_table_name( 'cleverreach_wc_entity' ) . '` (index_2, index_5);';
		$this->db->query( $query );

		$query = 'CREATE INDEX typeStatus ON `'
				. Base_Repository::get_table_name( 'cleverreach_wc_entity' ) . '` (index_1, index_3, index_8);';
		$this->db->query( $query );

		$query = 'CREATE INDEX equalityHash ON `'
				. Base_Repository::get_table_name( 'cleverreach_wc_entity' ) . '` (index_3, index_9);';
		$this->db->query( $query );
	}
}
