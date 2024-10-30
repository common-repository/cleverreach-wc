<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Components\Repositories;

use CleverReach\WooCommerce\Components\Util\Database;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Entity;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Utility\IndexHelper;

/**
 * Class Data_Resources_Entity_Repository
 *
 * @package CleverReach\WooCommerce\Components\Repositories
 */
class Data_Resources_Entity_Repository extends Base_Repository {

	/**
	 * Class Data_Resources_Entity_Repository constructor.
	 */
	public function __construct() {
		parent::__construct();
		$this->table_name = $this->db->prefix . Database::DATA_RESOURCES_ENTITY_TABLE;
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
			'data'    => wp_json_encode( $entity->toArray() ),
		);

		foreach ( $indexes as $index => $value ) {
			$storage_item[ 'index_' . $index ] = $value;
		}

		return $storage_item;
	}
}
