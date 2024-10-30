<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\ViewModel;

use CleverReach\WooCommerce\Components\Services\BusinessLogic\Configuration\Config_Service;
use CleverReach\WooCommerce\Components\Util\Shop_Helper;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Configuration\Configuration;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;

/**
 * Class Autoconfigure_Config
 *
 * @package CleverReach\WooCommerce\ViewModel
 */
class Autoconfigure_Config {

	/**
	 * Set up values for AutoConfigure screen
	 *
	 * @return array<string,mixed>
	 */
	public static function get_autoconfigure_config() {
		/**
		 * Config service
		 *
		 * @var Config_Service $config_service
		 */
		$config_service      = ServiceRegister::getService( Configuration::CLASS_NAME );
		$autoconfigure_state = $config_service->getAutoConfigurationState();

		$start_server_configuration_url = Shop_Helper::get_controller_url(
			'Autoconfigure',
			'start_server_configuration'
		);
		$check_status_url               = Shop_Helper::get_controller_url( 'Autoconfigure', 'check_status' );

		return array(
			'startServerConfigurationUrl' => $start_server_configuration_url,
			'checkStatusUrl'              => $check_status_url,
			'autoconfigureFailed'         => ! empty( $autoconfigure_state ) && 'failed' === $autoconfigure_state,
		);
	}
}
