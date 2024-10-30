<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Components\Repositories\Customers\Contracts;

interface Contact_Repository_Interface {

	/**
	 * Get receiver by email.
	 *
	 * @param string $email Email.
	 *
	 * @return mixed[]|false|mixed|null
	 */
	public function get_by_email( $email );

	/**
	 * Retrieves list of receiver emails provided by the integration.
	 *
	 * @return string[]
	 */
	public function get_emails();
	/**
	 * Retrieves list of receivers identified by the provided emails.
	 *
	 * @param string[] $emails List of emails.
	 *
	 * @return mixed[]
	 */
	public function get_by_emails( $emails );
}
