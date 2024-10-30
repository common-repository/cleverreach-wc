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

use CleverReach\WooCommerce\Components\Util\HTTP_Helper;
use CleverReach\WooCommerce\Components\Util\Shop_Helper;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Logger\LogContextData;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\Logger\Logger;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\TaskExecution\Interfaces\AsyncProcessService;

/**
 * Class Clever_Reach_Async_Process_Controller
 *
 * @package CleverReach\WooCommerce\Controllers
 */
class Clever_Reach_Async_Process_Controller extends Clever_Reach_Base_Controller {


	/**
	 * Is request call internal.
	 *
	 * @var bool
	 */
	protected $is_internal = false;

	/**
	 * Async process service.
	 *
	 * @var AsyncProcessService
	 */
	private $async_process_service;

	/**
	 * Runs process defined by guid request parameter.
	 *
	 * @return void
	 */
	public function run() {
		if ( ! Shop_Helper::is_plugin_enabled() ) {
			exit();
		}

		$guid = HTTP_Helper::get_param( 'guid' );
		Logger::logInfo( 'Received async process request.', 'Integration', array( new LogContextData( 'guid', $guid ) ) );

		$this->get_async_process_service()->runProcess( $guid );
	}

	/**
	 * Retrieves Async process service.
	 *
	 * @return AsyncProcessService
	 */
	private function get_async_process_service() {
		if ( null === $this->async_process_service ) {
			/**
			 * Async process service.
			 *
			 * @var AsyncProcessService $async_process_service
			 */
			$async_process_service       = ServiceRegister::getService( AsyncProcessService::CLASS_NAME );
			$this->async_process_service = $async_process_service;
		}

		return $this->async_process_service;
	}
}
