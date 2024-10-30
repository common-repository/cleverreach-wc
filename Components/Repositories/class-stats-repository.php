<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Components\Repositories;

use CleverReach\WooCommerce\Components\Util\Database;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\ORM\Interfaces\ConditionallyDeletes;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Entity;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\QueryFilter\QueryFilter;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Utility\IndexHelper;

/**
 * Class Stats_Repository
 *
 * @package CleverReach\WooCommerce\Components\Repositories
 */
class Stats_Repository extends Data_Resources_Entity_Repository implements ConditionallyDeletes {


	const THIS_CLASS_NAME = __CLASS__;

	/**
	 * Delete under condition
	 *
	 * @param QueryFilter|null $query_filter Query filter.
	 *
	 * @return void
	 *
	 * @throws QueryFilterInvalidParamException Exception if query filter parameters are invalid.
	 */
	public function deleteWhere( QueryFilter $query_filter = null ) {
		$this->table_name = $this->db->prefix . Database::BASE_TABLE;

		/**
		 * Entity object.
		 *
		 * @var Entity $entity
		 */
		$entity = new $this->entity_class();
		$type   = $entity->getConfig()->getType();

		$query = "DELETE FROM {$this->table_name} WHERE type = '" . esc_sql( $type ) . "' ";
		if ( $query_filter ) {
			$query .= $this->apply_query_filter( $query_filter, IndexHelper::mapFieldsToIndexes( $entity ) );
		}

		$this->db->query( $query );
	}
}
