<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Components\Services\BusinessLogic\Automation;

use CleverReach\WooCommerce\Components\Services\BusinessLogic\Automation\Entities\Recovery_Record;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Interfaces\RepositoryInterface;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\QueryFilter\Operators;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\QueryFilter\QueryFilter;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\RepositoryRegistry;

/**
 * Class Recovery_Record_Service
 *
 * @package CleverReach\WooCommerce\Components\Services\BusinessLogic\Automation
 */
class Recovery_Record_Service {


	/**
	 * Persists recovery record.
	 *
	 * @param Recovery_Record $record Recovery record entity.
	 *
	 * @return Recovery_Record
	 *
	 * @throws RepositoryNotRegisteredException Repository Not Registered Exception.
	 */
	public function create( Recovery_Record $record ) {
		$this->get_repository()->save( $record );

		return $record;
	}

	/**
	 * Provides recovery records identified by condition.
	 *
	 * @param array<string,mixed> $cond Filter conditions.
	 *
	 * @return Recovery_Record[] Recovery_Record.
	 *
	 * @throws RepositoryNotRegisteredException Repository Not Registered Exception.
	 * @throws QueryFilterInvalidParamException Query Filter Invalid Parameter Exception.
	 */
	public function find( array $cond ) {
		if ( empty( $cond ) ) {
			return array();
		}

		$query = new QueryFilter();
		foreach ( $cond as $field => $value ) {
			$query->where( $field, Operators::EQUALS, $value );
		}

		/**
		 * Recovery records.
		 *
		 * @var Recovery_Record[] $recovery_records
		 */
		$recovery_records = $this->get_repository()->select( $query );

		return $recovery_records;
	}

	/**
	 * Finds recovery record by token.
	 *
	 * @param string $token Recovery Record token.
	 *
	 * @return Recovery_Record|null
	 *
	 * @throws QueryFilterInvalidParamException Query Filter Invalid Parameter Exception.
	 * @throws RepositoryNotRegisteredException Repository Not Registered Exception.
	 */
	public function find_by_token( $token ) {
		$records = $this->find( array( 'token' => $token ) );

		return ! empty( $records[0] ) ? $records[0] : null;
	}

	/**
	 * Deletes recovery records identified by the condition.
	 *
	 * @param array<string,mixed> $cond Filter conditions.
	 *
	 * @return void
	 *
	 * @throws QueryFilterInvalidParamException Query Filter Invalid Parameter Exception.
	 * @throws RepositoryNotRegisteredException Repository Not Registered Exception.
	 */
	public function delete_by( $cond ) {
		$repository = $this->get_repository();
		$records    = $this->find( $cond );
		foreach ( $records as $record ) {
			$repository->delete( $record );
		}
	}

	/**
	 * Deletes recovery record.
	 *
	 * @param Recovery_Record $record Recovery record.
	 *
	 * @return void
	 *
	 * @throws RepositoryNotRegisteredException Repository Not Registered Exception.
	 */
	public function delete( Recovery_Record $record ) {
		$this->get_repository()->delete( $record );
	}

	/**
	 * Provides repository for recovery record.
	 *
	 * @return RepositoryInterface
	 *
	 * @throws RepositoryNotRegisteredException Repository Not Registered Exception.
	 */
	protected function get_repository() {
		return RepositoryRegistry::getRepository( Recovery_Record::getClassName() );
	}
}
