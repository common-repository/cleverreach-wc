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
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\EventsBuffering\Contracts\BufferingEventsHandler;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\EventsBuffering\Handler as EventsBufferHandler;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Group\Contracts\GroupService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Receiver;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Http\Proxy;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpCommunicationException;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpRequestException;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Serializer\Interfaces\Serializable;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Serializer\Serializer;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Task;
use Exception;

/**
 * Class Handler
 *
 * @package CleverReach\WooCommerce\Components\WebHooks\Handlers
 */
abstract class Receiver_Handler extends Task {


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
	 * @param array<string,mixed> $data data array.
	 *
	 * @return Receiver_Handler|Serializable|static
	 */
	public static function fromArray( array $data ) {
		return new static( $data['receiverId'] );
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
		$this->receiver_id = (string) (int) Serializer::unserialize( $serialized );
	}

	/**
	 * Serializes receiver_id to array
	 *
	 * @return array<string,mixed>
	 */
	public function toArray() {
		return array(
			'receiverId' => $this->receiver_id,
		);
	}

	/**
	 * Gets equality components.
	 *
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
	 * @return Receiver
	 * @throws FailedToRefreshAccessToken Exception if access token didn't refresh.
	 * @throws FailedToRetrieveAuthInfoException Exception if auth info not retrieved.
	 * @throws HttpCommunicationException Exception if HTTP communication error.
	 * @throws HttpRequestException Exception if HTTP request error.
	 */
	protected function get_receiver( $group_id, $receiver_id ) {
		/**
		 * Receiver.
		 *
		 * @var Receiver $receiver
		 */
		$receiver = $this->get_receiver_proxy()->getReceiver( $group_id, $receiver_id );

		return $receiver;
	}

	/**
	 * Retrieves receiver proxy
	 *
	 * @return Proxy
	 */
	protected function get_receiver_proxy() {
		/**
		 * Proxy.
		 *
		 * @var Proxy $proxy
		 */
		$proxy = ServiceRegister::getService( Proxy::CLASS_NAME );

		return $proxy;
	}

	/**
	 * Handles subscriber update/create event
	 *
	 * @param Receiver $receiver Receiver object.
	 * @param Receiver $subscriber Subscriber.
	 *
	 * @return void
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
		// @phpstan-ignore-next-line
		return ( $receiver->getDeactivated() !== null && $receiver->getDeactivated()->getTimestamp() !== 0 )
				// @phpstan-ignore-next-line
				|| ( $receiver->getActivated() !== null && $receiver->getActivated()->getTimestamp() !== 0 );
	}

	/**
	 * Creates or updates user webhook
	 *
	 * @param Receiver      $receiver Receiver object.
	 * @param Receiver|null $subscriber Subscriber.
	 *
	 * @return void
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
		/**
		 * Subscriber service.
		 *
		 * @var Subscriber_Service $subscriber_service
		 */
		$subscriber_service = ServiceRegister::getService( Subscriber_Service::THIS_CLASS_NAME );

		return $subscriber_service;
	}

	/**
	 * Retrieves evens buffer handler.
	 *
	 * @return EventsBufferHandler
	 */
	protected function get_events_buffer_handler() {
		/**
		 * Handler.
		 *
		 * @var EventsBufferHandler $handler
		 */
		$handler = ServiceRegister::getService( BufferingEventsHandler::class );

		return $handler;
	}

	/**
	 * Retrieves group service
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
}
