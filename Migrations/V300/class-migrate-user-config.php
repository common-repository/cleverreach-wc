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
 * Class Migrate_User_Config.
 *
 * @package CleverReach\WooCommerce\Migrations\V300
 */
class Migrate_User_Config extends Step {


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
			$database                 = new Database( $this->db );
			$default_newsletter_label = $database->get_old_config_value( 'CLEVERREACH_SUBSCRIBE_FOR_NEWSLETTER_LABEL' );
			if ( ! empty( $default_newsletter_label ) ) {
				$this->get_config_manager()->saveConfigValue(
					'CLEVERREACH_SUBSCRIBE_FOR_NEWSLETTER_LABEL',
					$default_newsletter_label
				);
			}

			$is_checkbox_disabled = $database->get_old_config_value( 'IS_CLEVERREACH_NEWSLETTER_CHECKBOX_DISABLED' );
			if ( ! empty( $is_checkbox_disabled ) ) {
				$this->get_config_manager()->saveConfigValue(
					'IS_CLEVERREACH_NEWSLETTER_CHECKBOX_DISABLED',
					$is_checkbox_disabled
				);
			}
		} catch ( Exception $e ) {
			throw new Failed_To_Execute_Migration_Step_Exception(
				'Failed to execute migration step because: ' . esc_html( $e->getMessage() )
			);
		}
	}

	/**
	 * Retrieve config manager
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
