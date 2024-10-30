<?php
/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

namespace CleverReach\WooCommerce\Migrations\V300;

use CleverReach\WooCommerce\Components\Util\Step;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\AuthorizationService;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Contracts\AuthorizationService as AuthorizationServiceInterface;
use CleverReach\WooCommerce\IntegrationCore\BusinessLogic\Authorization\Http\UserProxy;
use CleverReach\WooCommerce\IntegrationCore\Infrastructure\ServiceRegister;
use CleverReach\WooCommerce\Migrations\Exceptions\Failed_To_Execute_Migration_Step_Exception;
use Exception;

/**
 * Class Save_User_Info.
 *
 * @package CleverReach\WooCommerce\Migrations\V300
 */
class Save_User_Info extends Step {

	const CLASS_NAME = __CLASS__;

	/**
	 * Execute migration step.
	 *
	 * @throws Failed_To_Execute_Migration_Step_Exception Failed to execute migration step exception|Query filter invalid param exception.
	 */
	public function execute() {
		try {
			if ( $this->get_auth_service()->isOffline() ) {
				return;
			}

			$user_info = $this->get_user_proxy()->getUserInfo();
			$user_info->setLanguage( $this->get_user_proxy()->getUserLanguage( $user_info->getId() ) );
			$this->get_auth_service()->setUserInfo( $user_info );
		} catch ( Exception $e ) {
			throw new Failed_To_Execute_Migration_Step_Exception(
				'Failed to execute migration step because: ' . esc_html( $e->getMessage() )
			);
		}
	}

	/**
	 * Retreive Authorization service.
	 *
	 * @return AuthorizationService
	 */
	private function get_auth_service() {
		/**
		 * Authorization service.
		 *
		 * @var AuthorizationService $auth_service
		 */
		$auth_service = ServiceRegister::getService( AuthorizationServiceInterface::CLASS_NAME );

		return $auth_service;
	}

	/**
	 * Retrieve User proxy.
	 *
	 * @return UserProxy
	 */
	private function get_user_proxy() {
		/**
		 * User proxy.
		 *
		 * @var UserProxy $proxy
		 */
		$proxy = ServiceRegister::getService( UserProxy::CLASS_NAME );

		return $proxy;
	}
}
