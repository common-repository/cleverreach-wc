<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Components\Services\BusinessLogic\Customers;

use CleverReach\WooCommerce\Components\Repositories\Customers\Contracts\Receiver_Repository_Interface;
use CleverReach\WooCommerce\Components\Repositories\Customers\Contracts\Subscriber_Repository_Interface;
use CleverReach\WooCommerce\Components\Services\BusinessLogic\Configuration\Config_Service;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Modifier\Value\Decrement;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Receiver;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Tag\Special\Contact;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Tag\Special\Subscriber;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;
use Exception;

/**
 * Class Subscriber_Service
 *
 * @package CleverReach\WooCommerce\Components\Services\BusinessLogic\Customers
 */
class Subscriber_Service extends Base_Receiver_Service {


	const THIS_CLASS_NAME = __CLASS__;

	/**
	 * Subscriber_Service constructor.
	 */
	public function __construct() {
		/**
		 * Receiver repository interface.
		 *
		 * @var Receiver_Repository_Interface $repository
		 */
		$repository                = ServiceRegister::getService( Subscriber_Repository_Interface::class );
		$this->receiver_repository = $repository;
	}

	/**
	 * Updates subscriber.
	 *
	 * @param Receiver $receiver Receiver to update.
	 *
	 * @return void
	 *
	 * @throws Exception Exception if can't retrieve newsletter.
	 */
	public function update_subscriber( Receiver $receiver ) {
		/**
		 * Subscriber repository interface.
		 *
		 * @var Subscriber_Repository_Interface $repository
		 */
		$repository = $this->receiver_repository;
		$repository->update( $receiver );
	}

	/**
	 * Gets newsletter (subscribers and unsubscribes) by specific email.
	 *
	 * @param string $email Targeted email.
	 *
	 * @return Receiver|null
	 * @throws Exception Exception if it can't retrieve newsletter.
	 */
	public function get_newsletter_by_email( $email ) {
		/**
		 * Subscriber repository interface.
		 *
		 * @var Subscriber_Repository_Interface $repository
		 */
		$repository = $this->receiver_repository;
		$receiver   = $repository->get_newsletter_by_email( $email );

		if ( null === $receiver ) {
			return null;
		}

		$receiver = current( $this->format_user_batch( array( $receiver ) ) );
		if ( ! $receiver ) {
			return null;
		}

		return $receiver;
	}

	/**
	 * Sets tags.
	 *
	 * @param Receiver $receiver Receiver to set tags on.
	 *
	 * @inheritDoc
	 */
	protected function set_tags( Receiver $receiver ) {
		parent::set_tags( $receiver );
		$receiver->addModifier( new Decrement( 'tags', (string) ( new Contact( Config_Service::INTEGRATION_NAME ) ) ) );
		$receiver->addTag( new Subscriber( Config_Service::INTEGRATION_NAME ) );
	}
}
