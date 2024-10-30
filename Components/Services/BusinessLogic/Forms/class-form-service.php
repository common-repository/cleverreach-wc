<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Components\Services\BusinessLogic\Forms;

use CleverReach\WooCommerce\Components\Services\BusinessLogic\Configuration\Config_Service;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Form\FormService as BaseFormService;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Configuration\Configuration;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;

/**
 * Class Form_Service
 *
 * @package CleverReach\WooCommerce\Components\Services\BusinessLogic\Forms
 */
class Form_Service extends BaseFormService {


	/**
	 * Config service
	 *
	 * @var Config_Service|null
	 */
	private $config_service;

	/**
	 * Retrieves the integration's default form name.
	 *
	 * @return string
	 */
	public function getDefaultFormName() {
		return $this->getConfigService()->getIntegrationName();
	}

	/**
	 * Retrieves Config service.
	 *
	 * @return Config_Service
	 */
	private function getConfigService() {
		if ( empty( $this->config_service ) ) {
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
