<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Components\Services\BusinessLogic;

use CleverReach\WooCommerce\Components\Repositories\Orders\Contracts\Order_Repository_Interface;
use CleverReach\WooCommerce\Components\Util\Shop_Helper;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Order\Contracts\OrderService as OrderServiceInterface;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Order\DTO\OrderItem;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\SyncSettings\Contracts\SyncSettingsService;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Logger\Logger;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;
use Exception;

/**
 * Class Order_Service
 *
 * @package CleverReach\WooCommerce\Components\Services\BusinessLogic
 */
class Order_Service implements OrderServiceInterface {


	/**
	 * Order repository.
	 *
	 * @var Order_Repository_Interface $order_repository
	 */
	private $order_repository;

	/**
	 * Can synchronize order items.
	 *
	 * @return bool
	 */
	public function canSynchronizeOrderItems() {
		/**
		 * Sync settings service.
		 *
		 * @var SyncSettingsService $sync_settings_service
		 */
		$sync_settings_service = ServiceRegister::getService( SyncSettingsService::CLASS_NAME );
		$enabled_services      = $sync_settings_service->getEnabledServices();

		foreach ( $enabled_services as $enabled_service ) {
			if ( $enabled_service->getUuid() === 'buyer-service' ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Retrieves order items.
	 *
	 * @param int|string $order_id ID of the order to retrieve order items from.
	 *
	 * @return array|OrderItem[]
	 */
	public function getOrderItems( $order_id ) {
		try {
			$result = $this->get_order_repository()->get_order_items( $order_id );
		} catch ( Exception $e ) {
			Logger::logError( $e->getMessage() );
			$result = array();
		}

		return $result;
	}

	/**
	 * Get order source
	 *
	 * @param string|int $order_id Order id.
	 *
	 * @return string
	 */
	public function getOrderSource( $order_id ) {
		return Shop_Helper::get_shop_url() ? Shop_Helper::get_shop_url() : 'REST API';
	}

	/**
	 * Get list of order items for given customer email.
	 *
	 * @param string $email Customer email to get list of order items.
	 *
	 * @return OrderItem[]
	 */
	public function get_order_items_by_customer_email( $email ) {
		$orders = $this->get_order_repository()->get_orders_by_email( $email );

		try {
			$result = $this->fetch_order_items( $orders );
		} catch ( Exception $e ) {
			Logger::logError( $e->getMessage() );
			$result = array();
		}

		return $result;
	}

	/**
	 * Fetches order items based on orders
	 *
	 * @param string[] $orders Array of orders to fetch items from.
	 *
	 * @return OrderItem[]
	 * @throws Exception Exception.
	 */
	public function fetch_order_items( $orders ) {
		return $this->get_order_repository()->get_order_items_by_order_ids( $orders );
	}

	/**
	 * Returns order repository.
	 *
	 * @return Order_Repository_Interface
	 */
	private function get_order_repository() {
		if ( null === $this->order_repository ) {
			/**
			 * Order repository interface.
			 *
			 * @var Order_Repository_Interface $repository
			 */
			$repository             = ServiceRegister::getService( Order_Repository_Interface::class );
			$this->order_repository = $repository;
		}

		return $this->order_repository;
	}
}
