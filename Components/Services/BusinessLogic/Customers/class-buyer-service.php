<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Components\Services\BusinessLogic\Customers;

use CleverReach\WooCommerce\Components\Repositories\Customers\Contracts\Buyer_Repository_Interface;
use CleverReach\WooCommerce\Components\Repositories\Customers\Contracts\Receiver_Repository_Interface;
use CleverReach\WooCommerce\Components\Services\BusinessLogic\Configuration\Config_Service;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Modifier\Value\Decrement;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Receiver;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Tag\Special\Buyer;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Tag\Special\Contact;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;

/**
 * Class Buyer_Service
 *
 * @package CleverReach\WooCommerce\Components\Services\BusinessLogic\Customers
 */
class Buyer_Service extends Base_Receiver_Service {


	const THIS_CLASS_NAME = __CLASS__;

	/**
	 * Buyer_Service constructor.
	 */
	public function __construct() {
		/**
		 * Receiver repository interface.
		 *
		 * @var Receiver_Repository_Interface $repository
		 */
		$repository                = ServiceRegister::getService( Buyer_Repository_Interface::class );
		$this->receiver_repository = $repository;
	}

	/**
	 * Retrieves list of buyer emails.
	 *
	 * @return string[]
	 */
	public function get_emails() {
		return $this->receiver_repository->get_emails();
	}

	/**
	 * Retrieves buyer.
	 *
	 * @param string $email Email of the buyer.
	 * @param bool   $is_service_specific_data_required Is specific data required.
	 *
	 * @inheritDoc
	 */
	public function getReceiver( $email, $is_service_specific_data_required = false ) {
		$receiver = parent::getReceiver( $email, $is_service_specific_data_required );

		if ( null === $receiver ) {
			return null;
		}

		if ( $is_service_specific_data_required ) {
			$this->set_order_data( $receiver );
		}

		return $receiver;
	}

	/**
	 * Retrieves buyers batch
	 *
	 * @param string[] $emails Array of emails to retrieve.
	 * @param bool     $is_servicespecificdatarequired Is specific data required.
	 *
	 * @inheritDoc
	 */
	public function getReceiverBatch( array $emails, $is_servicespecificdatarequired = false ) {
		$receivers = parent::getReceiverBatch(
			$emails,
			$is_servicespecificdatarequired
		);

		if ( empty( $receivers ) ) {
			return array();
		}

		if ( $is_servicespecificdatarequired ) {
			foreach ( $receivers as $receiver ) {
				$this->set_order_data( $receiver );
			}
		}

		return $receivers;
	}

	/**
	 * Sets tags to buyer.
	 *
	 * @param Receiver $receiver Buyer to set tags to.
	 *
	 * @inheritDoc
	 */
	protected function set_tags( Receiver $receiver ) {
		parent::set_tags( $receiver );
		$receiver->addTag( new Buyer( Config_Service::INTEGRATION_NAME ) );
		$receiver->addModifier( new Decrement( 'tags', (string) ( new Contact( Config_Service::INTEGRATION_NAME ) ) ) );
	}
}
