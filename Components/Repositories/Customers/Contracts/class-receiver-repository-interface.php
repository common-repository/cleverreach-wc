<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Components\Repositories\Customers\Contracts;

use Exception;

/**
 * Interface Receiver_Repository_Interface
 *
 * @package CleverReach\WooCommerce\Components\Repositories\Customers\Contracts
 */
interface Receiver_Repository_Interface {


	/**
	 * Get receiver by email.
	 *
	 * @param string $email Receiver email.
	 *
	 * @return mixed[]|null
	 * @throws Exception Exception.
	 */
	public function get_by_email( $email );

	/**
	 * Retrieves list of receivers identified by the provided emails.
	 *
	 * @param string[] $emails List of emails.
	 *
	 * @return mixed[]
	 */
	public function get_by_emails( $emails );

	/**
	 * Retrieves list of receiver emails provided by the integration.
	 *
	 * @return string[]
	 */
	public function get_emails();

	/**
	 * Retrieves registered receiver by id.
	 *
	 * @param integer $receiver_id Receiver identification.
	 *
	 * @return mixed[]|null
	 */
	public function get_registered_receiver_by_id( $receiver_id );
}
