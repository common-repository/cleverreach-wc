<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Components\Services\BusinessLogic\Merger;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Receiver;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\Merger\Merger;

/**
 * Class Subscriber_Merger
 *
 * @package CleverReach\WooCommerce\Components\Services\BusinessLogic\Merger
 */
class Subscriber_Merger extends Merger {

	const CLASS_NAME = __CLASS__;

	/**
	 * Instance of Subscriber_Merger
	 *
	 * @var Subscriber_Merger
	 *
	 * @phpstan-ignore-next-line
	 */
	protected static $instance;

	/**
	 * Performs merge for subscribers.
	 *
	 * @param Receiver $from Merge from.
	 * @param Receiver $to Merge to.
	 *
	 * @return void
	 */
	public function merge( Receiver $from, Receiver $to ) {
		$target_email = $to->getEmail();
		if ( empty( $target_email ) ) {
			$to->setEmail( $from->getEmail() );
		}

		$to->setSource( $from->getSource() );

		$target_shop = $to->getShop();
		if ( empty( $target_shop ) ) {
			$to->setShop( $from->getShop() );
		}

		$target_customer_number = $to->getCustomerNumber();
		if ( empty( $target_customer_number ) ) {
			$to->setCustomerNumber( $from->getCustomerNumber() );
		}

		$to->setActivated( $from->getActivated() );
		$to->setRegistered( $from->getRegistered() );
		$to->addTags( $from->getTags() );
		$to->addModifiers( $from->getModifiers() );
		$to->setSalutation( $from->getSalutation() );
		$to->setLanguage( $from->getLanguage() );

		if ( $to->getBirthday() === null && $from->getBirthday() instanceof \DateTime ) {
			$to->setBirthday( $from->getBirthday() );
		}

		$target_phone = $to->getPhone();
		if ( empty( $target_phone ) ) {
			$to->setPhone( $from->getPhone() );
		}

		$target_first_name = $to->getFirstName();
		$target_last_name  = $to->getLastName();
		if ( empty( $target_first_name ) || empty( $target_last_name ) ) {
			$to->setLastName( $from->getLastName() );
			$to->setFirstName( $from->getFirstName() );
		}

		$target_street  = $to->getStreet();
		$target_zip     = $to->getZip();
		$target_city    = $to->getCity();
		$target_state   = $to->getState();
		$target_country = $to->getCountry();
		if ( ! empty( $target_street ) || ! empty( $target_zip ) || ! empty( $target_city )
			|| ! empty( $target_state ) || ! empty( $target_country ) ) {
			return;
		}

		$to->setStreet( $from->getStreet() );
		$to->setStreetNumber( $from->getStreetNumber() );
		$to->setZip( $from->getZip() );
		$to->setCity( $from->getCity() );
		$to->setCompany( $from->getCompany() );
		$to->setState( $from->getState() );
		$to->setCountry( $from->getCountry() );
	}
}
