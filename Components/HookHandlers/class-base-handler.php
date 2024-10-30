<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Components\HookHandlers;

use CleverReach\WooCommerce\Components\Repositories\Double_Opt_In_Repository;
use CleverReach\WooCommerce\Components\Services\BusinessLogic\Automation\Cart_Service;
use CleverReach\WooCommerce\Components\Services\BusinessLogic\Automation\Site_Automation_Service;
use CleverReach\WooCommerce\Components\Services\BusinessLogic\Configuration\Config_Service;
use CleverReach\WooCommerce\Components\Services\BusinessLogic\Customers\Subscriber_Service;
use CleverReach\WooCommerce\Components\Services\BusinessLogic\DOI\Double_Opt_In_Service;
use CleverReach\WooCommerce\Components\Services\BusinessLogic\Tag\Tag_Service;
use CleverReach\WooCommerce\Components\Util\Shop_Helper;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRefreshAccessToken;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRetrieveAuthInfoException;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\DoubleOptIn\DTO\DoubleOptInEmail;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\DoubleOptIn\Tasks\SendDoubleOptInEmailsTask;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\EventsBuffering\Contracts\BufferingEventsHandler;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\EventsBuffering\Events\Event;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\EventsBuffering\Handler;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Group\Contracts\GroupService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Contracts\RecoveryEmailStatus;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Entities\AutomationRecord;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Entities\CartAutomation;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Exceptions\FailedToCreateAutomationRecordException;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Exceptions\FailedToDeleteAutomationRecordException;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Exceptions\FailedToUpdateAutomationRecordException;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Multistore\AbandonedCart\Interfaces\AutomationRecordService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Receiver;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Tag\Tag;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Http\Proxy;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\TaskExecution\QueueService;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Configuration\Configuration;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpCommunicationException;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpRequestException;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Logger\LogContextData;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Logger\Logger;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Serializer\Serializer;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\QueueItem;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\QueueService as Base_Queue_Service;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Task;
use DateTime;
use Exception;

/**
 * Class Base_Handler
 *
 * @package CleverReach\WooCommerce\Components\HookHandlers
 */
abstract class Base_Handler {


	/**
	 * Tag Service
	 *
	 * @var Tag_Service
	 */
	private $tag_service;

	/**
	 * Config Service
	 *
	 * @var Config_Service
	 */
	private $config_service;

	/**
	 * Subscriber Service
	 *
	 * @var Subscriber_Service
	 */
	private $receiver_service;

	/**
	 * Double Opt In service
	 *
	 * @var Double_Opt_In_Service
	 */
	private $doi_service;

	/**
	 * Automation record service
	 *
	 * @var AutomationRecordService
	 */
	private $automation_record_service;

	/**
	 * Cart service
	 *
	 * @var Cart_Service
	 */
	private $cart_service;

	/**
	 * Handler
	 *
	 * @var Handler
	 */
	private $buffer_events_handler;

	/**
	 * Check whether event should be handled or not.
	 *
	 * @return bool
	 */
	protected function should_handle_event() {
		return Shop_Helper::is_plugin_enabled()
				&& $this->is_initial_sync_task_completed()
				&& Shop_Helper::is_woocommerce_active();
	}

	/**
	 * Enqueue task
	 *
	 * @param Task $task Task.
	 *
	 * @return void
	 */
	protected function enqueue_task( Task $task ) {
		if ( ! $this->is_initial_sync_task_completed() ) {
			return;
		}

		try {
			/**
			 *  Queue service
			 *
			 * @var QueueService $queue_service
			 */
			$queue_service = ServiceRegister::getService( Base_Queue_Service::CLASS_NAME );
			$queue_service->enqueue( $this->get_config_service()->getDefaultQueueName(), $task );
		} catch ( QueueStorageUnavailableException $ex ) {
			$message = json_encode(
				array(
					'Message'          => 'Failed to enqueue task ' . $task->getType(),
					'ExceptionMessage' => $ex->getMessage(),
					'ExceptionTrace'   => $ex->getTraceAsString(),
					'ShopData'         => Serializer::serialize( $task ),
				)
			);

			if ( ! $message ) {
				$message = 'Failed to enqueue task ' . $task->getType();
			}

			Logger::logDebug( $message, 'Integration' );
		}
	}

	/**
	 * Sends confirmation email
	 *
	 * @param string $email Email.
	 * @param Tag[]  $tags_for_delete Tags for delete.
	 *
	 * @return void
	 */
	protected function send_confirmation_email( $email, $tags_for_delete = array() ) {
		$doi_repository = new Double_Opt_In_Repository();

		if ( ! $doi_repository->does_doi_task_exist_for_email( $email ) ) {
			$form_id = $this->get_config_service()->get_default_form();

			$doi_email = new DoubleOptInEmail(
			// @phpstan-ignore-next-line
				$form_id,
				'activate',
				$email,
				$this->get_doi_service()->create_doi_data()
			);

			$this->get_events_buffer_handler()->handle(
				Event::subscriberUpdated( $email, $tags_for_delete )
			);

			$this->enqueue_task( new SendDoubleOptInEmailsTask( array( $doi_email ) ) );
		}
	}

	/**
	 * Sets wp_cr_newsletter_status to 1
	 *
	 * @param Receiver $subscriber Subscriber.
	 * @param Tag[]    $tags_for_delete Tags for delete.
	 *
	 * @return void
	 */
	protected function activate_subscriber( $subscriber, $tags_for_delete = array() ) {
		try {
			$subscriber->setActive( true );
			$this->get_subscriber_service()->update_subscriber( $subscriber );

			$this->get_events_buffer_handler()->handle(
				Event::subscriberUpdated( $subscriber->getEmail(), $tags_for_delete )
			);
			$this->get_events_buffer_handler()->handle(
				Event::subscriberSubscribed( $subscriber->getEmail() )
			);
		} catch ( Exception $e ) {
			Logger::logError(
				'Failed to get subscriber by email or update subscriber.',
				'Integration',
				array(
					new LogContextData( 'message', $e->getMessage() ),
					new LogContextData( 'trace', $e->getTraceAsString() ),
				)
			);
		}
	}

	/**
	 * Sets wp_cr_newsletter_status to 0
	 *
	 * @param Receiver $subscriber Subscriber.
	 * @param Tag[]    $tags_for_delete Tags for delete.
	 *
	 * @return void
	 */
	protected function deactivate_subscriber( $subscriber, $tags_for_delete = array() ) {
		try {
			$subscriber->setActive( false );
			$this->get_subscriber_service()->update_subscriber( $subscriber );

			if ( $this->is_recipient_subscriber( $subscriber->getEmail() ) ) {
				$this->get_events_buffer_handler()->handle(
					Event::subscriberUpdated( $subscriber->getEmail(), $tags_for_delete )
				);
				$this->get_events_buffer_handler()->handle(
					Event::subscriberUnsubscribed( $subscriber->getEmail() )
				);
			}
		} catch ( Exception $e ) {
			Logger::logError(
				'Failed to get subscriber by email or update subscriber.',
				'Integration',
				array(
					new LogContextData( 'message', $e->getMessage() ),
					new LogContextData( 'trace', $e->getTraceAsString() ),
				)
			);
		}
	}

	/**
	 * Retrieves config service
	 *
	 * @return Config_Service
	 */
	protected function get_config_service() {
		if ( null === $this->config_service ) {
			/**
			 * Config service.
			 *
			 * @var Config_Service $config_service
			 */
			$config_service       = ServiceRegister::getService( Configuration::CLASS_NAME );
			$this->config_service = $config_service;
		}

		return $this->config_service;
	}

	/**
	 * Retrieves config service
	 *
	 * @return Subscriber_Service
	 */
	protected function get_subscriber_service() {
		if ( null === $this->receiver_service ) {
			/**
			 * Subscriber service.
			 *
			 * @var Subscriber_Service $receiver_service
			 */
			$receiver_service       = ServiceRegister::getService( Subscriber_Service::THIS_CLASS_NAME );
			$this->receiver_service = $receiver_service;
		}

		return $this->receiver_service;
	}

	/**
	 * Retrieves Double Opt In Service
	 *
	 * @return Double_Opt_In_Service
	 */
	protected function get_doi_service() {
		if ( null === $this->doi_service ) {
			$this->doi_service = new Double_Opt_In_Service();
		}

		return $this->doi_service;
	}

	/**
	 * Retrieves Tag Service
	 *
	 * @return Tag_Service
	 */
	protected function get_tag_service() {
		if ( ! $this->tag_service ) {
			$this->tag_service = new Tag_Service();
		}

		return $this->tag_service;
	}

	/**
	 * Delete automation record
	 *
	 * @param string $cart_id Cart id (session_key).
	 *
	 * @return void
	 */
	protected function delete_automation_record( $cart_id ) {
		if ( empty( $cart_id ) ) {
			return;
		}

		try {
			$this->get_automation_record_service()
				->deleteBy(
					array(
						'cartId' => (string) $cart_id,
						'status' => RecoveryEmailStatus::PENDING,
					)
				);
		} catch ( FailedToDeleteAutomationRecordException $e ) {
			Logger::logError(
				'Failed to delete cart record.',
				'Integration',
				array(
					new LogContextData( 'message', $e->getMessage() ),
					new LogContextData( 'trace', $e->getTraceAsString() ),
					new LogContextData( 'cartId', $cart_id ),
				)
			);
		}
	}

	/**
	 * Create or update automation record
	 *
	 * @param mixed[]    $cart_items Array of cart items.
	 * @param float      $total Cart total.
	 * @param string     $email Customer email.
	 * @param string|int $cart_id Cart ID.
	 * @param bool       $refresh_schedule If AC schedule should be refreshed.
	 *
	 * @return void
	 */
	protected function create_or_update_automation_record( $cart_items, $total, $email, $cart_id, $refresh_schedule = false ) {
		if ( ( empty( $email ) && ! $refresh_schedule ) || empty( $cart_items ) ) {
			return;
		}

		$automation_service = new Site_Automation_Service();
		$automation         = $automation_service->get();
		if ( null === $automation || ! $automation->isActive() || 'created' !== $automation->getStatus() ) {
			return;
		}

		$record = $this->get_automation_record_service()
						->findBy(
							array(
								'cartId' => (string) $cart_id,
								'status' => RecoveryEmailStatus::PENDING,
							)
						);

		if ( 0 < count( $record ) ) {
			try {
				$record = $record[0];
				if ( ! empty( $email ) && $email !== $record->getEmail() ) {
					$record->setEmail( $email );
				}

				if ( $refresh_schedule ) {
					$record->setScheduledTime( new DateTime() );
					$record->setAmount( $total );
					$this->automation_record_service->refreshScheduleTime( $record );
				}

				$this->automation_record_service->update( $record );
			} catch ( FailedToUpdateAutomationRecordException $e ) {
				Logger::logError(
					'Failed to refresh schedule time on automation record.',
					'Integration',
					array(
						new LogContextData( 'message', $e->getMessage() ),
						new LogContextData( 'trace', $e->getTraceAsString() ),
					)
				);
			}
		} else {
			if ( empty( $email ) ) {
				return;
			}

			$this->create_record( $automation, $email, $cart_id, $total );
		}
	}

	/**
	 * Retrieves AutomationRecordService.
	 *
	 * @return AutomationRecordService
	 */
	protected function get_automation_record_service() {
		if ( null === $this->automation_record_service ) {
			/**
			 * Automation record service.
			 *
			 * @var AutomationRecordService $automation_record_service
			 */
			$automation_record_service       = ServiceRegister::getService( AutomationRecordService::CLASS_NAME );
			$this->automation_record_service = $automation_record_service;
		}

		return $this->automation_record_service;
	}

	/**
	 * Returns group service instance.
	 *
	 * @return GroupService
	 */
	protected function get_group_service() {
		/**
		 * Group service.
		 *
		 * @var GroupService $group_service
		 */
		$group_service = ServiceRegister::getService( GroupService::CLASS_NAME );

		return $group_service;
	}

	/**
	 * Return cart service instance.
	 *
	 * @return Cart_Service
	 */
	protected function get_cart_service() {
		if ( null === $this->cart_service ) {
			$this->cart_service = new Cart_Service();
		}

		return $this->cart_service;
	}

	/**
	 * Retrieves buffering events handler.
	 *
	 * @return Handler
	 */
	protected function get_events_buffer_handler() {
		if ( null === $this->buffer_events_handler ) {
			/**
			 * Buffering events handler.
			 *
			 * @var Handler $buffer_handler
			 */
			$buffer_handler              = ServiceRegister::getService( BufferingEventsHandler::class );
			$this->buffer_events_handler = $buffer_handler;
		}

		return $this->buffer_events_handler;
	}

	/**
	 * Creates automation record.
	 *
	 * @param CartAutomation $automation Automation.
	 * @param string         $email Customer email.
	 * @param string|int     $cart_id Cart ID.
	 * @param float          $total Cart total.
	 *
	 * @return void
	 */
	private function create_record( CartAutomation $automation, $email, $cart_id, $total ) {
		try {
			$record = new AutomationRecord();
			$record->setAutomationId( $automation->getId() );
			$record->setCartId( (string) $cart_id );
			$record->setGroupId( $this->get_group_service()->getId() );
			$record->setEmail( $email );
			$record->setAmount( $total );

			$this->automation_record_service->create( $record );
		} catch ( FailedToCreateAutomationRecordException $e ) {
			Logger::logError(
				'Failed to create cart record.',
				'Integration',
				array(
					new LogContextData( 'message', $e->getMessage() ),
					new LogContextData( 'trace', $e->getTraceAsString() ),
				)
			);
		}
	}

	/**
	 * Retrieves whether initial sync task in enqueued.
	 *
	 * @return bool
	 */
	private function is_initial_sync_task_completed() {
		/**
		 * Queue service
		 *
		 * @var QueueService $queue_service
		 */
		$queue_service     = ServiceRegister::getService( Base_Queue_Service::CLASS_NAME );
		$initial_sync_task = $queue_service->findLatestByType( 'InitialSyncTask' );

		return null !== $initial_sync_task && $initial_sync_task->getStatus() === QueueItem::COMPLETED;
	}

	/**
	 * Check if the recipient is subscribed in the CleverReach panel
	 *
	 * @param string $email - Subscriber's e-mail.
	 *
	 * @return bool
	 *
	 * @throws HttpCommunicationException Http Communication Exception.
	 * @throws FailedToRetrieveAuthInfoException Failed To Retrieve Auth Info Exception.
	 * @throws HttpRequestException Http Request Exception.
	 * @throws FailedToRefreshAccessToken Failed To Refresh Access Token.
	 */
	private function is_recipient_subscriber( $email ) {
		$receiver = $this->get_receiver_proxy()->getReceiver( $this->get_group_service()->getId(), $email );

		foreach ( $receiver->getTags() as $tag ) {
			if ( $tag->getValue() === 'Subscriber' ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Retrieves receiver proxy instance.
	 *
	 * @return Proxy
	 */
	private function get_receiver_proxy() {
		/**
		 * Receiver proxy instance.
		 *
		 * @var Proxy $proxy
		 */
		$proxy = ServiceRegister::getService( Proxy::CLASS_NAME );

		return $proxy;
	}
}
