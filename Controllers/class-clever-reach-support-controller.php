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

use CleverReach\WooCommerce\Components\Services\BusinessLogic\Support_Service;
use CleverReach\WooCommerce\Components\Util\HTTP_Helper;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\SupportConsole\Contracts\SupportService;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;

/**
 * Class Clever_Reach_Support_Controller
 *
 * @package CleverReach\WooCommerce\Controllers
 */
class Clever_Reach_Support_Controller extends Clever_Reach_Base_Controller {

	/**
	 * Support service.
	 *
	 * @var Support_Service
	 */
	private $support_service;

	/**
	 * Return configuration.
	 *
	 * @return void
	 */
	public function display() {
		if ( $this->is_internal ) {
			$this->validate_internal_call();
		}

		$this->return_json( array( $this->get_support_service()->get() ) );
	}

	/**
	 * Updates configuration
	 *
	 * @return void
	 *
	 * @throws QueryFilterInvalidParamException Wxception if query filter params are invalid.
	 * @throws RepositoryNotRegisteredException Exception if repository not registered.
	 */
	public function modify() {
		if ( $this->is_internal ) {
			$this->validate_internal_call();
		}

		$body = HTTP_Helper::get_body();

		$this->return_json( array( $this->get_support_service()->update( $body ) ) );
	}

	/**
	 * Validates if call made from plugin code is secure by checking session token.
	 * If call is not secure, returns 401 status and terminates request.
	 *
	 * @return void
	 */
	protected function validate_internal_call() {
		$logged_user_id = get_current_user_id();
		if ( empty( $logged_user_id ) ) {
			status_header( 401 );
			nocache_headers();

			exit();
		}
	}

	/**
	 * Returns support service.
	 *
	 * @return Support_Service
	 */
	private function get_support_service() {
		if ( null === $this->support_service ) {
			/**
			 * Support service.
			 *
			 * @var Support_Service $support_service
			 */
			$support_service       = ServiceRegister::getService( SupportService::CLASS_NAME );
			$this->support_service = $support_service;
		}

		return $this->support_service;
	}
}
