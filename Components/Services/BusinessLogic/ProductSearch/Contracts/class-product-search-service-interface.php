<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Components\Services\BusinessLogic\ProductSearch\Contracts;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\DynamicContent\DTO\Response\SearchResult\SearchResult;

interface Product_Search_Service_Interface {

	/**
	 * Searches products.
	 *
	 * @param string $sku_or_title Title or SKU of product to search.
	 *
	 * @return SearchResult
	 */
	public function search_products( $sku_or_title );
}
