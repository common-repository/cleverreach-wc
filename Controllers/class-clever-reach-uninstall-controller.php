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

use CleverReach\WooCommerce\Components\Services\BusinessLogic\Uninstall\Contracts\Uninstall_Service_Interface;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;

/**
 * Class Clever_Reach_Uninstall_Controller
 *
 * @package CleverReach\WooCommerce\Controllers
 */
class Clever_Reach_Uninstall_Controller extends Clever_Reach_Base_Controller {

	/**
	 * Uninstall service.
	 *
	 * @var Uninstall_Service_Interface $uninstall_service
	 */
	private $uninstall_service;

	/**
	 * Executes uninstall.
	 *
	 * @return void
	 */
	public function execute() {
		$this->get_uninstall_service()->remove_data();

		$this->return_json( array( 'success' => true ) );
	}

	/**
	 * Retrieves uninstall service.
	 *
	 * @return Uninstall_Service_Interface
	 */
	private function get_uninstall_service() {
		if ( null === $this->uninstall_service ) {
			/**
			 * Uninstall service.
			 *
			 * @var Uninstall_Service_Interface $uninstall_service
			 */
			$uninstall_service       = ServiceRegister::getService( Uninstall_Service_Interface::CLASS_NAME );
			$this->uninstall_service = $uninstall_service;
		}

		return $this->uninstall_service;
	}
}
