<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Components\Repositories;

/**
 * Class Role_Repository
 *
 * @package CleverReach\WooCommerce\Components\Repositories
 */
class Role_Repository {


	const ROLES_TABLE = 'user_roles';

	/**
	 * Retrieves all user roles.
	 *
	 * @return string[]
	 */
	public function get_user_roles() {
		$roles  = get_option( Base_Repository::get_table_name( self::ROLES_TABLE ) );
		$result = array();
		foreach ( $roles as $role => $details ) {
			$result[ $role ] = translate_user_role( $details['name'] );
		}

		return $result;
	}
}
