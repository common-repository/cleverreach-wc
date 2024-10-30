<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Components\Services\BusinessLogic\Automation;

use CleverReach\WooCommerce\Components\Repositories\Cart_Repository;
use CleverReach\WooCommerce\Components\Services\BusinessLogic\Automation\Entities\Recovery_Record;
use CleverReach\WooCommerce\Components\Util\Shop_Helper;
use Exception;

/**
 * Class Cart_Service
 *
 * @package CleverReach\WooCommerce\Components\Services\BusinessLogic\Automation
 */
class Cart_Service {


	/**
	 * Cart repository.
	 *
	 * @var Cart_Repository $cart_repository
	 */
	private $cart_repository;

	/**
	 * Merges items from recovery record to the cart.
	 *
	 * @param Recovery_Record $record Recovery record.
	 *
	 * @return void
	 *
	 * @throws Exception Exception.
	 */
	public function merge_carts( Recovery_Record $record ) {
		$items = $record->get_items();
		if ( empty( $items ) ) {
			return;
		}

		$cart = WC()->cart;

		foreach ( $items as $item ) {
			if ( $cart->get_cart_item( $item['key'] ) ) {
				$cart->set_quantity( $item['key'], $item['quantity'] );
			} else {
				$cart->add_to_cart(
					$item['product_id'],
					$item['quantity'],
					$item['variation_id'],
					$item['variation'],
					$item['cart_item_data']
				);
			}
		}
	}

	/**
	 * Returns cart items by session key.
	 *
	 * @param string $session_key Session key.
	 *
	 * @return mixed[]|mixed|string
	 */
	public function get_cart_items_by_session_key( $session_key ) {
		return $this->get_cart_repository()->get_cart_items_by_session_key( $session_key );
	}

	/**
	 * Returns cart total for cart items.
	 *
	 * @param array<string,mixed> $cart_items Cart items.
	 *
	 * @return float
	 */
	public function get_cart_total_for_cart_items( $cart_items = array() ) {
		if ( empty( $cart_items ) ) {
			return 0;
		}

		$total = 0;

		foreach ( $cart_items as $cart_item ) {
			$total += $cart_item['line_total'] + $cart_item['line_tax'];
		}

		return $total;
	}

	/**
	 * Returns cart repository.
	 *
	 * @return Cart_Repository
	 */
	private function get_cart_repository() {
		if ( null === $this->cart_repository ) {
			$this->cart_repository = new Cart_Repository();
		}

		return $this->cart_repository;
	}


	/**
	 * Returns recovery url
	 *
	 * @param Recovery_Record $recovery_record Recovery record.
	 *
	 * @return string Url
	 */
	public function get_recovery_link( Recovery_Record $recovery_record ) {
		return Shop_Helper::get_controller_url(
			'Cart_Recovery',
			'execute',
			array( 'token' => $recovery_record->get_token() )
		);
	}
}
