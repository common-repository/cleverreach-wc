<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Components\Services\BusinessLogic\Customers;

use CleverReach\WooCommerce\Components\Repositories\Customers\Contracts\Contact_Repository_Interface;
use CleverReach\WooCommerce\Components\Repositories\Customers\Contracts\Receiver_Repository_Interface;
use CleverReach\WooCommerce\Components\Services\BusinessLogic\Configuration\Config_Service;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Receiver;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Tag\Special\Contact;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;

/**
 * Class Contact_Service
 *
 * @package CleverReach\WooCommerce\Components\Services\BusinessLogic\Customers
 */
class Contact_Service extends Base_Receiver_Service {


	const THIS_CLASS_NAME = __CLASS__;

	/**
	 * Contact_Service constructor.
	 */
	public function __construct() {
		/**
		 * Receiver repository interface.
		 *
		 * @var Receiver_Repository_Interface $repository
		 */
		$repository                = ServiceRegister::getService( Contact_Repository_Interface::class );
		$this->receiver_repository = $repository;
	}

	/**
	 * Retrieves list of contact emails.
	 *
	 * @return string[]
	 */
	public function get_emails() {
		return $this->receiver_repository->get_emails();
	}

	/**
	 * Sets tags to receiver.
	 *
	 * @param Receiver $receiver Receiver to set tags to.
	 *
	 * @inheritDoc
	 */
	protected function set_tags( Receiver $receiver ) {
		parent::set_tags( $receiver );
		$receiver->addTag( new Contact( Config_Service::INTEGRATION_NAME ) );
	}
}
