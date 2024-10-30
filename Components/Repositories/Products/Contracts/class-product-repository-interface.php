<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Components\Repositories\Products\Contracts;

/**
 * Interface Product_Repository_Interface
 *
 * @package CleverReach\WooCommerce\Components\Repositories\Products\Contracts
 */
interface Product_Repository_Interface {


	/**
	 * Searches posts table for all products that have search term within their title or description.
	 *
	 * @param string $search_term Search term.
	 *
	 * @return int[] Array of product IDs.
	 */
	public function get_product_ids_by_search_term( $search_term );
}
