<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Migrations\V300;

use CleverReach\WooCommerce\Components\Util\Database;
use CleverReach\WooCommerce\Components\Util\Step;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Configuration\ConfigurationManager;
use CleverReach\WooCommerce\Migrations\Exceptions\Failed_To_Execute_Migration_Step_Exception;
use Exception;

/**
 * Class Migrate_Dynamic_Content_Data.
 *
 * @package CleverReach\WooCommerce\Migrations\V300
 */
class Migrate_Dynamic_Content_Data extends Step {


	const CLASS_NAME = __CLASS__;

	/**
	 * Configuration manager.
	 *
	 * @var ConfigurationManager
	 */
	private $config_manager;

	/**
	 * Execute migration step.
	 *
	 * @throws Failed_To_Execute_Migration_Step_Exception Failed to execute migration step exception.
	 */
	public function execute() {
		try {
			$database                = new Database( $this->db );
			$product_search_id       = $database->get_old_config_value( 'CLEVERREACH_PRODUCT_SEARCH_CONTENT_ID' );
			$product_search_password = $database->get_old_config_value( 'CLEVERREACH_PRODUCT_SEARCH_PASSWORD' );

			if ( $product_search_id && $product_search_password ) {
				$this->get_config_manager()->saveConfigValue( 'dynamicContentPassword', $product_search_password );

				$this->get_config_manager()->saveConfigValue(
					'dynamicContentIds',
					json_encode( array( $product_search_id ) )
				);
			}
		} catch ( Exception $e ) {
			throw new Failed_To_Execute_Migration_Step_Exception(
				'Failed to execute migration step because: ' . esc_html( $e->getMessage() )
			);
		}
	}

	/**
	 * Configuration Manager.
	 *
	 * @return ConfigurationManager
	 */
	private function get_config_manager() {
		if ( null === $this->config_manager ) {
			$this->config_manager = ConfigurationManager::getInstance();
		}

		return $this->config_manager;
	}
}
