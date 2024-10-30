<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Components\Util;

use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Logger\Logger;
use CleverReach\WooCommerce\Migrations\Exceptions\Failed_To_Execute_Migration_Step_Exception;
use wpdb;

/**
 * Class Migrator
 *
 * @package CleverReach\WooCommerce\Components\Util
 */
class Migrator {


	/**
	 * Migration steps
	 *
	 * @var string[]
	 */
	private $migration_steps;

	/**
	 * WordPress database
	 *
	 * @var wpdb WordPress database.
	 */
	private $db;

	/**
	 * Migrator constructor.
	 *
	 * @param string[] $migration_steps Migration steps.
	 * @param wpdb     $db database.
	 */
	public function __construct( $migration_steps, $db ) {
		$this->migration_steps = $migration_steps;
		$this->db              = $db;
	}

	/**
	 * Execute migration steps.
	 *
	 * @return void
	 */
	public function migrate() {
		try {
			foreach ( $this->migration_steps as $step ) {
				/**
				 * Executor
				 *
				 * @var Step $executor
				 */
				$executor = new $step( $this->db );
				$executor->execute();
			}
		} catch ( Failed_To_Execute_Migration_Step_Exception $e ) {
			Logger::logError( 'Failed to update plugin because: ' . esc_html( $e->getMessage() ) );

			return;
		}

		Logger::logInfo( 'Successfully executed migration.' );
	}
}
