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
 * Class Buyer_Merger
 *
 * @package CleverReach\WooCommerce\Components\Services\BusinessLogic\Merger
 */
class Buyer_Merger extends Merger {

	const CLASS_NAME = __CLASS__;

	/**
	 * Instance of Buyer Merger
	 *
	 * @var Buyer_Merger
	 *
	 * @phpstan-ignore-next-line
	 */
	protected static $instance;

	/**
	 * Performs merge for buyers.
	 *
	 * @param Receiver $from Merge from.
	 * @param Receiver $to Merge to.
	 *
	 * @return void
	 */
	public function merge( Receiver $from, Receiver $to ) {
		parent::merge( $from, $to );

		$to->setOrderItems( $from->getOrderItems() );
	}
}
