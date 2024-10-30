<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Components\Util;

use CleverReach\WooCommerce\Migrations\Exceptions\Failed_To_Execute_Migration_Step_Exception;
use wpdb;

/**
 * Class Step
 *
 * @package CleverReach\WooCommerce\Components\Util
 */
abstract class Step {


	/**
	 * WordPress database
	 *
	 * @var wpdb
	 */
	protected $db;

	/**
	 * Step constructor.
	 *
	 * @param wpdb $db WordPress database.
	 */
	public function __construct( $db ) {
		$this->db = $db;
	}


	/**
	 * Executes migration step.
	 *
	 * @return void
	 *
	 * @throws Failed_To_Execute_Migration_Step_Exception Failed to execute migration step exception.
	 */
	abstract public function execute();
}
