<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Components\WebHooks\Handlers;

use CleverReach\WooCommerce\Components\Services\BusinessLogic\Customers\Subscriber_Service;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRefreshAccessToken;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Exceptions\FailedToRetrieveAuthInfoException;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Group\Contracts\GroupService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Receiver;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Http\Proxy;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Data\DataTransferObject;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpCommunicationException;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpRequestException;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Logger\Logger;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Serializer\Interfaces\Serializable;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Serializer\Serializer;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Exceptions\QueueStorageUnavailableException;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\QueueService;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Task;
use Exception;

/**
 * Class Handler
 *
 * @package CleverReach\WooCommerce\Components\WebHooks\Handlers
 */
abstract class Handler extends Task {

	/**
	 * Receiver identification
	 *
	 * @var string
	 */
	protected $receiver_id;

	/**
	 * Handler constructor.
	 *
	 * @param string $receiver_id Receiver identification.
	 */
	public function __construct( $receiver_id ) {
		$this->receiver_id = $receiver_id;
	}

	/**
	 * Creates handler from array
	 *
	 * @param array $array data array.
	 *
	 * @return Handler|Serializable|static
	 */
	public static function fromArray( array $array ) {
		return new static( $array['receiverId'] );
	}

	/**
	 * Serializes data.
	 *
	 * @return string
	 */
	public function serialize() {
		return Serializer::serialize( $this->receiver_id );
	}

	/**
	 *  Unserializes data.
	 *
	 * @param string $serialized Serialized data.
	 */
	public function unserialize( $serialized ) {
		$this->receiver_id = (int) Serializer::unserialize( $serialized );
	}

	/**
	 * Serializes receiver_id to array
	 *
	 * @return array|string[]
	 */
	public function toArray() {
		return array(
			'receiverId' => $this->receiver_id,
		);
	}

	/**
	 * @inheritDoc
	 * @return array|string[]
	 */
	public function getEqualityComponents() {
		return $this->toArray();
	}

	/**
	 * Retrieves receiver
	 *
	 * @param string $group_id Group identification.
	 * @param string $receiver_id Receiver identification.
	 *
	 * @return Receiver|DataTransferObject
	 * @throws FailedToRefreshAccessToken Exception if access token didn't refresh.
	 * @throws FailedToRetrieveAuthInfoException Exception if auth info not retrieved.
	 * @throws HttpCommunicationException Exception if HTTP communication error.
	 * @throws HttpRequestException Exception if HTTP request error.
	 */
	protected function get_receiver( $group_id, $receiver_id ) {
		return $this->get_receiver_proxy()->getReceiver( $group_id, $receiver_id );
	}

	/**
	 * Retrieves receiver proxy
	 *
	 * @return Proxy
	 */
	protected function get_receiver_proxy() {
		/** @noinspection PhpIncompatibleReturnTypeInspection */
		return ServiceRegister::getService( Proxy::CLASS_NAME );
	}

	/**
	 * Handles subscriber update/create event
	 *
	 * @param Receiver $receiver Receiver object.
	 * @param Receiver $subscriber Subscriber.
	 *
	 * @throws Exception Exception.
	 */
	protected function handle_subscriber_update_or_create_event( $receiver, $subscriber ) {
		if ( ! $this->is_receiver_active( $receiver ) ) {
			return;
		}

		$this->update_subscriber( $receiver, $subscriber );
	}

	/**
	 * Returns receiver active status
	 *
	 * @param Receiver $receiver Receiver object.
	 *
	 * @return bool
	 */
	protected function is_receiver_active( Receiver $receiver ) {
		return ( $receiver->getDeactivated() !== null && $receiver->getDeactivated()->getTimestamp() !== 0 )
		       || ( $receiver->getActivated() !== null && $receiver->getActivated()->getTimestamp() !== 0 );
	}

	/**
	 * Creates or updates user webhook
	 *
	 * @param Receiver $receiver Receiver object.
	 * @param Receiver|null $subscriber Subscriber.
	 *
	 * @throws Exception Exception.
	 */
	protected function update_subscriber( Receiver $receiver, Receiver $subscriber = null ) {
		if ( $subscriber ) {
			$email = $subscriber->getEmail();
			! empty( $email ) && $this->get_subscriber_service()->update_subscriber( $receiver );
		}
	}

	/**
	 * Retrieves subscriber service
	 *
	 * @return Subscriber_Service
	 */
	protected function get_subscriber_service() {
		/** @noinspection PhpIncompatibleReturnTypeInspection */
		return ServiceRegister::getService( Subscriber_Service::CLASS_NAME );
	}

	/**
	 * Enqueues task
	 *
	 * @param Task $task Task.
	 */
	protected function enqueue( Task $task ) {
		$queue_name = $this->getConfigService()->getDefaultQueueName();

		try {
			$this->get_queue_service()->enqueue( $queue_name, $task );
		} catch ( QueueStorageUnavailableException $e ) {
			Logger::logError( $e->getMessage(), 'Integration' );
		}
	}

	/**
	 * Retrieves queue service
	 *
	 * @return QueueService
	 */
	protected function get_queue_service() {
		/** @noinspection PhpIncompatibleReturnTypeInspection */
		return ServiceRegister::getService( QueueService::CLASS_NAME );
	}

	/**
	 * Retrieves group service
	 *
	 * @return GroupService
	 */
	protected function get_group_service() {
		/** @noinspection PhpIncompatibleReturnTypeInspection */
		return ServiceRegister::getService( GroupService::CLASS_NAME );
	}
}
