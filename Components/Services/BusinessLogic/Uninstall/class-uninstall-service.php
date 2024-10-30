<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Components\Services\BusinessLogic\Uninstall;

use CleverReach\WooCommerce\Components\Services\BusinessLogic\Auth_Service;
use CleverReach\WooCommerce\Components\Services\BusinessLogic\Dynamic_Content_Service;
use CleverReach\WooCommerce\Components\Services\BusinessLogic\Events\Form_Events_Service;
use CleverReach\WooCommerce\Components\Services\BusinessLogic\Events\Group_Events_Service;
use CleverReach\WooCommerce\Components\Services\BusinessLogic\Events\Receiver_Events_Service;
use CleverReach\WooCommerce\Components\Services\BusinessLogic\Group_Service;
use CleverReach\WooCommerce\Components\Services\BusinessLogic\Uninstall\Contracts\Uninstall_Service_Interface;
use CleverReach\WooCommerce\Components\Util\Database;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Contracts\AuthorizationService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Http\TokenProxy;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\DynamicContent\Contracts\DynamicContentService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\DynamicContent\Http\Proxy as Dynamic_Content_Proxy;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Form\Entities\Form;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Form\FormEventsService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Group\Contracts\GroupService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Group\GroupEventsService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\ORM\Interfaces\ConditionallyDeletes;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\ReceiverEventsService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Scheduler\Models\Schedule;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\WebHookEvent\Http\Proxy as Webhook_Proxy;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Configuration\ConfigEntity;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Exceptions\BaseException;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Logger\Logger;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Interfaces\QueueItemRepository;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\QueryFilter\Operators;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\QueryFilter\QueryFilter;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\RepositoryRegistry;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Process;

/**
 * Class Uninstall_Service
 *
 * @package CleverReach\WooCommerce\Components\Services\BusinessLogic\Uninstall
 */
class Uninstall_Service implements Uninstall_Service_Interface {


	/**
	 * Removes all plugin data.
	 *
	 * @return void
	 */
	public function remove_data() {
		$this->delete_events();
		$this->delete_dynamic_content();
		$this->revoke_oauth();
		$this->remove_records_from_database();
	}

	/**
	 * Remove data on group deleted webhook event
	 *
	 * @return void
	 */
	public function remove_data_on_group_delete() {
		$this->delete_events();
		$this->delete_dynamic_content();
		$this->revoke_oauth();
		$this->remove_records_on_group_delete();
	}

	/**
	 * Remove records on group deleted webhook event
	 *
	 * @return void
	 * @throws QueryFilterInvalidParamException Exception if query filter is invalid.
	 * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\RepositoryClassException Exception if repository class does not exist.
	 * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException Exception if repository is not registered.
	 */
	private function remove_records_on_group_delete() {
		$query_filter = new QueryFilter();
		$query_filter->where( 'name', Operators::NOT_EQUALS, 'backendOrderEmail' )
					->where( 'name', Operators::NOT_EQUALS, 'taskRunnerStatus' );

		// @phpstan-ignore-next-line
		$this->getConfigRepo()->deleteWhere( $query_filter );

		$queue_item_filter = new QueryFilter();
		$queue_item_filter->where( 'taskType', Operators::NOT_EQUALS, 'Group_Deleted_Handler' );

		// @phpstan-ignore-next-line
		$this->getQueueItemRepo()->deleteWhere( $queue_item_filter );

		// @phpstan-ignore-next-line
		$this->getProcessRepo()->deleteWhere();
		// @phpstan-ignore-next-line
		$this->getScheduleRepo()->deleteWhere();
		// @phpstan-ignore-next-line
		$this->getFormRepo()->deleteWhere();
	}

	/**
	 * Delete Receiver events
	 *
	 * @return void
	 */
	private function delete_events() {
		/**
		 * Group service.
		 *
		 * @var Group_Service $group_service
		 */
		$group_service = ServiceRegister::getService( GroupService::CLASS_NAME );
		$group_id      = $group_service->getId();

		if ( '' === $group_id ) {
			return;
		}

		/**
		 * Form events service.
		 *
		 * @var Form_Events_Service $form_events_service
		 */
		$form_events_service = ServiceRegister::getService( FormEventsService::CLASS_NAME );
		$form_type           = $form_events_service->getType();

		/**
		 * Receiver events service.
		 *
		 * @var Receiver_Events_Service $receiver_events_service
		 */
		$receiver_events_service = ServiceRegister::getService( ReceiverEventsService::CLASS_NAME );
		$receiver_type           = $receiver_events_service->getType();

		/**
		 * Group events service.
		 *
		 * @var Group_Events_Service $group_events_service
		 */
		$group_events_service = ServiceRegister::getService( GroupEventsService::CLASS_NAME );
		$group_events_type    = $group_events_service->getType();

		/**
		 * Webhook proxy.
		 *
		 * @var Webhook_Proxy $webhook_proxy
		 */
		$webhook_proxy = ServiceRegister::getService( Webhook_Proxy::CLASS_NAME );

		try {
			$webhook_proxy->deleteEvent( $group_id, $form_type );
		} catch ( BaseException $e ) {
			Logger::logError( "Unable to delete form event because: {$e->getMessage()}", 'Integration' );
		}

		try {
			$webhook_proxy->deleteEvent( $group_id, $receiver_type );
		} catch ( BaseException $e ) {
			Logger::logError( "Unable to delete receiver event because: {$e->getMessage()}", 'Integration' );
		}

		try {
			$webhook_proxy->deleteEvent( $group_id, $group_events_type );
		} catch ( BaseException $e ) {
			Logger::logError( "Unable to delete group event because: {$e->getMessage()}", 'Integration' );
		}
	}

	/**
	 * Deletes dynamic content
	 *
	 * @return void
	 */
	private function delete_dynamic_content() {
		/**
		 * Dynamic content service.
		 *
		 * @var Dynamic_Content_Service $dynamic_content_service
		 */
		$dynamic_content_service = ServiceRegister::getService( DynamicContentService::CLASS_NAME );

		/**
		 * Dynamic content ids.
		 *
		 * @var string[] $content_ids
		 */
		$content_ids = array();
		try {
			$content_ids = $dynamic_content_service->getCreatedDynamicContentIds();
		} catch ( QueryFilterInvalidParamException $e ) {
			Logger::logError( "Unable to delete dynamic content because: {$e->getMessage()}", 'Integration' );
		}

		/**
		 * Dynamic content proxy
		 *
		 * @var Dynamic_Content_Proxy $dynamic_content_proxy
		 */
		$dynamic_content_proxy = ServiceRegister::getService( Dynamic_Content_Proxy::CLASS_NAME );

		foreach ( $content_ids as $content_id ) {
			try {
				$dynamic_content_proxy->deleteContent( $content_id );
			} catch ( BaseException $e ) {
				Logger::logError( "Unable to delete dynamic content because: {$e->getMessage()}", 'Integration' );
			}
		}
	}

	/**
	 * Removes Oauth tokens from db
	 *
	 * @return void
	 */
	private function revoke_oauth() {
		try {
			/**
			 * Token proxy.
			 *
			 * @var TokenProxy $token_proxy
			 */
			$token_proxy = ServiceRegister::getService( TokenProxy::CLASS_NAME );
			$token_proxy->revoke();

			/**
			 * Auth service
			 *
			 * @var Auth_Service $auth_service
			 */
			$auth_service = ServiceRegister::getService( AuthorizationService::CLASS_NAME );
			$auth_service->setAuthInfo();
			$auth_service->setUserInfo();
			$auth_service->setIsOffline( false );
		} catch ( BaseException $e ) {
			Logger::logError(
				"Failed to revoke access token because: {$e->getMessage()}",
				'Integration'
			);
		}
	}

	/**
	 * Truncates cleverreach_entity table
	 *
	 * @return void
	 */
	private function remove_records_from_database() {
		global $wpdb;
		$database = new Database( $wpdb );
		$database->truncate_tables();
	}

	/**
	 * Retrieves config repository.
	 *
	 * @return \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Interfaces\RepositoryInterface
	 * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException Repository not registered.
	 */
	private function getConfigRepo() {
		return RepositoryRegistry::getRepository( ConfigEntity::getClassName() );
	}

	/**
	 * Retrieves queue item repository.
	 *
	 * @return QueueItemRepository|ConditionallyDeletes
	 * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\RepositoryClassException Repository class exception.
	 * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException Repository not registered.
	 */
	private function getQueueItemRepo() {
		return RepositoryRegistry::getQueueItemRepository();
	}

	/**
	 * Retrieves process repository.
	 *
	 * @return \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Interfaces\RepositoryInterface
	 * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException Repository not registered.
	 */
	private function getProcessRepo() {
		return RepositoryRegistry::getRepository( Process::getClassName() );
	}

	/**
	 * Retrieves schedule repository.
	 *
	 * @return \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Interfaces\RepositoryInterface
	 * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException Repository not registered.
	 */
	private function getScheduleRepo() {
		return RepositoryRegistry::getRepository( Schedule::getClassName() );
	}

	/**
	 * Retrieves form repository.
	 *
	 * @return \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Interfaces\RepositoryInterface
	 * @throws \CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException Repository not registered.
	 */
	private function getFormRepo() {
		return RepositoryRegistry::getRepository( Form::getClassName() );
	}
}
