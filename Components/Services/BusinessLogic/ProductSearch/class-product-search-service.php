<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Components\Services\BusinessLogic\ProductSearch;

use CleverReach\WooCommerce\Components\Repositories\Products\Product_Repository;
use CleverReach\WooCommerce\Components\Services\BusinessLogic\ProductSearch\Contracts\Product_Search_Service_Interface;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\DynamicContent\DTO\Response\SearchResult\Item;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\DynamicContent\DTO\Response\SearchResult\SearchResult;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\DynamicContent\DTO\Response\SearchResult\Settings;

/**
 * Class Product_Search_Service
 *
 * @package CleverReach\WooCommerce\Components\Services\BusinessLogic\ProductSearch
 */
class Product_Search_Service implements Product_Search_Service_Interface {


	/**
	 * Searches products.
	 *
	 * @param string $sku_or_title Title or SKU of product to search.
	 *
	 * @return SearchResult
	 */
	public function search_products( $sku_or_title ) {
		$search_result = new SearchResult();
		$settings      = new Settings();
		$settings->setType( Settings::PRODUCT );
		$search_result->setSettings( $settings );

		$product_ids = $this->get_products_ids( $sku_or_title );

		foreach ( $product_ids as $product_id ) {
			$formatted_product = $this->format_product( $product_id );

			if ( $formatted_product ) {
				$search_result->addItem( $formatted_product );
			}
		}

		return $search_result;
	}

	/**
	 * Retrieves product IDs.
	 *
	 * @param string $search_term Term to search.
	 *
	 * @return int[]
	 */
	private function get_products_ids( $search_term ) {
		$product_repository = new Product_Repository();

		return $product_repository->get_product_ids_by_search_term( $search_term );
	}

	/**
	 * Formats product.
	 *
	 * @param int $product_id ID of product to format.
	 *
	 * @return Item|null
	 */
	private function format_product( $product_id ) {
		$wp_product = wc_get_product( $product_id );

		if ( ! $wp_product ) {
			return null;
		}

		$post = get_post( $product_id );

		$item = new Item( (string) $product_id );
		$item->setTitle( $post->post_title );
		$item->setDescription( wp_strip_all_tags( $post->post_excerpt ) );

		$item->setImage( get_the_post_thumbnail_url( $post->ID, array( 600, 0 ) ) );
		$item->setUrl( get_permalink( $post->ID ) );

		$wp_price = get_woocommerce_currency()
					. ' '
					. number_format_i18n( (float) $wp_product->get_price(), 2 );

		$item->setPrice( $wp_price );

		return $item;
	}
}
