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
 * Class Events_Buffer_Repository
 *
 * @package CleverReach\WooCommerce\Components\Repositories
 */
class Events_Buffer_Repository extends Base_Repository implements ConditionallyDeletes {

	const THIS_CLASS_NAME = __CLASS__;

	/**
	 * Returns full class name.
	 *
	 * @return string
	 */
	public static function getClassName() {
		return self::THIS_CLASS_NAME;
	}

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct();
		$this->table_name = $this->db->prefix . Database::EVENTS_BUFFER_TABLE;
	}

	/**
	 * Delete where.
	 *
	 * @param QueryFilter|null $query_filter Query filter.
	 *
	 * @throws QueryFilterInvalidParamException Exception if query param is invalid.
	 */
	public function deleteWhere( QueryFilter $query_filter = null ) {
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

	/**
	 * Prepares entity in format for storage.
	 *
	 * @param Entity $entity Entity to be stored.
	 *
	 * @return array<string,mixed> Item prepared for storage.
	 */
	protected function prepare_entity_for_storage( Entity $entity ) {
		$indexes      = IndexHelper::transformFieldsToIndexes( $entity );
		$storage_item = array(
			'type'    => $entity->getConfig()->getType(),
			'index_1' => null,
			'index_2' => null,
			'data'    => wp_json_encode( $entity->toArray() ),
		);

		foreach ( $indexes as $index => $value ) {
			$storage_item[ 'index_' . $index ] = $value;
		}

		return $storage_item;
	}
}
