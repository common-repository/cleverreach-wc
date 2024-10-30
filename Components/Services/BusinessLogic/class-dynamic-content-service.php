<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Components\Services\BusinessLogic;

use CleverReach\WooCommerce\Components\Services\BusinessLogic\Configuration\Config_Service;
use CleverReach\WooCommerce\Components\Util\Shop_Helper;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\DynamicContent\DTO\DynamicContent;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\DynamicContent\DTO\Response\SearchResult\Settings;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\DynamicContent\DynamicContentService;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Configuration\Configuration;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Logger\LogContextData;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Logger\Logger;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;

/**
 * Class Dynamic_Content_Service
 *
 * @package CleverReach\WooCommerce\Components\Services\BusinessLogic
 */
class Dynamic_Content_Service extends DynamicContentService {

	/**
	 * Config service.
	 *
	 * @var Config_Service $config_service
	 */
	private $config_service;

	/**
	 * Returns supported dynamic content.
	 *
	 * @inheritDoc
	 */
	public function getSupportedDynamicContent() {
		try {
			return array(
				$this->createDynamicContent( 'Product Search' ),
			);
		} catch ( QueryFilterInvalidParamException $e ) {
			Logger::logError(
				'Failed to create dynamic content.',
				'Integration',
				array(
					new LogContextData( 'message', $e->getMessage() ),
					new LogContextData( 'trace', $e->getTraceAsString() ),
				)
			);

			return array();
		}
	}

	/**
	 * Creates dynamic content
	 *
	 * @param string $label Dynamic content label.
	 *
	 * @return DynamicContent
	 * @throws QueryFilterInvalidParamException Exception when query filter params are invalid.
	 */
	protected function createDynamicContent( $label ) {
		$integration_name = $this->get_config_service()->getIntegrationName();
		$url              = $this->get_url();

		$dynamic_content = new DynamicContent();
		$dynamic_content->setUrl( $url );
		$dynamic_content->setCors( true );
		$dynamic_content->setIcon( strtolower( $integration_name ) );
		$dynamic_content->setPassword( $this->getDynamicContentPassword() );
		$dynamic_content->setType( Settings::PRODUCT );
		$dynamic_content->setName(
			DynamicContent::formatName(
				$integration_name,
				$label
			)
		);

		return $dynamic_content;
	}

	/**
	 * Retrieves Product search url.
	 *
	 * @return string
	 */
	public function get_url() {
		return Shop_Helper::get_controller_url( 'Product_Search', 'handle_request' );
	}

	/**
	 * Returns configuration service.
	 *
	 * @return Config_Service
	 */
	private function get_config_service() {
		if ( null === $this->config_service ) {
			/**
			 * Config service.
			 *
			 * @var Config_Service $config_service
			 */
			$config_service       = ServiceRegister::getService( Configuration::CLASS_NAME );
			$this->config_service = $config_service;
		}

		return $this->config_service;
	}
}
