<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Components\Handlers;

use CleverReach\WooCommerce\Components\Services\BusinessLogic\ProductSearch\Product_Search_Service;
use CleverReach\WooCommerce\Components\Util\HTTP_Helper;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\DynamicContent\DTO\Request\DynamicContentRequest;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\DynamicContent\DTO\Request\SearchTerms;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\DynamicContent\DTO\Response\Filter\Filter;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\DynamicContent\DTO\Response\Filter\FilterCollection;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\DynamicContent\DTO\Response\SearchResult\SearchResult;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\DynamicContent\DynamicContentHandler;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Logger\LogContextData;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Logger\Logger;
use Exception;
use WP_HTTP_Response;

/**
 * Class Product_Search_Handler
 *
 * @package CleverReach\WooCommerce\Components\Handlers
 */
class Product_Search_Handler extends DynamicContentHandler {


	const CLASS_NAME  = __CLASS__;
	const PRODUCT_KEY = 'sku';

	/**
	 * Product Search Service
	 *
	 * @var Product_Search_Service
	 */
	private $product_search_service;


	/**
	 * Handles product search request
	 *
	 * @param WP_HTTP_Response $response Response.
	 *
	 * @return WP_HTTP_Response
	 */
	public function handle_request( WP_HTTP_Response $response ) {
		$request = $this->create_dynamic_content_request();
		try {
			$result = $this->handle( $request );
			$response->set_data( $result );
			$response->set_status( 200 );
		} catch ( Exception $e ) {
			$response->set_status( 400 );
			$response->set_data(
				array(
					'status'  => false,
					'message' => $e->getMessage() ? $e->getMessage() : __( 'Unsuccessful connection.' ),
					'referer' => HTTP_Helper::get_param( 'referer' ),
				)
			);

			Logger::logError(
				"Unable to handle request in product search handler: {$e->getMessage()}",
				'Integration',
				array( new LogContextData( 'trace', $e->getTraceAsString() ) )
			);
		}

		return $response;
	}

	/**
	 * Filter Collection
	 *
	 * @return FilterCollection
	 */
	protected function getFilters() {
		$filters_collection = new FilterCollection();

		$filter = $this->createFilter(
			__( 'Title, Content or SKU', 'cleverreach-wc' ),
			self::PRODUCT_KEY,
			Filter::INPUT
		);
		$filters_collection->addFilter( $filter );

		return $filters_collection;
	}

	/**
	 * Retrieves product search results
	 *
	 * @param SearchTerms $search_terms Search terms.
	 *
	 * @return SearchResult|null
	 */
	protected function getSearchResults( SearchTerms $search_terms ) {
		if ( $search_terms->getValue( static::PRODUCT_KEY ) === null ) {
			return null;
		}

		$sku_or_title = $search_terms->getValue( self::PRODUCT_KEY );

		return $this->get_product_search_service()->search_products( $sku_or_title );
	}

	/**
	 * Retrieves product search service
	 *
	 * @return Product_Search_Service
	 */
	private function get_product_search_service() {
		if ( ! $this->product_search_service ) {
			$this->product_search_service = new Product_Search_Service();
		}

		return $this->product_search_service;
	}

	/**
	 * Generates dynamic content request
	 *
	 * @return DynamicContentRequest
	 */
	private function create_dynamic_content_request() {
		$dynamic_content_request = new DynamicContentRequest(
			HTTP_Helper::get_param( 'get' ),
			HTTP_Helper::get_param( 'password' ),
			''
		);

		if ( $dynamic_content_request->getType() === 'search' ) {
			$search_terms = new SearchTerms();
			$search_terms->add( 'sku', HTTP_Helper::get_param( 'sku' ) );

			$dynamic_content_request->setSearchTerms( $search_terms );
		}

		return $dynamic_content_request;
	}
}
