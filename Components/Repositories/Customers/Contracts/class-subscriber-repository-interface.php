<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Components\Repositories\Customers\Contracts;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Receiver\DTO\Receiver;

interface Subscriber_Repository_Interface {


	/**
	 * Get subscriber by email
	 *
	 * @param string $email Email.
	 *
	 * @return mixed[]|false|mixed|null
	 */
	public function get_by_email( $email );

	/**
	 * Get emails
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

	/**
	 * Get subscribed buyers
	 *
	 * @return string[]
	 */
	public function get_subscribed_buyers();

	/**
	 * Retrieves newsletter (subscribers and unsubscribes) by specific email.
	 *
	 * @param string $email Receiver email.
	 *
	 * @return mixed[]|null
	 */
	public function get_newsletter_by_email( $email );

	/**
	 * Get subscribed users
	 *
	 * @return string[]
	 */
	public function get_subscribed_users();

	/**
	 * Updates subscriber status.
	 *
	 * @param Receiver $receiver Subscriber.
	 *
	 * @return void
	 */
	public function update( Receiver $receiver );
}
