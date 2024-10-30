<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Components\Services\BusinessLogic\Automation\Trigger;

use CleverReach\WooCommerce\Components\Services\BusinessLogic\Configuration\Config_Service;
use CleverReach\WooCommerce\Components\Services\BusinessLogic\Customers\Subscriber_Service;
use CleverReach\WooCommerce\Components\Services\BusinessLogic\DOI\Double_Opt_In_Service;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\DoubleOptIn\DTO\DoubleOptInEmail;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\DoubleOptIn\Tasks\SendDoubleOptInEmailsTask;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Group\Contracts\GroupService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Receiver;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Http\Proxy as ReceiverProxy;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Tasks\Composite\Configuration\SyncConfiguration;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Tasks\Composite\ReceiverSyncTask;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Tasks\Composite\SubscribeReceiverTask;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Scheduler\Interfaces\Schedulable;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Configuration\Configuration;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\Exceptions\HttpRequestException;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Logger\Logger;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Serializer\Interfaces\Serializable;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Serializer\Serializer;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Task;
use Exception;

/**
 * Class Trigger_Newsletter_Subscription_Task
 *
 * @package CleverReach\WooCommerce\Components\Services\BusinessLogic\Automation\Trigger
 */
class Trigger_Newsletter_Subscription_Task extends Task implements Schedulable {


	/**
	 * Double Opt In service
	 *
	 * @var Double_Opt_In_Service
	 */
	private $doi_service;

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
	private $subscriber_service;

	/**
	 * Subscriber Email
	 *
	 * @var string
	 */
	private $subscriber_email;

	/**
	 * Transforms serializable object into an array.
	 *
	 * @return array<string,mixed> Array representation of a serializable object.
	 */
	public function toArray() {
		return array( 'subscriberEmail' => $this->subscriber_email );
	}

	/**
	 * Transforms array into an serializable object,
	 *
	 * @param array<string,mixed> $data Data that is used to instantiate serializable object.
	 *
	 * @return Serializable Instance of serialized object.
	 */
	public static function fromArray( array $data ) {
		return new self( $data['subscriberEmail'] );
	}

	/**
	 * String representation of object.
	 *
	 * @link https://php.net/manual/en/serializable.serialize.php Link.
	 *
	 * @return string the string representation of the object or null.
	 */
	public function serialize() {
		return Serializer::serialize( $this->subscriber_email );
	}

	/**
	 * Constructs the object.
	 *
	 * @param string $serialized Serialized.
	 *
	 * @return void
	 */
	public function unserialize( $serialized ) {
		$this->subscriber_email = Serializer::unserialize( $serialized );
	}

	/**
	 * Trigger_Newsletter_Subscription_Task constructor.
	 *
	 * @param string $subscriber_email Subscriber's email.
	 */
	public function __construct( $subscriber_email ) {
		$this->subscriber_email = $subscriber_email;
	}

	/**
	 * Executes the newsletter subscription trigger action.
	 *
	 * @return void
	 */
	public function execute() {
		try {
			$receiver = null;
			/**
			 * Group service.
			 *
			 * @var GroupService $group_service
			 */
			$group_service = ServiceRegister::getService( GroupService::CLASS_NAME );
			$group_id      = $group_service->getId();

			try {
				/**
				 * Receiver.
				 *
				 * @var Receiver $receiver
				 */
				$receiver = $this->get_receiver_proxy()->getReceiver( $group_id, $this->subscriber_email );
			} catch ( HttpRequestException $e ) {
				$message = json_encode(
					array(
						'Message'          => 'Receiver not found on CleverReach.',
						'ExceptionMessage' => $e->getMessage(),
						'ExceptionTrace'   => $e->getTraceAsString(),
					)
				);

				if ( ! $message ) {
					$message = 'Receiver not found on CleverReach.';
				}

				Logger::logInfo( $message, 'Integration' );
			}

			if ( null !== $receiver && $receiver->isActive() && $this->is_receiver_special_subscriber( $receiver ) ) {
				$this->reportProgress( 100 );

				return;
			}

			$this->reportProgress( 30 );

			if ( $this->get_doi_service()->is_doi_enabled() ) {
				$this->send_confirmation_email();
			} else {
				$this->activate_subscriber();
			}

			$this->reportProgress( 100 );
		} catch ( Exception $ex ) {
			$message = json_encode(
				array(
					'Message'          => 'Failed to execute newsletter subscription trigger.',
					'ExceptionMessage' => $ex->getMessage(),
					'ExceptionTrace'   => $ex->getTraceAsString(),
				)
			);

			if ( ! $message ) {
				$message = 'Failed to execute newsletter subscription trigger.';
			}

			Logger::logDebug( $message, 'Integration' );
		}
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
	 * Returns if task can have multiple queued instances.
	 *
	 * @return bool
	 */
	public function canHaveMultipleQueuedInstances() {
		return true;
	}

	/**
	 * Retrieves config service
	 *
	 * @return Config_Service
	 */
	private function get_config_service() {
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
	private function get_subscriber_service() {
		if ( null === $this->subscriber_service ) {
			/**
			 * Subscriber service.
			 *
			 * @var Subscriber_Service $subscriber_service
			 */
			$subscriber_service       = ServiceRegister::getService( Subscriber_Service::THIS_CLASS_NAME );
			$this->subscriber_service = $subscriber_service;
		}

		return $this->subscriber_service;
	}

	/**
	 * Retrieves Double Opt In Service.
	 *
	 * @return Double_Opt_In_Service
	 */
	private function get_doi_service() {
		if ( null === $this->doi_service ) {
			$this->doi_service = new Double_Opt_In_Service();
		}

		return $this->doi_service;
	}

	/**
	 * Sends confirmation email.
	 *
	 * @return void
	 *
	 * @throws Exception Exception.
	 */
	private function send_confirmation_email() {
		try {
			$form_id = $this->get_config_service()->get_default_form();

			$doi_email = new DoubleOptInEmail(
				// @phpstan-ignore-next-line
				$form_id,
				'activate',
				$this->subscriber_email,
				$this->get_doi_service()->create_doi_data()
			);

			$subscriber = $this->get_subscriber_service()->get_newsletter_by_email( $this->subscriber_email );

			if ( $subscriber ) {
				( new ReceiverSyncTask( new SyncConfiguration( array( $this->subscriber_email ) ) ) )->execute();
			}

			( new SendDoubleOptInEmailsTask( array( $doi_email ) ) )->execute();
		} catch ( Exception $ex ) {
			$message = json_encode(
				array(
					'Message'          => 'Failed to send DOI email to: ' . $this->subscriber_email,
					'ExceptionMessage' => $ex->getMessage(),
					'ExceptionTrace'   => $ex->getTraceAsString(),
				)
			);

			if ( ! $message ) {
				$message = 'Failed to send DOI email to: ' . $this->subscriber_email;
			}

			Logger::logDebug( $message, 'Integration' );
		}
	}

	/**
	 * Activates subscriber.
	 *
	 * @return void
	 */
	private function activate_subscriber() {
		try {
			$subscriber = $this->get_subscriber_service()->get_newsletter_by_email( $this->subscriber_email );

			if ( $subscriber ) {
				$subscriber->setActive( true );
				$this->get_subscriber_service()->update_subscriber( $subscriber );
				( new ReceiverSyncTask( new SyncConfiguration( array( $subscriber->getEmail() ) ) ) )->execute();
			}

			( new SubscribeReceiverTask( $this->subscriber_email ) )->execute();
		} catch ( Exception $ex ) {
			$message = json_encode(
				array(
					'Message'          => 'Failed to activate subscriber with email: ' . $this->subscriber_email,
					'ExceptionMessage' => $ex->getMessage(),
					'ExceptionTrace'   => $ex->getTraceAsString(),
				)
			);

			if ( ! $message ) {
				$message = 'Failed to activate subscriber with email: ' . $this->subscriber_email;
			}

			Logger::logDebug( $message, 'Integration' );
		}
	}

	/**
	 * Returns true if receiver is special subscriber.
	 *
	 * @param Receiver $receiver Receiver.
	 *
	 * @return bool
	 */
	private function is_receiver_special_subscriber( $receiver ) {
		$tags = $receiver->getTags();

		foreach ( $tags as $tag ) {
			if ( $tag->getType() === 'Special' && $tag->getValue() === 'Subscriber' ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Retrieves Receiver proxy.
	 *
	 * @return ReceiverProxy
	 */
	protected function get_receiver_proxy() {
		/**
		 * Receiver proxy.
		 *
		 * @var ReceiverProxy $proxy
		 */
		$proxy = ServiceRegister::getService( ReceiverProxy::CLASS_NAME );

		return $proxy;
	}
}
