<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Components\Repositories;

use CleverReach\WooCommerce\Components\Util\Database;

/**
 * Class Stats_Repository
 *
 * @package CleverReach\WooCommerce\Components\Repositories
 */
class Automation_Repository extends Base_Repository {


	/**
	 * Class Stats_Repository constructor.
	 */
	public function __construct() {
		parent::__construct();
		$this->table_name = $this->db->prefix . Database::AUTOMATION_TABLE;
	}

	/**
	 * Returns full class name.
	 *
	 * @return string Full class name.
	 */
	public static function getClassName() {
		return __CLASS__;
	}
}
