<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Components\Repositories\Products;

use CleverReach\WooCommerce\Components\Repositories\Base_Repository;
use CleverReach\WooCommerce\Components\Repositories\Products\Contracts\Product_Repository_Interface;

/**
 * Class Product_Repository
 *
 * @package CleverReach\WooCommerce\Components\Repositories\Products
 */
class Product_Repository extends Base_Repository implements Product_Repository_Interface {


	/**
	 * Searches posts table for all products that have search term within their title or description.
	 *
	 * @param string $search_term Search term.
	 *
	 * @inheritDoc
	 */
	public function get_product_ids_by_search_term( $search_term ) {
		$sql_configurable_product = "select variation.post_parent
										from {$this->db->posts} variation
										where variation.post_type in ('product', 'product_variation')
										  and (variation.post_title like %s
										           or variation.post_content like %s";

		$sql_search_by_sku = "select product.id 
								from {$this->db->posts} product
								inner join {$this->db->postmeta} meta on (product.id = meta.post_id)
								where product.post_type in ('product', 'product_variation')
								  and meta.meta_key = '_sku'
								  and meta.meta_value like %s
								and product.id not in ($sql_configurable_product))";

		$sql_search_by_title_or_content = "select product.id 
											from {$this->db->posts} product
											where product.post_type in ('product', 'product_variation') 
											  and (product.post_title like %s or product.post_content like %s)
											and product.id not in ($sql_configurable_product))
				";

		$sql   = "$sql_search_by_title_or_content union $sql_search_by_sku";
		$query = $this->db->prepare(
			$sql,
			"%$search_term%",
			"%$search_term%",
			"%$search_term%",
			"%$search_term%",
			"%$search_term%",
			"%$search_term%",
			"%$search_term%"
		);

		return $this->db->get_col( $query, 0 );
	}
}
