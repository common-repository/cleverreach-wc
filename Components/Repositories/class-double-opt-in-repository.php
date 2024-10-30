<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Components\Repositories;

use CleverReach\WooCommerce\Components\Util\Database;
use wpdb;

/**
 * Class Double_Opt_In_Repository
 *
 * @package CleverReach\WooCommerce\Components\Repositories
 */
class Double_Opt_In_Repository {


	/**
	 * Database session object.
	 *
	 * @var wpdb
	 */
	private $db;

	/**
	 * Task name.
	 *
	 * @var string
	 */
	const TASK_NAME = 'SendDoubleOptInEmailsTask';

	/**
	 * Construct.
	 *
	 * @return void
	 */
	public function __construct() {
		global $wpdb;
		$this->db = $wpdb;
	}

	/**
	 * Checks if double opt in task exists for given email.
	 *
	 * @param string $email Email for search.
	 *
	 * @return bool
	 */
	public function does_doi_task_exist_for_email( $email ) {
		$table_name = $this->db->prefix . Database::BASE_TABLE;

		$sql = "select count(id) 
				from {$table_name} 
				where index_2 = '" . self::TASK_NAME . "' and data like '%" . esc_sql( $email ) . "%'";

		$result = (int) $this->db->get_var( $sql );

		return $result > 0;
	}
}
