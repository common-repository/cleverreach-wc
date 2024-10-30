<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Migrations\V333;

use CleverReach\WooCommerce\Components\Util\Step;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Logger\Logger;
use CleverReach\WooCommerce\Components\Repositories\Base_Repository;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\RepositoryRegistry;
use Exception;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Configuration\ConfigEntity;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Form\Entities\Form;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\SyncSettings\Entities\EnabledServicesChangeLog;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Stats\Entity\Stats;

/**
 * Class Create_Data_Resources_Entity_Table
 *
 * @package CleverReach\WooCommerce\Migrations\V333
 */
class Migrate_Data_Resources_Entities extends Step {

	const CLASS_NAME = __CLASS__;

	/**
	 * Entities for migration.
	 *
	 * @var string[]
	 */
	protected static $data_resources_entities = array(
		ConfigEntity::CLASS_NAME,
		Form::CLASS_NAME,
		EnabledServicesChangeLog::CLASS_NAME,
		Stats::CLASS_NAME,
	);

	/**
	 * Execute.
	 *
	 * @inheritDoc
	 * @return void
	 */
	public function execute() {
		try {
			foreach ( self::$data_resources_entities as $data_resources_entity ) {
				$this->migrateEntityFromBaseToResourcesEntityTable( $data_resources_entity );
			}
		} catch ( Exception $exception ) {
			Logger::logError( 'Failed to create data resources entity table.' );
		}
	}

	/**
	 * Migrates given entity from base entity table to data resources entity table.
	 *
	 * @param string $entity_name that should be migrated from base entity table to data resources entity table.
	 *
	 * @return void
	 * @throws QueryFilterInvalidParamException If filter condition is invalid.
	 * @throws RepositoryNotRegisteredException If repository is not registered.
	 */
	private function migrateEntityFromBaseToResourcesEntityTable( string $entity_name ) {
		$base_repository = new Base_Repository();
		$base_repository->setEntityClass( $entity_name );
		$array_of_entities         = $base_repository->select();
		$resources_data_repository = RepositoryRegistry::getRepository( $entity_name );

		foreach ( $array_of_entities as $entity ) {
			$id = $entity->getId();
			$entity->setId( 0 );
			$resources_data_repository->save( $entity );
			$entity->setId( $id );
			$base_repository->delete( $entity );
		}
	}
}
