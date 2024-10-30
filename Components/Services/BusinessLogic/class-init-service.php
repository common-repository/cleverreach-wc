<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Components\Services\BusinessLogic;

use CleverReach\WooCommerce\Components\InitialSync\Listeners\Initial_Sync_Completed_Listener;
use CleverReach\WooCommerce\Components\Repositories\Archive_Repository;
use Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController;
use CleverReach\WooCommerce\Components\Repositories\Automation_Repository;
use CleverReach\WooCommerce\Components\Repositories\Buffer_Config_Repository;
use CleverReach\WooCommerce\Components\Repositories\Data_Resources_Entity_Repository;
use CleverReach\WooCommerce\Components\Repositories\Base_Repository;
use CleverReach\WooCommerce\Components\Repositories\Customers\Buyer_Repository;
use CleverReach\WooCommerce\Components\Repositories\Customers\Contact_Repository;
use CleverReach\WooCommerce\Components\Repositories\Customers\Contracts\Buyer_Repository_Interface;
use CleverReach\WooCommerce\Components\Repositories\Customers\Contracts\Contact_Repository_Interface;
use CleverReach\WooCommerce\Components\Repositories\Customers\Contracts\Subscriber_Repository_Interface;
use CleverReach\WooCommerce\Components\Repositories\Customers\HighPerformance\High_Performance_Buyer_Repository;
use CleverReach\WooCommerce\Components\Repositories\Customers\HighPerformance\High_Performance_Contact_Repository;
use CleverReach\WooCommerce\Components\Repositories\Customers\HighPerformance\High_Performance_Subscriber_Repository;
use CleverReach\WooCommerce\Components\Repositories\Customers\Subscriber_Repository;
use CleverReach\WooCommerce\Components\Repositories\Events_Buffer_Repository;
use CleverReach\WooCommerce\Components\Repositories\Orders\Contracts\Order_Repository_Interface;
use CleverReach\WooCommerce\Components\Repositories\Orders\HighPerformance\High_Performance_Order_Repository;
use CleverReach\WooCommerce\Components\Repositories\Orders\Order_Repository;
use CleverReach\WooCommerce\Components\Repositories\Queue_Item_Repository;
use CleverReach\WooCommerce\Components\Repositories\Schedule_Repository;
use CleverReach\WooCommerce\Components\Repositories\Stats_Repository;
use CleverReach\WooCommerce\Components\Services\BusinessLogic\Automation\Entities\Recovery_Record;
use CleverReach\WooCommerce\Components\Services\BusinessLogic\Automation\Multistore\Trigger_Service;
use CleverReach\WooCommerce\Components\Services\BusinessLogic\Automation\Multistore\Webhook_Service;
use CleverReach\WooCommerce\Components\Services\BusinessLogic\Configuration\Config_Service;
use CleverReach\WooCommerce\Components\Services\BusinessLogic\Customers\Buyer_Service;
use CleverReach\WooCommerce\Components\Services\BusinessLogic\Customers\Contact_Service;
use CleverReach\WooCommerce\Components\Services\BusinessLogic\Customers\Subscriber_Service;
use CleverReach\WooCommerce\Components\Services\BusinessLogic\Events\Form_Events_Service;
use CleverReach\WooCommerce\Components\Services\BusinessLogic\Events\Receiver_Events_Service;
use CleverReach\WooCommerce\Components\Services\BusinessLogic\Forms\Form_Service;
use CleverReach\WooCommerce\Components\Services\BusinessLogic\Language\Translation_Service;
use CleverReach\WooCommerce\Components\Services\BusinessLogic\Merger\Buyer_Merger;
use CleverReach\WooCommerce\Components\Services\BusinessLogic\Merger\Subscriber_Merger;
use CleverReach\WooCommerce\Components\Services\BusinessLogic\Notification\Notification_Service;
use CleverReach\WooCommerce\Components\Services\BusinessLogic\Schedule\Contracts\Schedule_Service_Interface;
use CleverReach\WooCommerce\Components\Services\BusinessLogic\Schedule\Schedule_Service;
use CleverReach\WooCommerce\Components\Services\BusinessLogic\Segments\Segment_Service;
use CleverReach\WooCommerce\Components\Services\BusinessLogic\SyncSettings\Buyer_Sync_Service;
use CleverReach\WooCommerce\Components\Services\BusinessLogic\SyncSettings\Contact_Sync_Service;
use CleverReach\WooCommerce\Components\Services\BusinessLogic\SyncSettings\Listeners\Initial_Sync_Task_Enqueuer;
use CleverReach\WooCommerce\Components\Services\BusinessLogic\SyncSettings\Subscriber_Sync_Service;
use CleverReach\WooCommerce\Components\Services\BusinessLogic\SyncSettings\Sync_Settings_Service;
use CleverReach\WooCommerce\Components\Services\BusinessLogic\Uninstall\Contracts\Uninstall_Service_Interface;
use CleverReach\WooCommerce\Components\Services\BusinessLogic\Uninstall\Uninstall_Service;
use CleverReach\WooCommerce\Components\Services\Infrastructure\Logger_Service;
use CleverReach\WooCommerce\Components\User\Offline_Mode_Tick_Handler;
use CleverReach\WooCommerce\Components\WebHooks\Listeners\Group_Deleted_Listener;
use CleverReach\WooCommerce\Components\WebHooks\Listeners\Receiver_Created_Listener;
use CleverReach\WooCommerce\Components\WebHooks\Listeners\Receiver_Subscribed_Listener;
use CleverReach\WooCommerce\Components\WebHooks\Listeners\Receiver_Unsubscribed_Listener;
use CleverReach\WooCommerce\Components\WebHooks\Listeners\Receiver_Updated_Listener;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Contracts\AuthorizationService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Contracts\RegistrationService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\BootstrapComponent;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\DoubleOptIn\Http\Proxy as Doi_Proxy;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\DynamicContent\Contracts\DynamicContentService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\EventsBuffering\Contracts\BufferingEventsHandler;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\EventsBuffering\Entities\EventsBufferEntity;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\EventsBuffering\Handler;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\EventsBuffering\Repositories\BufferConfigurationRepositoryInterface;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\EventsBuffering\TickHandler;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Field\Contracts\FieldService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Form\Contracts\FormService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Form\Entities\Form;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Form\FormEventsService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Group\Contracts\GroupService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Group\Events\GroupDeletedEvent;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Group\Events\GroupEventBus;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\InitialSynchronization\Events\InitialSyncCompletedEvent;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Language\Contracts\TranslationService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Mailing\Contracts\DefaultMailingService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Entities\AutomationRecord;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Entities\CartAutomation;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Interfaces\Required\AutomationWebhooksService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Interfaces\Required\CartAutomationTriggerService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Notification\Contracts\NotificationService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Order\Contracts\OrderService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Events\ReceiverCreatedEvent;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Events\ReceiverEventBus;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Events\ReceiverSubscribedEvent;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Events\ReceiverUnsubscribedEvent;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Events\ReceiverUpdatedEvent;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Merger\MergerRegistry;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\ReceiverEventsService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Scheduler\Models\Schedule;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Scheduler\ScheduleTickHandler;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Segment\Contracts\SegmentService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Stats\Entity\Stats;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\SupportConsole\Contracts\SupportService as Support_Service_Interface;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\SyncSettings\Contracts\SyncSettingsService as Base_Sync_Settings_Service;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\SyncSettings\DTO\SyncSettings;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\SyncSettings\Entities\EnabledServicesChangeLog;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\SyncSettings\Events\EnabledServicesSetEvent;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\SyncSettings\Events\SyncSettingsEventBus;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\TaskExecution\Events\TaskCompletedEventBus;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\TaskExecution\QueueService;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Configuration\ConfigEntity;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Configuration\Configuration;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\AutoConfiguration;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\HttpClient;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Logger\Interfaces\ShopLoggerAdapter;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\RepositoryClassException;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\RepositoryRegistry;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Serializer\Concrete\JsonSerializer;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Serializer\Serializer;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\ArchivedQueueItem;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Process;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\QueueItem;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\QueueService as Base_Queue_Service;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\TaskEvents\TickEvent;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Utility\Events\EventBus;
use InvalidArgumentException;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Group\GroupEventsService;
use CleverReach\WooCommerce\Components\Services\BusinessLogic\Events\Group_Events_Service;

/**
 * Class Init_Service
 *
 * @package CleverReach\CleverReachIntegration\Services\BusinessLogic
 */
class Init_Service {


	/**
	 * Are services registered
	 *
	 * @var bool
	 */
	private static $services_registered = false;

	/**
	 * Initializes services
	 *
	 * @return void
	 */
	public static function init() {
		if ( self::$services_registered ) {
			return;
		}

		self::register_services();
		self::register_customer_repositories();

		try {
			self::register_repositories();
		} catch ( RepositoryClassException $e ) {
			self::register_events();
			BootstrapComponent::init();

			return;
		}

		self::register_events();
		BootstrapComponent::init();
	}

	/**
	 * Registers services
	 *
	 * @return void
	 */
	private static function register_services() {
		BootstrapComponent::initServices();

		try {
			ServiceRegister::registerService(
				Configuration::CLASS_NAME,
				function () {
					return Config_Service::getInstance();
				}
			);

			ServiceRegister::registerService(
				TranslationService::CLASS_NAME,
				function () {
					return new Translation_Service();
				}
			);

			ServiceRegister::registerSErvice(
				FormService::CLASS_NAME,
				function () {
					return new Form_Service();
				}
			);

			ServiceRegister::registerService(
				DefaultMailingService::CLASS_NAME,
				function () {
					return new Mailing_Service();
				}
			);

			ServiceRegister::registerService(
				NotificationService::CLASS_NAME,
				function () {
					return new Notification_Service();
				}
			);

			ServiceRegister::registerService(
				AuthorizationService::CLASS_NAME,
				function () {
					return new Auth_Service();
				}
			);

			ServiceRegister::registerService(
				RegistrationService::CLASS_NAME,
				function () {
					return new Registration_Service();
				}
			);

			ServiceRegister::registerService(
				Schedule_Service_Interface::CLASS_NAME,
				function () {
					return new Schedule_Service();
				}
			);

			ServiceRegister::registerService(
				Uninstall_Service_Interface::CLASS_NAME,
				function () {
					return new Uninstall_Service();
				}
			);

			ServiceRegister::registerService(
				GroupService::CLASS_NAME,
				function () {
					return new Group_Service();
				}
			);

			ServiceRegister::registerService(
				FormEventsService::CLASS_NAME,
				function () {
					return new Form_Events_Service();
				}
			);

			ServiceRegister::registerService(
				ReceiverEventsService::CLASS_NAME,
				function () {
					return new Receiver_Events_Service();
				}
			);

			ServiceRegister::registerService(
				GroupEventsService::CLASS_NAME,
				function () {
					return new Group_Events_Service();
				}
			);

			ServiceRegister::registerService(
				DynamicContentService::CLASS_NAME,
				function () {
					return new Dynamic_Content_Service();
				}
			);

			ServiceRegister::registerService(
				Support_Service_Interface::CLASS_NAME,
				function () {
					return new Support_Service();
				}
			);

			ServiceRegister::registerService(
				ShopLoggerAdapter::CLASS_NAME,
				function () {
					return Logger_Service::getInstance();
				}
			);

			ServiceRegister::registerService(
				Serializer::CLASS_NAME,
				function () {
					return new JsonSerializer();
				}
			);

			ServiceRegister::registerService(
				Base_Queue_Service::CLASS_NAME,
				function () {
					return new QueueService();
				}
			);

			/**
			 * Config service.
			 *
			 * @var Configuration $config_service
			 */
			$config_service = ServiceRegister::getService( Configuration::CLASS_NAME );

			/**
			 * HTTP client.
			 *
			 * @var HttpClient $http_client
			 */
			$http_client = ServiceRegister::getService( HttpClient::CLASS_NAME );

			ServiceRegister::registerService(
				AutoConfiguration::CLASS_NAME,
				function () use ( $config_service, $http_client ) {
					return new AutoConfiguration(
						$config_service,
						$http_client
					);
				}
			);

			/**
			 * Auth service.
			 *
			 * @var AuthorizationService $auth_service
			 */
			$auth_service = ServiceRegister::getService( AuthorizationService::CLASS_NAME );

			ServiceRegister::registerService(
				Doi_Proxy::CLASS_NAME,
				function () use ( $http_client, $auth_service ) {
					return new Doi_Proxy(
						$http_client,
						$auth_service
					);
				}
			);

			ServiceRegister::registerService(
				Subscriber_Service::THIS_CLASS_NAME,
				function () {
					return new Subscriber_Service();
				}
			);

			ServiceRegister::registerService(
				Contact_Service::THIS_CLASS_NAME,
				function () {
					return new Contact_Service();
				}
			);

			ServiceRegister::registerService(
				Buyer_Service::THIS_CLASS_NAME,
				function () {
					return new Buyer_Service();
				}
			);

			ServiceRegister::registerService(
				FieldService::CLASS_NAME,
				function () {
					return new Receiver_Fields_Service();
				}
			);

			ServiceRegister::registerService(
				OrderService::CLASS_NAME,
				function () {
					return new Order_Service();
				}
			);

			MergerRegistry::register(
				Buyer_Merger::CLASS_NAME,
				function () {
					return Buyer_Merger::getInstance();
				}
			);

			MergerRegistry::register(
				Subscriber_Merger::CLASS_NAME,
				function () {
					return Subscriber_Merger::getInstance();
				}
			);

			ServiceRegister::registerService(
				Base_Sync_Settings_Service::CLASS_NAME,
				function () {
					return new Sync_Settings_Service();
				}
			);

			ServiceRegister::registerService(
				Contact_Sync_Service::CLASS_NAME,
				function () {
					return new Contact_Sync_Service();
				}
			);

			ServiceRegister::registerService(
				Buyer_Sync_Service::CLASS_NAME,
				function () {
					return new Buyer_Sync_Service();
				}
			);

			ServiceRegister::registerService(
				Subscriber_Sync_Service::CLASS_NAME,
				function () {
					return new Subscriber_Sync_Service();
				}
			);

			ServiceRegister::registerService(
				SegmentService::CLASS_NAME,
				function () {
					return new Segment_Service();
				}
			);

			ServiceRegister::registerService(
				DynamicContentService::CLASS_NAME,
				function () {
					return new Dynamic_Content_Service();
				}
			);

			ServiceRegister::registerService(
				AutomationWebhooksService::CLASS_NAME,
				function () {
					return new Webhook_Service();
				}
			);

			ServiceRegister::registerService(
				CartAutomationTriggerService::CLASS_NAME,
				function () {
					return new Trigger_Service();
				}
			);

			ServiceRegister::registerService(
				BufferingEventsHandler::class,
				static function () {
					return new Handler();
				}
			);

			ServiceRegister::registerService(
				BufferConfigurationRepositoryInterface::CLASS_NAME,
				static function () {
					return new Buffer_Config_Repository();
				}
			);

			self::$services_registered = true;
		} catch ( InvalidArgumentException $exception ) {
			// Don't do nothing if service is already register.
			return;
		}
	}

	/**
	 * Register customer repositories based on the High Performance Order Storage feature
	 *
	 * @return void
	 */
	private static function register_customer_repositories() {
		ServiceRegister::registerService(
			Subscriber_Repository_Interface::class,
			function () {
				if ( wc_get_container()->get( CustomOrdersTableController::class )->custom_orders_table_usage_is_enabled() ) {
					return new High_Performance_Subscriber_Repository();
				}

				return new Subscriber_Repository();
			}
		);

		ServiceRegister::registerService(
			Buyer_Repository_Interface::class,
			function () {
				if ( wc_get_container()->get( CustomOrdersTableController::class )->custom_orders_table_usage_is_enabled() ) {
					return new High_Performance_Buyer_Repository();
				}

				return new Buyer_Repository();
			}
		);

		ServiceRegister::registerService(
			Contact_Repository_Interface::class,
			function () {
				if ( wc_get_container()->get( CustomOrdersTableController::class )->custom_orders_table_usage_is_enabled() ) {
					return new High_Performance_Contact_Repository();
				}

				return new Contact_Repository();
			}
		);

		ServiceRegister::registerService(
			Order_Repository_Interface::class,
			function () {
				if ( wc_get_container()->get( CustomOrdersTableController::class )->custom_orders_table_usage_is_enabled() ) {
					return new High_Performance_Order_Repository();
				}

				return new Order_Repository();
			}
		);
	}

	/**
	 * Registers repositories
	 *
	 * @return void
	 *
	 * @throws RepositoryClassException Exception if no repository class.
	 */
	private static function register_repositories() {
		RepositoryRegistry::registerRepository( Schedule::CLASS_NAME, Schedule_Repository::getClassName() );
		RepositoryRegistry::registerRepository( QueueItem::getClassName(), Queue_Item_Repository::getClassName() );
		RepositoryRegistry::registerRepository( Process::getClassName(), Base_Repository::getClassName() );

		RepositoryRegistry::registerRepository(
			ConfigEntity::CLASS_NAME,
			Data_Resources_Entity_Repository::getClassName()
		);
		RepositoryRegistry::registerRepository(
			Form::getClassName(),
			Data_Resources_Entity_Repository::getClassName()
		);
		RepositoryRegistry::registerRepository(
			EnabledServicesChangeLog::getClassName(),
			Data_Resources_Entity_Repository::getClassName()
		);
		RepositoryRegistry::registerRepository( Stats::getClassName(), Stats_Repository::THIS_CLASS_NAME );

		RepositoryRegistry::registerRepository(
			AutomationRecord::getClassName(),
			Automation_Repository::getClassName()
		);
		RepositoryRegistry::registerRepository( CartAutomation::getClassName(), Automation_Repository::getClassName() );
		RepositoryRegistry::registerRepository(
			Recovery_Record::getClassName(),
			Automation_Repository::getClassName()
		);

		RepositoryRegistry::registerRepository( ArchivedQueueItem::getClassName(), Archive_Repository::getClassName() );
		RepositoryRegistry::registerRepository( EventsBufferEntity::CLASS_NAME, Events_Buffer_Repository::getClassName() );
	}

	/**
	 * Registers events
	 *
	 * @return void
	 */
	private static function register_events() {
		TaskCompletedEventBus::getInstance()->when(
			InitialSyncCompletedEvent::CLASS_NAME,
			Initial_Sync_Completed_Listener::CLASS_NAME . '::handle'
		);

		SyncSettingsEventBus::getInstance()->when(
			EnabledServicesSetEvent::CLASS_NAME,
			Initial_Sync_Task_Enqueuer::CLASS_NAME . '::handle'
		);

		GroupEventBus::getInstance()->when(
			GroupDeletedEvent::CLASS_NAME,
			Group_Deleted_Listener::CLASS_NAME . '::handle'
		);

		ReceiverEventBus::getInstance()->when(
			ReceiverCreatedEvent::CLASS_NAME,
			Receiver_Created_Listener::CLASS_NAME . '::handle'
		);

		ReceiverEventBus::getInstance()->when(
			ReceiverUpdatedEvent::CLASS_NAME,
			Receiver_Updated_Listener::CLASS_NAME . '::handle'
		);

		ReceiverEventBus::getInstance()->when(
			ReceiverSubscribedEvent::CLASS_NAME,
			Receiver_Subscribed_Listener::CLASS_NAME . '::handle'
		);

		ReceiverEventBus::getInstance()->when(
			ReceiverUnsubscribedEvent::CLASS_NAME,
			Receiver_Unsubscribed_Listener::CLASS_NAME . '::handle'
		);

		EventBus::getInstance()->when(
			TickEvent::CLASS_NAME,
			Offline_Mode_Tick_Handler::CLASS_NAME . '::handle'
		);

		EventBus::getInstance()->when(
			TickEvent::CLASS_NAME,
			array( new ScheduleTickHandler(), 'handle' )
		);

		EventBus::getInstance()->when(
			TickEvent::CLASS_NAME,
			array( new TickHandler(), 'handle' )
		);
	}
}
