<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Controllers;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use CleverReach\WooCommerce\Components\Services\BusinessLogic\Configuration\Config_Service;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Configuration\Configuration;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Exceptions\BaseException;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Http\AutoConfiguration;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;

/**
 * Class Clever_Reach_Autoconfigure_Controller
 *
 * @package CleverReach\WooCommerce\Controllers
 */
class Clever_Reach_Autoconfigure_Controller extends Clever_Reach_Base_Controller {


	/**
	 * Autoconfig service.
	 *
	 * @var AutoConfiguration
	 */
	private $autoconfigure_service;

	/**
	 * Config service.
	 *
	 * @var Config_Service
	 */
	private $config_service;

	/**
	 * Start server configuration.
	 *
	 * @return void
	 */
	public function start_server_configuration() {
		$auto_config_service = $this->get_auto_config_service();

		try {
			$data = array( 'success' => $auto_config_service->start() );
		} catch ( BaseException $e ) {
			$data = array( 'success' => false );
		}

		$this->return_json( $data );
	}

	/**
	 * Check status.
	 *
	 * @return void
	 */
	public function check_status() {
		$status = $this->get_config_service()->getAutoConfigurationState();

		$this->return_json( array( 'status' => ! empty( $status ) ? $status : 'pending' ) );
	}

	/**
	 * Returns autoconfiguration service.
	 *
	 * @return AutoConfiguration
	 */
	private function get_auto_config_service() {
		if ( null === $this->autoconfigure_service ) {
			/**
			 * Auto configuration.
			 *
			 * @var AutoConfiguration $autoconfigure_service
			 */
			$autoconfigure_service       = ServiceRegister::getService( AutoConfiguration::CLASS_NAME );
			$this->autoconfigure_service = $autoconfigure_service;
		}

		return $this->autoconfigure_service;
	}

	/**
	 * Returns config service.
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
