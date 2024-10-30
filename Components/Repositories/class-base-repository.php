<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Components\Repositories;

use CleverReach\WooCommerce\Components\Util\Database;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Entity;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Interfaces\RepositoryInterface;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\QueryFilter\QueryCondition;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\QueryFilter\QueryFilter;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Utility\IndexHelper;
use InvalidArgumentException;
use wpdb;

/**
 * Class Base_Repository
 *
 * @package CleverReach\WooCommerce\Components\Repositories
 */
class Base_Repository implements RepositoryInterface {

	/**
	 * Entity class FQN.
	 *
	 * @var string
	 */
	protected $entity_class;
	/**
	 * Database session object.
	 *
	 * @var wpdb
	 */
	protected $db;
	/**
	 * Table name.
	 *
	 * @var string
	 */
	protected $table_name;

	/**
	 * Base_Repository constructor.
	 */
	public function __construct() {
		global $wpdb;
		$this->db         = $wpdb;
		$this->table_name = $this->db->prefix . Database::BASE_TABLE;
	}

	/**
	 * Returns full class name.
	 *
	 * @return string Full class name.
	 */
	public static function getClassName() {
		return __CLASS__;
	}

	/**
	 * Sets repository entity
	 *
	 * @param string $entity_class Entity class.
	 *
	 * @return void
	 */
	public function setEntityClass( $entity_class ) {
		$this->entity_class = $entity_class;
	}

	/**
	 * Executes select query.
	 *
	 * @param QueryFilter $filter Filter for query.
	 *
	 * @return Entity[] A list of found entities ot empty array.
	 * @throws QueryFilterInvalidParamException If filter condition is invalid.
	 */
	public function select( QueryFilter $filter = null ) {
		/**
		 * Entity object.
		 *
		 * @var Entity $entity
		 */
		$entity = new $this->entity_class();
		$type   = $entity->getConfig()->getType();

		$query = "SELECT * FROM {$this->table_name} WHERE type = '" . esc_sql( $type ) . "' ";
		if ( $filter ) {
			$query .= $this->apply_query_filter( $filter, IndexHelper::mapFieldsToIndexes( $entity ) );
		}

		$raw_results = $this->db->get_results( $query, ARRAY_A );

		return $this->translateToEntities( $raw_results );
	}

	/**
	 * Executes select query and returns first result.
	 *
	 * @param QueryFilter $filter Filter for query.
	 *
	 * @return Entity|null First found entity or NULL.
	 * @throws QueryFilterInvalidParamException If filter condition is invalid.
	 */
	public function selectOne( QueryFilter $filter = null ) {
		if ( ! $filter ) {
			$filter = new QueryFilter();
		}

		$filter->setLimit( 1 );
		$results = $this->select( $filter );

		return ! empty( $results ) ? $results[0] : null;
	}

	/**
	 * Executes insert query and returns ID of created entity. Entity will be updated with new ID.
	 *
	 * @param Entity $entity Entity to be saved.
	 *
	 * @return int Identifier of saved entity.
	 */
	public function save( Entity $entity ) {
		if ( $entity->getId() ) {
			$this->update( $entity );

			return $entity->getId();
		}

		return $this->save_entity_to_storage( $entity );
	}

	/**
	 * Executes update query and returns success flag.
	 *
	 * @param Entity $entity Entity to be updated.
	 *
	 * @return bool TRUE if operation succeeded; otherwise, FALSE.
	 */
	public function update( Entity $entity ) {
		$item = $this->prepare_entity_for_storage( $entity );

		// Only one record should be updated.
		return 1 === $this->db->update( $this->table_name, $item, array( 'id' => $entity->getId() ) );
	}

	/**
	 * Executes delete query and returns success flag.
	 *
	 * @param Entity $entity Entity to be deleted.
	 *
	 * @return bool TRUE if operation succeeded; otherwise, FALSE.
	 */
	public function delete( Entity $entity ) {
		return false !== $this->db->delete( $this->table_name, array( 'id' => $entity->getId() ) );
	}

	/**
	 * Delete where.
	 *
	 * @param QueryFilter|null $query_filter Query filter.
	 *
	 * @return void
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
	 * Counts records that match filter criteria.
	 *
	 * @param QueryFilter $filter Filter for query.
	 *
	 * @return int Number of records that match filter criteria.
	 * @throws QueryFilterInvalidParamException If filter condition is invalid.
	 */
	public function count( QueryFilter $filter = null ) {
		/**
		 * Entity object.
		 *
		 * @var Entity $entity
		 */
		$entity = new $this->entity_class();
		$type   = $entity->getConfig()->getType();

		$query = "SELECT COUNT(*) as `total` FROM {$this->table_name} WHERE type = '" . esc_sql( $type ) . "' ";
		if ( $filter ) {
			$query .= $this->apply_query_filter( $filter, IndexHelper::mapFieldsToIndexes( $entity ) );
		}

		$result = $this->db->get_results( $query, ARRAY_A );

		return empty( $result ) ? 0 : (int) $result[0]['total'];
	}

	/**
	 * Escapes provided value.
	 *
	 * @param mixed $value Value to be escaped.
	 *
	 * @return string Escaped value.
	 */
	protected function escape( $value ) {
		return addslashes( $value );
	}

	/**
	 * Checks if value exists and escapes it if it's not.
	 *
	 * @param mixed $value Value to be escaped.
	 *
	 * @return string Escaped value.
	 */
	protected function escape_value( $value ) {
		return null === $value ? 'NULL' : "'" . $this->escape( $value ) . "'";
	}

	/**
	 * Converts filter value to index string representation.
	 *
	 * @param QueryCondition $condition Query condition.
	 *
	 * @return mixed[]|string|null Converted value.
	 */
	protected function convert_value( QueryCondition $condition ) {
		$value = IndexHelper::castFieldValue( $condition->getValue(), $condition->getValueType() );
		switch ( $condition->getValueType() ) {
			case 'integer':
			case 'dateTime':
			case 'boolean':
			case 'double':
				$value = $this->escape_value( $value );
				break;
			case 'string':
				$value = $this->escape_value( $condition->getValue() );
				break;
			case 'array':
				$values         = $condition->getValue();
				$escaped_values = array();
				foreach ( $values as $value ) {
					$escaped_values[] = is_string( $value ) ? $this->escape_value( $value ) : $value;
				}

				$value = '(' . implode( ', ', $escaped_values ) . ')';
				break;
		}

		return $value;
	}

	/**
	 * Builds query filter part of the query.
	 *
	 * @param QueryFilter $filter Query filter object.
	 * @param mixed[]     $field_index_map Property to index number map.
	 *
	 * @return string Query filter addendum.
	 * @throws QueryFilterInvalidParamException If filter condition is invalid.
	 */
	protected function apply_query_filter( QueryFilter $filter, array $field_index_map = array() ) {
		$query      = '';
		$conditions = $filter->getConditions();
		if ( ! empty( $conditions ) ) {
			$query .= ' AND (';
			$first  = true;
			foreach ( $conditions as $condition ) {
				$this->validate_index_column( $condition->getColumn(), $field_index_map );
				$chain_op = $first ? '' : $condition->getChainOperator();
				$first    = false;
				$column   = 'id' === $condition->getColumn() ? 'id' : 'index_' . $field_index_map[ $condition->getColumn() ];
				$operator = $condition->getOperator();
				/**
				 * Converted value.
				 *
				 * @var string|null $converted
				 */
				$converted = $this->convert_value( $condition );
				$query    .= " $chain_op $column $operator " . $converted;
			}

			$query .= ')';
		}

		if ( $filter->getOrderByColumn() ) {
			$this->validate_index_column( $filter->getOrderByColumn(), $field_index_map );
			$order_index = 'id' === $filter->getOrderByColumn() ? 'id' : 'index_' . $field_index_map[ $filter->getOrderByColumn() ];
			$query      .= " ORDER BY {$order_index} {$filter->getOrderDirection()}";
		}

		if ( $filter->getLimit() ) {
			$offset = (int) $filter->getOffset();

			if ( $offset ) {
				$query .= " LIMIT {$offset}, {$filter->getLimit()}";
			} else {
				$query .= " LIMIT {$filter->getLimit()}";
			}
		}

		return $query;
	}

	/**
	 * Transforms raw database query rows to entities.
	 *
	 * @param mixed[] $result Raw database query result.
	 *
	 * @return Entity[] Array of transformed entities.
	 */
	protected function translateToEntities( array $result ) {
		/**
		 * Array of decoded entities.
		 *
		 * @var Entity[] $entities
		 */
		$entities = array();
		foreach ( $result as $item ) {
			$data = json_decode( $item['data'], true );

			/**
			 * Entity object.
			 *
			 * @var Entity $entity
			 */
			$entity = isset( $data['class_name'] ) ? new $data['class_name']() : new $this->entity_class();
			$entity->inflate( $data );
			$entity->setId( $item['id'] );

			$entities[] = $entity;
		}

		return $entities;
	}

	/**
	 * Saves entity to system storage.
	 *
	 * @param Entity $entity Entity to be stored.
	 *
	 * @return int Inserted entity identifier.
	 */
	protected function save_entity_to_storage( Entity $entity ) {
		$storage_item = $this->prepare_entity_for_storage( $entity );

		$this->db->insert( $this->table_name, $storage_item );

		$insert_id = (int) $this->db->insert_id;
		$entity->setId( $insert_id );

		return $insert_id;
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
			'index_3' => null,
			'index_4' => null,
			'index_5' => null,
			'index_6' => null,
			'index_7' => null,
			'data'    => wp_json_encode( $entity->toArray() ),
		);

		foreach ( $indexes as $index => $value ) {
			$storage_item[ 'index_' . $index ] = $value;
		}

		return $storage_item;
	}

	/**
	 * Validates if column can be filtered or sorted by.
	 *
	 * @param string  $column Column name.
	 * @param mixed[] $index_map Index map.
	 *
	 * @return void
	 *
	 * @throws QueryFilterInvalidParamException If filter condition is invalid.
	 */
	protected function validate_index_column( $column, array $index_map ) {
		if ( 'id' !== $column && ! array_key_exists( $column, $index_map ) ) {
			throw new QueryFilterInvalidParamException(
				esc_html(
					__(
						'Column is not id or index.',
						'cleverreach-pro-shipping'
					)
				)
			);
		}
	}

	/**
	 * Builds query filter from conditions array.
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 *
	 * @param array<string,mixed> $conditions Array of conditions.
	 *
	 * @return QueryFilter Query filter object.
	 */
	protected function build_query_filter( array $conditions ) {
		$filter = new QueryFilter();
		$filter->setOffset( 0 );
		$filter->setLimit( 1 );
		foreach ( $conditions as $column => $value ) {
			if ( null === $value ) {
				$filter->where( $column, 'IS NULL' );
			} else {
				$filter->where( $column, '=', $value );
			}
		}

		return $filter;
	}

	/**
	 * Executes select query for update.
	 *
	 * @param array<string,mixed> $conditions Array of update conditions.
	 *
	 * @return Entity|null First found entity or NULL.
	 * @throws QueryFilterInvalidParamException If filter condition is invalid.
	 */
	protected function select_for_update( array $conditions ) {
		/**
		 * Entity object.
		 *
		 * @var Entity $entity
		 */
		$entity          = new $this->entity_class();
		$type            = $entity->getConfig()->getType();
		$field_index_map = IndexHelper::mapFieldsToIndexes( $entity );

		$filter = $this->build_query_filter( $conditions );

		$query  = "SELECT * FROM {$this->table_name} WHERE type = '" . esc_sql( $type ) . "' ";
		$query .= $this->apply_query_filter( $filter, $field_index_map );
		$query .= ' FOR UPDATE';

		$raw_results = $this->db->get_results( $query, ARRAY_A );

		$entities = $this->translateToEntities( $raw_results );

		return ! empty( $entities ) ? $entities[0] : null;
	}

	/**
	 * Updates single record.
	 *
	 * @param Entity              $item Queue item.
	 * @param array<string,mixed> $conditions List of simple search filters as key-value pair to find records to update.
	 *
	 * @return bool TRUE if operation succeeded; otherwise, FALSE.
	 *
	 * @throws InvalidArgumentException Invalid argument.
	 */
	protected function update_with_condition( Entity $item, array $conditions ) {
		$field_index_map = IndexHelper::mapFieldsToIndexes( $item );
		$prepared        = $this->prepare_entity_for_storage( $item );

		$indexed_conditions = array();
		foreach ( $conditions as $key => $value ) {
			if ( 'id' === $key ) {
				$indexed_conditions[ $key ] = (int) $value;
			} else {
				$indexed_conditions[ 'index_' . $field_index_map[ $key ] ] = IndexHelper::castFieldValue(
					$value,
					gettype( $value )
				);
			}
		}

		$result = $this->db->update( $this->table_name, $prepared, $indexed_conditions );

		return ( false !== $result ) && ( 1 >= (int) $result );
	}

	/**
	 * Returns full table name, with database prefix.
	 *
	 * @param string $table_name Table name.
	 *
	 * @return string
	 */
	public static function get_table_name( $table_name ) {
		global $wpdb;

		return $wpdb->prefix . $table_name;
	}
}
