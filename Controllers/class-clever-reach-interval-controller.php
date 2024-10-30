<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Controllers;

use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\EventsBuffering\Contracts\BufferConfigurationInterface;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\EventsBuffering\BufferConfigurationService;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;

/**
 * Class Clever_Reach_Interval_Controller
 *
 * @package CleverReach\WooCommerce\Controllers
 */
class Clever_Reach_Interval_Controller extends Clever_Reach_Base_Controller {

	/**
	 * Buffer config service.
	 *
	 * @var BufferConfigurationService Buffer config service.
	 */
	private $buffer_config_service;

	/**
	 * Retrieves buffer config.
	 *
	 * @return void
	 */
	public function get() {
		$buffer_config = $this->get_buffer_config_service()->getConfiguration( '' );

		if ( ! $buffer_config ) {
			$this->return_json( array( 'intervalType' => 'immediate' ) );
		}

		$result             = $buffer_config->toArray();
		$result['interval'] = $buffer_config->getInterval() / 60;

		$this->return_json( $result );
	}

	/**
	 * Retrieves BufferConfigurationService
	 *
	 * @return BufferConfigurationService Buffer config service.
	 */
	private function get_buffer_config_service() {
		if ( null === $this->buffer_config_service ) {
			/**
			 * Buffer config service.
			 *
			 * @var  BufferConfigurationService $buffer_config_service
			 */
			$buffer_config_service       = ServiceRegister::getService( BufferConfigurationInterface::CLASS_NAME );
			$this->buffer_config_service = $buffer_config_service;
		}

		return $this->buffer_config_service;
	}
}
