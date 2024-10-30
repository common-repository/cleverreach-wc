<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Components\Repositories\Orders\Contracts;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Order\DTO\OrderItem;
use Exception;

/**
 * Interface Order_Repository_Interface
 *
 * @package CleverReach\WooCommerce\Components\Repositories\Orders\Contracts
 */
interface Order_Repository_Interface {

	/**
	 * Returns order items by order ids.
	 *
	 * @param string[] $order_ids list of order identifications.
	 *
	 * @return OrderItem[]
	 * @throws Exception Exception.
	 */
	public function get_order_items_by_order_ids( $order_ids );

	/**
	 * Retrieves list of order items for a given order id.
	 *
	 * @param string | int $order_id Order identification.
	 *
	 * @return OrderItem[]
	 * @throws Exception Exception.
	 */
	public function get_order_items( $order_id );

	/**
	 * Returns orders data for the list of registered emails.
	 *
	 * @param string[] $emails emails.
	 *
	 * @return mixed[]
	 */
	public function get_order_data_for_registered_emails( $emails );

	/**
	 * Returns orders data for the list of guest emails.
	 *
	 * @param string[] $emails emails.
	 *
	 * @return mixed[]
	 */
	public function get_order_data_for_guest_emails( $emails );

	/**
	 * Returns list of orders for customer email.
	 *
	 * @param string $email email.
	 *
	 * @return string[]
	 */
	public function get_orders_by_email( $email );
}
